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
 * @category    TODO
 * @package     TODO
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */


class Form_Validate_MultiSubForm_RepeatedValues implements Form_Validate_IMultiSubForm {

    private $_elementName;

    private $_message;

    public function __construct($elementName, $message) {
        if (is_null($elementName) || strlen(trim($elementName)) == 0) {
            throw new Application_Exception(__METHOD__ . ' #1 argument must not be null or empty.');
        }

        if (is_null($message) || strlen(trim($message)) == 0) {
            throw new Application_Exception(__METHOD__ . ' #2 argument must not be null or empty.');
        }

        $this->_elementName = $elementName;
        $this->_message = $message;
    }

    public function isValid($data, $context = null) {
        return true;
    }

    public function prepareValidation($form, $data, $context = null) {
        $position = 0;

        $values = $this->getValues($this->_elementName, $data);

        foreach($form->getSubForms() as $name => $subform) {
            if (array_key_exists($name, $data)) {
                $element = $subform->getElement($this->_elementName);
                if (!is_null($element)) {
                    $element->addValidator(new Form_Validate_DuplicateValue($values, $position++, $this->_message));
                }
            }
        }
    }

    public function getValues($name, $context) {
        $values = array();

        foreach ($context as $index => $subform) {
            if (isset($subform[$name])) {
                $values[] = $subform[$name];
            }
        }

        return $values;
    }

    public function getElementName() {
        return $this->_elementName;
    }

    public function getMessage() {
        return $this->_message;
    }

}