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
 * Model for Search API
 */
class SearchApi {

    /**
     * Holds search boolean operators.
     *
     * @var array
     */
    private $__bool_operators = array();

    /**
     * Holds error message if search throws an exception
     *
     * @var string
     */
    private $__error_msg = null;

    /**
     * Holds search fields.
     *
     * @var array
     */
    private $__fields = array();

    /**
     * Holds a hitlist of founded search.
     *
     * @var Opus_Search_List_HitList
     */
    private $__hitlist = null;

    /**
     * Holds search language value
     *
     * @var string
     */
    private $__language = null;

    /**
     * Holds builded search query.
     *
     * @var string
     */
    private $__query = null;

    /**
     * Holds requested search terms.
     *
     * @var array
     */
    private $__terms = array();

    /**
     * Holds quest data array.
     *
     * @var array
     */
    private $__requestData = null;

    /**
     * Holds decision about a truncated search
     *
     * @var boolean
     */
    private $__truncated = false;

    /**
     * List of valid boolean operators.
     *
     * @var array
     */
    protected $_valid_bool_operators = array('and', 'or', 'not');

    /**
     * Builds a search query with former founded fields, terms and boolean operators.
     *
     * @return void
     */
    protected function _buildSearchRequest() {

        $query = '';

        foreach ($this->__queries as $key => $term) {
            // skip a query with less than 2 signs
            if (strlen($term) < 2) {
                continue;
            }

            // schema: query <number>
            $pos = $key[5];
            $fieldkey = 'field' . $pos;
            $bool = 'boolean' . ($pos - 1);

            // build only a query if a query term and a proper field exists
            if (true === array_key_exists($fieldkey , $this->__fields)) {

                if (($pos > 0) and (true === array_key_exists($bool, $this->__bool_operators))) {
                    $query .= ' ' . $this->__bool_operators[$bool] . ' ';
                }

                $query .= $this->__fields[$fieldkey] . ':';
                if (true === $this->__truncated) {
                    $query .= '*' . $term . '*';
                } else {
                    $query .= $term;
                }
            }
        }

        if (false === empty($this->__language) and (false === empty($query))) {
            $query .= ' and language:' . $this->__language;
        }

        $this->__query = $query;
    }

    /**
     * Small clean method for deleting unused params.
     *
     * @return void
     */
    protected function _cleanUp() {
        // clean a little bit up the working array
        unset($this->__requestData['module']);
        unset($this->__requestData['controller']);
        unset($this->__requestData['action']);
        unset($this->__requestData['original_action']);
    }

    /**
     * Iterate about a request data array and look for valid fields, terms and boolean operators.
     *
     * @return void
     */
    protected function _prepareSearchRequest() {

        $requestData = $this->__requestData;
        $truncated = '';

        foreach ($requestData as $key => $data) {
            if (false !== strpos($key, 'field')) {
                $this->__fields[$key] = $data;
            } else if (false !== strpos($key, 'query')) {
                $this->__queries[$key] = $data;
            } else if ((false !== strpos($key, 'boolean')) and
            (true === in_array(strtolower($data), $this->_valid_bool_operators, true))) {
                $this->__bool_operators[$key] = strtolower($data);
            } else if (false !== strpos($key, 'language')) {
                $this->__language = $data;
            } else if (false !== strpos($key, 'searchtype')) {
                $truncated = $data;
            }
        }

        $this->__truncated = ('truncated' === $truncated);
    }

    /**
     * Constructor for initial stuff.
     *
     * @param array $requestData (Optional) Data array with necessary request informations.
     */
    public function __construct(array $requestData = null) {
        if (false === empty($requestData)) {
            $this->__requestData = $requestData;
            $this->_cleanUp();
        }
    }

    /**
     * Initiate a search.
     *
     * @param array $requestData (Optional) Data array with necessary request informations.
     * @return void
     */
    public function search(array $requestData = null) {
        if (false === empty($requestData)) {
            $this->__requestData = $requestData;
            $this->_cleanup();
        }

        $this->_prepareSearchRequest();

        $this->_buildSearchRequest();

        $searchQuery = new Opus_Search_Query($this->__query);

        try {
            $this->__hitlist = $searchQuery->commit();
        } catch (Exception $e) {
            $this->__error_msg = $e->getMessage();
        }
    }

    /**
     * Returns the result of a search as an xml string.
     *
     * @return array Returns a array with a status code and a xml string.
     */
    public function getXMLResult() {
        $statuscode = 200;
        $xml = new DOMDocument('1.0', 'utf-8');
        $xml->formatOutput = true;

        $searchResult = $xml->createElement('SearchResult');
        $searchResult->setAttribute('xmlns:xlink', 'http://www.w3.org/1999/xlink');
        $xml->appendChild($searchResult);

        if (false === empty($this->__error_msg)) {
            $error = $xml->createElement('Error', $this->__error_msg);
            $searchResult->appendChild($error);
            return array('code' => 400, 'xml' => $xml->saveXML());
        }

        $hitlist = $this->__hitlist;

        $hitCount = $hitlist->count();

        $search = $xml->createElement('Search');
        $search->setAttribute('hits', $hitCount);
        $search->setAttribute('query', $this->__query);
        $searchResult->appendChild($search);

        if (0 < $hitCount) {
            $resultList = $xml->createElement('ResultList');
            $searchResult->appendChild($resultList);
            $view = Zend_Layout::getMvcInstance()->getView();
            $url = $view->url(array('controller' => 'document', 'module' => 'webapi'), 'default', true);
            for ($n = 0; $n < $hitCount; $n++) {
                $hit =  $hitlist->get($n)->getSearchHit()->getDocument();
                $result = $xml->createElement('Result');
                $result->setAttribute('number', $n);
                $result->setAttribute('xlink:href', $url . '/' . $hit['id']);
                $result->setAttribute('title', $hit['title']);
                $result->setAttribute('author', $hit['author']);
                $result->setAttribute('abstract', $hit['abstract']);
                $result->setAttribute('year', $hit['year']);
                $resultList->appendChild($result);
            }
        }

        return array('code' => $statuscode, 'xml' => $xml->saveXML());
    }
}
