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
class PublishingSecond extends Zend_Form {

    //String
    public $doctype = "";
    //Integer
    public $docId = "";
    //Integer 0 - 1
    public $fulltext = "";
    //array of fields to add additionally
    public $additionalFields = array();
    //array of given post data to fill in fields
    public $postData = array();
    //log-Object
    public $log;

    public function __construct($type, $id, $fulltext, $additionalFields, $postData, $options=null) {
        $this->doctype = $type;
        $this->docId = $id;
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

        //formArray for the templates
        $formArray = array();

        //get the xml file for the current doctype
        $xmlFile = "../config/xmldoctypes/" . $this->doctype . ".xml";

        //create the DOM Parser for reading the xml file
        //if (!$dom = domxml_open_mem(file_get_contents($xmlFile))){
        if (!$dom = new DOMDocument()) {
            echo "Error while trying to begin parsing the document type.";
            exit;
        }
        $dom->load($xmlFile);

        //parse the xml file for the tag "field"
        foreach ($dom->getElementsByTagname('field') as $field) {
            //=1= Catch all interesting attributes!
            if ($field->hasAttributes()) {
                $elementName = $field->getAttribute('name');
                $required = $field->getAttribute('required');
                $formElement = $field->getAttribute('formelement');
                $datatype = $field->getAttribute('datatype');
                $multiplicity = $field->getAttribute('multiplicity');
            }
            else
                throw new OpusServerPublishingException("Error while parsing xml document type: Choosen document type has missing attributes in element 'field'!");

            if (empty($elementName) || empty($required) || empty($formElement) || empty($datatype) || empty($multiplicity))
                throw new OpusServerPublishingException("Error while parsing the xml document type: Found attribute(s) are empty!");

            //=2= Check if there are child nodes -> concerning fulltext or other dependencies!
            if ($field->hasChildNodes()) {
                //check if the field has to be required for fulltext
                if ($this->fulltext === "1") {
                    $requiredIfFulltext = $field->getElementsByTagName("required-if-fulltext");
                    //case: fulltext => overwrite the $required value with yes
                    if ($requiredIfFulltext->length != 0)
                        $required = "yes";
                }
            }
            //=3= Get the proper validator from the datatape!
            $validator = $this->getValidatorsByName($datatype);

            //prepare form element: create Zend_Form_element with neede attributes
            $this->prepareFormElement($formElement, $elementName, $validator, $datatype, $required);

            //=4= Check if fields has to shown multi times!
            if ($multiplicity != "1") {
                //additionalFields != null means additinal fields have to be shown
                if ($this->additionalFields != null) {
                    //button and hidden element that carries the value of how often the element has to be shown
                    $countMoreHidden = $this->createElement('hidden', 'countMore' . $elementName);
                    $addMoreButton = $this->createElement('submit', 'addMore' . $elementName);
                    $addMoreButton->setLabel("Add one more " . $elementName);

                    $deleteMoreButton = $this->createElement('submit', 'deleteMore' . $elementName);
                    $deleteMoreButton->setLabel("Delete " . $elementName);


                    $currentNumber = 1;
                    if (array_key_exists($elementName, $this->additionalFields)) {
                        //$allowedNumbers is set in controller and given to the form by array as parameter
                        $currentNumber = $this->additionalFields[$elementName];
                        $countMoreHidden->setValue($currentNumber);
                        $this->addElement($countMoreHidden);

                        $this->log->debug("CountMoreHidden for element " . $elementName . " is set to value " . $currentNumber);
                        if ($multiplicity == "*")
                            $multiplicity = 99;
                        else
                            $multiplicity = (int) $multiplicity;

                        //start counting at lowest possible number -> also used for name
                        for ($i = 1; $i < $currentNumber; $i++) {
                            $counter = $i + 1;
                            $this->prepareFormElement($formElement, $elementName . $counter, $validator, $datatype, $required);
                        }

                        if ($currentNumber == 1) {
                            $this->addElement($addMoreButton);
                        }
                        else if ($currentNumber < $multiplicity) {
                            $this->addElement($addMoreButton);
                            $this->addElement($deleteMoreButton);
                            } else {
                                //here only a delete button
                                $this->addElement($deleteMoreButton);
                            }
                    }
                }

                //additionalFields == null means initial state -> field is shown one time and can be demanded
                else {
                    //button and hidden element that carries the value of how often the element has to be shown
                    $countMoreHidden = $this->createElement('hidden', 'countMore' . $elementName);
                    $countMoreHidden->setValue("1");
                    $this->addElement($countMoreHidden);

                    $addMoreButton = $this->createElement('submit', 'addMore' . $elementName);
                    $addMoreButton->setLabel("Add one more " . $elementName);
                    $this->addElement($addMoreButton);
                }
            }
        }


        //hidden field for fulltext to cummunicate between different forms
        $hiddenFull = $this->createElement('hidden', 'fullText');
        $hiddenFull->setValue($this->fulltext);
        $this->addElement($hiddenFull);

        //hidden field with document type
        $hiddenType = $this->createElement('hidden', 'documentType');
        $hiddenType->setValue($this->doctype);
        $this->addElement($hiddenType);

        //hidden field with document id
        $hiddenId = $this->createElement('hidden', 'documentId');
        $hiddenId->setValue($this->docId);
        $this->addElement($hiddenId);

        //Submit button
        $submit = $this->createElement('submit', 'send');
        $submit->setLabel('Send');
        $this->addElement($submit);
    }

    /**
     * this method is used while generating publishing forms and parsing xml document types
     * the "user datatypes" will be translated in proper Zend_Validator or own Opus_Validator
     * @param <type> $datatype parsed value in a xml document type
     * @return <type> array of validators that belong to the given datatype
     */
    public function getValidatorsByName($datatype) {
        switch ($datatype) {
            case 'Text': return new Zend_Validate_Alnum(true);
                break;
            case 'Integer': return new Zend_Validate_Int(null);
                break;
            case 'Year': return new Zend_Validate_GreaterThan('1900');
                break;
            case 'Person': return new Zend_Validate_Alpha(true);
                break;
            case 'Alpha': return new Zend_Validate_Alpha(false);
                break;

            default: return new OpusServerPublishingException("Error while parsing the xml document type: Found datatype " . $datatype . " is unknown!");
                break;
        }
        //TODO: Möglichkeit für den Admin einrichten, die Validatoren zu konfigurieren!!!
    }

    /**
     * method for creating a new form element and set the most needed values
     * @param <type> $formElement parsed from xml
     * @param <type> $elementName parsed from xml
     */
    public function prepareFormElement($formElement, $elementName, $validator, $datatype, $required) {
        if ($datatype != 'Person') {
            $this->addFormElement($formElement, $elementName, $validator, $required);
        } else {
            $nameFirst = $elementName . 'FirstName';
            $this->addFormElement($formElement, $nameFirst, $validator, 'no');

            $nameLast = $elementName . 'LastName';
            $this->addFormElement($formElement, $nameLast, $validator, $required);
        }
    }

    protected function addFormElement($formElement, $elementName, $validator, $required) {
        $formField = $this->createElement($formElement, $elementName);
        $formField->setLabel($elementName);
        $formField->addValidator($validator);
        if ($required == 'yes')
            $formField->setRequired(true);

        if ($this->postData != null)
            if (array_key_exists($elementName, $this->postData))
                $formField->setValue($this->postData[$elementName]);

        $this->addElement($formField);
    }
    
    /**
     * brings all form values in an order for the document types templates
     * @return array of ordererd form values
     */
    public function convertToArray($form) {
        $formArray = array();
        $formValues = $form->getValues();
        

        return $formArray;
    }
}
