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

    /** @var Application_Update_UpdateTranslations */
    private $translationUpdater;

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
     * @param string $oldEnrichmentKey
     * @param string $newEnrichmentKey
     */
    public function updateTranslations($oldEnrichmentKey, $newEnrichmentKey)
    {
        $colors = new ConsoleColors();
        $output = $this->getOutput();

        $conflictKeys = [];

        $translationUpdater   = $this->getTranslationUpdater();
        $enrichmentKeysHelper = new Admin_Model_EnrichmentKeys();

        $oldTranslationKeys = $enrichmentKeysHelper->getTranslationKeys($oldEnrichmentKey);
        $newTranslationKeys = $enrichmentKeysHelper->getTranslationKeys($newEnrichmentKey);

        foreach ($oldTranslationKeys as $patternName => $oldTranslationKey) {
            $newTranslationKey = $newTranslationKeys[$patternName];
            if ($translationUpdater->update($oldTranslationKey, $newTranslationKey) !== 0) {
                $conflictKeys[] = $newTranslationKey;
            }
        }

        // Provide information on modified new translation keys that already exist so
        // the user may check if something is wrong or everything is ok.
        // TODO does this make sense? Does it provide useful information?
        if (count($conflictKeys) > 0) {
            $output->writeln("The following keys with modified translations already existed for '{$newEnrichmentKey}'.");
            foreach ($conflictKeys as $key) {
                $output->writeln($colors->yellow($key));
            }
        }
    }

    /**
     * @return Application_Update_UpdateTranslations
     */
    public function getTranslationUpdater()
    {
        if ($this->translationUpdater === null) {
            $this->translationUpdater = new Application_Update_UpdateTranslations();
            $this->translationUpdater->setOutput($this->getOutput());
        }

        return $this->translationUpdater;
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

        if ($this->translationUpdater !== null) {
            $this->translationUpdater->setOutput($this->output);
        }

        return $this;
    }
}
