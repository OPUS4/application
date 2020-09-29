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
class Publish_Model_FormElement
{

    public $session;
    public $sessionOpus;
    public $form;
    public $log;
    public $additionalFields = [];
    public $postValues = [];
    public $isSubField = false;
    public $listOptions = [];
    //private member variables
    private $_elementName;
    private $_label;
    private $_required;                      //Bool
    private $_formElement;
    private $_datatype;
    private $_collectionRole;
    private $_collectionId;
    private $_multiplicity;
    private $_value;
    private $_default = [];
    private $_validationObject;              //Publish_Model_Validation
    private $_validation = [];
    private $_group;                         //Publish_Model_Group
    private $_subFormElements = [];     //array of Zend_Form_Element

    //Constants
    const FIRST = "FirstName";
    const LAST = "LastName";
    const VALUE = "Value";
    const LANG = "Language";
    const NR = "Number";

    public function __construct(
        $form,
        $name = null,
        $required = null,
        $formElement = null,
        $datatype = null,
        $multiplicity = null
    ) {
        $this->session = new Zend_Session_Namespace('Publish');
        $this->sessionOpus = new Zend_Session_Namespace();
        $this->log = Zend_Registry::get('Zend_Log');
        $this->form = $form;

        $this->_elementName = $name;
        $this->_label = $name;

        $this->_required = $required;

        $this->_formElement = strtolower($formElement);
        $this->_datatype = $datatype;
        $this->_multiplicity = $multiplicity;
        if (isset($this->_datatype)) {
            $this->initValidation();
        }
    }

    public function initValidation()
    {
        $this->_validationObject = new Publish_Model_Validation(
            $this->_datatype,
            $this->sessionOpus,
            $this->_collectionRole,
            $this->listOptions,
            $this->form->view
        );
        $this->_validationObject->validate();
        $this->_validation = $this->_validationObject->validator;
    }

    public function initGroup()
    {
        if ($this->isGroup()) {
            if ($this->isSubField === false) {
                $this->_group = new Publish_Model_DisplayGroup(
                    $this->_elementName,
                    $this->form,
                    $this->_multiplicity,
                    $this->log,
                    $this->session
                );
                if (isset($this->_collectionRole)) {
                    $this->_group->datatype = $this->_datatype;
                    $this->_group->collectionIds[] = $this->_collectionId;
                }

                if ($this->isPersonElement()) {
                    $implicitFields = $this->implicitFields('Person');
                    $this->addSubFormElements($implicitFields);
                } elseif ($this->isTitleElement()) {
                    $implicitFields = $this->implicitFields('Title');
                    $this->addSubFormElements($implicitFields);
                } elseif ($this->isSeriesElement()) {
                    $implicitFields = $this->implicitFields('Series');
                    $this->addSubFormElements($implicitFields);
                } elseif ($this->isSubjectUElement()) {
                    $implicitFields = $this->implicitFields('Subject');
                    $this->addSubFormElements($implicitFields);
                } else {
                    $this->addSubFormElement($this->transform());
                }

                $this->_group->setAdditionalFields($this->additionalFields);
                $this->_group->setSubFields($this->_subFormElements);
                if (isset($this->_collectionRole)) {
                    $this->_group->makeBrowseGroup();
                } else {
                    $this->_group->makeDisplayGroup();
                }
            }
            $displayGroup = $this->form->addDisplayGroup($this->_group->elements, $this->_group->label);
            $displayGroup->setDisableTranslator(true);
            return $displayGroup;
        }
    }

    private function isGroup()
    {
        return $this->isTitleElement() ||
                $this->isPersonElement() ||
                $this->isSeriesElement() ||
                $this->isSubjectUElement() ||
                $this->_datatype == 'Collection' ||
                $this->_multiplicity !== '1';
    }

