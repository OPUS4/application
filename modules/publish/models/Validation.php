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
 * @author      Michael Lang <lang@zib.de>
 * @copyright   Copyright (c) 2008-2014, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
class Publish_Model_Validation {

    public $datatype;
    public $validator = array();
    public $institutes = array();
    public $projects = array();
    public $licences = array();
    public $series = array();
    public $languages = array();
    public $listOptions = array();
    public $collectionRole;

    private $_view;
    private $_session;

    public function __construct($datatype, $session, $collectionRole = null, $options = null, $view = null) {
        if (isset($options) && !empty($options)) {
            $this->listOptions = $options;
            $this->datatype = 'List';
        }

        if (isset($collectionRole)) {
            $this->collectionRole = $collectionRole;
        }
        else {
            $this->datatype = $datatype;
        }

        $this->_view = $view;
        $this->_session = $session;
    }

    public function validate() {
        $this->_datatypeValidation();
    }

    private function _datatypeValidation() {
        switch ($this->datatype) {

            case 'Date' : $this->validator = $this->_validateDate();
                break;

            case 'Email' : $this->validator = $this->_validateEmail();
                break;

            case 'Integer': $this->validator = $this->_validateInteger();
                break;

            case 'Language' : $this->validator = $this->_validateLanguage();
                break;

            case 'Licence' : $this->validator = $this->_validateLicence();
                break;

            case 'List' : $this->validator = $this->_validateList();
                break;

            case 'Series' : $this->validator = $this->_validateSeries();
                break;

            case 'ThesisGrantor' : $this->validator = $this->_validateThesis(true);
                break;

            case 'ThesisPublisher' : $this->validator = $this->_validateThesis();
                break;

            case 'Year': $this->validator = $this->_validateYear();
                break;

            case 'Collection' :
            case 'CollectionLeaf' :
            case 'Enrichment' :
            case 'SeriesNumber' : //internal datatype, do not use in documenttypes!
            case 'Subject' :
            case 'Reference' :
            case 'Person' :
            case 'Note' :
            case 'Text' :
            case 'Title': $this->validator = null;
                break;

            default:
                //else no datatype required!
                break;
        }
    }

    private function _validateDate() {
        if (!isset($this->_session->language)) {
            return;
        }

        $lang = $this->_session->language;
        $validators = array();

        $validator = new Application_Form_Validate_Date();
        $validator->setLocale($lang);

        $messages = array(
            Zend_Validate_Date::INVALID => $this->translate('publish_validation_error_date_invalid'),
            Zend_Validate_Date::INVALID_DATE => $this->translate('publish_validation_error_date_invaliddate'),
            Zend_Validate_Date::FALSEFORMAT => $this->translate('publish_validation_error_date_falseformat')
        );
        $validator->setMessages($messages);

        $validators[] = $validator;
        return $validators;
    }

    private function _validateEmail() {
        $validators = array();
        $validator = new Publish_Model_ValidationEmail();
        $messages = array(
            Zend_Validate_EmailAddress::INVALID => $this->translate('publish_validation_error_email_invalid'),
            Zend_Validate_EmailAddress::INVALID_FORMAT => $this->translate('publish_validation_error_email_invalid'),
            Zend_Validate_EmailAddress::INVALID_HOSTNAME => $this->translate('publish_validation_error_email_invalid'),
            Zend_Validate_EmailAddress::INVALID_LOCAL_PART => $this->translate('publish_validation_error_email_invalid'),
            Zend_Validate_EmailAddress::INVALID_MX_RECORD => $this->translate('publish_validation_error_email_invalid'),
            Zend_Validate_EmailAddress::INVALID_SEGMENT => $this->translate('publish_validation_error_email_invalid'),
            Zend_Validate_EmailAddress::LENGTH_EXCEEDED => $this->translate('publish_validation_error_email_invalid'),
            Zend_Validate_EmailAddress::QUOTED_STRING => $this->translate('publish_validation_error_email_invalid'),
            Zend_Validate_EmailAddress::DOT_ATOM => $this->translate('publish_validation_error_email_invalid'),
        );
        $validator->setMessages($messages);

        $validators[] = $validator;
        return $validators;
    }

