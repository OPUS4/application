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
 * @author      Felix Ostrowski <ostrowski@hbz-nrw.de>
 * @author      Tobias Tappe <tobias.tappe@uni-bielefeld.de>
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Controller for administration of collections.
 *
 * @category    Framework
 * @package     Module_Admin
 */
class Admin_CollectionController extends Controller_Action {

    /**
     * Setup theme path
     *
     * @return void
     */
    public function init() {
        parent::init();
        Opus_Collection::setThemesPath('./layouts');
    }

    /**
     * List all available collection role instances
     *
     * @return void
     */
    public function indexAction() {
        $entries = Opus_CollectionRole::getAll();
        $this->view->entries = array();
        foreach ($entries as $entry) {
            $this->view->entries[] = array('id'=>$entry->getId(), 'name'=>$entry->getDisplayName());
        }
        $theme =Zend_Registry::getInstance()->get('Zend_Config')->theme;
        if (true === empty($theme)) {
            $theme = 'default';
        }
        $this->view->theme = $theme;
        $this->view->layoutPath = $this->view->baseUrl() .'/layouts/'. $theme;
    }

    /**
     * Edits a collection instance
     *
     * @return void
     */
    public function editAction() {
        $role = $this->getRequest()->getParam('role');
        $this->view->role = $role;
        $path = $this->getRequest()->getParam('path');
        $form_builder = new Form_Builder();
        if (1 == $role) {
            $collection = new Opus_OrganisationalUnits;
        } else {
            $collection = new Opus_CollectionRole($role);
        }
        if (true === isset($path)) {
            $trail = explode('-', $path);
            foreach($trail as $step) {
                $collection = $collection->getSubCollection($step);
            }
        }
        $session = new Zend_Session_Namespace('collection');
        $session->collection = $collection;
        $filter = $this->__createFilter($collection);
        $collectionForm = $form_builder->build($filter);
        $action_url = $this->view->url(array('action' => 'create'));
        $collectionForm->setAction($action_url);
        $this->view->form = $collectionForm;
    }

    /**
     * Create a new collection instance
     *
     * @return void
     */
    public function newAction() {
        $role = $this->getRequest()->getParam('role');
        $form_builder = new Form_Builder();
        if (true === isset($role) and 1 == $role) {
            $collection = new Opus_OrganisationalUnit;
        } else if (true === isset($role)) {
            $collection = new Opus_Collection(null, $role);
        } else {
            $collection = new Opus_CollectionRole();
            $allRoles = Opus_CollectionRole::getAll();
            $countRoles = count($allRoles);
            $pos_field = $collection->getField('Position');
            $pos_field->setDefault(array_combine(range(1,$countRoles+1),range(1,$countRoles+1)))->setSelection(true);

        }
        $session = new Zend_Session_Namespace('collection');
        $session->collection = $collection;
        $filter = $this->__createFilter($collection);
        $collectionForm = $form_builder->build($filter);
        $action_url = $this->view->url(array('action' => 'create'));
        $collectionForm->setAction($action_url);
        $this->view->form = $collectionForm;
    }

    /**
     * Deletes a collection or collection role instance
     *
     * @return void
     */
    public function manageAction() {
        $role = $this->getRequest()->getUserParam('role');
        $path = $this->getRequest()->getUserParam('path');
        $collection = new Opus_CollectionRole($role);

        // Store whether we're handling a collection or a collection role.
        $handlingCollection = (true === isset($path));
        if (true === $handlingCollection) {
            $parentCollId = 1;
            $trail = explode('-', $path);
            foreach($trail as $step) {
                $parentCollId = $collection->getId();
                $collection = $collection->getSubCollection($step);
            }
            $path = implode('-', array_slice($trail, 0, sizeof($trail) - 1));
        }
        if (false === is_null($this->_request->getParam('deleteall'))) {
            // Delete collection.
            $collection->delete();
        } else if (false === is_null($this->_request->getParam('undeleteall'))) {
            // Un-Delete collection.
            $collection->undelete();
        } else if (false === is_null($this->_request->getParam('delete'))) {
            // Delete collection.
            $collection->deletePosition($parentCollId);
        } else if (false === is_null($this->_request->getParam('move_up'))) {
            // Move collection up by one position.
            $collection->setPosition($collection->getPosition() - 1);
            $collection->store();
        } else if (false === is_null($this->_request->getParam('move_down'))) {
            // Move collection up by one position.
            $collection->setPosition($collection->getPosition() + 1);
            $collection->store();
        }

        if (true === $handlingCollection) {
            $this->_redirectTo('Collection successfully deleted.', 'show', null, null,
                    array('role' => $role, 'path' => $path));
        } elseif (false === is_null($this->_request->getParam('deleteall'))) {
            $this->_redirectTo('Role successfully deleted.', 'index');
        } else {
            $this->_redirectTo('', 'index');
        }
    }

