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

/**
 * Shows a publishing form for new documents
 *
 */
class Publish_Form_PublishingSecond extends Publish_Form_PublishingAbstract {
    CONST FIRST = "Firstname";
    CONST COUNTER = "1";
    CONST GROUP = "group";
    CONST EXPERT = "X";
    CONST LABEL = "_label";
    CONST ERROR = "Error";

    public $doctype = "";
    public $additionalFields = array();
    public $postData = array();
    public $log;
    public $view;

    public function __construct($log, $postData = null) {
        $this->postData = $postData;
        $this->log = $log;
        $this->view = $this->getView();

        parent::__construct();
    }

    /**
     * Overwritten method isValid to support extended validation
     * @param <type> $data 
     */
    public function isValid($data) {
        $extended = new Publish_Model_ExtendedValidation($this, $data);
        $valid1 = $extended->validate();

        $valid2 = parent::isValid($extended->data);

        $valid3 = $extended->validate();
        //inherit data changes during validation
        $this->populate($extended->data);
        $this->postData = $extended->data;

        return ($valid1 && $valid2 && $valid3);
    }

    /**
     * Build document publishing form whose fields depend on the choosen documenttype.
     * 
     * @param $doctype
     * @return void
     */
    public function init() {
        parent::init();

        $this->doctype = $this->session->documentType;
        $this->additionalFields = $this->session->additionalFields;

        if (!isset($this->doctype) or empty($this->doctype)) {
            throw new Publish_Model_FormSessionTimeoutException();
        }

        $dom = null;
        try {
            // Fetch the current XML DOM structure of the documenttype.
            $dom = Zend_Controller_Action_HelperBroker::getStaticHelper('DocumentTypes')->getDocument($this->doctype);
        } catch (Application_Exception $e) {
            $this->log->err("Unable to load document type '" . $this->doctype . "'");
            // TODO: Need another exception class?
            throw new Publish_Model_FormSessionTimeoutException();
        }
                
        // Call the parser for that DOM object and the current form object and set important members.
        $parser = new Publish_Model_DocumenttypeParser($dom, $this);
        $parser->setAdditionalFields($this->additionalFields);
        $parser->setPostValues($this->postData);
        $parser->parse();
        $parserElements = $parser->getFormElements();
        
        $this->log->info("Documenttype Parser ready with parsing " . $this->doctype . " found: " . count($parserElements) . " elements." );
        
        // Fill the Form Object!
        $this->addElements($parserElements);
        if(!is_null($this->getExternalElements()))
            $this->addElements($this->getExternalElements());
        
        $this->addSubmitButton('button_label_send', 'send');
        $this->addSubmitButton('button_label_back', 'back');

        if (!is_null($this->postData)) $this->populate($this->postData);

        $this->setViewValues();
    }

    /**
     * Checks if there are external fields that belongs to the form and are not defined 
     * by document type (e.g. "LegalNotices" be the View_Helper).
     * It sets important array values for these elements and returns an array of external fields.
     * @return type Array of external fields.
     */
    private function getExternalElements(){
        $externals = array();
        $session = new Zend_Session_Namespace('Publish');
        $externalFields = $session->DT_externals;
        
        // No external values found!
        if (is_null($externalFields))
            return;
        
        foreach ($externalFields AS $element) {
            // Element is already appended.
            if (!is_null($this->getElement($element['id'])))
                    return null;
            // ELSE: Create a new element and keep the element's values in an array.
            $externalElement = $this->createElement($element['createType'], $element['id']);
            $req = ($element['req']=='required') ? true : false;            
            $externalElement->setRequired($req)
                            ->setValue($element['value'])
                            ->setLabel($element['label'])
                            ->setAttrib('disabled' , $element['disabled'])
                            ->setAttrib('DT_external' , $element['DT_external'])
                            ->addErrorMessages($element['error']);
            $externals[] = $externalElement;
            $this->postData[$element['id']] = $element['value'];
            
        }
        return $externals;
    }
    
