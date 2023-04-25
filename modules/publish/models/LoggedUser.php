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
use Opus\Common\Log;
use Opus\Common\Person;
use Opus\Common\PersonInterface;
use Opus\Common\Security\SecurityException;

class Publish_Model_LoggedUser
{
    /** @var Zend_Log */
    private $log;

    /** @var string */
    private $login;

    /** @var AccountInterface */
    private $account;

    public function __construct()
    {
        $this->log = Log::get();

        $login = Zend_Auth::getInstance()->getIdentity();
        if ($login === null || trim($login) === '') {
            return;
        }

        try {
            $account = Account::fetchAccountByLogin($login);
        } catch (SecurityException $ex) {
            $account = null;
        }

        if ($account === null || $account->isNewRecord()) {
            $this->log->err("Error checking logged user: Invalid account returned for user '$login'!");
            return;
        }

        $this->login   = $login;
        $this->account = $account;
    }

    /**
     * Get ID of Account object.  Return null if no account has been found.
     *
     * @return int
     */
    public function getUserId()
    {
        return isset($this->account) ? $this->account->getId() : null;
    }

    /**
     * Create Person object for currently logged user.  If no account
     * has been found, return NULL.
     *
     * @return PersonInterface|null
     */
    public function createPerson()
    {
        if ($this->account === null) {
            return null;
        }

        $person = Person::new();

        $firstName = $this->account->getFirstName();
        if ($firstName !== null) {
            $person->setFirstName(trim($firstName)); // TODO trimming for values is/should be centralized
        }

        $person->setLastName(trim($this->account->getLastName() ?? ''));

        $email = $this->account->getEmail();
        if ($email !== null) {
            $person->setEmail(trim($email));
        }

        if (! $person->isValid()) {
            $this->log->err('Created Opus_Person object for user \'' . $this->login . '\' is NOT VALID. ');
        }

        return $person;
    }
}
