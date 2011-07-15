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
 * @author      Doreen Thiede <thiede@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Description of ExtendedValidation
 *
 * @author Susanne Gottwald
 */
class Publish_Model_ExtendedValidation {

    public $form;
    public $data;
    public $log;
    public $session;
    public $documentLanguage;

    public function __construct(Publish_Form_PublishingSecond $form, $data) {
        $this->form = $form;
        $this->data = $data;
        $this->log = Zend_Registry::get('Zend_Log');
        $this->session = new Zend_Session_Namespace('Publish');
    }

    /**
     * Method to trigger the validatation of desired fields: persons, titles...
     * @return <boolean> false = error, else true
     */
    public function validate() {

        if (array_key_exists('Language', $this->data))
            $this->documentLanguage = $this->data['Language'];
        else
            $this->documentLanguage = null;

        $validPersons = $this->_validatePersons();

        $validTitles = $this->_validateTitles();

        $validCheckboxes = $this->_validateCheckboxes();

        $validSubjectLanguages = $this->_validateSubjectLanguages();

        if ($validPersons && $validTitles && $validCheckboxes && $validSubjectLanguages)
            return true;
        else
            return false;
    }

    /**
     * Checks if filled first names also have a last name
     * @return boolean
     */
    private function _validatePersons() {
        //1) validate: for every given first name must exist a last name
        $valid1 = $this->_validateFirstNames();

        //2) validate: for a given email notification checkbox must exist an email
        $valid2 = $this->_validateEmailNotification();

        if ($valid1 && $valid2)
            return true;
        else
            return false;
    }

    /**
     * Checks if there are last names for every filled first name or else there would be an exception from the database.
     * @return boolean true, if yes
     */
    private function _validateFirstNames() {
        $validPersons = true;
        $firstNames = $this->_getPersonFirstNameFields();

        foreach ($firstNames as $key => $name) {
            $this->log->debug("(Validation): Firstname: " . $key . " with value " . $name);
            if ($name !== "") {
                //if $name is set and not null, find the corresponding lastname
                $lastKey = str_replace('First', 'Last', $key);
                $this->log->debug("(Validation): Replaced: " . $lastKey);
                if ($this->data[$lastKey] == "") {
                    //error case: Firstname exists but Lastname not
                    $element = $this->form->getElement($lastKey);
                    if (!$element->isRequired()) {
                        if (!$element->hasErrors()) {
                            $element->addError('publish_error_noLastButFirstName');
                            $validPersons = false;
                        }
                    }
                }
            }
        }
        return $validPersons;
    }

    /**
     * Checks if there are email adresses for a filled checkbox for email notification.
     * @return boolean true, if yes
     */
    private function _validateEmailNotification() {
        $validMails = true;
        $emailNotifications = $this->_getPersonEmailFields();

        foreach ($emailNotifications as $key => $check) {
            $this->log->debug("(Validation): Email Notification: " . $key . " with value " . $check);
            if ($check == "1") {
                //if $check is set and not null, find the corresponding email and name
                $emailKey = str_replace('Allow', '', $key);
                $emailKey = str_replace('Contact', '', $emailKey);
                $lastName = str_replace('Email', 'LastName', $emailKey);
                $firstName = str_replace('Last', 'First', $lastName);
                $titleName = str_replace('LastName', 'AcademicTitle', $lastName);

                $this->log->debug("(Validation): Replaced: " . $emailKey);

                if ($this->data[$lastName] != "" || $this->data[$lastName] != "") {
                    //just check the email if first or last name is given

                    if ($this->data[$emailKey] == "" || $this->data[$emailKey] == null) {
                        //error case: Email Check exists but Email not
                        $element = $this->form->getElement($emailKey);
                        if (!$element->isRequired()) {
                            if (!$element->hasErrors()) {
                                $element->addError('publish_error_noEmailButNotification');
                                $validMails = false;
                            }
                        }
                    }
                }
                else {
                    $this->data[$key] = "";
                    $this->data[$titleName] = "";
                }
            }
        }
        return $validMails;
    }

    /**
     * Retrieves all first names from form data
     * @return <Array> of first names
     */
    private function _getPersonFirstNameFields() {
        $firstNames = array();

        foreach ($this->data as $key => $value) {
            if (strstr($key, 'Person') && strstr($key, 'FirstName'))
                $firstNames[$key] = $value;
        }

        return $firstNames;
    }

    /**
     * Retrieves all first names from form data
     * @return <Array> of first names
     */
    private function _getPersonEmailFields() {
        $emails = array();

        foreach ($this->data as $key => $value) {
            if (strstr($key, 'Person') && strstr($key, 'AllowEmail'))
                $emails[$key] = $value;
        }

        return $emails;
    }

