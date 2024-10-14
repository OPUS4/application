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
 */

use Opus\Search\Util\Query;

/**
 * Modelklasse fuer Suchfunktionalität, die von den Controllern verwendet wird.
 */
class Solrsearch_Model_Search extends Application_Model_Abstract
{
    /**
     * Factory function for search plugin.
     *
     * @param string $searchType
     * @return Solrsearch_Model_Search_Abstract
     *
     * TODO eliminate switch and use configuration array instead
     */
    public function getSearchPlugin($searchType)
    {
        return Application_Util_Searchtypes::getSearchPlugin($searchType);
    }

    /**
     * @param Zend_Controller_Request_Http $request
     * @return array
     */
    public function createSimpleSearchUrlParams($request)
    {
        $params               = $this->createBasicSearchParams($request);
        $params['searchtype'] = $request->getParam('searchtype', Application_Util_Searchtypes::SIMPLE_SEARCH);
        $params['query']      = $request->getParam('query', '*:*');
        return array_merge($params, $this->getFilterParams($request));
    }

    /**
     * @param Zend_Controller_Request_Http $request
     * @return array
     */
    public function getFilterParams($request)
    {
        $params = $request->getParams();

        $params = array_filter($params, function ($key) {
            return substr($key, -2) === 'fq';
        }, ARRAY_FILTER_USE_KEY);

        return $params;
    }

    /**
     * @param Zend_Controller_Request_Http $request
     * @return array
     */
    public function createAdvancedSearchUrlParams($request)
    {
        $params               = $this->createBasicSearchParams($request);
        $params['searchtype'] = $request->getParam('searchtype', Application_Util_Searchtypes::ADVANCED_SEARCH);

        foreach (['author', 'title', 'persons', 'referee', 'abstract', 'fulltext', 'year'] as $fieldname) {
            $fieldvalue = $request->getParam($fieldname, '');
            if ($fieldvalue !== '') {
                $params[$fieldname]              = $fieldvalue;
                $params[$fieldname . 'modifier'] = $request->getParam(
                    $fieldname . 'modifier',
                    Query::SEARCH_MODIFIER_CONTAINS_ALL
                );
            }
        }
        return array_merge($params, $this->getFilterParams($request));
    }

    /**
     * @param Zend_Controller_Request_Http $request
     * @return array
     */
    public function createBasicSearchParams($request)
    {
        return [
            'start'     => $request->getParam('start', '0'),
            'rows'      => $request->getParam('rows', Query::getDefaultRows()),
            'sortfield' => $request->getParam('sortfield', 'score'),
            'sortorder' => $request->getParam('sortorder', 'desc'),
        ];
    }

    /**
     * @param Zend_Controller_Request_Http $request
     * @return bool
     */
    public function isSimpleSearchRequestValid($request)
    {
        $query = $request->getParam('query');
        return $query !== null && trim($query) !== '';
    }

    /**
     * @param Zend_Controller_Request_Http $request
     * @return bool
     */
    public function isAdvancedSearchRequestValid($request)
    {
        foreach (['author', 'title', 'persons', 'referee', 'abstract', 'fulltext', 'year'] as $fieldname) {
            $fieldvalue = $request->getParam($fieldname);
            if ($fieldvalue !== null && trim($fieldvalue) !== '') {
                return true;
            }
        }
        return false;
    }

    /**
     * Creates an URL to execute a search. The URL will be mapped to:
     * module=solrsearch, controller=index, action=search
     *
     * @param array $params
     * @param bool  $rss
     * @return string
     */
    public static function createSearchUrlArray($params = [], $rss = false)
    {
        $url = [
            'module'     => $rss ? 'rss' : 'solrsearch',
            'controller' => 'index',
            'action'     => $rss ? 'index' : 'search',
        ];
        foreach ($params as $key => $value) {
            $url[$key] = $value;
        }
        if ($rss) {
            // some ignores some search related parameters
            $url['rows']      = null;
            $url['start']     = null;
            $url['sortfield'] = null;
            $url['sortorder'] = null;
            $url['browsing']  = null;
        }
        return $url;
    }
}
