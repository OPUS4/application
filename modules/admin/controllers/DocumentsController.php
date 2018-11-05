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
 * @category    Application
 * @package     Module_Admin
 * @author      Henning Gerhardt (henning.gerhardt@slub-dresden.de)
 * @author      Oliver Marahrens <o.marahrens@tu-harburg.de>
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2009-2017, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Administrative work with document metadata.
 *
 * @category    Application
 * @package     Module_Admin
 *
 * TODO handle state as a facet
 * TODO redirect to remove invalid parameters from URL
 */
class Admin_DocumentsController extends Application_Controller_Action {

    const PARAM_HITSPERPAGE = 'hitsperpage'; // TODO rename to 'limit'
    const PARAM_STATE = 'state';
    const PARAM_SORT_BY = 'sort_order'; // TODO rename to 'sortby'
    const PARAM_SORT_DIRECTION = 'sort_reverse'; // TODO rename to 'order'

    protected $_sortingOptions = array('id', 'title', 'author', 'publicationDate', 'docType');

    protected $_docOptions = array('all', 'unpublished', 'inprogress', 'audited', 'published', 'restricted', 'deleted');

    private $_maxDocsDefault = 10;
    private $_stateOptionDefault = 'unpublished';
    private $_sortingOptionDefault = 'id';

    private $_namespace;

    public function init() {
        parent::init();

        $config = $this->getConfig();

        if (isset($config->admin->documents->linkToAuthorSearch)) {
            $this->view->linkToAuthorSearch = $config->admin->documents->linkToAuthorSearch;
        }
        else {
            $this->view->linkToAuthorSearch = 0;
        }

        if (isset($config->admin->documents->maxDocsDefault)) {
            $this->_maxDocsDefault = $config->admin->documents->maxDocsDefault;
        }
        else {
            $this->_maxDocsDefault = 10;
        }

        if (isset($config->admin->documents->defaultview)) {
            $default = $config->admin->documents->defaultview;
            if (!in_array($default, $this->_docOptions)) {
                $this->getLogger()->err("Option 'admin.documents.defaultview' hat ungegueltigen Wert '$default'.");
            }
            $this->_stateOptionDefault = $default;
        }
    }

