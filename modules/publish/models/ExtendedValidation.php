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
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\Collection;
use Opus\Common\Model\ModelException;
use Opus\Common\Repository;
use Opus\Common\Series;

class Publish_Model_ExtendedValidation
{
    /** @var Publish_Form_PublishingSecond */
    public $form;

    /** @var array */
    public $data;

    /** @var array */
    public $extendedData = [];

    /** @var Zend_Log */
    public $log;

    /** @var Zend_Session_Namespace */
    public $session;

    /** @var string */
    public $documentLanguage;

    /**
     * @param Publish_Form_PublishingSecond $form
     * @param array                         $data
     * @param Zend_Log                      $log
     * @param Zend_Session_Namespace        $session
     */
    public function __construct($form, $data, $log, $session)
    {
        $this->form    = $form;
        $this->data    = $data;
        $this->log     = $log;
        $this->session = $session;
        $this->initializeExtendedData();
    }

    private function initializeExtendedData()
    {
        foreach ($this->data as $key => $value) {
            $element = $this->form->getElement($key);
            if ($element !== null) {
                $this->extendedData[$key] = [
                    'value'    => $value,
                    'datatype' => $element->getAttrib('datatype'),
                    'subfield' => $element->getAttrib('subfield'),
                ];
            }
        }
    }

    /**
     * Method to trigger the validatation of desired fields: persons, titles...
     *
     * @return bool false = error, else true
     */
    public function validate()
    {
        if (array_key_exists('Language', $this->data)) {
            $this->documentLanguage = $this->data['Language'];
        } else {
            $this->documentLanguage = null;
        }

        $result = $this->validatePersons();
        $result = $this->validateTitles() && $result;
        $result = $this->validateCheckboxes() && $result;
        $result = $this->validateSubjectLanguages() && $result;
        $result = $this->validateCollectionLeafSelection() && $result;
        $result = $this->validateSeriesNumber() && $result;
        $result = $this->validateSeries() && $result;
        $result = $this->validateURN() && $result;
        $result = $this->validateDoi() && $result;
        return $result;
    }

    /**
     * Checks if filled first names also have a last name
     *
     * @return bool
     */
    private function validatePersons()
    {
        //1) validate: no first name without a last name
        $validFirstName = $this->validateFirstNames();

        //2) validate: no email without a name
        $validEmail = $this->validateEmail();

        //3) validate: no checkbox without mail
        $validEmailNotification = $this->validateEmailNotification();

        return $validFirstName && $validEmail && $validEmailNotification;
    }

    /**
     * Checks if there are last names for every filled first name or else there would be an exception from the database.
     *
     * @return bool true, if yes
     */
    private function validateFirstNames()
    {
        $validPersons = true;
        $firstNames   = $this->getPersonFirstNameFields();

        foreach ($firstNames as $key => $name) {
            $this->log->debug(__METHOD__ . " : Firstname: " . $key . " with value " . $name);
            if ($name !== '') {
                //if $name is set and not null, find the corresponding lastname
                $lastKey = str_replace('First', 'Last', $key);
                $this->log->debug(__METHOD__ . ' : Replaced: ' . $lastKey);
                if ($this->data[$lastKey] === '') {
                    //error case: Firstname exists but Lastname not
                    $element = $this->form->getElement($lastKey);
                    if (! $element->isRequired()) {
                        if (! $element->hasErrors()) {
                            $element->addError($this->translate('publish_error_noLastButFirstName'));
                            $validPersons = false;
                        }
                    }
                }
            }
        }
        return $validPersons;
    }

    /**
     * @return bool
     */
    private function validateEmail()
    {
        $validMails = true;
        $emails     = $this->getPersonEmailFields();

        foreach ($emails as $key => $mail) {
            $this->log->debug(__METHOD__ . ' : Email: ' . $key . ' with value ' . $mail);
            if ($mail !== "") {
                //if email is not null, find the corresponding first and last name
                $lastName = str_replace('Email', 'LastName', $key);

                if ($this->data[$lastName] === '') {
                    //error case: Email exists but Last name not
                    $element = $this->form->getElement($lastName);
                    if (! $element->isRequired()) {
                        if (! $element->hasErrors()) {
                            $element->addError($this->translate('publish_error_noLastNameButEmail'));
                            $validMails = false;
                        }
                    }
                }
            }
        }

        return $validMails;
    }

