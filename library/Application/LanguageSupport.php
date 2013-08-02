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
 * Klasse für das Laden von Übersetzungsressourcen.
 */
class Application_LanguageSupport {

    /**
     * Schlüssel für Zend_Translate in Zend_Registry.
     */
    const REGISTRY_KEY = 'Zend_Translate';

    /**
     * Singleton für das Laden von Übersetzungsressourcen.
     * @var Application_LanguageSupport
     */
    private static $instance = null;

    /**
     * Optionen für Zend_Translate.
     *
     * Muss vor der Verwendung noch um 'log' = Zend_Registry::get('Zend_Log') angereichert werden. Das passiert in
     * $this->getOptions().
     *
     * @var array
     */
    private $options = array(
        'logUntranslated' => true,
        'logMessage' => "Unable to translate key '%message%' into locale '%locale%'",
        'adapter' => Zend_Translate::AN_TMX,
        'locale' => 'auto',
        'clear' => false,
        'scan' => Zend_Translate::LOCALE_FILENAME,
        'ignore' => '.',
        'disableNotices' => true
    );

    /**
     * Array mit bereits geladenen Modulen.
     * @var array
     */
    private $loadedModules = array();

    /**
     * Logger.
     * @var Zend_Log
     */
    private $logger;

    /**
     * Verhindere Konstruktion von Instanzen.
     */
    private function __construct() {
    }

    /**
     * Liefert Instanz von Singleton Application_LanguageSupport zurück.
     * @return Application_LanguageSupport
     */
    public static function getInstance($init = false) {
        if (is_null(self::$instance) || $init) {
            self::$instance = new Application_LanguageSupport();
            self::$instance->init();
        }

        if (!Zend_Registry::isRegistered(self::REGISTRY_KEY)) {
            self::$instance->init();
        }

        return self::$instance;
    }

    /**
     * Erzeugt die Übersetzungsklasse mit den grundlegendsten Übersetzungen.
     */
    public function init() {
        $translate = new Zend_Translate(array_merge(
            array('content' => APPLICATION_PATH . '/modules/default/language/default.tmx'),
            $this->getOptions()));
        $this->loadedModules = array();
        Zend_Registry::set(self::REGISTRY_KEY, $translate);
    }

    /**
     * Lädt die Übersetzungen für ein Modul.
     * @param $name
     */
    public function loadModule($name) {
        if (!in_array($name, $this->loadedModules)) {
            $moduleDir = APPLICATION_PATH . '/modules/' . $name;
            $this->loadLanguageDirectory("$moduleDir/language/");
            $this->loadLanguageDirectory("$moduleDir/language_custom/");
            $this->loadedModules[] = $name;
        }
        else {
            $this->getLogger()->notice("Already loaded translations for module '$name'.");
        }
    }

    /**
     * Lädt TMX Dateien aus einem Verzeichnis.
     *
     * @param string $directory Pfad zum Verzeichnis
     * @return boolean
     */
    public function loadLanguageDirectory($directory) {
        $directory = realpath($directory);
        if (($directory === false) or (!is_dir($directory)) or (!is_readable($directory))) {
            $this->getLogger()->warn(__METHOD__ . " Directory '$directory' not found.");
            return false;
        }

        $handle = opendir($directory);
        if (!$handle) {
            return false;
        }

        if (!Zend_Registry::isRegistered(self::REGISTRY_KEY)) {
            $this->init();
        }

        $translate = Zend_Registry::get(self::REGISTRY_KEY);

        while (false !== ($file = readdir($handle))) {
            // Ignore directories.
            if (!is_file($directory . DIRECTORY_SEPARATOR . $file)) {
                continue;
            }

            // Ignore files with leading dot and files without extension tmx.
            if (preg_match('/^[^.].*\.tmx$/', $file) === 0) {
                continue;
            }

            $translate->addTranslation(array_merge(array(
                'content' => $directory . DIRECTORY_SEPARATOR . $file,
            ), $this->getOptions()));
        }

        return true;
    }

    /**
     * Liefert die Optionen für Zend_Translate.
     * @return array
     */
    public function getOptions() {
        return array_merge(array('log' => $this->getLogger()), $this->options);
    }

    /**
     * Liefert den Logger für diese Klasse.
     * @return Zend_Log
     */
    public function getLogger() {
        if (is_null($this->logger)) {
            $this->logger = Zend_Registry::get('Zend_Log');
        }

        return $this->logger;
    }

    /**
     * Setzt den Logger für diese Klasse.
     */
    public function setLogger($logger) {
        $this->logger = $logger;
    }

}
