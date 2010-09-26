<?php
/*
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
 * @category    TODO
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Account administration form.
 *
 */
class Admin_Form_Account extends Admin_Form_RolesAbstract {

    /**
     * Constructs empty form or populates it with values from Opus_Account($id).
     * @param mixed $id
     */
    public function __construct($id = null) {
        $env = (empty($id)) ? 'new' : 'edit';

        $config = new Zend_Config_Ini(APPLICATION_PATH .
                '/modules/admin/forms/account.ini', $env);

        parent::__construct($config->form->account);

        if (!empty($id)) {
            $account = new Opus_Account($id);

            $this->populateFromAccount($account);
        }
    }

    /**
     * Create form elements.
     */
    public function init() {
        parent::init();

        $this->getElement('username')->addValidator(
                new Form_Validate_LoginAvailable());

        // add password validator
        $confirmPassword = $this->getElement('confirmPassword');
        $passwordValidator = new Form_Validate_Password();
        $confirmPassword->addValidator($passwordValidator);

        // add form elements for selecting roles
        $this->_addRolesGroup();
    }
    
    /**
     * Populate the form values from Opus_Account instance.
     * @param <type> $account
     */
    public function populateFromAccount($account) {
        $this->getElement('username')->setValue($account->getLogin());

        $roles = $account->getRole();

        $this->setSelectedRoles($roles);

        $adminRoleElement = $this->getElement('roleadministrator');

        if (Zend_Auth::getInstance()->getIdentity() === $account->getLogin()) {
            $adminRoleElement->setAttrib('disabled', true);
        }
    }

}
?>