    /**
     * Checks if there are email adresses for a filled checkbox for email notification.
     *
     * @return bool true, if yes
     */
    private function validateEmailNotification()
    {
        $validMails         = true;
        $emailNotifications = $this->getPersonEmailNotificationFields();

        foreach ($emailNotifications as $key => $check) {
            $this->log->debug(__METHOD__ . ' : Email Notification: ' . $key . ' with value ' . $check);
            if ($check) {
                //if $check is set and not null, find the corresponding email and name
                $emailKey  = str_replace('Allow', '', $key);
                $emailKey  = str_replace('Contact', '', $emailKey);
                $lastName  = str_replace('Email', 'LastName', $emailKey);
                $firstName = str_replace('Last', 'First', $lastName);
                $titleName = str_replace('LastName', 'AcademicTitle', $lastName);

                $this->log->debug(__METHOD__ . " : Replaced: " . $emailKey);

                if ($this->data[$lastName] !== '' || $this->data[$firstName] !== '') {
                    //just check the email if first or last name is given

                    if ($this->data[$emailKey] === '' || $this->data[$emailKey] === null) {
                        //error case: Email Check exists but Email not
                        $element = $this->form->getElement($emailKey);
                        if (! $element->isRequired()) {
                            if (! $element->hasErrors()) {
                                $element->addError($this->translate('publish_error_noEmailButNotification'));
                                $validMails = false;
                            }
                        }
                    }
                } else {
                    $this->data[$key]       = '';
                    $this->data[$titleName] = '';
                }
            }
        }
        return $validMails;
    }

    /**
     * Retrieves all first names from form data
     *
     * @return array Of first names
     */
    private function getPersonFirstNameFields()
    {
        $firstNames = [];

        foreach ($this->extendedData as $name => $entry) {
            if ($entry['datatype'] === 'Person' && $entry['subfield'] && strstr($name, 'First')) {
                $firstNames[$name] = $entry['value'];
            }
        }

        return $firstNames;
    }

    /**
     * @return array
     */
    private function getPersonEmailFields()
    {
        $emails = [];

        foreach ($this->extendedData as $name => $entry) {
            if (
                $entry['datatype'] === 'Person' && $entry['subfield'] && strstr($name, 'Email')
                && ! strstr($name, 'Allow')
            ) {
                $emails[$name] = $entry['value'];
            }
        }

        return $emails;
    }

    /**
     * @return array
     */
    private function getPersonEmailNotificationFields()
    {
        $emails = [];

        foreach ($this->extendedData as $name => $entry) {
            if ($entry['datatype'] === 'Person' && $entry['subfield'] && strstr($name, 'AllowEmailContact')) {
                $emails[$name] = $entry['value'];
            }
        }

        return $emails;
    }

    /**
     * Validate all given titles with different constraint checks.
     *
     * @return bool true, if all checks were positive, else false
     */
    private function validateTitles()
    {
        //1) validate language Fields
        $validTitleLang = $this->validateTitleLanguages();

        //2) validate title fields
        $validTitleVal = $this->validateTitleValues();

        //3) validate titles per language
        $validTitlePerLang = $this->validateTitlesPerLanguage();

        //4) validate usage of document language for main titles
        $validDocLangForMainTitle = $this->validateDocumentLanguageForMainTitles();

        return $validTitleLang && $validTitleVal && $validTitlePerLang && $validDocLangForMainTitle;
    }

    /**
     * Checks if filled languages also have an title value.
     *
     * @return bool
     */
    private function validateTitleLanguages()
    {
        $validTitles = true;
        $languages   = $this->getTitleLanguageFields();

        foreach ($languages as $key => $lang) {
            if ($lang !== '') {
                //if $lang is set and not null, find the corresponding title
                $titleKey = str_replace('Language', '', $key);
                if ($this->data[$titleKey] === '' || $this->data[$titleKey] === null) {
                    //error case: language exists but title not
                    $element = $this->form->getElement($titleKey);
                    if (! $element->isRequired()) {
                        if (! $element->hasErrors()) {
                            $element->addError($this->translate('publish_error_noTitleButLanguage'));
                            $validTitles = false;
                        }
                    }
                }
            }
        }
        return $validTitles;
    }

