#!/usr/bin/env php5
<?php

/** This file is part of OPUS. The software OPUS has been originally developed
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
 * @author      Sascha Szott <szott@zib.de>
 * @author      Susanne Gottwald <gottwald@zib.de>
 * @copyright   Copyright (c) 2008-2012, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 **/

require_once dirname(__FILE__) . '/../common/bootstrap.php';

class FindMissingSeriesNumbers {

    private $logger;
    
    private $seriesRole;

    public function  __construct($logfile) {
        $this->seriesRole = Opus_CollectionRole::fetchByName('series');
        $this->initLogger($logfile);
    }
    
    /**
     * Initialise the logger with the given file.
     */
    private function initLogger($logfileName) {
        $logfile = @fopen($logfileName, 'a', false);
        $writer = new Zend_Log_Writer_Stream($logfile);        
	$formatter=new Zend_Log_Formatter_Simple('%priorityName%: %message%' . PHP_EOL);
	$writer->setFormatter($formatter);
        $this->logger = new Zend_Log($writer);        
    }
        

    /**
     *
     * Problem:
     * Dokumente, die einer Collection der Collection Role series zugeordnet sind und keinen IdentifierSerial
     * besitzen, müssen behandelt werden. Grund: bei der Migration in die neue Schriftenreihen-Struktur muss pro
     * Dokument und Schriftenreihe immer eine Bandnummer vergeben werden.
     *
     * Konfliktlösungsstrategie:
     * Für die betroffenen Dokumente wird im Rahmen der Migration eine künstliche Bandnummer
     * bestimmt.
     *
     * Logfile:
     * Im Logfile werden die IDs der betreffenden Dokumente ausgegeben, für die eine künstliche Bandnummer bestimmt
     * wird. Es wird außerdem die ID der Schriftenreihe ausgegeben, in deren Kontext das Dokumente eine künstliche
     * Bandnummer erhalten hat.
     * 
     * Erforderlich Nacharbeit durch den Benutzer:
     * @TODO
     *
     */
    private function handleMissingIdentifierSerials() {
        $conflictsFound = 0;
        $finder = new Opus_DocumentFinder();        
        $finder->setCollectionRoleId($this->seriesRole->getId());
        foreach ($finder->ids() as $docId) {
            $doc = new Opus_Document($docId);
            if (count($doc->getIdentifierSerial()) == 0) {
                $this->logger->info("doc #$docId : does not have a field IdentifierSerial -- document will not be migrated into new series structure");
                foreach (Opus_Collection::fetchCollectionIdsByDocumentId($docId) as $collectionId) {
                    $collection = new Opus_Collection($collectionId);
                    if ($collection->getRoleId() === $this->seriesRole->getId()) {
                        $this->logger->info("doc #$docId : needs to be manually migrated into series #" . $collectionId);
                        $conflictsFound++;
                    }
                }
            }
        }
        return $conflictsFound;
    }

    /**
     * 
     * Problem:
     * Dokumente, die einer Collection der Collection Role series zugeordnet
     * sind und mehr als einen IdentifierSerial besitzen, müssen behandelt werden.
     * Grund: bei der Migration in die neue Schriftenreihen-Struktur kann pro
     * Dokument und Schriftenreihe nur eine Bandnummer vergeben werden.
     *
     * Konfliktlösungsstrategie:
     * Es wird der ein IdentifierSerial beibehalten und als Bandnummer migriert.
     * Die restlichen IdentifierSerial werden gelöscht. Die Werte werden als
     * Enrichments mit dem Schlüssel 'serialmigration' angelegt.
     *
     * Logfile:
     * Im Logfile werden die IDs der betreffenden Dokumente ausgegeben. Außerdem
     * werden die Werte der gelöschten IdentifierSerial ausgegeben, die
     * jeweils in einem Enrichment 'serialmigration' gespeichert wurden.
     *
     * Erforderlich Nacharbeit durch den Benutzer:
     * @TODO
     * 
     */
    private function handleDocumentsWithMultipleIdentifierSerials() {
        $conflictsFound = 0;
        $finder = new Opus_DocumentFinder();
        $finder->setIdentifierTypeExists('serial');        
        $finder->setCollectionRoleId($this->seriesRole->getId());

        foreach ($finder->ids() as $docId) {
            $doc = new Opus_Document($docId);
            $serialIds = $doc->getIdentifierSerial();
            if (count($serialIds) > 1) {
                $this->logger->info("doc #$docId : needs to be updated manually (has " . count($serialIds) . ' values in field IdentifierSerial)');
                $this->createEnrichmentKeyIfItDoesNotExist('serialmigration');

                $remainingIdentifierSerial = $serialIds[0];                
                array_shift($serialIds);
                
                foreach ($serialIds as $serialId) {
                    $serialValue = $serialId->getValue();
                    try {
                        $doc->addEnrichment()->setKeyName('serialmigration')->setValue($serialValue);
                        $this->logger->info("doc #$docId : removed IdentifierSerial and stored value " . $serialValue . ' in enrichment serialmigration');
                        $doc->store();
                    }
                    catch (Opus_Enrichment_NotUniqueException $e) {
                        // enrichment serialmigration with value $serialvalue already exists for given document
                        $this->logger->info("doc #$docId : duplicate value " . $serialValue . ' in field IdentifierSerial -- removed duplicate value');
                        $doc = new Opus_Document($docId);
                    }
                    $conflictsFound++;
                }
                // remove all but the first SerialIdentifer from document
                $doc->setIdentifierSerial($remainingIdentifierSerial);
                $doc->store();
            }
        }
        return $conflictsFound;
    }