    /**
     * Display documents (all or filtered by state)
     *
     * @return void
     *
     * TODO separate out collection and series mode (handle as facets?)
     * TODO cleanup
     */
    public function indexAction() {
        $this->view->title = 'admin_documents_index';

        $data = $this->_request->getParams();

        $filter = $this->_getParam("filter");

        $page = 1;
        if (array_key_exists('page', $data)) {
            // set page if requested
            $page = $data['page'];
        }

        $collectionId = null;
        if (array_key_exists('collectionid', $data)) {
            $collectionId = $data['collectionid'];
        }

        $seriesId = null;
        if (array_key_exists('seriesid', $data)) {
            $seriesId = $data['seriesid'];
        }

        $sortReverse = $this->getSortingDirection($data);
        $state = $this->getStateOption($data);
        $sortOrder = $this->getSortingOption($data);

        if (!empty($collectionId)) {
            // TODO add as filter facet
            $collection = new Opus_Collection($collectionId);
            $result = $collection->getDocumentIds();
            $this->view->collection = $collection;
            if ($collection->isRoot()) {
                $collectionRoleName = 'default_collection_role_' . $collection->getRole()->getDisplayName();
                $this->view->collectionName = $this->view->translate($collectionRoleName);
                if ($this->view->collectionName == $collectionRoleName) {
                    $this->view->collectionName = $collection->getRole()->getDisplayName();
                }
            }
            else {
                $this->view->collectionName = $collection->getNumberAndName();
            }
        }
        else if (!empty($seriesId)) {
            // TODO add as filter facet
            $series = new Opus_Series($seriesId);
            $this->view->series = $series;
            $result = $series->getDocumentIdsSortedBySortKey();
        }
        else {
            if (array_key_exists('last_name', $data))
            {
                $person = array();
                $person['last_name'] = $data['last_name'];

                if (array_key_exists('first_name', $data))
                {
                    $person['first_name'] = $data['first_name'];
                }

                if (array_key_exists('identifier_orcid', $data))
                {
                    $person['identifier_orcid'] = $data['identifier_orcid'];
                }

                if (array_key_exists('identifier_gnd', $data))
                {
                    $person['identifier_gnd'] = $data['identifier_gnd'];
                }

                if (array_key_exists('identifier_misc', $data))
                {
                    $person['identifier_misc'] = $data['identifier_misc'];
                }

                if (is_null($state))
                {
                    $state = 'all';
                }

                $role = $this->getParam('role', 'all');

                $result = Opus_Person::getPersonDocuments($person, $state, $role, $sortOrder, !$sortReverse);

                $this->view->person = $person;

                $this->preparePersonRoleLinks();
                $this->view->role = $role;
            }
            else
            {
                $result = $this->_helper->documents($sortOrder, !$sortReverse, $state);
            }
        }

        $this->view->sort_reverse = $sortReverse;
        $this->view->sortDirection = ($sortReverse) ? 'descending' : 'ascending';
        $this->view->sort_order = $sortOrder;
        $this->view->state = $state;
        $this->view->filter = $filter;

        $this->prepareDocStateLinks();

        $urlCallId = array(
            'module' => 'admin',
            'controller' => 'document',
            'action' => 'index'
        );
        $this->view->url_call_id = $this->view->url($urlCallId, 'default', true);

        $this->prepareSortingLinks();

        $paginator = Zend_Paginator::factory($result);
        $page = 1;
        if (array_key_exists('page', $data)) {
            // paginator
            $page = $data['page'];
        }
        $this->view->maxHitsPerPage = $this->getItemCountPerPage($data);
        $paginator->setItemCountPerPage($this->view->maxHitsPerPage);
        $paginator->setCurrentPageNumber($page);
        $this->view->paginator = $paginator;
        $this->prepareItemCountLinks();

        $this->view->createLink = $this->view->url(
            ['module' => 'admin', 'controller' => 'document', 'action' => 'create'],
            'default',
            true
        );
    }

    /**
     * Liefert die Zahl der Dokumente, die auf einer Seite angezeigt werden soll.
     *
     * Der Wert wird aus verschiedenen Quellen ermittelt
     *
     * - Request Parameter
     * - Session
     * - Konfiguration?
     * - Default
     */
    protected function getItemCountPerPage($params) {
        $value = $this->getOption(self::PARAM_HITSPERPAGE, $params);

        if ($value === 'all' || $value < 0) {
            $value = 0;
        }

        if (!is_numeric($value)) {
            $value = $this->_maxDocsDefault;
        }

        $this->setOption(self::PARAM_HITSPERPAGE, $value);

        return $value;
    }

    /**
     * Ermittelt in welchem Status die angezeigten Dokumente sein sollen.
     * @param $params Request parameter
     * @return string
     */
    protected function getStateOption($params) {
        $value = $this->getOption(self::PARAM_STATE, $params);

        if (!in_array($value, $this->_docOptions)) {
            $value = $this->_stateOptionDefault;
        }

        $this->setOption(self::PARAM_STATE, $value);

        return $value;
    }

    /**
     * Ermittelt wonach die Dokumente sortiert werden sollen.
     * @param $params Request Parameter
     * @return string
     */
    protected function getSortingOption($params) {
        $value = $this->getOption(self::PARAM_SORT_BY, $params);

        if (!in_array($value, $this->_sortingOptions)) {
            $value = $this->_sortingOptionDefault;
        }

        $this->setOption(self::PARAM_SORT_BY, $value);

        return $value;
    }

