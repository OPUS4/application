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

use Opus\Common\Console\ConsoleColors;

/**
 * TODO harden code
 * TODO move generic functionality to separate class (maybe TranslationManager)
 */
class Application_Update_ImportStaticPages extends Application_Update_PluginAbstract
{
    /** @var bool */
    private $removeFilesEnabled = true;

    public function run()
    {
        $help  = new Home_Model_HelpFiles();
        $files = $help->getFiles();

        $path = $help->getHelpPath();

        $this->log('Importing static pages from folder');
        $this->log($path);

        $colors = new ConsoleColors();

        $this->log('Importing \'contact\' texts...');
        if (count(array_intersect($files, ['contact.en.txt', 'contact.de.txt'])) > 0) {
            $this->importFilesAsKey('contact', 'help_content_contact', 'home');
        } else {
            $this->log($colors->yellow('No text files for \'contact\' found.'));
        }

        $this->log('Importing \'imprint\' texts...');
        if (count(array_intersect($files, ['imprint.en.txt', 'imprint.de.txt'])) > 0) {
            $this->importFilesAsKey('imprint', 'help_content_imprint', 'home');
        } else {
            $this->log($colors->yellow('No text files for \'imprint\' found.'));
        }
    }

    /**
     * Import set of files as single translation key.
     *
     * Format: [NAME].[LANG].txt
     *
     * @param string $name
     * @param string $key
     * @param string $module
     */
    public function importFilesAsKey($name, $key, $module)
    {
        $translations = $this->getTranslations($name);

        $manager = new Application_Translate_TranslationManager();

        if (! empty($translations)) {
            $manager->setTranslation($key, $translations, $module);

            $files = $this->getFiles($name);

            if ($this->isRemoveFilesEnabled()) {
                $this->removeFiles($files);
            }
        } else {
            $colors = new ConsoleColors();
            $this->log($colors->red("No texts for '$name' found."));
        }

        $manager->clearCache();
    }

    /**
     * @param string $name
     * @return array
     */
    public function getTranslations($name)
    {
        $files = $this->getFiles($name);

        $translation = [];

        if (count($files) > 0) {
            foreach ($files as $name) {
                $parts = explode('.', $name);
                $lang  = $parts[1];
                $value = $this->getContent($name);

                $translation[$lang] = $value;
            }
        }

        return $translation;
    }

    /**
     * @param string|null $name
     * @return array
     */
    public function getFiles($name = null)
    {
        $helpFiles = new Home_Model_HelpFiles();
        $files     = $helpFiles->getFiles();

        if ($name !== null) {
            $files = array_filter($files, function ($value) use ($name) {
                return strpos($value, $name) === 0;
            });
        }

        return array_values($files);
    }

    /**
     * @param string $name
     * @return string
     */
    public function getContent($name)
    {
        $help = new Home_Model_HelpFiles();

        $path = $help->getHelpPath() . $name;

        $content = file_get_contents($path);

        return trim($content);
    }

    /**
     * @param array $files
     */
    public function removeFiles($files)
    {
        $help = new Home_Model_HelpFiles();
        foreach ($files as $name) {
            $path = $help->getHelpPath() . $name;

            if (is_writable($path)) {
                rename($path, $path . '.imported');
            }
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
