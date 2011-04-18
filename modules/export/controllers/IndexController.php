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
 * @copyright   Copyright (c) 2008-2011, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class Export_IndexController extends Controller_Xml {

    private $log;

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
        $this->prepareXml();
    }

    private function prepareXml() {
        $this->setStylesheet($this->getRequest()->getParam('stylesheet'));

        try {
            $searcher = new Opus_SolrSearch_Searcher();
            $resultList = $searcher->search($this->buildQuery());
            $this->handleResults($resultList->getResults());
        }
        catch (Opus_SolrSearch_Exception $e) {
            $this->log->err(__METHOD__ . ' : ' . $e->getMessage());
            throw new Application_Exception('Sorry, an internal server error occurred.');
        }
        
    }

    /**
     *
     * @param string $stylesheet
     * @return void
     */
    private function setStylesheet($stylesheet = null) {
        if (!is_null($stylesheet) && is_readable($this->view->getScriptPath('') . 'stylesheets-custom' . DIRECTORY_SEPARATOR . $stylesheet . '.xslt')) {            
            $this->loadStyleSheet($this->view->getScriptPath('') . 'stylesheets-custom' . DIRECTORY_SEPARATOR .  $stylesheet . '.xslt');
        }
        else {
            $this->loadStyleSheet($this->view->getScriptPath('') . 'stylesheets' . DIRECTORY_SEPARATOR . 'raw.xslt');
        }
    }

    /**
     *
     * @param array $results An array of Opus_SolrSearch_Result objects.
     */
    private function handleResults($results) {
        $this->_proc->setParameter('', 'timestamp', str_replace('+00:00', 'Z', Zend_Date::now()->setTimeZone('UTC')->getIso()));
        $this->_proc->setParameter('', 'docCount', count($results));
        $this->_xml->appendChild($this->_xml->createElement('Documents'));
        foreach ($results as $result) {
            $document = new Opus_Document($result->getId());
            $documentXml = new Util_DocumentXmlCache($document);
            $domNode = $this->_xml->importNode($documentXml->getNode(), true);
            $this->_xml->documentElement->appendChild($domNode);
        }
    }

    private function buildQuery() {
        $queryBuilder = new Util_QueryBuilder(true);
        $queryBuilderInput = array();
        try {
            $queryBuilderInput = $queryBuilder->createQueryBuilderInputFromRequest($this->getRequest());
        }
        catch (Util_QueryBuilderException $e) {
            throw $e;
        }

        $this->searchtype = $this->getRequest()->getParam('searchtype');
        if ($this->searchtype === Util_Searchtypes::COLLECTION_SEARCH) {
            $collectionList = null;
            try {
                $collectionList = new Solrsearch_Model_CollectionList($this->getRequest()->getParam('id'));
            }
            catch (Solrsearch_Model_Exception $e) {
                $this->log->debug($e->getMessage());
                throw new Application_Exception($e->getMessage(), $e->getCode(), $e);
            }
            $queryBuilderInput['collectionId'] = $collectionList->getCollectionId();
        }

        return $queryBuilder->createSearchQuery($queryBuilderInput);
    }
}

?>