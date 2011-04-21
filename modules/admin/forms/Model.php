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
class Admin_Form_Model extends Zend_Form_SubForm {

    private $modelClazz;

    private $includedFields;

    /**
     * Constructs form for Opus_Model_Abstract instance.
     * @param <type> $model
     * @param <type> $clear
     */
    public function __construct($clazz, $includedFields = null) {
        parent::__construct();

        $this->includedFields = $includedFields;

        if ($clazz instanceof Opus_Model_Field) {
            $this->modelClazz = null;
            $this->_init($clazz);
        }
        elseif ($clazz instanceof Opus_Model_Abstract) {
            $this->modelClazz = null;
            $this->_init($clazz);
        }
        else {
            $this->modelClazz = $clazz;
            $this->_init();
        }
    }

    protected function _init($field = null) {
        if (!empty($field) && $field instanceof Opus_Model_Field) {
            $linkModelClass = $field->getLinkModelClass();
            $modelClass = $field->getValueModelClass();

            if (!empty($linkModelClass)) {
                $model = new $linkModelClass;
                $target = new $modelClass;
                $model->setModel($target);
            }
            else {
                $model = new $modelClass;
            }
        }
        elseif (!empty($field) && $field instanceof Opus_Model_Abstract) {
            $model = $field;
        }
        else {
            $model = new $this->modelClazz;
        }

        $modelFields = $model->describe();

        $filteredFields = array();

        // get keys of fields that should be included
        if (!empty($this->includedFields)) {
            foreach ($modelFields as $fieldName) {
                if (in_array($fieldName, $this->includedFields)) {
                    $filteredFields[] = $fieldName;
                }
            }

            $modelFields = $filteredFields;
        }

        // iterate through fields and generate form elements
        foreach ($modelFields as $fieldName) {
            $field = $model->getField($fieldName);
            $element = $this->_getElementForField($field);
            // $element->setName($model->getName());
            if ($field->isMandatory()) {
                $element->setRequired(true);
            }
            $validator = $field->getValidator();
            if (!empty($validator)) {
                $element->addValidator($validator);
            }
            $this->addElement($element);
        }
    }

    /**
     * Populates form from model values.
     */
    public function populateFromModel($model) {
        // TODO check if model matches form

        $modelFields = $model->describe();

        // iterate through fields and generate form elements
        foreach ($modelFields as $fieldName) {
            $field = $model->getField($fieldName);
            $element = $this->getElement($field->getName());
            if (!empty($element)) {
                if ($element instanceof Zend_Form_Element_Select) {
                    $element->setValue($field->getValue());
                }
                elseif ($field->getValueModelClass() === 'Opus_Date') {
                    $value = $field->getValue();

                    if (!empty($value)) {
                        // TODO use common function for formatting
                        $date = $value->getZendDate();
                        $element->setValue($date->get('YYYY/MM/dd'));
                    }
                    else {
                        $element->setValue(null);
                    }
                }
                else {
                    $element->setValue($field->getValue());
                }
            }
        }
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
            $element = $this->_createSelect($field);
        }
        elseif ($field->isTextarea()) {
            $element = $this->_createTextarea($field);
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

    /**
     *
     * @param <type> $field
     * @return Zend_Form_Element_Text
     *
     * TODO handle validation for Date fields
     */
    protected function _createTextfield($field) {
        $name = $field->getName();
        $textfield = new Zend_Form_Element_Text($name);
        $textfield->setAttrib('size', 60);
        return $textfield;
    }

    protected function _createTextarea($field) {
        $name = $field->getName();
        $textarea = new Zend_Form_Element_Textarea($name);
        $textarea->setAttrib('cols', 100);
        $textarea->setAttrib('rows', 6);
        return $textarea;
    }

    protected function _createSelect($field) {
        $name = $field->getName();
        $select = new Zend_Form_Element_Select($name);

        // add message (null option)
        // TODO only if null is allowed
        // $message = Zend_Registry::get('Zend_Translate')->translate('choose_option');
        // $select->addMultiOption('', $message);

        // add possible values
        $options = $field->getDefault();

        foreach ($options as $option) {
            $select->addMultiOption($option, $option);
        }

        return $select;
    }

    protected function _getFieldNames() {

    }

}
?>
