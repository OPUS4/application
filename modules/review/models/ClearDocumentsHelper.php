<?php

/**
 * This file is part of OPUS. The software OPUS has been originally developed
 * at the University of Stuttgart with funding from the German Research Net,
 * the Federal Department of Higher Education and Research and the Ministry
 * of Science, Research and the Arts of the State of Baden-Wuerttemberg.
 *
 * OPUS 4 is a complete rewrite of the original OPUS software and was developed
 * by the Stuttgart University Library, the Library Service Center
 * Baden-Wuerttemberg, the Cooperative Library Network Berlin-Brandenburg,
 * the Saarland University and State Library, the Saxon State Library -
 * Dresden State and University Library, the Bielefeld University Library and
 * the University Library of Hamburg University of Technology with funding from
 * the German Research Foundation and the European Regional Development Fund.
 *
 * LICENCE
 * OPUS is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or any later version.
 * OPUS is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
 * details. You should have received a copy of the GNU General Public License
 * along with OPUS; if not, write to the Free Software Foundation, Inc., 51
 * Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\Date;
use Opus\Common\Document;
use Opus\Common\Log;
use Opus\Common\Person;
use Opus\Common\UserRole;

/**
 * Contains code for clearing documents (switching to published state).
 */
class Review_Model_ClearDocumentsHelper extends Application_Model_Abstract
{
    /**
     * Publishes documents and adds the given Person as referee.
     *
     * @param null|mixed  $userId
     * @param null|Person $person
     *
     * FIXME capture success or failure for display afterwards
     */
    public function clear(?array $docIds = null, $userId = null, $person = null)
    {
        $logger = Log::get();

        foreach ($docIds as $docId) {
            $logger->debug('Change state to "published" for document: ' . $docId);
            $document = Document::get($docId);
            $document->setServerState('published');

            $date = new Date();
            $date->setNow();
            $document->setServerDatePublished($date);

            // Only set PublishedDate, if it is empty (the field is used in various ways)
            $publishedDate = $document->getPublishedDate();
            if ($publishedDate === null) {
                $document->setPublishedDate($date);
            }

            $guestRole = null;

            if ($this->isAddGuestAccessEnabled()) {
                $guestRole = UserRole::fetchByName('guest');
                foreach ($document->getFile() as $file) {
                    $guestRole->appendAccessFile($file->getId());
                }
            }

            if (isset($person)) {
                $document->addPersonReferee($person);
            }

            $enrichment = $document->addEnrichment();
            $enrichment->setKeyName('review.accepted_by')
                    ->setValue($userId);

            // TODO: Put into same transaction...
            $document->store();

            if ($guestRole !== null) {
                $guestRole->store();
            }
        }
    }

    /**
     * @return bool
     */
    public function isAddGuestAccessEnabled()
    {
        $config = $this->getConfig();

        return ! isset($config->workflow->stateChange->published->addGuestAccess) ||
            filter_var($config->workflow->stateChange->published->addGuestAccess, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Rejects documents and adds the given Person as referee.
     *
     * @param null|mixed  $userId
     * @param null|Person $person
     *
     * FIXME capture success or failure for display afterwards
     */
    public function reject(?array $docIds = null, $userId = null, $person = null)
    {
        $logger = Log::get();

        foreach ($docIds as $docId) {
            $logger->debug('Deleting document with id: ' . $docId);
            $document = Document::get($docId);

            if (isset($person)) {
                $document->addPersonReferee($person);
            }

            $enrichment = $document->addEnrichment();
            $enrichment->setKeyName('review.rejected_by')
                    ->setValue($userId);

            $document->setServerState(Document::STATE_DELETED);
            $document->store();
        }
    }
}
