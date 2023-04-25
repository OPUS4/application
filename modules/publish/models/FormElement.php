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
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\CollectionRole;
use Opus\Common\Log;

class Publish_Model_FormElement
{
    /** @var Zend_Session_Namespace */
    public $session;

    /** @var Zend_Session_Namespace */
    public $sessionOpus;

    /** @var Zend_Form */
    public $form;

    /** @var Log */
    public $log;

    /** @var array */
    public $additionalFields = [];

    /** @var array */
    public $postValues = [];

    /** @var bool */
    public $isSubField = false;

    /** @var array */
    public $listOptions = [];

    //private member variables

    /** @var string|null */
    private $elementName;

    /** @var string|null  */
    private $label;

    /** @var bool */
    private $required;

    /** @var string */
    private $formElement;

    /** @var mixed|null */
    private $datatype;

    /** @var string */
    private $collectionRole;

    /** @var int */
    private $collectionId;

    /** @var int */
    private $multiplicity;

    /** @var string */
    private $value;

    /** @var array */
    private $default = [];

    /** @var Publish_Model_Validation */
    private $validationObject;

    /** @var array */
    private $validation = [];

    /** @var Publish_Model_DisplayGroup */
    private $group;

    /** @var Zend_Form_Element[] */
    private $subFormElements = [];

    //Constants
    public const FIRST = "FirstName";
    public const LAST  = "LastName";
    public const VALUE = "Value";
    public const LANG  = "Language";
    public const NR    = "Number";

    /**
     * @param Publish_Form_PublishingSecond $form
     * @param string|null                   $name
     * @param bool|null                     $required
     * @param bool|null                     $formElement
     * @param string|null                   $datatype
     * @param int|null                      $multiplicity
     * @throws Zend_Exception
     *
     * // TODO BUG parameter definition like int|null
     */
    public function __construct(
        $form,
        $name = null,
        $required = null,
        $formElement = null,
        $datatype = null,
        $multiplicity = null
    ) {
        $this->session     = new Zend_Session_Namespace('Publish');
        $this->sessionOpus = new Zend_Session_Namespace();
        $this->log         = Log::get();
        $this->form        = $form;

        $this->elementName = $name;
        $this->label       = $name;

        $this->required = $required;

        $this->formElement  = $formElement !== null ? strtolower($formElement) : ''; // TODO PHP8
        $this->datatype     = $datatype;
        $this->multiplicity = $multiplicity;
        if (isset($this->datatype)) {
            $this->initValidation();
        }
    }

    public function initValidation()
    {
        $this->validationObject = new Publish_Model_Validation(
            $this->datatype,
            $this->sessionOpus,
            $this->collectionRole,
            $this->listOptions,
            $this->form->view
        );
        $this->validationObject->validate();
        $this->validation = $this->validationObject->validator;
    }

