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
 * @package     Application_Controller
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2009-2013, OPUS 4 development team
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
class Application_Controller_Action_CRUD extends Controller_Action {
    
    /**
     * Name von Parameter für Model-ID.
     */
    const PARAM_MODEL_ID = 'id';

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
     * 
     * TODO Konfigurierbare Tabelle mit Links für Editing/Deleting
     */
    public function indexAction() {
        $entries = $this->getAllModels();
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
        $modelId = $this->getRequest()->getParam('id');
        if (!empty($modelId) && is_numeric($modelId)) {
            $model = $this->getModel($modelId);
            $this->view->entry = $model->toArray();
            $this->view->objectId = $modelId;
            return $model;
        }
        else {
            $this->_helper->redirector('index');
        }
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
        $action_url = $this->view->url(array('action' => 'create'));
        $modelForm->setAction($action_url);
        $this->view->form = $modelForm;
    }

    /**
     * Save model instance
     *
     * @return void
     */
    public function createAction() {        
        if (!$this->_request->isPost()) {
            return $this->_redirectTo('index');
        }

        // TODO: Use session to store model.
        $data = $this->_request->getPost();
        $form_builder = new Form_Builder();
        $id = $this->getRequest()->getParam('id');
        $session = new Zend_Session_Namespace('crud');
        $model = $session->{$this->_modelclass};
        if (array_key_exists('submit', $data) === false) {
            
            // TODO: when does this case occur?

            $form_builder->buildModelFromPostData($model, $data[$this->_modelclass]);
            $form = $form_builder->build($model);
            $action_url = $this->view->url(array('action' => 'create'));
            $form->setAction($action_url);
            $this->view->form = $form;

            // TODO: $this->view->title needs to be set appropriately

        }
        else {
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
                return $this->_redirectTo('show', '', $controller, $module, $params);
            }

            $this->view->form = $form;

            $replacement = '';
            $regexPattern = '/_create$/';
            if ($model->isNewRecord()) {
                $replacement = 'new';
            }
            else {
                $replacement = 'edit';
            }
            $this->view->title = preg_replace($regexPattern, '_' . $replacement, $this->view->title);
        }
    }

    /**
     * Edits a model instance
     *
     * @return void
     */
    public function editAction() {
        $modelId = $this->getRequest()->getParam('id');
        if (!empty($modelId) && is_numeric($modelId)) {
            $form_builder = new Form_Builder();
            $model = $this->getModel($modelId);
            $session = new Zend_Session_Namespace('crud');
            $session->{$this->_modelclass} = $model;
            $modelForm = $form_builder->build($model);
            $action_url = $this->view->url(array('action' => 'create'));
            $modelForm->setAction($action_url);
            $this->view->form = $modelForm;
        }
        else {
            $this->_helper->redirector('index');
        }
    }

    /**
     * Deletes a model instance
     *
     * @return void
     */
    public function deleteAction() {
        if ($this->_request->isPost() === true) {
            $modelId = $this->getRequest()->getPost('id');
            $model = $this->getModel($modelId);
            // TODO ask for confirmation before deleting
            $model->delete();
            $this->_redirectTo('index', 'Model successfully deleted.');
        } 
        else {
            $this->_redirectTo('index');
        }
    }
    
    /**
     * Liefert alle Instanzen der Model-Klasse.
     */
    public function getAllModels() {
        eval('$entries = ' . $this->_modelclass . '::getAll();');
        return $entries; 
    }
    
    /**
     * Liefert Instanz des Models.
     * @param type $modelId
     * @return \_modelclass
     */
    public function getModel($modelId) {
        return new $this->_modelclass($modelId);
    }
    
    /**
     * Liefert Formular für Model.
     */
    public function getModelForm() {
        
    }
    
}
