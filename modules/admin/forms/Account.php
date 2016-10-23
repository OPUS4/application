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
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Account administration form.
 *
 * TODO handle password change validation internally (not in AccountController)
 * by checking the context and the edit mode
 */
class Admin_Form_Account extends Admin_Form_RolesAbstract {

    const ELEMENT_LOGIN = 'username';
    const ELEMENT_FIRST_NAME = 'firstname';
    const ELEMENT_LAST_NAME = 'lastname';
    const ELEMENT_EMAIL = 'email';
    const ELEMENT_PASSWORD = 'password';
    const ELEMENT_PASSWORD_CONFIRM = 'confirmPassword';

    private $_mode;

    /**
     * Constructs empty form or populates it with values from Opus_Account($id).
     * @param mixed $id
     */
    public function __construct($id = null) {
        parent::__construct();

        $env = (empty($id)) ? 'new' : 'edit';

        $this->_mode = $env;

        if (!empty($id)) {
            $account = new Opus_Account($id);

            $this->populateFromModel($account);

            // when editing account password isn't required
            $this->getElement(self::ELEMENT_PASSWORD)->setRequired(false);
            $this->getElement(self::ELEMENT_PASSWORD_CONFIRM)->setRequired(false);
            // force validation on empty field to check identity to password
            $this->getElement(self::ELEMENT_PASSWORD_CONFIRM)->setAllowEmpty(false);
        }
    }

    /**
     * Create form elements.
     */
    public function init() {
        parent::init();

        $this->setLabelPrefix('admin_account_label_');
        $this->setUseNameAsLabel(true);

        $this->addElement('login', self::ELEMENT_LOGIN);

        $this->getElement(self::ELEMENT_LOGIN)->addValidator(
            new Application_Form_Validate_LoginAvailable(
                array('ignoreCase' => $this->_mode === 'edit')
            )
        );

        $this->addElement('text', self::ELEMENT_FIRST_NAME);
        $this->addElement('text', self::ELEMENT_LAST_NAME);
        $this->addElement('email', self::ELEMENT_EMAIL);
        $this->addElement('password', self::ELEMENT_PASSWORD);
        $this->addElement('password', self::ELEMENT_PASSWORD_CONFIRM);

        // add password validator
        $confirmPassword = $this->getElement(self::ELEMENT_PASSWORD_CONFIRM);
        $passwordValidator = new Application_Form_Validate_Password();
        $confirmPassword->setValidators(array($passwordValidator));

        // add form elements for selecting roles
        $this->_addRolesGroup();
    }

    /**
     * Populate the form values from Opus_Account instance.
     * @param <type> $account
     */
    public function populateFromModel($account) {
        $this->getElement(self::ELEMENT_LOGIN)->setValue(strtolower($account->getLogin()));
        $this->getElement(self::ELEMENT_FIRST_NAME)->setValue($account->getFirstName());
        $this->getElement(self::ELEMENT_LAST_NAME)->setValue($account->getLastName());
        $this->getElement(self::ELEMENT_EMAIL)->setValue($account->getEmail());

        $roles = $account->getRole();

        $this->setSelectedRoles($roles);

        $adminRoleElement = $this->getElement('roleadministrator');

        if (Zend_Auth::getInstance()->getIdentity() === strtolower($account->getLogin())) {
            $adminRoleElement->setAttrib('disabled', true);
        }
    }

    public function updateModel($account) {
        $account->setLogin($this->getElementValue(self::ELEMENT_LOGIN));
        $account->setFirstName($this->getElementValue(self::ELEMENT_FIRST_NAME));
        $account->setLastName($this->getElementValue(self::ELEMENT_LAST_NAME));
        $account->setEmail($this->getElementValue(self::ELEMENT_EMAIL));
        $account->setPassword($this->getElementValue(self::ELEMENT_PASSWORD));
    }

}