    private function implicitFields($workflow)
    {
        switch ($workflow) {
            case 'Person':
                $fields = [];
                $name = $this->_elementName . self::FIRST;

                //creates two subfields for first and last name
                if (! $this->isElementPresent($name)) {
                    $first = new Publish_Model_FormElement(
                        $this->form,
                        $name,
                        false,
                        'text',
                        'Person'
                    );
                    $first->isSubField = true;
                    $first->setDefaultValue($this->_default, self::FIRST);
                    $elementFirst = $first->transform();
                    $fields[] = $elementFirst;
                }

                $name = $this->_elementName . self::LAST;
                if (! $this->isElementPresent($name)) {
                    $last = new Publish_Model_FormElement(
                        $this->form,
                        $name,
                        $this->_required,
                        'text',
                        'Person'
                    );
                    $last->isSubField = false;
                    $last->setDefaultValue($this->_default, self::LAST);
                    $elementLast = $last->transform();
                    $fields[] = $elementLast;
                }

                return $fields;
                break;

            case 'Series':
                //creates a additional field for a number
                $number = new Publish_Model_FormElement(
                    $this->form,
                    $this->_elementName . self::NR,
                    $this->_required,
                    'text',
                    'SeriesNumber'
                );
                $number->isSubField = false;
                $number->setDefaultValue($this->_default, self::NR);
                $elementNumber = $number->transform();

                $select = new Publish_Model_FormElement(
                    $this->form,
                    $this->_elementName,
                    $this->_required,
                    'select',
                    'Series'
                );
                $select->isSubField = true;
                $select->setDefaultValue($this->_default);
                $element = $select->transform();

                return [$elementNumber, $element];
                break;

            case 'Subject':
            case 'Title':
                //creates two subfields: a value text field (subject, title) and language selection
                if ($this->isTitleElement()) {
                    if ($this->isTextareaElement()) {
                        $value = new Publish_Model_FormElement(
                            $this->form,
                            $this->_elementName,
                            $this->_required,
                            'textarea',
                            'Title'
                        );
                    } else {
                        $value = new Publish_Model_FormElement(
                            $this->form,
                            $this->_elementName,
                            $this->_required,
                            'text',
                            'Title'
                        );
                    }
                } else {
                    $value = new Publish_Model_FormElement(
                        $this->form,
                        $this->_elementName,
                        $this->_required,
                        'text',
                        'Subject'
                    );
                }
                $value->isSubField = false;
                $value->setDefaultValue($this->_default, self::VALUE);
                $elementValue = $value->transform();

                // die Sprache ist nicht verpflichtend: da als Default-Option immer die Dokumentsprache angenommen wird
                $lang = new Publish_Model_FormElement(
                    $this->form,
                    $this->_elementName . self::LANG,
                    false,
                    'select',
                    'Language'
                );
                $lang->isSubField = true;

                $lang->setDefaultValue($this->_default, self::LANG);
                $elementLang = $lang->transform();

                return [$elementValue, $elementLang];
                break;
        }
    }

    private function isTitleElement()
    {
        return $this->_datatype === 'Title';
    }

    private function isSeriesElement()
    {
        return $this->_datatype === 'Series';
    }

    /**
     * Subject field must have datatype "Subject"
     * @return boolean
     */
    private function isSubjectElement()
    {
        return $this->_datatype === 'Subject';
    }

    /**
     * Subject field must have datatype "Subject" and can be a SWD or uncontrolled field (with or without languague)
     * @return boolean
     */
    private function isSubjectUElement()
    {
        return $this->_datatype === 'Subject' && ! (strstr($this->_elementName, 'Swd'));
    }

    /**
     * A Person field must have datatype "Person" or is one of the possible subfields of Person.
     * @return type
     */
    private function isPersonElement()
    {
        return $this->_datatype === 'Person'
                || strstr($this->_elementName, 'Email')
                || strstr($this->_elementName, 'Birth')
                || strstr($this->_elementName, 'AcademicTitle');
    }

    private function isSelectElement()
    {
        return $this->_formElement === 'select';
    }

    private function isTextareaElement()
    {
        return $this->_formElement === 'textarea';
    }

    public function setPostValues($postValues)
    {
        $this->postValues = $postValues;
    }

    public function setAdditionalFields($additionalFields)
    {
        $this->additionalFields = $additionalFields;
    }

