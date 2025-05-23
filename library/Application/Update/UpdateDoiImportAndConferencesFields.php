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
 */
class Application_Update_UpdateDoiImportAndConferencesFields extends Application_Update_PluginAbstract
{
    /** @var array[] */
    private $keyNames = [
        'opus_import_data'           => 'opus_doi_json',
        'local_crossrefDocumentType' => 'opus_crossrefDocumentType',
        'local_crossrefLicence'      => 'opus_crossrefLicence',
        'local_doiImportPopulated'   => 'opus_doiImportPopulated',
        'local_import_origin'        => 'opus_import_origin',
        'conference_title'           => 'OpusConferenceName',
        'conference_place'           => 'OpusConferencePlace',
        'conference_number'          => 'OpusConferenceNumber',
        'conference_year'            => 'OpusConferenceYear',
    ];

    public function run()
    {
        $this->log('Updating enrichment keys for DOI import and conferences (including their translation keys)...');
        // Iterate through the keys
        foreach ($this->keyNames as $oldKeyString => $newKeyString) {
            $this->log("Updating '$oldKeyString' -> '$newKeyString'");
            $this->updateEnrichments($oldKeyString, $newKeyString);
            $this->updateTranslations($oldKeyString, $newKeyString);
        }
    }

    /**
     * Check and update enrichment field
     *
     * @param string $oldKeyString
     * @param string $newKeyString
     */
    public function updateEnrichments($oldKeyString, $newKeyString)
    {
        $colors = new ConsoleColors();

        // Identify already existing enrichment fields
        $oldField = EnrichmentKey::fetchByName($oldKeyString);
        $newField = EnrichmentKey::fetchByName($newKeyString);

        if ($oldField === null) {
            if ($newField === null) {
                // If old and new enrichment don't exist, the new enrichment field is created
                $newKey = EnrichmentKey::new();
                $this->updateEnrichmentKey($newKey, $newKeyString);
                $this->log("New enrichment '{$newKeyString}' created.");
            } else {
                // If only the new enrichment exists, no changes are needed
                $this->log("New enrichment field '{$newKeyString}' already exists. No changes needed.");
            }
        } else {
            if ($newField !== null) {
                // If old and new enrichment exist, something seems to be wrong and should be checked manually
                $this->log($colors->red(
                    "Old ({$oldKeyString}) and new ({$newKeyString}) enrichment exist. Please clean up manually."
                ));
            } else {
                // If only the old enrichment field exists, it is updated to its new name
                $this->updateEnrichmentKey($oldField, $newKeyString);
                $this->log("Enrichment key '{$oldKeyString}' has been changed to '{$newKeyString}'.");
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
                    $this->log($colors->yellow(
                        "New key '{$newKey}' already exists. Cannot rename old key '{$oldKey}'. Please check this manually."
                    ));
                    $conflictKeys[] = $newKey;
                } else {
                    // If the new translation key doesn't exist, the old one is renamed
                    $this->updateTranslationKey($oldKey, $translation, $newKey);
                    $this->log("Translation key '{$oldKey}' updated to '{$newKey}'.");
                }
            }
        }

        // Provide information on modified new translation keys that already exist so
        // the user may check if something is wrong or everything is ok.
        // TODO does this make sense? Does it provide useful information?
        $remainingKeys = array_diff($modNewKeys, $conflictKeys);
        if (count($remainingKeys) > 0) {
            $this->log("The following keys with modified translations already existed for '{$newKeyString}'.");
            foreach ($remainingKeys as $key) {
                $this->log($colors->yellow($key));
            }
        }
    }

    /**
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
}
