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
 * @package     Application
 * @author      Jens Schwidder <schwidder@zib.de>
 * @author      Michael Lang   <lang@zib.de>
 * @copyright   Copyright (c) 2008-2014, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Klasse für das Laden von Übersetzungsressourcen.
 */
class Application_Configuration {

    /**
     * Defaultsprache.
     */
    const DEFAULT_LANGUAGE = 'en';

    /**
     * Logger.
     * @var Zend_Log
     */
    private $_logger = null;

    /**
     * Unterstützte Sprachen.
     * @var array
     */
    private $_supportedLanguages = null;

    /**
     * Is language selection active in user interface.
     */
    private $_languageSelectionEnabled = null;

    /**
     * Path to folder for temporary files.
     * @var string
     */
    private $_tempPath = null;

    /**
     * @var string
     */
    private $defaultLanguage = null;

    /**
     * @var Application_Configuration
     */
    private static $_instance;

    /**
     * Returns instance of class.
     * @return Application_Configuration
     */
    public static function getInstance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new Application_Configuration();
        }

        return self::$_instance;
    }

    /**
     * Liefert den Logger für diese Klasse.
     * @return Zend_Log
     */
    public function getLogger() {
        if (is_null($this->_logger)) {
            $this->_logger = Zend_Registry::get('Zend_Log');
        }

        return $this->_logger;
    }

    /**
     * Setzt den Logger für diese Klasse.
     */
    public function setLogger($logger) {
        $this->_logger = $logger;
    }

    /**
     * Liefert die Konfiguration für Applikation.
     * @return Zend_Config
     */
    public function getConfig() {
        return Zend_Registry::get('Zend_Config');
    }

    /**
     * Returns name of repository.
     * @return string
     */
    public function getName() {
        $config = $this->getConfig();

        if (isset($config->name)) {
            $name = $config->name;
        }
        else {
            $name = 'OPUS 4';
        }

        return $name;
    }

    /**
     * Liefert die Sprachen, die von OPUS unterstützt werden.
     * @return array
     */
    public function getSupportedLanguages() {
        if (is_null($this->_supportedLanguages)) {
            $config = $this->getConfig();
            if (isset($config->supportedLanguages)) {
                $this->_supportedLanguages = explode(",", $config->supportedLanguages);
                $this->getLogger()->debug(
                    Zend_Debug::dump(
                        $this->_supportedLanguages, 'Supported languages ('
                        . count($this->_supportedLanguages) . ')', false
                    )
                );
            }
        }
        return $this->_supportedLanguages;
    }

    /**
     * Prüft, ob eine Sprache unterstützt wird.
     * @param string $language Sprachcode (z.B. 'en')
     * @return bool
     */
    public function isLanguageSupported($language) {
        $languages = $this->getSupportedLanguages();
        return in_array($language, $languages);
    }

    /**
     * Liefert Defaultsprache für Userinterface.
     * @return string
     */
    public function getDefaultLanguage() {
        if (is_null($this->defaultLanguage)) {
            $languages = $this->getSupportedLanguages();
            $this->defaultLanguage = $languages[0];

            if ($this->isLanguageSelectionEnabled()) {
                $locale = new Zend_Locale();
                $language = $locale->getDefault();
                if (is_array($language) and count($language) > 0) {
                    reset($language);
                    $language = key($language);
                } else {
                    $language = self::DEFAULT_LANGUAGE;
                }

                if ($this->isLanguageSupported($language)) {
                    $this->defaultLanguage = $language;
                }
            }
        }

        return $this->defaultLanguage;
    }

    /**
     * Prüft, ob mehr als eine Sprache unterstützt wird.
     * @return bool
     */
    public function isLanguageSelectionEnabled() {
        if (is_null($this->_languageSelectionEnabled)) {
            $this->_languageSelectionEnabled = count($this->getSupportedLanguages()) > 1;
        }
        return $this->_languageSelectionEnabled;
    }

    /**
     * Returns the path to the application workspace.
     *
     * @throws Application_Exception
     */
    public function getWorkspacePath() {
        $config = $this->getConfig();

        if (!isset($config->workspacePath)) {
            $this->getLogger()->err('missing config key workspacePath');
            throw new Application_Exception('missing configuration key workspacePath');
        }

        $workspacePath = $config->workspacePath;

        if (substr($workspacePath, -1) === DIRECTORY_SEPARATOR) {
            return $workspacePath;
        }
        else {
            return $config->workspacePath . DIRECTORY_SEPARATOR;
        }
    }

    /**
     * Returns path to temporary files folder.
     * @return string Path for temporary files.
     * @throws Application_Exception
     */
    public function getTempPath()
    {
        if (is_null($this->_tempPath))
        {
            $this->_tempPath = trim($this->getWorkspacePath() . 'tmp' . DIRECTORY_SEPARATOR);
        }

        return $this->_tempPath;
    }

    /**
     * Set path to folder for temporary files.
     * @param $tempPath
     */
    public function setTempPath($tempPath)
    {
        $this->_tempPath = $tempPath;
    }

    /**
     * Returns path to files folder for document files.
     * @return string Folder for storing document files
     * @throws Application_Exception
     */
    public function getFilesPath() {
        return $this->getWorkspacePath() . 'files' . DIRECTORY_SEPARATOR;
    }

    /**
     * Liest Inhalt von VERSION.txt um die installierte Opusversion zu ermitteln.
     */
    public static function getOpusVersion() {
        $config = Zend_Registry::get('Zend_Config');
        $localVersion = $config->version;
        return (is_null($localVersion)) ? 'unknown' : $localVersion;
    }

    /**
     * Liefert Informationen als Key -> Value Paare in einem Array.
     */
    public static function getOpusInfo() {
        $info = array();
        $info['admin_info_version'] = self::getOpusVersion();
        return $info;
    }

    /**
     * Saves configuration as XML file.
     * @param Zend_Config $config
     * @throws Zend_Config_Exception
     */
    public static function save(Zend_Config $config) {
        $writer = new Zend_Config_Writer_Xml();
        $writer->write(APPLICATION_PATH . '/application/configs/config.xml', $config);
    }

    public static function load() {
    }

    /**
     * Gets a value from a Zend_Config object.
     * @param Zend_Config $config
     * @param $option
     * @return mixed|Zend_Config
     */
    public static function getValueFromConfig(Zend_Config $config, $option) {
        $keys = explode('.', $option);
        $subconfig = $config;
        foreach ($keys as $key) {
            $subconfig = $subconfig->get($key);
            if (!($subconfig instanceof Zend_Config)) {
                break;
            }
        }
        return $subconfig;
    }

    /**
     * Returns value for key in current configuration.
     * @param $key Name of option
     */
    public function getValue($key)
    {
        return self::getValueFromConfig($this->getConfig(), $key);
    }

    /**
     * Updates a value in a Zend_Config object.
     *
     * @param Zend_Config $config
     * @param $option string Name of option
     * @param $value string New value for option
     * @throws Zend_Exception
     */
    public static function setValueInConfig(Zend_Config $config, $option, $value) {
        if ($config->readOnly()) {
            Zend_Registry::get('Zend_Log')->err('Zend_Config object is readonly.');
            return;
        }

        $keys = explode('.', $option);

        $subconfig = $config;

        $index = 0;

        foreach ($keys as $key) {
            $index++;
            if (is_null($subconfig->get($key)) && $index < count($keys)) {
                // create subsection
                eval('$subconfig->' . $key . ' = array();');
                $subconfig = $subconfig->get($key);
            }
            else {
                // set value
                eval('$subconfig->' . $key . ' = $value;');
            }
        }
    }

    /**
     * Removes instance.
     *
     * This is used to reset the configuration to defaults in ini files.
     */
    public static function clearInstance()
    {
        self::$_instance = null;
    }

    /**
     * Returns Zend_Translate instance for application.
     * @return Zend_Translate
     * @throws Zend_Exception
     */
    public function getTranslate()
    {
        return Zend_Registry::get('Zend_Translate');
    }

}
