<?php

/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Opus3Migration_Base
 *
 * @author gunar
 */

require_once 'OpusMigrationBase.php';
require_once 'import/models/Opus3CollectionsImport.php';
require_once 'import/models/Opus3InstituteImport.php';
require_once 'import/models/Opus3LicenceImport.php';
require_once 'import/models/Opus3XMLImport.php';

class Opus3Migration_Readline extends OpusMigrationBase {

    private $importData;
    private $fileinput;
    private $doclist = array();

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

    // Import collections and series
    public function load_collections() {
        $input = readline('Do you want to import collections and series from OPUS3? (y/n) ');
        if ($input === 'y' || $input === 'yes') {
            $import = new Import_Model_Opus3CollectionsImport($this->importData);
        }

    }

    // Import faculties and institutes
    public function load_institutes() {
        $input = readline('Do you want to import faculties and institutes from OPUS3? (y/n) ');
        if ($input === 'y' || $input === 'yes') {
            $import = new Import_Model_Opus3InstituteImport($this->importData);
        }
    }

    // Import Licences
    public function load_licences() {
        $input = readline('Do you want to import licences from OPUS3? (y/n) ');
        if ($input === 'y' || $input === 'yes') {
            $import= new Import_Model_Opus3LicenceImport($this->importData);
        }
    }

    // Import Documents
    public function load_documents($start = null, $end = null) {
        $input = readline('Do you want to import metadata of documents from OPUS3? (y/n) ');
        if ($input === 'y' || $input === 'yes') {
            $import = new Import_Model_Opus3XMLImport($this->xslt, $this->stylesheet);
            $toImport = $import->initImportFile($this->importData);
            $logfile = '../workspace/tmp/importerrors.xml';

            // TODO: Add error handling to fopen()
            $f = fopen($logfile, 'w');
            $totalCount = 0;
            $successCount = 0;
            $failureCount = 0;

            foreach ($toImport as $document) {
                //echo "Memory amount: " . round(memory_get_usage() / 1024 / 1024, 2) . " (MB), peak memory " . round(memory_get_peak_usage() / 1024 / 1024, 2) . " (MB)\n";
                $totalCount++;
                if (!(is_null($start)) && ($totalCount < $start)) { continue; }
                if (!(is_null($end)) && ($totalCount > $end)) { break; }

                $result = $import->import($document);
                if ($result['result'] === 'success') {
                    echo "Successfully imported old ID " . $result['oldid'] . " with new ID " . $result['newid'] . "\n";
                    $import->log("Successfully imported old ID " . $result['oldid'] . " with new ID " . $result['newid'] . "\n");
                    array_push($this->doclist, $result['newid']);
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
                echo "Please specify the access rights for this fulltext path (Opus3 ranges)!\n";
                $ipStart = readline('The IP-range starts at (e.g. 192.168.1.1): ');
                $ipEnd = readline('The IP-range ends at (e.g. 192.168.1.10): ');
                $this->path = $path;
                $this->importFiles($ipStart, $ipEnd, $this->doclist);
                $input = readline('Do you want to enter another fulltext path for files from another Opus3-area? (y/n) ');
                $path = null;
            } while ($input === 'y');
        }
    }

    // Import signatures
    public function load_signatures() {
        $input = readline('If you used signatures (GPG-Extension) in OPUS 3.x, do you want the signatures to be imported? (y/n) ');
        if ($input === 'y' || $input === 'yes') {
            $signaturePath = '';
            while (false === file_exists($signaturePath)) {
                $signaturePath = readline('Please type the path to your OPUS3 signature files (e.g. /usr/local/opus/htdocs/signatures): ');
            }
            $this->signaturePath = $signaturePath;
            $this->importSignatures();
        }
    }

    // Signing publications is only possible if files have been imported
    public function sign_publications() {
        if ($this->fileinput  === 'y' || $this->fileinput  === 'yes') {
            $input = readline('Do you want files to get signed automatically? (You need to have an internal key already) (y/n) ');
            if ($input === 'y' || $input === 'yes') {
                $newsigpass = readline('Please type the password for your signature key: ');
                echo "Signing publications ";
                $this->autosign($newsigpass);
                echo "finished!\n";
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
        //$this->init();
        $this->init('/home/gunar/opus4/dumps/opuszib_20100920.xml');

        // Load Collections
        $this->load_collections();

        // Load Institutes
        $this->load_institutes();

        // Load Institutes
        $this->load_licences();

        // Load Institutes
        $this->load_documents('100', '105');

        // Import files
        //$this->load_fulltext();
        $this->load_fulltext('/home/gunar/opus4/volltexte');

        // Import Signatures
        $this->load_signatures();

        // Sign Files
        $this->sign_publications();

        // Be Careful: cleanup will delete all Mapping Files for Institutes, Faculties etc.
        //$this->cleanup();
    }
}

?>
