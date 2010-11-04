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

        $validPersons = $this->_validateAllPersons();

        $validTitles = $this->_validateAllTitles();

        if ($validPersons && $validTitles)
            return true;
        else
            return false;
    }

    /**
     * Checks if filled first names also have a last name
     * @return boolean
     */
    private function _validateAllPersons() {
        $validPersons = true;
        $firstNames = $this->_getPersonFirstNameFields();

        foreach ($firstNames as $key => $name) {
            //$this->log->debug("(Validation): Firstname: " . $key . " with value ". $name);
            if ($name !== "") {
                //if $name is set and not null, find the corresponding lastname
                $lastKey = str_replace('First', 'Last', $key);
                //$this->log->debug("(Validation): Replaced: " . $lastKey);
                if ($this->data[$lastKey] == "" || $this->data[$lastKey] == null) {
                    //error case: Firstname exists but Lastname not
                    $element = $this->form->getElement($lastKey);
                    if (!$element->isRequired())
                        $element->addError('publish_error_noLastButFirstName');
                    $validPersons = false;
                }
            }
        }
        return $validPersons;
    }

    /**
     * Checks if filled titles also have an language.
     * @return boolean
     */
    private function _validateAllTitles() {
        $validTitles = true;

        //1) validate language Fields
        $languages = $this->_getTitleLanguageFields();

        foreach ($languages as $key => $lang) {
            //$this->log->debug("(Validation): language: " . $key . " with value ". $lang);
            if ($lang !== "") {
                //if $lang is set and not null, find the corresponding title
                $titleKey = str_replace('Language', '', $key);
                //$this->log->debug("(Validation): Replaced: " . $titleKey);
                if ($this->data[$titleKey] == "" || $this->data[$titleKey] == null) {
                    //error case: language exists but title not
                    $element = $this->form->getElement($titleKey);
                    if (!$element->isRequired()) {
                        $element->addError('publish_error_noTitleButLanguage');
                        $validTitles = false;
                    }
                }
            }
        }

        //2) validate title fields
        $titles = $this->_getTitleFields();

        foreach ($titles as $key => $title) {
            $this->log->debug("(Validation): Title: " . $key . " with value " . $title);
            if ($title !== "") {
                //if $name is set and not null, find the corresponding lastname
                $lastChar = substr($key, -1, 1);
                if ((int) $lastChar >= 1) {
                    $languageKey = substr($key, 0, strlen($key) - 1) . 'Language' . $lastChar;
                }
                else
                    $languageKey = $key . 'Language';

                $this->log->debug("(Validation): Found: " . $languageKey);
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
                            $element->addError('publish_error_noLanguageButTitle');
                            $validTitles = false;
                        }
                    }
                }
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

}

?>
