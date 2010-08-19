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
 * @package     Controller
 * @author      Felix Ostrowski <ostrowski@hbz-nrw.de>
 * @copyright   Copyright (c) 2009, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * CRUD Controller for Opus Applications.
 *
 * Extend this class and set protected static $_modelclass to the class of an
 * Opus_Model to gain basic C(reate) R(ead) U(pdate) D(elete) interface:
 *
 * New Form:    GET module/controller/new
 * Create:      POST module/controller/create
 * Read:        GET module/controller/show/id/x
 * Update Form: POST module/controller/create (with id set in model)
 * Delete:      POST module/controller/delete (with id parameter)
 *
 * See modules/admin/licence/views/scripts/licence/*.phtml for example templates.
 *
 * @category    Application
 * @package     Controller
 */
class Controller_CRUDAction extends Controller_Action {

    /**
     * The class of the model being administrated.
     *
     * @var Opus_Model_Abstract
     */
    protected $_modelclass = null;

    /**
     * List all available model instances
     *
     * @return void
     */
    public function indexAction() {
        eval('$entries = ' . $this->_modelclass . '::getAll();');
        $this->view->entries = array();
        foreach ($entries as $entry) {
            $this->view->entries[$entry->getId()] = $entry->getDisplayName();
        }
    }

    /**
     * Displays a model instance
     *
     * @return void
     */
    public function showAction() {
        $id = $this->getRequest()->getParam('id');
        $model = new $this->_modelclass($id);
        $this->view->entry = $model->toArray();
        return $model;
    }

    /**
     * Create a new model instance
     *
     * @return void
     */
    public function newAction() {
        $form_builder = new Form_Builder();
        $model = new $this->_modelclass;
        $session = new Zend_Session_Namespace('crud');
        $session->{$this->_modelclass} = $model;
        $modelForm = $form_builder->build($model);
        $action_url = $this->view->url(array("action" => "create"));
        $modelForm->setAction($action_url);
        $this->view->form = $modelForm;
    }

    /**
     * Save model instance
     *
     * @return void
     */
    public function createAction() {
        // TODO: Use session to store model.
        if ($this->_request->isPost() === true) {
            $data = $this->_request->getPost();
            $form_builder = new Form_Builder();
            $id = $this->getRequest()->getParam('id');
            $session = new Zend_Session_Namespace('crud');
            $model = $session->{$this->_modelclass};
            if (array_key_exists('submit', $data) === false) {
                $form_builder->buildModelFromPostData($model, $data[$this->_modelclass]);
                $form = $form_builder->build($model);
                $action_url = $this->view->url(array("action" => "create"));
                $form->setAction($action_url);
                $this->view->form = $form;
            } else {
                $form_builder->buildModelFromPostData($model, $data[$this->_modelclass]);
                $form = $form_builder->build($model);
                if ($form->isValid($data) === true) {
                    $model->store();
                    // The first 3 params are module, controller and action.
                    // Additional parameters are passed through.
                    $params = $this->getRequest()->getUserParams();
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

    /**
     * Edits a model instance
     *
     * @return void
     */
    public function editAction() {
        $id = $this->getRequest()->getParam('id');
        $form_builder = new Form_Builder();
        $model = new $this->_modelclass($id);
        $session = new Zend_Session_Namespace('crud');
        $session->{$this->_modelclass} = $model;
        $modelForm = $form_builder->build($model);
        $action_url = $this->view->url(array("action" => "create"));
        $modelForm->setAction($action_url);
        $this->view->form = $modelForm;
    }

    /**
     * Deletes a model instance
     *
     * @return void
     */
    public function deleteAction() {
        if ($this->_request->isPost() === true) {
            $id = $this->getRequest()->getPost('id');
            $model = new $this->_modelclass($id);
            $model->delete();
            $this->_redirectTo('Model successfully deleted.', 'index');
        } else {
            $this->_redirectTo('', 'index');
        }
    }

    /**
     * Dispatches requests to other actions.
     *
     * @return void
     *
     * FIXME: Implement generic dispatching based on 'action...'
     */
    public function dispatchAction() {
        $logger = Zend_Registry::get('Zend_Log');
        if ($this->_request->isPost() === true) {
            $request = $this->getRequest();
            $buttonEdit = $request->getPost('actionEdit');
            $buttonDelete = $request->getPost('actionDelete');

            if (isset($buttonEdit)) {
                $this->_forwardToAction('edit');
            }
            else if (isset($buttonDelete)) {
                $this->_forwardtoAction('delete');
            }
        }
    }
}
