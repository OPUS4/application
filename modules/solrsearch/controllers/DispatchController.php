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
 * @package     Module_Solrsearch
 * @author      Julian Heise <heise@zib.de>
 * @author      Sascha Szott <szott@zib.de>
 * @copyright   Copyright (c) 2008-2011, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class Solrsearch_DispatchController extends Controller_Action {

    private $log;

    public function  init() {
        parent::init();
        $this->log = Zend_Registry::get('Zend_Log');
    }

    public function indexAction() {
        $this->log->debug('Received new search request. Redirecting to search action of IndexController.');
        $params = array();
        $action = 'search';

        $searchtype = $this->getRequest()->getParam('searchtype', 'invalid searchtype');
        if($searchtype === Util_Searchtypes::SIMPLE_SEARCH) {
            if (!$this->isSimpleSearchRequestValid()) {
                $action = 'invalidsearchterm';
                $params = array('searchtype' => Util_Searchtypes::SIMPLE_SEARCH);
            }
            else {
                $params= $this->createSimpleSearchUrlParams();
            }
        }
        else if ($searchtype === Util_Searchtypes::ADVANCED_SEARCH || $searchtype === Util_Searchtypes::AUTHOR_SEARCH) {
            if (!$this->isAdvancedSearchRequestValid()) {
                $action = 'invalidsearchterm';
                $params = array('searchtype' =>  $searchtype);
            }
            else {
                $params = $this->createAdvancedSearchUrlParams();
            }
        }
        return $this->_redirectToPermanentAndExit($action, null, 'index', null, $params);
    }

    private function isSimpleSearchRequestValid() {
        $query = $this->getRequest()->getParam('query');
        return !is_null($query) && trim($query) != '';
    }

    private function isAdvancedSearchRequestValid() {
        foreach (array('author', 'title', 'referee', 'abstract', 'fulltext',  'year') as $fieldname) {
            $fieldvalue = $this->getRequest()->getParam($fieldname);
            if (!is_null($fieldvalue) && trim($fieldvalue) != '') {
                return true;
            }
        }
        return false;
    }

    private function createSimpleSearchUrlParams() {
        return array(
            'searchtype' => $this->getRequest()->getParam('searchtype', Util_Searchtypes::SIMPLE_SEARCH),
            'start' => $this->getRequest()->getParam('start', '0'),
            'rows' => $this->getRequest()->getParam('rows', '10'),
            'query' => $this->getRequest()->getParam('query', '*:*'),
            'sortfield'  => $this->getRequest()->getParam('sortfield', 'score'),
            'sortorder' => $this->getRequest()->getParam('sortorder', 'desc')
        );
    }

    private function createAdvancedSearchUrlParams() {
        $params = array (
            'searchtype' => $this->getRequest()->getParam('searchtype', Util_Searchtypes::ADVANCED_SEARCH),
            'start' => $this->getRequest()->getParam('start', '0'),
            'rows' => $this->getRequest()->getParam('rows', '10'),
            'sortfield' => $this->getRequest()->getParam('sortfield', 'score'),
            'sortorder' => $this->getRequest()->getParam('sortorder', 'desc')
        );

        foreach (array('author', 'title', 'abstract', 'fulltext', 'year', 'referee') as $fieldname) {
            $fieldvalue = $this->getRequest()->getParam($fieldname, '');
            if ($fieldvalue !== '') {
                $params[$fieldname] = $fieldvalue;
                $params[$fieldname . 'modifier'] = $this->getRequest()->getParam(
                        $fieldname . 'modifier', Opus_SolrSearch_Query::SEARCH_MODIFIER_CONTAINS_ALL);
            }
        }
        return $params;
    }
}
?>