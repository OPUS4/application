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
 * @package     Module_Setup
 * @author      Edouard Simon (edouard.simon@zib.de)
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2020, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

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
    const SORT_UNIT = 'key';

    /**
     * sort by application module
     */
    const SORT_MODULE = 'module';

    /**
     * sort by application module directory
     */
    const SORT_DIRECTORY = 'directory';

    /**
     * sort by filename
     */
    const SORT_FILENAME = 'filename';

    /**
     * Translations that have been customized (original in TMX files).
     */
    const STATE_EDITED = 1;

    /**
     * Translations that have been added (no entry in TMX files).
     */
    const STATE_ADDED = 2;

    /**
     * Search for matching keys.
     */
    const SCOPE_KEYS = 4;

    /**
     * Search for matching translation texts.
     */
    const SCOPE_TEXT = 8;

    /**
     * String used to filter translations by key and/or value.
     */
    private $filter;

    /**
     * array holding modules to include
     */
    private $modules = null;

    /**
     * @var string
     */
    private $filterBy = null;

    /**
     * Filter translations by state (all, edited, added).
     * @var string
     */
    private $state = null;

    /**
     * Filter translations by scope (keys and/or values).
     * @var string
     */
    private $scope = null;

    /**
     * Names of folders containing TMX files.
     * @var array
     */
    private $folders = ['language'];

    /**
     * Get editable modules.
     */
    public function getModules()
    {
        if (is_null($this->modules)) {
            $allowedModules  = $this->getAllowedModules();

            if (is_null($allowedModules)) {
                $modulesManager = Application_Modules::getInstance();
                $allowedModules = array_keys($modulesManager->getModules());
            }

            $this->modules = $allowedModules;
        }

        return $this->modules;
    }

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
     * @param array $modules Modules to include
     *
     */
    public function setModules($modules)
    {
        if (! is_array($modules) && ! is_null($modules)) {
            $this->modules = [$modules];
        } else {
            $this->modules = $modules;
        }
    }

    /**
     * Set filter used to filter translation units.
     * Only units which contain filter are returned.
     *
     *
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
     * @param int $sortOrder    Sort order as expected by @see array_multisort()
     * @throw Setup_Model_FileNotReadableException Thrown if loading tmx file(s) fails.
     *
     * TODO refactor
     */
    public function getTranslations($sortKey = self::SORT_UNIT, $sortOrder = SORT_ASC)
    {
        $fileData = $this->getFiles();

        $translations = [];
        $sortArray = [];

        foreach ($fileData as $module => $files) {
            foreach ($files as $dir => $filenames) {
                foreach ($filenames as $fileName) {
                    $relativeFilePath = "$module/$dir/$fileName";
                    $filePath = APPLICATION_PATH . "/modules/$relativeFilePath";
                    $tmxFile = new Application_Translate_TmxFile();

                    if ($tmxFile->load($filePath)) {
                        $translationUnits = $tmxFile->toArray();

                        foreach ($translationUnits as $key => $values) {
                            if ($this->matches($key, $values, $this->filter)) {
                                $row = [
                                    'key' => $key,
                                    'module' => $module,
                                    'directory' => $dir,
                                    'filename' => $fileName,
                                    'translations' => []
                                ];

                                foreach ($values as $lang => $value) {
                                    $row['translations'][$lang] = $value;
                                }

                                if (! array_key_exists($key, $translations)) {
                                    $translations[$key] = $row;
                                    $sortArray[] = $row[$sortKey];
                                } else {
                                    $entry = $translations[$key];
                                    if (isset($entry['duplicates'])) {
                                        $duplicates = $entry['duplicates'];
                                        unset($entry['duplicates']);
                                    } else {
                                        $duplicates = [];
                                    }
                                    $duplicates[] = $entry;
                                    $row['duplicates'] = $duplicates;
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
     *
     * @param string $sortKey
     * @param int $sortOrder
     * @return array Translations
     */
    public function getMergedTranslations($sortKey = self::SORT_UNIT, $sortOrder = SORT_ASC)
    {
        // get translations from TMX files
        $translations = $this->getTranslations($sortKey, $sortOrder);

        $database = new Opus_Translate_Dao();

        $modules = $this->getModules();

        $dbTranslations = $database->getTranslationsWithModules($modules);

        foreach ($dbTranslations as $key => $info) {
            $languages = $info['values'];
            if (array_key_exists($key, $translations)) {
                // key exists in TMX files and needs to be marked as EDITED
                // keep original values from TMX files
                if ($this->matches($key, $languages, $this->filter)) {
                    $translations[$key]['translationsTmx'] = $translations[$key]['translations'];
                    $translations[$key]['translations'] = $languages;
                    $translations[$key]['state'] = 'edited';
                } else {
                    // remove if edited version does not match anymore
                    unset($translations[$key]);
                }
            } else {
                // key does not exist in TMX file and needs to be marked as ADDED
                if ($this->matches($key, $languages, $this->filter)) {
                    $translations[$key]['key'] = $key;
                    $translations[$key]['translations'] = $languages;
                    $translations[$key]['state'] = 'added';
                    $translations[$key]['module'] = $info['module'];
                }
            }
        }

        if ($this->state !== null) {
            foreach ($translations as $key => $entry) {
                if ($this->state !== null && ! isset($entry['state'])) {
                    unset($translations[$key]);
                } else {
                    if (($entry['state'] === 'added' && $this->state & self::STATE_ADDED) ||
                        ($entry['state'] === 'edited' && $this->state & self::STATE_EDITED)) {
                        // keep entry
                    } else {
                        unset($translations[$key]);
                    }
                }
            }
        }

        return $translations;
    }

    /**
     * @param $key
     * TODO IMPORTANT optimize (do some caching or something)
     * @throws \Opus\Translate\UnknownTranslationKey
     */
    public function getTranslation($key)
    {
        $translations = $this->getMergedTranslations();

        $translation = null;

        if (isset($translations[$key])) {
            $translation = $translations[$key];
        } else {
            throw new \Opus\Translate\UnknownTranslationKey("Unknown key '$key'.");
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
     * TODO should filting by module happen here
     *
     * @param $key string Name of translation key
     * @param $values array Translated texts for supported languages
     * @param $filter string Filter string for matching entries
     * @param $module string Name of module for translation key
     * @return bool
     */
    public function matches($key, $values, $filter, $module = null)
    {
        if (empty($filter)) {
            return true;
        }

        if (($this->scope & self::SCOPE_KEYS || $this->scope == null) && stripos($key, $filter) !== false) {
            return true;
        }

        if ($this->scope & self::SCOPE_TEXT || $this->scope == null) {
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
     * @param $needle
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
                            if ($tmxFile->isFile() && substr($tmxFileName, -4) == '.tmx') {
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
     * @param $names
     */
    public function setFolderNames($names)
    {
        if (! is_array($names)) {
            $names = [$names];
        }
        $this->folders = $names;
    }

    /**
     * @param $name
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
     * @param $key
     * @return bool
     *
     * TODO should check database and TMX
     */
    public function keyExists($key)
    {
        $database = new Opus_Translate_Dao();

        return ! is_null($database->getTranslation($key));
    }

    public function isEdited($key)
    {
        $translation = $this->getTranslation($key);

        return (isset($translation['state']) && $translation['state'] == 'edited');
    }

    /**
     * Removes a translation key from the database if the key exists in TMX files.
     * @param $key
     */
    public function reset($key)
    {
        $database = new Opus_Translate_Dao();

        $translations = $this->getTranslations();

        if (array_key_exists($key, $translations)) {
            $this->delete($key);
        }
    }

    /**
     * Removes a translation key from the database.
     * @param $key string Translation key
     * @param $module string Module of translation key
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
        $translate = Zend_Registry::get('Zend_Translate');
        $translate->clearCache();
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
     */
    public function getExportTmxFile($unmodified = false)
    {
        $translations = $this->getMergedTranslations();

        $tmxFile = new Application_Translate_TmxFile();

        foreach ($translations as $key => $data) {
            if ($unmodified || isset($data['state'])) {
                $module = $data['module'];
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
            } catch (\Opus\Translate\UnknownTranslationKey $ex) {
                // do nothing
                // TODO work without exception here (keyExists)?
            }

            if (! is_null($old)) {
                // check if modules match
                if (is_null($module) || $old['module'] !== $module) {
                    $module = $old['module'];
                    // TODO write to log
                }

                if (isset($old['transaltionsTmx'])) {
                    $oldValues = $old['translationsTmx'];
                } else {
                    $oldValues = $old['translations'];
                }

                if ($oldValues != $values) {
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
     * @return Opus_Translate_Dao $database
     */
    public function getDatabase()
    {
        return new Opus_Translate_Dao();
    }

    public function setState($state)
    {
        $this->state = $state;
    }

    public function setScope($scope)
    {
        $this->scope = $scope;
    }

    /**
     * @param $key
     * @param $translations
     * @param $module
     * @param null $oldKey
     * TODO perform as transaction
     * TODO refactor to be efficient
     */
    public function updateTranslation($key, $translations, $module = null, $oldKey = null)
    {
        $dao = new Opus_Translate_Dao();
        $translate = Zend_Registry::get('Zend_Translate');

        if ($key !== $oldKey && ! is_null($oldKey)) {
            $translation = $this->getTranslation($oldKey);
            if (isset($translation['state']) && $translation['state'] === 'added') {
                $dao->remove($oldKey);
                $translate->clearCache();
            } else {
                throw new \Opus\Translate\Exception("Name of key '$oldKey' cannot be changed.");
            }
        } else {
            $translation = $this->getTranslation($key);
            $changeModule = isset($translation['module']) && $translation['module'] !== $module && ! is_null($module);
            if ($changeModule) {
                if (isset($translation['state']) && $translation['state'] === 'added') {
                    $dao->remove($key);
                    $translate->clearCache();
                } else {
                    throw new \Opus\Translate\Exception("Module of key '$key' cannot be changed.");
                }
            }
        }

        if (! is_null($translation)) {
            if (is_null($translations)) {
                $translations = $translation['translations'];
            }
            if (is_null($module)) {
                $module = $translation['module'];
            }
        }

        $dao->setTranslation($key, $translations, $module);
    }

    /**
     * Finds and returns duplicate keys in TMX files.
     *
     * TODO automatically search all modules? development setting?
     */
    public function getDuplicateKeys()
    {
        $all = $this->getTranslations();

        $duplicateKeys = [];

        foreach ($all as $key => $entry) {
            if (isset($entry['duplicates'])) {
                $duplicates = $entry['duplicates'];
                unset($entry['duplicates']);
                $duplicates[] = $entry;
                $duplicateKeys[$key] = $duplicates;
            }
        }

        return $duplicateKeys;
    }

    public function setTranslation($key, $values, $module = null)
    {
        try {
            $old = $this->getTranslation($key);
        } catch (\Opus\Translate\UnknownTranslationKey $excep) {
            $old = null;
        }

        if (! is_null($old)) {
            if (isset($old['translationsTmx'])) {
                $defaultValues = $old['translationsTmx'];
            } else {
                $defaultValues = $old['translations'];
            }

            if ($defaultValues == $values) {
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
