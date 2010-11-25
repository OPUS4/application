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
class Opus3XMLImport {

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
     * Holds the collections predifined in OPUS
     *
     * @var array  Defaults to the OPUS collections (Name => ID).
     */
    protected $collections = array();
    /**
     * Holds the logfile for Importer
     *
     * @var string  Path to logfile
     */
    protected $logfile = '../workspace/log/import.log';
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
        $this->_xml = new DomDocument;
        $this->_xslt = new DomDocument;
        $this->_xslt->load($stylesheetPath . '/' . $xslt);
        $this->_proc = new XSLTProcessor;
        $this->_proc->registerPhpFunctions();
        $this->_proc->importStyleSheet($this->_xslt);
        $this->_logfile = fopen($this->logfile, 'a');
    }

    public function log($string) {
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

        $doc = null;

        $oldid = null;
        $oldid = $document->getElementsByTagName('IdentifierOpus3')->Item(0)->getAttribute('Value');

        $oldclasses = array();
        $oldclasses = $document->getElementsByTagName('OldClasses');
        $newclasses = array();
        while ($oldclasses->length >0) {
            $oc=$oldclasses->Item(0);
            if (($oc->getAttribute('Key') != "") && ($oc->getAttribute('Value')) != "") {
                $nc = array('Key'=>strtolower($oc->getAttribute('Key')), 'Value'=>$oc->getAttribute('Value'));
                array_push($newclasses, $nc);
            }
            $this->document->removeChild($oc);
        }

        $oldcollections = array();
        $oldcollections = $document->getElementsByTagName('OldCollections');
        $newcollections = array();
        while ($oldcollections->length > 0) {
           $c = $oldcollections->Item(0);
           $mappingFile = '../workspace/tmp/collections.map';
           if (!is_null( $this->getMapping($mappingFile, $c->getAttribute('Value')))) {
                array_push($newcollections, $this->getMapping($mappingFile, $c->getAttribute('Value')));
           }
           $this->document->removeChild($c);
        }

        $oldddc = null;
        $oldddc = $document->getElementsByTagName('OldDdc')->Item(0);
        $newddc = null;
        if ($oldddc !== null) {
            $newddc = $oldddc->getAttribute('Value');
            $this->document->removeChild($oldddc);
        }

        $oldgrantor = null;
        $oldgrantor = $document->getElementsByTagName('OldGrantor');
        $newgrantor = null;
        while ($oldgrantor->length > 0) {
           $g = $oldgrantor->Item(0);
           $mappingFile = '../workspace/tmp/grantor.map';
           if (!is_null( $this->getMapping($mappingFile, $g->getAttribute('Value')))) {
                $newgrantor =  $this->getMapping($mappingFile, $g->getAttribute('Value'));
           }
           $this->document->removeChild($g);
        }

        $oldinstitutes = null;
        $oldinstitutes = $document->getElementsByTagName('OldInstitute');
        $newinstitutes = array();
        while ($oldinstitutes->length >0) {
            $i=$oldinstitutes->Item(0);
            $mappingFile = '../workspace/tmp/institute.map';
            if (!is_null($this->getMapping($mappingFile, $i->getAttribute('Value')))) {
                array_push($newinstitutes, $this->getMapping($mappingFile, $i->getAttribute('Value')));
            }
            $this->document->removeChild($i);
        }

        $oldlicence = null;
        $oldlicence = $document->getElementsByTagName('OldLicence');
        $newlicence = null;
        while ($oldlicence->length >0) {
            $l=$oldlicence->Item(0);
            $mappingFile = '../workspace/tmp/license.map';
            if (!is_null($this->getMapping($mappingFile, $l->getAttribute('Value')))) {
                $newlicence = $this->getMapping($mappingFile, $l->getAttribute('Value'));
            }
            $this->document->removeChild($l);
        }

        $oldpublisher = null;
        $oldpublisher = $document->getElementsByTagName('OldPublisherUniversity');
        $newpublisher = null;
        while ($oldpublisher->length > 0) {
           $p = $oldpublisher->Item(0);
           $mappingFile = '../workspace/tmp/universities.map';
           if (!is_null( $this->getMapping($mappingFile, $p->getAttribute('Value')))) {
                $newpublisher =  $this->getMapping($mappingFile, $p->getAttribute('Value'));
           }
           $this->document->removeChild($p);
        }

        $oldseries = null;
        $oldseries = $document->getElementsByTagName('OldSeries');
        $newseries = array();
        while ($oldseries->length >0) {
            $s = $oldseries->Item(0);
            $mappingFile = '../workspace/tmp/series.map';
            if (!is_null($this->getMapping($mappingFile, $s->getAttribute('Value')))) {
                $ns = array('Key'=>$this->getMapping($mappingFile, $s->getAttribute('Value')), 'Value'=>$s->getAttribute('Issue'));
                array_push($newseries, $ns);
            }
            $this->document->removeChild($s);
        }

        $imported = array();

        try {
            
            // Dummyobject, does not need any content, because only one node is transformed
            $doc = Opus_Document::fromXml('<Opus>' . $this->completeXML->saveXML($this->document) . '</Opus>');

            //echo "BEFORE:".$this->completeXML->saveXML($this->document)."\n";

            if (count($newclasses) > 0) {
                foreach ($newclasses as $c) {
                  $this->addDocumentToCollection($doc, $c['Key'], $c['Value']);
                }
            }

            if (count($newcollections) > 0) {
                foreach ($newcollections as $c) {
                    $coll = new Opus_Collection($c);
                    $doc->addCollection($coll);
                }
            }

            if ($newddc !== null) {
                  $this->addDocumentToCollection($doc, 'ddc', $newddc);
            }

            if ($newgrantor !== null) {
                $dnbInstitute = new Opus_DnbInstitute($newgrantor);
                $doc->setThesisGrantor($dnbInstitute);
                $dnbInstitute = new Opus_DnbInstitute(1);
                $doc->setThesisPublisher($dnbInstitute);
            }

            if (count($newinstitutes) > 0) {
                foreach ($newinstitutes as $i) {
                    $coll = new Opus_Collection($i);
                    $doc->addCollection($coll);
                }
            }

            if ($newlicence !== null) {
                /* TODO: Throw Exception if Licence not valid */
                $doc->addLicence(new Opus_Licence($newlicence));
            } else {
                $doc->addLicence(new Opus_Licence('1'));
            }

            /*
            if ($newpublisher !== null) {
                $dnbInstitute = new Opus_DnbInstitute($newpublisher);
                $doc->setThesisPublisher($dnbInstitute);
                $dnbInstitute = new Opus_DnbInstitute(1);
                $doc->setThesisPublisher($dnbInstitute);
            }
             *
             */

            if (count($newseries) > 0) {
                foreach ($newseries as $s) {
                    $coll = new Opus_Collection($s['Key']);
                    $doc->addCollection($coll);
                    $doc->setIssue($s['Value']);
                }
            }

            // Set the publication status to published since only published documents shall be imported
            $doc->setServerState('published');

             // store the document
            //echo "AFTER:".$this->completeXML->saveXML($this->document)."\n";
            $doc->store();

            $imported['result'] = 'success';
            $imported['oldid'] = $oldid;
            $imported['newid'] = $doc->getId();
        } catch (Exception $e) {
            $imported['result'] = 'failure';
            $imported['message'] = $e->getMessage();
            $imported['entry'] = $this->completeXML->saveXML($this->document);
            $imported['oldid'] = $oldid;
        }

        unset($doc);
	unset($this->document);
        unset($oldid);
        unset($oldclasses);
        unset($newclasses);
        unset($oldcollections);
        unset($newcollections);
        unset($oldddc);
        unset($newddc);
        unset($oldgrantor);
        unset($newgrantor);
        unset($oldinstitutes);
        unset($newinstitutes);
	unset($oldlicence);
	unset($newlicence);
	unset($oldpublisher);
	unset($newpublisher);
	unset($oldseries);
	unset($newseries);

        return $imported;
    }

    /**
     * Get mapped Values for a document and add it
     *
     * @mappingFile: name of the Mapping-File
     * @id: original id
     * @return new id
     */
     private function getMapping($mappingFile, $id) {
        $fp = file($mappingFile);
        $mapping = array();
        foreach ($fp as $line) {
            $values = explode(" ", $line);
            $mapping[$values[0]] = $values[1];
        }
        if (array_key_exists($id, $mapping) === false) {
            return null;
        }
        unset($fp);
        return $mapping[$id];
    }

    /**
     * Add Document to a Collection identified by role_name and number
     *
     * @document: Opus-Document
     * @role_name: Role_Name of Colelction
     * @number: Number of Collection
     * @return Opus_Licence Licence to be added to the document
     */
     private function addDocumentToCollection($document, $role_name, $number) {

        $role = Opus_CollectionRole::fetchByName($role_name);
        $colls = Opus_Collection::fetchCollectionsByRoleNumber($role->getId(), $number);

        //echo "role_id ". $role->getId(). " number " . $number ."\n";

        if (count($colls) > 0) {
            foreach ($colls as $c) {
                /* TODO: DDC-Hack */
                if (($role_name == 'ddc') and (count($c->getChildren()) > 0)) { continue; }
                $document->addCollection($c);
                //echo "Document added to $role_name Collection $number \n";
            }
        }
        else {
            echo "ERROR: Document not added to $role_name Collection $number \n";
            $this->log("ERROR: Document not added to $role_name Collection $number \n");
        }
    }
}
