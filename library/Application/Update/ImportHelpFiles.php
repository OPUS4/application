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
 * @copyright   Copyright (c) 2020, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\Translate\TranslateException;

/**
 * Imports help files into database.
 *
 * Starting with OPUS 4.7 the content of the FAQ page is stored in TMX files.
 * Customizations are stored in the database.
 * The TXT files in 'application/configs/help' are no longer used.
 * The help.ini file is still used to determine the structure of the FAQ page.
 *
 * In a clean instance basically nothing needs to happen because the keys and
 * the content are already defined in TMX files in the 'help' module.
 *
 * For customized instances the content of the TXT files needs to be stored in
 * the corresponding keys.
 *
 * Before 4.7 the key 'help_content_searchtipps' translated into the names of
 * the files actually containing the content. Starting with 4.7 the key directly
 * translates into the content.
 *
 * During the update process the corresponding files have to be identified by
 * parsing 'help.ini' and translating the keys into the names of the files.
 * Then the files have to be read and the content stored in the keys that so far
 * contained the filenames.
 *
 * If a key does not contain a file name it is ignored.
 *
 * If the content matches the default TMX entries in the help module it is not
 * stored in the database.
 *
 * The 'imprint' and 'contact' files are ignored, because they are imported in a
 * separate earlier step. The content of the files is handled differently because
 * there are separate pages for imprint and contact and their content is only
 * optionally included on the FAQ page. (It makes sense from a user perspective,
 * but it is a special case that needs to be handled in various places.)
 *
 * TODO eliminate overlap with Application_update_ImportStaticPages
 */
class Application_Update_ImportHelpFiles extends Application_Update_PluginAbstract
{
    /** @var bool */
    private $removeFilesEnabled = true;

    /** @var string */
    private $helpPath;

    public function run()
    {
        // clean up help keys and move keys from home to help
        $this->moveKeysToHelp();

        // load help.ini
        $entries = $this->getHelpFiles();

        $this->log('Importing help files into database...');

        if (count($entries) > 0) {
            foreach ($entries as $key => $translations) {
                if (! in_array($key, ['help_content_contact', 'help_content_imprint'])) {
                    $this->log("Importing files for '$key' ...");
                    $this->importFiles($key, $translations, 'help');
                }
            }
        }
    }

    /**
     * Move help (FAQ) related keys into the help module.
     *
     * Starting with OPUS 4.7 the help translations are bundled in a separate 'help' module.
     * However imprint and contact while sometimes being included on the FAQ page are
     * stored in the 'home' module. That is because the help implementation might be exchanged,
     * but the contact and the imprint pages are essential part of the core user interface.
     */
    public function moveKeysToHelp()
    {
        $manager = new Application_Translate_TranslationManager();
        $manager->setModules('home');
        $manager->setFilter('help_');
        $translations = $manager->getMergedTranslations();

        $helpKeys = [];

        // find keys to move
        foreach ($translations as $key => $data) {
            if (
                strpos($key, 'help_') === 0
                    && ! in_array($key, ['help_content_contact', 'help_content_imprint'])
            ) {
                $helpKeys[] = $key;
            }
        }

        if (count($helpKeys) > 0) {
            $this->log('Moving FAQ translations keys to help module ...');
            foreach ($helpKeys as $key) {
                $translation = $manager->getTranslation($key);
                if (isset($translation['state']) && $translation['state'] === 'added') {
                    $this->log("Move '$key'");
                    $manager->delete($key);
                    $manager->setTranslation($key, $translation['translations'], 'help');
                }
            }
        }
    }

    /**
     * Returns help keys with associated content/file names.
     *
     * In the default configuration (starting with 4.7) the values will directly contain the
     * content text. In customized setups before 4.7 the values should be file names.
     *
     * @return array
     */
    public function getHelpFiles()
    {
        $help = new Home_Model_HelpFiles();
        $help->setHelpPath($this->getHelpPath());

        $entries = $help->getHelpEntries();

        $iterator = new RecursiveIteratorIterator(new RecursiveArrayIterator($entries));

        $manager = new Application_Translate_TranslationManager();
        $manager->setModules('help');
        $manager->setFilter('help_content_');
        $translations = $manager->getMergedTranslations();

        $files = [];

        foreach ($iterator as $name) {
            $key = "help_content_$name";

            // only process keys that exist and have been edited
            // TODO handle local modified files
            if (array_key_exists($key, $translations)) {
                $translation = $translations[$key];
                $files[$key] = $translation['translations'];
            }
        }

        return $files;
    }

    /**
     * @param string $key
     * @param array  $files
     * @param string $module
     * @throws TranslateException
     */
    public function importFiles($key, $files, $module)
    {
        $helpPath = $this->getHelpPath();

        $values = [];

        foreach ($files as $lang => $file) {
            $path = $helpPath . $file;
            if (is_readable($path)) {
                $content       = file_get_contents($path);
                $values[$lang] = $content;
                $this->removeFile($path);
            } else {
                // OPUSVIER-4304 Try to see if there is a file after all.
                $this->log("Trying to resolve default file for key '$key' in language '$lang'.");
                $prefix = 'help_content_';
                if (substr($key, 0, strlen($prefix)) === $prefix) {
                    $baseName = substr($key, strlen($prefix));
                    $fileName = "$baseName.{$lang}.txt";
                    $path     = $helpPath . $fileName;
                    if (is_readable($path)) {
                        $this->log("Default file '$fileName' found.");
                        $content       = file_get_contents($path);
                        $content       = trim($content ?: '');
                        $values[$lang] = $content;
                        $this->removeFile($path);
                    } else {
                        $this->log("Default file '$fileName' not found.");
                    }
                }
            }
        }

        $manager = new Application_Translate_TranslationManager();

        $manager->setTranslation($key, $values, $module);
    }

    /**
     * @param bool $enabled
     * @return $this
     */
    public function setRemoveFilesEnabled($enabled)
    {
        $this->removeFilesEnabled = $enabled;
        return $this;
    }

    /**
     * @return bool
     */
    public function isRemoveFilesEnabled()
    {
        return $this->removeFilesEnabled;
    }

    /**
     * @return string
     */
    public function getHelpPath()
    {
        if ($this->helpPath === null) {
            $this->helpPath = APPLICATION_PATH . '/application/configs/help/';
        }

        return $this->helpPath;
    }

    /**
     * @param string $path
     * @return $this
     */
    public function setHelpPath($path)
    {
        $this->helpPath = rtrim($path, '/') . '/';
        return $this;
    }

    /**
     * @param string $path
     */
    protected function removeFile($path)
    {
        if ($this->isRemoveFilesEnabled() && is_writable($path)) {
            rename($path, $path . '.imported');
        }
    }
}