    /**
     * @return Zend_Form
     * @throws Zend_Form_Exception
     */
    public function initGroup()
    {
        if ($this->isGroup()) {
            if ($this->isSubField === false) {
                $this->group = new Publish_Model_DisplayGroup(
                    $this->elementName,
                    $this->form,
                    $this->multiplicity,
                    $this->log,
                    $this->session
                );
                if (isset($this->collectionRole)) {
                    $this->group->datatype        = $this->datatype;
                    $this->group->collectionIds[] = $this->collectionId;
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

                $this->group->setAdditionalFields($this->additionalFields);
                $this->group->setSubFields($this->subFormElements);
                if (isset($this->collectionRole)) {
                    $this->group->makeBrowseGroup();
                } else {
                    $this->group->makeDisplayGroup();
                }
            }
            $displayGroup = $this->form->addDisplayGroup($this->group->elements, $this->group->label);
            $displayGroup->setDisableTranslator(true);
            return $displayGroup;
        }
    }

    /**
     * @return bool
     */
    private function isGroup()
    {
        return $this->isTitleElement() ||
                $this->isPersonElement() ||
                $this->isSeriesElement() ||
                $this->isSubjectUElement() ||
                $this->datatype === 'Collection' ||
                $this->multiplicity !== '1';
    }

    /**
     * @param string $workflow
     * @return array|null
     * @throws Zend_Exception
     */
    private function implicitFields($workflow)
    {
        switch ($workflow) {
            case 'Person':
                $fields = [];
                $name   = $this->elementName . self::FIRST;

                //creates two subfields for first and last name
                if (! $this->isElementPresent($name)) {
                    $first             = new Publish_Model_FormElement(
                        $this->form,
                        $name,
                        false,
                        'text',
                        'Person'
                    );
                    $first->isSubField = true;
                    $first->setDefaultValue($this->default, self::FIRST);
                    $elementFirst = $first->transform();
                    $fields[]     = $elementFirst;
                }

                $name = $this->elementName . self::LAST;
                if (! $this->isElementPresent($name)) {
                    $last             = new Publish_Model_FormElement(
                        $this->form,
                        $name,
                        $this->required,
                        'text',
                        'Person'
                    );
                    $last->isSubField = false;
                    $last->setDefaultValue($this->default, self::LAST);
                    $elementLast = $last->transform();
                    $fields[]    = $elementLast;
                }

                return $fields;

            case 'Series':
                //creates a additional field for a number
                $number             = new Publish_Model_FormElement(
                    $this->form,
                    $this->elementName . self::NR,
                    $this->required,
                    'text',
                    'SeriesNumber'
                );
                $number->isSubField = false;
                $number->setDefaultValue($this->default, self::NR);
                $elementNumber = $number->transform();

                $select             = new Publish_Model_FormElement(
                    $this->form,
                    $this->elementName,
                    $this->required,
                    'select',
                    'Series'
                );
                $select->isSubField = true;
                $select->setDefaultValue($this->default);
                $element = $select->transform();

                return [$elementNumber, $element];

            case 'Subject':
            case 'Title':
                //creates two subfields: a value text field (subject, title) and language selection
                if ($this->isTitleElement()) {
                    if ($this->isTextareaElement()) {
                        $value = new Publish_Model_FormElement(
                            $this->form,
                            $this->elementName,
                            $this->required,
                            'textarea',
                            'Title'
                        );
                    } else {
                        $value = new Publish_Model_FormElement(
                            $this->form,
                            $this->elementName,
                            $this->required,
                            'text',
                            'Title'
                        );
                    }
                } else {
                    $value = new Publish_Model_FormElement(
                        $this->form,
                        $this->elementName,
                        $this->required,
                        'text',
                        'Subject'
                    );
                }
                $value->isSubField = false;
                $value->setDefaultValue($this->default, self::VALUE);
                $elementValue = $value->transform();

                // die Sprache ist nicht verpflichtend: da als Default-Option immer die Dokumentsprache angenommen wird
                $lang             = new Publish_Model_FormElement(
                    $this->form,
                    $this->elementName . self::LANG,
                    false,
                    'select',
                    'Language'
                );
                $lang->isSubField = true;

                $lang->setDefaultValue($this->default, self::LANG);
                $elementLang = $lang->transform();

                return [$elementValue, $elementLang];
        }

        return null;
    }

    /**
     * @return bool
     */
    private function isTitleElement()
    {
        return $this->datatype === 'Title';
    }

    /**
     * @return bool
     */
    private function isSeriesElement()
    {
        return $this->datatype === 'Series';
    }

    /**
     * Subject field must have datatype "Subject"
     *
     * @return bool
     */
    private function isSubjectElement()
    {
        return $this->datatype === 'Subject';
    }

    /**
     * Subject field must have datatype "Subject" and can be a SWD or uncontrolled field (with or without languague)
     *
     * @return bool
     */
    private function isSubjectUElement()
    {
        return $this->datatype === 'Subject' && ! strstr($this->elementName, 'Swd');
    }

    /**
     * A Person field must have datatype "Person" or is one of the possible subfields of Person.
     *
     * @return bool
     */
    private function isPersonElement()
    {
        return $this->datatype === 'Person'
                || strstr($this->elementName, 'Email')
                || strstr($this->elementName, 'Birth')
                || strstr($this->elementName, 'AcademicTitle');
    }

    /**
     * @return bool
     */
    private function isSelectElement()
    {
        return $this->formElement === 'select';
    }

    /**
     * @return bool
     */
    private function isTextareaElement()
    {
        return $this->formElement === 'textarea';
    }

    /**
     * @param array $postValues
     */
    public function setPostValues($postValues)
    {
        $this->postValues = $postValues;
    }

    /**
     * @param array $additionalFields
     */
    public function setAdditionalFields($additionalFields)
    {
        $this->additionalFields = $additionalFields;
    }

    /**
     * @return Zend_Form_Element|null
     * @throws Zend_Form_Exception
     */
    public function transform()
    {
        if (isset($this->form)) {
            if (false === $this->isSelectElement()) {
                $element = $this->form->createElement($this->formElement, $this->elementName);
                $element->setDisableTranslator(true);
            } else {
                $options = null;
                if ($this->listOptions === null || empty($this->listOptions)) {
                    $options = $this->validationObject->selectOptions($this->datatype);
                } else {
                    $options = $this->listOptions;
                }

                if ($options === null) {
                    //no options found in database / session / cache
                    $element = $this->form->createElement('text', $this->elementName);
                    $element->setDisableTranslator(true);
                    $element->setDescription('hint_no_selection_' . $this->elementName);
                    $element->setAttrib('disabled', true);
                    $this->required = false;
                } else {
                    $element = $this->showSelectField($options);
                }
            }

            $element->setRequired($this->required);
            if ($this->required) {
                $element->addValidator(
                    'NotEmpty',
                    true,
                    ['messages' => $this->form->view->translate('publish_validation_error_notempty_isempty')]
                );
            }

            if (isset($this->default[0]['value']) && ! empty($this->default[0]['value'])) {
                $element->setValue($this->default[0]['value']);
                $this->log->debug(
                    'Value set to default for ' . $this->elementName . ' => '
                    . $this->default[0]['value']
                );
            }

            if (isset($this->default[0]['edit']) && $this->default[0]['edit'] === 'no') {
                $element->setAttrib('disabled', true);
                $element->setRequired(false);
            }
            $element->setLabel($this->label);
            if ($this->validation !== null) {
                if (is_array($this->validation)) {
                    $element->addValidators($this->validation);
                } else {
                    $element->addValidator($this->validation);
                }
            }

            $element->setAttrib('datatype', $this->realDatatype($element));

            if ($this->isSubField) {
                $element->setAttrib('subfield', true);
            } else {
                $element->setAttrib('subfield', false);
            }

            if ($this->datatype === 'CollectionLeaf') {
                $element->setAttrib('collectionLeaf', true);
            }

            return $element;
        }

        return null;
    }

    /**
     * @return string|null
     */
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

        return $this->datatype;
    }

