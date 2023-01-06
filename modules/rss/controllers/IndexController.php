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
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 *
 * TODO context spezifische Titel für RSS feed (latest, collections, ...)
 * TODO move feed code into Rss_Model_Feed
 */

use Opus\Common\Document;
use Opus\Search\Result\Base;
use Opus\Search\SearchException;

class Rss_IndexController extends Application_Controller_Xml
{
    public const NUM_OF_ITEMS_PER_FEED = '25';

    public const RSS_SORT_FIELD = 'server_date_published';

    public const RSS_SORT_ORDER = 'desc';

    public function init()
    {
        parent::init();
    }

    /**
     * @throws Application_Exception
     *
     * TODO function should only call performSearch instead of doing the search steps separately
     */
    public function indexAction()
    {
        // support backward compatibility: interpret missing parameter searchtype as latest search
        $searchType = $this->getRequest()->getParam('searchtype', Application_Util_Searchtypes::LATEST_SEARCH);

        $search = Application_Util_Searchtypes::getSearchPlugin($searchType);

        $params = [];

        try {
            $params = $search->createQueryBuilderInputFromRequest($this->getRequest());
        } catch (Application_Search_QueryBuilderException $e) {
            $this->getLogger()->err(__METHOD__ . ' : ' . $e->getMessage());
            $applicationException = new Application_Exception($e->getMessage());
            $code                 = $e->getCode();
            if ($code !== 0) {
                $applicationException->setHttpResponseCode($code);
            }
            throw $applicationException;
        }

        // overwrite parameters in rss context
        // rss feeds have a fixed maximum number of items
        $params['rows']  = self::NUM_OF_ITEMS_PER_FEED;
        $params['start'] = 0;
        // rss feeds have both a fixed sort field and sort order
        $params['sortField'] = self::RSS_SORT_FIELD;
        $params['sortOrder'] = self::RSS_SORT_ORDER;

        $resultList = [];
        try {
            $searcher   = Application_Search_SearcherFactory::getSearcher();
            $resultList = $searcher->search($search->createSearchQuery($params));
        } catch (SearchException $exception) {
            $this->handleSolrError($exception);
        }

        $this->loadStyleSheet($this->view->getScriptPath('') . 'stylesheets' . DIRECTORY_SEPARATOR . 'rss2_0.xslt');

        $this->setParameters();
        $this->setDates($resultList);
        $this->setItems($resultList);
        $this->setFrontdoorBaseUrl();
    }

    private function handleSolrError(SearchException $exception)
    {
        $this->_helper->layout()->enableLayout();
        $this->getLogger()->err(__METHOD__ . ' : ' . $exception);

        if ($exception->isServerUnreachable()) {
            $e = new Application_Exception('error_search_unavailable');
            $e->setHttpResponseCode(503);
            throw $e;
        } elseif ($exception->isInvalidQuery()) {
            $e = new Application_Exception('error_search_invalidquery');
            $e->setHttpResponseCode(500);
            throw $e;
        } else {
            $e = new Application_Exception('error_search_unknown');
            $e->setHttpResponseCode(500);
            throw $e;
        }
    }

    /**
     * Sets parameters for XSLT processor.
     */
    private function setParameters()
    {
        $feed = new Rss_Model_Feed($this->view);

        $feedLink = $this->view->serverUrl() . $this->getRequest()->getBaseUrl() . '/index/index/';

        $this->proc->setParameter('', 'feedTitle', $feed->getTitle());
        $this->proc->setParameter('', 'feedDescription', $feed->getDescription());
        $this->proc->setParameter('', 'link', $feedLink);
    }

    /**
     * @param Base $resultList
     * @throws Exception
     */
    private function setDates($resultList)
    {
        if ($resultList->getNumberOfHits() > 0) {
            $latestDoc = $resultList->getResults();
            $document  = Document::get($latestDoc[0]->getId());
            $date      = $document->getServerDatePublished()->getDateTime();
        } else {
            $date = new DateTime(); // now
        }

        $dateOutput = $date->format(DateTime::RFC2822);
        $this->proc->setParameter('', 'lastBuildDate', $dateOutput);
        $this->proc->setParameter('', 'pubDate', $dateOutput);
    }

    /**
     * @param Base $resultList
     * @throws Application_Exception
     * @throws DOMException
     */
    private function setItems($resultList)
    {
        $this->xml->appendChild($this->xml->createElement('Documents'));
        foreach ($resultList->getResults() as $result) {
            $document    = Document::get($result->getId());
            $documentXml = new Application_Util_Document($document);
            $domNode     = $this->xml->importNode($documentXml->getNode(), true);

            // add publication date in RFC_2822 format
            $date        = $document->getServerDatePublished()->getDateTime();
            $itemPubDate = $this->xml->createElement('ItemPubDate', $date->format(DateTime::RFC2822));
            $domNode->appendChild($itemPubDate);
            $this->xml->documentElement->appendChild($domNode);
        }
    }

    private function setFrontdoorBaseUrl()
    {
        $this->proc->setParameter('', 'frontdoorBaseUrl', $this->view->fullUrl() . '/frontdoor/index/index/docId/');
    }
}
