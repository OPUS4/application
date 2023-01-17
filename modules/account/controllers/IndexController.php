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
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\Account;

/**
 * Controller for editing account of logged in user.
 */
class Account_IndexController extends Application_Controller_Action
{
    /**
     * Custom access check to be called by parent class.  Returns the value of
     * config key "account.editOwnAccount" if set; false otherwise.
     *
     * @return bool
     */
    protected function customAccessCheck()
    {
        $parentValue = parent::customAccessCheck();

        $config = $this->getConfig();

        if (! isset($config) || ! isset($config->account->editOwnAccount)) {
            return false;
        }

        return $parentValue && filter_var($config->account->editOwnAccount, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Show account form for logged in user.
     *
     * TODO show title on page H" $this->view->title;
     */
    public function indexAction()
    {
        $login = Zend_Auth::getInstance()->getIdentity();

        if (! empty($login)) {
            $accountForm = new Account_Form_Account();
            $account     = Account::fetchAccountByLogin($login);
            $accountForm->populateFromModel($account);

            $actionUrl = $this->view->url(['action' => 'save']);

            $accountForm->setAction($actionUrl);

            $this->renderForm($accountForm);
        } else {
            $params = $this->_helper->returnParams->getReturnParameters();
            $this->_helper->redirector->gotoSimple('index', 'auth', 'default', $params);
        }
    }

    /**
     * Save account information.
     *
     * TODO move logic into model or form
     */
    public function saveAction()
    {
        $login = Zend_Auth::getInstance()->getIdentity();

        $config = $this->getConfig();
        $logger = $this->getLogger();

        if (! empty($login) && $this->getRequest()->isPost()) {
            $accountForm = new Account_Form_Account();
            $account     = Account::fetchAccountByLogin($login);
            $accountForm->populateFromModel($account);

            $postData = $this->getRequest()->getPost();

            $isPasswordChanged = true;

            if (empty($postData['password'])) {
                // modify to pass default validation
                // TODO think about better solution
                $postData[Account_Form_Account::ELEMENT_PASSWORD]         = 'notchanged';
                $postData[Account_Form_Account::ELEMENT_CONFIRM_PASSWORD] = 'notchanged';
                $isPasswordChanged                                        = false;
            }

            // check if username was provided and if it may be changed
            if (
                ! isset($postData['username'])
                    || (isset($config->account->editPasswordOnly) && filter_var($config->account->editPasswordOnly, FILTER_VALIDATE_BOOLEAN))
                    || (isset($config->account->changeLogin) && (! filter_var($config->account->changeLogin, FILTER_VALIDATE_BOOLEAN)))
            ) {
                $postData['username'] = $login;
            }

            $postData['oldLogin'] = $login;

            if ($accountForm->isValid($postData)) {
                $account = Account::fetchAccountByLogin($login);

                $newLogin  = $postData['username'];
                $password  = $postData['password'];
                $firstname = $postData['firstname'];
                $lastname  = $postData['lastname'];
                $email     = $postData['email'];

                $isLoginChanged = false;

                if (isset($config->account->editPasswordOnly) && (! filter_var($config->account->editPasswordOnly, FILTER_VALIDATE_BOOLEAN))) {
                    $account->setFirstName($firstname);
                    $account->setLastName($lastname);
                    $account->setEmail($email);

                    $logger->debug('login = ' . $login);
                    $logger->debug('new login = ' . $newLogin);

                    $isLoginChanged = $login === $newLogin ? false : true;

                    if ($isLoginChanged && ($login !== 'admin')) {
                        $logger->debug('login changed');
                        $account->setLogin($newLogin);
                    }
                }

                if ($isPasswordChanged) {
                    $logger->debug('Password changed');
                    $account->setPassword($password);
                }

                $account->store();

                if ($isLoginChanged || $isPasswordChanged) {
                    Zend_Auth::getInstance()->clearIdentity();
                    $this->_helper->redirector->redirectToAndExit('index', 'account_password_change_success', 'index', 'auth');
                    return;
                }
            } else {
                $actionUrl = $this->view->url(['action' => 'save']);
                $accountForm->setAction($actionUrl);

                $this->renderForm($accountForm);
                return;
            }
        }

        $this->_helper->redirector('index');
    }
}