    public function transform()
    {
        if (isset($this->form)) {
            if (false === $this->isSelectElement()) {
                $element = $this->form->createElement($this->_formElement, $this->_elementName);
                $element->setDisableTranslator(true);
            } else {
                $options = null;
                if (is_null($this->listOptions) || empty($this->listOptions)) {
                    $options = $this->_validationObject->selectOptions($this->_datatype);
                } else {
                    $options = $this->listOptions;
                }

                if (is_null($options)) {
                    //no options found in database / session / cache
                    $element = $this->form->createElement('text', $this->_elementName);
                    $element->setDisableTranslator(true);
                    $element->setDescription('hint_no_selection_' . $this->_elementName);
                    $element->setAttrib('disabled', true);
                    $this->_required = false;
                } else {
                    $element = $this->showSelectField($options);
                }
            }

            $element->setRequired($this->_required);
            if ($this->_required) {
                $element->addValidator(
                    'NotEmpty',
                    true,
                    ['messages' => $this->form->view->translate('publish_validation_error_notempty_isempty')]
                );
            }

            if (isset($this->_default[0]['value']) && ! empty($this->_default[0]['value'])) {
                $element->setValue($this->_default[0]['value']);
                $this->log->debug(
                    "Value set to default for " . $this->_elementName . " => "
                    . $this->_default[0]['value']
                );
            }

            if (isset($this->_default[0]['edit']) && $this->_default[0]['edit'] === 'no') {
                $element->setAttrib('disabled', true);
                $element->setRequired(false);
            }
            $element->setLabel($this->_label);
            if (! is_null($this->_validation)) {
                if (is_array($this->_validation)) {
                    $element->addValidators($this->_validation);
                } else {
                    $element->addValidator($this->_validation);
                }
            }

            $element->setAttrib('datatype', $this->realDatatype($element));

            if ($this->isSubField == true) {
                $element->setAttrib('subfield', true);
            } else {
                $element->setAttrib('subfield', false);
            }

            if ($this->_datatype == 'CollectionLeaf') {
                $element->setAttrib('collectionLeaf', true);
            }

            return $element;
        }
    }

    private function realDatatype()
    {
        if ($this->isPersonElement()) {
            return 'Person';
        }

        if ($this->isTitleElement()) {
            return 'Title';
        }

        if ($this->isSeriesElement()) {
            return 'Series';
        }

        if ($this->isSubjectElement()) {
            return 'Subject';
        }

        return $this->_datatype;
    }

    private function showSelectField($options, $datatype = null, $elementName = null)
    {
        if (isset($elementName)) {
            $name = $elementName;
        } else {
            $name = $this->_elementName;
        }
        $element = $this->form->createElement('select', $name);
        $element->setDisableTranslator(true);

        if (isset($datatype)) {
            $switchVar = $datatype;
        } else {
            $switchVar = $this->_datatype;
        }

        // reorganize $options since Zend multiOptions does not accept integers as keys
        $reorgOptions = [];
        foreach ($options as $key => $value) {
            $reorgOptions[] = [
                'key' => is_string($key) ? $key : strval($key),
                'value' => $value];
        }

        switch ($switchVar) {
            case 'Collection':
            case 'CollectionLeaf':
                $element->setMultiOptions(
                    array_merge(
                        ['' => $this->form->view->translate('choose_valid_'.$this->_collectionRole)],
                        $reorgOptions
                    )
                );
                break;

            case 'Licence':
                $element->setMultiOptions(
                    array_merge(
                        ['' => $this->form->view->translate('choose_valid_licence')],
                        $reorgOptions
                    )
                );
                break;

            case 'Language':
                if ($this->_elementName === 'Language') {
                    $element->setMultiOptions(
                        array_merge(
                            ['' => $this->form->view->translate('choose_valid_language')],
                            $reorgOptions
                        )
                    );
                } else {
                    // bei allen Sprachfeldern (außer dem Feld für die Festlegung der Dokumentsprache) wird als
                    // Default-Option "Dokumentsprache" angeboten
                    $element->setMultiOptions(
                        array_merge(
                            ['' => $this->form->view->translate('inherit_document_language')],
                            $reorgOptions
                        )
                    );
                }
                break;

            case 'ThesisGrantor':
                $element->setMultiOptions(
                    array_merge(
                        ['' => $this->form->view->translate('choose_valid_thesisgrantor')],
                        $reorgOptions
                    )
                );
                break;

            case 'ThesisPublisher':
                $element->setMultiOptions(
                    array_merge(
                        ['' => $this->form->view->translate('choose_valid_thesispublisher')],
                        $reorgOptions
                    )
                );
                break;

            case 'Series':
                $element->setMultiOptions(
                    array_merge(
                        ['' => $this->form->view->translate('choose_valid_series')],
                        $reorgOptions
                    )
                );
                break;

            default:
                $element->setMultiOptions(
                    array_merge(
                        ['' => $this->form->view->translate('choose_valid_option')],
                        $reorgOptions
                    )
                );
                break;
        }

        return $element;
    }

