#!/usr/bin/env php5
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
 * @package     Import
 * @author      Sascha Szott <szott@zib.de>
 * @copyright   Copyright (c) 2008-2012, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */


require_once dirname(__FILE__) . '/../common/bootstrap.php';
require_once 'Log.php';

class CSVImporter {

    // das ist aktuell nur eine Auswahl der Metadatenfelder (speziell für Fromm zugeschnitten)

    const NUM_OF_COLUMNS = 28;

    const OLD_ID = 0;
    const LANGUAGE = 1;
    const TYPE = 2;
    const SERVER_STATE = 3;
    const TITLE_MAIN_LANGUAGE = 4;
    const TITLE_MAIN_VALUE = 5;
    const ABSTRACT_LANGUAGE = 6;
    const ABSTRACT_VALUE = 7;
    const OTHER_TITLE_TYPE = 8;
    const OTHER_TITLE_LANGUAGE = 9;
    const OTHER_TITLE_VALUE = 10;
    const PERSON_TYPE = 11;
    const PERSON_FIRSTNAME = 12;
    const PERSON_LASTNAME = 13;
    const DATE_TYPE = 14;
    const DATE_VALUE = 15;
    const IDENTIFIER_TYPE = 16;
    const IDENTIFIER_VALUE = 17;
    const NOTE_VISIBILITY = 18;
    const NOTE_VALUE = 19;
    const COLLECTION_ID = 20;
    const LICENCE_ID = 21;
    const ENRICHMENTS = 22; // wird aktuell ignoriert
    //TODO bei Fromm gibt es fünf Enrichmentkeys
    const ENRICHMENT_AVAILABILITY = 23;
    const ENRICHMENT_FORMAT = 24;
    const ENRICHMENT_KINDOFPUBLICATION = 25;
    const ENRICHMENT_IDNO = 26;
    const ENRICHMENT_RELEVANCE = 27;


    public function run($argv) {
        if (count($argv) < 2) {
            echo "missing file name\n";
            return;
        }

        $ignoreHeader = true;
        if (count($argv) > 2 && $argv[2] == 'noheader') {
            $ignoreHeader = false;
        }

        $filename = $argv[1];
        if (!is_readable($filename)) {
            echo "import file does not exist or is not readable\n";
        }

        $file = fopen($filename, 'r');
        if (!$file) {
            echo "Error while opening import file\n";
        }

        $rowCounter = 0;
        $docCounter = 0;
        $errorCounter = 0;
        // TODO Feldtrenner und Feldbegrenzer konfigurierbar machen
        while (($row = fgetcsv($file, 0, "\t", '"', '\\')) != false) {
            $rowCounter++;
            $numOfCols = count($row);
            if ($numOfCols != self::NUM_OF_COLUMNS) {
                echo "unexpected number of columns ($numOfCols) in row $rowCounter: row is skipped\n";           
                // TODO add to reject.log
                continue;
            }
            if ($ignoreHeader && $rowCounter == 1) {
                continue;
            }
            if ($this->processRow($row)) {
                $docCounter++;
            }
            else {
                $errorCounter++;
            }            
        }

        echo "number of rows: $rowCounter\n";
        echo "number of created docs: $docCounter\n";
        echo "number of skipped docs: $errorCounter\n";

        fclose($file);
    }

    private function processRow($row) {        
        $doc = new Opus_Document();

        $doc->unregisterPlugin('Opus_Document_Plugin_XmlCache');
        $doc->unregisterPlugin('Opus_Document_Plugin_SequenceNumber');

        $oldId = $row[self::OLD_ID];

        try {
            $doc->setLanguage($row[self::LANGUAGE]);
            // Dokumenttyp muss kleingeschrieben werden (bei Fromm aber groß)
            $doc->setType(lcfirst($row[self::TYPE]));
            $doc->setServerState($row[self::SERVER_STATE]);
            $this->processTitlesAndAbstract($row, $doc, $oldId);
            $this->processPersons($row, $doc, $oldId);
            $this->processDate($row, $doc, $oldId);
            $this->processIdentifier($row, $doc);
            $this->processNote($row, $doc);
            $this->processCollections($row, $doc);
            $this->processLicence($row, $doc);

            // TODO Fromm verwendet aktuell fünf Enrichments (muss noch generalisiert werden)
            $enrichementkeys = array(
                self::ENRICHMENT_AVAILABILITY,
                self::ENRICHMENT_FORMAT,
                self::ENRICHMENT_KINDOFPUBLICATION,
                self::ENRICHMENT_IDNO,
                self::ENRICHMENT_RELEVANCE
            );
            foreach ($enrichementkeys as $enrichmentkey) {
                $this->processEnrichment($enrichmentkey, $row, $doc);
            }

            $doc->store();
            //echo "created document #" . $doc->getId() . "\n";
            return true;
        }
        catch (Exception $e) {
            echo "import of document " . $oldId . " was not successful: " . $e->getMessage() . "\n";
        }
        return false;
    }

