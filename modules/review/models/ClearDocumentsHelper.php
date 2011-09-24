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
 * @category    Application
 * @package     Module_Review
 * @author      Thoralf Klein <thoralf.klein@zib.de>
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2011, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Contains code for clearing documents (switching to published state).
 */
class Review_Model_ClearDocumentsHelper {

    /**
     * Publishes documents and adds the given Person as referee.
     *
     * @param array $docIds
     * @param mixed $userId
     * @param Opus_Person $person
     *
     * FIXME capture success or failure for display afterwards
     */
    public function clear(array $docIds = null, $userId = null, $person = null) {
        $logger = Zend_Registry::get('Zend_Log');

        foreach ($docIds AS $docId) {
            $logger->debug('Change state to "published" for document: ' . $docId);
            $document = new Opus_Document($docId);
            $document->setServerState('published');

            $date = new Opus_Date();
            $date->setNow();
            $document->setServerDatePublished($date);
            $document->setPublishedDate($date);

            $guest_role = Opus_UserRole::fetchByName('guest');
            foreach ($document->getFile() AS $file)  {
                $guest_role->appendAccessFile($file->getId());
            }

            if (isset($person)) {
                $document->addPersonReferee($person);
            }

            $enrichment = $document->addEnrichment();
            $enrichment->setKeyName('review.accepted_by')
                    ->setValue($userId);

            // TODO: Put into same transaction...
            $document->store();
            $guest_role->store();
        }

        return;
    }

    /**
     * Rejects documents and adds the given Person as referee.
     *
     * @param array $docIds
     * @param mixed $userId
     * @param Opus_Person $person
     *
     * FIXME capture success or failure for display afterwards
     */
    public function reject(array $docIds = null, $userId = null, $person = null) {
        $logger = Zend_Registry::get('Zend_Log');

        foreach ($docIds AS $docId) {
            $logger->debug('Deleting document with id: ' . $docId);
            $document = new Opus_Document($docId);

            if (isset($person)) {
                $document->addPersonReferee($person);
            }

            $enrichment = $document->addEnrichment();
            $enrichment->setKeyName('review.rejected_by')
                    ->setValue($userId);

            $document->delete();
        }

        return;
    }
}