    public function setForm(Publish_Form_PublishingSecond $form)
    {
        $this->form = $form;
    }

    public function getElementName()
    {
        return $this->_elementName;
    }

    public function setElementName($elementName)
    {
        $this->_elementName = $elementName;
        $this->_label = $elementName;
    }

    public function getValue()
    {
        return $this->_elementName;
    }

    public function setValue($value)
    {
        $this->_value = $value;
    }

    public function getDefault()
    {
        return $this->_default;
    }

    public function setCollectionRole($root)
    {
        $this->_collectionRole = $root;
    }

    public function getCollectionRole()
    {
        return $this->_collectionRole;
    }

    public function setCurrentCollectionId($setRoot = false)
    {
        if (! $setRoot) {
            $collectionRole = Opus_CollectionRole::fetchByName($this->_collectionRole);
            if (! is_null($collectionRole)) {
                $rootCollection = $collectionRole->getRootCollection();
                if (! is_null($rootCollection)) {
                    $collectionId = $rootCollection->getId();
                    $this->log->debug(
                        "CollectionRoot: " . $this->_collectionRole . " * CollectionId: "
                        . $collectionId
                    );
                    $this->_collectionId = $collectionId;
                }
            }
        }
    }

    public function getCurrentCollectionId()
    {
        return $this->_collectionId;
    }

    public function setDefaultValue($defaultValue, $forValue = null)
    {
        if (isset($forValue)) {
            foreach ($defaultValue as $default) {
                if ($default['for'] == $forValue) {
                    $this->_default[] = $default;
                }
            }
            return;
        }

        if (! isset($this->_value) || is_null($this->_value)) {
            if (isset($defaultValue['value'])) {
                //Date Field has be set to current date
                if ($defaultValue['value'] === 'today') {
                    if ($this->sessionOpus->language === 'de') {
                        $defaultValue['value'] = date('d.m.Y');
                    } else {
                        $defaultValue['value'] = date('Y/m/d');
                    }
                }
                $this->_default[] = $defaultValue;
            }
        }
    }

    public function getLabel()
    {
        return $this->_label;
    }

    public function setLabel($label)
    {
        $this->_label = $label;
    }

    public function setListOptions($options)
    {
        $this->listOptions = $options;
        $this->initValidation();
    }

    public function getMultiplicity()
    {
        return $this->_multiplicity;
    }

    public function setMultiplicity($multiplicity)
    {
        $this->_multiplicity = $multiplicity;
    }

    public function getFormElement()
    {
        return $this->_formElement;
    }

    public function setFormElement($formElement)
    {
        $this->_formElement = strtolower($formElement);
    }

    public function getDatatype()
    {
        return $this->_datatype;
    }

    public function setDatatype($datatype)
    {
        $this->_datatype = $datatype;
        if ($this->_datatype !== 'List') {
            $this->initValidation();
        }
    }

    public function getRequired()
    {
        return $this->_required;
    }

    public function setRequired($required)
    {
        $this->_required = $required;
    }

    public function getValidator()
    {
        return $this->_validation;
    }

    public function setValidator($validator)
    {
        $this->_validation = $validator;
    }

    public function getGroup()
    {
        return $this->_group;
    }

    public function setGroup($group)
    {
        $this->_group = $group;
    }

    public function getSubFormElements()
    {
        return $this->_subFormElements;
    }

    /**
     * If elements are already present do not add again.
     *
     * @param $subFormElements
     */
    public function addSubFormElements($subFormElements)
    {
        $this->_subFormElements = array_merge($subFormElements, $this->_subFormElements);
    }

    public function addSubFormElement($subField)
    {
        $this->_subFormElements[] = $subField;
    }

    protected function isElementPresent($name)
    {
        if (is_array($this->_subFormElements)) {
            foreach ($this->_subFormElements as $element) {
                if ($element->getLabel() === $name) {
                    return true;
                }
            }
        }

        return false;
    }
}
