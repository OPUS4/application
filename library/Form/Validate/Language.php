<?php
/*
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
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2012, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Checks if a language has already been used for document and field and type.
 *
 * This validation prevents creating two titles or abstracts with the same
 * language. The validation requires
 *
 * document
 * field (TitleMain, ...)
 * language
 *
 * This validator also checks if the language is allowed.
 *
 * TODO distinguish between disabled and invalid language id
 */
class Form_Validate_Language extends Zend_Validate_Abstract {

    /**
     * Error constant for language that has been used already.
     */
    const NOT_AVAILABLE = 'notAvailable';

    /**
     * Error constant for language that has been disabled.
     */
    const NOT_ENABLED = 'notEnabled';

    /**
     * Error constant for language ID that does not exist.
     */
    const NOT_VALID = 'notValid';

    /**
     * Error messages.
     *
     * TOOD figure this out
     */
    protected $_messageTemplates = array(
        self::NOT_AVAILABLE => 'admin_validate_error_language_used',
        self::NOT_VALID => 'admin_validate_error_language_invalid',
        self::NOT_ENABLED => 'admin_validate_error_language_disabled'
    );

    /**
     * Document that is being checked.
     * @var Opus_Document
     */
    private $doc;

    /**
     * Name of field for check.
     * @var string
     */
    private $fieldName;

    /**
     * Constructs validator for checking if language has been used for field.
     *
     * @param type $doc
     * @param type $fieldName
     *
     * TODO throw exception if parameter is null
     */
    public function __construct($options = null) {
        if (isset($options['doc'])) {
            $this->doc = $options['doc'];
        }

        if (isset($options['fieldName'])) {
            $this->fieldName = $options['fieldName'];
        }
    }

    /**
     * Checks if the language has already been used.
     *
     * @param string $value Language identifier (e.g. 'deu' or 'eng')
     * @param hash $context Values of other form elements
     */
    public function isValid($value, $context = null) {
        $value = (string) $value;
        $this->_setValue($value);

        $helper = new Admin_Model_Languages();

        // Check if language ID exists
        $allLanguages = $this->getAllLanguages();

        if (!in_array($value, $allLanguages)) {
            $this->_error(self::NOT_VALID);
            return false;
        }

        // Check if language is enabled
        $languages = $this->getLanguages();

        if (!in_array($value, $languages)) {
            $this->_error(self::NOT_ENABLED);
            return false;
        }

        $fieldName = $this->fieldName;

        // if no fieldName was given
        if (empty($fieldName)) {
            if (!is_null($context)) {
                $fieldName = 'Title' . ucfirst($context['Type']);
            }
        }

        if (!empty($fieldName) && !empty($this->doc)) {
            // Check if language has been used already for field and document
            if ($helper->isLanguageUsed($this->doc, $fieldName, $value)) {
                $this->_error(self::NOT_AVAILABLE);
                return false;
            }
        }

        return true;
    }

    /**
     * Returns all existing language IDs in the system.
     * @return array Language IDs
     */
    private function getAllLanguages() {
        $languages = array();

        $dbLanguages = Opus_Language::getAll();

        if (isset($dbLanguages) || count($dbLanguages) >= 1) {
            foreach ($dbLanguages as $language) {
                $languages[] = $language->getPart2T();
            }
        }

        return $languages;
    }

    /**
     * Returns all active language IDs in the system.
     * @return type
     * TODO there is code for this in Bootstrap and in Validation (publish)
     */
    private function getLanguages() {
        if (Zend_Registry::isRegistered('Available_Languages') === true) {
            $languages = Zend_Registry::get('Available_Languages');
            return array_keys($languages);
        }
        else {
            // TODO do we need to deal with this case
            return array();
        }
    }

}

