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

    public $doctype = "";      
    public $additionalFields = array();
    public $postData = array();
    public $log;    
    public $msc = array();
    public $session;
    public $helper;
    public $view;

    public function __construct($view, $postData=null, $options=null) {
        $this->session = new Zend_Session_Namespace('Publish');
        $this->log = Zend_Registry::get('Zend_Log');
        $this->doctype = $this->session->documentType;               
        $this->additionalFields = $this->session->additionalFields;
        $this->postData = $postData;
        
        $this->view = $view;
        if (is_null($this->view))
                throw new Publish_Model_NoViewFoundException();
        
        $this->helper = new Publish_Model_FormHelper($this->view, $this);

        parent::__construct($options);
        $this->setSecondFormViewVariables($this);
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
            $this->session->invalidForm = '0';
            return true;
        }
        else {
            $this->session->invalidForm = '1';
            return false;
        }
    }

    /**
     * Build document publishing form that depends on the doc type
     * @param $doctype
     * @return void
     */
    public function init() {
        
        if (!isset($this->doctype) or empty($this->doctype)) {
            throw new Publish_Model_FormSessionTimeoutException();
        }

        $dom = null;
        try {
            $dom = Zend_Controller_Action_HelperBroker::getStaticHelper('DocumentTypes')->getDocument($this->doctype);
        }
        catch (Application_Exception $e) {
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
        $this->_addSubmit('button_label_send', 'send');
        $this->_addSubmit('button_label_back', 'back');

        if (isset($this->postData))
            $this->populate($this->postData);
    }

    /**
     * Adds submit button to the form.
     * @param <type> $label
     */
    public function _addSubmit($label, $name) {
        //Submit button
        $submit = $this->createElement('submit', $name);
        $submit->setLabel($label);
        $this->addElement($submit);
    }

    /**
     *
     * @param <type> $elementName
     * @return string
     */
    public function getElementAttributes($elementName) {
        $elementAttributes = $this->helper->getElementAttributes($elementName);
        return $elementAttributes;
    }

    public function showTemplate() {
        $this->view->subtitle = $this->view->translate($this->session->documentType);        
        $this->view->doctype = $this->session->documentType;                
        $action_url = $this->view->url(array('controller' => 'form', 'action' => 'check')) . '#current';
        $this->setAction($action_url);
        $this->setMethod('post');        
        $this->view->action_url = $action_url;
        $this->view->form = $this;
    }

    public function showCheckpage() {
        $this->view->subtitle = $this->view->translate('publish_controller_check2');
        $this->view->header = $this->view->translate('publish_controller_changes');
        $action_url = $this->view->url(array('controller' => 'deposit', 'action' => 'deposit'));
        $this->setAction($action_url);
        $this->setMethod('post');
        $this->prepareCheck();
        $this->view->action_url = $action_url;
        $this->view->form = $this;
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
            }
            else {
                $this->session->elements[$name]['name'] = $name;
                $this->session->elements[$name]['value'] = $element->getValue();
                $this->session->elements[$name]['label'] = $element->getLabel();
            }
        }

        $this->_addSubmit('button_label_back', 'back');
        $this->_addSubmit('button_label_collection', 'collection');
        $this->_addSubmit('button_label_send2', 'send');
    }

    public function getExtendedForm($postData, $reload) {
        return $this->helper->getExtendedForm($postData, $reload);
    }

    public function setSecondFormViewVariables() {
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

            //get the display group for the current element and build the complete group
            $displayGroup = $this->getDisplayGroup($groupName);
            if (!is_null($displayGroup)) {
                $group = $this->_buildViewDisplayGroup($displayGroup, $this);
                $group["Name"] = $groupName;
                $this->view->$groupName = $group;                
            }

            //single field name (for calling with helper class)
            $elementAttributes = $this->getElementAttributes($currentElement); //array

            if (strstr($currentElement, 'Enrichment')) {
                $name = str_replace('Enrichment', '', $currentElement);
                $this->view->$name = $elementAttributes;                
            }
            else {
                $this->view->$currentElement = $elementAttributes;                
            }

            $label = $currentElement . self::LABEL;
            $this->view->$label = $this->view->translate($this->getElement($currentElement)->getLabel());

            //EXPERT VIEW:
            //also support more difficult templates for "expert admins"
            $expertField = $currentElement . self::EXPERT;
            $this->view->$expertField = $this->getElement($currentElement)->getValue();
            //error values for expert fields view
            if (isset($errors[$currentElement])) {
                foreach ($errors[$currentElement] as $error => $errorMessage) {
                    $errorElement = $expertField . self::ERROR;
                    $this->view->$errorElement = $errorMessage;
                }
            }
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
        }
        else {
            //element belongs to a group
            $pos = stripos($element, self::COUNTER);
            if ($pos != false) {
                $name = substr($element, 0, $pos);
            }
            else {
                //"normal" element name without changes
                $name = $element;
            }
        }
        return $name;
    }

    /**
     * Method to build a disply group by a number of arrays for fields, hidden fields and buttons.
     * @param <Zend_Form_DisplayGroup> $displayGroup
     * @param <Publishing_Second> $form
     * @return <Array> $group
     */
    private function _buildViewDisplayGroup($displayGroup, $form) {
        $groupFields = array(); //Fields
        $groupHiddens = array(); //Hidden fields for adding and deleting fields
        $groupButtons = array(); //Buttons

        foreach ($displayGroup->getElements() AS $groupElement) {

            $elementAttributes = $form->getElementAttributes($groupElement->getName()); //array
            if ($groupElement->getType() === 'Zend_Form_Element_Submit') {
                //buttons
                $groupButtons[$elementAttributes["id"]] = $elementAttributes;
            }
            else if ($groupElement->getType() === 'Zend_Form_Element_Hidden') {
                //hidden fields
                $groupHiddens[$elementAttributes["id"]] = $elementAttributes;
            }
            else {
                //normal fields
                $groupFields[$elementAttributes["id"]] = $elementAttributes;
            }
        }
        $group[] = array();
        $group["Fields"] = $groupFields;
        $group["Hiddens"] = $groupHiddens;
        $group["Buttons"] = $groupButtons;

        return $group;
    }

}