    private function _validateInteger() {
        $validators = array();
        $validator = new Zend_Validate_Int();
        $validator->setMessage($this->translate('publish_validation_error_int'), Zend_Validate_Int::NOT_INT);

        $validators[] = $validator;
        return $validators;
    }

    private function _validateLanguage() {
        $validators = array();
        $languages = array_keys($this->getLanguages());

        if (is_null($languages)) {
            return null;
        }

        return $this->validateSelect($languages);
    }

    private function validateSelect($set) {
        $validator = new Zend_Validate_InArray($set);
        $messages = array(
            Zend_Validate_InArray::NOT_IN_ARRAY => $this->translate('publish_validation_error_inarray_notinarray')
        );
        $validator->setMessages($messages);

        $validators[] = $validator;
        return $validators;
    }

    private function _validateLicence() {
        $validators = array();
        $licences = array_keys($this->getLicences());
        if (is_null($licences)) {
            return null;
        }

        return $this->validateSelect($licences);
    }

    private function _validateSeries() {
        $validators = array();
        $series = array_keys($this->getSeries());
        if (is_null($series)) {
            return null;
        }

        return $this->validateSelect($series);
    }

    private function _validateList() {
        $validators = array();
        foreach ($this->listOptions as $option) {
            $this->listOptions[$option] = $option;
        }

        return $this->validateSelect($this->listOptions);
    }

    private function _validateThesis($grantors = null) {
        $validators = array();
        $thesisGrantors = $this->getThesis($grantors);
        if (!is_null($thesisGrantors)) {
            $thesises = array_keys($thesisGrantors);
            if (is_null($thesises)) {
                return null;
            }
            return $this->validateSelect($thesises);
        }
    }

    private function _validateYear() {
        $validators = array();

        $greaterThan = new Zend_Validate_GreaterThan('0000');
        $greaterThan->setMessage(
            $this->translate('publish_validation_error_year_greaterthan'),
            Zend_Validate_GreaterThan::NOT_GREATER
        );
        $validators[] = $greaterThan;

        $validInt = new Zend_Validate_Int();
        $messages = array(
            Zend_Validate_Int::INVALID => $this->translate('publish_validation_error_year_intinvalid'),
            Zend_Validate_Int::NOT_INT => $this->translate('publish_validation_error_year_notint')
        );
        $validInt->setMessages($messages);
        $validators[] = $validInt;

        return $validators;
    }

    public function selectOptions($datatype = null) {
        if (isset($datatype)) {
            $switchVar = $datatype;
        }
        else {
            $switchVar = $this->datatype;
        }

        switch ($switchVar) {
            case 'Collection':
            case 'CollectionLeaf' :
                return $this->_collectionSelect();
                break;

            case 'Language':
                return $this->_languageSelect();
                break;

            case 'Licence':
                return $this->_licenceSelect();
                break;

            case 'List':
                return $this->listOptions;
                break;

            case 'ThesisGrantor' :
                return $this->_thesisSelect(true);
                break;

            case 'ThesisPublisher' :
                return $this->_thesisSelect();
                break;

            case 'Series' :
                return $this->_seriesSelect();
                break;

            default :
                //else no select options required
                break;
        }
    }

    private function _collectionSelect() {
        $collectionRole = Opus_CollectionRole::fetchByName($this->collectionRole);
        if (is_null($collectionRole) || is_null($collectionRole->getRootCollection())) {
            return null;
        }

        if ($collectionRole->getVisible() == '1' && $collectionRole->getRootCollection()->getVisiblePublish() == '1'
                    && $this->hasVisiblePublishChildren($collectionRole)) {
            $children = array();
            $collectionId = $collectionRole->getRootCollection()->getId();
            $collection = new Opus_Collection($collectionId);

            $colls = $collection->getVisiblePublishChildren();

            foreach ($colls as $coll) {
                $children[$coll->getId()] = $coll->getDisplayNameForBrowsingContext($collectionRole);
            }
            return $children;
        }
        return null;
    }

