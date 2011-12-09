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
     * Factory for form elements.
     */
    private $__formElementFactory;

    /**
     * Logger for this class.
     * @var Zend_Log
     */
    private $__logger;

    /**
     * Controller helper for handling dates.
     * @var Controller_Helper_Dates
     */
    private $__dates;

    /**
     * Constructs form for Opus_Model_Abstract instance.
     * @param multi $clazz
     * @param array $includedFields
     */
    public function __construct($clazz, $includedFields = null) {
        parent::__construct();

        $this->__dates = Zend_Controller_Action_HelperBroker::getStaticHelper('Dates');

        $this->__formConfig = new Admin_Model_FormConfig();

        $this->__formElementFactory = new Admin_Model_FormElementFactory();

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

            $element = $this->__formElementFactory->getElementForField($model,
                    $field);

            // Disable element if necessary
            if ($this->isFieldDisabled($fieldName)) {
                $element->setAttrib('disabled', 'disabled');
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
                            $element->setValue($this->__dates->getDateString($value));
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
