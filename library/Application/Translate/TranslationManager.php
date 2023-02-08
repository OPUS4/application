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

use Opus\Common\Translate\TranslateException;
use Opus\Common\Translate\UnknownTranslationKeyException;
use Opus\Translate\Dao;

/**
 * Management of translations across OPUS 4 modules.
 *
 * # Editing translations in modules
 *
 * The translation manager does not automatically cover all modules, but only those allowed by the the configuration.
 * If the configuration is empty editing of keys in all modules is allowed.
 *
 * setup.translation.modules.allowed = default,publish
 *
 * # Duplicate keys
 *
 * Duplicate keys are not allowed. Each key in a module should always start with the name of the module.
 *
 * All translations are always loaded, because modules can depend on each other and that dependency cannot be resolved
 * at runtime to decide which translations should be loaded. It is easier to load all translations. if a key occurs
 * twice, the value loaded last is used.
 *
 * It does not make sense to make the management of the translations more complicated to handle duplicate keys. So for
 * normal operations a key can also appear only once in the management interface. However when loading the translations
 * duplicates can be detected and conflicts highlighted. Since those conflicts come from TMX files. These conflicts have
 * to be resolved by the developer of the module.
 *
 * TODO easy way to cover all modules (for development purposes)
 * TODO maybe development mode where original files can be edited
 * TODO detect duplicate keys and values
 *
 * TODO add logic where translations are automatically stored in language_custom and
 *      distinguish between DEFAULT and CUSTOM values in order to display them together in the user interface
 * TODO place groups of translations like for collections in a "logical" module/namespace
 * TODO namespaces for translations (?)
 */
class Application_Translate_TranslationManager extends Application_Model_Abstract
{
    /**
     * sort by translation unit
     */
    public const SORT_UNIT = 'key';

    /**
     * sort by application module
     */
    public const SORT_MODULE = 'module';

    /**
     * sort by application module directory
     */
    public const SORT_DIRECTORY = 'directory';

    /**
     * sort by filename
     */
    public const SORT_FILENAME = 'filename';

    /**
     * Translations that have been customized (original in TMX files).
     */
    public const STATE_EDITED = 1;

    /**
     * Translations that have been added (no entry in TMX files).
     */
    public const STATE_ADDED = 2;

    /**
     * Search for matching keys.
     */
    public const SCOPE_KEYS = 4;

    /**
     * Search for matching translation texts.
     */
    public const SCOPE_TEXT = 8;

    /** @var string Used to filter translations by key and/or value. */
    private $filter;

    /** @var string[] Modules to include. */
    private $modules;

    /** @var int Filter translations by state (all, edited, added). */
    private $state;

    /** @var int Filter translations by scope (keys and/or values). */
    private $scope;

    /** @var array Names of folders containing TMX files. */
    private $folders = ['language'];

    /** @var array Reference for sorting languages */
    private $languageOrderRef;

    /**
     * Get editable modules.
     *
     * @return string[]
     */
    public function getModules()
    {
        if ($this->modules === null) {
            $allowedModules = $this->getAllowedModules();

            if ($allowedModules === null) {
                $modulesManager = Application_Modules::getInstance();
                $allowedModules = array_keys($modulesManager->getModules());
            }

            $this->modules = $allowedModules;
        }

        return $this->modules;
    }

    /**
     * @return string[]
     * @throws Zend_Exception
     */
    public function getAllowedModules()
    {
        $config = $this->getConfig();

        $allowedModules = null;

        if (isset($config->setup->translation->modules->allowed)) {
            $value = $config->setup->translation->modules->allowed;
            if (! empty($value)) {
                $modules = array_map('trim', explode(',', $value));

                $allModules = Application_Modules::getInstance()->getModules();

                $allowedModules = [];

                foreach ($modules as $name) {
                    if (array_key_exists($name, $allModules)) {
                        $allowedModules[] = $name;
                    } else {
                        $this->getLogger()->err(
                            "Configuration 'setup.translation.modules.allowed' contains unknown module '$name'."
                        );
                    }
                }
            }
        }

        return $allowedModules;
    }

