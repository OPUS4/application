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
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Description of Validation
 *
 * @author Susanne Gottwald
 */
class Publish_Model_Validation {

    public $datatype;
    public $validator = array();
    public $institutes = array();
    public $projects = array();
    public $licences = array();
    public $languages = array();
    public $log;
    public $sessionP;
    public $session;
    public $listOptions = array();
    public $collectionRole;

    public function __construct($datatype, $collectionRole=null, $options=null) {
        if (isset($options) && !empty($options)) {
            $this->listOptions = $options;
            $this->datatype = 'List';
        }
        if (isset($collectionRole)) {
            $this->collectionRole = $collectionRole;
        }
        else
            $this->datatype = $datatype;
        $this->log = Zend_Registry::get('Zend_Log');
        $this->sessionP = new Zend_Session_Namespace('Publish');
        $this->session = new Zend_Session_Namespace();
    }

    public function validate() {

        $this->_datatypeValidation();
    }

    private function _datatypeValidation() {
        switch ($this->datatype) {

            case 'Collection' : //$this->validator = $this->_validateCollection($this->collectionRole);
                return null;
                break;

            case 'ccs' : $this->validator = $this->_validateCCS();
                break;

            case 'Date' : $this->validator = $this->_validateDate();
                break;

            case 'ddc' : $this->validator = $this->_validateDDC();
                break;

            case 'Email' : $this->validator = $this->_validateEmail();
                break;

            case 'Enrichment' : $this->validator = null;
                break;

            case 'Integer': $this->validator = $this->_validateInteger();
                break;

            case 'Institute': $this->validator = $this->_validateCollection('institutes');
                break;

            case 'Language' : $this->validator = $this->_validateLanguage();
                break;

            case 'Licence' : $this->validator = $this->_validateLicence();
                break;

            case 'List' : $this->validator = $this->_validateList();
                break;

            case 'msc' : $this->validator = $this->_validateMSC();
                break;

            case 'pacs' : $this->validator = $this->_validatePACS();
                break;

            case 'Project' : $this->validator = $this->_validateCollection('projects');
                break;

            case 'Text': //$this->validator = $this->_validateText();
                $this->validator = null;
                break;

            case 'ThesisGrantor' : $this->validator = $this->_validateThesis(true);
                break;

            case 'ThesisPublisher' : $this->validator = $this->_validateThesis();
                break;

            case 'Title': $this->validator = null;
                break;

            case 'Year': $this->validator = $this->_validateYear();
                break;

            default:
                //else no datatype required! 
                //throw new Publish_Model_OpusServerException("Error while parsing the xml document type: Found datatype " . $this->datatype . " is unknown!");
                break;
        }
    }

    private function _validateDate() {
        $format_de = "DD.MM.YYYY";
        $format_en = "YYYY/MM/DD";

        $lang = $this->session->language;
        $validators = array();

        switch ($lang) {
            case 'en' : $validator = new Zend_Validate_Date(array('format' => $format_en, 'locale' => $lang));
                break;
            case 'de' : $validator = new Zend_Validate_Date(array('format' => $format_de, 'locale' => $lang));
                break;
            default : $validator = new Zend_Validate_Date(array('format' => $format_en, 'locale' => $lang));
                break;
        }
        $messages = array(
            Zend_Validate_Date::INVALID => 'publish_validation_error_date_invalid',
            Zend_Validate_Date::INVALID_DATE => 'publish_validation_error_date_invaliddate',
            Zend_Validate_Date::FALSEFORMAT => 'publish_validation_error_date_falseformat');
        $validator->setMessages($messages);

        $validators[] = $validator;
        return $validators;
    }

    private function _validateCCS() {
        $validators = array();
        $validator = new Opus_Validate_SubjectCCS();
        $messages = array(
            Opus_Validate_SubjectCCS::MSG_SUBJECTCCS => 'publish_validation_error_subjectccs_msgsubjectccs');
        $validator->setMessages($messages);

        $validators[] = $validator;
        return $validators;
    }

