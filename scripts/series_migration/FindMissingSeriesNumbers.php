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
 * @author      Susanne Gottwald <gottwald@zib.de>
 * @copyright   Copyright (c) 2008-2012, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 **/

// Bootstrapping
require_once dirname(__FILE__) . '../common/bootstrap.php';

class FindMissingSeriesNumbers {

    private $logger;
    
    /**
     * Initialise the logger with the given file.
     */
    private function initLogger($logfileName) {
        $logfile = @fopen($logfileName, 'a', false);
        $writer = new Zend_Log_Writer_Stream($logfile);        
	$formatter=new Zend_Log_Formatter_Simple('%message%' . PHP_EOL);
	$writer->setFormatter($formatter);
        $this->logger = new Zend_Log($writer);        
    }
    
    /**
     * Prints a help message to the console.
     */
    private function printHelpMessage($argv) {
        echo "\nThis program helps with migrating the old series to the new concept.";
        echo "\nIt shows and logs in $argv[1] which series documents do not have a neccessary IdentifierSerial.\n";                       
    }

    /**
     * Runs a sql query on the DB.
     */
    private function query() {
        $query = 'SELECT document_id, collection_id AS series_id 
            FROM link_documents_collections 
            WHERE role_id = (SELECT id FROM collections_roles WHERE name = "series") 
            AND document_id NOT IN (SELECT document_id FROM document_identifiers WHERE type = "serial");';
        $db = Zend_Db_Table::getDefaultAdapter();
        $result = $db->fetchAssoc($query);
       
        return $result;
    }

    /** 
     * Main method.
     */
    public function run() {
        global $argc, $argv;
        $this->initLogger($argv[1]);

        $this->logger->info('script ' . __FILE__ . ' started.');
        
        $this->printHelpMessage($argv);        
        
        $queryresult = $this->query(); 
        
        if (!is_null($queryresult)) {
            $this->logger->info(count($queryresult) . ' documents do not have an IdentifierSerial.');
            $this->logger->info('');
            
            foreach ($queryresult AS $row) {
                $this->logger->info('document_id: ' . $row['document_id'] . ' -> series_id: ' . $row['series_id']);
            }
        }
        
        return $queryresult;
    }
}

$numbers = new FindMissingSeriesNumbers;
$result = $numbers->run();
if (!is_null($result)) {
	$count = count($result);
        if ($count > 0) {
            echo "\n$count documents do not have an IdentiferSerial.";
            echo "\nMore information can be found in the log file $argv[1]\n\n";
        }
        else { 
            echo "\nNo problems found.\n";
	}
}