    /**
     * Display subcollections of collections and collection roles.
     *
     * @return void
     */
    public function showAction() {

        $theme =Zend_Registry::getInstance()->get('Zend_Config')->theme;
        if (true === empty($theme)) {
            $theme = 'default';
        }
        $this->view->theme = $theme;
        $this->view->layoutPath = $this->view->baseUrl() .'/layouts/'. $theme;



        $roleId = (int) $this->getRequest()->getParam('role');
        $collection = new Opus_CollectionRole($roleId);
        $roleName = $collection->getDisplayName();
        $path = $this->getRequest()->getParam('path');
        $copy = $this->getRequest()->getParam('copy');
        if (true === isset($copy)) {
            $cpCollection = $collection;
            $trail = explode('-', $copy);
            foreach($trail as $step) {
                $cpCollection = $cpCollection->getSubCollection($step);
            }
            $copyId = $cpCollection->getId();
            unset($position);
        } else {
            $copyId = 0;
        }
        $subcollections     = array();
        $breadcrumb         = array();
        $severalAppearances = array();
        $visibility = array();
        $nameLength = 0;
        if (true === isset($path)) {
            $trail = explode('-', $path);
            foreach($trail as $step) {
                if (false === isset($position)) {
                    $position = $step;
                } else {
                    $position .= '-' . $step;
                }
                $collection = $collection->getSubCollection($step);
                $breadcrumb[$position] = $collection->getDisplayName();
            }
            foreach($collection->getSubCollection() as $i => $subcollection) {
                $severalAppearances[$path . '-' . $i] = (true === $subcollection->getSeveralAppearances())?'several':'unique';
                $visibility[$path . '-' . $i] = (true === $subcollection->getVisibility())?'visible':'hidden';
                // Alle Parents der aktuellen Subcollection
                $parentIds = Opus_Collection_Information::getAllParents($roleId, (int) $subcollection->getId());

                $copypaste[$path . '-' . $i] = ((int) $copyId === (int) $subcollection->getId())?'forbidden':'allowed';
                $this->view->copypaste = $copypaste;
                $prn = implode(',', $parentIds);
                $subcollections[$path . '-' . $i] = $subcollection->getDisplayName();
                $nameLength = max($nameLength, strlen($subcollection->getDisplayName()));
            }
        } else {
            foreach($collection->getSubCollection() as $i => $subcollection) {
                $severalAppearances[$i] = (true === $subcollection->getSeveralAppearances())?'several':'unique';
                $visibility[$i] = (true === $subcollection->getVisibility())?'visible':'hidden';
                // Alle Parents der aktuellen Subcollection
                $parentIds = Opus_Collection_Information::getAllParents($roleId, (int) $subcollection->getId());//print_r($parentIds);
                $copypaste[$i] = ((int) $copyId === (int) $subcollection->getId())?'forbidden':'allowed';
                $this->view->copypaste = $copypaste;

                $prn = implode(',', $parentIds);

                $subcollections[$i] = $subcollection->getDisplayName();
                $nameLength = max($nameLength, strlen($subcollection->getDisplayName()));
            }
        }
        // Freakin' Zend appears to consider layout as file name, not direcory
        // name...
        //$this->_helper->layout->setLayout($collection->getTheme());
        $this->view->severalAppearances = $severalAppearances;
        $this->view->visibility = $visibility;
        $this->view->subcollections = $subcollections;
        $this->view->role_id = $roleId;
        $this->view->role_name = $roleName;
        $this->view->path = $path;
        $this->view->copy = $copy;
        $this->view->breadcrumb = $breadcrumb;
    }