    /**
     * Validate all given titles with different constraint checks.
     * @return <Bool> true, if all checks were positive, else false
     */
    private function _validateTitles() {
        $validTitles = true;

        //1) validate language Fields
        $validate1 = $this->_validateTitleLanguages();

        //2) validate title fields
        $validate2 = $this->_validateTitleValues();

        //3) validate titles per language
        $validate3 = $this->_validateTitlesPerLanguage();

        //4) validate usage of document language for titles
        $validate4 = $this->_validateDocumentLanguageForTitles();

        $validTitles = $validate1 && $validate2 && $validate3;

        return $validTitles;
    }

    /**
     * Checks if filled languages also have an title value.
     * @return boolean
     */
    private function _validateTitleLanguages() {
        $validTitles = true;
        $languages = $this->_getTitleLanguageFields();

        foreach ($languages as $key => $lang) {
            if ($lang !== "") {

                //if $lang is set and not null, find the corresponding title
                $titleKey = str_replace('Language', '', $key);

                if ($this->data[$titleKey] == "" || $this->data[$titleKey] == null) {

                    //error case: language exists but title not
                    $element = $this->form->getElement($titleKey);
                    if (!$element->isRequired()) {
                        if (!$element->hasErrors()) {
                            $element->addError('publish_error_noTitleButLanguage');
                            $validTitles = false;
                        }
                    }
                }
            }
        }
        return $validTitles;
    }

    /**
     * Checks if filled titles also have an language.
     * @return boolean
     */
    private function _validateTitleValues() {
        $validTitles = true;
        $titles = $this->_getTitleFields();

        foreach ($titles as $key => $title) {
            $this->log->debug("(Validation): Title: " . $key . " with value " . $title);
            if ($title !== "") {
                //if $name is set and not null, find the corresponding lastname
                $lastChar = substr($key, -1, 1);

                if ((int) $lastChar >= 1)
                    $languageKey = substr($key, 0, strlen($key) - 1) . 'Language' . $lastChar;
                else
                    $languageKey = $key . 'Language';

                if ($this->data[$languageKey] == "" || $this->data[$languageKey] == null) {
                    //error case: Title exists but Language not
                    $element = $this->form->getElement($languageKey);
                    //set language value to the document language
                    if ($this->documentLanguage != null) {
                        $this->log->debug("(Validation): Set value of " . $languageKey . " to " . $this->documentLanguage);
                        $element->setValue($this->documentLanguage);
                        //store the new value in $data array
                        $this->data[$languageKey] = $this->documentLanguage;
                    }
                    else {
                        //error: no document language set -> throw error message
                        if (!$element->isRequired()) {
                            if (!$element->hasErrors()) {
                                $element->addError('publish_error_noLanguageButTitle');
                                $validTitles = false;
                            }
                        }
                    }
                }
            }
        }

        return $validTitles;
    }

    /**
     * Method counts the same title types per language and throws an error, if there are more than one titles with the same language
     * @return boolean
     */
    private function _validateTitlesPerLanguage() {
        $validTitles = true;
        $titles = $this->_getTitleFields();

        $languagesPerTitleType = array();

        foreach ($titles as $key => $title) {
            $this->log->debug("(Validation): Title: " . $key . " with value " . $title);
            if ($title !== "") {
                //if $title is set and not null, find the corresponding language
                $lastChar = substr($key, -1, 1);
                if ((int) $lastChar >= 1) {
                    $titleType = substr($key, 0, strlen($key) - 1);
                    $languageKey = substr($key, 0, strlen($key) - 1) . 'Language' . $lastChar;
                }
                else {
                    $titleType = $key;
                    $languageKey = $key . 'Language';
                }

                if ($this->data[$languageKey] != "") {
                    //count title types and languages => same languages for same title type must produce an error
                    $index = $titleType . $this->data[$languageKey]; //z.B. TitleSubdeu
                    $this->log->debug("(Validation): language is set, titletype " . $titleType . " and language " . $languageKey . " index = " . $index);

                    if (isset($languagesPerTitleType[$index])) {
                        $languagesPerTitleType[$index] = $languagesPerTitleType[$index] + 1;
                    }
                    else {
                        $languagesPerTitleType[$index] = 1;
                    }

                    if ($languagesPerTitleType[$index] > 1) {
                        $this->log->debug("(Validation): > 1 -> error for element " . $languageKey);
                        $element = $this->form->getElement($languageKey);
                        $element->clearErrorMessages();
                        $element->addError('publish_error_justOneLanguagePerTitleType');
                        $validTitles = false;
                    }
                }
            }
        }
        return $validTitles;
    }

