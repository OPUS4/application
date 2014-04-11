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
 * @author      Oliver Marahrens <o.marahrens@tu-harburg.de>
 * @author      Gunar Maiwald <maiwald@zib.de>
 * @copyright   Copyright (c) 2009, 2010 OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class Opus3LicenceImport {

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
     * Holds the Mappings from ConfigFile
     *
     * @var Array
     */
    protected $mapping = array();

   /**
    * Holds Maximum Sortorder
    *
    */
    protected $maxSortOrder = 1;

   /**
    * Holds the complete data to import in XML
    *
    * @var xml-structure
    */
    protected $data = null;


    /**
     * Imports licenses data to Opus4
     *
     * @param Strring $data XML-String with classifications to be imported
     * @return array List of documents that have been imported
     */
    public function __construct($data) {
        $this->config = Zend_Registry::get('Zend_Config');
        $this->logger = Zend_Registry::get('Zend_Log');
        $this->mapping['language'] =  array('old' => 'OldLanguage', 'new' => 'Language', 'config' => $this->config->migration->language);
        $this->data = $data;

        foreach (Opus_Licence::getAll() as $lic) {
            if ($lic->getSortOrder() > $this->maxSortOrder) {
                $this->maxSortOrder = $lic->getSortOrder();
            }
        }
    }

    /**
     * Public Method for import of Licenses
     *
     * @param void
     * @return void
     *
     */

    public function start() {
	$doclist = $this->data->getElementsByTagName('table_data');
	foreach ($doclist as $document) {
            if ($document->getAttribute('name') === 'license_de') {
                $this->readLicenses($document);
            }
        }
    }


    /**
     * transfers any OPUS3-conform classification System into an array
     *
     * @param DOMDocument $data XML-Document to be imported
     * @return array List of documents that have been imported
     */


    protected function transferOpus3Licence($data) {
    	//$classification = array();
	$doclist = $data->getElementsByTagName('row');
        $licenses = array();
	foreach ($doclist as $document) {
            $lic = new Opus_Licence();
            $shortname = "";
            foreach ($document->getElementsByTagName('field') as $field) {
                if ($field->nodeValue === '') { continue; }
                if ($field->getAttribute('name') === 'active') $lic->setActive($field->nodeValue);
                if ($field->getAttribute('name') === 'comment') $lic->setCommentInternal($field->nodeValue);
                if ($field->getAttribute('name') === 'desc_html') $lic->setDescMarkup($field->nodeValue);
                if ($field->getAttribute('name') === 'desc_text') $lic->setDescText(html_entity_decode($field->nodeValue, ENT_COMPAT, 'UTF-8'));
                if ($field->getAttribute('name') === 'language') $lic->setLanguage($this->mapLanguage($field->nodeValue));
                if ($field->getAttribute('name') === 'link') $lic->setLinkLicence($field->nodeValue);
                if ($field->getAttribute('name') === 'logo') $lic->setLinkLogo($field->nodeValue);
                if ($field->getAttribute('name') === 'link_tosign') $lic->setLinkSign($field->nodeValue);
                if ($field->getAttribute('name') === 'mime_type')  $lic->setMimeType($field->nodeValue); 
                if ($field->getAttribute('name') === 'longname') $lic->setNameLong(html_entity_decode($field->nodeValue, ENT_COMPAT, 'UTF-8'));
                if ($field->getAttribute('name') === 'pod_allowed') $lic->setPodAllowed($field->nodeValue);
                //if ($field->getAttribute('name') === 'sort') $lic->setSortOrder($field->nodeValue);
           	if ($field->getAttribute('name') === 'shortname') $shortname = $field->nodeValue;
            }

            $this->checkMandatoryFields($lic, $shortname);

            $this->maxSortOrder++;
            $lic->setSortOrder($this->maxSortOrder);

            $licenses[$shortname] = $lic;
	}
	return $licenses;
    }

    /**
     * Map Languages from Opus3-Notation to Opus4-Notation
     *
     * @param Opus3-Language-String
     * @return Opus4-Language-String
     */
    private function mapLanguage($lang) {
        return $this->config->migration->language->$lang;
    }


    /**
     * reates a mapping file from old licence identifiers to the ones in Opus4
     *
     * @param DOMDocument $data XML-Document to be imported
     * @return array List of documents that have been imported
     */
    protected function readLicenses($data) {

        $mf = $this->config->migration->mapping->licences;
        $fp = null;
        try {
            $fp = @fopen($mf, 'w');
            if (!$fp) {
                throw new Exception("Could not create '".$mf."' for Licences");
            }
        } catch (Exception $e){
            $this->logger->log($e->getMessage(), Zend_Log::ERR);
            return;
        }
        $licenses = $this->transferOpus3Licence($data);
	foreach ($licenses as $key => $licence) {
            
            $id = $licence->store();

            $this->logger->log("Licence imported: " . $key, Zend_Log::DEBUG);
            fputs($fp, $key . ' ' . $id . "\n");
	}
	fclose($fp);
    }

     protected function checkMandatoryFields($lic, $name) {
         if (is_null($lic->getActive())) {
             $this->logger->log("No Attribute 'active' for " . $name, Zend_Log::ERR);
             if (!is_null($this->config->migration->licence->active)) {
                $lic->setActive($this->config->migration->licence->active);
                $this->logger->log_error("Set Attribute 'active' to default value '" . $lic->getActive() ."' for " .$name );
             }
         }

         if (is_null($lic->getLanguage())) {
             $this->logger->log("No Attribute 'language' for " . $name, Zend_Log::ERR);
             if (!is_null($this->config->migration->licence->language)) {
                $lic->setLanguage($this->config->migration->licence->language);
                $this->logger->log("Set Attribute 'language' to default value '" .
                    $lic->getLanguage() . "' for " .$name, Zend_Log::ERR);
             }
         }

         if (is_null($lic->getLinkLicence())) {
             $this->logger->log("No Attribute 'link_licence' for " . $name, Zend_Log::ERR);
             if (!is_null($this->config->migration->licence->link_licence)) {
                $lic->setLinkLicence($this->config->migration->licence->link_licence);
                $this->logger->log("Set Attribute 'link_licence' to default value '" .
                    $lic->getLinkLicence() ."' for " .$name, Zend_Log::ERR);
             }
         }

         if (is_null($lic->getMimeType())) {
             $this->logger->log("No Attribute 'mime_type' for " . $name, Zend_Log::ERR);
             if (!is_null($this->config->migration->licence->mime_type)) {
                $lic->setMimeType($this->config->migration->licence->mime_type);
                $this->logger->log("Set Attribute 'mime_type' to default value '" .
                    $lic->getMimeType() ."' for " .$name, Zend_Log::ERR);
             }
         }

         if (is_null($lic->getNameLong())) {
             $this->logger->log("No Attribute 'name_long' for " . $name, Zend_Log::ERR);
             if (!is_null($this->config->migration->licence->name_long)) {
                $lic->setNameLong($this->config->migration->licence->name_long);
                $this->logger->log("Set Attribute 'name_long' to default value '" .
                    $lic->getNameLong() ."' for " .$name, Zend_Log::ERR);
             }
         }

         if (is_null($lic->getPodAllowed())) {
             $this->logger->log("No Attribute 'pod_allowed' for " . $name, Zend_Log::ERR);
             if (!is_null($this->config->migration->licence->pod_allowed)) {
                $lic->setPodAllowed($this->config->migration->licence->pod_allowed);
                $this->logger->log("Set Attribute 'pod_allowed' to default value '" .
                    $lic->getPodAllowed() ."' for " .$name, Zend_Log::ERR);
             }
         }

     }

}