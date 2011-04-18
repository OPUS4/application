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

class Export_RssController extends Controller_Xml {

    private $log;
    const NUM_OF_ITEMS_PER_FEED = '25';

    public function init() {
        parent::init();
        $this->log = Zend_Registry::get('Zend_Log');
    }

    public function indexAction() {
        $queryBuilder = new Util_QueryBuilder(true);
        $params = array (
            'rows' => self::NUM_OF_ITEMS_PER_FEED,
            'searchtype' => Util_Searchtypes::LATEST_SEARCH,
        );
        
        $resultList = array();
        try {
            $searcher = new Opus_SolrSearch_Searcher();
            $resultList = $searcher->search($queryBuilder->createSearchQuery($params));
        }
        catch (Opus_SolrSearch_Exception $e) {
            $this->log->err(__METHOD__ . ' : ' . $e->getMessage());
            throw new Application_Exception('Sorry, an internal server error occurred.');
        }
        
        $this->loadStyleSheet($this->view->getScriptPath('') . 'stylesheets' . DIRECTORY_SEPARATOR . 'rss2_0.xslt');
        $this->setLink();
        $this->setDates($resultList);
        $this->setItems($resultList);
        $this->setFrontdoorBaseUrl();
    }

    private function setLink() {
        $this->_proc->setParameter('', 'link', $this->view->serverUrl() . $this->getRequest()->getBaseUrl() . '/export/rss/');
    }

    private function setDates($resultList) {
        if ($resultList->getNumberOfHits() > 0) {
            $latestDoc = $resultList->getResults();
            $document = new Opus_Document($latestDoc[0]->getId());
            $date = new Zend_Date($document->getServerDatePublished());
            $this->_proc->setParameter('', 'lastBuildDate', $date->get(Zend_Date::RFC_2822));
            $this->_proc->setParameter('', 'pubDate', $date->get(Zend_Date::RFC_2822));
        }
    }

    private function setItems($resultList) {                    
        $this->_xml->appendChild($this->_xml->createElement('Documents'));
        foreach ($resultList->getResults() as $result) {
            $document = new Opus_Document($result->getId());
            $documentXml = new Util_Document($document);
            $domNode = $this->_xml->importNode($documentXml->getNode(), true);

            // add publication date in RFC_2822 format
            $doc = new Opus_Document($result->getId());
            $date = new Zend_Date($doc->getServerDatePublished());
            $itemPubDate = $this->_xml->createElement('ItemPubDate', $date->get(Zend_Date::RFC_2822));
            $domNode->appendChild($itemPubDate);
            $this->_xml->documentElement->appendChild($domNode);
        }
    }

    private function setFrontdoorBaseUrl() {
        $this->_proc->setParameter('', 'frontdoorBaseUrl', $this->view->serverUrl() . $this->getRequest()->getBaseUrl() . '/frontdoor/index/index/docId/');
    }
}
?>