    /**
     * Fills empty title languages with current document language
     *
     * @return true
     */
    private function validateTitleValues()
    {
        $titles = $this->getTitleFields();

        foreach ($titles as $key => $title) {
            if (! empty($title)) {
                $counter = $this->getCounterOrType($key);

                if ($counter !== null) {
                    $titleType   = $this->getCounterOrType($key, 'type');
                    $languageKey = $titleType . 'Language_' . $counter;
                } else {
                    $titleType   = $key;
                    $languageKey = $key . 'Language';
                }
                $this->checkLanguageElement($languageKey);
            }
        }
        return true;
    }

    /**
     * Method counts the same title types per language and throws an error, if there are more than one titles with the
     * same language
     *
     * @return bool
     */
    private function validateTitlesPerLanguage()
    {
        $validTitles = true;
        $titles      = $this->getTitleFields();

        $languagesPerTitleType = [];

        foreach ($titles as $key => $title) {
            $this->log->debug(__METHOD__ . ' : Title: ' . $key . ' with value ' . $title);
            if ($title !== "") {
                $counter = $this->getCounterOrType($key);
                if ($counter !== null) {
                    $titleType   = $this->getCounterOrType($key, 'type');
                    $languageKey = $titleType . 'Language_' . $counter;
                } else {
                    $titleType   = $key;
                    $languageKey = $key . 'Language';
                }

                if ($this->data[$languageKey] !== '') {
                    //count title types and languages => same languages for same title type must produce an error
                    $index = $titleType . $this->data[$languageKey]; //z.B. TitleSubdeu
                    $this->log->debug(
                        __METHOD__ . ' : language is set, titletype ' . $titleType . ' and language ' . $languageKey
                        . ' index = ' . $index
                    );

                    if (isset($languagesPerTitleType[$index])) {
                        $languagesPerTitleType[$index] += 1;
                    } else {
                        $languagesPerTitleType[$index] = 1;
                    }

                    if ($languagesPerTitleType[$index] > 1) {
                        $this->log->debug(__METHOD__ . ' : > 1 -> error for element ' . $languageKey);
                        $element = $this->form->getElement($languageKey);
                        $element->clearErrorMessages();
                        $element->addError($this->translate('publish_error_justOneLanguagePerTitleType'));
                        $validTitles = false;
                    }
                }
            }
        }
        return $validTitles;
    }

    /**
     * Methods checks if the user entered a main title in the specified document language (this is needed for Solr)
     *
     * @return bool
     */
    private function validateDocumentLanguageForMainTitles()
    {
        $validTitles = true;
        $titles      = $this->getTitleMainFields();
        $languages   = $this->getTitleMainLanguageFields();
        if (array_key_exists('Language', $this->data) && $this->data['Language'] !== '') {
            $docLanguage = $this->data['Language'];
        } else {
            return true;
        }

        $i = 0;

        foreach ($languages as $title => $lang) {
            if ($lang === $docLanguage) {
                $i++;
            }
        }

        if ($i === 0) {
            $titles  = array_keys($titles);
            $element = $this->form->getElement($titles[0]);
            $element->clearErrorMessages();
            $element->addError($this->translate('publish_error_TitleInDocumentLanguageIsRequired'));
            $validTitles = false;
        }

        return $validTitles;
    }

    /**
     * Retrieves all title language fields from form data
     *
     * @return array Languages
     */
    private function getTitleLanguageFields()
    {
        $languages = [];

        foreach ($this->extendedData as $name => $entry) {
            if ($entry['datatype'] === 'Title' && $entry['subfield'] && strstr($name, 'anguage')) {
                $languages[$name] = $entry['value'];
            }
        }

        return $languages;
    }

    /**
     * Retrieves all title fields from form data
     *
     * @return array Titles
     */
    private function getTitleFields()
    {
        $titles = [];

        foreach ($this->extendedData as $name => $entry) {
            if ($entry['datatype'] === 'Title' && ! $entry['subfield']) {
                $titles[$name] = $entry['value'];
            }
        }

        return $titles;
    }

