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
 * @copyright   Copyright (c) 2009, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\Collection;
use Opus\Common\Person;
use Opus\Common\Series;

/**
 * Administrative work with document metadata.
 *
 * TODO handle state as a facet
 * TODO redirect to remove invalid parameters from URL
 */
class Admin_DocumentsController extends Application_Controller_Action
{
    public const PARAM_HITSPERPAGE    = 'hitsperpage'; // TODO rename to 'limit'
    public const PARAM_STATE          = 'state';
    public const PARAM_SORT_BY        = 'sort_order'; // TODO rename to 'sortby'
    public const PARAM_SORT_DIRECTION = 'sort_reverse'; // TODO rename to 'order'

    /** @var string[] */
    protected $sortingOptions = ['id', 'title', 'author', 'publicationDate', 'docType'];

    /** @var string[] */
    protected $docOptions = ['all', 'unpublished', 'inprogress', 'audited', 'published', 'restricted', 'deleted'];

    /** @var int */
    private $maxDocsDefault = 10;

    /** @var string */
    private $stateOptionDefault = 'unpublished';

    /** @var string */
    private $sortingOptionDefault = 'id';

    /** @var Zend_Session_Namespace */
    private $namespace;

    public function init()
    {
        parent::init();

        $config                         = $this->getConfig();
        $this->view->linkToAuthorSearch = isset($config->admin->documents->linkToAuthorSearch) &&
            filter_var($config->admin->documents->linkToAuthorSearch, FILTER_VALIDATE_BOOLEAN);

        if (isset($config->admin->documents->maxDocsDefault)) {
            $this->maxDocsDefault = $config->admin->documents->maxDocsDefault;
        } else {
            $this->maxDocsDefault = 10;
        }

        if (isset($config->admin->documents->defaultview)) {
            $default = $config->admin->documents->defaultview;
            if (! in_array($default, $this->docOptions)) {
                $this->getLogger()->err("Option 'admin.documents.defaultview' hat ungegueltigen Wert '$default'.");
            }
            $this->stateOptionDefault = $default;
        }
    }