    /**
     * Prepares the form object for check view page and data storing in database.
     * It removes submit buttons, hidden fields, root nodes of browsing fields and #
     * external fields (e.g. in view helpers) from the form object.
     * Other elements are left untouched.
     * It adds two new buttons for "Back" and "Deposit Data".
     */
    public function prepareCheck() {
        $this->session->elements = array();

        //iterate over form elements
        foreach ($this->getElements() as $element) {
            $name = $element->getName();
            $element->removeDecorator('Label');

            if ($element->getValue() == "" 
                    || $element->getType() == "Zend_Form_Element_Submit"        // Submit buttons
                    || $element->getType() == "Zend_Form_Element_Hidden"        // Hidden fields
                    || $element->getAttrib('isRoot') == true                    // Rood Nodes of Browsefields
                    || (!is_null($this->session->DT_externals)) && array_key_exists($element->getName(), $this->session->DT_externals)) {   // additional externals fields (from view helpers)
                
                $this->removeElement($name);
                
            } else {
                // set important element values in an  array: name, value, label, datatype and subfield
                // these are used for Deposit
                $this->session->elements[$name]['name'] = $name;
                $this->session->elements[$name]['value'] = $element->getValue();
                $this->session->elements[$name]['label'] = $element->getLabel();
                $this->session->elements[$name]['datatype'] = $element->getAttrib('datatype');
                if ($element->getAttrib('subfield'))
                    $this->session->elements[$name]['subfield'] = '1';
                else 
                    $this->session->elements[$name]['subfield'] = '0';
            }
        }

        $this->addSubmitButton('button_label_back', 'back');
        $this->addSubmitButton('button_label_send2', 'send');
    }

    public function getExtendedForm($postData, $reload) {
        $this->view->currentAnchor = "";        
        if ($reload === true) {

            //find out which button was pressed            
            $pressedButtonName = $this->_getPressedButton();

            //find out the resulting workflow and the field to extend
            $result = $this->_workflowAndFieldFor($pressedButtonName);
            $fieldName = $result[0];
            $workflow = $result[1];

            if (!is_null($this->session->additionalFields[$fieldName]))
                $currentNumber = $this->session->additionalFields[$fieldName];
            else
                $currentNumber = 1;

            // update collection fields in session member addtionalFields and find out the current level of collection browsing
            $level = $this->_updateCollectionField($fieldName, $currentNumber, $postData);            

            $saveName = "";
            //Enrichment-Gruppen haben Enrichment im Namen, die aber mit den currentAnchor kollidieren            
            if (strstr($fieldName, 'Enrichment')) {
                $saveName = $fieldName;
                $fieldName = str_replace('Enrichment', '', $fieldName);
            }
            if ($saveName != "")
                $fieldName = $saveName;

            $this->view->currentAnchor = 'group' . $fieldName;            
            
            $fieldsetCount = $currentNumber;
            
            // Updates several counter in additionalFields that depends on the button label.
            switch ($workflow) {
                
                case 'add':                    
                    // Add another form field.
                    $currentNumber = (int) $currentNumber + 1;
                    break;
                
                case 'delete':
                    // Delete the last field.
                    if ($currentNumber > 1) {
                        if (isset($level)) {
                            for ($i = 0; $i <= $level; $i++)
                                $this->session->additionalFields['collId' . $i . $fieldName . $currentNumber] = "";
                        }
                        //remove one more field, only down to 0
                        $currentNumber = (int) $currentNumber - 1;
                    }
                    break;
                    
                case 'down':
                    // Browse down in the Collection hierarchy.
                    if ($postData[$fieldName . $currentNumber] !== '' || 
                        array_key_exists('collId' . $level . $fieldName . $currentNumber, $this->session->additionalFields) &&
                                $this->session->additionalFields['collId' . $level . $fieldName . $currentNumber] !== '')
                        $level = (int) $level + 1;
                    break;
                    
                case 'up' :
                    // Brose up in the Collection hierarchy.
                    if ($level >= 2) {
                        unset($this->session->additionalFields['collId' . $level . $fieldName . $currentNumber]);
                        $level = (int) $level - 1;                             
                    }                                        
                    if ($level == 1)
                        unset($this->session->additionalFields['collId1'. $fieldName . $currentNumber]);
                    // unset root node in disabled array
                    if (array_key_exists($fieldName . $currentNumber, $this->session->disabled))
                            unset($this->session->disabled[$fieldName . $currentNumber]);
                    break;
                default:
                    break;
            }

            //set the increased value for the pressed button
            $this->session->additionalFields[$fieldName] = $currentNumber;
            if (isset($level)) {
                $this->session->additionalFields['step' . $fieldName . $fieldsetCount] = $level;
            }
        }
    }

