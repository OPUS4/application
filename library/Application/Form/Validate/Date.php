<?PHP
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
 * @package     Form_Validate
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Validiert Datumseingaben.
 */
class Application_Form_Validate_Date extends Zend_Validate_Date {

    /**
     * Regex pattern for valid date input.
     * @var string
     */
    private $_inputPattern;

    /**
     * Date formats and input patterns used by Opus.
     * @var array
     */
    private static $_dateFormats = array(
        'de' => array(
            'format' => 'dd.MM.yyyy',
            'regex' => '#^[0-9]{1,2}\.[0-9]{1,2}\.[0-9]{1,4}$#'
        ),
        'en' => array(
            'format' => 'yyyy/MM/dd',
            'regex' => '#^[0-9]{1,4}/[0-9]{1,2}/[0-9]{1,2}$#'
        )
    );

    /**
     * Constructs Application_Form_Validate_Date class for validating date input.
     * @param array $config Configuration options (see Zend_Validate_Date)
     */
    public function __construct($config = null) {
        parent::__construct($config);
        // automatically set date format used by Opus
        $this->setFormat($this->getDateFormat());
        $this->setInputPattern($this->getDatePattern());

        $this->setMessages(
            array(
                Zend_Validate_Date::INVALID => 'validation_error_date_invalid',
                Zend_Validate_Date::INVALID_DATE => 'validation_error_date_invaliddate',
                Zend_Validate_Date::FALSEFORMAT => 'validation_error_date_falseformat'
            )
        );
    }

    /**
     * Modified function validates input pattern.
     * @param string $value
     * @return boolean - True only if date input is valid for Opus requirements
     */
    public function isValid($value) {
        $this->_setValue($value);
        // Check first if input matches expected pattern
        $datePattern = $this->getInputPattern();
        $validator = new Zend_Validate_Regex($datePattern);
        if (!$validator->isValid($value)) {
            $this->_error(Zend_Validate_Date::FALSEFORMAT);
            return false;
        }

        // Perform check in parent class
        return parent::isValid($value);
    }

    /**
     * Returns input pattern that was set or default input pattern for locale.
     * @return string Regex input pattern for dates
     */
    public function getInputPattern() {
        if (empty($this->_inputPattern)) {
            return $this->getDatePattern();
        }
        else {
            return $this->_inputPattern;
        }
    }

    /**
     * Sets the expected input pattern for dates.
     * @param string $pattern Regex input pattern
     */
    public function setInputPattern($pattern) {
        $this->_inputPattern = $pattern;
    }

    /**
     * Sets locale and updated input format automatically.
     * @param Zend_Locale $locale
     */
    public function setLocale($locale = null) {
        parent::setLocale($locale);
        if ($locale instanceof Zend_Locale) {
            $dateFormat = $this->getDateFormat($locale->getLanguage());
            $inputPattern = $this->getDatePattern($locale->getLanguage());
        }
        else {
            $dateFormat = $this->getDateFormat($locale);
            $inputPattern = $this->getDatePattern($locale);
        }
        $this->setFormat($dateFormat);
        $this->setInputPattern($inputPattern);
    }

    /**
     * Returns date format string for selected language in session.
     * @return string Date format string
     */
    public function getDateFormat($locale = null) {
        if (empty($locale)) {
            $session = new Zend_Session_Namespace();
            $language = $session->language;
        }
        else {
            $language = $locale;
        }

        return $this->__getDateFormatForLocale($language);
    }

    /**
     * Returns date format pattern for selected language in session.
     * @return string Input pattern for dates
     */
    public function getDatePattern($locale = null) {
        if (empty($locale)) {
            $session = new Zend_Session_Namespace();
            $language = $session->language;
        }
        else {
            $language = $locale;
        }
        return $this->__getDatePatternForLocale($language);
    }

    /**
     * Returns date format for locale or default format.
     * @param string $locale Locale string like 'de'
     * @return string Date format for locale
     */
    private function __getDateFormatForLocale($locale) {
        if (array_key_exists($locale, self::$_dateFormats)) {
            return self::$_dateFormats[$locale]['format'];
        }
        else {
            return self::$_dateFormats['en']['format'];
        }
    }

    /**
     * Returns date input pattern for locale or default input pattern.
     * @param string $locale Locale string like 'de'
     * @return string Date input pattern for locale
     */
    private function __getDatePatternForLocale($locale) {
        if (array_key_exists($locale, self::$_dateFormats)) {
            return self::$_dateFormats[$locale]['regex'];
        }
        else {
            return self::$_dateFormats['en']['regex'];
        }
    }

}
