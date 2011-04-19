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
require_once 'ZIBBibtexImport.php';

class ZIBImport_BibTeX {

    private $importFile;
    private $importData;

    private $xslt;
    private $stylesheet;

    private $start = null;
    private $end = null;

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
        if (array_key_exists('f', $options) !== false) { $this->importFile = $options["f"]; }
        if (array_key_exists('s', $options) !== false) { $this->start = $options["s"]; }
        if (array_key_exists('e', $options) !== false) { $this->end  = $options["e"]; }
    }


    // Set XMl-Dump-Import-File
    public function init($file = null, $start = null, $end = null) {

        $this->status = self::_INIT;

        while (false === file_exists($file)) {
            $file= readline('Please type the path to your OPUS3 database export file (a dumpfile of the database in XML format e.g. /usr/local/opus/bibtex.xml): ');
        }
        $this->importFile = $file;

        while (false === is_numeric($start)) {
            $start = readline('Please type the number of the first document to import (e.g. 1) : ');
        }
        $this->start = $start;

        while (false === is_numeric($end)) {
            $end = readline('Please type the number of the last document to import (e.g. 50) : ');
        }
        $this->end = $end;

        $this->loadImportFile();
        $this->setStylesheet();
    }

    // Set XMl-Dump-Import-File
    // Import collections and series
    public function import_bibtex() {

        $bibteximporter = new ZIBBibtexImport($this->xslt, $this->stylesheet, $this->importFile);
        $toImport = $bibteximporter->initImportFile($this->importData);
	$bibteximporter->initZRTitleArray();
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

            $result = $bibteximporter->import($document);
            if ($result['result'] === 'success') {
                //echo date('Y-m-d H:i:s') . " Successfully imported old ID " . $result['oldid'] . " with new ID " . $result['newid'] . " -- memory $mem_now (KB), peak memory $mem_peak (KB)\n";
                //$bibteximporter->log(date('Y-m-d H:i:s') . " Successfully imported old ID " . $result['oldid'] . " with new ID " . $result['newid'] . " -- memory $mem_now (KB), peak memory $mem_peak (KB)\n");
                echo "Successfully imported old ID " . $result['oldid'] . " with new ID " . $result['newid'] . "\n";
	    } else if ($result['result'] === 'failure') {
                echo "ERROR: " . $result['message'] . " for old ID " . $result['oldid'] . "\n";
                //$bibteximporter->log(date('Y-m-d H:i:s') . " ERROR: " . $result['message'] . " for old ID " . $result['oldid'] . "\n" . $result['entry'] . "\n");
            }
        }
        $bibteximporter->finalize();
    }

    private function loadImportFile() {
        $this->importData = new DOMDocument;
        $this->importData->load($this->importFile);
    }


    private function setStylesheet() {
        
        $filename = $this->importFile;
        $filename = preg_replace('/.*\//', '', $filename);
        $filename = preg_replace('/\..*/', '', $filename);
        $filename = strtolower($filename);

        $this->stylesheet = '../import/stylesheets';

        if (true === file_exists($this->stylesheet . '/' . 'zib_bibtex_' . $filename. '.xslt')) {
            $this->xslt = 'zib_bibtex_' . $filename. '.xslt';
        }
        else {
            $this->xslt = 'zib_bibtex.xslt';
        }
    }



    
   /**
     * Migrates OPUS3 to OPUS4 using readline
     *
     * @return void
     */
    public function run() {

        // Initialisierung
        $this->init($this->importFile, $this->start, $this->end);

        // Import BibTex-File
        $this->import_bibtex();
	//$bibteximporter = new ZIBBibtexImport($this->xslt, $this->stylesheet, $this->importFile);
        //$toImport = $bibteximporter->initImportFile($this->importData);
	//$bibteximporter->initZRTitleArray(); 

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

$options = getopt("f:s:e:");

// Start Opus3Migration
$import = new ZIBImport_BibTeX($options);
$import->run();


