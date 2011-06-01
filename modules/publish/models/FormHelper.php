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
 * @copyright   Copyright (c) 2008-2011, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Description of Publish_Model_FormHelper
 *
 * @author Susanne Gottwald
 */
class Publish_Model_FormHelper {
    CONST FIRST = "Firstname";
    CONST COUNTER = "1";
    CONST GROUP = "group";
    CONST EXPERT = "X";
    CONST LABEL = "_label";
    CONST ERROR = "Error";

    public $log;
    public $session;
    public $form;
    public $view;

    public function __construct($view = null, $form = null) {
        $this->log = Zend_Registry::get('Zend_Log');
        $this->session = new Zend_Session_Namespace('Publish');

        if (!is_null($view)) {
            $this->setCurrentView($view);
        }

        if (!is_null($form)) {
            $this->setCurrentForm($form);
        }
    }

    public function setCurrentForm($form) {
        $this->form = $form;
    }

    public function setCurrentView($view) {
        $this->view = $view;
    }

    /**
     * Renders the data check page in case that all given form values are valid.
     * @param <type> $this->form 
     */
    public function showCheckPage() {
        $this->log->debug("Variables are valid!");
        $this->view->title = $this->view->translate('publish_controller_index');
        $this->view->subtitle = $this->view->translate('publish_controller_check2');
        $this->view->header = $this->view->translate('publish_controller_changes');

        $depositForm = new Publish_Form_PublishingSecond($this->form->getValues());
        $action_url = $this->view->url(array('controller' => 'deposit', 'action' => 'deposit'));
        $depositForm->setAction($action_url);
        $depositForm->setMethod('post');
        $depositForm->populate($this->form->getValues());
        $depositForm->prepareCheck();
        $this->view->action_url = $action_url;
        $this->view->form = $depositForm;
    }

    /**
     * Renders the documenttype specific template
     * @param <type> $helper
     */
    public function showTemplate($helper) {
        $templateName = $helper->documentTypes->getTemplateName($this->session->documentType);
        $helper->viewRenderer($templateName);
        $this->view->subtitle = $this->view->translate($this->session->documentType);
        $this->view->requiredHint = $this->view->translate('publish_controller_required_hint');
        $this->view->doctype = $this->session->documentType;

        $publishForm = new Publish_Form_PublishingSecond(null);
        $action_url = $this->view->url(array('controller' => 'form', 'action' => 'check')) . '#current';
        $publishForm->setAction($action_url);
        $publishForm->setMethod('post');
        $this->setSecondFormViewVariables($publishForm);
        $this->view->action_url = $action_url;
        $this->view->form = $publishForm;
    }

