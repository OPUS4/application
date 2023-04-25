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

use Opus\Common\AccountInterface;

class Account_Form_Account extends Application_Form_Model_Abstract
{
    public const ELEMENT_LOGIN            = 'username';
    public const ELEMENT_FIRSTNAME        = 'firstname';
    public const ELEMENT_LASTNAME         = 'lastname';
    public const ELEMENT_EMAIL            = 'email';
    public const ELEMENT_PASSWORD         = 'password';
    public const ELEMENT_CONFIRM_PASSWORD = 'confirm';
    public const ELEMENT_SUBMIT           = 'submit';

    public function init()
    {
        parent::init();

        $this->setUseNameAsLabel(true);
        $this->setLabelPrefix('admin_account_label_');

        $this->addElement('Login', self::ELEMENT_LOGIN, [
            'label' => 'admin_account_label_login',
        ]);
        $this->getElement(self::ELEMENT_LOGIN)->addValidator(
            new Application_Form_Validate_LoginAvailable(['ignoreCase' => true])
        );

        $this->addElement('Text', self::ELEMENT_FIRSTNAME);
        $this->addElement('Text', self::ELEMENT_LASTNAME);
        $this->addElement('Email', self::ELEMENT_EMAIL);

        $this->addElement('Password', self::ELEMENT_PASSWORD);
        $this->addElement('Password', self::ELEMENT_CONFIRM_PASSWORD, [
            'label' => 'admin_account_label_confirmPassword',
        ]);

        $this->getElement(self::ELEMENT_CONFIRM_PASSWORD)->addValidator(
            new Application_Form_Validate_Password()
        );

        $this->getElement(self::ELEMENT_PASSWORD)->addErrorMessages(
            [Zend_Validate_StringLength::TOO_SHORT => 'admin_account_error_password_tooshort']
        );
    }

    /**
     * @param AccountInterface $account
     */
    public function populateFromModel($account)
    {
        $login = strtolower($account->getLogin());

        $this->getElement(self::ELEMENT_LOGIN)->setValue($login);

        $config = $this->getApplicationConfig();

        $this->getElement('firstname')->setValue($account->getFirstName());
        $this->getElement('lastname')->setValue($account->getLastName());
        $this->getElement('email')->setValue($account->getEmail());

        if (isset($config->account->editPasswordOnly) && filter_var($config->account->editPasswordOnly, FILTER_VALIDATE_BOOLEAN)) {
            $this->getElement('username')->setAttrib('disabled', true);
            $this->getElement('firstname')->setAttrib('disabled', true);
            $this->getElement('lastname')->setAttrib('disabled', true);
            $this->getElement('email')->setAttrib('disabled', true);
        } elseif (isset($config->account->changeLogin) && (! filter_var($config->account->changeLogin, FILTER_VALIDATE_BOOLEAN))) {
            $this->getElement('username')->setAttrib('disabled', true);
        }

        if ($login === 'admin') {
            $this->getElement('username')->setAttrib('disabled', true);
        }
    }

    /**
     * @param AccountInterface $account
     */
    public function updateModel($account)
    {
    }
}
