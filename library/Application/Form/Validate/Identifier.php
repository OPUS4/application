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
 * Class for delegate validator for identifier in Admin-Form.
 */
class Application_Form_Validate_Identifier extends Zend_Validate_Abstract
{

    /**
     * Represent the identifier Form_Element.
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
     * Delegate the validation.
     * @param mixed $value inserted text
     * @return bool
     */
    public function isValid($value)
    {
        $value = (string)$value;
        $this->_setValue($value);

        /**
         * At this point, we check the type of an identifier. If this is maybe ISBN, we delegate the validation to the
         * ISBN-Validator. If the ISBN is not valid in this case, we take the Errormessages of the ISBN-Class and
         * give them out. This is important, to make the code variable.
         */
        switch (strtoupper($this->_element->getValue()))
        {
            case 'ISBN':
                $validateISBN = new Opus_Validate_Isbn();
                $result = $validateISBN->isValid($value);
                $this->_messageTemplates = $validateISBN->getMessageTemplates();
                if ($result === false) {
                    foreach ($validateISBN->getErrors() as $error) {
                        $this->_error($error);
                    }
                }
                return $result;

            default:
                if (!empty($value)) {
                    return true;
                }
        }

        return false;
    }

}