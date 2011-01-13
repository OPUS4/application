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
 * @author      Thoralf Klein <thoralf.klein@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
class Publish_Model_LoggedUser {
    
    private $_log     = null;
    private $_login   = null;
    private $_account = null;

    public function __construct() {
        $this->_log = Zend_Registry::get("Zend_Log");

        $login = Zend_Auth::getInstance()->getIdentity();
        if (is_null($login) or trim($login) == '') {
            return;
        }

        $account = Opus_Account::fetchAccountByLogin($login);
        if (is_null($account) or $account->isNewRecord()) {
            $this->_log->err("Error checking logged user: Invalid account returned for user '$login'!");
            return;
        }

        $this->_login   = $login;
        $this->_account = $account;
    }

    public function getUserId() {
        return isset($this->_account) ? $this->_account->getId() : null;
    }

    public function createPerson() {
        if (is_null($this->_account)) {
            return;
        }

        $person = new Opus_Person();
        $person->setFirstName(trim($this->_account->getFirstName()));
        $person->setLastName(trim($this->_account->getLastName()));
        $person->setEmail(trim($this->_account->getEmail()));

        if (!$person->isValid()) {
            $this->_log->err("Created Opus_Person object for user '" . $this->_login . "' is NOT VALID. ");
        }

        return $person;
    }
}
