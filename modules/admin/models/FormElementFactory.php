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
 * @package     Module_Admin
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Factory for creating Zend form elements.
 */
class Admin_Model_FormElementFactory extends Admin_Model_AbstractModel {

    /**
     * Helper for generating translation keys.
     */
    private $__translationKeys;

    /**
     * Constructs a Admin_Model_FormElementFactory.
     */
    public function __construct() {
        $this->__translationKeys =
                Zend_Controller_Action_HelperBroker::getStaticHelper(
                        'Translation');
    }

    /**
     * Returns a Zend_Form_Element for a model field.
     */
    public function getElementForField($model, $field) {
        $element = null;

        if ($field->isCheckbox()) {
            $element = $this->_createCheckbox($field);
        }
        elseif ($field->isSelection()) {
            if ($model instanceOf Opus_Model_Dependent_Link_Abstract) {
                $modelName = $model->getModelClass();
            }
            else {
                $modelName = get_class($model);
            }
            $element = $this->_createSelect($modelName, $field);
        }
        elseif ($field->isTextarea()) {
            $element = $this->_createTextarea($field);
        }
        else {
            $element = $this->_createTextfield($field);
        }

        $fieldName = $field->getName();

        // TODO consider always prepending the class
        switch ($fieldName) {
            case "Type":
                $element->setLabel(get_class($model) . "_" . $fieldName);
                break;
            default:
                $element->setLabel($field->getName());
                break;
        }

        return $element;
    }

    /**
     * Creates checkbox input element.
     * @param Opus_Model_Field $field
     * @return Zend_Form_Element_Checkbox
     */
    protected function _createCheckbox($field) {
        $name = $field->getName();
        $checkbox = new Zend_Form_Element_Checkbox($name);
        return $checkbox;
    }

    /**
     * Create text input element.
     * @param Opus_Model_Field $field
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

    /**
     * Creates textarea input element.
     * @param Opus_Model_Field $field
     * @return Zend_Form_Element_Textarea
     */
    protected function _createTextarea($field) {
        $name = $field->getName();
        $textarea = new Zend_Form_Element_Textarea($name);
        $textarea->setAttrib('cols', 100);
        $textarea->setAttrib('rows', 6);

        // special code for striping new lines from TitleMain, ... fields
        if ($field->getOwningModelClass() === 'Opus_Title'
                && strpos($name, 'Value') === 0) {
            $filter = new Form_Filter_ReplaceNewlines();
            $textarea->addFilter($filter);
        }
        return $textarea;
    }

    /**
     * Creates select input element.
     * @param Opus_Model_Field $field
     * @return Zend_Form_Element_Select
     */
    protected function _createSelect($modelName, $field) {
        $name = $field->getName();

        $select = new Zend_Form_Element_Select($name);

        // add possible values
        if ($name === 'Type' && $modelName === 'Opus_Document') {
            $docTypeHelper =
                    Zend_Controller_Action_HelperBroker::getStaticHelper(
                            'DocumentTypes');
            $options = $docTypeHelper->getDocumentTypes();
        }
        else {
            switch ($name) {
                case 'ThesisPublisher':
                    $options['nothing'] = 'admin_document_publisher_none';
                    $options = array_merge($options, $field->getDefault());
                    break;
                case 'ThesisGrantor':
                    $options['nothing'] = 'admin_document_grantor_none';
                    $options = array_merge($options, $field->getDefault());
                    break;
                default:
                    $options = $field->getDefault();
                break;
            }
        }

        foreach ($options as $index => $option) {
            switch ($name) {
                case 'Licence':
                case 'ThesisPublisher':
                case 'ThesisGrantor':
                case 'Language':
                    if ($option instanceof Opus_Model_Abstract) {
                        $select->addMultiOption($option->getId(), $option);
                    }
                    else {
                        $select->addMultiOption($index, $option);
                    }
                    break;
                default:
                    if ($name === 'Type' && $modelName === 'Opus_Document') {
                        // Document type translations do not have a prefix
                        $select->addMultiOption($option, $option);
                    }
                    else {
                        // Select value translations have a prefix
                        $select->addMultiOption($option,
                                $this->__translationKeys->getKeyForValue(
                                        $modelName, $name, $option));
                    }
                    break;
            }
        }

        return $select;
    }

}
