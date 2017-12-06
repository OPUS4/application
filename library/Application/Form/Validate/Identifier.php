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
 * @author      Maximilian Salomon <salomon@zib.de>
 * @copyright   Copyright (c) 2017, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Validator that delegates validation of identifiers depending on the type.
 *
 * This validator is used in the document metadate form.
 */
class Application_Form_Validate_Identifier extends Zend_Validate_Abstract
{

    /**
     * Form element for the type of identifier.
     * @var Zend_Form_Element
     */
    private $_element;

    /**
     * Application_Form_Validate_Identifier constructor.
     * @param Zend_Form_Element $element
     */
    public function __construct($element)
    {
        if ($element === null) {
            throw new InvalidArgumentException('Argument must not be NULL');
        }
        elseif ($element instanceof Zend_Form_Element) {
            $this->_element = $element;
        }
        else {
            throw new InvalidArgumentException('Object must be Zend_Form_Element');
        }
    }

    /**
     * Uses the config-file to delegate the validation of identifier.
     *
     * If there is a validator set for the chosen identifier, delegate at the class, which is linked in the config.
     * Check the identifier with isValid of the class and if its wrong, get the error-messages and set the right
     * message. If there is no validator for the chosen identifier and the value is not empty, the return is true.
     *
     * @param mixed $value inserted text
     * @return bool
     */
    public function isValid($value)
    {
        $value = (string)$value;
        $this->_setValue($value);

        $type = strtolower($this->_element->getValue());
        $config = Application_Configuration::getInstance()->getConfig();

        if (isset($config->identifier->validation->$type->class)) {
            $validatorClass = $config->identifier->validation->$type->class;
            $validator = new $validatorClass;
            $result = $validator->isValid($value);
            if ($result === false) {
                if (isset($config->identifier->validation->$type->messageTemplates)) {
                    $this->_messageTemplates = array_merge($validator->getMessageTemplates(), $config->identifier
                        ->validation->$type->messageTemplates->toArray());
                }
                else {
                    $this->_messageTemplates = $validator->getMessageTemplates();
                }
                foreach ($validator->getErrors() as $error) {
                    $this->_error($error);
                }
            }

            return $result;
        }
        else {
            if (!empty($value)) {
                return true;
            }
        }

        return false;
    }

}
