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
 * @copyright   Copyright (c) 2008-2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Erweiterung von Zend_Translate, um Übersetzungsressourcen für Module zu laden.
 * 
 * Zend_Translate wid in den Zend Komponenten verwendet und in der Zend_Registry normalerweise unter 'Zend_Translate'
 * gespeichert. Mit den Erweiterungen können an beliebigen Stellen problemlos weitere Übersetzungsdateien geladen
 * werden.
 */
class Application_Translate extends Zend_Translate {

    /**
     * Schlüssel für Zend_Translate in Zend_Registry.
     */
    const REGISTRY_KEY = 'Zend_Translate';
    
    /**
     * Array mit bereits geladenen Modulen.
     * @var array
     */
    private $_loadedModules = array();
    
    /**
     * Logger.
     * @var Zend_Log
     */
    private $_logger;
    
    /**
     * Optionen für Zend_Translate.
     *
     * @var array
     */
   private $_options = array(
        'logMessage' => "Unable to translate key '%message%' into locale '%locale%'",
        'logPriority' => Zend_Log::DEBUG,
        'adapter' => Zend_Translate::AN_TMX,
        'locale' => 'auto',
        'clear' => false,
        'scan' => Zend_Translate::LOCALE_FILENAME,
        'ignore' => '.',
        'disableNotices' => true
    );    
      
    /**
     * Konstruiert Klasse für Übersetzungen in OPUS Applikation.
     */
    public function __construct($options = null) {
        $options = (!is_null($options)) ?  array_merge($this->getOptions(), $options) : $this->getOptions();
        parent::__construct($options);
    }
    
    /**
     * Lädt die Übersetzungen für ein Modul.
     * @param $name
     */
    public function loadModule($name) {
        if (!in_array($name, $this->_loadedModules)) {
            $moduleDir = APPLICATION_PATH . '/modules/' . $name;
            $this->loadLanguageDirectory("$moduleDir/language/", false);
            $this->loadLanguageDirectory("$moduleDir/language_custom/", false);
            $this->_loadedModules[] = $name;
        }
        else {
            $this->getLogger()->notice("Already loaded translations for module '$name'.");
        }
    }

    /**
     * Lädt TMX Dateien aus einem Verzeichnis.
     *
     * @param string $directory Pfad zum Verzeichnis
     * @param string $warnIfMissing Optionally warn in log file if folder is missing
     * @return boolean
     *
     * TODO better than supressing the warning would be for each module to register language directories in bootstrap
     */
    public function loadLanguageDirectory($directory, $warnIfMissing = true) {
        $path = realpath($directory);

        if (($path === false) or (!is_dir($path)) or (!is_readable($path))) {
            if ($warnIfMissing)
            {
                $this->getLogger()->warn(__METHOD__ . " Directory '$directory' not found.");
            }
            return false;
        }

        $handle = opendir($path);
        if (!$handle) {
            return false;
        }

        while (false !== ($file = readdir($handle))) {
            // Ignore directories.
            if (!is_file($path . DIRECTORY_SEPARATOR . $file)) {
                continue;
            }

            // Ignore files with leading dot and files without extension tmx.
            if (preg_match('/^[^.].*\.tmx$/', $file) === 0) {
                continue;
            }
            
            $options = array_merge(
                array('content' => $path . DIRECTORY_SEPARATOR . $file),
                $this->getOptions()
            );

            $this->addTranslation($options);
        }
        
        return true;
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
     * Liefert die Optionen für Zend_Translate.
     * @return array
     */
    public function getOptions() {
        $options = array_merge(
            $this->_options, array(
            'log' => $this->getLogger(),
            'logUntranslated' => $this->isLogUntranslatedEnabled()    
            )
        );
        return $options;
    }
    
    /**
     * 
     * @return type
     */
    public function isLogUntranslatedEnabled() {
        $config = Zend_Registry::get('Zend_Config');
        return (isset($config->log->untranslated)) ? (bool)$config->log->untranslated : false; 
    }
    
}