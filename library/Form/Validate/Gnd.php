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
 * @category    Application
 * @author      Michael Lang <lang@zib.de>
 * @copyright   Copyright (c) 2008-2014, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class Form_Validate_Gnd extends Zend_Validate_Abstract {

    /**
     * Constant for login is not available anymore.
     */
    const NOT_VALID = 'isAvailable';

    protected $_messageTemplates = array(
        self::NOT_VALID => 'admin_validate_person_gnd'
    );

    /**
     * Returns true if the gnd identifier can be validated.
     *
     * @param  mixed $value
     * @return boolean
     * @throws Zend_Validate_Exception If validation of $value is impossible
     */
    public function isValid($value) {
        if (strlen($value) > 11 || strlen($value) < 8) {
            $this->_error(self::NOT_VALID);
            return false;
        }
        if ($this->generateCheckDigit($value) != $value{strlen($value) - 1}) {
            $this->_error(self::NOT_VALID);
            return false;
        }
        return true;

    }

    /**
      * Calculates the GND check digit.
      */
    public static function generateCheckDigit($baseDigits) {
        $total = 0;
        $weight = 11;
        for ($i = 0; $i < strlen($baseDigits) - 1; $i++) {
            $digit = intval($baseDigits{$i});
            $total += $digit * ($weight - $i);
        }
        $remainder = $total % 11;
        $result = (11 - $remainder) % 11;
        $r = $result == 10 ? "X" : (string) $result;
        return $result == 10 ? "X" : (string) $result;
    }

}