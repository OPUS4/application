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
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2018, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

class Admin_RoleController extends Application_Controller_Action
{

    private static $_protectedRoles = array('guest', 'administrator');

    /**
     * Shows list of all roles.
     */
    public function indexAction() {
        $this->view->title = $this->view->translate('admin_role_index');

        $roles = Opus_UserRole::getAll();

        if (empty($roles)) {
            $this->view->render('none');
        }
        else {
            $this->view->roles = array();
            foreach ($roles as $role) {
                $this->view->roles[$role->getId()] = $role->getDisplayName();
            }
        }

        $this->view->protectedRoles = self::$_protectedRoles;
    }

    /**
     * Show a role.
     */
    public function showAction() {
        $roleId = $this->getRequest()->getParam('id');

        if (!empty($roleId)) {
            $this->view->title = $this->view->translate('admin_role_show');

            $role = new Opus_UserRole($roleId);
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
                return $this->_helper->Redirector->redirectTo('index');
            }

            $form = new Admin_Form_Role();

            if ($form->isValid($postData)) {

                $name = $postData['name'];

                $roleExists = $this->_isRoleNameExists($name);

                $this->_updateRole(null, $name);
            }
            else {
                $actionUrl = $this->view->url(array('action' => 'create'));
                $form->setAction($actionUrl);
                $this->view->form = $form;
                $this->view->title = 'admin_role_new';
                $this->_helper->viewRenderer->setRender('new');
                return $this->render('new');
            }
        }

        $this->_helper->Redirector->redirectTo('index');
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

            $role = new Opus_UserRole($roleId);

            if (!isset($postData['name'])) {
                $postData['name'] = $role->getName();
            }

            $postData['oldRole'] = $role->getName();

            $roleForm = new Admin_Form_Role();

            if ($roleForm->isValid($postData)) {
                $name = $postData['name'];
                $this->_updateRole($roleId, $name);
            }
            else {
                $actionUrl = $this->view->url(array('action' => 'update', 'id' => $roleId));
                $roleForm->setAction($actionUrl);
                $this->view->roleForm = $roleForm;
                $this->view->title = 'admin_role_edit';
                $this->_helper->viewRenderer->setRender('edit');
                return $this->render('edit');
            }
        }

        $this->_helper->redirector('index');
    }

    /**
     * Deletes a role from the database.
     */
    public function deleteAction() {
        $roleId = $this->getRequest()->getParam('id');

        if (!empty($roleId)) {
            $role = new Opus_UserRole($roleId);

            $roleName = $role->getName();

            if (in_array($roleName, self::$_protectedRoles)) {
                // TODO deliver message to user
            }
            else {
                $role->delete();
            }
        }

        $this->_helper->redirector('index');
    }

    protected function _updateRole($roleId, $name) {
        if (!empty($roleId)) {
            $role = new Opus_UserRole($roleId);
        }
        else {
            $role = new Opus_UserRole();
        }

        $oldName = $role->getName();

        if (!empty($name) && (!in_array($name, self::$_protectedRoles))) {
            $role->setName($name);
        }

        $role->store();
    }

    protected function _isRoleNameExists($name) {
        $role = Opus_UserRole::fetchByName($name);

        if (empty($role)) {
            return false;
        }
        else {
            return true;
        }
    }

}