    private function processTitlesAndAbstract($row, $doc, $oldId) {
        $t = $doc->addTitleMain();
        $t->setValue(trim($row[self::TITLE_MAIN_VALUE]));
        $t->setLanguage(trim($row[self::TITLE_MAIN_LANGUAGE]));

        // Abstract ist kein Pflichtfeld
        if (trim($row[self::ABSTRACT_LANGUAGE]) != '') {
            if (trim($row[self::ABSTRACT_VALUE]) != '') {
                $t = $doc->addTitleAbstract();
                $t->setValue(trim($row[self::ABSTRACT_VALUE]));
                $t->setLanguage(trim($row[self::ABSTRACT_LANGUAGE]));
            }
            else {
                echo "Dokument $oldId mit leerem Abstract, aber vorhandener Sprachangabe\n";
            }
        }

        // weitere Titel sind nicht Pflicht
        if (trim($row[self::OTHER_TITLE_LANGUAGE]) != '') {
            if (trim($row[self::OTHER_TITLE_VALUE]) != '') {
                $method = 'addTitle' . ucfirst($row[self::OTHER_TITLE_TYPE]);
                $t = $doc->$method();
                $t->setValue(trim($row[self::OTHER_TITLE_VALUE]));
                $t->setLanguage(trim($row[self::OTHER_TITLE_LANGUAGE]));
            }
            else {
                echo "Dokument $oldId mit leerem Titel (Typ: " . $row[self::OTHER_TITLE_TYPE] . "), aber vorhandener Sprachangabe\n";
            }
        }
    }

    private function processPersons($row, $doc, $oldId) {
        // Personen sind nicht Pflicht

        if ($row[self::PERSON_TYPE] != '') {


            // die drei Spalten persontype, firstname, lastname können mehrere
            // Personen enthalten
            // in diesem Fall erfolgt die Abtrennung *innerhalb* des Felds durch ||
            $types = $row[self::PERSON_TYPE];
            $firstnames = $row[self::PERSON_FIRSTNAME];
            $lastnames = $row[self::PERSON_LASTNAME];
            $numOfPipesTypeField = substr_count($types, '||');
            $numOfPipesTypeFirstnames = substr_count($firstnames, '||');
            $numOfPipesTypeLastnames = substr_count($lastnames, '||');
            if ($numOfPipesTypeFirstnames > 0) {
                if ($numOfPipesTypeField == 0) {
                    // alle Personen haben den gleichen Typ
                    if ($numOfPipesTypeFirstnames != $numOfPipesTypeLastnames) {
                        throw new Exception("skip all persons of document $oldId");
                    }
                }
                else {
                    if (!($numOfPipesTypeField == $numOfPipesTypeFirstnames && $numOfPipesTypeField == $numOfPipesTypeLastnames)) {
                        throw new Exception("skip all persons of document $oldId");
                    }
                }
                $firstnames = explode('||', $firstnames);
                $lastnames = explode('||', $lastnames);
                $types = explode('||', $types);
                for ($i = 0; $i <= $numOfPipesTypeFirstnames; $i++) {
                    if ($numOfPipesTypeField == 0) {
                        $this->addPerson($doc, $types[0], $firstnames[$i], $lastnames[$i], $oldId);
                    }
                    else {
                        $this->addPerson($doc, $types[$i], $firstnames[$i], $lastnames[$i], $oldId);
                    }
                }
            }
            else {
                if (!($numOfPipesTypeLastnames == 0 && $numOfPipesTypeField == 0)) {
                    throw new Exception("skip all persons of document $oldId");
                }
                $this->addPerson($doc, $types, $firstnames, $lastnames, $oldId);
            }
        }
    }

