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

        $parser = new Publish_Model_DocumenttypeParser($dom, $this);
        $this->log->debug("Parser created");
        $parser->setAdditionalFields($this->additionalFields);

        $parser->setPostValues($this->postData);

        if ($parser !== false)
            $parser->parse();

        $this->log->debug("Parsing ready");

        $formElements = $parser->formElements;
        $this->addElements($formElements);

        $this->_addSubmit('button_label_send', 'send');

        $this->_addSubmit('button_label_back', 'back');

        if (isset($this->postData))
            $this->populate($this->postData);
    }

    /**
     * Adds submit button to the form.
     * @param <type> $label
     */
    protected function _addSubmit($label, $name) {
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

        if ($element->getType() === 'Zend_Form_Element_Checkbox') {            
            $elementAttributes['value'] = $element->getCheckedValue();            
        }

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

    public function prepareCheck() {
        $defaultNS = new Zend_Session_Namespace('Publish');
        $defaultNS->elements = array();
        $defaultNS->depositForm = $this;
//        foreach ($this->getDisplayGroups() AS $group) {
//            $this->removeDisplayGroup($group->getName());
//        }

        foreach ($this->getElements() as $element) {
            $name = $element->getName();
            if ($element->getValue() == "" || $element->getType() == "Zend_Form_Element_Submit" || $element->getType() == "Zend_Form_Element_Hidden") {
                $element->removeDecorator('Label');
                $this->removeElement($name);
            }
            else {
                $defaultNS->elements[$name]['name'] = $name;
                $defaultNS->elements[$name]['value'] = $element->getValue();
                $defaultNS->elements[$name]['label'] = $element->getLabel();
                $element->removeDecorator('Label');
          //      $this->removeElement($name);
            }
        }
        $this->_addSubmit('button_label_back', 'back');
        $this->_addSubmit('button_label_collection', 'collection');
        $this->_addSubmit('button_label_send2', 'send');
    }

}
