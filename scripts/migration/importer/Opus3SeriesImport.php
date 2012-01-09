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
 * @author      Gunar Maiwald <maiwald@zib.de>
 * @copyright   Copyright (c) 2012, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id: Opus3SeriesImport.php 9682 2012-01-06 12:06:34Z gmaiwald $
 */

require_once 'Opus3ImportLogger.php';

class Opus3SeriesImport {

   /**
    * Holds Zend-Configurationfile
    *
    * @var file
    */
    protected $config = null;

   /**
    * Holds Logger
    *
    * @var file
    */
    protected $logger = null;

   /**
    * Holds the complete data to import in XML
    *
    * @var xml-structure
    */
    protected $data = null;

    /**
     * Imports Series data to Opus4
     *
     * @param Strring $data XML-String with data to be imported
     */
    public function __construct($data) {

        $this->config = Zend_Registry::get('Zend_Config');
        $this->logger = new Opus3ImportLogger();
        $this->data = $data;

    }
    
    /**
     * Public Method for import of Sers
     *
     * @param void
     * @return void
     *
     */

    public function start() {

        $tables = $this->data->getElementsByTagName('table_data');
	foreach ($tables as $table)	{
            if ($table->getAttribute('name') === 'schriftenreihen') {
                $this->importSeries($table);
            }
	}
    }

    /**
     * Finalisation of Object
     *
     * @param void
     * @return void
     *
     */
    public function finalize() {
        $this->logger->finalize();
    }    

 


    /**
     * Imports Series from Opus3 to Opus4
     *
     * @param DOMDocument $data XML-Document to be imported
     * @return array List of documents that have been imported
     */
    protected function importSeries($data) {
        $mf = $this->config->import->mapping->series;
        $fp = null;
        try {
            $fp = @fopen($mf, 'w');
            if (!$fp) {
                throw new Exception("Could not create '" . $mf . "' for Series");
            }
        } catch (Exception $e){
            $this->logger->log_error("Opus3SeriesImport", $e->getMessage());
            return;
        }
             
        $classification = $this->transferOpusClassification($data);
        foreach ($classification as $class) {
            if (array_key_exists('name', $class) === false) { continue; }
            if (array_key_exists('sr_id', $class) === false) { continue; }
            //echo ".";
            $series = new Opus_Series();
            $series->setTitle($class['name']);
            $series->store();

            $this->logger->log_debug("Opus3SeriesImport","Series imported: " . $class['name']);

            fputs($fp, $class['sr_id'] . ' ' . $series->getId() . "\n");
        }
	fclose($fp);
    }

    /**
     * transfers any OPUS3-conform classification System into an array
     *
     * @param DOMDocument $data XML-Document to be imported
     * @return array List of documents that have been imported
     */
    protected function transferOpusClassification($data) {
	$classification = array();
	$doclist = $data->getElementsByTagName('row');
	$index = 0;
	foreach ($doclist as $document)	{
            $classification[$index] = array();
            foreach ($document->getElementsByTagName('field') as $field) {
           	$classification[$index][$field->getAttribute('name')] = $field->nodeValue;
            }
            $index++;
	}
	return $classification;
    }
}
