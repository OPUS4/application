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
    private $required;
    private $formElement;
    private $datatype;
    private $collectionRole;
    private $collectionId;
    private $multiplicity;
    private $value;
    private $default = array();
    private $validationObject; //Publish_Model_Validation
    private $validation = array();
    private $group;         //Publish_Model_Group
    private $subFormElements = array();         //array of Zend_Form_Element    

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

        $this->formElement = $formElement;
        $this->datatype = $datatype;
        $this->multiplicity = $multiplicity;

        if (isset($this->datatype))
            $this->initValidation();
    }

    public function initValidation() {
        $this->validationObject = new Publish_Model_Validation($this->datatype, $this->collectionRole, $this->listOptions);
        $this->validationObject->validate();
        $this->validation = $this->validationObject->validator;
    }

    public function initGroup() {
        if ($this->isGroup()) {
            if ($this->isSubField === false) {
                $this->group = new Publish_Model_DisplayGroup($this->elementName, $this->form, $this->multiplicity);
                if (isset($this->collectionRole)) {
                    $this->group->isBrowseField = true;
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
                    $this->group->implicitGroup = true;
                }
                else if ($this->isSubjectElement()) {
                    $implicitFields = $this->implicitFields('Subject');
                    $this->addSubFormElements($implicitFields);
                }
                else {
                    $this->addSubFormElement($this->transform());
                }

                $this->group->setAdditionalFields($this->additionalFields);
                $this->group->setSubFields($this->subFormElements);
                if (isset($this->collectionRole))
                    $this->group->makeBrowseGroup();
                else
                    $this->group->makeDisplayGroup();
            }
            $displayGroup = $this->form->addDisplayGroup($this->group->elements, $this->group->label);
            return $displayGroup;
        }
    }

    private function isGroup() {
        if ($this->isTitleElement())
            return true;
        else if ($this->isPersonElement())
            return true;
        else if ($this->isSeriesElement())
            return true;
        else if ($this->datatype == 'Collection')
            return true;
        else if ($this->multiplicity !== '1')
            return true;
        else
            return false;
    }

    private function implicitFields($workflow) {
        switch ($workflow) {
            case 'Person':
                //creates two subfields for first and last name
                $first = new Publish_Model_FormElement($this->form, $this->elementName . self::FIRST, $this->required, 'text', 'Text');
                $first->isSubField = true;
                $first->setDefaultValue($this->default, self::FIRST);
                $elementFirst = $first->transform();

                $last = new Publish_Model_FormElement($this->form, $this->elementName . self::LAST, $this->required, 'text', 'Text');
                $last->isSubField = true;
                $last->setDefaultValue($this->default, self::LAST);
                $elementLast = $last->transform();

                return array($elementFirst, $elementLast);
                break;

            case 'Series':
                //creates a additional field for a number
                $number = new Publish_Model_FormElement($this->form, $this->elementName . self::NR, $this->required, 'text', 'Text');
                $number->isSubField = true;
                $number->setDefaultValue($this->default, self::NR);
                $elementNumber = $number->transform();

                $select = new Publish_Model_FormElement($this->form, $this->elementName, $this->required, 'select', 'Collection');
                $select->isSubField = true;
                $select->collectionRole = $this->collectionRole;
                $select->collectionId = $this->collectionId;
                $select->validationObject->collectionRole = $this->collectionRole;
                $select->setDefaultValue($this->default);
                $element = $select->transform();

                return array($elementNumber, $element);
                break;

            case 'Subject':
                //creates two subfields for subject and language
                $subject = new Publish_Model_FormElement($this->form, $this->elementName, $this->required, 'text', 'Text');
                $subject->isSubField = true;
                $subject->setDefaultValue($this->default, self::VALUE);
                $elementSubject = $subject->transform();

                $lang = new Publish_Model_FormElement($this->form, $this->elementName . self::LANG, $this->required, 'select', 'Language');
                $lang->isSubField = true;
                $lang->setDefaultValue($this->default, self::LANG);
                $elementLang = $lang->transform();

                return array($elementSubject, $elementLang);
                break;

            case 'Title':
                //creates two subfields for title value and language (select)
                if ($this->isTextareaElement())
                    $value = new Publish_Model_FormElement($this->form, $this->elementName, $this->required, 'textarea', 'Text');
                else
                    $value = new Publish_Model_FormElement($this->form, $this->elementName, $this->required, 'text', 'Text');
                $value->isSubField = true;
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
        if (strstr($this->elementName, 'Title')
                || strstr($this->elementName, 'Abstract'
                        || $this->datatype === 'Title'))
            return true;
        else
            return false;
    }

    private function isSeriesElement() {
        if (strstr($this->elementName, 'Series'))
            return true;
        else
            return false;
    }

    private function isSubjectElement() {
        if (strstr($this->elementName, 'Swd')
                || strstr($this->elementName, 'Uncontrolled'))
            return true;
        else
            return false;
    }

    private function isPersonElement() {
        if (strstr($this->elementName, 'Person')
                || strstr($this->elementName, 'Email')
                || strstr($this->elementName, 'Birth')
                || strstr($this->elementName, 'AcademicTitle'))
            return true;

        else
            return false;
    }

    private function isSelectElement() {
        if ($this->formElement === 'select' || $this->formElement === 'Select')
            return true;
        else
            return false;
    }

    private function isTextareaElement() {
        if ($this->formElement === 'textarea' || $this->formElement === 'Textarea')
            return true;
        else
            return false;
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
            }
            else {
                if (is_null($this->listOptions) || empty($this->listOptions)) {
                    $options = $this->validationObject->selectOptions($this->datatype);
                }
                else
                    $options = $this->listOptions;

                if (is_null($options)) {
                    //no options found in database / session / cache
                    $this->log->debug("No options found for element " . $this->elementName);
                    $element = $this->form->createElement('text', $this->elementName);
                    $element->setDescription('hint_no_selection_' . $this->datatype);
                    $element->setAttrib('disabled', true);
                    $this->required = false;
                }
                else {
                    $this->log->debug("Options found for element " . $this->elementName . " => " . implode(',', $options));

                    $element = $this->showSelectField($options);
                }
            }

            $element->setRequired($this->required);

            if (isset($this->default[0]['value']) && !empty($this->default[0]['value'])) {
                $element->setValue($this->default[0]['value']);
                $this->log->debug("Value set to default for " . $this->elementName . " => " . $this->default[0]['value']);
            }

            if (isset($this->default[0]['edit']) && $this->default[0]['edit'] === 'no') {                
                $this->session->disabled[$this->elementName] = $element->getValue();
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

            return $element;
        }
    }

    private function showSelectField($options, $datatype=null, $elementName=null) {
        if (isset($elementName))
            $name = $elementName;
        else
            $name = $this->elementName;

        $element = $this->form->createElement('select', $name);

        if (isset($datatype))
            $switchVar = $datatype;
        else
            $switchVar = $this->datatype;

        switch ($switchVar) {
            case 'Collection':
                $element->setMultiOptions(array_merge(array('___EMPTY' => 'choose_valid_collection'), $options));
                break;
            case 'Licence' :
                $element->setMultiOptions(array_merge(array('' => 'choose_valid_licence'), $options));
                break;
            case 'Language' :
                if ($this->elementName === 'Language')
                    $element->setMultiOptions(array_merge(array('' => 'choose_valid_language'), $options));
                else
                    $element->setMultiOptions(array_merge(array('' => 'inherit_document_language'), $options));
                break;
            case 'Project' : //deprecated
                $element->setMultiOptions(array_merge(array('' => 'choose_valid_project'), $options));
                break;
            case 'Institute' : //deprecated
                $element->setMultiOptions(array_merge(array('' => 'choose_valid_institute'), $options));
                break;
            case 'ThesisGrantor' :
                $element->setMultiOptions(array_merge(array('' => 'choose_valid_thesisgrantor'), $options));
                break;
            case 'ThesisPublisher':
                $element->setMultiOptions(array_merge(array('' => 'choose_valid_thesispublisher'), $options));
                break;
            default:
                $element->setMultiOptions(array_merge(array('' => 'choose_valid_option'), $options));
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
        $this->formElement = $formElement;
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

?>
