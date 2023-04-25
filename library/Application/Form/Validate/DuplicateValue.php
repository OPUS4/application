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

use Opus\Common\Log;

/**
 * Checks if in an edit form a value is selected more than once for a field.
 *
 * This form check if the same element across multiple subforms has been set to
 * the same value.
 *
 * This validator is used to check if the same language has been selected more
 * than once for document titles or abstracts. This works independent of the
 * actual document, because the POST for editing the titles contains all the
 * titles of the same type in a subform, each title in its own additional
 * subform. For instance like this:
 *
 * TitleMain form (array)
 *   +-> title main 1 form (array)
 *   +-> title main 2 form (array)
 *
 * TODO Basisklasse mit getLogger
 */
class Application_Form_Validate_DuplicateValue extends Zend_Validate_Abstract
{
    /**
     * Error constant for language ID that does not exist.
     */
    public const NOT_VALID = 'notValid';

    /**
     * Werte fuer Feld in benachbarten Formularen.
     *
     * @var array
     */
    private $values;

    /**
     * Position des Unterformulares.
     *
     * Die Position ist wichtig damit nicht mit nachfolgenden Werten verglichen wird. Dadurch gibt es keine
     * Fehlermeldung beim ersten auftreten eines Wertes.
     *
     * @var int
     */
    private $position;

    /**
     * Error messages.
     *
     * @var string[]
     * @phpcs:disable
     */
    protected $_messageTemplates = [
        self::NOT_VALID => 'admin_validate_error_duplicated_value',
    ];
    // @phpcs:enable

    /**
     * Konstruiert Validator.
     *
     * @param array       $values Werte der benachbarten Unterformulare
     * @param int         $position Position des Unterformulars
     * @param string|null $message Fehlermeldung
     */
    public function __construct($values, $position, $message = null)
    {
        $this->values   = $values;
        $this->position = $position;
        if ($message !== null) {
            $this->setMessage($message, self::NOT_VALID);
        }
    }

    /**
     * Checks if the elements of subforms have same value.
     *
     * The function assumes that the context contains multiple arrays (subforms)
     * that contain the same element.
     *
     * @param string     $value Does not matter for this validator
     * @param array|null $context Values of all the subforms
     * @return bool
     */
    public function isValid($value, $context = null)
    {
        $value = (string) $value;
        $this->_setValue($value);

        $valueCount = count($this->values);

        if (! $this->position < $valueCount) {
             Log::get()->err(
                 __CLASS__
                 . ' mit Position > count(values) konstruiert.'
             );
        }

        if ($this->values !== null) {
            for ($index = 0; $index < $this->position && $index < $valueCount; $index++) {
                if ($this->isEqual($value, $context, $this->values[$index])) {
                    $this->_error(self::NOT_VALID);
                    return false;
                }
            }
        } else {
             Log::get()->err(__CLASS__ . ' mit Values = NULL konstruiert.');
        }

        return true;
    }

    /**
     * @param string $value
     * @param array  $context
     * @param string $other
     * @return bool
     */
    protected function isEqual($value, $context, $other)
    {
        return $value === $other;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @return array
     */
    public function getValues()
    {
        return $this->values;
    }
}
