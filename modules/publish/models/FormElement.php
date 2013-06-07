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
 * @package     Module_Publish
 * @author      Susanne Gottwald <gottwald@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
class Publish_Model_FormElement {

    public $session;
    public $session2;
    public $form;
    public $log;
    public $additionalFields = array();
    public $postValues = array();
    public $isSubField = false;
    public $listOptions = array();
    //private member variables
    private $elementName;
    private $label;
    private $required;                      //Bool
    private $formElement;
    private $datatype;
    private $collectionRole;
    private $collectionId;
    private $multiplicity;
    private $value;
    private $default = array();
    private $validationObject;              //Publish_Model_Validation
    private $validation = array();
    private $group;                         //Publish_Model_Group
    private $subFormElements = array();     //array of Zend_Form_Element    

    //Constants
    const FIRST = "FirstName";
    const LAST = "LastName";
    const VALUE = "Value";
    const LANG = "Language";
    const NR = "Number";

    public function __construct($form, $name = null, $required = null, $formElement = null, $datatype = null, $multiplicity = null) {
        $this->session = new Zend_Session_Namespace('Publish');
        $this->session2 = new Zend_Session_Namespace();
        $this->log = Zend_Registry::get('Zend_Log');
        $this->form = $form;

        $this->elementName = $name;
        $this->label = $name;

        $this->required = $required;

        $this->formElement = strtolower($formElement);        
        $this->datatype = $datatype;
        $this->multiplicity = $multiplicity;       
        if (isset($this->datatype)) {
            $this->initValidation();
        }
    }

    public function initValidation() {        
        $this->validationObject = new Publish_Model_Validation($this->datatype, $this->session2, $this->collectionRole, $this->listOptions, $this->form->view);
        $this->validationObject->validate();
        $this->validation = $this->validationObject->validator;
    }

    public function initGroup() {
        if ($this->isGroup()) {
            if ($this->isSubField === false) {                                
                $this->group = new Publish_Model_DisplayGroup($this->elementName, $this->form, $this->multiplicity, $this->log, $this->session);
                if (isset($this->collectionRole)) {
                    $this->group->datatype = $this->datatype;                      
                    $this->group->collectionIds[] = $this->collectionId;
                }

                if ($this->isPersonElement()) {
                    $implicitFields = $this->implicitFields('Person');
                    $this->addSubFormElements($implicitFields);
                }
                else if ($this->isTitleElement()) {
                    $implicitFields = $this->implicitFields('Title');
                    $this->addSubFormElements($implicitFields);
                }
                else if ($this->isSeriesElement()) {
                    $implicitFields = $this->implicitFields('Series');
                    $this->addSubFormElements($implicitFields);                    
                }
                else if ($this->isSubjectUElement()) {
                    $implicitFields = $this->implicitFields('Subject');
                    $this->addSubFormElements($implicitFields);
                }
                else {                    
                    $this->addSubFormElement($this->transform());
                }

                $this->group->setAdditionalFields($this->additionalFields);
                $this->group->setSubFields($this->subFormElements);
                if (isset($this->collectionRole)) {
                    $this->group->makeBrowseGroup();
                }
                else {
                    $this->group->makeDisplayGroup();
                }
            }
            $displayGroup = $this->form->addDisplayGroup($this->group->elements, $this->group->label);
            $displayGroup->setDisableTranslator(true);
            return $displayGroup;
        }
    }

    private function isGroup() {
        return $this->isTitleElement() ||
                $this->isPersonElement() ||
                $this->isSeriesElement() ||
                $this->isSubjectUElement() ||
                $this->datatype == 'Collection' ||
                $this->multiplicity !== '1';
    }

    private function implicitFields($workflow) {
        switch ($workflow) {
            case 'Person':
                //creates two subfields for first and last name
                $first = new Publish_Model_FormElement($this->form, $this->elementName . self::FIRST, false, 'text', 'Person');
                $first->isSubField = true;
                $first->setDefaultValue($this->default, self::FIRST);
                $elementFirst = $first->transform();

                $last = new Publish_Model_FormElement($this->form, $this->elementName . self::LAST, $this->required, 'text', 'Person');
                $last->isSubField = false;
                $last->setDefaultValue($this->default, self::LAST);
                $elementLast = $last->transform();

                return array($elementFirst, $elementLast);
                break;

            case 'Series':
                //creates a additional field for a number
                $number = new Publish_Model_FormElement($this->form, $this->elementName . self::NR, $this->required, 'text', 'SeriesNumber');
                $number->isSubField = false;
                $number->setDefaultValue($this->default, self::NR);
                $elementNumber = $number->transform();

                $select = new Publish_Model_FormElement($this->form, $this->elementName, $this->required, 'select', 'Series');
                $select->isSubField = true;                
                $select->setDefaultValue($this->default);
                $element = $select->transform();

                return array($elementNumber, $element);
                break;

            case 'Subject':
            case 'Title' :
                //creates two subfields: a value text field (subject, title) and language selection                
                if ($this->isTitleElement())
                    if ($this->isTextareaElement())
                        $value = new Publish_Model_FormElement($this->form, $this->elementName, $this->required, 'textarea', 'Title');
                    else
                        $value = new Publish_Model_FormElement($this->form, $this->elementName, $this->required, 'text', 'Title');
                else
                    $value = new Publish_Model_FormElement($this->form, $this->elementName, $this->required, 'text', 'Subject');
                $value->isSubField = false;
                $value->setDefaultValue($this->default, self::VALUE);
                $elementValue = $value->transform();

                $lang = new Publish_Model_FormElement($this->form, $this->elementName . self::LANG, $this->required, 'select', 'Language');
                $lang->isSubField = true;
                $lang->setDefaultValue($this->default, self::LANG);
                $elementLang = $lang->transform();

                return array($elementValue, $elementLang);
                break;
        }
    }