    /**
     * Finds out which button for which field was pressed.
     * @param type $button String button label
     * @return string array with fieldname and workflow
     */
    private function _workflowAndFieldFor($button) {
        $result = array();
        if (substr($button, 0, 7) == "addMore") {
            $result[0] = substr($button, 7);
            $result[1] = 'add';
        } else if (substr($button, 0, 10) == "deleteMore") {
            $result[0] = substr($button, 10);
            $result[1] = 'delete';
        } else if (substr($button, 0, 10) == "browseDown") {
            $result[0] = substr($button, 10);
            $result[1] = 'down';
        } else if (substr($button, 0, 8) == "browseUp") {
            $result[0] = substr($button, 8);
            $result[1] = 'up';
        }
        return $result;
    }

    /**
     * Finds the current level of collection browsing for a given field.
     * @param type $field name of field
     * @param type $value counter of fieldsets
     * @param type $post Array of post data
     * @return type current level
     */
    private function _updateCollectionField($field, $value, $post) {
        $level = '1';
        if (array_key_exists('step' . $field . $value, $this->session->additionalFields)) {

            $level = $this->session->additionalFields['step' . $field . $value];
            // Root Node 
            if ($level == '1') {
                if (isset($post[$field . $value])) {
                    if ($post[$field . $value] !== '')
                        $this->session->additionalFields['collId1' . $field . $value] = substr($post[$field . $value], 3);
                    }
                }
            // Middle Node or Leaf
            else {
                if (isset($post['collId' . $level . $field . $value])) {
                    $entry = substr($post['collId' . $level . $field . $value], 3);
                    $this->session->additionalFields['collId' . $level . $field . $value] = $entry;
                }
            }
        }
        return $level;
    }

    /**
     * Set values of view variables.
     */
    public function setViewValues() {
        $errors = $this->getMessages();

        //group fields and single fields for view placeholders
        foreach ($this->getElements() AS $currentElement => $value) {
            //element names have to loose special strings for finding groups
            $name = $this->_getRawElementName($currentElement);

            if (strstr($name, 'Enrichment')) {
                $name = str_replace('Enrichment', '', $name);
            }

            //build group name
            $groupName = self::GROUP . $name;
            $this->view->$name = $this->view->translate($name);
            $groupCount = 'num' . $groupName;

            //get the display group for the current element and build the complete group
            $displayGroup = $this->getDisplayGroup($groupName);
            if (!is_null($displayGroup)) {
                $group = $this->buildViewDisplayGroup($displayGroup);
                $group['Name'] = $groupName;
                $group['Counter'] = $this->session->$groupCount;                
                $this->view->$groupName = $group;
            }

            //single field name (for calling with helper class)
            $elementAttributes = $this->getElementAttributes($currentElement); //array

            if (strstr($currentElement, 'Enrichment')) {
                $name = str_replace('Enrichment', '', $currentElement);
                $this->view->$name = $elementAttributes;
            } else {
                $this->view->$currentElement = $elementAttributes;
            }

            $label = $currentElement . self::LABEL;
            $this->view->$label = $this->view->translate($this->getElement($currentElement)->getLabel());
        }
    }

    /**
     * Method to find out the element name stemming.
     * @param <String> $element element name
     * @return <String> $name
     */
    private function _getRawElementName($element) {
        $name = "";
        //element is a person element
        $pos = stripos($element, self::FIRST);
        if ($pos !== false) {
            $name = substr($element, 0, $pos);
        } else {
            //element belongs to a group
            $pos = stripos($element, self::COUNTER);
            if ($pos != false) {
                $name = substr($element, 0, $pos);
            } else {
                //"normal" element name without changes
                $name = $element;
            }
        }
        return $name;
    }

    /**
     * Method to check which button in the form was pressed 
     * @return <String> name of button
     */
    private function _getPressedButton() {
        $pressedButtonName = "";
        foreach ($this->getElements() AS $element) {
            $name = $element->getName();
            if (strstr($name, 'addMore') || strstr($name, 'deleteMore') || strstr($name, 'browseDown') || strstr($name, 'browseUp')) {
                $value = $element->getValue();
                if (!is_null($value))
                    $pressedButtonName = $name;
            }
        }

        if ($pressedButtonName == "")
            throw new Publish_Model_FormNoButtonFoundException();
        else
            return $pressedButtonName;
    }
    
}
