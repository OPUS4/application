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
 * Controller for administration of user accounts.
 *
 * @category    Application
 * @package     Module_Admin
 *
 * TODO Support GET requests for create and update?
 */
class Admin_AccountController extends Controller_Action {


    /**
     * Default action presents list of existing accounts.
     */
    public function indexAction() {
        $this->view->title = $this->view->translate('admin_account_index');

        if ($this->_request->isPost() === true) {
            $request = $this->getRequest();
            $buttonEdit = $request->getPost('actionEdit');
            $buttonDelete = $request->getPost('actionDelete');

            if (isset($buttonEdit)) {
                $this->_forwardToAction('edit');
            }
            else if (isset($buttonDelete)) {
                $this->_forwardToAction('delete');
            }
        }
        
        $accounts = Opus_Account::getAll();

        if (empty($accounts)) {
            $this->view->render('none');
        }
        else {
            $this->view->accounts = array();
            foreach ($accounts as $account) {
                $this->view->accounts[$account->getId()] = $account->getDisplayName();
            }
        }
    }

    /**
     * Shows account information.
     */
    public function showAction() {
        $this->view->title = $this->view->translate('admin_account_show');

        $id = $this->getRequest()->getParam('id');
        if (empty($id)) {
            $this->_logger->debug('Missing parameter account id.');
            $this->_helper->redirector('index');
        }
        else {
            $account = new Opus_Account($id);
            $this->view->account = $account;
            return $account;
        }
    }

    /**
     * Shows form for creating new accounts.
     */
    public function newAction() {
        $this->view->title = $this->view->translate('admin_account_new');

        $accountForm = $this->_getAccountForm();

        $actionUrl = $this->view->url(array('action' => 'create'));

        $accountForm->setAction($actionUrl);

        $this->view->form = $accountForm;
    }

    /**
     * Creates new account.
     */
    public function createAction() {
        if ($this->getRequest()->isPost()) {

            $button = $this->getRequest()->getParam('cancel');
            if (isset($button)) {
                $this->_helper->redirector('index');
                return;
            }

            $accountForm = $this->_getAccountForm();

            $postData = $this->getRequest()->getPost();

            if ($accountForm->isValid($postData)) {
                $login = $postData['username'];

                if (!$this->_isLoginUsed($login)) {
                    $password = $postData['password'];

                    $account = new Opus_Account();

                    $account->setLogin($login);
                    $account->setPassword($password);

                    $roles = Opus_Role::getAll();

                    foreach ($roles as $roleName) {
                        $roleSelected = $postData['role' . $roleName];
                        if ($roleSelected) {
                            $role = Opus_Role::fetchByName($roleName);
                            $account->addRole($role);
                        }
                    }

                    $account->store();

                    $url = $this->view->url(array('action' => 'index'));
                    $this->redirectTo($url);
                }
                else {
                    $accountForm->getElement('username')->addError($this->view->translate('admin_account_error_login_used'));
                    $actionUrl = $this->view->url(array('action' => 'create'));
                    $accountForm->setAction($actionUrl);
                    $this->view->form = $accountForm;
                    return $this->renderScript('account/new.phtml');
                }
            }
            else {
                $actionUrl = $this->view->url(array('action' => 'create'));
                $accountForm->setAction($actionUrl);
                $this->view->form = $accountForm;
                return $this->renderScript('account/new.phtml');
            }
        }
        else {
            $this->_helper->redirector('index');
        }
    }

    /**
     * Shows edit form for an account.
     */
    public function editAction() {
        $this->view->title = $this->view->translate('admin_account_edit');

        $accountForm = $this->_getAccountForm();

        $id = $this->getRequest()->getParam('id');
        if (empty($id)) {
            $this->_logger->debug('Missing parameter account id.');
            $this->_helper->redirector('index');
        }
        else {
            $account = new Opus_Account($id);

            $login = $account->getLogin();

            $accountForm->getElement('username')->setValue($login);

            $roles = $account->getRole();

            foreach ($roles as $roleName) {
                $role = $accountForm->getElement('role' . $roleName);
                $role->setValue(1);
                if (Zend_Auth::getInstance()->getIdentity() === $account->getLogin()) {
                    $role->setAttrib('disabled', true);
                }
            }

            $actionUrl = $this->view->url(array('action' => 'update', 'id' => $id));

            $accountForm->setAction($actionUrl);

            $this->view->form = $accountForm;
        }
    }