    private function _languageSelect() {
        $languages = $this->getLanguages();
        if (isset($languages) || count($languages) >= 1) {
            asort($languages);
            return $languages;
        }
        return null;
    }

    /**
     * TODO REFACTOR: Function still needed since it does not sort anymore?
     */
    private function _licenceSelect() {
        $licences = $this->getLicences();
        if (isset($licences) && count($licences) >= 1) {
            return $licences;
        }
        return null;
    }

    private function _seriesSelect() {
        $sets = $this->getSeries();
        if (isset($sets) && count($sets) >= 1) {
            return $sets;
        }
        return null;
    }

    private function _thesisSelect($grantors = null) {
        $thesisList = $this->getThesis($grantors);
        if (!is_null($thesisList)) {
            asort($thesisList);
        }
        return $thesisList;
    }

    /**
     * return the available languages from registry, database or chache
     * @return <Array> languages
     */

    private function getLanguages() {
        return Application_Form_Element_Language::getLanguageList();
    }

    /**
     * return the available licences from registry, database or chache
     * @return <Array> languages
     */
    private function getLicences() {
        $licences = array();
        if (empty($this->licences)) {
            foreach ($dbLicences = Opus_Licence::getAll() as $lic) {
                if ($lic->getActive() == '1') {
                    $name = $lic->getDisplayName();
                    $id = $lic->getId();
                    $licences[$id] = $name;
                }
            }
            $this->licences = $licences;
            return $licences;
        }
        else {
            return $this->licences;
        }
    }

    /**
     * return all visible series from database or cache
     * @return <Array> sets
     */
    private function getSeries() {
        $sets = array();
        if (empty($this->series)) {
            foreach ($dbSeries = Opus_Series::getAllSortedBySortKey() as $set) {
                    if ($set->getVisible()) {
                        $title = $set->getTitle();
                        $id = $set->getId();
                        $sets[$id] = $title;
                    }
            }

            $config = Zend_Registry::get('Zend_Config');

            if (isset($config->browsing->series->sortByTitle) && boolval($config->browsing->series->sortByTitle))
            {
                uasort($sets, function ($value1, $value2) {
                    return strnatcmp($value1, $value2);
                });
            }

            $this->series = $sets;
            return $sets;
        }
        else {
            return $this->series;
        }
    }

    /**
     * Retrieves all available ThesisGrantors or ThesisPublishers in a array.
     * Used for generating a select box.
     *
     * @param $grantors true -> get thesis grantors
     *                  null -> get thesis publishers
     * @return Array of Dnb_Institutes Objects
     */
    private function getThesis($grantors = null) {
        $thesises = array();
        if ($grantors === true) {
            $thesises = Opus_DnbInstitute::getGrantors();
        }
        else if (is_null($grantors)) {
            $thesises = Opus_DnbInstitute::getPublishers();
        }
        if (empty($thesises)) {
            return null;
        }

        $thesisList = array();
        foreach ($thesises AS $thesis) {
            $thesisList[$thesis->getId()] = $thesis->getDisplayName();
        }
        return $thesisList;
    }

    private function translate($key) {
        if (is_null($this->_view)) {
            return $key;
        }
        return $this->_view->translate($key);
    }

    /**
     *
     * code taken from Solrsearch_Model_CollectionRoles()
     *
     */
    private function hasVisiblePublishChildren($collectionRole) {
        $rootCollection = $collectionRole->getRootCollection();
        if (is_null($rootCollection)) {
            return false;
        }
        return $rootCollection->hasVisiblePublishChildren();
    }
}

