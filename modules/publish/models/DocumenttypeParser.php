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

use Opus\Common\EnrichmentKey;
use Opus\Common\Log;

class Publish_Model_DocumenttypeParser extends Application_Model_Abstract
{
    /** @var DOMDocument */
    public $dom;

    /** @var Publish_Form_PublishingSecond */
    public $form;

    /** @var array Array of Publish_Model_FormElement */
    public $formElements = [];

    /** @var Zend_Log */
    private $log;

    /** @var Zend_Session_Namespace */
    private $session;

    /** @var array */
    private $postValues = [];

    /** @var array */
    private $additionalFields = [];

    /**
     * @param DOMDocument                   $dom
     * @param Publish_Form_PublishingSecond $form
     * @param array                         $additionalFields
     * @param array                         $postValues
     */
    public function __construct($dom, $form, $additionalFields = [], $postValues = [])
    {
        $this->log     = Log::get();
        $this->session = new Zend_Session_Namespace('Publish');
        $this->form    = $form;
        $this->dom     = $dom;
        if (is_array($additionalFields)) {
            $this->additionalFields = $additionalFields;
        }
        if (is_array($postValues)) {
            $this->postValues = $postValues;
        }
    }

    /**
     * Parsing the XML Doumenttype for elements named "field"!
     * Allocates the member variables currentElement, formElements.
     * Parses "field" for existing attributes, subfields and subelements like "default" and "required-if-fulltext".
     * At the end each found element is transformed to Zend_Element and stored in array.
     */
    public function parse()
    {
        //parse root node for tags named 'field'
        foreach ($this->dom->getElementsByTagname('field') as $field) {
            $currentElement = new Publish_Model_FormElement($this->form);
            $currentElement->setAdditionalFields($this->additionalFields);
            $this->parseAttributes($field, $currentElement);
            $this->parseSubFields($field, $currentElement);
            $this->parseDefaultEntry($currentElement, $field);
            $this->parseRequiredIfFulltext($field, $currentElement);
            $currentElement->setPostValues($this->postValues);
            $group                = $currentElement->initGroup();
            $this->formElements[] = $group;

            if (! isset($group)) {
                $element              = $currentElement->transform();
                $this->formElements[] = $element;
            }
        }
    }

    /**
     * Allocates member variables of currentElement with found attributes in XML Documenttype for element "field".
     * Parses for top elements "field" and their atrributes.
     *
     * @param Publish_Model_FormElement $currentElement
     * @return bool
     */
    private function parseAttributes(DOMElement $field, $currentElement)
    {
        if ($field->hasAttributes()) {
            $elementName  = $field->getAttribute('name');
            $required     = $field->getAttribute('required');
            $formElement  = $field->getAttribute('formelement');
            $datatype     = $field->getAttribute('datatype');
            $multiplicity = $field->getAttribute('multiplicity');

            if ($datatype === 'Enrichment') {
                if ($this->isValidEnrichmentKey($elementName)) {
                    $elementName = 'Enrichment' . $elementName;
                }
            }

            if ($datatype === 'Collection' || $datatype === 'CollectionLeaf') {
                $collectionRole = $field->getAttribute('root');
                $currentElement->setCollectionRole($collectionRole);
                $currentElement->setCurrentCollectionId();
            }

            $this->zendConformElementName($elementName);

            $currentElement->setElementName($elementName);

            if ($required === 'yes') {
                $currentElement->setRequired(true);
            } else {
                $currentElement->setRequired(false);
            }

            $currentElement->setFormElement($formElement);
            $currentElement->setDatatype($datatype);
            $currentElement->setMultiplicity($multiplicity);
            return true;
        } else {
            // No Attributes found!
            return false;
        }
    }

    /**
     * Allocates member variables of currentElement and its children.
     * Parses for child nodes and there atrributes.
     *
     * @param Publish_Model_FormElement $currentElement
     * @return bool false: no child nodes or no attributes have been found.
     */
    private function parseSubFields(DOMElement $field, $currentElement)
    {
        if ($field->hasChildNodes()) {
            foreach ($field->getElementsByTagname('subfield') as $subField) {
                //subfields have also type FormElement
                $currentSubField = new Publish_Model_FormElement($this->form);

                if ($subField->hasAttributes()) {
                    $subElementName = $subField->getAttribute('name');

                    if (! $this->useSubfield($currentElement->getElementName(), $subElementName)) {
                        continue;
                    }

                    $subRequired    = $subField->getAttribute('required');
                    $subFormElement = $subField->getAttribute('formelement');
                    $subDatatype    = $subField->getAttribute('datatype');

                    $currentSubField->setElementName($currentElement->getElementName() . $subElementName);
                    if ($subRequired === 'yes') {
                        $currentSubField->setRequired(true);
                    } else {
                        $currentSubField->setRequired(false);
                    }
                    $currentSubField->setFormElement($subFormElement);
                    $currentSubField->setDatatype($subDatatype);

                    $currentSubField->isSubField = true;
                } else {
                    // No Attributes found!
                    return false;
                }

                if ($subField->hasChildNodes()) {
                    $this->parseDefaultEntry($currentElement, $subField, $currentSubField);
                }
                $currentElement->addSubFormElement($currentSubField->transform());
            }

            $options = [];
            foreach ($field->getElementsByTagname('option') as $option) {
                if ($option->hasAttributes()) {
                    $value           = $option->getAttribute('value');
                    $options[$value] = $value;
                }
            }
            $currentElement->setListOptions($options);

            return true;
        } else {
            // No Subfields found!
            return false;
        }
    }

