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
 * @package     Application_Update
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2019, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

class Application_Update_ImportCustomTranslations extends Application_Update_PluginAbstract
{

    /**
     * Performs import of custom translations and removal of old files.
     * @return mixed
     */
    public function run()
    {
        $modules = array_keys(Application_Modules::getInstance()->getModules());

        $manager = new Application_Translate_TranslationManager();
        $manager->setFolderNames('language_custom');
        $manager->setModules($modules);

        $this->log("Importing custom translations into database...");

        $files = $manager->getFiles();

        $colors = new Opus_Util_ConsoleColors();

        if (count($files) > 0) {
            // Iterate through modules und TMX files in 'language_custom'
            foreach ($files as $module => $folders) {
                $this->importFolder($folders['language_custom'], $module);
                $this->removeFolder("/modules/$module/language_custom");
            }
        } else {
            $this->log($colors->yellow('No custom TMX files found'));
        }

        $this->log("Clearing translation cache...");
        Zend_Translate::clearCache();
    }

    /**
     * Import TMX files in folder for module.
     * @param $files
     * @param $module
     */
    public function importFolder($files, $module)
    {
        $colors = new Opus_Util_ConsoleColors();

        foreach ($files as $filename) {
            $path = "/modules/$module/language_custom/$filename";

            $this->log("Importing translations from '$path'...");
            $fullPath = APPLICATION_PATH . $path;

            $tmx = new Application_Translate_TmxFile();
            $tmx->load($fullPath);

            $translations = $tmx->toArray();

            $database = new Opus_Translate_Dao();

            $database->addTranslations($translations, $module);

            // Remove imported file
            if (is_writeable($fullPath)) {
                unlink($fullPath);
                $this->log("Removed file '$path'" . PHP_EOL);
            } else {
                $this->log($colors->red("Could not remove file '$path'") . PHP_EOL);
            }
        }
    }

    /**
     * Remove 'language_custom' folder if they are empty.
     * @param $path
     */
    public function removeFolder($path)
    {
        $colors = new Opus_Util_ConsoleColors();
        if (! (new \FilesystemIterator(APPLICATION_PATH . $path))->valid()) {
            rmdir(APPLICATION_PATH . $path);
            $this->log("Removed folder '$path'" . PHP_EOL);
        } else {
            $this->log($colors->red("Folder '$path' not removed, because it is not empty") . PHP_EOL);
        }
    }
}
