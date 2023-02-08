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
 * @copyright   Copyright (c) 2017, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Custom HTTP auth adapter for OPUS 4.
 *
 * This class is needed because the passwords in the database are hashed.
 */
class Application_Security_HttpAuthAdapter extends Zend_Auth_Adapter_Http
{
    /**
     * Compares two string hashing the second string.
     *
     * The second string is the password from the request and needs to be hashed before it can be compared to the
     * string stored in the OPUS 4 database.
     *
     * @param string $a Password from database
     * @param string $b Password from request
     * @return bool true - if strings are identical
     * @phpcs:disable PSR2.Methods.MethodDeclaration
     */
    protected function _secureStringCompare($a, $b)
    {
        // @phpcs:enable
        return parent::_secureStringCompare($a, sha1($b));
    }
}
