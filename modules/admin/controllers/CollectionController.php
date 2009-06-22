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
     * List all available collection role instances
     *
     * @return void
     */
    public function indexAction() {
        $entries = Opus_CollectionRole::getAll();
        $this->view->entries = array();
        foreach ($entries as $entry) {
            $this->view->entries[$entry->getId()] = $entry->getDisplayName();
        }
    }

    /**
     * Edits a collection instance
     *
     * @return void
     */
    public function editAction() {
        $role = $this->getRequest()->getParam('role');
        $path = $this->getRequest()->getParam('path');
        $form_builder = new Form_Builder();
        $collection = new Opus_CollectionRole($role);
        if (true === isset($path)) {
            $trail = explode('-', $path);
            foreach($trail as $step) {
                $collection = $collection->getSubCollection($step);
            }
        }
        $filter = new Opus_Model_Filter;
        $filter->setModel($collection);
        $filter->setBlacklist(array('SubCollection', 'ParentCollection', 'CollectionsContentSchema', 'Visibility', 'SeveralAppearances'));
        $filter->setSortOrder(array('Name'));
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
        if (true === isset($role)) {
            $collection = new Opus_Collection($role);
        } else {
            $collection = new Opus_CollectionRole;
        }
        $filter = new Opus_Model_Filter;
        $filter->setModel($collection);
        $filter->setBlacklist(array('SubCollection', 'ParentCollection', 'Visibility', 'SeveralAppearances'));
        $filter->setSortOrder(array('Name'));
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
        //($this->_request->isPost() === true) {
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
            } else {
                $this->_redirectTo('Role successfully deleted.', 'index');
            }

/*        } else {
            $this->_redirectTo('', 'index');
        }*/
    }

    /**
     * Display subcollections of collections and collection roles.
     *
     * @return void
     */
    public function showAction() {
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
        $subcollections = array();
        $breadcrumb = array();
        $severalAppearances = array();
        $visibility = array();
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
                //$parents = $subcollection->getParentCollection();

                $copypaste[$path . '-' . $i] = ((int) $copyId === (int) $subcollection->getId())?'forbidden':'allowed';
                //$copypaste[$path . '-' . $i] = (true === in_array($copyId, $parentIds))?'forbidden':'allowed';
                $prn = implode(',', $parentIds);
                $subcollections[$path . '-' . $i] = $subcollection->getDisplayName();
            }
        } else {
            foreach($collection->getSubCollection() as $i => $subcollection) {
                $severalAppearances[$i] = (true === $subcollection->getSeveralAppearances())?'several':'unique';
                $visibility[$i] = (true === $subcollection->getVisibility())?'visible':'hidden';
                // Alle Parents der aktuellen Subcollection
                $parentIds = Opus_Collection_Information::getAllParents($roleId, (int) $subcollection->getId());//print_r($parentIds);
                //$parents = $subcollection->getParentCollection();
                $copypaste[$i] = ((int) $copyId === (int) $subcollection->getId())?'forbidden':'allowed';

                //$copypaste[$i] = (true === in_array($copyId, $parentIds))?'forbidden':'allowed';

                $prn = implode(',', $parentIds);

                $subcollections[$i] = $subcollection->getDisplayName();

            }
        }
        $this->view->severalAppearances = $severalAppearances;
        $this->view->visibility = $visibility;
        $this->view->subcollections = $subcollections;
        $this->view->role_id = $roleId;
        $this->view->role_name = $roleName;
        $this->view->path = $path;
        $this->view->copy = $copy;
        $this->view->breadcrumb = $breadcrumb;
        $this->view->copypaste = $copypaste;
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
            if (array_key_exists('submit', $data) === false) {
                $form = $form_builder->buildFromPost($data);
                $action_url = $this->view->url(array('action' => 'create'));
                $form->setAction($action_url);
                $this->view->form = $form;
            } else {
                $form = $form_builder->buildFromPost($data);
                $params = $this->getRequest()->getUserParams();
                if ($form->isValid($data) === true) {
                    // retrieve values from form and save them into model
                    $model = $form_builder->getModelFromForm($form);
                    $form_builder->setFromPost($model, $form->getValues());
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
}

