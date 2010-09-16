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
    public $validator;
    public $institutes = array();
    public $projects = array();
    public $msc = array();
    public $licences = array();
    public $languages = array();

    public function __construct($datatype) {
        $this->datatype = $datatype;
    }

    public function validate() {

        $this->_datatypeValidation();

        $this->_extendedValidation();
    }

    private function _datatypeValidation() {
        switch ($this->datatype) {
            case 'Date' : $this->validator = $this->_validateDate();
                break;

            case 'Integer': $this->validator = $this->_validateInteger();
                break;

            case 'Institute': $this->validator = $this->_validateCollection('institutes');
                break;

            case 'Language' : $this->validator = $this->_validateLanguage();
                break;

            case 'Licence' : $this->validator = $this->_validateLicence();
                break;

            case 'msc' : $this->validator = $this->_validateMSC();
                break;

            case 'Person': $this->validator = $this->_validatePerson();
                break;

            case 'Project' : $this->validator = $this->_validateCollection('projects');
                break;

            case 'Text': $this->validator = null;
                break;

            case 'Title': $this->validator = null;
                break;

            case 'Year': $this->validator = $this->_validateYear();
                break;

            default:
                throw new Publish_Model_OpusServerException("Error while parsing the xml document type: Found datatype " . $this->datatype . " is unknown!");
                break;
        }
    }

    private function _extendedValidation() {
        //TODO Extended Validation
    }

    private function _validateDate() {
        $validator = new Zend_Validate_Date();
        $messages = array(
            Zend_Validate_Date::INVALID => 'publish_validation_error_date_invalid',
            Zend_Validate_Date::INVALID_DATE => 'publish_validation_error_date_invaliddate',
            Zend_Validate_Date::FALSEFORMAT => 'publish_validation_error_date_falseformat');
        $validator->setMessages($messages);
        return $validator;
    }

    private function _validateInteger() {
        $validator = new Zend_Validate_Int(null);
        $validator->setMessage('publish_validation_error_int', Zend_Validate_Int::NOT_INT);
    }

    private function _validateCollection($role) {
        $validValues = $this->getCollection($role);
        if ($validValues == null)
            return null;
        else {
            $validator = new Zend_Validate_InArray($validValues);
            $messages = array(
                Zend_Validate_InArray::NOT_IN_ARRAY => 'publish_validation_error_inarray_notinarray');
            $validator->setMessages($messages);
            return $validator;
        }
    }

    private function _validateLanguage() {
        $validator = new Zend_Validate_InArray(array_keys($this->getLanguages()));
        $messages = array(
            Zend_Validate_InArray::NOT_IN_ARRAY => 'publish_validation_error_inarray_notinarray');
        $validator->setMessages($messages);
        return $validator;
    }

    private function _validateLicence() {
        $licences = $this->getLicences();
        if ($licences == null)
            return null;
        else {
            $validator = new Zend_Validate_InArray($licences);
            $messages = array(
                Zend_Validate_InArray::NOT_IN_ARRAY => 'publish_validation_error_inarray_notinarray');
            $validator->setMessages($messages);
            return $validator;
        }
    }

    private function _validateMSC() {
        $validator = new Opus_Validate_SubjectMSC();
        $messages = array(
            Opus_Validate_SubjectMSC::MSG_SUBJECTMSC => 'publish_validation_error_subjectmsc_msgsubjectmsc');
        $validator->setMessages($messages);
        return $validator;
    }

    private function _validatePerson() {
        $validator = new Zend_Validate_Alpha(true);
        $messages = array(
            Zend_Validate_Alpha::INVALID => 'publish_validation_error_person_invalid',
            Zend_Validate_Alpha::NOT_ALPHA => 'publish_validation_error_person_notalpha',
            Zend_Validate_Alpha::STRING_EMPTY => 'publish_validation_error_person_stringempty');
        $validator->setMessages($messages);
        return $validator;
    }

    private function _validateYear() {
        $validator = new Zend_Validate_GreaterThan('1900');
        $validator->setMessage('publish_validation_error_year', Zend_Validate_GreaterThan::NOT_GREATER);
        return $validator;
    }

    public function selectOptions() {
        switch ($this->datatype) {
            case 'Language':
                $languages = $this->getLanguages();
                if (isset($languages) || count($languages) >= 1) {
                    asort($languages);
                    return $languages;
                } else {
                    $languages = null;
                    return $languages;
                }

            case 'Licence':
                $licences = $this->getLicences();
                if (isset($licences) || count($licences) >= 1) {
                    $data = array();
                    foreach ($licences AS $key => $li)
                        $data[$key] = $li;
                    asort($data);
                    return $data;
                } else {
                    $data = null;
                    return $data;
                }

            case 'Institute' :
                $oaiName = 'institutes';
                $institutes = $this->getCollection($oaiName);
                if (isset($institutes)) {
                    $data = array();
                    foreach ($institutes AS $inst)
                        $data[$inst] = $inst;

                    return $data;
                } else {
                    $data = null;
                    return $data;
                }
                break;

            case 'Project' :
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
    }

    /**
     * method to fetch collections for different types of data: institutes, projects...
     * also checks, if the collections have already be fetched
     * @param <String> $oaiName
     * @return Zend_Validate_InArray
     */
    private function getCollection($oaiName) {
        if (empty($this->$oaiName)) {
            // $this->log->debug($oaiName . " has to be fetched from database!");
            $role = Opus_CollectionRole::fetchByOaiName($oaiName);
            if ($role === null)
                return null;
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
            //$this->log->debug($oaiName . " can be fetched from cache!");
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
            } else {

                $dbLanguages = Opus_Language::getAllActive();
                if (isset($dbLanguages) || count($dbLanguages) >= 1) {
                    foreach ($dbLanguages as $lan)
                        $languages[$lan->getPart2B()] = $lan->getDisplayName();
                    $this->languages = $languages;

                    return $languages;
                }else
                    return null;
            }
        } else
            return $this->languages;
    }

    /**
     * return the available languages from registry, database or chache
     * @return <Array> languages
     */
    private function getLicences() {
        $licences = array();
        if (empty($this->licences)) {
            foreach ($licences = Opus_Licence::getAll() as $lic) {
                $name = $lic->getDisplayName();
                $licences[$name] = $name;
            }
            $this->licences = $licences;
            return $licences;
        } else
            return $this->licences;
    }

}

?>