    private function _validateCollection($role) {
        $validators = array();
        $validValues = $this->getCollection($role);

        if (is_null($validValues))
            return null;
        else {
            $validator = new Zend_Validate_InArray($validValues);
            $messages = array(
                Zend_Validate_InArray::NOT_IN_ARRAY => 'publish_validation_error_inarray_notinarray');
            $validator->setMessages($messages);

            $validators[] = $validator;
            return $validators;
        }
    }

    private function _validateDDC() {
        $validators = array();
        $validator = new Opus_Validate_SubjectDDC();
        $messages = array(
            Opus_Validate_SubjectDDC::MSG_SUBJECTDDC => 'publish_validation_error_subjectddc_msgsubjectddc');
        $validator->setMessages($messages);

        $validators[] = $validator;
        return $validators;
    }

    private function _validateEmail() {
        $validators = array();
        $validator = new Zend_Validate_EmailAddress();
        $messages = array(
            Zend_Validate_EmailAddress::INVALID => 'publish_validation_error_email_invalid');
        $validator->setMessages($messages);

        $validators[] = $validator;
        return $validators;
    }

    private function _validateInteger() {
        $validators = array();
        $validator = new Zend_Validate_Int();
        $validator->setMessage('publish_validation_error_int', Zend_Validate_Int::NOT_INT);

        $validators[] = $validator;
        return $validators;
    }

    private function _validateLanguage() {
        $validators = array();
        $validator = new Zend_Validate_InArray(array_keys($this->getLanguages()));
        $messages = array(
            Zend_Validate_InArray::NOT_IN_ARRAY => 'publish_validation_error_inarray_notinarray');
        $validator->setMessages($messages);

        $validators[] = $validator;
        return $validators;
    }

    private function _validateLicence() {
        $validators = array();
        $licences = array_keys($this->getLicences());
        if (is_null($licences))
            return null;
        else {
            $validator = new Zend_Validate_InArray($licences);
            $messages = array(
                Zend_Validate_InArray::NOT_IN_ARRAY => 'publish_validation_error_inarray_notinarray');
            $validator->setMessages($messages);

            $validators[] = $validator;
            return $validators;
        }
    }

    private function _validateList() {
        $validators = array();
        foreach ($this->listOptions as $option)
            $this->listOptions[$option] = $option;

        $validator = new Zend_Validate_InArray($this->listOptions);
        $messages = array(
            Zend_Validate_InArray::NOT_IN_ARRAY => 'publish_validation_error_inarray_notinarray');
        $validator->setMessages($messages);

        $validators[] = $validator;
        return $validators;
    }

    private function _validateMSC() {
        $validators = array();
        $validator = new Opus_Validate_SubjectMSC();
        $messages = array(
            Opus_Validate_SubjectMSC::MSG_SUBJECTMSC => 'publish_validation_error_subjectmsc_msgsubjectmsc');
        $validator->setMessages($messages);

        $validators[] = $validator;
        return $validators;
    }

    private function _validatePACS() {
        $validators = array();
        $validator = new Opus_Validate_SubjectPACS();
        $messages = array(
            Opus_Validate_SubjectPACS::MSG_SUBJECTPACS => 'publish_validation_error_subjectpacs_msgsubjectpacs');
        $validator->setMessages($messages);

        $validators[] = $validator;
        return $validators;
    }

//    private function _validatePerson() {
//        //allow characters and whitespace
//        $validator = new Zend_Validate_Alpha(true);
//        $messages = array(
//            Zend_Validate_Alpha::INVALID => 'publish_validation_error_person_invalid',
//            Zend_Validate_Alpha::NOT_ALPHA => 'publish_validation_error_person_notalpha',
//            Zend_Validate_Alpha::STRING_EMPTY => 'publish_validation_error_person_stringempty');
//        $validator->setMessages($messages);
//        return $validator;
//    }