    private function addPerson($doc, $type, $firstname, $lastname, $oldId) {
        $p = new Opus_Person();
        if (trim($firstname) == '') {
            // TODO OPUSHOSTING-70
            echo "Datensatz $oldId ohne Wert für $type.firstname\n";
            $p->setFirstName('anonymous');
        }
        else {
            $p->setFirstName(trim($firstname));
        }
        $p->setLastName(trim($lastname));

        $method = 'addPerson' . ucfirst(trim($type));
        $doc->$method($p);        
    }

    private function processDate($row, $doc, $oldId) {
        // TODO aktuell nur Unterstützung für Jahreszahlen
        $method = 'set' . ucfirst($row[self::DATE_TYPE]) . "Year";
        $date = trim($row[self::DATE_VALUE]);
        if (!preg_match("/^[0-9]{4}$/", $date)) {
            $date = '0000';
            echo "Dokument $oldId mit Jahresangabe 0000 versehen\n";
        }
        $doc->$method($date);
    }

    private function processIdentifier($row, $doc) {
        // TODO aktuell nur Unterstützung für *einen* Identifier
        // ist kein Pflichtfeld
        if (trim($row[self::IDENTIFIER_TYPE]) != '') {
            $i = $doc->addIdentifier();
            $i->setValue(trim($row[self::IDENTIFIER_VALUE]));
            $i->setType($row[self::IDENTIFIER_TYPE]);
        }
    }

    private function processNote($row, $doc) {
        // TODO aktuell nur Unterstützung für *eine* Note
        // ist kein Pflichtfeld
        if (trim($row[self::NOTE_VISIBILITY]) != '') {
            $n = $doc->addNote();
            $n->setMessage(trim($row[self::NOTE_VALUE]));
            $n->setVisibility($row[self::NOTE_VISIBILITY]);
        }
    }

    private function processCollections($row, $doc) {
        // TODO mehrere Collection-IDs können innerhalb des Felds durch || getrennt werden
        // ist kein Pflichtfeld                
        if (trim($row[self::COLLECTION_ID]) != '') {
            $collIds = explode('||', $row[self::COLLECTION_ID]);
            foreach ($collIds as $collId) {
                $collectionId = trim($collId);
                // check if collection with given id exists
                try {
                    $c = new Opus_Collection($collectionId);
                    $doc->addCollection($c);
                }
                catch (Opus_Model_NotFoundException $e) {
                    throw new Exception('collection id ' . $collectionId . ' does not exist: ' . $e->getMessage());
                }
            }
        }
    }

    private function processLicence($row, $doc) {
        // TODO aktuell nur Unterstützung für *eine* Lizenz
        if (trim($row[self::LICENCE_ID]) != '') {
            $licenceId = trim($row[self::LICENCE_ID]);
            try {
                $l = new Opus_Licence($licenceId);
                $doc->addLicence($l);
            }
            catch (Opus_Model_NotFoundException $e) {
                throw new Exception('licence id ' . $licenceId . ' does not exist: ' . $e->getMessage());
            }            
        }
    }

    private function processEnrichment($enrichmentkey, $row, $doc, $oldId) {
        // aktuell hat der Feldinhalt die Struktur '{ ekey: evalue }'
        // TODO das ist natürlich redundant, da innerhalb einer Spalte immer
        // nur Enrichments eines Enrichmentkeys stehen
        $value = trim($row[$enrichmentkey]);
        if ($value != '') {
            preg_match('/^{([A-Za-z]+):(.+)}$/', $value, $matches);

            if (count($matches) != 3) {
                throw new Exception("unerwarteter Wert '$value' für Enrichment in Spalte $enrichmentkey");
            }
            
            $key = trim($matches[1]);
            // check if enrichment key exists
            try {
                new Opus_EnrichmentKey($key);
            }
            catch (Opus_Model_NotFoundException $e) {
                throw new Exception('enrichment key ' . $key . ' does not exist: ' . $e->getMessage());
            }

            $e = $doc->addEnrichment();
            $e->setKeyName($key);
            $e->setValue(trim($matches[2]));
        }
    }
}

$importer = new CSVImporter();
$importer->run($argv);