    /**
     * 
     * Problem:
     * Wenn zwei oder mehrere Dokumente existieren, die jeweils der gleichen Collection in der Collection Role
     * series zugeordnet wurden, und den gleichen Wert im Feld IdentifierSerial besitzen, dann müssen wir vor
     * der Übernahme der Dokumente in die neue Series-Struktur die Eindeutigkeit von IdentifierSerial für die
     * betroffenen Dokumente sicherstellen. Andernfalls würde das anschließend ausgeführte Migrationsskript
     * einen Verstoß gegen die Constraints auf der Tabelle link_documents_series auslösen (Bandnummer muss
     * innerhalb einer Schriftenreihe eindeutig sein)
     *
     * Konfliktlösungsstrategie:
     * Für das erste Dokument wird der IdentifierSerial belassen. Für alle anderen betroffenen Dokumente
     * wird der IdentifierSerial gelöscht. Der Wert wird in das Enrichment serialmigration geschrieben.
     *
     * Logfile:
     * Im Logfile werden die IDs der betreffenden Dokumente ausgegeben. Außerdem werden die IDs der gelöschten
     * IdentifierSerial ausgegeben und der jeweilige Werte, der im Enrichment serialmigration gespeichert wurde.
     *
     * Erforderlich Nacharbeit durch den Benutzer:
     * @TODO
     *
     */
    private function handleConflictingIdentifierSerials() {
        $conflictsFound = 0;
        $finder = new Opus_DocumentFinder();
        $finder->setIdentifierTypeExists('serial');
        $finder->setCollectionRoleId($this->seriesRole->getId());

        $serialIdsInUse = array();
        foreach ($finder->ids() as $docId) {
            $doc = new Opus_Document($docId);            

            $seriesCollectionIds = array();
            foreach (Opus_Collection::fetchCollectionIdsByDocumentId($docId) as $collectionId) {
                $c = new Opus_Collection($collectionId);
                if (!$c->isRoot() && $c->getRoleId() === $this->seriesRole->getId()) {
                    array_push($seriesCollectionIds, $collectionId);
                }
            }

            $serialIdsToRemove = array();
            foreach ($doc->getIdentifierSerial() as $serialId) {
                $serialValue = $serialId->getValue();
                foreach ($seriesCollectionIds as $collectionId) {
                    if (array_key_exists($collectionId, $serialIdsInUse)) {
                        if (in_array($serialValue, $serialIdsInUse[$collectionId])) {
                            array_push($serialIdsToRemove, $serialId->getId());
                        }
                    }
                }
            }
            if (!empty($serialIdsToRemove)) {
                $remainingSerialIds = array();
                foreach ($doc->getIdentifierSerial() as $serialId) {
                    if (!in_array($serialId->getId(), $serialIdsToRemove)) {
                        array_push($remainingSerialIds, $serialId);
                    }
                    else {
                        $serialValue = $serialId->getValue();
                        // value of field IdentifierSerial is already in use: remove it from document and store it in enrichment serialmigration
                        $this->logger->info("doc #$docId : has a conflicting value in field IdentifierSerial and needs to be updated manually");
                        $this->createEnrichmentKeyIfItDoesNotExist('serialmigration');
                        try {
                            $doc->addEnrichment()->setKeyName('serialmigration')->setValue($serialValue);
                            $doc->store();
                        }
                        catch (Opus_Enrichment_NotUniqueException $e) {
                            // enrichment serialmigration with value $serialvalue already exists for given document
                            $doc = new Opus_Document($docId);
                        }
                        $this->logger->info("doc #$docId : removed IdentifierSerial and stored value " . $serialValue . ' in enrichment serialmigration');
                        $conflictsFound++;
                    }
                }
                $doc->setIdentifierSerial($remainingSerialIds);
                $doc->store();

                if (empty($remainingSerialIds)) {
                    foreach ($seriesCollectionIds as $collectionId) {
                        $this->logger->info("doc #$docId : needs to be manually migrated into series #$collectionId");
                    }
                }
            }
            
            foreach ($doc->getIdentifierSerial() as $serialId) {
                $serialValue = $serialId->getValue();
                if (!in_array($serialId->getId(), $serialIdsToRemove)) {
                    foreach ($seriesCollectionIds as $collectionId) {
                        if (array_key_exists($collectionId, $serialIdsInUse)) {
                            array_push($serialIdsInUse[$collectionId], $serialValue);
                        }
                        else {
                            $serialIdsInUse[$collectionId] = array($serialValue);
                        }
                    }
                }
            }
        }
        return $conflictsFound;
    }

