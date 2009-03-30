<?php
/**
 * This file is part of OPUS. The software OPUS has been originally developed
 * at the University of Stuttgart with funding from the German Research Net,
 * the Federal Department of Higher Education and Research and the Ministry
 * of Science, Research and the Arts of the State of Baden-Wuerttemberg.
 *
 * OPUS 4 is a complete rewrite of the original OPUS software and was developed
 * by the Stuttgart University Library, the Library Service Center
 * Baden-Wuerttemberg, the North Rhine-Westphalian Library Service Center,
 * the Cooperative Library Network Berlin-Brandenburg, the Saarland University
 * and State Library, the Saxon State Library - Dresden State and University
 * Library, the Bielefeld University Library and the University Library of
 * Hamburg University of Technology with funding from the German Research
 * Foundation and the European Regional Development Fund.
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
 * @category   Application
 * @package    Module_Webapi
 * @author     Henning Gerhardt (henning.gerhardt@slub-dresden.de)
 * @copyright  Copyright (c) 2009, OPUS 4 development team
 * @license    http://www.gnu.org/licenses/gpl.html General Public License
 * @version    $Id$
 */

/**
 * Controller for handling search specific requests.
 */
class Webapi_SearchController extends Controller_Rest {

    /**
     * (non-PHPdoc)
     * @see    library/Controller/Controller_Rest#getAction()
     */
    public function getAction() {
        $requestData = $this->requestData;
        $xml = new DOMDocument('1.0', 'utf-8');
        $xml->formatOutput = true;

        $searchResult = $xml->createElement('SearchResult');
        $searchResult->setAttribute('xmlns:xlink', 'http://www.w3.org/1999/xlink');
        $xml->appendChild($searchResult);

        // clean a little bit up the working array
        unset($requestData['module']);
        unset($requestData['controller']);
        unset($requestData['action']);
        unset($requestData['original_action']);

        $language = '';

        $fields = array();
        $queries = array();
        $bool_operators = array();
        $valid_bool_operators = array('and', 'or', 'not');

        foreach ($requestData as $key => $data) {
            if (false !== strpos($key, 'field')) {
                $fields[$key] = $data;
            } else if (false !== strpos($key, 'query')) {
                $queries[$key] = $data;
            } else if ((false !== strpos($key, 'boolean')) and
                 (true === in_array(strtolower($data), $valid_bool_operators, true))) {
                    $bool_operators[$key] = strtolower($data);
            } else if (false !== strpos($key, 'language')) {
                $language = $data;
            } else if (false !== strpos($key, 'searchtype')) {
                $truncated = $data;
            }
        }

        $truncated = ('truncated' === @$requestData['searchtype']);

        $query = '';
        foreach ($queries as $key => $term) {
            // skip a query with less than 2 signs
            if (strlen($term) < 2) {
                continue;
            }

            $pos = $key[5]; // schema: query<number>
            $fieldkey = 'field' . $pos;
            $bool = 'boolean' . ($pos - 1);

            // build only a query if a query term and a proper field exists
            if (true === array_key_exists($fieldkey , $fields)) {

                if (($pos > 0) and (true === array_key_exists($bool, $bool_operators))) {
                   $query .= ' ' . $bool_operators[$bool] . ' ';
                }

                $query .= $fields[$fieldkey] . ':';
                if (true === $truncated) {
                    $query .= '*' . $term . '*';
                } else {
                    $query .= $term;
                }
            }
        }

        if (false === empty($language) and (false === empty($query))) {
            $query .= ' and language:' . $language;
        }

        $searchQuery = new Opus_Search_Query($query);

        try {
            $hitlist = $searchQuery->commit();
        } catch (Exception $e) {
            $error = $xml->createElement('Error', $e->getMessage());
            $searchResult->appendChild($error);
            $this->getResponse()->setBody($xml->saveXML());
            $this->getResponse()->setHttpResponseCode(400);
            return;
        }

        $hitCount = $hitlist->count();

        $search = $xml->createElement('Search');
        $search->setAttribute('hits', $hitCount);
        $search->setAttribute('query', $query);
        $searchResult->appendChild($search);

        if (0 < $hitCount) {
            $resultList = $xml->createElement('ResultList');
            $searchResult->appendChild($resultList);
            $url = $this->getRequest()->getBasePath() . $this->_helper->url('', 'document', 'webapi');
            for ($n = 0; $n < $hitlist->count(); $n++) {
                $hit =  $hitlist->get($n)->getSearchHit()->getDocument();
                $result = $xml->createElement('Result');
                $result->setAttribute('number', $n);
                $result->setAttribute('xlink:href', $url . $hit['id']);
                $result->setAttribute('title', $hit['title']);
                $result->setAttribute('author', $hit['author']);
                $result->setAttribute('abstract', $hit['abstract']);
                $result->setAttribute('year', $hit['year']);
                $resultList->appendChild($result);
            }
        }
        $this->getResponse()->setBody($xml->saveXML());
    }

}