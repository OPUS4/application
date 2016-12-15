<?php
/**
 * This file is part of OPUS. The software OPUS has been originally developed
 * at the University of Stuttgart with funding from the German Research Net,
 * the Federal Department of Higher Education and Research and the Ministry
 * of Science, Research and the Arts of the State of Baden-Wuerttemberg.
 *
 * OPUS 4 is a complete rewrite of the original OPUS software and was developed
 * by the Stuttgart University Library, the Library Service Center
 * Baden-Wuerttemberg, the North Rhine-Westphalian Library Service Center,
 * the Cooperative Library Network Berlin-Brandenburg, the Saarland University
 * and State Library, the Saxon State Library - Dresden State and University
 * Library, the Bielefeld University Library and the University Library of
 * Hamburg University of Technology with funding from the German Research
 * Foundation and the European Regional Development Fund.
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
 * @category   Application
 * @package    Module_Oai
 * @author     Thoralf Klein <thoralf.klein@zib.de>
 * @copyright  Copyright (c) 2012, OPUS 4 development team
 * @license    http://www.gnu.org/licenses/gpl.html General Public License
 * @version    $Id$
 */

class Oai_Model_DocumentList {

    /**
     * Holds information about which document state aka server_state
     * are delivered out
     *
     * @var array
     *
     * TODO should be private
     */
    public $deliveringDocumentStates = null;

    /**
     * Holds restriction types for xMetaDiss
     *
     * @var array
     *
     * TODO should be private
     */
    public $xMetaDissRestriction = null;

    /**
     * Retrieve all document ids for a valid oai request.
     *
     * @param array &$oaiRequest
     * @return array
     *
     * TODO function contains metadataPrefix specifische criteria for generating document list (refactor!)
     */
    public function query(array $oaiRequest) {
        $today = date('Y-m-d', time());

        $finder = new Opus_DocumentFinder();

        // add server state restrictions
        $finder->setServerStateInList($this->deliveringDocumentStates);

        $metadataPrefix = $oaiRequest['metadataPrefix'];
        if ('xMetaDissPlus' === $metadataPrefix 
            || 'xMetaDiss' === $metadataPrefix) {
            $finder->setFilesVisibleInOai();
            $finder->setNotEmbargoedOn($today);
        }
        if ('xMetaDiss' === $metadataPrefix) {
            $finder->setTypeInList($this->xMetaDissRestriction);
            $finder->setNotEmbargoedOn($today);
        }
        if ('epicur' === $metadataPrefix) {
            $finder->setIdentifierTypeExists('urn');
        }

        if (array_key_exists('set', $oaiRequest)) {
            $setarray = explode(':', $oaiRequest['set']);
            if (!isset($setarray[0])) {
                return array();
            }

            if ($setarray[0] == 'doc-type') {
                if (count($setarray) === 2 and !empty($setarray[1])) {
                    $finder->setType($setarray[1]);
                }
                else {
                    return array();
                }
            }
            else if ($setarray[0] == 'bibliography') {
                if (count($setarray) !== 2 or empty($setarray[1])) {
                    return array();
                }
                $setValue = $setarray[1];

                $bibliographyMap = array(
                    "true"  => 1,
                    "false" => 0,
                );
                if (false === isset($setValue, $bibliographyMap[$setValue])) {
                    return array();
                }

                $finder->setBelongsToBibliography($bibliographyMap[$setValue]);
            }
            else {
                if (count($setarray) < 1 or count($setarray) > 2) {
                    $msg = "Invalid SetSpec: Must be in format 'set:subset'.";
                    throw new Oai_Model_Exception($msg);
                }

                // Trying to locate collection role and filter documents.
                $role = Opus_CollectionRole::fetchByOaiName($setarray[0]);
                if (is_null($role)) {
                    $msg = "Invalid SetSpec: Top level set does not exist.";
                    throw new Oai_Model_Exception($msg);
                }
                $finder->setCollectionRoleId($role->getId());

                // Trying to locate given collection and filter documents.
                if (count($setarray) == 2) {
                    $subsetName = $setarray[1];
                    $foundSubsets = array_filter(
                        $role->getOaiSetNames(), function ($s) use ($subsetName) {
                                return $s['oai_subset'] === $subsetName;
                        }
                    );

                    if (count($foundSubsets) < 1) {
                        $emptySubsets = array_filter($role->getAllOaiSetNames(), function ($s) use ($subsetName) {
                            return $s['oai_subset'] === $subsetName;
                        });

                        if (count($emptySubsets) === 1) {
                            return array();
                        }
                        else {
                            $msg = "Invalid SetSpec: Subset does not exist.";
                            throw new Oai_Model_Exception($msg);
                        }
                    }

                    foreach ($foundSubsets AS $subset) {
                        if ($subset['oai_subset'] !== $subsetName) {
                            $msg = "Invalid SetSpec: Internal error.";
                            throw new Oai_Model_Exception($msg);
                        }
                        $finder->setCollectionId($subset['id']);
                    }
                }
            }

        }

        if (array_key_exists('from', $oaiRequest) and !empty($oaiRequest['from'])) {
            $from = DateTime::createFromFormat('Y-m-d', $oaiRequest['from']);
            $finder->setServerDateModifiedAfter($from->format('Y-m-d'));
        }

        if (array_key_exists('until', $oaiRequest)) {
            $until = DateTime::createFromFormat('Y-m-d', $oaiRequest['until']);
            $until->add(new DateInterval('P1D'));
            $finder->setServerDateModifiedBefore($until->format('Y-m-d'));
        }

        return $finder->ids();
    }
}
