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
    public $form;
    public $log;
    public $additionalFields = array();
    public $postValues = array();
    //private member variables
    private $elementName;
    private $label;
    private $required;
    private $formElement;
    private $datatype;
    private $multiplicity;
    private $value;
    private $default = array(
        'value' => '',
        'edit' => 'yes',
        'public' => 'yes'
    );
    public $isSubField = false;
    private $validationObject; //Publish_Model_Validation
    private $validation = array();
    private $group;         //Publish_Model_Group
    private $subFormElements = array();         //array of Zend_Form_Element

    //Constants
    const FIRST = "FirstName";
    const LAST = "LastName";
    const VALUE = "Value";
    const LANG = "Language";

    public function __construct($form, $name = null, $required = null, $formElement = null, $datatype = null, $multiplicity = null) {
        $this->session = new Zend_Session_Namespace();
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

    private function initValidation() {
        $this->validationObject = new Publish_Model_Validation($this->datatype);
        $this->validationObject->validate();
        $this->validation = $this->validationObject->validator;
    }

    public function initGroup() {
        if ($this->isGroup()) {
            if ($this->isSubField === false) {
                $this->group = new Publish_Model_DisplayGroup($this->elementName, $this->form, $this->multiplicity);

                if ($this->isPersonElement()) {
                    $this->log->debug("FormElement -> initGroup(): person element");
                    $implicitFields = $this->implicitFields('Person');
                    $this->addSubFormElements($implicitFields);
                }
                else if ($this->isTitleElement()) {
                    $this->log->debug("FormElement -> initGroup(): title element");
                    $implicitFields = $this->implicitFields('Title');
                    $this->addSubFormElements($implicitFields);
                }
                else {
                    $this->log->debug("FormElement -> initGroup(): other element");
                    $this->addSubFormElement($this->transform());
                }

                $this->group->setAdditionalFields($this->additionalFields);
                $this->group->setSubFields($this->subFormElements);
                $this->group->makeDisplayGroup();
            }
            $displayGroup = $this->form->addDisplayGroup($this->group->elements, $this->group->label);
            return $displayGroup;
        }
    }

    private function isGroup() {
        $groupCount = 'num' . $this->elementName;
        $this->session->$groupCount = 0;
        if ($this->isTitleElement())
            return true;
        else if ($this->isPersonElement())
            return true;
        else if ($this->multiplicity !== '1')
            return true;

        else
            return false;
    }

    private function implicitFields($workflow) {
        switch ($workflow) {
            case 'Person':
                $first = new Publish_Model_FormElement($this->form, $this->elementName . self::FIRST, $this->required, 'text', 'Text');
                $first->isSubField = true;
                $elementFirst = $first->transform();
                $last = new Publish_Model_FormElement($this->form, $this->elementName . self::LAST, $this->required, 'text', 'Text');
                $last->isSubField = true;
                $elementLast = $last->transform();
                return array($elementFirst, $elementLast);
                break;

            case 'Title':
                if ($this->isTextareaElement())
                    $value = new Publish_Model_FormElement($this->form, $this->elementName, $this->required, 'textarea', 'Text');
                else
                    $value = new Publish_Model_FormElement($this->form, $this->elementName, $this->required, 'text', 'Text');
                $value->isSubField = true;
                $elementValue = $value->transform();
                $lang = new Publish_Model_FormElement($this->form, $this->elementName . self::LANG, $this->required, 'select', 'Language');
                $lang->isSubField = true;
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
                $options = $this->validationObject->selectOptions($this->datatype);
                if (is_null($options)) {
                    //no options found in database / session / cache
                    $this->log->debug("No options found for element " . $this->elementName);
                    $element = $this->form->createElement('text', $this->elementName);
                    $element->setDescription('hint_no_selection_' . $this->datatype);
                    $element->setAttrib('disabled', true);
                    $this->required = false;
                }
                else {
                    $this->log->debug("Options found for element " . $this->elementName);

                    $element = $this->showSelectField($options);
                }
            }

            $element->setRequired($this->required);

            if (isset($this->default['value']) && !empty($this->default['value'])) {
                $element->setValue($this->default['value']);
                $this->log->debug("Value set to default for " . $this->elementName . " => " . $this->default['value']);
            }

            if (isset($this->default['edit']) && $this->default['edit'] === 'no') {
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
            case 'Licence' :
                $element->setMultiOptions(array_merge(array('' => 'choose_valid_licence'), $options));
                break;
            case 'Language' :
                if ($this->elementName === 'Language')
                    $element->setMultiOptions(array_merge(array('' => 'choose_valid_language'), $options));
                else
                    $element->setMultiOptions(array_merge(array('' => 'inherit_document_language'), $options));
                break;
            case 'Project' :
                $element->setMultiOptions(array_merge(array('' => 'choose_valid_project'), $options));
                break;
            case 'Institute' :
                $element->setMultiOptions(array_merge(array('' => 'choose_valid_institute'), $options));
                break;
            case 'ThesisGrantor' :
                $element->setMultiOptions(array_merge(array('' => 'choose_valid_thesisgrantor'), $options));
                break;
            case 'ThesisPublisher':
                $element->setMultiOptions(array_merge(array('' => 'choose_valid_thesispublisher'), $options));
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

    public function setDefaultValue($defaultValue) {
        if (!isset($this->value) || is_null($this->value)) {
            if (isset($defaultValue['value'])) {
                //Date Field has be set to current date
                if ($defaultValue['value'] === 'today') {
                    if ($this->session->language === 'de')
                        $this->default['value'] = date('d.m.Y');
                    else
                        $this->default['value'] = date('Y/m/d');
                }
                else
                    $this->default['value'] = $defaultValue['value'];
            }

            if (isset($defaultValue['edit']))
                $this->default['edit'] = $defaultValue['edit'];

            if (isset($defaultValue['public']))
                $this->default['public'] = $defaultValue['public'];
        }
    }

    public function getLabel() {
        return $this->label;
    }

    public function setLabel($label) {
        $this->label = $label;
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
