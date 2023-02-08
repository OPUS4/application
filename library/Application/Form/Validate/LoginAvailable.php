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
 * Checks if a login already exists.
 */
class Application_Form_Validate_LoginAvailable extends Zend_Validate_Abstract
{
    /**
     * Constant for login is not available anymore.
     */
    public const NOT_AVAILABLE = 'isAvailable';

    /**
     * If this is set to true, the validation assumes an account is available
     * if the old login name and the new one only differ in upper or lower case
     * characters. This is used to avoid validation errors if an existing account
     * is edited.
     *
     * @var bool
     */
    private $ignoreCase = false;

    /**
     * @param array|null $options
     */
    public function __construct($options = null)
    {
        if (isset($options['ignoreCase'])) {
            $this->ignoreCase = $options['ignoreCase'];
        }
    }

    /**
     * Error messages.
     *
     * @var array
     * @phpcs:disable
     */
    protected $_messageTemplates = [
        self::NOT_AVAILABLE => 'admin_account_error_login_used',
    ];
    // @phpcs:enable

    /**
     * Checks if a login already exists.
     *
     * Returns true if a login does not exist or if the oldLogin value equals
     * the current value. Which means the login hasn't changed.
     *
     * TODO Is there a better way to deal with updates?
     *
     * @param string     $value
     * @param array|null $context
     * @return bool
     */
    public function isValid($value, $context = null)
    {
        $value = (string) $value;

        $this->_setValue($value);

        $oldLogin = null;

        if (is_array($context)) {
            if (isset($context['oldLogin'])) {
                $oldLogin = $context['oldLogin'];
            }
        } elseif (is_string($context)) {
            $oldLogin = $context;
        }

        if ($this->ignoreCase) {
            $value    = $value !== null ? strtolower($value) : null;
            $oldLogin = $oldLogin !== null ? strtolower($oldLogin) : null;
        }

        if ($this->isLoginUsed($value) && $oldLogin !== $value) {
            $this->_error(self::NOT_AVAILABLE);
            return false;
        }

        return true;
    }

    /**
     * Checks if a login name already exists in database.
     *
     * @param string $login
     * @return bool
     */
    protected function isLoginUsed($login)
    {
        try {
            Account::fetchAccountByLogin($login);
        } catch (Exception $ex) {
            return false;
        }
        return true;
    }
}