    /**
     * Methods checks if the user entered a title in the specified document language (this is needed for Solr)
     * @return boolean
     */
    private function _validateDocumentLanguageForTitles() {
        $validTitles = true;
        $titles = $this->_getTitleFields();
        if (array_key_exists('Language', $this->data) && $this->data['Language'] !== "")
            $docLanguage = $this->data['Language'];
        else
            return true;

        $titlesWithDocLanguage = array();

        foreach ($titles as $key => $title) {
            $this->log->debug("(Validation): Title: " . $key . " with value " . $title);
            if ($title !== "") {
                //if $title is set and not null, find the corresponding language
                $lastChar = substr($key, -1, 1);
                if ((int) $lastChar >= 1) {
                    $titleType = substr($key, 0, strlen($key) - 1); //z.B. TitleMain
                    $languageKey = substr($key, 0, strlen($key) - 1) . 'Language' . $lastChar;
                }
                else {
                    $titleType = $key;
                    $languageKey = $key . 'Language';
                }

                if (!array_key_exists($titleType, $titlesWithDocLanguage))
                    $titlesWithDocLanguage[$titleType] = 0; // 0 means no doc language

                    if ($this->data[$languageKey] != "" && $this->data[$languageKey] == $docLanguage) {
                    $titlesWithDocLanguage[$titleType] = $titlesWithDocLanguage[$titleType] + 1;
                }
            }
        }

        foreach ($titlesWithDocLanguage as $titleType => $counter) {
            if ($counter == 0) {
                if (array_key_exists($titleType . '1', $this->data))
                    $element = $this->form->getElement($titleType . '1');
                else
                    $element = $this->form->getElement($titleType);
                $element->clearErrorMessages();
                $element->addError('publish_error_TitleInDocumentLanguageIsRequired');
                $validTitles = false;
            }
        }

        return $validTitles;
    }

    /**
     * Retrieves all language fields from form data
     * @return <Array> of languages
     */
    private function _getTitleLanguageFields() {
        $languages = array();

        foreach ($this->data as $key => $value) {
            if (strstr($key, 'Title') && strstr($key, 'Language'))
                $languages[$key] = $value;
        }

        return $languages;
    }

    /**
     * Retrieves all language fields from form data
     * @return <Array> of languages
     */
    private function _getTitleFields() {
        $titles = array();

        foreach ($this->data as $key => $value) {
            if (strstr($key, 'Title') && !strstr($key, 'Language') && !strstr($key, 'Academic'))
                $titles[$key] = $value;
        }

        return $titles;
    }

    /**
     * Checks if filled subjects (swd, uncontrolled) also have an language.
     * @return boolean
     */
    private function _validateSubjectLanguages() {
        $validSubjects = true;
        $subjects = $this->_getSubjectFields();

        foreach ($subjects as $key => $subject) {
            $this->log->debug("(Validation): Subject: " . $key . " with value " . $subject);
            if ($subject !== "") {
                //if $name is set and not null, find the corresponding lastname
                $lastChar = substr($key, -1, 1);

                if ((int) $lastChar >= 1)
                    $languageKey = substr($key, 0, strlen($key) - 1) . 'Language' . $lastChar;
                else
                    $languageKey = $key . 'Language';

                if ($this->data[$languageKey] == "" || $this->data[$languageKey] == null) {
                    //error case: Title exists but Language not
                    $element = $this->form->getElement($languageKey);
                    //set language value to the document language
                    if ($this->documentLanguage != null) {
                        $this->log->debug("(Validation): Set value of " . $languageKey . " to " . $this->documentLanguage);
                        $element->setValue($this->documentLanguage);
                        //store the new value in $data array
                        $this->data[$languageKey] = $this->documentLanguage;
                    }
                    else {
                        //error: no document language set -> throw error message
                        if (!$element->isRequired()) {
                            if (!$element->hasErrors()) {
                                $element->addError('publish_error_noLanguageButTitle');
                                $validSubjects = false;
                            }
                        }
                    }
                }
            }
        }

        return $validSubjects;
    }

    /**
     * Retrieves all language fields from form data
     * @return <Array> of languages
     */
    private function _getSubjectFields() {
        $titles = array();

        foreach ($this->data as $key => $value) {
            if (strstr($key, 'SubjectSwd') || strstr($key, 'SubjectUncontrolled'))
                if (!strstr($key, 'Language'))
                    $titles[$key] = $value;
        }

        return $titles;
    }

    private function _validateCheckboxes() {
        $validCheckboxes = true;
        $checkBoxes = $this->_getRequiredCheckboxes();

        foreach ($checkBoxes AS $box) {
            if ($this->data[$box] === '0') {
                $this->log->debug("(Validation): error for element " . $box);
                $element = $this->form->getElement($box);
                $element->clearErrorMessages();
                $element->addError('publish_error_rights_checkbox_empty');
                $validCheckboxes = false;
            }
        }

        return $validCheckboxes;
    }

    private function _getRequiredCheckboxes() {
        $boxes = array();

        foreach ($this->form->getElements() as $element) {
            if ($element->getType() === 'Zend_Form_Element_Checkbox' && $element->isRequired())
                $boxes[] = $element->getName();
        }

        return $boxes;
    }

}

?>
