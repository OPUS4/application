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

class Application_Form_Validate_Gnd extends Zend_Validate_Abstract
{
    /**
     * Error message for numbers that are too long.
     */
    public const NOT_VALID_FORMAT = 'notValidFormat';

    /**
     * Error message for numbers that have an invalid check digit.
     */
    public const NOT_VALID_CHECKSUM = 'notValidChecksum';

    /**
     * Pattern for format checking.
     */
    public const PATTERN = '/^[1-9]\d{6,10}-?[0-9X]/';

    /**
     * Translation keys for validation errors.
     *
     * @var array
     * @phpcs:disable
     */
    protected $_messageTemplates = [
        self::NOT_VALID_FORMAT   => 'validation_error_person_gnd',
        self::NOT_VALID_CHECKSUM => 'validation_error_person_gnd_checksum',
    ];
    // @phpcs:enable

    /**
     * Returns true if the gnd identifier can be validated.
     *
     * @param  string $value
     * @return bool
     * @throws Zend_Validate_Exception If validation of $value is impossible.
     */
    public function isValid($value)
    {
        // TODO andere formate zulassen
        if (strlen($value) > 11 || strlen($value) < 8 || ! preg_match(self::PATTERN, $value)) {
            $this->_error(self::NOT_VALID_FORMAT);
            return false;
        }

        if (preg_match('/.*-./', $value)) {
            $number     = substr($value, 0, strlen($value) - 2);
            $checkDigit = substr($value, -2);
            $valid      = self::generateCheckDigitPreGnd($number) !== $checkDigit;
        } else {
            $number     = substr($value, 0, strlen($value) - 1);
            $checkDigit = substr($value, -1);
            $valid      = self::generateCheckDigit($number) === $checkDigit;
        }

        if ($valid) {
            return true;
        }

        $this->_error(self::NOT_VALID_CHECKSUM);
        return false;
    }

    /**
     * Calculates the GND check digit.
     *
     * @param string $number
     * @return string
     */
    public function generateCheckDigit($number)
    {
        $total  = $this->calculateSum($number);
        $result = (11 - ($total % 11)) % 11;
        return $result === 10 ? "X" : (string) $result;
    }

    /**
     * @param string $number
     * @return string
     */
    public function generateCheckDigitPreGnd($number)
    {
        $total  = $this->calculateSum($number);
        $result = (11 - (11 - $total % 11)) % 11;
        return (string) $result;
    }

    /**
     * @param string $number
     * @return int
     */
    protected function calculateSum($number)
    {
        $total  = 0;
        $weight = 11 - (10 - strlen($number));
        for ($i = 0; $i < strlen($number); $i++) {
            $digit  = intval($number[$i]);
            $total += $digit * $weight;
            $weight--;
        }
        return $total;
    }
}