    /**
     * Set Modules to include.
     *
     * @param array|string|null $modules Modules to include
     */
    public function setModules($modules)
    {
        if (! is_array($modules) && $modules !== null) {
            $this->modules = [$modules];
        } else {
            $this->modules = $modules;
        }
    }

    /**
     * Set filter used to filter translation units.
     * Only units which contain filter are returned.
     *
     * @param string $string
     */
    public function setFilter($string)
    {
        $this->filter = $string;
    }

    /**
     * Returns translations in modules set via @see setModules()
     * and (optionally) matching string set via @see setFilter()
     *
     * @param string $sortKey   Key used to sort result array.
     *                          Valid keys are defined as class constants
     * @param int    $sortOrder Sort order as expected by @see array_multisort()
     * @return array
     * @throw Setup_Model_FileNotReadableException Thrown if loading tmx file(s) fails.
     *
     * TODO refactor
     */
    public function getTranslations($sortKey = self::SORT_UNIT, $sortOrder = SORT_ASC)
    {
        $fileData = $this->getFiles();

        $translations = [];
        $sortArray    = [];

        foreach ($fileData as $module => $files) {
            foreach ($files as $dir => $filenames) {
                foreach ($filenames as $fileName) {
                    $relativeFilePath = "$module/$dir/$fileName";
                    $filePath         = APPLICATION_PATH . "/modules/$relativeFilePath";
                    $tmxFile          = new Application_Translate_TmxFile();

                    if ($tmxFile->load($filePath)) {
                        $translationUnits = $tmxFile->toArray();

                        foreach ($translationUnits as $key => $values) {
                            if ($this->matches($key, $values, $this->filter)) {
                                $row = [
                                    'key'          => $key,
                                    'module'       => $module,
                                    'directory'    => $dir,
                                    'filename'     => $fileName,
                                    'translations' => [],
                                ];

                                foreach ($values as $lang => $value) {
                                    $row['translations'][$lang] = $value;
                                }

                                $row['translations'] = $this->sortLanguages($row['translations']);

                                if (! array_key_exists($key, $translations)) {
                                    $translations[$key] = $row;
                                    $sortArray[]        = $row[$sortKey];
                                } else {
                                    $entry = $translations[$key];
                                    if (isset($entry['duplicates'])) {
                                        $duplicates = $entry['duplicates'];
                                        unset($entry['duplicates']);
                                    } else {
                                        $duplicates = [];
                                    }
                                    $duplicates[]       = $entry;
                                    $row['duplicates']  = $duplicates;
                                    $translations[$key] = $row;
                                }
                            }
                        }
                    } else {
                        throw new Setup_Model_FileNotReadableException($filePath);
                    }
                }
            }
        }

        array_multisort($sortArray, $sortOrder, SORT_STRING, $translations);

        return $translations;
    }

    /**
     * @param array $translations
     * @return array
     */
    protected function sortLanguages($translations)
    {
        $ref = array_intersect_key($this->getLanguageOrderRef(), $translations);
        return array_merge($ref, $translations);
    }

    /**
     * @return array
     */
    protected function getLanguageOrderRef()
    {
        if ($this->languageOrderRef === null) {
            $this->languageOrderRef = array_flip(Application_Configuration::getInstance()->getSupportedLanguages());
        }

        return $this->languageOrderRef;
    }

    /**
     * @return array|null
     */
    public function getLanguageOrder()
    {
        if (is_array($this->languageOrderRef)) {
            return array_flip($this->getLanguageOrderRef());
        } else {
            return null;
        }
    }

    /**
     * @param array $order
     */
    public function setLanguageOrder($order)
    {
        $this->languageOrderRef = array_flip($order);
    }

