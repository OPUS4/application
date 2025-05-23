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
 * Changes names of enrichment fields for doi based metadata import to new OPUS default.
 * Changes names of existing and adds new enrichment fields for conferences.
 *
 * Checks whether old fields exist and changes their names. If one or more old fields
 * are missing, the corresponding new fields are created unless the new fields do
 * already exist, too. In this case, an error message for manual revision is generated.
 *
 * Adapts key names for edited or added translations of the enrichment fields for
 * the doi based metadata import and conferences to the new names of the enrichment
 * fields.
 *
 * Checks whether old keys with edited or added translations exist and changes their
 * names. If the new translation key already exists with an edited or added translation,
 * an error message for manual revision is generated.
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
        $this->log("Updating enrichment fieds for doi import and conferences as well as their translations");
        $colors = new ConsoleColors();

        // Iterate through the keys
        foreach ($this->keyNames as $oldKeyString => $newKeyString) {
            $this->log("Updating '$oldKeyString' -> '$newKeyString'");
            $this->updateEnrichments($oldKeyString, $newKeyString, $colors);
            $this->updateTranslations($oldKeyString, $newKeyString, $colors);
        }
    }

    /**
     * Check and update enrichment field
     *
     * @param string $oldKeyString
     * @param string $newKeyString
     * @param object $colors
     */
    public function updateEnrichments($oldKeyString, $newKeyString, $colors)
    {
        // Identify already existing enrichment fields
        $oldField = EnrichmentKey::fetchByName($oldKeyString);
        $newField = EnrichmentKey::fetchByName($newKeyString);

        if ($oldField === null) {
            // If old and new enrichment field don't exist, the new enrichtment field is created
            if ($newField === null) {
                $newKey = EnrichmentKey::new();
                $this->updateEnrichmentKey($newKey, $newKeyString);
                $this->getLogger()->info("Old enrichment field '$oldKeyString' doesn't exist. New enrichment field '$newKeyString' didn't exist either and was created.");
            // If only the new enrichment field exists, no changes are needed
            } else {
                $this->getLogger()->info("Old enrichment field '$oldKeyString' doesn't exist. New enrichment field '$newKeyString' exists. Already up-to-date. No changes needed.");
            }
        } else {
            // If old and new enrichment field exist already, something seems to be wrong and should be checked
            if ($newField !== null) {
                $this->log($colors->red("Old enrichment field '$oldKeyString' and new one '$newKeyString' exist parallel. Please clean this up manually."));
            // If only the old enrichment field exists, it is updated to its new name
            } else {
                $this->updateEnrichmentKey($oldField, $newKeyString);
                $this->getLogger()->info("Old enrichment field '$oldKeyString' did exist. It has been updated to '$newKeyString'.");
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
     * @param  string $oldKeyString
     * @param  string $newKeyString
     * @param  object $colors
     */
    public function updateTranslations($oldKeyString, $newKeyString, $colors)
    {
        $manager = new Application_Translate_TranslationManager();

        // Identify already existing modified new translation keys
        $manager->setFilter($newKeyString);
        $newTranslations = $manager->getMergedTranslations();
        foreach (array_keys($newTranslations) as $newKey) {
            $translation = $manager->getTranslation($newKey);
            if (isset($translation['state']) && (in_array($translation['state'], ['edited', 'added']))) {
                $modNewKeys[$newKey] = '';
            }
        }

        // Identify modified old translation keys and update them to their new names
        $manager->setFilter($oldKeyString);
        $oldTranslations = $manager->getMergedTranslations();
        foreach (array_keys($oldTranslations) as $oldKey) {
            $translation = $manager->getTranslation($oldKey);

            if (isset($translation['state']) && (in_array($translation['state'], ['edited', 'added']))) {
                $newKey = str_replace($oldKeyString, $newKeyString, $oldKey);
                // If the new translation key exists and has been modified, something might be wrong and should be checked
                if ($manager->keyExists($newKey)) {
                    $this->log($colors->yellow("New translation key '$newKey' already exists. Cannot rename old key '$oldKey'. Please check this manually."));
                    // Unset modified new key to avoid provding redundant information to user
                    if (array_key_exists($newKey, $modNewKeys)) {
                        unset($modNewKeys[$newKey]);
                    }

                // If the new translation key doesn't exist, the old one is renamed
                } else {
                    $this->updateTranslationKey($oldKey, $translation, $newKey);
                    $this->getLogger()->info("Translation key '$oldKey' updated successfully to '$newKey'.");
                }
            // If the old translation key doesn't exist, no action is needed
            } else {
                $this->getLogger()->info("Old translation Key '$oldKey' was not edited. No changes needed.");
            }
        }

        // Provide information on modified new translation keys that already exist so
        // the user may check whether something might be wrong or everything is ok.
        if (isset($modNewKeys) && count(array_keys($modNewKeys)) > 0) {
            foreach (array_keys($modNewKeys) as $modNewKey) {
                $this->log($colors->yellow("New translation key '$modNewKey' exists and has already been modified. Old key doesn't exist or hasn't been modified. You should check this manually."));
            }
        } elseif (isset($oldTranslations) && count(array_keys($oldTranslations)) === 0) {
            $this->getLogger()->info("Translation keys are up-to-date. No changes needed.");
        }
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