    private function isTitleElement() {
        return $this->datatype === 'Title';
    }

    private function isSeriesElement() {
        return $this->datatype === 'Series';
    }

    /**
     * Subject field must have datatype "Subject" 
     * @return boolean 
     */ 
    private function isSubjectElement() {        
        return $this->datatype === 'Subject';
    }

    /**
     * Subject field must have datatype "Subject" and can be a SWD or uncontrolled field (with or without languague)
     * @return boolean 
     */ 
    private function isSubjectUElement() {        
        return $this->datatype === 'Subject' && !(strstr($this->elementName, 'Swd'));
    }
    
    /**
     * A Person field must have datatype "Person" or is one of the possible subfields of Person.
     * @return type 
     */
    private function isPersonElement() {
        return $this->datatype === 'Person'
                || strstr($this->elementName, 'Email')
                || strstr($this->elementName, 'Birth')
                || strstr($this->elementName, 'AcademicTitle');
    }

    private function isSelectElement() {        
        return $this->formElement === 'select';
    }

    private function isTextareaElement() {
        return $this->formElement === 'textarea';
    }

    public function setPostValues($postValues) {
        $this->postValues = $postValues;
    }

    public function setAdditionalFields($additionalFields) {
        $this->additionalFields = $additionalFields;
    }

    public function transform() {
        if (isset($this->form)) {

            if (false === $this->isSelectElement()) {
                $element = $this->form->createElement($this->formElement, $this->elementName);
                $element->setDisableTranslator(true);                
            }
            else {
                $options = null;
                if (is_null($this->listOptions) || empty($this->listOptions)) {
                    $options = $this->validationObject->selectOptions($this->datatype);                    
                }
                else {
                    $options = $this->listOptions;
                }

                if (is_null($options)) {
                    //no options found in database / session / cache                    
                    $element = $this->form->createElement('text', $this->elementName);
                    $element->setDisableTranslator(true);
                    $element->setDescription('hint_no_selection_' . $this->elementName);
                    $element->setAttrib('disabled', true);                    
                    $this->required = false;
                }
                else {
                    $element = $this->showSelectField($options);
                }
            }

            $element->setRequired($this->required);
            if ($this->required) {
                $element->addValidator('NotEmpty', true, array('messages' => $this->form->view->translate('publish_validation_error_notempty_isempty')));
            }

            if (isset($this->default[0]['value']) && !empty($this->default[0]['value'])) {
                $element->setValue($this->default[0]['value']);
                $this->log->debug("Value set to default for " . $this->elementName . " => " . $this->default[0]['value']);
            }

            if (isset($this->default[0]['edit']) && $this->default[0]['edit'] === 'no') {                
                $element->setAttrib('disabled', true);
                $element->setRequired(false);
            }
            $element->setLabel($this->label);
            if (!is_null($this->validation)) {
                if (is_array($this->validation))
                    $element->addValidators($this->validation);
                else
                    $element->addValidator($this->validation);
            }
            
            $element->setAttrib('datatype', $this->realDatatype($element)); 
            
            if ($this->isSubField == true) {
                $element->setAttrib('subfield', true);
            }
            else {
                $element->setAttrib('subfield', false);
            }
            
            if ($this->datatype == 'CollectionLeaf') {
                $element->setAttrib('collectionLeaf', true);
            }
            
            return $element;
        }
    }
    
    private function realDatatype() {
        if ($this->isPersonElement())
            return 'Person';
        
        if ($this->isTitleElement())
            return 'Title';
        
        if ($this->isSeriesElement())
            return 'Series';
        
        if ($this->isSubjectElement())
            return 'Subject';

        return $this->datatype;
    }