    /**
     * Updates account information.
     */
    public function updateAction() {
        if ($this->getRequest()->isPost()) {

            $button = $this->getRequest()->getParam('cancel');
            if (isset($button)) {
                $this->_helper->redirector('index');
                return;
            }

            $accountForm = $this->_getAccountForm();

            $postData = $this->getRequest()->getPost();

            $passwordChanged = true;

            if (empty($postData['password'])) {
                // modify to pass default validation
                // TODO think about better solution
                $postData['password'] = 'notchanged';
                $postData['confirmPassword'] = 'notchanged';
                $passwordChanged = false;
            }

            $id = $this->getRequest()->getParam('id');

            if ($accountForm->isValid($postData)) {

                $account = new Opus_Account($id);

                $oldLogin = $account->getLogin();

                $currentUser = Zend_Auth::getInstance()->getIdentity();

                $isCurrentUser = ($currentUser === $oldLogin) ? true : false;

                // update login name
                $newLogin = $postData['username'];

                if ($newLogin !== $oldLogin) {
                    if (!$this->_isLoginUsed($newLogin)) {
                        $account->setLogin($newLogin);
                        $loginChanged = true;
                    }
                    else {
                        $accountForm->getElement('username')->addError($this->view->translate('admin_account_error_login_used'));
                        $actionUrl = $this->view->url(array('action' => 'update', 'id' => $id));
                        $accountForm->setAction($actionUrl);
                        $this->view->form = $accountForm;
                        return $this->renderScript('account/edit.phtml');
                    }
                }
                else {
                    $loginChanged = false;
                }

                // update password
                if ($passwordChanged) {
                    $password = $postData['password'];
                    $account->setPassword($password);
                }

                // update roles
                $roles = Opus_Role::getAll();

                $newRoles = array();

                foreach ($roles as $roleName) {
                    $roleSelected = $postData['role' . $roleName];
                    if ($roleSelected) {
                        $role = Opus_Role::fetchByName($roleName);
                        $newRoles[] = $role;
                    }
                    else if ((strtolower($roleName) === 'administrator') && $isCurrentUser) {
                        $newRoles[] = Opus_Role::fetchByName($roleName);
                    }
                }

                $account->setRole($newRoles);

                $account->store();

                if ($isCurrentUser &&  ($loginChanged || $passwordChanged))  {
                    Zend_Auth::getInstance()->clearIdentity();
                }
                
                $this->_helper->redirector('index');
            }
            else {
                $actionUrl = $this->view->url(array('action' => 'update', 'id' => $id));
                $accountForm->setAction($actionUrl);
                $this->view->form = $accountForm;
                return $this->renderScript('account/edit.phtml');
            }
        }
        else {
            $this->_helper->redirector('index');
        }

    }

    /**
     * Deletes account.
     */
    public function deleteAction() {
        $accountId = $this->getRequest()->getParam('id');

        if (!empty($accountId)) {
            $account = new Opus_Account($accountId);

            if (!empty($account)) {
                $currentUser = Zend_Auth::getInstance()->getIdentity();
                
                // Check that user doesn't delete himself (especially the admin)
                if ($currentUser === $account->getLogin()) {
                    // TODO
                }
                else {
                    $account->delete();
                }
            }
        }

        $this->_helper->redirector('index');
    }

    /**
     * Creates form for creating and editing an account.
     *
     * @return Zend_Form
     */
    protected function _getAccountForm() {
        $config = new Zend_Config_Ini(APPLICATION_PATH . '/modules/admin/forms/account.ini', 'production');
        $form = new Zend_Form($config->form->account);

        $confirmPassword = $form->getElement('confirmPassword');

        $passwordValidator = new Form_Validate_Password();

        $confirmPassword->addValidator($passwordValidator);

        $roles = Opus_Role::getAll();

        $rolesGroup = array();

        foreach ($roles as $role) {
            $roleName = $role->getDisplayName();
            $roleCheckbox = $form->createElement('checkbox', 'role' . $roleName)->setLabel($roleName);
            $form->addElement($roleCheckbox);
            $rolesGroup[] = $roleCheckbox->getName();
        }

        $form->addDisplayGroup($rolesGroup, 'Roles', array('legend' => 'admin_form_group_roles'));

        return $form;
    }

    protected function _isLoginUsed($login) {
        try {
            $account = new Opus_Account(null, null, $login);
        } catch (Exception $ex) {
            return false;
        }
        return true;
    }


}
