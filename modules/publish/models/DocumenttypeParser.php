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

/**
 * Description of DocumenttypeParser
 *
 * @author Susanne Gottwald
 */
class Publish_Model_DocumenttypeParser {

    private $log;
    private $session;
    public $dom;
    public $form; //PublishingSecond
    public $formElements = array(); // Array of FormElement
    private $currentElement; //FormElement
    private $postValues = array();
    private $additionalFields = array();

    /**
     * Constructor!!!
     * Allocates the member variables form, log, session and dom.
     * @param DOMDocument $dom
     * @param Publish_Form_PublishingSecond $form
     * @return false: Dom Object couldn't be accessed
     */
    public function __construct(DOMDocument $dom, Publish_Form_PublishingSecond $form) {
        $this->form = $form;
        $this->log = Zend_Registry::get('Zend_Log');
        $this->session = new Zend_Session_Namespace('Publish');
        if (!is_null($dom))
            $this->dom = $dom;
        else
            return false;
    }

    /**
     * Parsing the XML Doumenttype for elements named "field"!
     * Allocates the member variables currentElement, formElements.
     * Parses "field" for existing attributes, subfields and subelements like "default" and "required-if-fulltext".
     * At the end each found element is transformed to Zend_Element and stored in array.
     */
    public function parse() {
        //parse root node for tags named 'field'
        foreach ($this->dom->getElementsByTagname('field') as $field) {

            $this->currentElement = new Publish_Model_FormElement($this->form);

            $this->currentElement->setAdditionalFields($this->additionalFields);

            $this->_parseAttributes($field);

            $this->_parseSubFields($field);

            $this->_parseDefaultEntry($field);

            $this->_parseRequiredIfFulltext($field);

            $this->currentElement->setPostValues($this->postValues);

            $group = $this->currentElement->initGroup();

            $this->formElements[] = $group;
            
            if (!isset($group)) {
                $element = $this->currentElement->transform();
                $this->formElements[] = $element;
            }
        }
    }

    /**
     * Allocates member variable additionalFields. Used for Publishing_Second.
     * @param <Array> $additionalFields
     */
    public function setAdditionalFields($additionalFields) {
        if (isset($additionalFields) && is_array($additionalFields))
            $this->additionalFields = $additionalFields;
    }

    /**
     * Allocates member variable postValues. Used for Publishing_Second.
     * @param <Array> $postValues
     */
    public function setPostValues($postValues) {
        if (isset($postValues) && is_array($postValues))
            $this->postValues = $postValues;
    }

    /**
     * Allocates member variables of currentElement with found attributes in XML Documenttype for element "field".
     * Parses for top elements "field" and their atrributes.
     * @param DomElement $field
     * @return false: field has no attributes
     */
    private function _parseAttributes(DomElement $field) {

        if ($field->hasAttributes()) {
            $elementName = $field->getAttribute('name');
            $required = $field->getAttribute('required');
            $formElement = $field->getAttribute('formelement');
            $datatype = $field->getAttribute('datatype');
            $multiplicity = $field->getAttribute('multiplicity');

            if ($datatype === 'Enrichment')
                $elementName = 'Enrichment' . $elementName;
            
            $this->currentElement->setElementName($elementName);
            if ($required === 'yes')
                $this->currentElement->setRequired(true);
            else
                $this->currentElement->setRequired(false);

            $this->currentElement->setFormElement($formElement);
            $this->currentElement->setDatatype($datatype);
            $this->currentElement->setMultiplicity($multiplicity);
        }
        // No Attributes found!
        else
            return false;
    }

    /**
     * Allocates member variables of currentElement and its children.
     * Parses for child nodes and there atrributes.
     * @param DomElement $field
     * @return false: no child nodes or no attributes have been found.
     */
    private function _parseSubFields(DomElement $field) {

        if ($field->hasChildNodes()) {

            foreach ($field->getElementsByTagname('subfield') as $subField) {
                //subfields have also type FormElement
                $currentSubField = new Publish_Model_FormElement($this->form);

                if ($subField->hasAttributes()) {

                    $subElementName = $subField->getAttribute('name');
                    $subRequired = $subField->getAttribute('required');
                    $subFormElement = $subField->getAttribute('formelement');
                    $subDatatype = $subField->getAttribute('datatype');

                    $currentSubField->setElementName($this->currentElement->getElementName() . $subElementName);
                    if ($subRequired === 'yes')
                        $currentSubField->setRequired(true);
                    else
                        $currentSubField->setRequired(false);
                    $currentSubField->setFormElement($subFormElement);
                    $currentSubField->setDatatype($subDatatype);

                    $currentSubField->isSubField = true;
                }
                else
                //No Attributes found!
                    return false;

                if ($subField->hasChildNodes()) {
                    $this->_parseDefaultEntry($subField, $currentSubField);
                }
                $this->currentElement->addSubFormElement($currentSubField->transform());
            }
        }
        //No Subfields found!
        else
            return false;
    }

    /**
     * Allocates member variables of currentElement or can be used for subfields.
     * Parses for default values and the possibility of edit and make it public.
     * @param DOMElement $field
     * @param Publish_Model_FormElement $subfield
     * @return false if there are no child nodes
     */
    private function _parseDefaultEntry(DOMElement $field, Publish_Model_FormElement $subfield=null) {
        if ($field->hasChildNodes()) {
            foreach ($field->getElementsByTagname('default') as $default) {

                if ($default->hasAttributes()) {
                    $defaultArray = array();
                    $value = $default->getAttribute('value');
                    $defaultArray['value'] = $value;

                    $edit = $default->getAttribute('edit');
                    if (isset($edit))
                        $defaultArray['edit'] = $edit;

                    $public = $default->getAttribute('public');
                    if (isset($public))
                        $defaultArray['public'] = $public;

                    if (!isset($subfield)) {
                        $this->currentElement->setDefaultValue($defaultArray);
                        $this->log->debug("Parser -> parseDefault(): " . $value);
                    }
                    else {
                        $subfield->setDefaultValue($defaultArray);
                    }
                }
                else
                    return false;
            }
        }
    }

    /**
     * Allocates member variables currentElement.
     * Parses for specific child node "required-if-fulltext" and sets the value "required" to true in case a fulltext has been uploaded.
     * @param DomElement $field
     */
    private function _parseRequiredIfFulltext(DomElement $field) {
        if ($field->hasChildNodes()) {
            foreach ($field->getElementsByTagname('required-if-fulltext') as $fulltext) {
                if ($this->session->fulltext === '1') {
                    $this->currentElement->setRequired(true);
                    $this->log->debug("currentElement : " . $this->currentElement->getElementName() . " and its required has been set to true!");
                }
                else
                    $this->log->debug("currentElement : " . $this->currentElement->getElementName() . " and its required hasn't been changed!");
            }
        }
    }

}

?>
