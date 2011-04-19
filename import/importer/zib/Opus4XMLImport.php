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
 * @copyright   Copyright (c) 2009, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id: Opus3XMLImport.php -1   $
 */
class Opus4XMLImport {
    
   /**
    * Holds Zend-Configurationfile
    *
    * @var file.
    */

    protected $config = null;

    /**
     * Holds xml representation of document information to be processed.
     *
     * @var DomDocument  Defaults to null.
     */
    protected $_xml = null;
    /**
     * Holds the stylesheet for the transformation.
     *
     * @var DomDocument  Defaults to null.
     */
    protected $_xslt = null;
    /**
     * Holds the xslt processor.
     *
     * @var DomDocument  Defaults to null.
     */
    protected $_proc = null;
    /**
     * Holds the document that should get imported
     *
     * @var DomNode  XML-Representation of the document to import
     */
    protected $document = null;

    /**
     * Holds the complete XML-Representation of the Importfile
     *
     * @var DomDocument  XML-Representation of the importfile
     */
    protected $completeXML = null;

    /**
     * Holds the logfile for Importer
     *
     * @var string  Path to logfile
     */
    protected $logfile = null;
    /**
     * Holds the filehandle of the logfile
     *
     * @var file  Fileandle logfile
     */
    protected $_logfile;

    /**
     * Do some initialization on startup of every action
     *
     * @param string $xslt Filename of the stylesheet to be used
     * @param string $stylesheetPath Path to the stylesheet
     * @return void
     */
    public function __construct($xslt, $stylesheetPath) {
        // Initialize member variables.
        $this->config = Zend_Registry::get('Zend_Config');
        $this->_xml = new DomDocument;
        $this->_xslt = new DomDocument;
        $this->_xslt->load($stylesheetPath . '/' . $xslt);
        $this->_proc = new XSLTProcessor;
        $this->_proc->registerPhpFunctions();
        $this->_proc->importStyleSheet($this->_xslt);
        $this->logfile = $this->config->import->logfile;

        try {
            $this->_logfile= @fopen($this->logfile, 'a');
            if (!$this->_logfile) {
                throw new Exception("ERROR Opus4XMLImport: Could not create '".$this->logfile."'\n");
            }
        } catch (Exception $e){
            echo $e->getMessage();
        }
    }

    public function log($string) {
        echo $string;
        fputs($this->_logfile, $string);
    }

    public function finalize() {
        fclose($this->_logfile);
    }

    public function initImportFile($data) {
        $this->completeXML = new DOMDocument;
        $this->completeXML->loadXML($this->_proc->transformToXml($data));
        $doclist = $this->completeXML->getElementsByTagName('Opus_Document');
        return $doclist;
    }

    /**
     * Imports metadata from an XML-Document
     *
     * @param DOMDocument $data XML-Document to be imported
     * @return array information about the document that has been imported
     */
    public function import($document) {

        $this->document = $document;
	
	//echo "(1):".$this->completeXML->saveXML($this->document)."\n\n";
	

        $oldcollections = array();
        $oldcollections = $this->document->getElementsByTagName('OldCollection');
        $newcollections = array();
        while ($oldcollections->length > 0) {
           $c = $oldcollections->Item(0);
           $oldValue = $c->getAttribute('Id');
           // ZIB-Report
           if ($oldValue === '15989') { $newValue = '15989'; }
           // Stuidienabschlussarbeiten
           else if ($oldValue === '15991') { $newValue = '15988'; }
           // Numerik
           else if ($oldValue === '15993') { $newValue = '15991'; }
           // Visualisierung
           else if ($oldValue === '15994') { $newValue = '15992'; }
           // Optimierung
           else if ($oldValue === '15995') { $newValue = '15993'; }
           // Vergleichende Visualisierung (Visualisierung)
           else if ($oldValue === '16233') { $newValue = '15992'; }
           // Telekommunikation (Optimierung)
           else if ($oldValue === '16236') { $newValue = '15993'; }
           // Klassifikationen
           else { $newValue = $oldValue; }
           array_push($newcollections, $newValue);
           $this->document->removeChild($c);
        }

        $oldpublisher = array();
        $oldpublisher = $this->document->getElementsByTagName('OldPublisher');
        $newpublisher = array();
        while ($oldpublisher->length > 0) {
           $c = $oldpublisher->Item(0);
           $oldValue = $c->getAttribute('Id');
           $newValue = $oldValue;
           array_push($newpublisher, $newValue);
           $this->document->removeChild($c);
        }

        $oldgrantor = array();
        $oldgrantor = $this->document->getElementsByTagName('OldGrantor');
        $newgrantor = array();
        while ($oldgrantor->length > 0) {
           $c = $oldgrantor->Item(0);
           $oldValue = $c->getAttribute('Id');
           $newValue = $oldValue;
           array_push($newgrantor, $newValue);
           $this->document->removeChild($c);
        }

        $oldlicence = array();
        $oldlicence = $this->document->getElementsByTagName('OldLicence');
        $newlicence = array();
        while ($oldlicence->length > 0) {
           $c = $oldlicence->Item(0);
           $oldValue = $c->getAttribute('Id');
           $newValue = $oldValue;
           array_push($newlicence, $newValue);
           $this->document->removeChild($c);
        }

        $imported = array();
        $doc = null;



        try {

            $doc = Opus_Document::fromXml('<Opus>' . $this->completeXML->saveXML($this->document) . '</Opus>');

            if (count($newcollections) > 0) {
                foreach ($newcollections as $c) {
                    $coll = new Opus_Collection($c);
                    $doc->addCollection($coll);
                }
            }

            if (count($newpublisher) > 0) {
                foreach ($newpublisher as $c) {
                    $doc->setThesisPublisher(new Opus_DnbInstitute($c));
                }
            }

            if (count($newgrantor) > 0) {
                foreach ($newgrantor as $c) {
                    $doc->setThesisGrantor(new Opus_DnbInstitute($c));
                }
            }

            if (count($newlicence) > 0) {
                foreach ($newlicence as $c) {
                    $doc->addLicence(new Opus_Licence($c));
                }
            }

            //echo "(2):".$this->completeXML->saveXML($this->document)."\n\n";
            $doc->store();

            $imported['result'] = 'success';
            $imported['newid'] = $doc->getId();
        } catch (Exception $e) {
            $imported['result'] = 'failure';
            $imported['message'] = $e->getMessage();
            $imported['entry'] = $this->completeXML->saveXML($this->document);
        }

        unset($this->document);

        return $imported;
    }



}
