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
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\Account;
use Opus\Common\AccountInterface;
use Opus\Common\UserRole;

/**
 * Controller for administration of user accounts.
 *
 * This controller allows creating, editing and removing user accounts.
 *
 * - For new accounts the login must not already exist.
 * - The password has to be entered twice for validation.
 * - The guest role is always checked and disabled, because every user, annonymous or not, has at least that role.
 * - The admin user cannot be removed.
 *
 * For editing accounts:
 *
 * - If the admin user is edited the 'administrator' role cannot be removed.
 * - Validation for passwords is disabled unless something is entered into the fields.
 * - If login or password of the current user are changed, the user is logged out.
 */
class Admin_AccountController extends Application_Controller_ActionCRUD
{
    public function init()
    {
        $this->setFormClass(Admin_Form_Account::class);
        parent::init();
    }

    /**
     * @return Application_Form_Model_Table
     */
    public function getIndexForm()
    {
        $form = parent::getIndexForm();
        $form->setViewScript('account/modeltable.phtml');
        return $form;
    }

    /**
     * Admin and current user account cannot be deleted.
     *
     * @param AccountInterface $account
     * @return bool
     */
    public function isDeletable($account)
    {
        $login = $account->getLogin();

        return (Zend_Auth::getInstance()->getIdentity() !== strtolower($login)) && ($login !== 'admin');
    }

    /**
     * @param AccountInterface $model
     * @return Application_Form_ModelFormInterface
     */
    public function getEditModelForm($model)
    {
        $form = parent::getEditModelForm($model);
        $form->setMode(Admin_Form_Account::MODE_EDIT);
        return $form;
    }

    /**
     * Shows account information.
     *
     * TODO update look of page
     * TODO move code into model classes for easier testing
     *
     * @return AccountInterface // TODO BUG ???
     */
    public function showAction()
    {
        $this->view->title = $this->view->translate('admin_account_show');

        $id = $this->getRequest()->getParam('id');
        if (empty($id)) {
            $this->getLogger()->debug('Missing parameter account id.');
            $this->_helper->redirector('index');
        }

        $modules = array_keys(Application_Modules::getInstance()->getModules());
        unset($modules['default']);

        $this->view->allModules = $modules;

        $account             = Account::get($id);
        $this->view->account = $account;

        // Get all UserRoles for current Account *plus* 'guest'
        $roles = [];
        foreach ($account->getRole() as $roleLinkModel) {
            $roles[] = $roleLinkModel->getModel();
        }

        $guestRole = UserRole::fetchByName('guest');
        if ($guestRole !== null) {
            $roles[] = $guestRole;
        }

        // Build module-roles table.
        $modulesRoles = [];
        foreach ($this->view->allModules as $module) {
            $modulesRoles[$module] = [];
        }

        foreach ($roles as $role) {
            $roleName    = $role->getName();
            $roleModules = $role->listAccessModules();

            foreach ($roleModules as $module) {
                if (! array_key_exists($module, $modulesRoles)) {
                    $modulesRoles[$module] = [];
                }

                $modulesRoles[$module][] = $roleName;
            }
        }

        foreach (array_keys($modulesRoles) as $module) {
            $modulesRoles[$module] = array_unique($modulesRoles[$module]);
            sort($modulesRoles[$module]);
        }

        $this->view->modulesRoles = $modulesRoles;

        return $account;
    }
}
