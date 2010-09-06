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

class Search_Model_RSSOutput {
	
	
	/**
	 * Gets RSS output for a given list of documents 
	 */
	public function getTemplate($hitlist, $givenTitle = null, $rssVersion = '2.0') {

        $hitCount = $hitlist->getTotalItemCount();
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

        if (0 < $hitCount && is_int($hitCount) === true) {
            foreach ($hitlist as $searchhit) {
            	$h = $searchhit->getSearchHit();
                $doc = $h->getDocument();
                $id = $doc['id'];
            	
                $doc = new Opus_Document( (int) $id);
                $result = $xml->createElement('item');
                $channel->appendChild($result);
                $url = $view->url(
                    array(
                        'action' => 'index', 
                        'controller' => 'index', 
                        'module' => 'frontdoor',
                        'docId' => $id
                    ), 
                    'default', 
                    true
                );
                $linkElement = $xml->createElement('link', $_SERVER['HTTP_HOST'] . $url);
                $result->appendChild($linkElement);
                $titleElement = $xml->createElement('title', $doc->getTitleMain(0)->getValue());
                $result->appendChild($titleElement);
                $c = count($doc->getPersonAuthor());
                $authorString = '';
                for ($counter = 0; $counter < $c; $counter++) {
            	    $name = $doc->getPersonAuthor($counter)->getName();
                    $authorString .= $name;
                    if ($counter < $c-1) $authorString .= '; ';
                }
                $authorElement = $xml->createElement('author', $authorString);
                $result->appendChild($authorElement);
                $abstractElement = $xml->createElement('description', str_replace("&hellip;", " ... ", $doc->getTitleAbstract(0)->getValue()));
                $result->appendChild($abstractElement);
                $yearElement = $xml->createElement('pubDate', $doc->getServerDatePublished());
                $result->appendChild($yearElement);
            }
        }

        return array('code' => $statuscode, 'xmlobject' => $xml);
    }
	
}