    private function _validateText() {
        $validators = array();
        //allow characters, numbers and whitespace
        $validator = new Zend_Validate_Alpha(true);
        $messages = array(
            Zend_Validate_Alpha::INVALID => 'publish_validation_error_text_invalid',
            Zend_Validate_Alpha::NOT_ALPHA => 'publish_validation_error_text_notalnum',
            Zend_Validate_Alpha::STRING_EMPTY => 'publish_validation_error_text_stringempty');
        $validator->setMessages($messages);

        $validators[] = $validator;
        return $validators;
    }

    private function _validateThesis($grantors = null) {
        $validators = array();
        $thesisGrantors = $this->getThesis($grantors);
        if (!is_null($thesisGrantors)) {
            $thesises = array_keys($thesisGrantors);
            if (is_null($thesises))
                return null;
            else {
                $validator = new Zend_Validate_InArray($thesises);
                $messages = array(
                    Zend_Validate_InArray::NOT_IN_ARRAY => 'publish_validation_error_inarray_notinarray');
                $validator->setMessages($messages);

                $validators[] = $validator;
                return $validators;
            }
        }
    }

    private function _validateYear() {
        $validators = array();

        $validator1 = new Zend_Validate_GreaterThan('1900');
        $validator1->setMessage('publish_validation_error_year_greaterthan', Zend_Validate_GreaterThan::NOT_GREATER);
        $validators[] = $validator1;

        $validator2 = new Zend_Validate_Int();
        $messages = array(
            Zend_Validate_Int::INVALID => 'publish_validation_error_year_intinvalid',
            Zend_Validate_Int::NOT_INT => 'publish_validation_error_year_notint');
        $validator2->setMessages($messages);
        $validators[] = $validator2;

        return $validators;
    }

    public function selectOptions($datatype=null) {
        if (isset($datatype))
            $switchVar = $datatype;
        else
            $switchVar = $this->datatype;

        switch ($switchVar) {
            case 'Collection': return $this->_collectionSelect();
                break;

            case 'Language': return $this->_languageSelect();
                break;

            case 'Licence': return $this->_licenceSelect();
                break;

            case 'List': return $this->listOptions;
                break;

            case 'Institute' : return $this->_instituteSelect();
                break;

            case 'Project' : return $this->_projectSelect();
                break;

            case 'ThesisGrantor' : return $this->_thesisSelect(true);
                break;

            case 'ThesisPublisher' : return $this->_thesisSelect();
                break;

            default : throw new Publish_Model_OpusServerException("Error while parsing the xml document type: Found datatype " . $this->datatype . " is unknown!");
                break;
        }
    }

    private function _collectionSelect() {
        $browsingHelper1 = new Solrsearch_Model_CollectionRoles();
        $collectionRole = Opus_CollectionRole::fetchByOaiName($this->collectionRole);        
        $children = array();
        if ($browsingHelper1->hasVisibleChildren($collectionRole)) {
            $collectionId = $collectionRole->getRootCollection()->getId();
            $collection = new Opus_Collection($collectionId);
            $colls = $collection->getChildren();

            foreach ($colls as $coll) {
                if ($coll->getVisible() == 1)
                    $children['ID:' . $coll->getId()] = $coll->getDisplayName();
            }
        }        
        return $children;
    }

    private function _instituteSelect() {
        $oaiName = 'institutes';
        $institutes = $this->getCollection($oaiName);
        if (isset($institutes)) {
            $data = array();
            foreach ($institutes AS $inst)
                $data[$inst] = $inst;
            return $data;
        }
        else {
            $data = null;
            return $data;
        }
    }

    private function _languageSelect() {
        $languages = $this->getLanguages();
        if (isset($languages) || count($languages) >= 1) {
            asort($languages);
            return $languages;
        }
        else {
            $languages = null;
            return $languages;
        }
    }

