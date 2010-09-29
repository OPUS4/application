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
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Main entry point for this module.
 *
 * @category    Application
 * @package     Module_Admin
 */
class Admin_RoleController extends Controller_Action {

    /**
     * Shows list of all roles.
     */
    public function indexAction() {
        $this->view->title = $this->view->translate('admin_role_index');

        $roles = Opus_Role::getAll();

        if (empty($roles)) {
            $this->view->render('none');
        }
        else {
            $this->view->roles = array();
            foreach ($roles as $role) {
                $this->view->roles[$role->getId()] = $role->getDisplayName();
            }
        }
    }
    
    /**
     * Show a role.
     */
    public function showAction() {
        $roleId = $this->getRequest()->getParam('id');

        if (!empty($roleId)) {
            $this->view->title = $this->view->translate('admin_role_show');

            $role = new Opus_Role($roleId);
            $this->view->role = $role;
        }
        else {
            $this->_helper->redirector('index');
        }
    }
    
    /**
     * Shows form for creating a new role.
     */
    public function newAction() {
        $form = new Admin_Form_Role();

        $actionUrl = $this->view->url(array('action' => 'create'));

        $form->setAction($actionUrl);

        $this->view->form = $form;
    }
    
    /**
     * Creates a new role in the database.
     */
    public function createAction() {
        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();

            if ($this->getRequest()->getPost('cancel')) {
                $this->_helper->redirector('index');
            }

            $form = new Admin_Form_Role();

            if ($form->isValid($postData)) {

                
            }
            else {
                $actionUrl = $this->view->url(array('action' => 'new'));
                $form->setAction($actionUrl);
                $this->view->form = $form;
                return $this->renderScript('role/new.phtml');
            }
        }

        $this->_helper->redirector('index');
    }
    
    /**
     * Shows form for editing a role.
     */
    public function editAction() {
        $roleId = $this->getRequest()->getParam('id');

        if (!empty($roleId)) {
            $roleForm = new Admin_Form_Role($roleId);
            $actionUrl = $this->view->url(array('action' => 'update', 'id' => $roleId));
            $roleForm->setAction($actionUrl);
            $this->view->roleForm = $roleForm;
        }
        else {
            $this->_helper->redirector('index');
        }
    }
    
    /**
     * Updates a role in the database.
     */
    public function updateAction() {
        $roleId = $this->getRequest()->getParam('id');

        if (!empty($roleId)) {
            $postData = $this->getRequest()->getPost();

            $name = $postData['name'];
            $selectedPrivileges = Admin_Form_Role::parseSelectedPrivileges($postData);

//            Zend_Registry::get('Zend_Log')->debug(count($privileges));
//
//            foreach ($privileges as $privilege) {
//                Zend_Registry::get('Zend_Log')->debug('Selected privilege = ' . $privilege->getPrivilege());
//            }

            $this->_updateRole($roleId, $name, $selectedPrivileges);
        }

        $this->_helper->redirector('index');
    }

    /**
     * Deletes a role from the database.
     */
    public function deleteAction() {
        $roleId = $this->getRequest()->getParam($id);

        if (!empty($roleId)) {
            $role = new Opus_Role($roleId);

            $role->delete();
        }

        $this->_helper->redirector('index');
    }

    protected function _updateRole($roleId, $name, $selectedPrivileges) {
        $role = new Opus_Role($roleId);

        if (!empty($name)) {
            $role->setDisplayName($name);
        }

        $currentPrivileges = $role->getPrivilege();

        // check which privileges are already granted and
        // remove the ones that are not selected anymore
        foreach ($currentPrivileges as $currentPrivilege) {
            $name = $currentPrivilege->getDisplayName();
            if (isset($selectedPrivileges[$name])) {
                // remove from list of selected, since the role already has it
                unset($selectedPrivileges[$name]);
            }
            else {
                // remove from role, since it isn't a selected privilege
                $role->removePrivilege($currentPrivilege);
            }
        }

        // add remaining selected privileges
        foreach($selectedPrivileges as $newPrivilege) {
            $privilege = new Opus_Privilege();
            $privilege->setDisplayName($newPrivilege);
            $role->addPrivilege($privilege);
        }

        $role->store();
    }

}
