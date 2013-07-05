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
 * @category    TODO
 * @author      Gunar Maiwald <maiwald@zib.de>
 * @copyright   Copyright (c) 2008-2012, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

// Configure include path
require_once dirname(__FILE__) . '/../common/bootstrap.php';

set_include_path('.' . PATH_SEPARATOR
        . PATH_SEPARATOR . dirname(dirname(dirname(__FILE__))) . '/scripts/migration/importer'
        . PATH_SEPARATOR . get_include_path());

require_once 'Opus3ImportLogger.php';

class Opus3Migration_Validation {

    private $logger;
    private $importFile;
    private $type;
    private $config;

    function __construct($options) {
	$this->config = Zend_Registry::get('Zend_Config');
        if (isset($this->config->migration->file)) {
            $this->importFile = $this->config->migration->file;
        }
        $this->logger = new Opus3ImportLogger();
    }
    
    public function run() {
	$this->validateImportFile();
	$this->checkConsistencyOfImportFile();
    }

    private function validateImportFile() {
    	print "Validation of Opus3-XML-Dumpfile\n";
        libxml_use_internal_errors(true);
	$file = file_get_contents($this->importFile, true);
        $xml = simplexml_load_string($file);
        $xmlstr = explode("\n", $file);

        if (count(libxml_get_errors()) > 0) {
            foreach (libxml_get_errors() as $error) {
                $this->displayErrorLine($xmlstr[$error->line - 1], $error);
            }
            libxml_clear_errors();
            throw new Exception("XML-Dump-File is not well-formed.");
        }
    }

    public function getStatus() {
        return $this->status;
    }

    public function log_error($string) {
        $this->logger->log_error("Opus3Migration_Validation", $string);
    }
    
    private function checkConsistencyOfImportFile() {
	print "Validation of Consistency of Opus3-XML-Dumpfile\n";
	$xml = new DOMDocument;
	$xml->load($this->importFile);

	$xsl = new DOMDocument;
	$xsl->load('stylesheets/check.xslt');

	$proc = new XSLTProcessor;
	$proc->importStyleSheet($xsl);
	
	$result = $proc->transformToXML($xml);
        if (strlen($result) > 0) {
	    echo $result;
            throw new Exception("XML-Dump-File is not consistent.");
        }	
    }
    

    private function displayErrorLine($line, $error) {
        echo "\n" . $this->importFile .": " . $error->line ."(" . $error->column .") : ". $error->message;
	if ($error->column <= 50) {
		echo substr($line, 0, 80)."\n";
	} else {
		echo substr($line, $error->column-40, 80)."\n";
	}
    }
}