    /**
     * @param string $elementName
     * @param string $subfieldName
     * @return bool
     */
    private function useSubfield($elementName, $subfieldName)
    {
        switch ($elementName) {
            case 'PersonAuthor':
                if (
                    ! $this->includeExtendedAuthorInformation()
                    && in_array($subfieldName, ['DateOfBirth', 'PlaceOfBirth'])
                ) {
                    return false;
                }
                if (
                    ! $this->includeAuthorEmail()
                    && in_array($subfieldName, ['Email', 'AllowEmailContact'])
                ) {
                    return false;
                }
                break;
            default:
        }

        return true;
    }

    /**
     * Allocates member variables of currentElement or can be used for subfields.
     * Parses for default values and the possibility of edit and make it public.
     *
     * @param Publish_Model_FormElement $currentElement
     * @return bool false if there are no child nodes
     */
    private function parseDefaultEntry(
        $currentElement,
        DOMElement $field,
        ?Publish_Model_FormElement $subfield = null
    ) {
        if ($field->hasChildNodes()) {
            foreach ($field->getElementsByTagname('default') as $default) {
                if ($default->hasAttributes()) {
                    $defaultArray = [];

                    $forValue = $default->getAttribute('for');
                    if (isset($forValue)) {
                        $defaultArray['for'] = $forValue;
                    }

                    $value = $default->getAttribute('value');
                    if (isset($value)) {
                        $defaultArray['value'] = $value;
                    }

                    $edit = $default->getAttribute('edit');
                    if (isset($edit)) {
                        $defaultArray['edit'] = $edit;
                    }

                    $public = $default->getAttribute('public');
                    if (isset($public)) {
                        $defaultArray['public'] = $public;
                    }

                    if (! isset($subfield)) {
                        $currentElement->setDefaultValue($defaultArray);
                        $this->log->debug(__METHOD__ . " : " . $value);
                    } else {
                        $subfield->setDefaultValue($defaultArray);
                    }
                } else {
                    return false;
                }
            }

            return true;
        }

        return false;
    }

    /**
     * Allocates member variables currentElement.
     * Parses for specific child node "required-if-fulltext" and sets the value "required" to true in case a fulltext
     * has been uploaded.
     *
     * @param Publish_Model_FormElement $currentElement
     */
    private function parseRequiredIfFulltext(DOMElement $field, $currentElement)
    {
        if ($field->hasChildNodes()) {
            foreach ($field->getElementsByTagname('required-if-fulltext') as $fulltext) {
                if ($this->session->fulltext) {
                    $currentElement->setRequired(true);
                    $this->log->debug(
                        "currentElement : " . $currentElement->getElementName()
                        . " and its required has been set to true!"
                    );
                } else {
                    $this->log->debug(
                        "currentElement : " . $currentElement->getElementName()
                        . " and its required hasn't been changed!"
                    );
                }
            }
        }
    }

    /**
     * @return array
     */
    public function getFormElements()
    {
        return $this->formElements;
    }

    /**
     * @param string $string
     * @return true if string can be used as zend_form_element name, else Exception
     */
    private function zendConformElementName($string)
    {
        $element = new Zend_Form_Element_Text($string);
        $element->setName($string);

        if ($element->getName() !== $string) {
            throw new Publish_Model_FormIncorrectFieldNameException($string);
        }

        return true;
    }

    /**
     * @param string $elementName
     * @return true
     * @throws Publish_Model_FormIncorrectEnrichmentKeyException
     */
    private function isValidEnrichmentKey($elementName)
    {
        $enrichment = EnrichmentKey::fetchByName($elementName);
        if ($enrichment === null) {
            throw new Publish_Model_FormIncorrectEnrichmentKeyException($elementName);
        }

        return true;
    }

    /**
     * @return bool
     * @throws Zend_Exception
     */
    public function includeAuthorEmail()
    {
        $config = $this->getConfig();

        return isset($config->publish->includeAuthorEmail)
            && filter_var($config->publish->includeAuthorEmail, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @return bool
     * @throws Zend_Exception
     */
    public function includeExtendedAuthorInformation()
    {
        $config = $this->getConfig();

        return isset($config->publish->includeExtendedAuthorInformation)
            && filter_var($config->publish->includeExtendedAuthorInformation, FILTER_VALIDATE_BOOLEAN);
    }
}