    /**
     * Method sets the different variables and arrays for the view and the templates in the first form
     * @param <Zend_Form> $form
     */
    public function setFirstFormViewVariables() {
        $errors = $this->form->getMessages();

        //first form single fields for view placeholders
        foreach ($this->form->getElements() AS $currentElement => $value) {
            //single field name (for calling with helper class)
            $elementAttributes = $this->form->getElementAttributes($currentElement); //array
            $this->view->$currentElement = $elementAttributes;
        }

        //Upload-Field and its number of fields (for fieldset)
        $displayGroup = $this->form->getDisplayGroup('documentUpload');
        $this->session->numdocumentUpload = 2;

        $groupName = $displayGroup->getName();
        $groupFields = array(); //Fields
        $groupHiddens = array(); //Hidden fields for adding and deleting fields
        $groupButtons = array(); //Buttons

        foreach ($displayGroup->getElements() AS $groupElement) {

            $elementAttributes = $this->form->getElementAttributes($groupElement->getName()); //array
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
        $group = array();
        $group["Fields"] = $groupFields;
        $group["Hiddens"] = $groupHiddens;
        $group["Buttons"] = $groupButtons;
        $group["Name"] = $groupName;
        $this->view->$groupName = $group;
        $this->view->MAX_FILE_SIZE = $this->session->maxFileSize;
    }

    /**
     * Method to set the different variables and arrays for the view and the templates of the second form
     * @param <Zend_Form> $form
     */
    public function setSecondFormViewVariables($form = null) {
        if (is_null($form)) {
            $form = $this->form;
        }

        $this->session->elementCount = 0;
        $errors = $form->getMessages();

        //group fields and single fields for view placeholders
        foreach ($form->getElements() AS $currentElement => $value) {
            //element names have to loose special strings for finding groups
            $name = $this->_getRawElementName($currentElement);

            if (strstr($name, 'Enrichment')) {
                $name = str_replace('Enrichment', '', $name);
            }

            //build group name
            $groupName = self::GROUP . $name;
            $this->view->$name = $this->view->translate($name);

            //get the display group for the current element and build the complete group
            $displayGroup = $form->getDisplayGroup($groupName);
            if (!is_null($displayGroup)) {
                $group = $this->_buildViewDisplayGroup($displayGroup, $form);
                $group["Name"] = $groupName;
                $this->view->$groupName = $group;
                $this->viewElementsCount++;
            }

            //single field name (for calling with helper class)
            $elementAttributes = $form->getElementAttributes($currentElement); //array

            if (strstr($currentElement, 'Enrichment')) {
                $name = str_replace('Enrichment', '', $currentElement);
                $this->view->$name = $elementAttributes;
                $this->viewElementsCount++;
            }
            else {
                $this->view->$currentElement = $elementAttributes;
                $this->viewElementsCount++;
            }

            $label = $currentElement . self::LABEL;
            $this->view->$label = $this->view->translate($form->getElement($currentElement)->getLabel());

            //EXPERT VIEW:
            //also support more difficult templates for "expert admins"
            $expertField = $currentElement . self::EXPERT;
            $this->view->$expertField = $form->getElement($currentElement)->getValue();
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

    /**
     * Method finds out which fields has to be added or deleted in the current form (depends on the clicked button)
     * @param <Array> $postData
     * @param <Boolean> $reload
     * @return <View>
     */
    public function getExtendedForm($postData=null, $reload=true) {
        $this->session->currentAnchor = "";
        if ($reload === true) {
            //find out which button was pressed
            $pressedButtonName = $this->_getPressedButton();
            if (substr($pressedButtonName, 0, 7) == "addMore") {
                $fieldName = substr($pressedButtonName, 7);
                $workflow = 'add';
            }
            else if (substr($pressedButtonName, 0, 10) == "deleteMore") {
                $fieldName = substr($pressedButtonName, 10);
                $workflow = 'delete';
            }
            else if (substr($pressedButtonName, 0, 10) == "browseDown") {
                $fieldName = substr($pressedButtonName, 10);
                $workflow = 'down';
            }
            else if (substr($pressedButtonName, 0, 8) == "browseUp") {
                $fieldName = substr($pressedButtonName, 8);
                $workflow = 'up';
            }

            $currentNumber = $this->session->additionalFields[$fieldName];
            if (array_key_exists('step' . $fieldName . $currentNumber, $this->session->additionalFields)) {
                $currentCollectionLevel = $this->session->additionalFields['step' . $fieldName . $currentNumber];
                if ($currentCollectionLevel == '1') {
                    if (isset($postData[$fieldName . $currentNumber])) {
                        if (substr($postData[$fieldName . $currentNumber], 3) !== 'EMPTY') 
                                $this->session->additionalFields['collId1' . $fieldName . $currentNumber] = substr($postData[$fieldName . $currentNumber], 3);
                    }
                }
                else {
                    if (isset($postData['collId' . $currentCollectionLevel . $fieldName . $currentNumber])) {
                        $entry = substr($postData['collId' . $currentCollectionLevel . $fieldName . $currentNumber], 3);
                        $this->session->additionalFields['collId' . $currentCollectionLevel . $fieldName . $currentNumber] = $entry;
                    }
                }
            }

            $saveName = "";
            //Enrichment-Gruppen haben Enrichment im Namen, die aber mit den currentAnchor kollidieren            
            if (strstr($fieldName, 'Enrichment')) {
                $saveName = $fieldName;
                $fieldName = str_replace('Enrichment', '', $fieldName);
            }

            $this->session->currentAnchor = 'group' . $fieldName;
            //erst Enrichment entfernen und dann unverändert weiter geben
            //todo: schönere Lösung als diese blöden String-Sachen!!!
            if ($saveName != "")
                $fieldName = $saveName;
            $fieldsetCount = $currentNumber;

            switch ($workflow) {
                case 'add':
                    //show one more fields
                    $currentNumber = (int) $currentNumber + 1;
                    break;
                case 'delete':
                    if ($currentNumber > 1) {
                        if (isset($currentCollectionLevel)) {
                            for ($i = 0; $i <= $currentCollectionLevel; $i++)
                                $this->session->additionalFields['collId' . $i . $fieldName . $currentNumber] = "";
                        }
                        //remove one more field, only down to 0
                        $currentNumber = (int) $currentNumber - 1;
                    }
                    break;
                case 'down':
                    if (substr($postData[$fieldName . $currentNumber], 3) !== 'EMPTY')
                            $currentCollectionLevel = (int) $currentCollectionLevel + 1;
                    break;
                case 'up' :
                    if ($currentCollectionLevel >= 2)
                        $currentCollectionLevel = (int) $currentCollectionLevel - 1;
                    break;
                default:
                    break;
            }

            //set the increased value for the pressed button and create a new form
            $this->session->additionalFields[$fieldName] = $currentNumber;
            if (isset($currentCollectionLevel)) {
                $this->session->additionalFields['step' . $fieldName . $fieldsetCount] = $currentCollectionLevel;
            }
            //var_dump($this->session->additionalFields);
        }

        $form2 = new Publish_Form_PublishingSecond($postData);
        $action_url = $this->view->url(array('controller' => 'form', 'action' => 'check')) . '#current';
        $form2->setAction($action_url);
        $this->view->action_url = $action_url;
        $this->setSecondFormViewVariables($form2);
        $this->view->form = $form2;
    }

    /**
     * Method to check which button in the form was pressed 
     * @return <String> name of button
     */
    private function _getPressedButton() {
        $pressedButtonName = "";
        foreach ($this->form->getElements() AS $element) {
            $name = $element->getName();
            if (strstr($name, 'addMore') || strstr($name, 'deleteMore') || strstr($name, 'browseDown') || strstr($name, 'browseUp')) {                
                $value = $element->getValue();               
                if (!is_null($value))
                    $pressedButtonName = $name;
            }
        }

        if ($pressedButtonName == "")
            throw new Publish_Model_OpusServerException("No pressed button found! Possibly the values of the buttons are not equal in the view and Publish class.");
        else
            return $pressedButtonName;
    }

}

?>
