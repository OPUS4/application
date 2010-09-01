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
    //array of institutes
    public $institutes = array();
    //array of projects
    public $projects = array();
    //array of msc classifications
    public $msc = array();
    //array of languages
    public $languages = array();


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
        $dom = $this->_getDocument();

        //parse the xml file for the tag "field"
        foreach ($dom->getElementsByTagname('field') as $field) {
            $this->_parseField($field);
        }

        //hidden field for fulltext to cummunicate between different forms
        $this->_addHiddenField('fullText', $this->fulltext);

        //hidden field with document type
        $this->_addHiddenField('documentType', $this->doctype);

        //hidden field with document id
        $this->_addHiddenField('documentId', $this->docId);

        $this->_addSubmit('Formular abschicken');
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

    /**
     * Adds a Selection field to the form with the given data array as options
     * @param <String> $elementName
     * @param <Zend_Validate> $validator
     * @param <String> $required
     * @param <String> $label
     * @param <Array> $data list of options
     */
    protected function _addSelect($workflow, $elementName, $validator, $required, $label, $data) {
        $this->log->debug('method _addSelect ... ');
        if (count($data) == 1) {
            $value = (array_keys($data));
            $formField = $this->createElement('text', $elementName);
            $formField->setValue($value[0]);
        } else {

            $formField = new Zend_Form_Element_Select($elementName);

            switch ($workflow) {
                case 'licence' :
                    $formField->setMultiOptions(array_merge(array('' => 'choose_valid_licence'), $data));
                    break;
                case 'language' :
                    $formField->setMultiOptions(array_merge(array('' => 'choose_valid_language'), $data));
                    break;
                case 'projects' :
                    $formField->setMultiOptions(array_merge(array('' => 'choose_valid_project'), $data));
                    break;
                case 'institutes' :
                    $formField->setMultiOptions(array_merge(array('' => 'choose_valid_institute'), $data));
            }
        }
        $formField->setLabel($label)
                ->addValidator($validator);

        if ($required === "yes")
            $formField->setRequired(true);
        else
            $formField->setRequired(false);

        if ($this->postData !== null)
            if (array_key_exists($elementName, $this->postData))
                $formField->setValue($this->postData[$elementName]);

        $this->addElement($formField);
        return $formField;
    }

    /**
     * Returns the DOMDocument for the document type.
     * @return DOMDocument
     */
    protected function _getDocument() {
        $config = Zend_Registry::get('Zend_Config');

        //formArray for the templates
        $formArray = array();

        //get the xml file for the current doctype
        $xmlFile = $config->publish->doctypesPath . DIRECTORY_SEPARATOR . $this->doctype . ".xml";

        //create the DOM Parser for reading the xml file
        //if (!$dom = domxml_open_mem(file_get_contents($xmlFile))){
        if (!$dom = new DOMDocument()) {
            echo "Error while trying to begin parsing the document type.";
            exit;
        }
        $dom->load($xmlFile);

        return $dom;
    }

    /**
     * Parse the DOM for a field.
     * @param <type> $field
     */
    protected function _parseField(DOMElement $field) {
        //=1= Catch all interesting attributes!
        if ($field->hasAttributes()) {
            $elementName = $field->getAttribute('name');
            $required = $field->getAttribute('required');
            $formElement = $field->getAttribute('formelement');
            $datatype = $field->getAttribute('datatype');
            $multiplicity = $field->getAttribute('multiplicity');
        }

        //=2= Check if there are child nodes -> concerning fulltext or other dependencies!
        if ($field->hasChildNodes()) {
            //check if the field has to be required for fulltext
            if ($this->fulltext === "1") {
                $requiredIfFulltext = $field->getElementsByTagName("required-if-fulltext");
                //case: fulltext => overwrite the $required value with yes
                if ($requiredIfFulltext->length != 0) {
                    $this->log->debug($elementName . " is required-if-fulltext! And Fulltext ist set to " . $this->fulltext );
                    $required = "yes";
                }
            }
//parse the xml file for the tag "subfield"
//                foreach ($dom->getElementsByTagname('subfield') as $subField) {
//                    if ($subField->hasAttributes()) {
//                        $subElementName = $subField->getAttribute('name');
//                        $subRequired = $subField->getAttribute('required');
//                        $subFormElement = $subField->getAttribute('formelement');
//                    }
//                    else
//                        throw new OpusServerPublishingException("Error while parsing xml document type: Choosen document type has missing attributes in element 'subfield'!");
//               }
            $validation = $this->_parseValidation($field);
        }

        //=3= Get the proper validator from the datatape!
        $validator = $this->_getValidatorsByDatatype($datatype);
        // TODO combine with result of _parseValidation
        //=4= Check if fields has to shown multi times!
        if ($multiplicity !== "1") {
            $this->_addDisplayGroup($formElement, $elementName, $validator, $datatype, $required, $multiplicity);
        } else {
            //prepare element that do not belong to a display group
            $this->_prepareFormElement($formElement, $elementName, $validator, $datatype, $required, null, $elementName);
        }
    }

    /**
     * Parses the validation configuration for a field.
     *
     * @param <type> $field
     */
    protected function _parseValidation(DOMElement $field) {
        $validationConfig = $field->getElementsByTagName('validation');
        if (!empty($validationConfig)) {
            foreach ($field->getElementsByTagName('validator') as $validate) {
                $name = $validate->getAttribute('name');
                $negate = $validate->getAttribute('negate');
                $params = $this->_parseParameters($validate);
                $validatorInstance = $this->_getValidator($name, $params);
            }
        } else {
            return null;
        }
    }

    /**
     * Parses parameter elements for a element.
     *
     * <param name="min" value="6" />
     *
     * @param DOMElement $field
     * @return hash of parameters
     */
    protected function _parseParameters(DOMElement $field) {
        $parameters = array();
        foreach ($validate->getElementsByTagName('param') as $param) {
            $key = $param->getAttribute('name');
            $value = $param->getAttribute('value');
            $parameters[$key] = $value;
        }
        return parameters;
    }

    /**
     *
     * @param <type> $name
     * @param <type> $params
     * @return Form_Validate_RequiredIf
     */
    protected function _getValidator($name, $params = null) {
// TODO change name to lower case
        switch ($name) {
            case 'requiredif':
                return new Form_Validate_RequiredIf($params);
        }
    }

    /**
     * Adds a group of elements to the form.
     *
     * @param <type> $formElement
     * @param <type> $elementName
     * @param <type> $validator
     * @param <type> $datatype
     * @param <type> $required
     */
    protected function _addDisplayGroup($formElement, $elementName, $validator, $datatype, $required, $multiplicity) {
        //array is used to initilaize the display group for this group of elements
        $group = array();
        $groupName = 'group' . $elementName;
        //current element has also to be in that group
        //prepare form element: create Zend_Form_element with needed attributes
        $label = $elementName;
        $group = $this->_prepareFormElement($formElement, $elementName . "1", $validator, $datatype, $required, $group, $label);

        //additionalFields != null means additinal fields have to be shown
        if ($this->additionalFields != null) {
            //button and hidden element that carries the value of how often the element has to be shown
            $countMoreHidden = $this->createElement('hidden', 'countMore' . $elementName);
            $addMoreButton = $this->createElement('submit', 'addMore' . $elementName);
            $addMoreButton->setLabel('button_label_add_one_more' . $elementName);

            $deleteMoreButton = $this->createElement('submit', 'deleteMore' . $elementName);
            $deleteMoreButton->setLabel('button_label_delete' . $elementName);

            $currentNumber = 1;
            if (array_key_exists($elementName, $this->additionalFields)) {
                //$allowedNumbers is set in controller and given to the form by array as parameter
                $currentNumber = $this->additionalFields[$elementName];
                $countMoreHidden->setValue($currentNumber);
                $this->addElement($countMoreHidden);
                $group[] = $countMoreHidden->getName();

                //$this->log->debug("CountMoreHidden for element " . $elementName . " is set to value " . $currentNumber);
                if ($multiplicity == "*")
                    $multiplicity = 99;
                else
                    $multiplicity = (int) $multiplicity;

                //start counting at lowest possible number -> also used for name
                for ($i = 1; $i < $currentNumber; $i++) {
                    $counter = $i + 1;
                    $group = $this->_prepareFormElement($formElement, $elementName . $counter, $validator, $datatype, $required, $group, $label);
                }

                if ($currentNumber == 1) {
                    //only one field is shown -> nothing to delete
                    $this->addElement($addMoreButton);
                    $group[] = $addMoreButton->getName();
                } else if ($currentNumber < $multiplicity) {
                    //more than one field has to be shown -> delete buttons are needed
                    $this->addElement($addMoreButton);
                    $group[] = $addMoreButton->getName();
                    $this->addElement($deleteMoreButton);
                    $group[] = $deleteMoreButton->getName();
                } else {
                    //maximum fields are shown -> here only a delete button
                    $this->addElement($deleteMoreButton);
                    $group[] = $deleteMoreButton->getName();
                }
            }

            //add a displaygroup to the form for grouping same elements
            $displayGroup = $this->addDisplayGroup($group, $groupName);
            //$this->log->debug("Added Displaygroup to form: " . $groupName);
        } else {
            //additionalFields == null means initial state -> field is shown one time and can be demanded
            //button and hidden element that carries the value of how often the element has to be shown
            $countMoreHidden = $this->createElement('hidden', 'countMore' . $elementName);
            $countMoreHidden->setValue("1");
            $this->addElement($countMoreHidden);
            $group[] = $countMoreHidden->getName();

            $addMoreButton = $this->createElement('submit', 'addMore' . $elementName);
            $addMoreButton->setLabel('button_label_add_one_more' . $elementName);
            $this->addElement($addMoreButton);
            $group[] = $addMoreButton->getName();

            $displayGroup = $this->addDisplayGroup($group, $groupName);
            //$this->log->debug("Added Displaygroup to form: " . $groupName);
        }
    }

    /**
     * this method is used while generating publishing forms and parsing xml document types
     * the "user datatypes" will be translated in proper Zend_Validator or own Opus_Validator
     * @param <type> $datatype parsed value in a xml document type
     * @return <type> array of validators that belong to the given datatype
     * @throw Publish_Model_OpusServerException
     */
    protected function _getValidatorsByDatatype($datatype) {
        $this->log->debug("method _getValidatorsByDatatype (". $datatype .") ...");
        switch ($datatype) {
            case 'Alpha':
                return new Zend_Validate_Alpha(false);
                break;

            case 'Date' :
                return new Zend_Validate_Date();
                break;

            case 'Integer':
                return new Zend_Validate_Int(null);
                break;

            case 'Institute':
                return new Zend_Validate_InArray($this->getCollection('institutes'));
                break;

            case 'Language' :
                return new Zend_Validate_InArray(array_keys($this->getLanguages()));
                break;

            case 'Licence' :
                return new Zend_Validate_InArray(Opus_Licence::getAll());
                break;

            case 'msc' :
                return new Zend_Validate_InArray($this->getCollection('msc'));
                break;

            case 'Person':
                return new Zend_Validate_Alpha(true);
                break;

            case 'Project' :
                return new Zend_Validate_InArray($this->getCollection('projects'));
                break;

            case 'Text':
                return null;
                break;

            case 'Title':
                return null;
                break;

            case 'Year':
                return new Zend_Validate_GreaterThan('1900');
                break;

            default:
                throw new Publish_Model_OpusServerException("Error while parsing the xml document type: Found datatype " . $datatype . " is unknown!");
                break;
        }
    }

    /**
     * method to prepare a element for the given form, check implicit fields for person, title, licence, language
     * @param <type> $formElement
     * @param <type> $elementName
     * @param <type> $validator
     * @param <type> $datatype
     * @param <type> $required
     * @param <type> $group
     * @param <type> $label
     * @return string
     */
    protected function _prepareFormElement($formElement, $elementName, $validator, $datatype, $required, $group, $label) {
        switch ($datatype) {
            case 'Language':
                return $this->_prepareLanguageElement($elementName, $validator, $required, $label);
                break;

            case 'Licence':
                return $this->_prepareLicenceElement($elementName, $validator, $required, $label);
                break;

            case 'Institute' :
                return $this->_prepareInstituteElement($elementName, $validator, $required, $label, $group);
                break;

            case 'Project' :
                return $this->_prepareProjectElement($elementName, $validator, $required, $label, $group);
                break;

            case 'Person' :
                return $this->_preparePersonElement($formElement, $elementName, $validator, $required, $group, $label);
                break;

            case 'Title':
                return $this->_prepareTitleElement($formElement, $elementName, $validator, $required, $group, $label);
                break;

            default:
                $this->_addFormElement($formElement, $elementName, $validator, $required, $label);
                $group[] = $elementName;
        }
        return $group;
    }

    /**
     * prepare Language Element: create language list and add selection
     * @param <type> $elementName
     * @param <type> $validator
     * @param <type> $required
     * @param <type> $label
     */
    protected function _prepareLanguageElement($elementName, $validator, $required, $label) {
        $this->log->debug("method _prepareLanguageelement...");
        $languages = $this->getLanguages();
        asort($languages);
        $this->_addSelect('language', $elementName, $validator, $required, $label, $languages);
    }

    /**
     * prpeare Licence Element: create licence list and add selection
     * @param <type> $elementName
     * @param <type> $validator
     * @param <type> $required
     * @param <type> $label
     */
    protected function _prepareLicenceElement($elementName, $validator, $required, $label) {
        $licences = Opus_Licence::getAll();
        $this->log->debug("Licences: ");
        $data = array();
        foreach ($licences AS $li) {
            $name = $li->getDisplayName();
            $data[$name] = $name;
            $this->log->debug($name);
        }
        asort($data);
        $this->_addSelect('licence', $elementName, $validator, $required, $label, $data);
    }

    /**
     * prepare MSC Selection Element: create msc list and add selection
     * @param <type> $elementName
     * @param <type> $validator
     * @param <type> $required
     * @param <type> $label
     */
    protected function _prepareInstituteElement($elementName, $validator, $required, $label, $group) {
        $groupWas = "0";
        if ($group === null) {
            $groupWas = "1";
            $group = array();
        }
        $oaiName = 'institutes';
        $institutes = $this->getCollection($oaiName);
        $data = array();
        foreach ($institutes AS $inst) {
            $data[$inst] = $inst;
        }
        $this->_addSelect($oaiName, $elementName, $validator, $required, $label, $data);
        $group[] = $elementName;
        return $group;
    }

    /**
     * prepare Person Element: create implicit fields and group them
     * @param <type> $formElement
     * @param <type> $elementName
     * @param <type> $validator
     * @param <type> $required
     * @param <type> $group
     * @param <type> $label
     */
    protected function _preparePersonElement($formElement, $elementName, $validator, $required, $group, $label) {
        $groupWas = "0";
        if ($group === null) {
            $groupWas = "1";
            $group = array();
        }
        $first = "FirstName";
        $nameFirst = $elementName . $first;
        $this->_addFormElement($formElement, $nameFirst, $validator, $required, $label . $first);
        $group[] = $nameFirst;
        $last = "LastName";
        $nameLast = $elementName . $last;
        $this->_addFormElement($formElement, $nameLast, $validator, $required, $label . $last);
        $group[] = $nameLast;

        if ($groupWas == "1") {
            $groupName = 'group' . $elementName;
            $displayGroup = $this->addDisplayGroup($group, $groupName);
            $this->log->debug("Added Displaygroup to form: " . $groupName);
        }

        return $group;
    }

    /**
     * prepare MSC Selection Element: create msc list and add selection
     * @param <type> $elementName
     * @param <type> $validator
     * @param <type> $required
     * @param <type> $label
     */
    protected function _prepareProjectElement($elementName, $validator, $required, $label, $group) {
        $groupWas = "0";
        if ($group === null) {
            $groupWas = "1";
            $group = array();
        }
        $oaiName = 'projects';
        $projects = $this->getCollection($oaiName);
        $data = array();
        foreach ($projects AS $pro) {
            $data[$pro] = $pro;
        }
        $this->_addSelect($oaiName, $elementName, $validator, $required, $label, $data);
        $group[] = $elementName;
        return $group;
    }

    /**
     * prpeare Title Element: create implicit fields and group them
     * @param <type> $formElement
     * @param <type> $elementName
     * @param <type> $validator
     * @param <type> $required
     * @param <type> $group
     * @param <type> $label
     */
    protected function _prepareTitleElement($formElement, $elementName, $validator, $required, $group, $label) {
        $groupWas = "0";
        if ($group === null) {
            $groupWas = "1";
            $group = array();
        }

        //add Title value
        $this->_addFormElement($formElement, $elementName, $validator, $required, $label);
        $group[] = $elementName;

        $language = "Language";
        $languageName = $elementName . $language;
        $validator = $this->_getValidatorsByDatatype("Language");

        //create language selection
        $languageName = $elementName . 'Language';
        $this->_prepareLanguageElement($languageName, $validator, $required, $label . 'Language');
        $group[] = $languageName;

        if ($groupWas == "1") {
            $groupName = 'group' . $elementName;
            $displayGroup = $this->addDisplayGroup($group, $groupName);
            $this->log->debug("Added Displaygroup to form: " . $groupName);
        }
        return $group;
    }

    /**
     *
     * @param <type> $formElement
     * @param <type> $elementName
     * @param <type> $validator
     * @param <type> $required
     * @param <type> $label
     * @return <type>
     */
    protected function _addFormElement($formElement, $elementName, $validator, $required, $label) {
        $formField = $this->createElement($formElement, $elementName);
        $formField->setLabel($label);
        if (isset($validator))
            $formField->addValidator($validator);
        if ($required == 'yes')
            $formField->setRequired(true);

        if ($this->postData != null)
            if (array_key_exists($elementName, $this->postData))
                $formField->setValue($this->postData[$elementName]);

        if ($formElement == 'textarea') {
            $formField->setAttrib('rows', 9);
            $formField->setAttrib('cols', 30);
        }
        $this->addElement($formField);
        return $formField;
    }

    /**
     *
     * @param <type> $elementName
     * @return string
     */
    public function getElementAttributes($elementName) {
        $elementAttributes = array();
        $element = $this->getElement($elementName);
        $elementAttributes["value"] = $element->getValue();
        $elementAttributes["label"] = $element->getLabel();
        $elementAttributes["error"] = $element->getMessages();
        $elementAttributes["id"] = $element->getId();
        $elementAttributes["type"] = $element->getType();
        $elementAttributes["hint"] = 'hint_' . $elementName;

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

    /**
     * method to fetch collections for different types of data: institutes, projects...
     * also checks, if the collections have already be fetched
     * @param <String> $oaiName
     * @return Zend_Validate_InArray
     */
    protected function getCollection($oaiName) {
        if (empty($this->$oaiName)) {
            $this->log->debug($oaiName . " has to be fetched from database!");
            $role = Opus_CollectionRole::fetchByOaiName($oaiName);
            if ($role === null)
                throw new Publish_Model_OpusServerException("No Collections found in database for " . $oaiName);
            else {
                $colls = Opus_Collection::fetchCollectionsByRoleId($role->getId());
                $collections = array();
                foreach ($colls AS $coll) {
                    $number = $coll->getNumber();
                    if (strlen($number) >= 1 && $number != 'Projects') {
                        $collections[] = $number;
                    } else {
                        $name = $coll->getName();
                        if (strlen($name) >= 1 && $name != 'Institutes')
                            $collections[] = $name;
                    }
                }
            }
            $this->$oaiName = $collections;
            return $collections;
        } else {
            $this->log->debug($oaiName . " can be fetched from cache!");
            return $this->$oaiName;
        }
    }

    /**
     * return the available languages from registry, database or chache
     * @return <Array> languages
     */
    protected function getLanguages() {
        $languages = array();
        if (empty($this->languages)) {
            $this->log->debug("Languages has to fetched from Registry!");
            if (Zend_Registry::isRegistered('Available_Languages') === true) {
                $languages = Zend_Registry::get('Available_Languages');
                $this->languages = $languages;

                return $languages;
            } else {
                $this->log->debug("Languages has to fetched from Database!");
                foreach (Opus_Language::getAllActive() as $lan)
                    $languages[$lan->getPart2B()] = $lan->getDisplayName();
                $this->languages = $languages;
                
                return $languages;
            }
            
        } else {
            $this->log->debug("Languages can be fetched from cache!");
            return $this->languages;
        }
    }

}
