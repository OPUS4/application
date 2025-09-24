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
use Opus\Common\EnrichmentKey;
use Opus\Translate\Dao;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Updates enrichment fields used by DOI import functionality.
 *
 * Changes names of enrichment fields for doi based metadata import to new OPUS default.
 *
 * Changes names of existing and adds new enrichment fields for conferences.
 *
 * Adapts key names for edited or added translations of the enrichment fields for
 * the doi based metadata import and conferences to the new names of the enrichment
 * fields.
 *
 * TODO use ConsoleOutput (support logging)
 */
class Application_Update_UpdateEnrichments
{
    /** @var OutputInterface */
    private $output;

    /**
     * @param array $changes
     * @return void
     */
    public function update($changes)
    {
        $output = $this->getOutput();
        $output->writeln(
            'Updating enrichment keys for DOI import and conferences (including their translation keys)...'
        );
        // Iterate through the keys
        foreach ($changes as $oldKey => $newKey) {
            $output->writeln("Updating '{$oldKey}' -> '{$newKey}'");
            $this->updateEnrichment($oldKey, $newKey);
            $this->updateTranslations($oldKey, $newKey);
        }
    }

    /**
     * Check and update enrichment field
     *
     * @param string $oldKeyString
     * @param string $newKeyString
     */
    public function updateEnrichment($oldKeyString, $newKeyString)
    {
        $colors = new ConsoleColors();
        $output = $this->getOutput();

        // Identify already existing enrichment fields
        $oldField = EnrichmentKey::fetchByName($oldKeyString);
        $newField = EnrichmentKey::fetchByName($newKeyString);

        if ($oldField === null) {
            if ($newField === null) {
                // If old and new enrichment don't exist, the new enrichment field is created
                $newKey = EnrichmentKey::new();
                $this->updateEnrichmentKey($newKey, $newKeyString);
                $output->writeln("New enrichment '{$newKeyString}' created.");
            } else {
                // If only the new enrichment exists, no changes are needed
                $output->writeln("New enrichment field '{$newKeyString}' already exists. No changes needed.");
            }
        } else {
            if ($newField !== null) {
                // If old and new enrichment exist, something seems to be wrong and should be checked manually
                $output->writeln($colors->red(
                    "Old ({$oldKeyString}) and new ({$newKeyString}) enrichment exist. Please clean up manually."
                ));
            } else {
                // If only the old enrichment field exists, it is updated to its new name
                $this->updateEnrichmentKey($oldField, $newKeyString);
                $output->writeln("Enrichment key '{$oldKeyString}' has been changed to '{$newKeyString}'.");
            }
        }
    }

    /**
     * Update enrichment field key
     *
     * @param object $enrichmentKey
     * @param string $newKey
     */
    public function updateEnrichmentKey($enrichmentKey, $newKey)
    {
        $enrichmentKey->setName($newKey);
        $enrichmentKey->store();
    }

    /**
     * Update translations
     *
     * @param string $oldKeyString
     * @param string $newKeyString
     */
    public function updateTranslations($oldKeyString, $newKeyString)
    {
        $colors = new ConsoleColors();
        $output = $this->getOutput();

        // Identify already existing modified new translation keys (in database)
        $modNewKeys   = $this->getModifiedTranslationKeys($newKeyString);
        $conflictKeys = [];

        // Identify modified old translation keys and update them to their new names
        $manager = new Application_Translate_TranslationManager();
        $manager->setFilter($oldKeyString);
        $oldTranslations = $manager->getMergedTranslations(); // TODO expensive, seems unnecessary just for keys in DB

        foreach (array_keys($oldTranslations) as $oldKey) {
            $translation = $manager->getTranslation($oldKey);

            if (isset($translation['state']) && (in_array($translation['state'], ['edited', 'added']))) {
                $newKey = str_replace($oldKeyString, $newKeyString, $oldKey);
                if ($manager->keyExists($newKey)) {
                    // New translation key exists and has been modified, something might be wrong and should be checked
                    $output->writeln($colors->yellow(
                        "New key '{$newKey}' already exists. Cannot rename old key '{$oldKey}'. Please check this manually."
                    ));
                    $conflictKeys[] = $newKey;
                } else {
                    // If the new translation key doesn't exist, the old one is renamed
                    $this->updateTranslationKey($oldKey, $translation, $newKey);
                    $output->writeln("Translation key '{$oldKey}' updated to '{$newKey}'.");
                }
            }
        }

        // Provide information on modified new translation keys that already exist so
        // the user may check if something is wrong or everything is ok.
        // TODO does this make sense? Does it provide useful information?
        $remainingKeys = array_diff($modNewKeys, $conflictKeys);
        if (count($remainingKeys) > 0) {
            $output->writeln("The following keys with modified translations already existed for '{$newKeyString}'.");
            foreach ($remainingKeys as $key) {
                $output->writeln($colors->yellow($key));
            }
        }
    }

    /**
     * Returns all modified (added/edited) translation keys for Enrichment.
     *
     * @param string $filterKey
     * @return array
     */
    protected function getModifiedTranslationKeys($filterKey)
    {
        $manager = new Application_Translate_TranslationManager();
        $manager->setFilter($filterKey);

        $modifiedKeys = [];

        $translations = $manager->getMergedTranslations();
        foreach (array_keys($translations) as $key) {
            $translation = $manager->getTranslation($key);
            if (isset($translation['state']) && (in_array($translation['state'], ['edited', 'added']))) {
                $modifiedKeys[] = $key;
            }
        }

        return $modifiedKeys;
    }

    /**
     * Insert new key with translations, delete old key
     *
     * @param string $oldKey
     * @param array  $translation
     * @param string $newKey
     */
    public function updateTranslationKey($oldKey, $translation, $newKey)
    {
        $dao = new Dao();
        $dao->remove($oldKey);
        $dao->setTranslation($newKey, $translation['translations'], $translation['module']);
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