    private function _licenceSelect() {
        $licences = $this->getLicences();
        if (isset($licences) && count($licences) >= 1) {
            $data = array();
            foreach ($licences AS $key => $li)
                $data[$key] = $li;
            asort($data);
            return $data;
        }
        else {
            $data = null;
            return $data;
        }
    }

    private function _projectSelect() {
        $oaiName = 'projects';
        $projects = $this->getCollection($oaiName);
        if (isset($projects)) {
            $data = array();
            foreach ($projects AS $pro) {
                if ($pro !== 'Projects' && strlen($pro) > 1)
                    $data[$pro] = $pro;
            }
            asort($data);
            return $data;
        }
        else {
            $data = null;
            return $data;
        }
    }

    private function _thesisSelect($grantors = null) {
        $thesisList = $this->getThesis($grantors);
        if (!is_null($thesisList))
            asort($thesisList);
        return $thesisList;
    }

    /**
     * method to fetch collections for different types of data: institutes, projects...
     * also checks, if the collections have already be fetched
     * @param <String> $oaiName
     * @return Zend_Validate_InArray
     */
    private function getCollection($oaiName) {
        if (empty($this->$oaiName)) {
            $role = Opus_CollectionRole::fetchByName($oaiName);
            if (is_null($role)) {
                return null;
            }
            else {
                $colls = Opus_Collection::fetchCollectionsByRoleId($role->getId());
                $collections = array();
                foreach ($colls AS $coll) {
                    if ($oaiName === 'institutes') {
                        $name = $coll->getName();
                        if (strlen($name) >= 1 && $name != 'Institutes')
                            $collections[] = $name;
                    }
                    else {
                        $number = $coll->getNumber();
                        if (strlen($number) >= 1 && $number != 'Projects') {
                            $collections[] = $number;
                        }
                    }
                }
            }
            $this->$oaiName = $collections;
            return $collections;
        }
        else {
            return $this->$oaiName;
        }
    }

    /**
     * return the available languages from registry, database or chache
     * @return <Array> languages
     */
    private function getLanguages() {
        $languages = array();
        if (empty($this->languages)) {
            if (Zend_Registry::isRegistered('Available_Languages') === true) {
                $languages = Zend_Registry::get('Available_Languages');
                $this->languages = $languages;

                return $languages;
            }
            else {

                $dbLanguages = Opus_Language::getAllActive();
                if (isset($dbLanguages) || count($dbLanguages) >= 1) {
                    foreach ($dbLanguages as $lan)
                        $languages[$lan->getPart2B()] = $lan->getDisplayName();
                    $this->languages = $languages;

                    return $languages;
                } else
                    return null;
            }
        } else
            return $this->languages;
    }

    /**
     * return the available licences from registry, database or chache
     * @return <Array> languages
     */
    private function getLicences() {
        $licences = array();
        if (empty($this->licences)) {
            foreach ($dbLicences = Opus_Licence::getAll() as $lic) {
                $name = $lic->getDisplayName();
                $id = $lic->getId();
                $licences["ID:" . $id] = $name;
            }
            $this->licences = $licences;
            return $licences;
        } else
            return $this->licences;
    }

    /**
     * Retrieves all available ThesisGrantors or ThesisPublishers in a array.
     * Used for generating a select box.
     * @param <type> $grantors true -> ThesisGrantors, null -> ThesisPublishers
     * @return Array of Dnb_Institutes Objects
     */
    private function getThesis($grantors = null) {
        $thesisList = array();
        if ($grantors === true) {
            //get all grantors
            $thesises = Opus_DnbInstitute::getGrantors();
            if (is_null($thesises) || empty($thesises))
                return null;
        } else if (is_null($grantors)) {
            //get all Publishers
            $thesises = Opus_DnbInstitute::getPublishers();
            if (is_null($thesises) || empty($thesises))
                return null;
        }

        foreach ($thesises AS $thesis) {
            $thesisList["ID:" . $thesis->getId()] = $thesis->getDisplayName();
        }
        return $thesisList;
    }

}

?>
