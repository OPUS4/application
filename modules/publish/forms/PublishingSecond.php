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

    public function __construct($postData=null) {
        $this->postData = $postData;
        $this->log = Zend_Registry::get('Zend_Log');
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

        if ($valid1 && $valid2 && $valid3) {
            //$this->session->invalidForm = '0';
            return true;
        } else {
            //$this->session->invalidForm = '1';
            return false;
        }
    }

    /**
     * Build document publishing form that depends on the doc type
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
            $dom = Zend_Controller_Action_HelperBroker::getStaticHelper('DocumentTypes')->getDocument($this->doctype);
        } catch (Application_Exception $e) {
            $this->log->err("Unable to load document type '" . $this->doctype . "'");
            // TODO: Need another exception class?
            throw new Publish_Model_FormSessionTimeoutException();
        }
        $parser = new Publish_Model_DocumenttypeParser($dom, $this);
        $this->log->debug("Parser created");
        $parser->setAdditionalFields($this->additionalFields);
        $parser->setPostValues($this->postData);

        if ($parser !== false)
            $parser->parse();

        $this->log->debug("Parsing ready");
        $this->addElements($parser->getFormElements());
        
        $this->addSubmitButton('button_label_send', 'send');
        $this->addSubmitButton('button_label_back', 'back');

        if (isset($this->postData))
            $this->populate($this->postData);

        $this->setViewValues();
    }

    public function prepareCheck() {
        $this->session->elements = array();

        //iterate over form elements
        foreach ($this->getElements() as $element) {
            $name = $element->getName();
            $element->removeDecorator('Label');

            if ($element->getValue() == ""
                    || $element->getType() == "Zend_Form_Element_Submit"
                    || $element->getType() == "Zend_Form_Element_Hidden") {

                $this->removeElement($name);
            } else {
                $this->session->elements[$name]['name'] = $name;
                $this->session->elements[$name]['value'] = $element->getValue();
                $this->session->elements[$name]['label'] = $element->getLabel();
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
            
            switch ($workflow) {
                case 'add':                    
                    $currentNumber = (int) $currentNumber + 1;
                    break;
                case 'delete':
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
                    if ($postData[$fieldName . $currentNumber] !== '' || $this->session->additionalFields['collId1' . $fieldName . $currentNumber] !== '')
                        $level = (int) $level + 1;
                    break;
                case 'up' :
                    if ($level >= 2)
                        $level = (int) $level - 1;
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
