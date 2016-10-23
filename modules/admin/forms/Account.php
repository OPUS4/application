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

    private $_mode;

    /**
     * Constructs empty form or populates it with values from Opus_Account($id).
     * @param mixed $id
     */
    public function __construct($id = null) {
        $env = (empty($id)) ? 'new' : 'edit';

        $this->_mode = $env;

        $config = new Zend_Config_Ini(
            APPLICATION_PATH .
            '/modules/admin/forms/account.ini', $env
        );

        parent::__construct($config->form->account);

        if (!empty($id)) {
            $account = new Opus_Account($id);

            $this->populateFromAccount($account);

            // when editing account password isn't required
            $this->getElement('password')->setRequired(false);
            $this->getElement('confirmPassword')->setRequired(false);
            // force validation on empty field to check identity to password
            $this->getElement('confirmPassword')->setAllowEmpty(false);
        }
    }

    /**
     * Create form elements.
     */
    public function init() {
        parent::init();

        $this->getElement('username')->addValidator(
            new Application_Form_Validate_LoginAvailable(
                array('ignoreCase' => $this->_mode === 'edit')
            )
        );

        // add password validator
        $confirmPassword = $this->getElement('confirmPassword');
        $passwordValidator = new Application_Form_Validate_Password();
        $confirmPassword->addValidator($passwordValidator);

        // add form elements for selecting roles
        $this->_addRolesGroup();
    }

    /**
     * Populate the form values from Opus_Account instance.
     * @param <type> $account
     */
    public function populateFromAccount($account) {
        $this->getElement('username')->setValue(strtolower($account->getLogin()));
        $this->getElement('firstname')->setValue($account->getFirstName());
        $this->getElement('lastname')->setValue($account->getLastName());
        $this->getElement('email')->setValue($account->getEmail());

        $roles = $account->getRole();

        $this->setSelectedRoles($roles);

        $adminRoleElement = $this->getElement('roleadministrator');

        if (Zend_Auth::getInstance()->getIdentity() === strtolower($account->getLogin())) {
            $adminRoleElement->setAttrib('disabled', true);
        }
    }

}