    /**
     * Display documents (all or filtered by state)
     *
     * TODO separate out collection and series mode (handle as facets?)
     * TODO cleanup
     */
    public function indexAction()
    {
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
        $state       = $this->getStateOption($data);
        $sortOrder   = $this->getSortingOption($data);

        if (! empty($collectionId)) {
            // TODO add as filter facet
            $collection             = Collection::get($collectionId);
            $result                 = $collection->getDocumentIds();
            $this->view->collection = $collection;
            if ($collection->isRoot()) {
                $collectionRoleName         = 'default_collection_role_' . $collection->getRole()->getDisplayName();
                $this->view->collectionName = $this->view->translate($collectionRoleName);
                if ($this->view->collectionName === $collectionRoleName) {
                    $this->view->collectionName = $collection->getRole()->getDisplayName();
                }
            } else {
                $this->view->collectionName = $collection->getNumberAndName();
            }
        } elseif (! empty($seriesId)) {
            // TODO add as filter facet
            $series             = Series::get($seriesId);
            $this->view->series = $series;
            $result             = $series->getDocumentIdsSortedBySortKey();
        } else {
            if (array_key_exists('last_name', $data)) {
                $person              = [];
                $person['last_name'] = $data['last_name'];

                if (array_key_exists('first_name', $data)) {
                    $person['first_name'] = $data['first_name'];
                }

                if (array_key_exists('identifier_orcid', $data)) {
                    $person['identifier_orcid'] = $data['identifier_orcid'];
                }

                if (array_key_exists('identifier_gnd', $data)) {
                    $person['identifier_gnd'] = $data['identifier_gnd'];
                }

                if (array_key_exists('identifier_misc', $data)) {
                    $person['identifier_misc'] = $data['identifier_misc'];
                }

                if ($state === null) {
                    $state = 'all';
                }

                $role = $this->getParam('role', 'all');

                $persons = Person::getModelRepository();

                $result = $persons->getPersonDocuments($person, $state, $role, $sortOrder, ! $sortReverse);

                $this->view->person = $person;

                $this->preparePersonRoleLinks();
                $this->view->role = $role;
            } else {
                $result = $this->_helper->documents($sortOrder, ! $sortReverse, $state);
            }
        }

        $this->view->sort_reverse  = $sortReverse;
        $this->view->sortDirection = $sortReverse ? 'descending' : 'ascending';
        $this->view->sort_order    = $sortOrder;
        $this->view->state         = $state;
        $this->view->filter        = $filter;

        $this->prepareDocStateLinks();

        $urlCallId               = [
            'module'     => 'admin',
            'controller' => 'document',
            'action'     => 'index',
        ];
        $this->view->url_call_id = $this->view->url($urlCallId, 'default', true);

        $this->prepareSortingLinks();

        $paginator = Zend_Paginator::factory($result);
        $page      = 1;
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

        $this->view->bibtexImportLink = $this->view->url(
            ['module' => 'admin', 'controller' => 'import', 'action' => 'bibtex'],
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
     *
     * @param array $params
     * @return int
     */
    protected function getItemCountPerPage($params)
    {
        $value = $this->getOption(self::PARAM_HITSPERPAGE, $params);

        if ($value === 'all' || $value < 0) {
            $value = 0;
        }

        if (! is_numeric($value)) {
            $value = $this->maxDocsDefault;
        }

        $this->setOption(self::PARAM_HITSPERPAGE, $value);

        return $value;
    }

    /**
     * Ermittelt in welchem Status die angezeigten Dokumente sein sollen.
     *
     * @param array $params Request parameter
     * @return string
     */
    protected function getStateOption($params)
    {
        $value = $this->getOption(self::PARAM_STATE, $params);

        if (! in_array($value, $this->docOptions)) {
            $value = $this->stateOptionDefault;
        }

        $this->setOption(self::PARAM_STATE, $value);

        return $value;
    }

    /**
     * Ermittelt wonach die Dokumente sortiert werden sollen.
     *
     * @param array $params Request Parameter
     * @return string
     */
    protected function getSortingOption($params)
    {
        $value = $this->getOption(self::PARAM_SORT_BY, $params);

        if (! in_array($value, $this->sortingOptions)) {
            $value = $this->sortingOptionDefault;
        }

        $this->setOption(self::PARAM_SORT_BY, $value);

        return $value;
    }

    /**
     * Ermittelt die Sortierrrichtung (aufwaerts/abwaerts).
     *
     * @param array $params Request Parameter
     * @return bool
     */
    protected function getSortingDirection($params)
    {
        $value = $this->getOption(self::PARAM_SORT_DIRECTION, $params);

        if (! is_bool($value) && ! is_numeric($value)) {
            $value = false;
        } else {
            $value = $value ? true : false;
        }

        $this->setOption(self::PARAM_SORT_DIRECTION, $value);

        return $value;
    }

    /**
     * Holt eine Option vom Request oder der Session.
     *
     * @param string $name Name der Option
     * @param array  $params Request Parameter
     * @return mixed|null
     */
    protected function getOption($name, $params)
    {
        $namespace = $this->getSession();

        if (array_key_exists($name, $params)) {
            $value = $params[$name];
        } else {
            $value = $namespace->$name ?? null;
        }

        return $value;
    }

    /**
     * Setzt Option in der Session.
     *
     * @param string $name Name der Option
     * @param string $value Optionswert
     */
    protected function setOption($name, $value)
    {
        $namespace        = $this->getSession();
        $namespace->$name = $value;
    }

    /**
     * Liefert die Session für diesen Controller.
     *
     * @return Zend_Session_Namespace
     */
    protected function getSession()
    {
        if ($this->namespace === null) {
            $this->namespace = new Zend_Session_Namespace('Admin');
        }

        return $this->namespace;
    }

    /**
     * Bereitet die Links für die Auswahl der Anzahl der Dokumente pro Seite vor.
     */
    protected function prepareItemCountLinks()
    {
        $config = $this->getConfig();

        if (isset($config->admin->documents->maxDocsOptions)) {
            $options = $config->admin->documents->maxDocsOptions;
        } else {
            $options = "10,50,100,all";
        }

        $itemCountOptions = explode(',', $options);

        $itemCountLinks = [];

        foreach ($itemCountOptions as $option) {
            $link = [];

            $link['label'] = $option;
            $link['url']   = $this->view->url([self::PARAM_HITSPERPAGE => $option], null, false);

            $itemCountLinks[$option] = $link;
        }

        $this->view->itemCountLinks = $itemCountLinks;
    }

    /**
     * Bereitet die Links für Status Optionen vor.
     */
    protected function prepareDocStateLinks()
    {
        $registers = [];

        foreach ($this->docOptions as $name) {
            $params           = ['module' => 'admin', 'controller' => 'documents', 'action' => 'index'];
            $params['state']  = $name;
            $url              = $this->view->url($params, null, false);
            $registers[$name] = $url;
        }

        $this->view->registers = $registers;
    }

    /**
     * Bereitet die Links für die Sortier Optionen vor.
     */
    protected function prepareSortingLinks()
    {
        $sortingLinks = [];

        foreach ($this->sortingOptions as $name) {
            $params              = [
                'module'     => 'admin',
                'controller' => 'documents',
                'action'     => 'index',
                'sort_order' => $name,
            ];
            $sortUrl             = $this->view->url($params, 'default', false);
            $sortingLinks[$name] = $sortUrl;
        }

        $this->view->sortingLinks = $sortingLinks;

        $directionLinks = [];

        $directionLinks['ascending']  = $this->view->url(['sort_reverse' => '0'], 'default', false);
        $directionLinks['descending'] = $this->view->url(['sort_reverse' => '1'], 'default', false);

        $this->view->directionLinks = $directionLinks;
    }

    protected function preparePersonRoleLinks()
    {
        $roles = ['all', 'author', 'editor', 'contributor', 'referee', 'other', 'translator', 'submitter', 'advisor'];

        $personRoles = [];

        foreach ($roles as $role) {
            $params             = [
                'module'     => 'admin',
                'controller' => 'documents',
                'action'     => 'index',
                'role'       => $role,
            ];
            $roleUrl            = $this->view->url($params, 'default', false);
            $personRoles[$role] = $roleUrl;
        }

        $this->view->personRoles = $personRoles;
    }
}
