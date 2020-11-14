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
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2019, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\EnrichmentKey;

class Publish_Model_DocumenttypeParser extends Application_Model_Abstract
{

    /**
     *
     * @var \DOMDocument
     */
    public $dom;

    /**
     *
     * @var Publish_Form_PublishingSecond
     */
    public $form;

    /**
     *
     * @var array Array of Publish_Model_FormElement
     */
    public $formElements = [];

    private $_log;
    private $_session;
    private $_postValues = [];
    private $_additionalFields = [];

    /**
     *
     * @param \DOMDocument $dom
     * @param Publish_Form_PublishingSecond $form
     * @param array $additionalFields
     * @param array $postValues
     */
    public function __construct($dom, $form, $additionalFields = [], $postValues = [])
    {
        $this->_log = \Zend_Registry::get('Zend_Log');
        $this->_session = new \Zend_Session_Namespace('Publish');
        $this->form = $form;
        $this->dom = $dom;
        if (is_array($additionalFields)) {
            $this->_additionalFields = $additionalFields;
        }
        if (is_array($postValues)) {
            $this->_postValues = $postValues;
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
            $currentElement->setAdditionalFields($this->_additionalFields);
            $this->_parseAttributes($field, $currentElement);
            $this->_parseSubFields($field, $currentElement);
            $this->_parseDefaultEntry($currentElement, $field);
            $this->_parseRequiredIfFulltext($field, $currentElement);
            $currentElement->setPostValues($this->_postValues);
            $group = $currentElement->initGroup();
            $this->formElements[] = $group;

            if (! isset($group)) {
                $element = $currentElement->transform();
                $this->formElements[] = $element;
            }
        }
    }

    /**
     * Allocates member variables of currentElement with found attributes in XML Documenttype for element "field".
     * Parses for top elements "field" and their atrributes.
     *
     * @param DomElement $field
     * @param Publish_Model_FormElement $currentElement
     *
     * @return false: field has no attributes
     */
    private function _parseAttributes(DomElement $field, $currentElement)
    {

        if ($field->hasAttributes()) {
            $elementName = $field->getAttribute('name');
            $required = $field->getAttribute('required');
            $formElement = $field->getAttribute('formelement');
            $datatype = $field->getAttribute('datatype');
            $multiplicity = $field->getAttribute('multiplicity');

            if ($datatype === 'Enrichment') {
                if ($this->isValidEnrichmentKey($elementName)) {
                    $elementName = 'Enrichment' . $elementName;
                }
            }

            if ($datatype == 'Collection' || $datatype == 'CollectionLeaf') {
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
        } else {
            // No Attributes found!
            return false;
        }
    }

    /**
     * Allocates member variables of currentElement and its children.
     * Parses for child nodes and there atrributes.
     * @param DomElement $field
     * @return false: no child nodes or no attributes have been found.
     */
    private function _parseSubFields(DomElement $field, $currentElement)
    {
        if ($field->hasChildNodes()) {
            foreach ($field->getElementsByTagname('subfield') as $subField) {
                //subfields have also type FormElement
                $currentSubField = new Publish_Model_FormElement($this->form);

                if ($subField->hasAttributes()) {
                    $subElementName = $subField->getAttribute('name');

                    if (! $this->useSubfield($currentElement->getElementName(), $subElementName)) {
                        continue;
                    };

                    $subRequired = $subField->getAttribute('required');
                    $subFormElement = $subField->getAttribute('formelement');
                    $subDatatype = $subField->getAttribute('datatype');

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
                //No Attributes found!
                    return false;
                }

                if ($subField->hasChildNodes()) {
                    $this->_parseDefaultEntry($currentElement, $subField, $currentSubField);
                }
                $currentElement->addSubFormElement($currentSubField->transform());
            }

            $options = [];
            foreach ($field->getElementsByTagname('option') as $option) {
                if ($option->hasAttributes()) {
                    $value = $option->getAttribute('value');
                    $options[$value] = $value;
                }
            }
            $currentElement->setListOptions($options);
        } else {
            // No Subfields found!
            return false;
        }
    }

    private function useSubfield($elementName, $subfieldName)
    {
        switch ($elementName) {
            case 'PersonAuthor':
                if (! $this->includeExtendedAuthorInformation()
                    && in_array($subfieldName, ['DateOfBirth', 'PlaceOfBirth'])) {
                    return false;
                }
                if (! $this->includeAuthorEmail()
                    && in_array($subfieldName, ['Email', 'AllowEmailContact'])) {
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
     * @param DOMElement $field
     * @param Publish_Model_FormElement $subfield
     * @return false if there are no child nodes
     */
    private function _parseDefaultEntry(
        $currentElement,
        DOMElement $field,
        Publish_Model_FormElement $subfield = null
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
                        $this->_log->debug(__METHOD__ . " : " . $value);
                    } else {
                        $subfield->setDefaultValue($defaultArray);
                    }
                } else {
                    return false;
                }
            }
        }
    }

    /**
     * Allocates member variables currentElement.
     * Parses for specific child node "required-if-fulltext" and sets the value "required" to true in case a fulltext
     * has been uploaded.
     * @param DomElement $field
     */
    private function _parseRequiredIfFulltext(DomElement $field, $currentElement)
    {
        if ($field->hasChildNodes()) {
            foreach ($field->getElementsByTagname('required-if-fulltext') as $fulltext) {
                if ($this->_session->fulltext === '1') {
                    $currentElement->setRequired(true);
                    $this->_log->debug(
                        "currentElement : " . $currentElement->getElementName()
                        . " and its required has been set to true!"
                    );
                } else {
                    $this->_log->debug(
                        "currentElement : " . $currentElement->getElementName()
                        . " and its required hasn't been changed!"
                    );
                }
            }
        }
    }

    public function getFormElements()
    {
        return $this->formElements;
    }

    /**
     * @return true if string can be used as zend_form_element name, else Exception
     *
     */
    private function zendConformElementName($string)
    {

        $element = new \Zend_Form_Element_Text($string);
        $element->setName($string);

        if ($element->getName() !== $string) {
            throw new Publish_Model_FormIncorrectFieldNameException($string);
        }

        return true;
    }

    private function isValidEnrichmentKey($elementName)
    {
        $enrichment = EnrichmentKey::fetchByName($elementName);
        if (is_null($enrichment)) {
            throw new Publish_Model_FormIncorrectEnrichmentKeyException($elementName);
        }

        return true;
    }

    public function includeAuthorEmail()
    {
        $config = $this->getConfig();

        return isset($config->publish->includeAuthorEmail)
            && filter_var($config->publish->includeAuthorEmail, FILTER_VALIDATE_BOOLEAN);
    }

    public function includeExtendedAuthorInformation()
    {
        $config = $this->getConfig();

        return isset($config->publish->includeExtendedAuthorInformation)
            && filter_var($config->publish->includeExtendedAuthorInformation, FILTER_VALIDATE_BOOLEAN);
    }
}
