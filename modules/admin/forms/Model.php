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
 * @package     Module_Admin
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Description of Admin_Form_Document
 *
 * @author jens
 */
class Admin_Form_Model extends Zend_Form {

    private $modelClazz;

    private $includedFields;

    /**
     * Constructs form for Opus_Model_Abstract instance.
     * @param <type> $model
     * @param <type> $clear
     */
    public function __construct($clazz, $includedFields = null) {
        parent::__construct();
        $this->modelClazz = $clazz;
        $this->includedFields = $includedFields;

        $this->_init();

        $this->setAction('update');
    }

    protected function _init() {
        $model = new $this->modelClazz;

        // get keys of fields that should be included
        if (empty($includedFields)) {
            $modelFields = array_keys($model->toArray());
        }
        else {
            $modelFields = $this->includedFields;
        }

        // iterate through fields and generate form elements
        foreach ($modelFields as $fieldName) {
            $field = $model->getField($fieldName);
            $element = $this->_getElementForField($field);
            $this->addElement($element);
        }
    }

    /**
     * Populates form from model values.
     */
    public function popluateFromModel() {

    }

    /**
     * Sets values in model instance.
     */
    public function populateModel() {
    }

    /**
     * Generates a Zend_Form_Element for model field.
     *
     * TODO add method to Opus_Field to *getField(Render)Type()*
     */
    protected function _getElementForField($field) {
        $element = null;

        if ($field->isCheckbox()) {
            $element = $this->_createCheckbox($field);
        }
        elseif ($field->isSelection()) {
        }
        elseif ($field->isTextarea()) {
        }
        else {
            $element = $this->_createTextfield($field);
        }

        $element->setLabel($field->getName());

        return $element;
    }

    protected function _createCheckbox($field) {
        $name = $field->getName();
        $checkbox = new Zend_Form_Element_Checkbox($name);
        return $checkbox;
    }

    protected function _createTextfield($field) {
        $name = $field->getName();
        $textfield = new Zend_Form_Element_Text($name);
        return $textfield;
    }

}
?>