    /**
     * Save model instance
     *
     * @return void
     */
    public function createAction() {
        $copy = $this->getRequest()->getParam('copy');
        if ($this->_request->isPost() === true) {
            $data = $this->_request->getPost();
            $form_builder = new Form_Builder();
            $session = new Zend_Session_Namespace('collection');
            $form_builder->buildModelFromPostData($session->collection, $data['Opus_Model_Filter']);
            $form = $form_builder->build($this->__createFilter($session->collection));
            if (array_key_exists('submit', $data) === false) {
                $action_url = $this->view->url(array('action' => 'create'));
                $form->setAction($action_url);
                $this->view->form = $form;
            } else {
                $params = $this->getRequest()->getUserParams();
                if ($form->isValid($data) === true) {
                    // retrieve values from form and save them into model
                    $model = $session->collection;
                    if (true === $model->isNewRecord()) {
                        $role = $this->getRequest()->getParam('role');
                        $path = $this->getRequest()->getParam('path');
                        $below = $this->getRequest()->getParam('below');
                        $above = $this->getRequest()->getParam('above');
                        // Handling new collection in existing role.
                        if (true === isset($role)) {
                            $collection = new Opus_CollectionRole($role);
                            // Handling a new collection in an existing collection.
                            if (true === isset($path)) {
                                // Insert as last subcollection
                                $trail = explode('-', $path);
                                foreach($trail as $step) {
                                    $collection = $collection->getSubCollection($step);
                                }
                                $collection->addSubCollection($model);
                            } else if (true === isset($below)) {
                                // Insert below specified position
                                $trail = explode('-', $below);
                                foreach($trail as $i => $step) {
                                    if ($i < sizeof($trail) - 1) {
                                        $collection = $collection->getSubCollection($step);
                                    }
                                }
                                $collection->insertSubCollectionAt(end($trail) + 1, $model);
                            } else if (true === isset($above)) {
                                // Insert above specified position
                                $trail = explode('-', $above);
                                foreach($trail as $i => $step) {
                                    if ($i < sizeof($trail) - 1) {
                                        $collection = $collection->getSubCollection($step);
                                    }
                                }
                                $collection->insertSubCollectionAt(end($trail), $model);
                            } else {
                                $collection->addSubCollection($model);
                            }
                            $collection->store();
                        } else {
                            // Handling new role.
                            $model->store();
                            $params['role'] = $model->getId();
                        }
                    } else {
                        $model->store();
                    }
                    $module = array_shift($params);
                    $controller = array_shift($params);
                    $action = array_shift($params);
                    $this->_redirectTo('', 'show', $controller, $module, $params);
                } else {
                    $this->view->form = $form;
                }
            }
        } else if (true === isset($copy)) {
            // Copying a reference to a collection  to a new position
            $role  = $this->getRequest()->getParam('role');
            $below = $this->getRequest()->getParam('below');
            $above = $this->getRequest()->getParam('above');

            $sourceCollection = new Opus_CollectionRole($role);
            // $copy is the path to the source collection
            // fetch corresponding collection model
            if (true === isset($copy)) {
                $trail = explode('-', $copy);
                foreach($trail as $i => $step) {
                    if ($i < sizeof($trail)) {
                        $sourceCollection = $sourceCollection->getSubCollection($step);
                    }
                }
            }

            $targetCollection = new Opus_CollectionRole($role);
            // $below/$above is the path to the destination collection
            if (true === isset($below)) {
                // Insert below specified position
                $trail = explode('-', $below);
                foreach($trail as $i => $step) {
                    if ($i < sizeof($trail) - 1) {
                        $targetCollection = $targetCollection->getSubCollection($step);
                    }
                }
                $targetCollection->insertSubCollectionAt(end($trail) + 1, $sourceCollection);
            } else if (true === isset($above)) {
                // Insert above specified position
                $trail = explode('-', $above);
                foreach($trail as $i => $step) {
                    if ($i < sizeof($trail) - 1) {
                        $targetCollection = $targetCollection->getSubCollection($step);
                    }
                }
                $targetCollection->insertSubCollectionAt(end($trail), $sourceCollection);
            }
            $targetCollection->store();

            // redirect to parent of created collection
            array_pop($trail);
            $path = implode('-', $trail);

            $this->_redirectTo('Collection successfully copied.', 'show', null, null,
                    array('role' => $role, 'path' => $path));
        } else {
            $this->_redirectTo('', 'index');
        }
    }

