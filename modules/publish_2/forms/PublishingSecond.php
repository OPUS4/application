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

    public $doctype;

    public function __construct($type, $options=null) {
        $this->doctype = $type;
        parent::__construct($options);
    }

    /**
     * Build document publishing form that depends on the doc type
     * @param $doctype
     * @return void
     */
    public function init() {

        //get the xml file for the current doctype
        $xmlFile = "../config/xmldoctypes/" . $this->doctype . ".xml";

        //create the DOM Parser for reading the xml file
        //if (!$dom = domxml_open_mem(file_get_contents($xmlFile))){
        if (!$dom = new DOMDocument()) {
            echo "Error while parsing the document\n";
            exit;
        }
        $dom->load($xmlFile);

        //parse the xml file for the tag "field"
        foreach ($dom->getElementsByTagname('field') as $field) {
            //catch all interesting attributes
            $elementName = $field->getAttribute('name');
            $required = $field->getAttribute('required');
            $formElement = $field->getAttribute('formelement');
            $datatype = $field->getAttribute('datatype');

            //get the proper validator from the datatape
            $validator = $this->getValidatorsByName($datatype);

            if ($datatype != 'Person') {
                $this->addFormElement($formElement, $elementName, $validator, $required);
            }

            //in case of person -> show 2 field for first and last name
            else {
                $nameFirst = $elementName . 'FirstName';
                $this->addFormElement($formElement, $nameFirst, $validator, $required);
                $nameLast = $elementName . 'LastName';
                $this->addFormElement($formElement, $nameLast, $validator, $required);
            }
        }

        //hidden field with document type
        $hidden = $this->createElement('hidden', 'documentType');
        $hidden->setValue($this->doctype);

        //Submit button
        $submit = $this->createElement('submit', 'send');
        $submit->setLabel('Send');

        $this->addElement($hidden);
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
            case 'Integer': return new Zend_Validate_Int(null);
            case 'Year': return new Zend_Validate_GreaterThan('1900');
            case 'Person': return new Zend_Validate_Alpha(true);
            case 'Alpha': return new Zend_Validate_Alpha(false);

            //case 'LicencesDB': //

            default:
                break;
        }
        //TODO: Möglichkeit für den Admin einrichten, die Validatoren zu konfigurieren!!!
    }

    /**
     * method for creating a new form element and set the most needed values
     * @param <type> $formElement parsed from xml
     * @param <type> $elementName parsed from xml
     */
    public function addFormElement($formElement, $elementName, $validator, $required) {
        $formField = $this->createElement($formElement, $elementName);
        $formField->setLabel($elementName);
        $formField->addValidator($validator);
        if ($required == 'yes') {
            $formField->setRequired(true);
        }
        $this->addElement($formField);
    }

}
