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
 * @category    Application
 * @package     Form_Validate
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2015, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Class Application_Form_Validate_AtLeastOneNotEmpty validates that at least one of multiple fields has a value.
 *
 * @category    Application
 * @package     Form_Validate
 */
class Application_Form_Validate_AtLeastOneNotEmpty extends \Zend_Validate_Abstract
{

    const ALL_EMPTY = 'allElementsEmpty';

    /**
     * @var array List of form elements that need to contain at least one value in the group.
     */
    private $_elements;

    protected $_messageTemplates = [
        self::ALL_EMPTY => 'admin_collection_error_name_or_number_required'
    ];

    /**
     * Constructs validator.
     * @param null $elements
     */
    public function __construct($elements = null)
    {
        $this->_elements = $elements;
    }

    /**
     * Returns true if and only if $value meets the validation requirements
     *
     *
     *
     * @param  mixed $value
     * @return boolean
     * @throws Zend_Validate_Exception If validation of $value is impossible
     */
    public function isValid($value, $context = null)
    {
        if (is_array($this->_elements)) {
            $notEmpty = new \Zend_Validate_NotEmpty();
            foreach ($this->_elements as $name) {
                if (isset($context[$name]) && $notEmpty->isValid($context[$name])) {
                    return true;
                }
            }
        }
        $this->_error(self::ALL_EMPTY);
        return false;
    }

    /**
     * Adds a form element to group for validation.
     * @param $element \Zend_Form_Element
     */
    public function addElement($element)
    {
        if (! is_array($this->_elements)) {
            $this->_elements = [];
        }
        if (! in_array($element, $this->_elements)) {
            $this->_elements[] = $element;
        }
    }
}