    /**
     * Ermittelt die Sortierrrichtung (aufwaerts/abwaerts).
     * @param $params Request Parameter
     * @return bool
     */
    protected function getSortingDirection($params) {
        $value = $this->getOption(self::PARAM_SORT_DIRECTION, $params);

        if (!is_bool($value) && !is_numeric($value)) {
            $value = false;
        }
        else {
            $value = ($value) ? true : false;
        }

        $this->setOption(self::PARAM_SORT_DIRECTION, $value);

        return $value;
    }

    /**
     * Holt eine Option vom Request oder der Session.
     * @param $name Name der Option
     * @param $params Request Parameter
     * @return mixed|null
     */
    protected function getOption($name, $params) {
        $namespace = $this->getSession();

        if (array_key_exists($name, $params)) {
            $value = $params[$name];
        }
        else {
            $value = (isset($namespace->$name)) ? $namespace->$name : null;
        }

        return $value;
    }

    /**
     * Setzt Option in der Session.
     *
     * @param $name Name der Option
     * @param $value Optionswert
     */
    protected function setOption($name, $value) {
        $namespace = $this->getSession();
        $namespace->$name = $value;
    }

    /**
     * Liefert die Session für diesen Controller.
     * @return Zend_Session_Namespace
     */
    protected function getSession() {
        if (is_null($this->_namespace)) {
            $this->_namespace = new Zend_Session_Namespace('Admin');
        }

        return $this->_namespace;
    }

    /**
     * Bereitet die Links für die Auswahl der Anzahl der Dokumente pro Seite vor.
     */
    protected function prepareItemCountLinks() {
        $config = $this->getConfig();

        if (isset($config->admin->documents->maxDocsOptions)) {
            $options = $config->admin->documents->maxDocsOptions;
        }
        else {
            $options ="10,50,100,all";
        }

        $itemCountOptions = explode(',', $options);

        $itemCountLinks = array();

        foreach ($itemCountOptions as $option) {
            $link = array();

            $link['label'] = $option;
            $link['url'] = $this->view->url(array(self::PARAM_HITSPERPAGE => $option), null, false);

            $itemCountLinks[$option] = $link;
        }

        $this->view->itemCountLinks = $itemCountLinks;
    }

    /**
     * Bereitet die Links für Status Optionen vor.
     */
    protected function prepareDocStateLinks() {
        $registers = array();

        foreach ($this->_docOptions as $name) {
            $params = array('module' => 'admin', 'controller'=>'documents', 'action'=>'index');
            $params['state'] = $name;
            $url = $this->view->url($params, null, false);
            $registers[$name] = $url;
        }

        $this->view->registers = $registers;
    }

    /**
     * Bereitet die Links für die Sortier Optionen vor.
     */
    protected function prepareSortingLinks() {
        $sortingLinks = array();

        foreach ($this->_sortingOptions as $name) {
            $params = array(
                'module' => 'admin',
                'controller' => 'documents',
                'action' => 'index',
                'sort_order' => $name
            );
            $sortUrl = $this->view->url($params, 'default', false);
            $sortingLinks[$name] = $sortUrl;
        }

        $this->view->sortingLinks = $sortingLinks;

        $directionLinks = array();

        $directionLinks['ascending'] = $this->view->url(array('sort_reverse' => '0'), 'default', false);
        $directionLinks['descending'] = $this->view->url(array('sort_reverse' => '1'), 'default', false);

        $this->view->directionLinks = $directionLinks;
    }

    protected function preparePersonRoleLinks()
    {
        $roles = array('all', 'author', 'editor', 'contributor', 'referee', 'other', 'translator', 'submitter', 'advisor');

        $personRoles = array();

        foreach ($roles as $role)
        {
            $params = array(
                'module' => 'admin',
                'controller' => 'documents',
                'action' => 'index',
                'role' => $role
            );
            $roleUrl = $this->view->url($params, 'default', false);
            $personRoles[$role] = $roleUrl;
        }

        $this->view->personRoles = $personRoles;
    }

}
