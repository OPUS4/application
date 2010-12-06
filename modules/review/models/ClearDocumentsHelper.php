<?php
/*
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
 * @category    TODO
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Contains code for clearing documents (switching to published state).
 */
class Review_Model_ClearDocumentsHelper {

    /**
     * Publishes documents and adds referee.
     *
     * @param array $docIds
     * @param string $lastName
     * @param string $firstName
     *
     * FIXME add referee
     * FIXME capture success or failure for display afterwards
     */
    public function clear($docIds, $lastName = null, $firstName = null, $email = null) {
        $logger = Zend_Registry::get('Zend_Log');

        $logger->debug('Clearing documents.');

        foreach ($docIds as $index => $docId) {
            $document = new Opus_Document( (int) $docId);

            try {
                $state = $document->getServerState();
 
                if ($state === 'unpublished') {
                    $logger->debug('Change state to \'published\' for document:' . $docId);
                    $document->setServerState('published');

                    $person = new Opus_Person();
                    $person->setFirstName($firstName);
                    $person->setLastName($lastName);
                    $person->setEmail($email);
                    $document->addPersonReferee($person);

                    $date = new Opus_Date();
                    $date->setNow();

                    $document->setServerDatePublished($date);

                    $config = Zend_Registry::get('Zend_Config');

                    $moduleConfig = $config->clearing;

                    if (isset($moduleConfig)) {
                        if ($moduleConfig->setPublishedDate) {
                            $document->setPublishedDate($date);
                        }
                    }

                    $document->getCollection(); // FIXME make sure collections are loaded (OPUSVIER-863)

                    $document->store();
                }
                else {
                    // already published or deleted
                    $logger->warn('Document ' . $docId . ' already published.');
                }
            }
            catch (Exception $e) {
                $logger->err($e);
                // TODO throw something, show something
            }

        }

    }

    public function reject($docIds) {
        foreach ($docIds as $index => $docId) {
            $document = new Opus_Document( (int) $docId);

            try {
                $state = $document->getServerState();

                if ($state === 'unpublished') {
                    $document->delete();
                }
                else {
                    // already published or deleted
                    $logger->warn('Document ' . $docId . ' not in unpublished
                        state.');
                }
            }
            catch (Exception $e) {
                $logger->err($e);
                // TODO throw something, show something
            }
        }
    }

}

?>
