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

class Application_Form_Validate_Orcid extends Zend_Validate_Abstract
{
    /**
     * Constant for message for invalid format.
     */
    public const NOT_VALID_FORMAT = 'notValidFormat';

    /**
     * Constant for message for invalid checksum.
     */
    public const NOT_VALID_CHECKSUM = 'notValidChecksum';

    /**
     * Pattern for checking ORC-ID format.
     */
    public const PATTERN = '/\d{4}-\d{4}-\d{4}-\d{3}[0-9X]/';

    /**
     * Translation keys for validation messages.
     *
     * @var string[]
     * @phpcs:disable
     */
    protected $_messageTemplates = [
        self::NOT_VALID_FORMAT   => 'validation_error_person_orcid',
        self::NOT_VALID_CHECKSUM => 'validation_error_person_orcid_checksum',
    ];
    // @phpcs:enable

    /**
     * Returns true if the orcid identifier can be validated.
     *
     * @param  mixed $value
     * @return bool
     * @throws Zend_Validate_Exception If validation of $value is impossible.
     */
    public function isValid($value)
    {
        if (strlen($value) !== 19 || ! preg_match(self::PATTERN, $value)) {
            $this->_error(self::NOT_VALID_FORMAT);
            return false;
        }

        if ($this->generateCheckDigit(substr($value, 0, 18)) !== substr($value, -1)) {
            $this->_error(self::NOT_VALID_CHECKSUM);
            return false;
        }

        return true;
    }

    /**
     * Generates the ORC-ID check digit.
     *
     * @param string $baseDigits Number without check digit
     * @return string check digit
     */
    public static function generateCheckDigit($baseDigits)
    {
        $total = 0;
        for ($i = 0; $i < strlen($baseDigits); $i++) {
            if ($baseDigits[$i] !== '-') {
                $digit = intval($baseDigits[$i]);
                $total = ($total + $digit) * 2;
            }
        }
        $remainder = $total % 11;
        $result    = (12 - $remainder) % 11;
        return $result === 10 ? "X" : (string) $result;
    }
}
