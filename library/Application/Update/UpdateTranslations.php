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
 * @copyright   Copyright (c) 2025, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\Console\ConsoleColors;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Updates translation keys.
 */
class Application_Update_UpdateTranslations
{
    /** @var OutputInterface */
    private $output;

    /** @var Application_Translate_TranslationManager */
    private $translationManager;

    /**
     * @param string $oldTranslationKey
     * @param string $newTranslationKey
     * @return int
     *
     * TODO change to work with full translation keys (not enrichment specific)
     */
    public function update($oldTranslationKey, $newTranslationKey)
    {
        $colors   = new ConsoleColors();
        $output   = $this->getOutput();
        $manager  = $this->getTranslationManager();
        $database = $manager->getDatabase();

        if ($database->getTranslation($oldTranslationKey) !== null) {
            if ($database->getTranslation($newTranslationKey) !== null) {
                // New translation key exists and has been modified, something might be wrong and should be checked
                $output->writeln($colors->yellow(
                    "New key '{$newTranslationKey}' already exists. Cannot rename old key '{$oldTranslationKey}'. Please check this manually."
                ));
                return 1;
            } else {
                // If the new translation key doesn't exist, the old one is renamed
                $this->updateTranslationKey($oldTranslationKey, $newTranslationKey);
                $output->writeln("Translation key '{$oldTranslationKey}' updated to '{$newTranslationKey}'.");
            }
        }

        return 0;
    }

    /**
     * Insert new key with translations, delete old key
     *
     * @param string $oldTranslationKey
     * @param string $newTranslationKey
     */
    public function updateTranslationKey($oldTranslationKey, $newTranslationKey)
    {
        $manager = $this->getTranslationManager();

        $translation = $manager->getTranslation($oldTranslationKey);

        $dao = $manager->getDatabase();
        $dao->remove($oldTranslationKey);
        $dao->setTranslation($newTranslationKey, $translation['translations'], $translation['module']);
    }

    /**
     * @return Application_Translate_TranslationManager
     */
    public function getTranslationManager()
    {
        if ($this->translationManager === null) {
            $this->translationManager = new Application_Translate_TranslationManager();
        }

        return $this->translationManager;
    }

    /**
     * @return OutputInterface
     */
    public function getOutput()
    {
        if ($this->output === null) {
            $this->output = new ConsoleOutput();
        }

        return $this->output;
    }

    /**
     * @param OutputInterface $output
     * @return $this
     */
    public function setOutput($output)
    {
        $this->output = $output;
        return $this;
    }
}
