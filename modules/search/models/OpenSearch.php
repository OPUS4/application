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
 * @package     Module_Search
 * @author      Oliver Marahrens <o.marahrens@tu-harburg.de>
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class OpenSearch {

    /**
     * Holds the result list from the performed query
     */
    protected $__hitlist;

    /**
     * Holds the query which was performed
     */
    protected $__query;
    
    /**
     * Holds the query which was performed
     */
    public $startOffset = 1;

    /**
     * Holds the query which was performed
     */
    public $itemsPerPage = 10;

    /**
     * Constructor
     */
    public function __construct($query = null) {
        if (null !== $query) {
            $getResult = new Opus_Search_Query($query);
            $this->__hitlist = $getResult->commit();
    	    $this->__query = $query;
        }
    }

    /**
     * Returns the result of a search as an OpenSearch compliant xml string.
     *
     * @return string Returns a OpenSearch-Result-XML-Document
     */
    public function getRssResult() {
    	
    	$template = new RSSOutput();
    	$result = $template->getTemplate($this->__hitlist, 'OpenSearch RSS Search Result');
        $xml = $result['xmlobject'];

        $searchResult = $xml->getElementsByTagName('rss');
        $searchResult->item(0)->setAttribute('xmlns:opensearch', 'http://a9.com/-/spec/opensearch/1.1/');
        $searchResult->item(0)->setAttribute('xmlns:atom', 'http://www.w3.org/2005/Atom');

        $hitlist = $this->__hitlist;
        $hitCount = $hitlist->count();
        // Put the hitlist into a Pagionator
        $hitlistIterator = new Opus_Search_Iterator_HitListIterator($hitlist);
        $hitlist_paginator = Zend_Paginator::factory($hitlistIterator);
        $hitlist_paginator->setCurrentPageNumber((($this->startOffset-1)/$this->itemsPerPage)+1);
        $hitlist_paginator->setItemCountPerPage($this->itemsPerPage);

        $channelItems = $xml->getElementsByTagName('channel');
        $channel = $channelItems->item(0);

        $view = Zend_Layout::getMvcInstance()->getView();

        // Add OpenSearch-specific Elements to RSS-Output
        $results = $xml->createElement('opensearch:totalResults', $hitCount);
        $channel->appendChild($results);

        $startIndex = $xml->createElement('opensearch:startIndex', $this->startOffset);
        $channel->appendChild($startIndex);
        
        $itemsPerPage = $xml->createElement('opensearch:itemsPerPage', $this->itemsPerPage);
        $channel->appendChild($itemsPerPage);

        $openSearchDescriptionLink = $xml->createElement('atom:link');
        $openSearchDescriptionLink->setAttribute('rel', 'search');
        $openSearchDescriptionLink->setAttribute('type', 'application/opensearchdescription+xml');
        $descriptionurl = $view->url(array('action' => 'description', 
                                        'controller' => 'opensearch', 
                                        'module' => 'search'
                                        ), 'default', true);
        $openSearchDescriptionLink->setAttribute('href', $_SERVER['HTTP_HOST'] . $descriptionurl);
        $channel->appendChild($openSearchDescriptionLink);

        $search = $xml->createElement('opensearch:Query');
        $search->setAttribute('role', 'request');
        $search->setAttribute('searchTerms', $this->__query);
        $search->setAttribute('startPage', ((($this->startOffset-1)/$this->itemsPerPage)+1));
        $channel->appendChild($search);

        return array('code' => $result['code'], 'xml' => $xml->saveXML());
    }

    /**
     * Returns the result of a search as an OpenSearch compliant xml string.
     *
     * @return string Returns a OpenSearch-Result-XML-Document
     */
    public function getAtomResult() {
    	
    }

    /**
     * Returns the result of a search as an OpenSearch compliant xml string.
     *
     * @return string Returns a OpenSearch-Result-XML-Document
     */
    public function getDescription() {
        $statuscode = 200;
        $xml = new DOMDocument('1.0', 'utf-8');
        $xml->formatOutput = true;

        $searchResult = $xml->createElement('OpenSearchDescription');
        $searchResult->setAttribute('xmlns', 'http://a9.com/-/spec/opensearch/1.1/');
        $xml->appendChild($searchResult);

        if (false === empty($this->__error_msg)) {
            $error = $xml->createElement('Error', $this->__error_msg);
            $searchResult->appendChild($error);
            return array('code' => 400, 'xml' => $xml->saveXML());
        }

        $name = $xml->createElement('ShortName', 'OPUS Repository OpenSearch');
        $searchResult->appendChild($name);

        $description = $xml->createElement('Description', 'Search in this OPUS Repository with OpenSearch');
        $searchResult->appendChild($description);

        $tags = $xml->createElement('Tags', 'example web');
        $searchResult->appendChild($tags);

        $contact = $xml->createElement('Contact', 'admin@example.com');
        $searchResult->appendChild($contact);

        $atomUrl = $xml->createElement('Url');
        $atomUrl->setAttribute('type', 'application/atom+xml');
        $view = Zend_Layout::getMvcInstance()->getView();
        $atomurl = $view->url(array('module' => 'search',
                         'controller' => 'opensearch',
                         'action' => 'query',
                         'q' => '{searchTerms}',
                         'format' => 'atom',
                         'start' => '{startIndex}',
                         'items' => '{count}'
                         ), null, true);
        $atomUrl->setAttribute('template', $_SERVER['HTTP_HOST'] . $atomurl);
        $searchResult->appendChild($atomUrl);

        $rssUrl = $xml->createElement('Url');
        $rssUrl->setAttribute('type', 'application/rss+xml');
        $rssurl = $view->url(array('module' => 'search',
                         'controller' => 'opensearch',
                         'action' => 'query',
                         'q' => '{searchTerms}',
                         'format' => 'rss',
                         'start' => '{startIndex}',
                         'items' => '{count}'
                         ), null, true);
        $rssUrl->setAttribute('template', $_SERVER['HTTP_HOST'] . $rssurl);
        $searchResult->appendChild($rssUrl);

        $htmlUrl = $xml->createElement('Url');
        $htmlUrl->setAttribute('type', 'text/html');
        $htmlurl = $view->url(array('module' => 'search',
                         'controller' => 'search',
                         'action' => 'search',
                         'query' => '{searchTerms}',
                         'start' => '{startIndex}',
                         'items' => '{count}'
                         ), null, true);
        $htmlUrl->setAttribute('template', $_SERVER['HTTP_HOST'] . $htmlurl);
        $searchResult->appendChild($htmlUrl);

        $longname = $xml->createElement('LongName', 'Example.com Web Search');
        $searchResult->appendChild($longname);

        $logo = $xml->createElement('Image', 'http://example.com/websearch.png');
        $logo->setAttribute('height', '64');
        $logo->setAttribute('width', '64');
        $logo->setAttribute('type', 'image/png');
        $searchResult->appendChild($logo);

        $icon = $xml->createElement('Image', 'http://example.com/websearch.ico');
        $icon->setAttribute('height', '16');
        $icon->setAttribute('width', '16');
        $icon->setAttribute('type', 'image/vnd.microsoft.icon');
        $searchResult->appendChild($icon);

        $example = $xml->createElement('Query');
        $example->setAttribute('role', 'example');
        $example->setAttribute('searchTerms', 'cat');
        $searchResult->appendChild($example);

        $dev = $xml->createElement('Developer', 'OPUS4 Development Team');
        $searchResult->appendChild($dev);

        $attrib = $xml->createElement('Attribution', 'Data provided by OPUS4');
        $searchResult->appendChild($attrib);

        $syndication = $xml->createElement('SyndicationRight', 'open');
        $searchResult->appendChild($syndication);

        $adult = $xml->createElement('AdultContent', 'false');
        $searchResult->appendChild($adult);

        $lang = $xml->createElement('Language', 'de-de');
        $searchResult->appendChild($lang);

        $outEnc = $xml->createElement('OutputEncoding', 'UTF-8');
        $searchResult->appendChild($outEnc);

        $inEnc = $xml->createElement('InputEncoding', 'UTF-8');
        $searchResult->appendChild($inEnc);

        return array('code' => $statuscode, 'xml' => $xml->saveXML());
    }
}