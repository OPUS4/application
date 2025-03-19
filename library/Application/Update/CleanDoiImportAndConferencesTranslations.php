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

use Opus\Translate\Dao;

/**
 * Adapts key names for edited or added translations of the enrichment fields for
 * the doi based metadata import and conferences to the new names of the enrichment
 * fields.
 *
 * Checks whether old keys with edited or added translations exist and changes their
 * names. If the new translation key already exists with an edited or added translation,
 * an error message for manual revision is generated.
 */
class Application_Update_CleanDoiImportAndConferencesTranslations extends Application_Update_PluginAbstract
{
    /** @var string[] */
    private $keyNames = [
        'opus_import_data'           => 'opus_doi_json',
        'local_crossrefDocumentType' => 'opus_crossrefDocumentType',
        'local_crossrefLicence'      => 'opus_crossrefLicence',
        'local_doiImportPopulated'   => 'opus_doiImportPopulated',
        'local_import_origin'        => 'opus_import_origin',
        'conference_title'           => 'OpusConferenceTitle',
        'conference_place'           => 'OpusConferencePlace',
        'conference_number'          => 'OpusConferenceNumber',
        'conference_year'            => 'OpusConferenceYear',
    ];

    public function run()
    {
        $manager = new Application_Translate_TranslationManager();

        foreach ($this->keyNames as $oldKeyString => $newKeyString) {
            $manager->setFilter("$newKeyString");
            $translationsNew = $manager->getMergedTranslations();

            foreach (array_keys($translationsNew) as $newKey) {
                $translation = $manager->getTranslation($newKey);
                if (isset($translation['state']) && (in_array($translation['state'], ['edited', 'added']))) {
                    $newKeys["$newKey"] = '';
                }
            }

            $manager->setFilter("$oldKeyString");
            $translationsOld = $manager->getMergedTranslations();

            foreach (array_keys($translationsOld) as $oldKey) {
                $translation = $manager->getTranslation($oldKey);

                if (isset($translation['state']) && (in_array($translation['state'], ['edited', 'added']))) {
                    $newKey = str_replace($oldKeyString, $newKeyString, $oldKey);
                    if ($manager->keyExists($newKey)) {
                        $this->log("New translation key '$newKey' already exists. Cannot rename old key '$translation[key]'. Please check this manually.");
                        unset($newKeys[$newKey]);
                    } else {
                        $this->updateTranslationKey($oldKey, $translation, $newKey);
                        $this->log("Translation key '$oldKey' updated successfully to '$newKey'.");
                    }
                } else {
                    $this->log("Old translation Key '$oldKey' was not edited. No changes needed.");
                }
            }
        }

        if (is_array($newKeys) && count(array_keys($newKeys)) > 0) {
            $newKeyStrings = implode("\n", array_keys($newKeys));
            $this->log("Following new keys exist already with edited translations. You should check them manually:\n$newKeyStrings");
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
