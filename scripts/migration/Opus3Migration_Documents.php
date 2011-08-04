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
 * @copyright   Copyright (c) 2009-2011, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

// Configure include path.
set_include_path('.' . PATH_SEPARATOR
        . PATH_SEPARATOR . dirname(dirname(dirname(__FILE__))) . '/import/importer'
        . PATH_SEPARATOR . dirname(dirname(dirname(__FILE__))) . '/import/stylesheets'
        . PATH_SEPARATOR . dirname(dirname(dirname(__FILE__))) . '/library'
        . PATH_SEPARATOR . dirname(dirname(dirname(__FILE__))) . '/modules/import'
        . PATH_SEPARATOR . dirname(dirname(dirname(__FILE__))) . '/modules'
        . PATH_SEPARATOR . get_include_path());

// Define path to application directory
defined('APPLICATION_PATH')
       || define('APPLICATION_PATH', realpath(dirname(dirname(dirname(__FILE__)))));

define('APPLICATION_ENV', 'testing');

require_once 'Zend/Application.php';
require_once 'Opus3XMLImport.php';
require_once 'Opus3FileImport.php';
require_once 'Opus3ImportLogger.php';

class Opus3Migration_Documents {

    private $logger = null;

    private $importFile;
    private $importData;
    private $stylesheet;
    private $fulltextPath;
    private $xslt;
    private $start = null;
    private $end = null;
    private $doclist = array();
    private $role = array();

    private $status;
    
    //CONST _ERROR = "-1";
    CONST _INIT = "-1";
    CONST _FINISH = "0";
    CONST _BREAK = "1";

    /**
     * Constructur.
     *
     * @param array $options Array with input options.
     */
    function __construct($options) {
         $this->logger = new Opus3ImportLogger();

        if (array_key_exists('f', $options) !== false) { $this->importFile = $options["f"]; }
        if (array_key_exists('p', $options) !== false) { $this->fulltextPath = $options["p"]; }
        if (array_key_exists('s', $options) !== false) { $this->start = $options["s"]; }
        if (array_key_exists('e', $options) !== false) { $this->end  = $options["e"]; }
    }

    // Set XMl-Dump-Import-File
    public function init($file = null, $path = null, $start = null, $end = null) {

        $this->status = self::_INIT;

        $this->setStylesheet();
        while (false === file_exists($file)) {
            $file= readline('Please type the path to your OPUS3 database export file (a dumpfile of the database in XML format e.g. /usr/local/opus/complete_database.xml): ');
        }
        $this->importFile = $file;
        $this->loadImportFile();

        while (false === file_exists($path)) {
            $path = readline('Please type the path to your OPUS3 fulltext files (e.g. /usr/local/opus/htdocs/volltexte): ');
        }
        $this->fulltextPath = rtrim($path,"/");

        while (false === is_numeric($start)) {
            $start = readline('Please type the number of the first document to import (e.g. 1) : ');
        }
        $this->start = $start;

        while (false === is_numeric($end)) {
            $end = readline('Please type the number of the last document to import (e.g. 50) : ');
        }
        $this->end = $end;
    }

   // Import Documents
    public function load_documents() {
        $xmlImporter = new Opus3XMLImport($this->xslt, $this->stylesheet);
        $toImport = $xmlImporter->initImportFile($this->importData);
        $totalCount = 0;

        $this->status = self::_FINISH;

        foreach ($toImport as $document) {
            $mem_now = round(memory_get_usage() / 1024 );
            $mem_peak = round(memory_get_peak_usage() / 1024);

            $totalCount++;

            if (!(is_null($this->start)) && ($totalCount < $this->start)) { continue; }
            if (!(is_null($this->end)) && ($totalCount > $this->end)) {
                $this->status = self::_BREAK;
                break;
            }

            $result = $xmlImporter->import($document);
            if ($result['result'] === 'success') {
                $this->logger->log_debug("Opus3Migration_Documents", "Successfully imported old ID " . $result['oldid'] . " with new ID " . $result['newid'] . " -- memory $mem_now (KB), peak memory $mem_peak (KB)");
                array_push($this->doclist, $result['newid']);
                if (array_key_exists('roleid', $result))  {
                    $this->role[$result['newid']] = $result['roleid'];
                }
            } else if ($result['result'] === 'failure') {
                $this->logger->log_error("Opus3Migration_Documents", $result['message'] . " for old ID " . $result['oldid'] . "\n" . $result['entry']);
            }
        }

        $xmlImporter->finalize();
   }

    public function load_fulltext() {

        $fileImporter = new Opus3FileImport($this->fulltextPath);
 
        foreach ($this->doclist as $id) {

            $mem_now = round(memory_get_usage() / 1024 );
            $mem_peak = round(memory_get_peak_usage() / 1024);

            if (array_key_exists($id, $this->role)) {
                $numberOfFiles = $fileImporter->loadFiles($id, $this->role[$id]);
            } else {
                $numberOfFiles = $fileImporter->loadFiles($id);
            }

            $mem_now = round(memory_get_usage() / 1024 );
            $mem_peak = round(memory_get_peak_usage() / 1024 );

            if ($numberOfFiles > 0) {
                $this->logger->log_debug("Opus3Migration_Documents", $numberOfFiles . " file(s) have been imported successfully for document ID " . $id . " -- memory $mem_now (KB), peak memory $mem_peak (KB)");
            }
        }

        $fileImporter->finalize();
    }

    private function setStylesheet() {
        $this->stylesheet = '../../import/stylesheets';
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
        $this->init($this->importFile, $this->fulltextPath, $this->start, $this->end);

         // Load Metadata
        $status = $this->load_documents();

        // Load Fulltext
        $this->load_fulltext();

        return $this->status;

    }
}

// Bootstrap application.
$application = new Zend_Application(
    APPLICATION_ENV,
    array(
        "config"=>array(
            APPLICATION_PATH . '/application/configs/application.ini',
            APPLICATION_PATH . '/application/configs/config.ini',
            APPLICATION_PATH . '/application/configs/import.ini'
        )
    )
);
$application->bootstrap(array('Configuration', 'Logging', 'Database'));

$options = getopt("f:p:s:e:");

// Start Opus3Migration
$migration = new Opus3Migration_Documents($options);
$status = $migration->run();

if ($status === Opus3Migration_Documents::_BREAK) { exit(1); }

