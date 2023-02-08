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
 * @copyright   Copyright (c) 2019, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\Console\ConsoleColors;
use Opus\Translate\Dao;

class Application_Update_ImportCustomTranslations extends Application_Update_PluginAbstract
{
    /** @var bool */
    private $removeFilesEnabled = true;

    /**
     * Performs import of custom translations and removal of old files.
     */
    public function run()
    {
        $modules = array_keys(Application_Modules::getInstance()->getModules());

        $manager = new Application_Translate_TranslationManager();
        $manager->setFolderNames('language_custom');
        $manager->setModules($modules);

        $this->log("Importing custom translations into database...");

        $files = $manager->getFiles();

        $colors = new ConsoleColors();

        if (count($files) > 0) {
            // Iterate through modules und TMX files in 'language_custom'
            foreach ($files as $module => $folders) {
                $this->importFolder($folders['language_custom'], $module);
            }
        } else {
            $this->log($colors->yellow('No custom TMX files found'));
        }

        // Remove example.tmx.template files
        $this->log('Remove example.tmx.template files...');
        foreach ($modules as $module) {
            $path = APPLICATION_PATH . "/modules/$module/language_custom/example.tmx.template";
            if (is_writable($path)) {
                $this->log("Removing $path");
                if ($this->isRemoveFilesEnabled()) {
                    unlink($path);
                }
            }
        }

        // Remove empty language_custom folders for all modules
        $this->log(PHP_EOL . 'Remove empty \'language_custom\' directories...');
        foreach ($modules as $module) {
            $path = "/modules/$module/language_custom";
            if (is_dir(APPLICATION_PATH . $path)) {
                $this->removeFolder($path);
            }
        }

        $this->log(PHP_EOL . 'Clearing translation cache...' . PHP_EOL);
        Zend_Translate::clearCache();
    }

    /**
     * Import TMX files in folder for module.
     *
     * @param array  $files
     * @param string $module
     */
    public function importFolder($files, $module)
    {
        $colors = new ConsoleColors();

        foreach ($files as $filename) {
            $path = "/modules/$module/language_custom/$filename";

            $this->log("Importing translations from '$path'...");
            $fullPath = APPLICATION_PATH . $path;

            $tmx = new Application_Translate_TmxFile();
            $tmx->load($fullPath);

            $translations = $tmx->toArray();

            $database = new Dao();

            $database->addTranslations($translations, $module);

            // Remove imported file
            if (is_writable($fullPath)) {
                unlink($fullPath);
                $this->log("Removed file '$path'" . PHP_EOL);
            } else {
                $this->log($colors->red("Could not remove file '$path'") . PHP_EOL);
            }
        }
    }

    /**
     * Remove 'language_custom' folder if they are empty.
     *
     * @param string $path
     */
    public function removeFolder($path)
    {
        $colors = new ConsoleColors();
        if (! (new FilesystemIterator(APPLICATION_PATH . $path))->valid()) {
            rmdir(APPLICATION_PATH . $path);
            $this->log("Removed folder '$path'");
        } else {
            $this->log($colors->red("Folder '$path' not removed, because it is not empty"));
        }
    }

    /**
     * @param bool $enabled
     */
    public function setRemoveFilesEnabled($enabled)
    {
        $this->removeFilesEnabled = $enabled;
    }

    /**
     * @return bool
     */
    public function isRemoveFilesEnabled()
    {
        return $this->removeFilesEnabled;
    }
}