    /**
     * Retrieves all title main fields from form data
     *
     * @return array Main titles
     */
    private function getTitleMainFields()
    {
        $titles = [];
        foreach ($this->extendedData as $name => $entry) {
            //find only TitleMain fields: datatype=Title, no subfield, 'main' must be present in name
            $fieldname = strtolower($name);
            if ($entry['datatype'] === 'Title' && ! $entry['subfield'] && strstr($fieldname, 'main')) {
                $titles[$name] = $entry['value'];
            }
        }

        return $titles;
    }

    /**
     * Retrieves all title main language fields from form data
     *
     * @return array Languages
     */
    private function getTitleMainLanguageFields()
    {
        $titles = [];

        foreach ($this->extendedData as $name => $entry) {
            $fieldname = strtolower($name);
            //find only TitleMainLanguage fields: datatype=Language, subfield, 'main' must be present in name
            if ($entry['datatype'] === 'Language' && $entry['subfield'] && strstr($fieldname, 'main')) {
                if (empty($entry['value'])) {
                    $entry['value'] = $this->documentLanguage;
                }
                $titles[$name] = $entry['value'];
            }
        }

        return $titles;
    }

    /**
     * Fills empty language subject fields with current document language
     *
     * @return true
     */
    private function validateSubjectLanguages()
    {
        $subjects = $this->getSubjectFields();

        foreach ($subjects as $key => $subject) {
            if (! empty($subject)) {
                $counter = $this->getCounterOrType($key);

                if ($counter !== null) {
                    $titleType   = $this->getCounterOrType($key, 'type');
                    $languageKey = $titleType . 'Language_' . $counter;
                } else {
                    $titleType   = $key;
                    $languageKey = $key . 'Language';
                }
                $this->checkLanguageElement($languageKey);
            }
        }

        return true;
    }

    /**
     * @param string $dataKey
     * @param string $method
     * @return mixed|null
     *
     * TODO Prüfen ob dieser "Doppelpack" Sinn macht. Wären zwei Funktionen
     * getCounter und getType nicht vielleicht lesbarer, bzw. es scheint, daß
     * die beiden Varianten fast immer zusammen verwendet werden. Vielleicht
     * sollte die Funktion einfach ein Array mit den Componenten zurückliefern.
     *
     * TODO BUG return handling, design, ...
     */
    private function getCounterOrType($dataKey, $method = 'counter')
    {
        if (! strstr($dataKey, '_')) {
            return null;
        }

        $array = explode('_', $dataKey);
        $i     = count($array);

        if ($method === 'counter') {
            return $array[$i - 1];
        }

        if ($method === 'type') {
            return $array[0];
        }

        return null;
    }

    /**
     * @param string $languageKey
     * @return true
     */
    private function checkLanguageElement($languageKey)
    {
        if (
            ! array_key_exists($languageKey, $this->data) || $this->data[$languageKey] === null
            || empty($this->data[$languageKey])
        ) {
            //set language value to the document language
            if ($this->documentLanguage !== null) {
                $this->data[$languageKey] = $this->documentLanguage;
            }
        }
        return true;
    }

    /**
     * Retrieves all language fields from form data
     *
     * @return array Languages
     */
    private function getSubjectFields()
    {
        $titles = [];

        foreach ($this->extendedData as $name => $entry) {
            if ($entry['datatype'] === 'Subject' && ! $entry['subfield'] && strstr($name, 'ncontrolled')) {
                $titles[$name] = $entry['value'];
            }
        }

        return $titles;
    }

    /**
     * @return bool
     */
    private function validateCheckboxes()
    {
        $validCheckboxes = true;
        $checkBoxes      = $this->getRequiredCheckboxes();

        foreach ($checkBoxes as $box) {
            if (! $this->data[$box]) {
                $this->log->debug(__METHOD__ . " : error for element " . $box);
                $element = $this->form->getElement($box);
                $element->clearErrorMessages();
                $element->addError($this->translate('publish_error_rights_checkbox_empty'));
                $validCheckboxes = false;
            }
        }

        return $validCheckboxes;
    }

