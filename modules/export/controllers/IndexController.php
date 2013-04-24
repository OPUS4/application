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
 * @package     Module_Export
 * @author      Sascha Szott <szott@zib.de>
 * @author      Gunar Maiwald <maiwald@zib.de>
 * @copyright   Copyright (c) 2008-2011, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class Export_IndexController extends Controller_Xml {

    private $log;
    private $stylesheetDirectory;

    public function init() {
        parent::init();
        $this->log = Zend_Registry::get('Zend_Log');
    }    

    public function indexAction() {
        $exportParam = $this->getRequest()->getParam('export');
        if (is_null($exportParam)) {
            throw new Application_Exception('export format is not specified');
        }

        // currently only xml is supported here
        if ($exportParam !== 'xml') {
            throw new Application_Exception('export format is not supported');
        }

        // parameter stylesheet is mandatory (only administrator is able to see raw output)
        // non-administrative users can only reference user-defined stylesheets
        if (is_null($this->getRequest()->getParam('stylesheet')) && !Opus_Security_Realm::getInstance()->checkModule('admin')) {
            throw new Application_Exception('missing parameter stylesheet');

        }

        $this->stylesheetDirectory = 'stylesheets-custom';
        $this->prepareXml();
    }

    private function prepareXml() {
        $this->setStylesheet($this->getRequest()->getParam('stylesheet'));

        try {
            $searcher = new Opus_SolrSearch_Searcher();
            $resultList = $searcher->search($this->buildQuery());
            $this->handleResults($resultList->getResults(), $resultList->getNumberOfHits());
        }
        catch (Opus_SolrSearch_Exception $e) {
            $this->log->err(__METHOD__ . ' : ' . $e);
            throw new Application_SearchException($e, true);
        }        
    }

    /**
     *
     * @param string $stylesheet
     * @return void
     */
    private function setStylesheet($stylesheet = null) {
        if (!is_null($stylesheet)) {

            $stylesheetsAvailable = array();
            $dir = new DirectoryIterator($this->view->getScriptPath('') . $this->stylesheetDirectory);
            foreach ($dir as $file) {
                if ($file->isFile() && $file->getFilename() != '.' && $file->getFilename() != '..' && $file->isReadable()) {
                    array_push($stylesheetsAvailable, $file->getBasename('.xslt'));
                }
            }            

            $pos = array_search($stylesheet, $stylesheetsAvailable);            
            if ($pos !== FALSE) {
                $this->loadStyleSheet($this->view->getScriptPath('') . $this->stylesheetDirectory . DIRECTORY_SEPARATOR .  $stylesheetsAvailable[$pos] . '.xslt');
                return;
            }
            throw new Application_Exception('given stylesheet does not exist or is not readable');
        }
        $this->loadStyleSheet($this->view->getScriptPath('') . 'stylesheets' . DIRECTORY_SEPARATOR . 'raw.xslt');
    }

    /**
     *
     * @param array $results An array of Opus_SolrSearch_Result objects.
     */
    private function handleResults($results, $numOfHits) {
        $this->_proc->setParameter('', 'timestamp', str_replace('+00:00', 'Z', Zend_Date::now()->setTimeZone('UTC')->getIso()));
        $this->_proc->setParameter('', 'docCount', count($results));
        $this->_proc->setParameter('', 'queryhits', $numOfHits);
        $this->_xml->appendChild($this->_xml->createElement('Documents'));
        
        $resultIds = array();
        foreach ($results as $result) {
            $resultIds[] = $result->getId();
        }
        if(!empty($resultIds)) {
            $documentCacheTable = new Opus_Db_DocumentXmlCache();
            $docXmlCache = $documentCacheTable->fetchAll($documentCacheTable->select()->where('document_id IN (?)', $resultIds));//->find($this->document->getId(), '1')->current()->xml_data;

            $processedIds = array();
            
            foreach($docXmlCache as $row) {
                $fragment = new DomDocument();
                $fragment->loadXML($row->xml_data);
                $domNode = $this->_xml->importNode($fragment->getElementsByTagName('Opus_Document')->item(0), true);
                $this->_xml->documentElement->appendChild($domNode);
                $processedIds[] = $row->document_id;
            }
            
            // create and append cache for documents without cache
            $unprocessedIds = array_diff($resultIds, $processedIds);
            
            if(!empty($unprocessedIds)) {
                foreach($unprocessedIds as $docId) {
                    $document = new Opus_Document($docId);
                    $documentXml = new Util_Document($document);
                    $domNode = $this->_xml->importNode($documentXml->getNode(), true);
                    $this->_xml->documentElement->appendChild($domNode);
                }
            }
        }
    }

    private function buildQuery() {
        $queryBuilder = new Util_QueryBuilder($this->log, true);
        $queryBuilderInput = array();
        try {
            $queryBuilderInput = $queryBuilder->createQueryBuilderInputFromRequest($this->getRequest());
        }
        catch (Util_QueryBuilderException $e) {
            $this->log->err(__METHOD__ . ' : ' . $e->getMessage());
            throw new Application_Exception($e->getMessage());
        }
        
        return $queryBuilder->createSearchQuery($queryBuilderInput);
    }

    public function publistAction() {
        $stylesheetParam = $this->getRequest()->getParam('stylesheet');
        if (is_null($stylesheetParam)) {
            throw new Application_Exception('stylesheet is not specified');
        }

        $roleParam = $this->getRequest()->getParam('role');
        if (is_null($roleParam)) {
            throw new Application_Exception('role is not specified');
        }

        $numberParam = $this->getRequest()->getParam('number');
        if (is_null($numberParam)) {
            throw new Application_Exception('number is not specified');
        }

        $this->stylesheetDirectory = 'publist';
        $this->prepareXML();
    }

}

