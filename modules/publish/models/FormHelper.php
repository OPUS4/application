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
   
    public $session;
    public $form;
    public $view;

    public function __construct($view = null, $form = null) {        
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
     *
     * @param <type> $elementName
     * @return string
     */
    public function getElementAttributes($elementName) {
        $elementAttributes = array();
        if (!is_null($this->form->getElement($elementName))) {
            $element = $this->form->getElement($elementName);
            $elementAttributes['value'] = $element->getValue();
            $elementAttributes['label'] = $element->getLabel();
            $elementAttributes['error'] = $element->getMessages();
            $elementAttributes['id'] = $element->getId();
            $elementAttributes['type'] = $element->getType();
            $elementAttributes['desc'] = $element->getDescription();
            $elementAttributes['hint'] = 'hint_' . $elementName;
            $elementAttributes['header'] = 'header_' . $elementName;
            $elementAttributes['disabled'] = $element->getAttrib('disabled');

            if ($element->getType() === 'Zend_Form_Element_Checkbox') {
                $elementAttributes['value'] = $element->getCheckedValue();
                if ($element->isChecked())
                    $elementAttributes['check'] = 'checked';
                else
                    $elementAttributes['check'] = '';
            }

            if ($element->getType() === 'Zend_Form_Element_Select') {
                $elementAttributes["options"] = $element->getMultiOptions(); //array
            }

            if ($element->isRequired())
                $elementAttributes["req"] = "required";
            else
                $elementAttributes["req"] = "optional";
        }

        return $elementAttributes;
    }

    /**
     * Method finds out which fields has to be added or deleted in the current form (depends on the clicked button)
     * @param <Array> $postData
     * @param <Boolean> $reload
     * @return <View>
     */
    public function getExtendedForm($postData=null, $reload=true) {
        $this->view->currentAnchor = "";
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

            if (!is_null($this->session->additionalFields[$fieldName]))
                    $currentNumber = $this->session->additionalFields[$fieldName];
            else 
                $currentNumber = 1;

            //collection
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

            $this->view->currentAnchor = 'group' . $fieldName;
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
                    if (substr($postData[$fieldName . $currentNumber], 3) !== 'EMPTY' || $this->session->additionalFields['collId1' . $fieldName . $currentNumber] !== 'EMPTY')
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
        }

        $form2 = null;
        try {
            $form2 = new Publish_Form_PublishingSecond($this->view, $postData);
        } catch (Publish_Model_FormSessionTimeoutException $e) {
            // Session timed out.
            return $this->_redirectTo('index', '', 'index');
        }
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
            throw new Publish_Model_FormNoButtonFoundException();
        else
            return $pressedButtonName;
    }

}

?>