    /**
     * Assign a document to a collection
     *
     * @return void
     */
    public function assignAction() {
        $documentId = $this->getRequest()->getParam('document');
        $role = $this->getRequest()->getParam('role');
        $path = $this->getRequest()->getParam('path');
        $document = new Opus_Document($documentId);
        if ($this->_request->isPost() === true) {
            $collection = new Opus_CollectionRole($role);
            $roleName = $collection->getDisplayName();
            if (true === isset($path)) {
                $trail = explode('-', $path);
                foreach($trail as $i => $step) {
                    if ($i < sizeof($trail)) {
                        $collection = $collection->getSubCollection($step);
                    }
                }
            }
            $collection->addEntry($document);
            $this->_redirectTo('Document successfully assigned to collection "' . $collection->getDisplayName() . '".'
                    , 'edit', 'documents', 'admin', array('id' => $document->getId()));
        } else if (false === isset($role)) {
            $collections = array();
            foreach (Opus_CollectionRole::getAll() as $collection) {
                $collections[$collection->getId()] = $collection->getDisplayName();
            }
            $this->view->subcollections = $collections;
            $this->view->breadcrumb = array();
            $this->view->assign = $documentId;
            $this->view->role_id = null;
        } else {
            $collection = new Opus_CollectionRole($role);
            $roleName = $collection->getDisplayName();
            $subcollections = array();
            $breadcrumb = array();
            if (true === isset($path)) {
                $trail = explode('-', $path);
                foreach($trail as $step) {
                    if (false === isset($position)) {
                        $position = $step;
                    } else {
                        $position .= '-' . $step;
                    }
                    $collection = $collection->getSubCollection($step);
                    $breadcrumb[$position] = $collection->getDisplayName();
                }
            }
            if ($collection instanceof Opus_CollectionRole) {
                foreach($collection->getSubCollection() as $i => $subcollection) {
                    $subcollections[$i] = $subcollection->getDisplayName();
                }
            } else {
                foreach($collection->getSubCollection() as $i => $subcollection) {
                    $subcollections[$path . '-' . $i] = $subcollection->getDisplayName();
                }
            }
            $this->view->subcollections = $subcollections;
            $this->view->role_id = $role;
            $this->view->role_name = $roleName;
            $this->view->path = $path;
            $this->view->assign = $documentId;
            $this->view->breadcrumb = $breadcrumb;
        }
    }

    /**
     * Returns a filtered representation of the collection.
     *
     * @param  Opus_Model_Abstract  $collection The collection to be filtered.
     * @return Opus_Model_Filter The filtered collection.
     */
    private function __createFilter(Opus_Model_Abstract $collection) {
        $filter = new Opus_Model_Filter();
        $filter->setModel($collection);
        $filter->setBlacklist(array('SubCollection', 'ParentCollection', 'Visibility', 'SeveralAppearances', 'RoleId', 'RoleName','RoleDisplayFrontdoor'));
        $filter->setSortOrder(array('Name'));
        return $filter;
    }
}

