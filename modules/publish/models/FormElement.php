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

    public $form;
    public $log;
    public $additionalFields = array();
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
    private $validationObject;
    private $validation;    //Publish_Model_Validation
    private $group;         //Publish_Model_Group
    private $subFormElements = array();         //array of Zend_Form_Element

    public function __construct($name = null, $required = null, $formElement = null, $datatype = null, $multiplicity = null) {

        $this->log = Zend_Registry::get('Zend_Log');

        $this->elementName = $name;
        $this->label = $name;

        if (isset($required) && $required === 'yes')
            $this->required = true;
        else
            $this->required = false;

        $this->formElement = $formelement;
        $this->datatype = $datatype;
        $this->multiplicity = $multiplicity;

        if (isst($this->datatype))
            $this->initValidation();

        $this->initGroup();
    }

    private function initValidation() {
        $this->validationObject = new Publish_Model_Validation($this->datatype);
        $this->validationObject->validate();
        return $this->validation = $this->validationObject->validator;
    }

    private function initGroup() {

        if ($this->isSubField === false) {
            if ($this->isPersonElement())
                $this->group = new Publish_Model_DisplayGroup('Person', $this->elementName);

            else {
                if ($this->isTitleElement())
                    $this->group = new Publish_Model_DisplayGroup('Title', $this->elementName);
            }
            $this->group->setAddtionalFields($this->additionalFields);
            $this->group->setSubFields($this->subFormElements);
            $group = $group->getGroupLabel();
            return;

            if ($this->multiplicity !== '1') {
                $this->group = new Publish_Model_DisplayGroup('Multi', $this->elementName);
            }
        }
    }

    private function isTitleElement() {
        if (strstr($this->elementName, 'Title')
                || strstr($this->elementName, 'Abstract'))
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
        if ($this->formElement === 'select')
            return true;
        else
            return false;
    }

    public function setPostValue($postValues) {
        if (isset($postValues) && is_array($postValues))
            if (array_key_exists($this->elementName, $postValues))
                $this->value = $postValues[$this->elementName];
    }

    public function setAdditionalFields($additionalFields) {
        $this->additionalFields = $additionalFields;
    }

    public function transform() {
        if (isset($this->form)) {

            if (false === $this->isSelectElement()) {

                $element = $this->form->createElement($this->formElement, $this->elementName);
            } else {
                $options = $this->validationObject->selectOptions();
                if (!isset($options)) {
                    //no options found in database / session / cache
                    $element = $this->form->createElement('text', $this->elementName);
                    $element->setDescription('hint_no_collection_' . $workflow)
                            ->setAttrib('disabled', true);
                    $required = null;
                }
                else
                    $element = $this->showSelectField($options);
            }

            $element->setRequired($this->required);

            if (isset($this->default['value']))
                $element->setValue($this->default['value']);
            else
                $element->setValue($this->value);

            if (isset($this->default['edit']) && $this->default['edit'] === 'no') {
                $element->setAttrib('disabled', true);
                $element->setRequired(false);
            }
            $element->setLabel($this->label);
            $element->addValidator($this->validation);

            return $element;
        }
    }

    private function showSelectField($options) {
        if (count($options) == 1) {
            //if theres only one entry, just show an text fields
            $value = (array_keys($options));
            $element = $this->createElement('text', $elementName);
            $element->setValue($value[0]);
        } else {
            //at least 2 entry: show a select field
            $element = $this->form->createElement('select', $this->elementName);

            switch ($this->datatype) {
                case 'Licence' :
                    $element->setMultiOptions(array_merge(array('' => 'choose_valid_licence'), $options));
                    break;
                case 'Language' :
                    $element->setMultiOptions(array_merge(array('' => 'choose_valid_language'), $options));
                    break;
                case 'Project' :
                    $element->setMultiOptions(array_merge(array('' => 'choose_valid_project'), $options));
                    break;
                case 'Institute' :
                    $element->setMultiOptions(array_merge(array('' => 'choose_valid_institute'), $options));
            }
        }
    }

    public function setForm(Publish_Form_PublishingSecond $form) {
        $this->form = $form;
    }

    public function getElementName() {
        return $this->elementName;
    }

    public function setElementName($elementName) {
        $this->elementName = $elementName;
    }

    public function getValue() {
        return $this->elementName;
    }

    public function setValue($value) {
        $this->value;
    }

    public function getDefault() {
        return $this->default;
    }

    public function setDefaultValue($defaultValue) {
        if (isset($defaultValue['value']))
            $this->default['value'] = $defaultValue['value'];

        if (isset($defaultValue['edit']))
            $this->default['edit'] = $defaultValue['edit'];

        if (isset($defaultValue['public']))
            $this->default['public'] = $defaultValue['public'];
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

    public function setSubFormElements($subFormElements) {
        $this->subFormElements = $subFormElements;
    }

    public function addSubFormElement(Publish_Model_FormElement $subFormElement) {
        $subField = $subFormElement->transform();
        $this->subFormElements[] = $subField;
    }

}

?>
