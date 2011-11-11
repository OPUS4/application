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
 * Dynamic form for editing model classes.
 */
class Admin_Form_Model extends Zend_Form_SubForm {

    /**
     * Name of model class for form.
     * @var string
     */
    private $modelClazz;

    /**
     * Instance of model for form.
     * @var Opus_Model_Abstract
     */
    private $model;

    /**
     * Visible fields in form.
     * @var array
     */
    private $includedFields;

    /**
     * Configuration for form for model class.
     */
    private $__formConfig;

    /**
     * Logger for this class.
     * @var Zend_Log
     */
    private $__logger;

    /**
     * Constructs form for Opus_Model_Abstract instance.
     * @param multi $clazz
     * @param array $includedFields
     */
    public function __construct($clazz, $includedFields = null) {
        parent::__construct();

        $this->__formConfig = new Admin_Model_FormConfig();

        $this->includedFields = $includedFields;

        if ($clazz instanceof Opus_Model_Field) {
            $this->modelClazz = null;
            $this->model = $clazz;
            $this->_init($clazz);
        }
        elseif ($clazz instanceof Opus_Model_Abstract) {
            $this->modelClazz = null;
            $this->model = $clazz;
            $this->_init($clazz);
        }
        else {
            $this->modelClazz = $clazz;
            $this->_init();
        }
    }

    /**
     * Constructs form.
     * @param type $field
     */
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

        $modelFields = $this->getVisibleFields($model);

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

            if ($model instanceof Opus_Document && $field->getName() === 'Type') {
                $element = $this->_getElementForField($model, $field, 'DocType');
            }
            else {
                $element = $this->_getElementForField($model, $field);
            }

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
     * Populates form element from model instance.
     */
    public function populateFromModel($model) {
        // TODO check if model matches form

        $modelFields = $model->describe();

        if ($model instanceof Opus_Model_Dependent_Link_DocumentLicence) {
            $element = $this->getElement('Licence');
            $element->setValue($model->getLinkedModelId());
        }
        else {
            // iterate through fields and generate form elements
            foreach ($modelFields as $fieldName) {
                $field = $model->getField($fieldName);
                $element = $this->getElement($field->getName());
                if (!empty($element)) {
                    if ($element instanceof Zend_Form_Element_Select) {
                        if ($field->getValueModelClass() == 'Opus_DnbInstitute') {
                            $value = $field->getValue();
                            switch ($field->getName()) {
                                case 'ThesisGrantor':
                                    $value = $model->getThesisGrantor();
                                    break;
                                case 'ThesisPublisher':
                                    $value = $model->getThesisPublisher();
                                    break;
                            }
                            if (isset($value[0])) {
                                //throw new Exception($value[0]);
                                $element->setValue($value[0]->getId());
                            }
                        }
                        else {
                            $element->setValue($field->getValue());
                        }
                    }
                    elseif ($field->getValueModelClass() === 'Opus_Date') {
                        $value = $field->getValue();

                        if (!empty($value)) {
                            // TODO use common function for formatting
                            $dateFormat = Admin_Model_DocumentHelper::getDateFormat();
                            $this->getLogger()->debug('Reading Date ' . $value . ' for field ' . $field->getName());
                            $date = $value->getZendDate();
                            $this->getLogger()->debug('Reading Date ' . $date . ' for field ' . $field->getName());
                            $element->setValue($date->get($dateFormat));
                            $this->getLogger()->debug('Formatting ' . $field->getName() . ' using ' . $dateFormat);
                            $this->getLogger()->debug('Reading Date ' . $date->get($dateFormat) . ' for field ' . $field->getName());
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
    }

    /**
     * Generates a Zend_Form_Element for model field.
     *
     * TODO add method to Opus_Field to *getField(Render)Type()*
     */
    protected function _getElementForField($model, $field, $flag = null) {
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
            $element = $this->_createSelect($modelName, $field, $flag);
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

        if ($this->isFieldDisabled($fieldName)) {
            $element->setAttrib('disabled', 'disabled');
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
        return $textarea;
    }

    /**
     * Creates select input element.
     * @param Opus_Model_Field $field
     * @param string $flag
     * @return Zend_Form_Element_Select
     */
    protected function _createSelect($modelName, $field, $flag) {
        $name = $field->getName();
        $select = new Zend_Form_Element_Select($name);

        // add message (null option)
        // TODO only if null is allowed
        // $message = Zend_Registry::get('Zend_Translate')->translate('choose_option');
        // $select->addMultiOption('', $message);

        // add possible values
        if ($flag === 'DocType') {
            $docTypeHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('DocumentTypes');
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
                    // TODO needed for any field?
                    if ($flag === 'DocType') {
                        $select->addMultiOption($option, $option);
                    }
                    else {
                        $select->addMultiOption($option, $modelName . '_' . $name . '_Value_' . ucfirst($option));
                    }
                    break;
            }
        }

        return $select;
    }

    /**
     * Set logger for this class.
     * @param Zend_Log $logger
     */
    public function setLogger($logger) {
        $this->__logger = $logger;
    }

    /**
     * Returns logger for this class.
     * @return Zend_Log
     */
    public function getLogger() {
        if (empty($this->__logger)) {
            $this->__logger = Zend_Registry::get('Zend_Log');
        }

        return $this->__logger;
    }

    /**
     * Returns the list of fields of model that should be part of form.
     * @param Opus_Model_Abstract $model
     * @return array Names of fields
     */
    public function getVisibleFields($model) {
        $fields = $this->__formConfig->getFields($this->getModelClass());

        if (empty($fields)) {
            $modelFields = $model->describe();

            $fields = array();

            foreach ($modelFields as $fieldName) {
                if (!$this->isFieldHidden($fieldName)) {
                    $fields[] = $fieldName;
                }
            }

            return $fields;
        }
        else {
            return $fields;
        }
    }

    /**
     * Return name of model class for form.
     * @return string Name of model class
     */
    public function getModelClass() {
        if (!empty($this->modelClazz)) {
            return $this->modelClazz;
        }
        else if (!empty($this->model)) {
            if ($this->model instanceof Opus_Model_Field) {
                $modelClass = $this->model->getLinkModelClass();
                if (empty($modelClass)) {
                    $modelClass = $this->model->getValueModelClass();
                }
                return $modelClass;
            }
            else {
                return get_class($this->model);
            }
        }
        else {
            return null;
        }
    }

    /**
     * Checks if field is disabled for model class of form.
     * @param string $fieldName Name of field
     * @return boolean True - if field disabled
     */
    public function isFieldDisabled($fieldName) {
        $disabledFields = $this->__formConfig->getDisabledFields(
                $this->getModelClass());

        return in_array($fieldName, $disabledFields);
    }

    /**
     * Checks if field is hidden for model class of form.
     * @param string $fieldName Name of field
     * @return boolean True - if field is hidden
     */
    public function isFieldHidden($fieldName) {
        $hiddenFields = $this->__formConfig->getHiddenFields(
                $this->getModelClass());

        return in_array($fieldName, $hiddenFields);
    }

}
?>
