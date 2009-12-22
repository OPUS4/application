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

class RSSOutput {
	
	
	/**
	 * Gets RSS output for a given list of documents 
	 */
	public function getTemplate($hitlist, $givenTitle = null, $rssVersion = '2.0') {

        $hitCount = $hitlist->count();
        // Put the hitlist into a Pagionator
        $hitlistIterator = new Opus_Search_Iterator_HitListIterator($hitlist);
        $hitlist_paginator = Zend_Paginator::factory($hitlistIterator);

        $statuscode = 200;
        $xml = new DOMDocument('1.0', 'utf-8');
        $xml->formatOutput = true;

        $searchResult = $xml->createElement('rss');
        $searchResult->setAttribute('version', $rssVersion);
        $xml->appendChild($searchResult);

        $channel = $xml->createElement('channel');
        $searchResult->appendChild($channel);

        $title = $xml->createElement('title', $givenTitle);
        $channel->appendChild($title);

        // instantiate a View to get the URL method 
        $view = Zend_Layout::getMvcInstance()->getView();

        if (0 < $hitCount) {
            foreach ($hitlist_paginator as $searchhit) {
                $hit =  $searchhit->getSearchHit()->getDocument();
                $doc = new Opus_Document($hit['id']);
                $result = $xml->createElement('item');
                $channel->appendChild($result);
                $url = $view->url(array('action' => 'index', 
                                        'controller' => 'index', 
                                        'module' => 'frontdoor',
                                        'docId' => $hit['id']
                                        ), 'default', true);
                $linkElement = $xml->createElement('link', $_SERVER['HTTP_HOST'] . $url);
                $result->appendChild($linkElement);
                $titleElement = $xml->createElement('title', $hit['title']);
                $result->appendChild($titleElement);
                $authorElement = $xml->createElement('author', $hit['author']);
                $result->appendChild($authorElement);
                $abstractElement = $xml->createElement('description', $hit['abstract']);
                $result->appendChild($abstractElement);
                $docArray = $doc->toArray();
                $pubDate = $docArray['ServerDatePublished']['day'] . '.' . $docArray['ServerDatePublished']['month'] . '.' . $docArray['ServerDatePublished']['year'];  
                $yearElement = $xml->createElement('pubDate', $pubDate);
                $result->appendChild($yearElement);
            }
        }

        return array('code' => $statuscode, 'xmlobject' => $xml);
    }
	
}