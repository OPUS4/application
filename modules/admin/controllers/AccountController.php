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

        $accounts = Opus_Account::getAll();

        if (empty($accounts)) {
            return $this->renderScript('account/none.phtml');
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

        $accountForm = new Admin_Form_Account();

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

            $accountForm = new Admin_Form_Account();

            $postData = $this->getRequest()->getPost();

            if ($accountForm->isValid($postData)) {
                $login = $postData['username'];
                $password = $postData['password'];
                $firstname = $postData['firstname'];
                $lastname = $postData['lastname'];
                $email = $postData['email'];
                $roles = Admin_Form_Account::parseSelectedRoles($postData);

                $account = new Opus_Account();

                $account->setLogin($login);
                $account->setPassword($password);
                $account->setFirstName($firstname);
                $account->setLastName($lastname);
                $account->setEmail($email);
                $account->setRole($roles);

                $account->store();
            }
            else {
                $actionUrl = $this->view->url(array('action' => 'create'));
                $accountForm->setAction($actionUrl);
                $this->view->form = $accountForm;
                return $this->renderScript('account/new.phtml');
            }
        }

        $this->_helper->redirector->gotoSimple('index');
    }

    /**
     * Shows edit form for an account.
     */
    public function editAction() {
        $this->view->title = $this->view->translate('admin_account_edit');

        $id = $this->getRequest()->getParam('id');

        if (empty($id)) {
            $this->_logger->debug('Missing parameter account id.');
            $this->_helper->redirector('index');
        }
        else {
            $accountForm = new Admin_Form_Account($id);
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

            $id = $this->getRequest()->getParam('id');

            $accountForm = new Admin_Form_Account($id);

            $postData = $this->getRequest()->getPost();

            $passwordChanged = true;
            if (empty($postData['password'])) {
                // modify to pass default validation
                // TODO think about better solution
                $postData['password'] = 'notchanged';
                $postData['confirmPassword'] = 'notchanged';
                $passwordChanged = false;
            }

            $account = new Opus_Account($id);

            $postData['oldLogin'] = $account->getLogin();

            if ($accountForm->isValid($postData)) {

                $account->setFirstName($postData['firstname']);
                $account->setLastName($postData['lastname']);
                $account->setEmail($postData['email']);

                $oldLogin = $account->getLogin();

                // update login name
                $newLogin = $postData['username'];

                if ($newLogin !== $oldLogin) {
                    $account->setLogin($newLogin);
                    $loginChanged = true;
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
                $newRoles = Admin_Form_Account::parseSelectedRoles($postData);

                // TODO optimize code
                $hasAdministratorRole = false;
                
                foreach ($newRoles as $role) {
                    if (strtolower($role->getDisplayName()) === 'administrator') {
                        $hasAdministratorRole = true;
                        break;
                    }
                }
                    
                $currentUser = Zend_Auth::getInstance()->getIdentity();
                $isCurrentUser = ($currentUser === $oldLogin) ? true : false;

                if (!$hasAdministratorRole && $isCurrentUser) {
                    $newRoles[] = Opus_UserRole::fetchByName('administrator');
                }

                $account->setRole($newRoles);

                $account->store();

                if ($isCurrentUser &&  ($loginChanged || $passwordChanged))  {
                    Zend_Auth::getInstance()->clearIdentity();
                }
            }
            else {
                $actionUrl = $this->view->url(array('action' => 'update', 'id' => $id));
                $accountForm->setAction($actionUrl);
                $this->view->form = $accountForm;
                return $this->renderScript('account/edit.phtml');
            }
        }

        $this->_helper->redirector('index');
    }

    /**
     * Deletes account.
     */
    public function deleteAction() {
        $accountId = $this->getRequest()->getParam('id');

        $message = null;

        if (!empty($accountId)) {
            $account = new Opus_Account($accountId);

            if (!empty($account)) {
                $currentUser = Zend_Auth::getInstance()->getIdentity();

                // Check that user does not delete himself and protect admin
                // account
                if ($currentUser === $account->getLogin()) {
                    $message = 'admin_account_error_delete_self';
                }
                else if ($account->getLogin() === 'admin') {
                    $message = 'admin_account_error_delete_admin';
                }
                else {
                    $account->delete();
                }
            }
            else {
                $message = 'admin_account_error_badid';
            }
        }
        else {
            $message = 'admin_account_error_missingid';
        }

        $messages = array();

        if ($message === null) {
            $messages['notice'] = $this->view->translate(
                    'admin_account_delete_success');
        }
        else {
            $messages['failure'] = $this->view->translate($message);
        }

        $this->_redirectTo('index', $messages);
    }

}