    private function createEnrichmentKeyIfItDoesNotExist($keyName) {
        try {
            $enrichmentKey = new Opus_EnrichmentKey();
            $enrichmentKey->setName($keyName);
            $enrichmentKey->store();
            $this->logger->debug('created enrichment key ' . $keyName);
        }
        catch (Opus_Model_Exception $e) {
            // enrichment key does already exist
        }
    }

    private function migrateCollectionToSeries() {
        $numOfCollectionsMigrated = 0;
        foreach (Opus_Collection::fetchCollectionsByRoleId($this->seriesRole->getId()) as $collection) {            
            // ignore root collection (does not have valid data and associated documents)
            if ($collection->isRoot()) {
                continue;
            }
            $series = new Opus_Series(Opus_Series::createRowWithCustomId($collection->getId()));
            $series->setTitle($collection->getName());
            $series->setVisible($collection->getVisible());
            $series->setSortOrder($collection->getSortOrder());
            $series->store();
            $this->logger->debug('created series with id ' . $collection->getId());
            $numOfCollectionsMigrated++;
        }
        return $numOfCollectionsMigrated;
    }

    private function migrateDocuments() {
        $numOfDocsMigrated = 0;
        $finder = new Opus_DocumentFinder();
        $finder->setIdentifierTypeExists('serial');
        $finder->setCollectionRoleId($this->seriesRole->getId());
        
        foreach ($finder->ids() as $docId) {
            $doc = new Opus_Document($docId);            

            // remove identifier serial
            $serial = $doc->getIdentifierSerial();
            if (count($serial) !== 1) {
                // this case is not intended to occur
                $this->logger->error("doc #$docId : has " . count($serial) . ' values for field IdentifierSerial -- ignore document while migrating series');
                continue;
            }
            $serialNumber = $serial[0]->getValue();            
            $doc->setIdentifierSerial(array());
            $this->logger->debug("doc #$docId : removed field IdentifierSerial " . $serialNumber);

            // remove document from collections and assign it to series
            $collections = $doc->getCollection();
            $remainingCollections = array();
            foreach ($collections as $collection) {
                if ($collection->getRoleId() === $this->seriesRole->getId()) {                    
                    $series = new Opus_Series($collection->getId());
                    $doc->addSeries($series)->setNumber($serialNumber);
                    $this->logger->debug("doc #$docId: removed assignment from collection #" . $collection->getId());
                    $this->logger->debug("doc #$docId: created assignment to series #" . $collection->getId() . ' with value ' . $serialNumber);
                }
                else {
                    array_push($remainingCollections);
                }
            }
            $doc->setCollection($remainingCollections);
            $doc->store();
            $numOfDocsMigrated++;
        }
        return $numOfDocsMigrated;
    }

    private function hideCollectionRoleSeries() {
        $this->seriesRole->setVisible(0);
        $this->seriesRole->setVisibleBrowsingStart(0);
        $this->seriesRole->setVisibleFrontdoor(0);
        $this->seriesRole->setVisibleOai(0);
        $this->seriesRole->store();
    }

    public function run() {               
        $conflictsFound = $this->handleMissingIdentifierSerials();
        $conflictsFound += $this->handleDocumentsWithMultipleIdentifierSerials();
        $conflictsFound += $this->handleConflictingIdentifierSerials();

        $numOfCollectionsMigrated = $this->migrateCollectionToSeries();
        $numOfDocsMigrated = $this->migrateDocuments();
        $this->hideCollectionRoleSeries();
        
        return array($conflictsFound, $numOfCollectionsMigrated, $numOfDocsMigrated);
    }
}
if ($argc < 2) {
    echo "missing argument: logfile\n";
    exit;
}
echo "migrating series -- can take a while";
$numbers = new FindMissingSeriesNumbers($argv[1]);
$result = $numbers->run();
if ($result[0] > 0 || $result[1] > 0 || $result[2] > 0) {
    echo "\n" . $result[0] . ' conflicts were found while series migration';
    echo "\n" . $result[1] . ' collections were migrated into series';
    echo "\n" . $result[2] . ' documents were migrated into series';
    echo "\nMore information can be found in the log file $argv[1]\n";
}