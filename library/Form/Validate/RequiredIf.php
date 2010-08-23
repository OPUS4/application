<?php
/**
 * This file is part of OPUS. The software OPUS has been originally developed
 * at the University of Stuttgart with funding from the German Research Net,
 * the Federal Department of Higher Education and Research and the Ministry
 * of Science, Research and the Arts of the State of Baden-Wuerttemberg.
 *
 * OPUS 4 is a complete rewrite of the original OPUS software and was developed
 * by the Stuttgart University Library, the Library Service Center
 * Baden-Wuerttemberg, the North Rhine-Westphalian Library Service Center,
 * the Cooperative Library Network Berlin-Brandenburg, the Saarland University
 * and State Library, the Saxon State Library - Dresden State and University
 * Library, the Bielefeld University Library and the University Library of
 * Hamburg University of Technology with funding from the German Research
 * Foundation and the European Regional Development Fund.
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
 * @package     Opus_Form
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id: Builder.php 4483 2010-06-08 16:17:55Z marahrens $
 */

/**
 * Validates a field depending on another field.
 *
 * The field becomes required, so it can't be empty, if another field meets a
 * certain condition.
 */
class Form_Validate_RequiredIf extends Zend_Validate_Abstract
{

    const FAILED = 'failed';

    /**
     * Name of other field.
     * @var string
     */
    private $dependsOnField;

    /**
     * Apply not to result or not.
     * @var <type>
     */
    private $negateResult = false;

    /**
     * Expected value in target field.
     * @var <type>
     */
    private $expectedValue = null;

    /**
     * Validator messages.
     * @var <type>
     */
    protected $_messageTemplates = array(
        self::FAILED => "The values entered are not the same."
    );

    public function __construct($options) {
        $this->dependsOnField = $options['target'];
        $this->negateResult = $options['negate'];
        $this->expectedValue = $options['targetValue'];
    }

    public function isValid($value, $context = null) 
    {
        $result = false;

        // check if field is not empty
        if (!empty($value)) {
            $result = true;
        }
        else {
            // check if field is required if it is empty
            $result = $this->_checkTargetField($context);
        }

        // Apply not to result if negateResult is true
        $result = ($result XOR $this->negateResult);

        // Set error message
        if (!$result) {
            $this->_error(self::FAILED);
        }

        return $result;
    }


    protected function _checkTargetField($context = null) {
        $result = true;

        if (is_array($context))
        {
            if (isset($context[$this->dependsOnField]))
            {
                $otherValue = $context[$this->dependsOnField];

                if (empty($this->expectedValue)) {
                    // if no targetValue has been set check if notEmpty
                    $result = !Zend_Validate::is($otherValue, "NotEmpty");
                }
                else {
                    // check if targetValue is expected
                    $result = !Zend_Validate::is($otherValue, "Identical", array('token' => $this->expectedValue));
                }
            }
            else {
                // if value wasn't set
                $result = true;
            }
        }
        else {
            // TODO throw something and log
        }

        return $result;
    }

}

?>
