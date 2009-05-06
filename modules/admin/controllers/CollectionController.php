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
        $form_builder = new Opus_Form_Builder();
        $collection = new Opus_CollectionRole($role);
        if (true === isset($path)) {
            $trail = explode('-', $path);
            foreach($trail as $step) {
                $collection = $collection->getSubCollection($step);
            }
        }
        $collectionForm = $form_builder->build($collection);
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
        $path = $this->getRequest()->getParam('path');
        $form_builder = new Opus_Form_Builder();
        if (true === isset($role)) {
            $collection = new Opus_Collection($role);
        } else {
            $collection = new Opus_CollectionRole;
        }
        $collectionForm = $form_builder->build($collection);
        $action_url = $this->view->url(array('action' => 'create'));
        $collectionForm->setAction($action_url);
        $this->view->form = $collectionForm;
    }

    /**
     * Deletes a collection or collection role instance
     *
     * @return void
     */
    public function deleteAction() {
        if ($this->_request->isPost() === true) {
            $role = $this->getRequest()->getUserParam('role');
            $path = $this->getRequest()->getUserParam('path');
            $collection = new Opus_CollectionRole($role);
            if (true === isset($path)) {
                $trail = explode('-', $path);
                foreach($trail as $step) {
                    $collection = $collection->getSubCollection($step);
                }
                $path = implode('-', array_slice($trail, 0, sizeof($trail) - 1));
                $collection->delete();
                $this->_redirectTo('Model successfully deleted.', 'show', null, null,
                        array('role' => $role, 'path' => $path));
            } else {
                $collection->delete();
                $this->_redirectTo('Model successfully deleted.', 'index');
            }
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
        $roleId = $this->getRequest()->getParam('role');
        $collection = new Opus_CollectionRole($roleId);
        $roleName = $collection->getName();
        $path = $this->getRequest()->getParam('path');
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
                $breadcrumb[$position] = $collection->getName();
            }
            foreach($collection->getSubCollection() as $i => $subcollection) {
                $subcollections[$path . '-' . $i] = $subcollection->getName();
            }
        } else {
            foreach($collection->getSubCollection() as $i => $subcollection) {
                $subcollections[$i] = $subcollection->getName();
            }
        }
        $this->view->subcollections = $subcollections;
        $this->view->role_id = $roleId;
        $this->view->role_name = $roleName;
        $this->view->path = $path;
        $this->view->breadcrumb = $breadcrumb;
    }

    /**
     * Save model instance
     *
     * @return void
     */
    public function createAction() {
        if ($this->_request->isPost() === true) {
            $data = $this->_request->getPost();
            $form_builder = new Opus_Form_Builder();
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
                        // Handling new collection in existing role.
                        if (true === isset($role)) {
                            $collection = new Opus_CollectionRole($role);
                            // Handling a new collection in an existing collection.
                            if (true === isset($path)) {
                                $trail = explode('-', $path);
                                foreach($trail as $step) {
                                    $collection = $collection->getSubCollection($step);
                                }
                            }
                            $collection->addSubCollection($model);
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
        } else {
            $this->_redirectTo('', 'index');
        }
    }
}