    /**
     * Returns all translations from TMX files and database.
     *
     * - key
     * - module
     * - directory
     * - filename
     * - translations
     *   - en -> English string
     *   - de -> German string
     *
     * From database we get
     *
     *   key -> [
     *       'en' -> Value,
     *       'de' -> Value
     *   ]
     *
     * @param string $sortKey
     * @param int    $sortOrder
     * @return array Translations
     */
    public function getMergedTranslations($sortKey = self::SORT_UNIT, $sortOrder = SORT_ASC)
    {
        // get translations from TMX files
        $translations = $this->getTranslations($sortKey, $sortOrder);

        $database = new Dao();

        $modules = $this->getModules();

        $dbTranslations = $database->getTranslationsWithModules($modules);

        foreach ($dbTranslations as $key => $info) {
            $languages = $info['values'];
            if (array_key_exists($key, $translations)) {
                // key exists in TMX files and needs to be marked as EDITED
                // keep original values from TMX files
                if ($this->matches($key, $languages, $this->filter)) {
                    $translations[$key]['translationsTmx'] = $translations[$key]['translations'];
                    $translations[$key]['translations']    = $this->sortLanguages($languages);
                    $translations[$key]['state']           = 'edited';
                } else {
                    // remove if edited version does not match anymore
                    unset($translations[$key]);
                }
            } else {
                // key does not exist in TMX file and needs to be marked as ADDED
                if ($this->matches($key, $languages, $this->filter)) {
                    $translations[$key]['key']          = $key;
                    $translations[$key]['translations'] = $this->sortLanguages($languages);
                    $translations[$key]['state']        = 'added';
                    $translations[$key]['module']       = $info['module'];
                }
            }
        }

        if ($this->state !== null) {
            foreach ($translations as $key => $entry) {
                if ($this->state !== null && ! isset($entry['state'])) {
                    unset($translations[$key]);
                } else {
                    if (
                        ! ($entry['state'] === 'added' && $this->state === self::STATE_ADDED) &&
                        ! ($entry['state'] === 'edited' && $this->state === self::STATE_EDITED)
                    ) {
                        unset($translations[$key]);
                    }
                }
            }
        }

        return $translations;
    }

    /**
     * @param string $key
     * @return array
     * @throws UnknownTranslationKeyException
     *
     * TODO IMPORTANT optimize (do some caching or something)
     */
    public function getTranslation($key)
    {
        $translations = $this->getMergedTranslations();

        $translation = null;

        if (isset($translations[$key])) {
            $translation = $translations[$key];
        } else {
            throw new UnknownTranslationKeyException("Unknown key '$key'.");
        }

        return $translation;
    }

