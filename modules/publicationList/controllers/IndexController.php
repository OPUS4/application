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
 * @category    Module_SolrSearch
 * @author      Julian Heise <heise@zib.de>
 * @author      Sascha Szott <szott@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id:$
 */

class PublicationList_IndexController extends Controller_Action {

    const SIMPLE_SEARCH = 'simple';
    const ADVANCED_SEARCH = 'advanced';
    const AUTHOR_SEARCH = 'authorsearch';
    const COLLECTION_SEARCH = 'collection';
    const LATEST_SEARCH = 'latest';

    
    private $log;
    private $query;
    private $numOfHits;
    private $searchtype;
    private $resultList;
    private $publicationSite;
   
    public function  init() {
        $this->log = Zend_Registry::get('Zend_Log');
    }


    public function searchAction() {
        $this->query = $this->buildQuery();  /* OKAY*/
        $this->performSearch();
        $this->createPublicationLists();
        $this->setPublicationView();

        if ($this->getRequest()->getParam("theme") === 'plain') {
            $this->render('plainresults');
        }
        else {
            $this->render('results');
        }
    }


    private function buildQuery() {
        if (is_null($this->getRequest()->getParams()))
            throw new Application_Exception("Unable to read request data. Search cannot be performed.");

        if (is_null($this->getRequest()->getParam('searchtype')))
            throw new Application_Exception("Unable to create query for unspecified searchtype");

        $query = null;
        $this->searchtype = $this->getRequest()->getParam('searchtype');
        if ($this->searchtype === self::COLLECTION_SEARCH)
            $query = $this->createCollectionSearchQuery();
        else
            throw new Application_Exception("Unable to create query for searchtype " . $this->searchtype);

        $this->validateQuery($query);
        return $query;
    }



    private function createCollectionSearchQuery() {
        $this->log->debug("Constructing query for collection search.");

        $collectionId = $this->prepareChildren();

        $query = new Opus_SolrSearch_Query(Opus_SolrSearch_Query::SIMPLE);
        $query->setStart($this->getRequest()->getParam('start', Opus_SolrSearch_Query::DEFAULT_START));
        $query->setCatchAll('*:*');
        //$query->setRows($this->getRequest()->getParam('rows', Opus_SolrSearch_Query::DEFAULT_ROWS));
        $query->setRows(1000);
        $query->setSortField($this->getRequest()->getParam('sortfield', Opus_SolrSearch_Query::DEFAULT_SORTFIELD));
        $query->setSortOrder($this->getRequest()->getParam('sortorder', Opus_SolrSearch_Query::DEFAULT_SORTORDER));

        $query->addFilterQuery('collection_ids:' . $collectionId);
        $this->log->debug("Query $query complete");
        return $query;
    }


    private function prepareChildren() {
        $collectionList = null;
        try {
            $collectionList = new SolrSearch_Model_CollectionList($this->getRequest()->getParam('id'));
        }
        catch (SolrSearch_Model_Exception $e) {
            $this->log->debug($e->getMessage());
            $this->_redirectToAndExit('index', '', 'browse', null, array(), true);
        }

        $this->view->children = $collectionList->getChildren();
        $this->view->parents = $collectionList->getParents();
        $this->view->collectionRoleTitle = $this->view->translate($collectionList->getCollectionRoleTitle());

        if ($collectionList->isRootCollection()) {
            $this->view->title = $this->view->translate($collectionList->getTitle());
        }
        else {
            $this->view->title = $collectionList->getTitle();
        }

        // Get the theme assigned to this collection iff usertheme is
        // set in the request.  To enable the collection theme, add
        // /usetheme/1/ to the URL.
        $usetheme = $this->getRequest()->getParam("usetheme");
           if (!is_null($usetheme) && 1 === (int) $usetheme) {
            $this->_helper->layout->setLayoutPath(APPLICATION_PATH . '/public/layouts/' . $collectionList->getTheme());
        }
        return $collectionList->getCollectionId();
    }

    private function validateQuery($query) {
        // TODO check if the two subsequent rows checks are obsolete
        if($query->getRows() > 1000) {
            $this->log->warn("Values greater than 100 are currently not allowed for the rows paramter.");
            $query->setRows('1000');
        }
        if($query->getRows() < 1) {
            $this->log->warn("row parameter is smaller than 1: adjusting to 1.");
            $query->setRows('1');
        }
        if ($query->getStart() < 0) {
            $this->log->warn("A negative start parameter is ignored.");
            $query->setStart('0');
        }
    }

    private function performSearch() {
        $this->log->debug('performing search');
        $searcher = new Opus_SolrSearch_Searcher();
        $this->resultList = $searcher->search($this->query);
        $this->numOfHits = $this->resultList->getNumberOfHits();
        $this->log->debug("resultlist: $this->resultList");
    }

    private function createPublicationLists() {
        $this->publicationSite = new PublicationList_Model_PublicationSite();
        foreach ($this->resultList->getResults() as $resultHit) {
            $publication = new PublicationList_Model_Publication($resultHit->getId());
            $year = $publication->getDoc()->getPublishedYear();
            $inListe = 0;
            foreach ($this->publicationSite->getSingleList() as $sl) {
                if ($sl->getYear() === $year) {
                    $sl->addPublication($publication);
                    $inListe = 1;
                }
            }
            if ($inListe === 0) {
                $sl = new PublicationList_Model_SingleList($year);
                $sl->addPublication($publication);
                $this->publicationSite->addSingleList($sl);
            }
        }
        $this->publicationSite->orderSingleLists();

    }


    private function setPublicationView() {
        if (!is_null($this->getRequest()->getParam("theme"))) { $this->setTheme($this->getRequest()->getParam("theme")); };
        $this->view->results = $this->publicationSite->getSingleList();
    }

    private function setTheme($theme) {
        $this->_helper->layout->setLayoutPath(APPLICATION_PATH . '/public/layouts/' . $theme);
    }



    /**
     * Creates an URL to execute a search. The URL will be mapped to:
     * module=solrsearch, controller=index, action=search
     */
    
    public static function createSearchUrlArray($params = array()) {
        $url = array(
            'module' => 'solrsearch',
            'controller' => 'index',
            'action' => 'search');
        foreach($params as $key=>$value) {
            $url[$key]=$value;
        }
        return $url;
    }

}
?>
