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
 * @package     Module_Import
 * @author      Oliver Marahrens <o.marahrens@tu-harburg.de>
 * @author      Gunar Maiwald <maiwald@zib.de>
 * @copyright   Copyright (c) 2009, 2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

require_once 'Opus3CollectionsImport.php';
require_once 'Opus3FileImport.php';
require_once 'Opus3InstituteImport.php';
require_once 'Opus3LicenceImport.php';
require_once 'Opus3XMLImport.php';

class Opus3Migration_Readline  {

    private $importfile;
    private $importData;
    private $fileinput;
    //private $doclist = array();
    private $path;
    private $magicPath = '/usr/share/file/magic'; # on Ubuntu-Systems this should be the magic path
    private $stylesheet;
    private $xslt;

    // Set XMl-Dump-Impor
    public function init($file = null) {
        $this->setStylesheet();
        $importFilePath = $this->importfile;
        while (false === file_exists($file)) {
            $file = readline('Please type the path to your OPUS3 database export file (a dumpfile of the database in XML format e.g. /usr/local/opus/complete_database.xml): ');
        }
        $this->importfile = $file;
        $this->importData = $this->loadImportFile();
    }

    // Create Collections
    public function create_collection_roles() {


        $roles = array(
            "Institute" => array("name" => "institutes", "position" => 1),
            "Collections" => array("name" => "collections", "position" => 9),
            "Sammlungen" => array("name" => "series", "position" => 10)
        );

        foreach ($roles as $r) {
            $role = new Opus_CollectionRole();
            $role->setName($r["name"]);
            $role->setOaiName($r["name"]);
            $role->setPosition($r["position"]);
            $role->setVisible(1);
            $role->setVisibleBrowsingStart(1);
            $role->setDisplayBrowsing('Name');
            $role->setVisibleFrontdoor(1);
            $role->setDisplayFrontdoor('Name');
            $role->setVisibleOai(1);
            $role->setDisplayOai('Name');
            $role->store();

            $root = $role->addRootCollection()->setVisible(1);
            $root->store();
        }

    }

    // Import collections and series
    public function load_collections() {
        $input = readline('Do you want to import collections and series from OPUS3? (y/n) ');
        if ($input === 'y' || $input === 'yes') {
            $import = new Opus3CollectionsImport($this->importData);
        }

    }

    // Import faculties and institutes
    public function load_institutes() {
        $input = readline('Do you want to import faculties and institutes from OPUS3? (y/n) ');
        if ($input === 'y' || $input === 'yes') {
            $import = new Opus3InstituteImport($this->importData);
        }
    }

    // Import Licences
    public function load_licences() {
        $input = readline('Do you want to import licences from OPUS3? (y/n) ');
        if ($input === 'y' || $input === 'yes') {
            $import= new Opus3LicenceImport($this->importData);
        }
    }

