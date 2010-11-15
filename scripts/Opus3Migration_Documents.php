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
 * @version     $Id: Opus3Migration_Readline.php 7086 2010-11-12 16:40:52Z gmaiwald $
 */

// Configure include path.
set_include_path('.' . PATH_SEPARATOR
        . PATH_SEPARATOR . dirname(dirname(__FILE__)) . '/import/importer'
        . PATH_SEPARATOR . dirname(dirname(__FILE__)) . '/import/stylesheets'
        . PATH_SEPARATOR . dirname(dirname(__FILE__)) . '/library'
        . PATH_SEPARATOR . dirname(dirname(__FILE__)) . '/modules/import'
        . PATH_SEPARATOR . dirname(dirname(__FILE__)) . '/modules'
        . PATH_SEPARATOR . get_include_path());

// Define path to application directory
defined('APPLICATION_PATH')
       || define('APPLICATION_PATH', realpath(dirname(dirname(__FILE__))));

define('APPLICATION_ENV', 'testing');

require_once 'Zend/Application.php';
require_once 'Opus3XMLImport.php';
require_once 'Opus3FileImport.php';

class Opus3Migration_Documents {

    private $importFile;
    private $importData;
    private $stylesheet;
    private $xslt;
    private $start = null;
    private $end = null;
    private $doclist = array();
    private $magicPath = '/usr/share/file/magic'; # on Ubuntu-Systems this should be the magic path

    /**
     * Constructur.
     *
     * @param array $options Array with input options.
     */
    function __construct($options) {
        if (array_key_exists('f', $options) !== false) { $this->importFile = $options["f"]; }
        if (array_key_exists('p', $options) !== false) { $this->fulltextPath = $options["p"]; }
        if (array_key_exists('s', $options) !== false) { $this->starth = $options["s"]; }
        if (array_key_exists('e', $options) !== false) { $this->end  = $options["e"]; }
    }

    // Set XMl-Dump-Import-File
    public function init($file = null, $path = null, $start= null, $end = null) {
        $this->setStylesheet();
        while (false === file_exists($file)) {
            $file= readline('Please type the path to your OPUS3 database export file (a dumpfile of the database in XML format e.g. /usr/local/opus/complete_database.xml): ');
        }
        $this->importFile = $file;
        $this->loadImportFile();

        while (false === file_exists($path)) {
            $path = readline('Please type the path to your OPUS3 fulltext files (e.g. /usr/local/opus/htdocs/volltexte): ');
        }
        $this->fulltextPath = $path;
        $this->loadImportFile();
    }

   // Import Documents
    public function load_documents() {

        $xmlImporter = new Opus3XMLImport($this->xslt, $this->stylesheet);
        $toImport = $xmlImporter->initImportFile($this->importData);
        $logfile = '../workspace/tmp/importerrors.xml';

        // TODO: Add error handling to fopen()
        $f = fopen($logfile, 'w');
        $totalCount = 0;
        $successCount = 0;
        $failureCount = 0;

        foreach ($toImport as $document) {
            //echo "Memory amount: " . round(memory_get_usage() / 1024 / 1024, 2) . " (MB), peak memory " . round(memory_get_peak_usage() / 1024 / 1024, 2) . " (MB)\n";
            $mem_now = round(memory_get_usage() / 1024 );
            $mem_peak = round(memory_get_peak_usage() / 1024);

            $totalCount++;
            if (!(is_null($this->start)) && ($totalCount < $this->start)) { continue; }
            if (!(is_null($this->end)) && ($totalCount > $this->end)) { break; }

            $result = $xmlImporter->import($document);
            if ($result['result'] === 'success') {
                //echo "Successfully imported old ID " . $result['oldid'] . " with new ID " . $result['newid'] . "\n";
                echo "Successfully imported old ID " . $result['oldid'] . " with new ID " . $result['newid'] . " -- memory $mem_now (KB), peak memory $mem_peak (KB)\n";
                //$xmlImporter->log("Successfully imported old ID " . $result['oldid'] . " with new ID " . $result['newid'] . "\n");
                array_push($this->doclist, $result['newid']);
                $successCount++;
            } else if ($result['result'] === 'failure') {
                echo "ERROR: " . $result['message'] . " for old ID " . $result['oldid'] . "\n";
                //$xmlImporter->log("ERROR: " . $result['message'] . " for old ID " . $result['oldid'] . "\n");
                fputs($f, $result['entry'] . "\n");
                $failureCount++;
            }
            flush();
        }
        fclose($f);
        $xmlImporter->finalize();

        echo "Imported " . $successCount . " documents successfully.\n";
        echo $failureCount . " documents have not been imported due to failures listed above. See $logfile for details about failed entries.\n";

    }

    public function load_fulltext() {

        $fileImporter = new Opus3FileImport($this->fulltextPath, $this->magicPath);

 
        foreach ($this->doclist as $id) {

            $mem_now = round(memory_get_usage() / 1024 );
            $mem_peak = round(memory_get_peak_usage() / 1024);

            $doc = new Opus_Document($id);
            $numberOfFiles = $fileImporter->loadFiles($id);

            $mem_now = round(memory_get_usage() / 1024 );
            $mem_peak = round(memory_get_peak_usage() / 1024 );

            if ($numberOfFiles > 0) {
                echo $numberOfFiles . " file(s) have been imported successfully for document ID " . $doc->getId() . " -- memory $mem_now (KB), peak memory $mem_peak (KB)\n";
            }
        }
    }

    private function setStylesheet() {
        $this->stylesheet = '../import/stylesheets';
        $this->xslt = 'opus3.xslt';
    }

    private function loadImportFile() {
        $this->importData = new DOMDocument;
        $this->importData->load($this->importFile);
    }

   /**
     * Migrates OPUS3 to OPUS4 using readline
     *
     * @return void
     */
    public function run() {

        // Load Opus3-mySQL-XML-dump
        $this->init($this->importFile, $this->fulltextPath);

         // Load Metadata
        $this->load_documents();

        // Load Fulltext
        $this->load_fulltext();
    }
}

// Bootstrap application.
$application = new Zend_Application(
    APPLICATION_ENV,
    array(
        "config"=>array(
            APPLICATION_PATH . '/application/configs/application.ini',
            APPLICATION_PATH . '/application/configs/config.ini'
        )
    )
);
$application->bootstrap(array('Configuration', 'Logging', 'Database'));

$options = getopt("f:p:s:e:");

// Start Opus3Migration
$migration = new Opus3Migration_Documents($options);
$migration->run();


