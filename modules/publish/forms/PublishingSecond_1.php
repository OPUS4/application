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
 */

/**
 * Shows a publishing form for new documents
 *
 * @category    Application
 * @package     Module_Publish
 * */
class Publish_Form_PublishingSecond extends Zend_Form {

    public $doctype = "";
    public $docid = "";
    public $fulltext = "";
    public $additionalFields = array();
    public $postData = array();
    public $log;
    public $institutes = array();
    public $projects = array();
    public $msc = array();
    public $licences = array();
    public $languages = array();

    public function __construct($type, $id, $fulltext, $additionalFields, $postData, $options=null) {
        $this->doctype = $type;
        $this->docid = $id;
        $this->fulltext = $fulltext;
        $this->additionalFields = $additionalFields;
        $this->postData = $postData;

        $log = Zend_Registry::get('Zend_Log');
        $this->log = $log;

        parent::__construct($options);
    }

    /**
     * Build document publishing form that depends on the doc type
     * @param $doctype
     * @return void
     */
    public function init() {
        $dom = Zend_Controller_Action_HelperBroker::getStaticHelper('DocumentTypes')->getDocument($this->doctype);

        $parser = new Publish_Model_DocumenttypeParser($this, $dom);
        $parser->setAdditionalFields($this->additionalFields);
        $parser->setPostValues($this->postData);
        if ($parser !== false)
            $parser->parse();

        $formElements = $parser->formElements;

        $this->addElements($formElements);

        //hidden field for fulltext to cummunicate between different forms
        $this->_addHiddenField('fullText', $this->fulltext);

        //hidden field with document type
        $this->_addHiddenField('documentType', $this->doctype);

        //hidden field with document id
        $this->_addHiddenField('documentId', $this->docid);

        $this->_addSubmit('button_label_send');
    }

    /**
     * Adds submit button to the form.
     * @param <type> $label
     */
    protected function _addSubmit($label) {
        //Submit button
        $submit = $this->createElement('submit', 'send');
        $submit->setLabel($label);
        $this->addElement($submit);
    }

    /**
     * Adds a hidden field to the form.
     * @param <type> $name
     * @param <type> $value
     * @return <type>
     */
    protected function _addHiddenField($name, $value) {
        $hidden = $this->createElement('hidden', $name);
        $hidden->setValue($value);
        $this->addElement($hidden);
        return $hidden;
    }

    
//    private function _addDisplayGroup($formElement, $elementName, $validator, $datatype, $required, $multiplicity) {
//        //array is used to initilaize the display group for this group of elements
//        $group = array();
//        $groupName = 'group' . $elementName;
//        //current element has also to be in that group
//        //prepare form element: create Zend_Form_element with needed attributes
//        $label = $elementName;
//        $group = $this->_prepareFormElement($formElement, $elementName . "1", $validator, $datatype, $required, $group, $label);
//
//        //additionalFields != null means additinal fields have to be shown
//        if ($this->additionalFields != null) {
//            //button and hidden element that carries the value of how often the element has to be shown
//            $countMoreHidden = $this->createElement('hidden', 'countMore' . $elementName);
//            $addMoreButton = $this->createElement('submit', 'addMore' . $elementName);
//            $addMoreButton->setLabel('button_label_add_one_more' . $elementName);
//
//            $deleteMoreButton = $this->createElement('submit', 'deleteMore' . $elementName);
//            $deleteMoreButton->setLabel('button_label_delete' . $elementName);
//
//            $currentNumber = 1;
//            if (array_key_exists($elementName, $this->additionalFields)) {
//                //$allowedNumbers is set in controller and given to the form by array as parameter
//                $currentNumber = $this->additionalFields[$elementName];
//                $countMoreHidden->setValue($currentNumber);
//                $this->addElement($countMoreHidden);
//                $group[] = $countMoreHidden->getName();
//
//                //$this->log->debug("CountMoreHidden for element " . $elementName . " is set to value " . $currentNumber);
//                if ($multiplicity == "*")
//                    $multiplicity = 99;
//                else
//                    $multiplicity = (int) $multiplicity;
//
//                //start counting at lowest possible number -> also used for name
//                for ($i = 1; $i < $currentNumber; $i++) {
//                    $counter = $i + 1;
//                    $group = $this->_prepareFormElement($formElement, $elementName . $counter, $validator, $datatype, $required, $group, $label);
//                }
//
//                if ($currentNumber == 1) {
//                    //only one field is shown -> nothing to delete
//                    $this->addElement($addMoreButton);
//                    $group[] = $addMoreButton->getName();
//                } else if ($currentNumber < $multiplicity) {
//                    //more than one field has to be shown -> delete buttons are needed
//                    $this->addElement($addMoreButton);
//                    $group[] = $addMoreButton->getName();
//                    $this->addElement($deleteMoreButton);
//                    $group[] = $deleteMoreButton->getName();
//                } else {
//                    //maximum fields are shown -> here only a delete button
//                    $this->addElement($deleteMoreButton);
//                    $group[] = $deleteMoreButton->getName();
//                }
//            }
//
//            //add a displaygroup to the form for grouping same elements
//            $displayGroup = $this->addDisplayGroup($group, $groupName);
//            //$this->log->debug("Added Displaygroup to form: " . $groupName);
//        } else {
//            //additionalFields == null means initial state -> field is shown one time and can be demanded
//            //button and hidden element that carries the value of how often the element has to be shown
//            $countMoreHidden = $this->createElement('hidden', 'countMore' . $elementName);
//            $countMoreHidden->setValue("1");
//            $this->addElement($countMoreHidden);
//            $group[] = $countMoreHidden->getName();
//
//            $addMoreButton = $this->createElement('submit', 'addMore' . $elementName);
//            $addMoreButton->setLabel('button_label_add_one_more' . $elementName);
//            $this->addElement($addMoreButton);
//            $group[] = $addMoreButton->getName();
//
//            $displayGroup = $this->addDisplayGroup($group, $groupName);
//            //$this->log->debug("Added Displaygroup to form: " . $groupName);
//        }
//    }

    /**
     *
     * @param <type> $elementName
     * @return string
     */
    public function getElementAttributes($elementName) {
        $elementAttributes = array();
        $element = $this->getElement($elementName);
        $elementAttributes['value'] = $element->getValue();
        $elementAttributes['label'] = $element->getLabel();
        $elementAttributes['error'] = $element->getMessages();
        $elementAttributes['id'] = $element->getId();
        $elementAttributes['type'] = $element->getType();
        $elementAttributes['desc'] = $element->getDescription();
        $elementAttributes['hint'] = 'hint_' . $elementName;
        $elementAttributes['disabled'] = $element->getAttrib('disabled');

        if ($element->getType() === 'Zend_Form_Element_Select')
            $elementAttributes["options"] = $element->getMultiOptions(); //array

            if ($element->isRequired())
            $elementAttributes["req"] = "required";
        else
            $elementAttributes["req"] = "optional";

        return $elementAttributes;
    }

    /**
     * used to set special attributes of an element, for example a hint-text
     * @param <type> $element
     * @param <type> $attributeName
     * @param <type> $attributeValue
     */
    public function setElementAttribute($element, $attributeName, $attributeValue) {
        $element = $this->getElement($elementName);
        $element->setAttrib($attributeName, $attributeValue);
    }

}
