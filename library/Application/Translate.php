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

use Opus\Common\Config;
use Opus\Common\LoggingTrait;
use Opus\Translate\Dao;
use Opus\Translate\DatabaseAdapter;

/**
 * Erweiterung von Zend_Translate, um Übersetzungsressourcen für Module zu laden.
 *
 * Zend_Translate wid in den Zend Komponenten verwendet und in der Zend_Registry normalerweise unter 'Zend_Translate'
 * gespeichert. Mit den Erweiterungen können an beliebigen Stellen problemlos weitere Übersetzungsdateien geladen
 * werden.
 *
 * Normally all translations from all modules should be loaded at startup, because modules can use classes from other
 * modules. Loading the translations for a module only when a request is directed at that module might not load all the
 * necessary translations if this module uses resources from another module that has not been loaded.
 */
class Application_Translate extends Zend_Translate
{
    use LoggingTrait;

    /**
     * Schlüssel für Zend_Translate in Zend_Registry.
     */
    public const REGISTRY_KEY = 'Zend_Translate';

    /** @var bool */
    private $loaded = false;

    /** @var self */
    private static $instance;

    /**
     * Optionen für Zend_Translate.
     *
     * @var array
     */
    private $options = [
        'logMessage'     => "Unable to translate key '%message%' into locale '%locale%'",
        'logPriority'    => Zend_Log::WARN,
        'adapter'        => 'tmx',
        'locale'         => 'en',
        'clear'          => false,
        'scan'           => Zend_Translate::LOCALE_FILENAME,
        'ignore'         => '.',
        'disableNotices' => true,
    ];

    /**
     * Konstruiert Klasse für Übersetzungen in OPUS Applikation.
     *
     * @param array|null $options
     */
    public function __construct($options = null)
    {
        $options = $options !== null ? array_merge($this->getOptions(), $options) : $this->getOptions();
        parent::__construct($options);
    }

    /**
     * @return self
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new Application_Translate();
        }

        return self::$instance;
    }

    /**
     * @param self $instance
     */
    public static function setInstance($instance)
    {
        self::$instance = $instance;
        Zend_Registry::set(self::REGISTRY_KEY, $instance);
    }

    /**
     * Loads all modules.
     *
     * @param bool $reload
     */
    public function loadModules($reload = false)
    {
        if (! $this->loaded || $reload) {
            $modules = Application_Modules::getInstance()->getModules();

            foreach ($modules as $name => $module) {
                $moduleDir = APPLICATION_PATH . '/modules/' . $name;
                $this->loadLanguageDirectory("$moduleDir/language/", false, $reload);
            }
        }

        $this->loaded = true;
    }

    /**
     * @param bool $reload
     * @throws Zend_Translate_Exception
     *
     * TODO Is there a way to add both locales in one steps?
     */
    public function loadDatabase($reload = false)
    {
        // TODO use cache
        $translate = new Zend_Translate([
            'adapter'        => DatabaseAdapter::class,
            'content'        => 'all',
            'locale'         => 'en',
            'disableNotices' => true,
            'reload'         => $reload,
        ]);

        $locales = Application_Configuration::getInstance()->getSupportedLanguages();

        foreach ($locales as $locale) {
            $this->addTranslation([
                'content' => $translate,
                'locale'  => $locale,
            ]);
        }

        unset($translate); // TODO Garbage collection? Does it work?
    }

    /**
     * @param bool $reload
     */
    public function loadTranslations($reload = false)
    {
        $this->loadModules($reload);
        $this->loadDatabase($reload);
    }

    /**
     * Lädt TMX Dateien aus einem Verzeichnis.
     *
     * @param string $directory Pfad zum Verzeichnis
     * @param bool   $warnIfMissing Optionally warn in log file if folder is missing
     * @param bool   $reload
     * @return bool
     *
     * TODO better than supressing the warning would be for each module to register language directories in bootstrap
     */
    public function loadLanguageDirectory($directory, $warnIfMissing = true, $reload = false)
    {
        $path = realpath($directory);

        if (($path === false) || (! is_dir($path)) || (! is_readable($path))) {
            if ($warnIfMissing) {
                $this->getLogger()->warn(__METHOD__ . " Directory '$directory' not found.");
            }
            return false;
        }

        $handle = opendir($path);
        if (! $handle) {
            return false;
        }

        while (false !== ($file = readdir($handle))) {
            // Ignore directories.
            if (! is_file($path . DIRECTORY_SEPARATOR . $file)) {
                continue;
            }

            // Ignore files with leading dot and files without extension tmx.
            if (preg_match('/^[^.].*\.tmx$/', $file) === 0) {
                continue;
            }

            // 'reload' is always set, because this code should only be executed if the module has not been loaded yet
            // Otherwise there is a mechanism preventing repeated loading in the parent class.
            $options = array_merge([
                'content' => $path . DIRECTORY_SEPARATOR . $file,
                'reload'  => $reload,
            ], $this->getOptions());

            $this->addTranslation($options);
        }

        return true;
    }

    /**
     * Liefert die Optionen für Zend_Translate.
     *
     * @return array
     */
    public function getOptions()
    {
        return array_merge($this->options, [
            'log'             => $this->getLogger(),
            'logUntranslated' => $this->isLogUntranslatedEnabled(),
        ]);
    }

    /**
     * @return bool
     */
    public function isLogUntranslatedEnabled()
    {
        $config = Config::get();
        return isset($config->log->untranslated) ?
            filter_var($config->log->untranslated, FILTER_VALIDATE_BOOLEAN) : false;
    }

    /**
     * Translates language names utilizing PHP functions.
     *
     * @param string $langId
     * @return string
     */
    public function translateLanguage($langId)
    {
        if ($this->isTranslated($langId)) {
            $language = $this->translate($langId);
        } else {
            $language = Locale::getDisplayLanguage($langId, $this->getLocale());
        }
        return $language;
    }

    /**
     * Returns translations for a key from TMX or database source.
     *
     * @param string $key
     * @return array|null
     *
     * TODO can this be done without knowing the sources?
     */
    public function getTranslations($key)
    {
        if (! $this->isTranslated($key)) {
            return null;
        }

        $languages = Application_Configuration::getInstance()->getSupportedLanguages();

        $translations = [];

        foreach ($languages as $language) {
            $translation             = $this->translate($key, $language);
            $translations[$language] = $translation;
        }

        return $translations;
    }

    /**
     * Stores custom translations for a key.
     *
     * @param string $key
     * @param array  $translations
     * @param string $module
     */
    public function setTranslations($key, $translations, $module = 'default')
    {
        $database = new Dao();

        $database->setTranslation($key, $translations, $module);

        self::clearCache();
    }

    /**
     * @throws Zend_Translate_Exception
     *
     * TODO does not work as intended - messes up some other tests
     */
    public function clear()
    {
        $translate = new Zend_Translate([
            'adapter'        => 'array',
            'content'        => ['en' => [], 'de' => []],
            'locale'         => 'en',
            'disableNotices' => true,
            'clear'          => true,
        ]);

        $this->addTranslation($translate);

        $translate = null;
    }

    /**
     * @return array
     */
    public function getTmxFiles()
    {
        return $this->files;
    }
}
