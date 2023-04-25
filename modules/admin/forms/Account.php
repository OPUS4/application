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
use Opus\Common\AccountInterface;
use Opus\Common\Model\NotFoundException;

/**
 * Account administration form.
 *
 * TODO handle password change validation internally (not in AccountController)
 *      by checking the context and the edit mode
 * TODO clean up MODE handling (see constructor)
 */
class Admin_Form_Account extends Application_Form_Model_Abstract
{
    public const ELEMENT_LOGIN            = 'username';
    public const ELEMENT_FIRST_NAME       = 'firstname';
    public const ELEMENT_LAST_NAME        = 'lastname';
    public const ELEMENT_EMAIL            = 'email';
    public const ELEMENT_PASSWORD         = 'password';
    public const ELEMENT_PASSWORD_CONFIRM = 'confirmPassword';

    public const SUBFORM_ROLES = 'roles';

    public const MODE_NEW  = 'new';
    public const MODE_EDIT = 'edit';

    /** @var string Mode modifies validation depending on if an account is being created or edited. */
    private $mode;

    /**
     * Constructs empty form or populates it with values from Account($id).
     *
     * @param int|null $id TODO BUG int should not be null
     */
    public function __construct($id = null)
    {
        // TODO cannot call setMode() here because it access elements created later in init()
        $this->mode = empty($id) ? self::MODE_NEW : self::MODE_EDIT;

        parent::__construct();

        if ($this->getMode() === self::MODE_EDIT) {
            $account = Account::get($id);
            $this->populateFromModel($account);
        }

        $this->setMode($this->mode);
    }

    /**
     * Create form elements.
     */
    public function init()
    {
        parent::init();

        $this->setLabelPrefix('admin_account_label_');
        $this->setUseNameAsLabel(true);
        $this->setModelClass(Account::class);

        $this->addElement('login', self::ELEMENT_LOGIN);

        $this->getElement(self::ELEMENT_LOGIN)->addValidator(
            new Application_Form_Validate_LoginAvailable(
                ['ignoreCase' => $this->mode === self::MODE_EDIT]
            )
        );

        $this->addElement('text', self::ELEMENT_FIRST_NAME);
        $this->addElement('text', self::ELEMENT_LAST_NAME);
        $this->addElement('email', self::ELEMENT_EMAIL);
        $this->addElement('password', self::ELEMENT_PASSWORD);
        $this->addElement('password', self::ELEMENT_PASSWORD_CONFIRM);

        // add password validator
        $confirmPassword   = $this->getElement(self::ELEMENT_PASSWORD_CONFIRM);
        $passwordValidator = new Application_Form_Validate_Password();
        $confirmPassword->setValidators([$passwordValidator]);

        $roles = new Admin_Form_UserRoles();

        $this->addSubForm($roles, self::SUBFORM_ROLES);
    }

    /**
     * Populate the form values from Account instance.
     *
     * @param AccountInterface $account
     */
    public function populateFromModel($account)
    {
        $this->getElement(self::ELEMENT_MODEL_ID)->setValue($account->getId());
        $this->getElement(self::ELEMENT_LOGIN)->setValue(strtolower($account->getLogin() ?? ''));
        $this->getElement(self::ELEMENT_FIRST_NAME)->setValue($account->getFirstName());
        $this->getElement(self::ELEMENT_LAST_NAME)->setValue($account->getLastName());
        $this->getElement(self::ELEMENT_EMAIL)->setValue($account->getEmail());

        $rolesForm = $this->getSubForm(self::SUBFORM_ROLES);

        $rolesForm->populateFromModel($account);

        // current user cannot remode administrator permission
        // TODO does it make sense?
        $adminRoleElement = $rolesForm->getElement('administrator');
        if (Zend_Auth::getInstance()->getIdentity() === strtolower($account->getLogin() ?? '')) {
            $adminRoleElement->setAttrib('disabled', true);
        }
    }