    /**
     * @param array       $options
     * @param string|null $datatype
     * @param string|null $elementName
     * @return Zend_Form_Element
     * @throws Zend_Form_Exception
     */
    private function showSelectField($options, $datatype = null, $elementName = null)
    {
        if (isset($elementName)) {
            $name = $elementName;
        } else {
            $name = $this->elementName;
        }
        $element = $this->form->createElement('select', $name);
        $element->setDisableTranslator(true);

        if (isset($datatype)) {
            $switchVar = $datatype;
        } else {
            $switchVar = $this->datatype;
        }

        // reorganize $options since Zend multiOptions does not accept integers as keys
        $reorgOptions = [];
        foreach ($options as $key => $value) {
            $reorgOptions[] = [
                'key'   => is_string($key) ? $key : strval($key),
                'value' => $value,
            ];
        }

        switch ($switchVar) {
            case 'Collection':
            case 'CollectionLeaf':
                $element->setMultiOptions(
                    array_merge(
                        ['' => $this->form->view->translate('choose_valid_' . $this->collectionRole)],
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
                if ($this->elementName === 'Language') {
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

    /**
     * @return string|null
     */
    public function getElementName()
    {
        return $this->elementName;
    }

    /**
     * @param string $elementName
     */
    public function setElementName($elementName)
    {
        $this->elementName = $elementName;
        $this->label       = $elementName;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->elementName;
    }

    /**
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return array
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * @param string $root
     */
    public function setCollectionRole($root)
    {
        $this->collectionRole = $root;
    }

    /**
     * @return string
     */
    public function getCollectionRole()
    {
        return $this->collectionRole;
    }

    /**
     * @param bool $setRoot
     */
    public function setCurrentCollectionId($setRoot = false)
    {
        if (! $setRoot) {
            $collectionRole = CollectionRole::fetchByName($this->collectionRole);
            if ($collectionRole !== null) {
                $rootCollection = $collectionRole->getRootCollection();
                if ($rootCollection !== null) {
                    $collectionId = $rootCollection->getId();
                    $this->log->debug(
                        "CollectionRoot: " . $this->collectionRole . " * CollectionId: "
                        . $collectionId
                    );
                    $this->collectionId = $collectionId;
                }
            }
        }
    }

    /**
     * @return int
     */
    public function getCurrentCollectionId()
    {
        return $this->collectionId;
    }

    /**
     * @param array       $defaultValue
     * @param string|null $forValue
     */
    public function setDefaultValue($defaultValue, $forValue = null)
    {
        if (isset($forValue)) {
            foreach ($defaultValue as $default) {
                if ($default['for'] === $forValue) {
                    $this->default[] = $default;
                }
            }
            return;
        }

        if (! isset($this->value) || $this->value === null) {
            if (isset($defaultValue['value'])) {
                //Date Field has be set to current date
                if ($defaultValue['value'] === 'today') {
                    if ($this->sessionOpus->language === 'de') {
                        $defaultValue['value'] = date('d.m.Y');
                    } else {
                        $defaultValue['value'] = date('Y/m/d');
                    }
                }
                $this->default[] = $defaultValue;
            }
        }
    }

    /**
     * @return string|null
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * @param array $options
     */
    public function setListOptions($options)
    {
        $this->listOptions = $options;
        $this->initValidation();
    }

    /**
     * @return int|null
     */
    public function getMultiplicity()
    {
        return $this->multiplicity;
    }

    /**
     * @param int $multiplicity
     */
    public function setMultiplicity($multiplicity)
    {
        $this->multiplicity = $multiplicity;
    }

    /**
     * @return string
     */
    public function getFormElement()
    {
        return $this->formElement;
    }

    /**
     * @param string $formElement
     */
    public function setFormElement($formElement)
    {
        $this->formElement = strtolower($formElement);
    }

    /**
     * @return string|null
     */
    public function getDatatype()
    {
        return $this->datatype;
    }

    /**
     * @param string $datatype
     */
    public function setDatatype($datatype)
    {
        $this->datatype = $datatype;
        if ($this->datatype !== 'List') {
            $this->initValidation();
        }
    }

    /**
     * @return bool|null
     */
    public function getRequired()
    {
        return $this->required;
    }

    /**
     * @param bool $required
     */
    public function setRequired($required)
    {
        $this->required = $required;
    }

    /**
     * @return array
     */
    public function getValidator()
    {
        return $this->validation;
    }

    /**
     * @param array $validator
     */
    public function setValidator($validator)
    {
        $this->validation = $validator;
    }

    /**
     * @return Publish_Model_DisplayGroup
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @param Publish_Model_DisplayGroup $group
     */
    public function setGroup($group)
    {
        $this->group = $group;
    }

    /**
     * @return Zend_Form_Element[]
     */
    public function getSubFormElements()
    {
        return $this->subFormElements;
    }

    /**
     * If elements are already present do not add again.
     *
     * @param array $subFormElements
     */
    public function addSubFormElements($subFormElements)
    {
        $this->subFormElements = array_merge($subFormElements, $this->subFormElements);
    }

    /**
     * @param Zend_Form_Element $subField
     */
    public function addSubFormElement($subField)
    {
        $this->subFormElements[] = $subField;
    }

    /**
     * @param string $name
     * @return bool
     */
    protected function isElementPresent($name)
    {
        if (is_array($this->subFormElements)) {
            foreach ($this->subFormElements as $element) {
                if ($element->getLabel() === $name) {
                    return true;
                }
            }
        }

        return false;
    }
}