    private function showSelectField($options, $datatype = null, $elementName = null) {
        if (isset($elementName)) {
            $name = $elementName;
        }
        else {
            $name = $this->elementName;
        }
        $element = $this->form->createElement('select', $name);
        $element->setDisableTranslator(true);

        if (isset($datatype)) {
            $switchVar = $datatype;
        }
        else {
            $switchVar = $this->datatype;
        }

        // reorganize $options since Zend multiOptions does not accept integers as keys
        $reorgOptions = array();
        foreach ($options as $key => $value) {
            $reorgOptions[] = array(
                'key' => is_string($key) ? $key : strval($key),
                'value' => $value);
        }        

        switch ($switchVar) {
            case 'Collection': 
            case 'CollectionLeaf' :
                $element->setMultiOptions(array_merge(array('' => $this->form->view->translate('choose_valid_'.$this->collectionRole)), $reorgOptions));
                break;

            case 'Licence' :
                $element->setMultiOptions(array_merge(array('' => $this->form->view->translate('choose_valid_licence')), $reorgOptions));
                break;

            case 'Language' :
                if ($this->elementName === 'Language') {
                    $element->setMultiOptions(array_merge(array('' => $this->form->view->translate('choose_valid_language')), $reorgOptions));
                }
                else {
                    $element->setMultiOptions(array_merge(array('' => $this->form->view->translate('inherit_document_language')), $reorgOptions));
                }
                break;

            case 'ThesisGrantor' :
                $element->setMultiOptions(array_merge(array('' => $this->form->view->translate('choose_valid_thesisgrantor')), $reorgOptions));
                break;

            case 'ThesisPublisher':
                $element->setMultiOptions(array_merge(array('' => $this->form->view->translate('choose_valid_thesispublisher')), $reorgOptions));
                break;

            case 'Series':
                $element->setMultiOptions(array_merge(array('' => $this->form->view->translate('choose_valid_series')), $reorgOptions));
                break;

            default:
                $element->setMultiOptions(array_merge(array('' => $this->form->view->translate('choose_valid_option')), $reorgOptions));
                break;
        }
        
        return $element;
    }

    public function setForm(Publish_Form_PublishingSecond $form) {
        $this->form = $form;
    }

    public function getElementName() {
        return $this->elementName;
    }

    public function setElementName($elementName) {
        $this->elementName = $elementName;
        $this->label = $elementName;
    }

    public function getValue() {
        return $this->elementName;
    }

    public function setValue($value) {
        $this->value = $value;
    }

    public function getDefault() {
        return $this->default;
    }

    public function setCollectionRole($root) {
        $this->collectionRole = $root;
    }

    public function getCollectionRole() {
        return $this->collectionRole;
    }

    public function setCurrentCollectionId($setRoot=false) {
        if (!$setRoot) {
            $collectionRole = Opus_CollectionRole::fetchByOaiName($this->collectionRole);
            if (!is_null($collectionRole)) {
                $rootCollection = $collectionRole->getRootCollection();
                if (!is_null($rootCollection)) {
                    $collectionId = $rootCollection->getId();
                    $this->log->debug("CollectionRoot: " . $this->collectionRole . " * CollectionId: " . $collectionId);
                    $this->collectionId = $collectionId;
                }
            }
        }
    }

    public function getCurrentCollectionId() {
        return $this->collectionId;
    }

    public function setDefaultValue($defaultValue, $forValue = null) {
        if (isset($forValue)) {
            foreach ($defaultValue AS $default) {
                if ($default['for'] == $forValue) {
                    $this->default[] = $default;
                }
            }
            return;
        }

        if (!isset($this->value) || is_null($this->value)) {
            if (isset($defaultValue['value'])) {

                //Date Field has be set to current date
                if ($defaultValue['value'] === 'today') {
                    if ($this->session2->language === 'de')
                        $defaultValue['value'] = date('d.m.Y');
                    else
                        $defaultValue['value'] = date('Y/m/d');
                }
                $this->default[] = $defaultValue;
            }
        }
    }

    public function getLabel() {
        return $this->label;
    }

    public function setLabel($label) {
        $this->label = $label;
    }

    public function setListOptions($options) {
        $this->listOptions = $options;
        $this->initValidation();
    }

    public function getMultiplicity() {
        return $this->multiplicity;
    }

    public function setMultiplicity($multiplicity) {
        $this->multiplicity = $multiplicity;
    }

    public function getFormElement() {
        return $this->formElement;
    }

    public function setFormElement($formElement) {
        $this->formElement = strtolower($formElement);
    }

    public function getDatatype() {
        return $this->datatype;
    }

    public function setDatatype($datatype) {
        $this->datatype = $datatype;
        if ($this->datatype !== 'List')
            $this->initValidation();
    }

    public function getRequired() {
        return $this->required;
    }

    public function setRequired($required) {
        $this->required = $required;
    }

    public function getValidator() {
        return $this->validation;
    }

    public function setValidator($validator) {
        $this->validation = $validator;
    }

    public function getGroup() {
        return $this->group;
    }

    public function setGroup($group) {
        $this->group = $group;
    }

    public function getSubFormElements() {
        return $this->subFormElements;
    }

    public function addSubFormElements($subFormElements) {
        $this->subFormElements = array_merge($subFormElements, $this->subFormElements);
    }

    public function addSubFormElement($subField) {
        $this->subFormElements[] = $subField;
    }

}