    /**
     * @param AccountInterface $account
     */
    public function updateModel($account)
    {
        $logout = false;

        if ($this->isLoginChanged()) {
            $account->setLogin($this->getElementValue(self::ELEMENT_LOGIN));
            $logout = true;
        }

        $account->setFirstName($this->getElementValue(self::ELEMENT_FIRST_NAME));
        $account->setLastName($this->getElementValue(self::ELEMENT_LAST_NAME));
        $account->setEmail($this->getElementValue(self::ELEMENT_EMAIL));

        if ($this->isPasswordChanged()) {
            $account->setPassword($this->getElementValue(self::ELEMENT_PASSWORD));
            $logout = true;
        }

        $rolesForm = $this->getSubForm(self::SUBFORM_ROLES);
        $rolesForm->updateModel($account);

        // TODO storing of model happens in ActionCRUD controller class -> need way to move this into controller
        // logout current user if login or password has changed
        if ($this->isCurrentUser() && $logout) {
            Zend_Auth::getInstance()->clearIdentity();
        }
    }

    /**
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * @param string $mode
     */
    public function setMode($mode)
    {
        $this->mode = $mode;

        if ($mode === self::MODE_EDIT) {
            // when editing account password isn't required
            $this->getElement(self::ELEMENT_PASSWORD)->setRequired(false);
            $this->getElement(self::ELEMENT_PASSWORD_CONFIRM)->setRequired(false);
            // force validation on empty field to check identity to password
            $this->getElement(self::ELEMENT_PASSWORD_CONFIRM)->setAllowEmpty(false);
        } else {
            // when creating new account password is required
            $this->getElement(self::ELEMENT_PASSWORD)->setRequired(true);
            $this->getElement(self::ELEMENT_PASSWORD_CONFIRM)->setRequired(true);
            // password confirmation must not be empty
            $this->getElement(self::ELEMENT_PASSWORD_CONFIRM)->setAllowEmpty(true);
        }
    }

    /**
     * @param array $values
     * @return bool
     * @throws Zend_Form_Exception
     * @throws NotFoundException
     */
    public function isValid($values)
    {
        if (isset($values[self::ELEMENT_MODEL_ID])) {
            $accountId = $values[self::ELEMENT_MODEL_ID];

            if (! empty($accountId)) {
                $this->setMode(self::MODE_EDIT);
                $account            = Account::get($accountId);
                $values['oldLogin'] = $account->getLogin();
            }
        }

        $passwordChanged = false;

        if (empty($values[self::ELEMENT_PASSWORD])) {
            if ($this->getMode() === self::MODE_EDIT) {
                $values[self::ELEMENT_PASSWORD]         = 'notchanged';
                $values[self::ELEMENT_PASSWORD_CONFIRM] = 'notchanged';
            }
        } else {
            $passwordChanged = true;
        }

        $result = parent::isValid($values);

        if (! $passwordChanged) {
            $this->getElement(self::ELEMENT_PASSWORD)->setValue(null);
            $this->getElement(self::ELEMENT_PASSWORD_CONFIRM)->setValue(null);
        }

        return $result;
    }

    /**
     * @return bool
     * @throws NotFoundException
     */
    public function isLoginChanged()
    {
        $accountId = $this->getElementValue(self::ELEMENT_MODEL_ID);

        if (! empty($accountId)) {
            $account  = Account::get($accountId);
            $oldLogin = $account->getLogin();
        } else {
            $oldLogin = null;
        }

        return $oldLogin !== $this->getElementValue(self::ELEMENT_LOGIN);
    }

    /**
     * @return bool
     * @throws NotFoundException
     */
    public function isCurrentUser()
    {
        $currentUser = Zend_Auth::getInstance()->getIdentity();

        $accountId = $this->getElementValue(self::ELEMENT_MODEL_ID);

        if (! empty($accountId)) {
            $account  = Account::get($accountId);
            $oldLogin = $account->getLogin();
        } else {
            $oldLogin = null;
        }

        return $currentUser === $oldLogin;
    }

    /**
     * @return bool
     */
    public function isPasswordChanged()
    {
        return ! empty($this->getElementValue(self::ELEMENT_PASSWORD));
    }

    /**
     * @return $this
     */
    public function populate(array $values)
    {
        parent::populate($values);

        $accountId = $this->getElement(self::ELEMENT_MODEL_ID);

        if (! empty($accountId)) {
            $this->setMode(self::MODE_EDIT);
        } else {
            $this->setMode(self::MODE_NEW);
        }

        return $this;
    }

    /*
    public function rest() {

            // find out if administrator


            if (!$hasAdministratorRole && $isCurrentUser) {
                $newRoles[] = UserRole::fetchByName('administrator');
            }

            $account->setRole($newRoles);

        }

        return $result;
    }*/
}