    /**
     * @return array
     */
    private function getRequiredCheckboxes()
    {
        $boxes = [];

        foreach ($this->form->getElements() as $element) {
            if ($element->getType() === 'Zend_Form_Element_Checkbox' && $element->isRequired()) {
                $boxes[] = $element->getName();
            }
        }

        return $boxes;
    }

    /**
     * @return array
     */
    public function getValidatedValues()
    {
        return $this->data;
    }

    /**
     * @return bool
     */
    public function validateCollectionLeafSelection()
    {
        $collectionLeafSelection = true;
        $elements                = $this->form->getElements();

        foreach ($elements as $element) {
            /** @var Zend_Form_Element $element */
            if ($element->getAttrib('collectionLeaf') !== true) {
                continue;
            }

            $elementName = $element->getName();
            if (isset($this->session->additionalFields['step' . $elementName])) {
                $step = $this->session->additionalFields['step' . $elementName];
                if ($step >= 2) {
                    $element = $this->form->getElement('collId' . $step . $elementName);
                }
            }

            $matches = [];
            $value   = $element->getValue();
            if ($value === null || preg_match('/^(\d+)$/', $value, $matches) === 0) {
                continue;
            }

            $collId = $matches[1];

            if (isset($collId)) {
                $coll = null;
                try {
                    $coll = Collection::get($collId);
                } catch (ModelException $e) {
                    $this->log->err("could not instantiate Opus_Collection with id $collId", $e);
                    $collectionLeafSelection = false;
                }

                if ($coll !== null && $coll->hasChildren()) {
                    if (isset($element)) {
                        $element->clearErrorMessages();
                        $element->addError($this->translate('publish_error_collection_leaf_required'));
                        $collectionLeafSelection = false;
                    }
                }
            }
        }
        return $collectionLeafSelection;
    }

    /**
     * @return bool
     */
    private function validateSeriesNumber()
    {
        $validSeries = true;
        $series      = $this->fetchSeriesFields();

        // in $series befinden sich auch die nicht vom Benutzer ausgefüllten Felder
        $seriesWithoutDefaults = [];
        foreach ($series as $key => $value) {
            if ($value !== '') {
                $seriesWithoutDefaults[$key] = $value;
            }
        }

        if (count($seriesWithoutDefaults) === 0) {
            return true; // es wurden keine Schriftenreihen / Bandnummern ausgewählt / eingegeben
        }

        // prüfe, ob zu jedem Series-Select eine zugehörige Bandnummer existiert und umgekehrt
        foreach ($seriesWithoutDefaults as $seriesElement => $value) {
            if (strpos($seriesElement, 'Series_') === 0) {
                // Schriftenreihe gefunden: zugehörige Bandnummer erwartet
                $key            = str_replace('Series_', 'SeriesNumber_', $seriesElement);
                $errorMsgPrefix = 'seriesnumber';
            } elseif (strpos($seriesElement, 'SeriesNumber_') === 0) {
                // Bandnummer gefunden: zugehörige Schriftenreihe erwartet
                $key            = str_replace('SeriesNumber_', 'Series_', $seriesElement);
                $errorMsgPrefix = 'seriesselect';
            } else {
                $this->log->warn(__METHOD__ . " unbekanntes Schriftenreihen-Formularfeld: " . $seriesElement);
                continue;
            }

            if (! array_key_exists($key, $seriesWithoutDefaults)) {
                // Mismatch gefunden: Validierungsfehlermeldung ausgeben
                $element = $this->form->getElement($key);
                if ($element !== null) {
                    $element->clearErrorMessages();
                    $element->addError($this->translate('publish_error_series_missing_' . $errorMsgPrefix));
                }
                $this->log->debug(__METHOD__ . " Feld $seriesElement ohne zugehöriges Feld $key");
                $validSeries = false;
            }
        }

        foreach ($series as $fieldname => $number) {
            if (strpos($fieldname, 'SeriesNumber_') === 0 && $number !== '') {
                $selectFieldName = str_replace('SeriesNumber_', 'Series_', $fieldname);
                if (array_key_exists($selectFieldName, $this->data)) {
                    $selectFieldValue = $this->data[$selectFieldName];

                    $matches = [];
                    if (preg_match('/^(\d+)$/', $selectFieldValue, $matches) === 0) {
                        continue;
                    }

                    $seriesId   = $matches[1];
                    $currSeries = null;
                    try {
                        $currSeries = Series::get($seriesId);
                    } catch (ModelException $e) {
                        $this->log->err(__METHOD__ . " could not instantiate Opus\Series with id $seriesId", $e);
                        $validSeries = false;
                    }

                    /** TODO optionally supress duplicate series number values OPUSVIER-3917
                    if ($currSeries !== null && ! $currSeries->isNumberAvailable($number)) {
                        $this->log->debug(
                            __METHOD__ . " : error for element $fieldname : serial number $number not available"
                        );
                        $element = $this->form->getElement($fieldname);
                        if ($element !== null) {
                            $element->clearErrorMessages();
                            $element->addError($this->translate('publish_error_seriesnumber_not_available'));
                        }
                        $validSeries = false;
                    }*/
                }
            }
        }

        return $validSeries;
    }

