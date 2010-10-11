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
 * @package     Module_Admin
 * @author      Sascha Szott <szott@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Controller for administration of collection roles.
 *
 */
class Admin_CollectionrolesController extends Controller_Action {

    /**
     * List all available collection role instances
     *
     */
    public function indexAction() {
        $this->view->collectionRoles = Opus_CollectionRole::fetchAll();
    }

    public function newAction() {
        $collectionRoleModel = new Admin_Model_CollectionRole();
        $this->view->form = $this->getRoleForm($collectionRoleModel->getObject());
    }

    public function editAction() {
        $collectionRoleModel = null;
        try {
            $collectionRoleModel = new Admin_Model_CollectionRole($this->getRequest()->getParam('roleid', ''));
        }
        catch (Application_Exception $e) {
            $this->_redirectToAndExit('index', array('failure' => $e->getMessage()));
            return;
        }
        $this->view->form = $this->getRoleForm($collectionRoleModel->getObject());
    }

    public function moveAction() {
        try {
            $collectionRoleModel = new Admin_Model_CollectionRole($this->getRequest()->getParam('roleid', ''));
            $collectionRoleModel->move($this->getRequest()->getParam('pos'));
            $this->_redirectTo('index', 'Operation completed successfully.');
        }
        catch (Application_Exception $e) {
            $this->_redirectToAndExit('index', array('failure' => $e->getMessage()));
        }
    }

    private function changeRoleVisibility($visibility) {
        try {
            $collectionRoleModel = new Admin_Model_CollectionRole($this->getRequest()->getParam('roleid', ''));
            $collectionRoleModel->setVisibility($visibility);
            $this->_redirectTo('index', 'Operation completed successfully.');
        }
        catch (Application_Exception $e) {
            $this->_redirectToAndExit('index', array('failure' => $e->getMessage()));
        }        
    }

    public function hideAction() {
        $this->changeRoleVisibility(false);        
    }

    public function unhideAction() {
        $this->changeRoleVisibility(true);
    }


    private function initCreateRoleForm($form, $collectionRole) {
        if ($collectionRole->isNewRecord()) {
            $form->setAction($this->view->url(array('action' => 'create')));
        }
        else {
            $form->setAction($this->view->url(array('action' => 'create', 'oid' => $collectionRole->getId())));
        }
        return $form;
    }

    private function returnIfNotPost() {
        if (!$this->getRequest()->isPost()) {
            $this->_redirectToAndExit('index');
        }
    }

    public function createAction() {
        $this->returnIfNotPost();

        $data = $this->_request->getPost();

        $collectionRoleModel = new Admin_Model_CollectionRole($this->getRequest()->getParam('oid'));
        $collectionRole = $collectionRoleModel->getObject();

        $form_builder = new Form_Builder();
        $form_builder->buildModelFromPostData($collectionRole, $data['Opus_Model_Filter']);
        $form = $form_builder->build($this->__createRoleFilter($collectionRole));

        if (!$form->isValid($data)) {
            $this->view->form = $this->initCreateRoleForm($form, $collectionRole);
        }
        else {
            // manuelles Überprüfen der IBs in Tabelle collections_roles
            $tmpRole = Opus_CollectionRole::fetchByName($collectionRole->getName());
            if (!is_null($tmpRole) && $tmpRole->getId() !== $collectionRole->getId()) {
                $this->view->form = $this->initCreateRoleForm($form, $collectionRole);
                $this->view->message = 'name is not unique';
                return;
            }

            $tmpRole = Opus_CollectionRole::fetchByOaiName($collectionRole->getOaiName());
            if (!is_null($tmpRole) && $tmpRole->getId() !== $collectionRole->getId()) {
                $this->view->form = $this->initCreateRoleForm($form, $collectionRole);
                $this->view->message = 'oainame is not unique';
                return;
            }

            if (true === $collectionRole->isNewRecord()) {
                if (true === is_null($collectionRole->getRootCollection())) {
                    $collectionRole->addRootCollection();
                    $collectionRole->getRootCollection()->setVisible('1');
                }
                $collectionRole->store();
                $this->_redirectTo('index', 'Collection role \'' . $collectionRole->getName() . '\' successfully created.');
            }
            else {
                $collectionRole->store();
                $this->_redirectTo('index', 'Collection role \'' . $collectionRole->getName() . '\' successfully edited.');
            }
        }
    }


    private function getRoleForm(Opus_CollectionRole $collectionRole) {
        $form_builder = new Form_Builder();
        $collectionForm = $form_builder->build($this->__createRoleFilter($collectionRole));
        if ($collectionRole->isNewRecord()) {
            $collectionForm->setAction($this->view->url(array('action' => 'create')));
        }
        else {
            $collectionForm->setAction($this->view->url(array('action' => 'create', 'oid' => $collectionRole->getId())));
        }
        return $collectionForm;
    }

    private function __createRoleFilter(Opus_CollectionRole $collectionRole) {
        $filter = new Opus_Model_Filter();
        $filter->setModel($collectionRole);
        $filter->setBlacklist(array('RootCollection'));
        return $filter;
    }

    public function deleteAction() {
        try {
            $collectionRoleModel = new Admin_Model_CollectionRole($this->getRequest()->getParam('roleid', ''));
            $collectionRoleModel->delete();
        }
        catch (Application_Exception $e) {
            $this->_redirectToAndExit('index', array('failure' => $e->getMessage()));
            return;
        }
        $this->_redirectTo('index', 'Operation completed successfully.');
    }

}
?>