    // Import Documents
    public function load_documents($start = null, $end = null) {
        $input = readline('Do you want to import metadata of documents from OPUS3? (y/n) ');
        if ($input === 'y' || $input === 'yes') {
            $import = new Opus3XMLImport($this->xslt, $this->stylesheet);
            $toImport = $import->initImportFile($this->importData);
            $logfile = '../workspace/tmp/importerrors.xml';

            // TODO: Add error handling to fopen()
            $f = fopen($logfile, 'w');
            $totalCount = 0;
            $successCount = 0;
            $failureCount = 0;

            $mem_now = round(memory_get_usage() / 1024 / 1024);
            $mem_peak = round(memory_get_peak_usage() / 1024 / 1024);

            foreach ($toImport as $document) {
                //echo "Memory amount: " . round(memory_get_usage() / 1024 / 1024, 2) . " (MB), peak memory " . round(memory_get_peak_usage() / 1024 / 1024, 2) . " (MB)\n";
                $totalCount++;
                if (!(is_null($start)) && ($totalCount < $start)) { continue; }
                if (!(is_null($end)) && ($totalCount > $end)) { break; }

                $result = $import->import($document);
                if ($result['result'] === 'success') {
                    //echo "Successfully imported old ID " . $result['oldid'] . " with new ID " . $result['newid'] . "\n";
                    echo "Successfully imported old ID " . $result['oldid'] . " with new ID " . $result['newid'] . " -- memory $mem_now MB, peak memory $mem_peak (MB)\n";
                    $import->log("Successfully imported old ID " . $result['oldid'] . " with new ID " . $result['newid'] . "\n");
                    //array_push($this->doclist, $result['newid']);
                    $successCount++;
                } else if ($result['result'] === 'failure') {
                    echo "ERROR: " . $result['message'] . " for old ID " . $result['oldid'] . "\n";
                    $import->log("ERROR: " . $result['message'] . " for old ID " . $result['oldid'] . "\n");
                    fputs($f, $result['entry'] . "\n");
                    $failureCount++;
                }
                flush();
            }
            fclose($f);
            $import->finalize();
            echo "Imported " . $successCount . " documents successfully.\n";
            echo $failureCount . " documents have not been imported due to failures listed above. See $logfile for details about failed entries.\n";
        }
    }

    // Import Fulltext-Files
    public function load_fulltext($path = null) {
        $this->fileinput = readline('Do you want to import the files from OPUS3? Note: this script needs to have direct physical reading access to the files in your OPUS3 directory tree! Import via HTTP is not possible! (y/n) ');
        $input = null;
        if ($this->fileinput  === 'y' || $this->fileinput  === 'yes') {
            do {
               while (false === file_exists($path)) {
                    $path = readline('Please type the path to your OPUS3 fulltext files (e.g. /usr/local/opus/htdocs/volltexte): ');
                }
                $this->path = $path;
                //$this->importFiles($this->doclist);
                $this->importFiles();
                $input = readline('Do you want to enter another fulltext path for files from another Opus3-area? (y/n) ');
                $path = null;
            } while ($input === 'y');
        }
    }


    private function setStylesheet() {
        $this->stylesheet = '../import/stylesheets';
        $this->xslt = 'opus3.xslt';
    }

    private function loadImportFile() {
        $importData = new DOMDocument;
        $importData->load($this->importfile);
        return $importData;
    }

    private function importFiles() {
        $fileImporter = new Opus3FileImport($this->path, $this->magicPath);
        $docList = Opus_Document::getAllIds();

        foreach ($docList as $id) {
            $doc = new Opus_Document($id);
            $numberOfFiles = $fileImporter->loadFiles($id);

            $mem_now = round(memory_get_usage() / 1024 / 1024);
            $mem_peak = round(memory_get_peak_usage() / 1024 / 1024);

            if ($numberOfFiles > 0) {
                echo $numberOfFiles . " file(s) have been imported successfully for document ID " . $doc->getId() . " -- memory $mem_now MB, peak memory $mem_peak (MB)\n";
            }
            unset ($doc);
            unset ($numberOfFiles);
        }
    }

    private function cleanup() {
        $filereader = opendir('../workspace/tmp/');
        while (false !== ($file = readdir($filereader))) {
            if (substr($file, -4) === '.map') {
                unlink('../workspace/tmp/' . $file);
            }
        }
    }


   /**
     * Migrates OPUS3 to OPUS4 using readline
     *
     * @return void
     */
    public function run() {

        echo "Start Opus3-Migration\n";

        // Load Opus3-mySQL-XML-dump
        $this->init();

        // Create Collection Roles
        $this->create_collection_roles();

        // Load Collections
        $this->load_collections();

        // Load Institutes
        $this->load_institutes();

        // Load Institutes
        $this->load_licences();

        // Load Institutes
        $this->load_documents();

        // Import files
        $this->load_fulltext();

        // Be Careful: cleanup will delete all Mapping Files for Institutes, Faculties etc.
        $this->cleanup();
    }
}

?>