    /**
     * @return bool
     */
    private function validateSeries()
    {
        $validSeries = true;
        $series      = $this->fetchSeriesFields(false);
        $countSeries = [];

        foreach ($series as $fieldname => $option) {
            $matches = [];
            if (preg_match('/^(\d+)$/', $option, $matches) === 0) {
                continue;
            }

            $seriesId = $matches[1];

            //count how often the same series id has to be stored for the same document
            if (isset($countSeries[$seriesId])) {
                $countSeries[$seriesId] += 1;
            } else {
                $countSeries[$seriesId] = 1;
            }

            if ($countSeries[$seriesId] > 1) {
                $this->log->debug(
                    __METHOD__ . " : error for element $fieldname : is used " . $countSeries[$seriesId]
                    . ' times'
                );
                $element = $this->form->getElement($fieldname);
                if ($element !== null) {
                    $element->clearErrorMessages();
                    $element->addError($this->translate('publish_error_only_one_series_per_document'));
                }
                $validSeries = false;
            }
        }

        return $validSeries;
    }

    /**
     * prevent URN collisions: check that given URN is unique (in our database)
     *
     * @return bool
     */
    private function validateURN()
    {
        if (! array_key_exists('IdentifierUrn', $this->extendedData)) {
            return true;
        }

        $urn   = $this->extendedData['IdentifierUrn'];
        $value = $urn['value'];
        if (trim($value) === '') {
            return true;
        }

        // check URN $urn for collision
        $finder = Repository::getInstance()->getDocumentFinder();
        $finder->setIdentifierValue('urn', $value);
        if ($finder->getCount() === 0) {
            return true;
        }

        $element = $this->form->getElement('IdentifierUrn');
        if ($element !== null) {
            $element->clearErrorMessages();
            $element->addError($this->translate('publish_error_urn_collision'));
        }
        return false;
    }

    /**
     * Prevent DOI collisions.
     *
     * @return bool
     */
    private function validateDoi()
    {
        if (! array_key_exists('IdentifierDoi', $this->extendedData)) {
            return true;
        }

        $doi   = $this->extendedData['IdentifierDoi'];
        $value = $doi['value'];

        if (trim($value) === '') {
            return true;
        }

        $finder = Repository::getInstance()->getDocumentFinder();
        $finder->setIdentifierValue('doi', $value);

        if ($finder->getCount() === 0) {
            return true;
        }

        $element = $this->form->getElement('IdentifierDoi');

        if ($element !== null) {
            $element->clearErrorMessages();
            $element->addError($this->translate('publish_error_doi_collision'));
        }

        return false;
    }

    /**
     * Fetch the transmitted series numbers or the series selection fields.
     *
     * @param bool $fetchNumbers
     * @return array Array of series numbers
     */
    private function fetchSeriesFields($fetchNumbers = true)
    {
        $series = [];

        foreach ($this->extendedData as $name => $entry) {
            if ($entry['datatype'] === 'SeriesNumber' && $fetchNumbers) {
                $series[$name] = $entry['value'];
            } else {
                if ($entry['datatype'] === 'Series') {
                    $series[$name] = $entry['value'];
                }
            }
        }

        return $series;
    }

    /**
     * @param string $key
     * @return string
     */
    private function translate($key)
    {
        return $this->form->view->translate($key);
    }
}