    /**
     * Checks if translation matches configured criteria.
     *
     * Depending on the configured scope the function checks the key and/or the text of translations.
     * Filtering by state happens outside this function because this depends on the presence of the
     * key in the TMX files and the database or just the database.
     *
     * TODO should filtering by module happen here
     *
     * @param string      $key Name of translation key
     * @param array       $values Translated texts for supported languages
     * @param string      $filter Filter string for matching entries
     * @param string|null $module Name of module for translation key
     * @return bool
     */
    public function matches($key, $values, $filter, $module = null)
    {
        if (empty($filter)) {
            return true;
        }

        if (($this->scope === self::SCOPE_KEYS || $this->scope === null) && stripos($key, $filter) !== false) {
            return true;
        }

        if ($this->scope === self::SCOPE_TEXT || $this->scope === null) {
            foreach ($values as $lang => $value) {
                if (stripos($value, $filter) !== false) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Returns translations that contain a specified string.
     *
     * @param string $needle
     * @return array
     * @throws Setup_Model_FileNotReadableException
     *
     * TODO integrate into getTranslations? performance? How will it be used? Maybe use Zend_Cache?
     */
    public function findTranslations($needle)
    {
        $translations = $this->getTranslations();

        $result = [];

        foreach ($translations as $translation) {
            if (stripos($translation['key'], $needle) !== false) {
                $result[] = $translation;
            }
        }

        return $result;
    }

    /**
     * returns an array containing all translation files found for all modules
     * set via @see setModules()
     *
     * @return array
     */
    public function getFiles()
    {
        $modules = [];

        $languageDirs = $this->getFolderNames();

        foreach ($this->getModules() as $moduleName) {
            $moduleFiles = [];

            $modulePath = realpath(APPLICATION_PATH . "/modules/$moduleName");

            if (! is_dir($modulePath)) {
                // TODO should this throw an exception? basically this here should never happen
                $this->getLogger()->err(
                    "Module directory '$moduleName' not found for loading translations."
                );
                continue;
            }

            $moduleSubDirs = new RecursiveDirectoryIterator(
                $modulePath,
                FilesystemIterator::CURRENT_AS_SELF
            );

            foreach ($moduleSubDirs as $moduleSubDir) {
                if ($moduleSubDir->isDir()) {
                    $dirName = $moduleSubDir->getFilename();
                    if (in_array($dirName, $languageDirs)) {
                        $tmxFiles = $moduleSubDir->getChildren();
                        foreach ($tmxFiles as $tmxFile) {
                            $tmxFileName = $tmxFile->getFilename();
                            if ($tmxFile->isFile() && substr($tmxFileName, -4) === '.tmx') {
                                $moduleFiles[$dirName][] = $tmxFile->getFilename();
                            }
                        }
                    }
                }
            }

            if (! empty($moduleFiles)) {
                $modules[$moduleName] = $moduleFiles;
            }
        }

        return $modules;
    }

    /**
     * @return array
     */
    public function getFolderNames()
    {
        if (! is_array($this->folders)) {
            $this->folders = [];
        }
        return $this->folders;
    }

    /**
     * @param string|string[] $names
     */
    public function setFolderNames($names)
    {
        if (! is_array($names)) {
            $names = [$names];
        }
        $this->folders = $names;
    }

    /**
     * @param string $name
     */
    public function addFolderName($name)
    {
        $names = $this->getFolderNames();

        if (! in_array($name, $names)) {
            $names[] = $name;
            $this->setFolderNames($names);
        }
    }

    /**
     * @param string $key
     * @return bool
     *
     * TODO should check database and TMX
     */
    public function keyExists($key)
    {
        $database = new Dao();

        return $database->getTranslation($key) !== null;
    }

    /**
     * @param string $key
     * @return bool
     * @throws UnknownTranslationKeyException
     */
    public function isEdited($key)
    {
        $translation = $this->getTranslation($key);

        return isset($translation['state']) && $translation['state'] === 'edited';
    }

    /**
     * Removes a translation key from the database if the key exists in TMX files.
     *
     * @param string $key
     */
    public function reset($key)
    {
        $database = new Dao();

        $translations = $this->getTranslations();

        if (array_key_exists($key, $translations)) {
            $this->delete($key);
        }
    }

    /**
     * Removes a translation key from the database.
     *
     * @param string      $key Translation key
     * @param string|null $module Module of translation key
     */
    public function delete($key, $module = null)
    {
        $database = $this->getDatabase();
        $database->remove($key, $module);

        $this->clearCache();
    }

    public function deleteAll()
    {
        $database = $this->getDatabase();
        $database->removeAll();

        $this->clearCache();
    }

    public function clearCache()
    {
        $translate = Application_Translate::getInstance();
        if ($translate !== null) {
            $translate->clearCache();
        }
    }

    /**
     * TODO IMPORTANT bad performance, improve!
     */
    public function deleteMatches()
    {
        $matches = $this->getMergedTranslations();

        $database = $this->getDatabase();

        foreach ($matches as $key => $translation) {
            if (isset($translation['state']) && (in_array($translation['state'], ['edited', 'added']))) {
                $database->remove($key);
            }
        }
    }

    /**
     * Returns custom translations as TMX file.
     *
     * @param bool $unmodified
     * @return Application_Translate_TmxFile
     */
    public function getExportTmxFile($unmodified = false)
    {
        $translations = $this->getMergedTranslations();

        $tmxFile = new Application_Translate_TmxFile();

        foreach ($translations as $key => $data) {
            if ($unmodified || isset($data['state'])) {
                $module    = $data['module'];
                $languages = $data['translations'];
                foreach ($languages as $lang => $value) {
                    $tmxFile->setTranslation($key, $lang, $value, $module);
                }
            }
        }

        return $tmxFile;
    }

    /**
     * TODO move into separate class
     * TODO cache keys in manager (performance)
     *
     * @param Application_Translate_TmxFile $tmxFile
     */
    public function importTmxFile($tmxFile)
    {
        $translations = $tmxFile->toArray();

        $database = $this->getDatabase();

        foreach ($translations as $key => $values) {
            $module = $tmxFile->getModuleForKey($key);

            $old = null;

            try {
                $old = $this->getTranslation($key);
            } catch (UnknownTranslationKeyException $ex) {
                // do nothing
                // TODO work without exception here (keyExists)?
            }

            if ($old !== null) {
                // check if modules match
                if ($module === null || $old['module'] !== $module) {
                    $module = $old['module'];
                    // TODO write to log
                }

                if (isset($old['translationsTmx'])) {
                    $oldValues = $old['translationsTmx'];
                } else {
                    $oldValues = $old['translations'];
                }

                if (count(array_diff($values, $oldValues)) > 0) {
                    // only set if values differ from existing
                    $database->setTranslation($key, $values, $module);
                }
            } else {
                // add unknown key to database
                $database->setTranslation($key, $values, $module);
            }
        }
    }

    /**
     * @return Dao
     */
    public function getDatabase()
    {
        return new Dao();
    }

    /**
     * @param int $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * @param string $scope
     */
    public function setScope($scope)
    {
        $this->scope = $scope;
    }

    /**
     * @param string      $key
     * @param array       $translations
     * @param string|null $module
     * @param string|null $oldKey
     * TODO perform as transaction
     * TODO refactor to be efficient
     */
    public function updateTranslation($key, $translations, $module = null, $oldKey = null)
    {
        $dao = new Dao();

        if ($key !== $oldKey && $oldKey !== null) {
            $translation = $this->getTranslation($oldKey);
            if (isset($translation['state']) && $translation['state'] === 'added') {
                $dao->remove($oldKey);
                $this->clearCache();
            } else {
                throw new TranslateException("Name of key '$oldKey' cannot be changed.");
            }
        } else {
            $translation  = $this->getTranslation($key);
            $changeModule = isset($translation['module']) && $translation['module'] !== $module && $module !== null;
            if ($changeModule) {
                if (isset($translation['state']) && $translation['state'] === 'added') {
                    $dao->remove($key);
                    $this->clearCache();
                } else {
                    throw new TranslateException("Module of key '$key' cannot be changed.");
                }
            }
        }

        if ($translation !== null) {
            if ($translations === null) {
                $translations = $translation['translations'];
            }
            if ($module === null) {
                $module = $translation['module'];
            }
        }

        $dao->setTranslation($key, $translations, $module);
    }

    /**
     * Finds and returns duplicate keys in TMX files.
     *
     * TODO automatically search all modules? development setting?
     *
     * @return array
     */
    public function getDuplicateKeys()
    {
        $all = $this->getTranslations();

        $duplicateKeys = [];

        foreach ($all as $key => $entry) {
            if (isset($entry['duplicates'])) {
                $duplicates = $entry['duplicates'];
                unset($entry['duplicates']);
                $duplicates[]        = $entry;
                $duplicateKeys[$key] = $duplicates;
            }
        }

        return $duplicateKeys;
    }

    /**
     * @param string      $key
     * @param array       $values
     * @param string|null $module
     * @throws TranslateException
     */
    public function setTranslation($key, $values, $module = null)
    {
        try {
            $old = $this->getTranslation($key);
        } catch (UnknownTranslationKeyException $excep) {
            $old = null;
        }

        if ($old !== null) {
            if (isset($old['translationsTmx'])) {
                $defaultValues = $old['translationsTmx'];
            } else {
                $defaultValues = $old['translations'];
            }

            if (count(array_diff($values, $defaultValues)) === 0) {
                $this->reset($key);
            } else {
                $this->updateTranslation($key, $values, $module);
            }
        } else {
            $dao = $this->getDatabase();
            $dao->setTranslation($key, $values, $module);
        }
    }
}
