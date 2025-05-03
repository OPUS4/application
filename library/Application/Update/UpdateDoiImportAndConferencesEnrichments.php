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

use Opus\Common\EnrichmentKey;

/**
 * Changes names of enrichment fields for doi based metadata import to new OPUS default.
 * Changes names of existing and adds new enrichment fields for conferences.
 *
 * Checks whether old fields exist and changes their names. If one or more old fields
 * are missing, the corresponding new fields are created unless the new fields do
 * already exist, too. In this case, an error message for manual revision is generated.
 */
class Application_Update_UpdateDoiImportAndConferencesEnrichments extends Application_Update_PluginAbstract
{
    /** @var string[] */
    private $keyNames = [
        'opus_import_data'           => 'opus_doi_json',
        'local_crossrefDocumentType' => 'opus_crossrefDocumentType',
        'local_crossrefLicence'      => 'opus_crossrefLicence',
        'local_doiImportPopulated'   => 'opus_doiImportPopulated',
        'local_import_origin'        => 'opus_import_origin',
        'conference_place'           => 'OpusConferencePlace',
        'conference_title'           => 'OpusConferenceName',
        'conference_number'          => 'OpusConferenceNumber',
        'conference_year'            => 'OpusConferenceYear',
    ];

    public function run()
    {
        foreach ($this->keyNames as $oldName => $newName) {
            $enrichmentKey    = EnrichmentKey::fetchByName($oldName);
            $enrichmentKeyNew = EnrichmentKey::fetchByName($newName);

            if ($enrichmentKey === null) {
                $this->log("Old enrichment key '$oldName' doesn't exist.");

                if ($enrichmentKeyNew === null) {
                    $this->getLogger()->info("New enrichment key '$newName' doesn't exist.");
                    $this->log("Creating new enrichment key '$newName' ...");
                    $enrichmentKey = EnrichmentKey::new();
                    $enrichmentKey->setName($newName);
                    $enrichmentKey->store();
                    $this->getLogger()->info("New enrichment key '$newName' created.");
                } else {
                    $this->log("New enrichment key '$newName' already exists. No changes needed.");
                }
            } else {
                if ($enrichmentKeyNew !== null) {
                    $this->log("Old enrichment key '$oldName' and new one '$newName' exist parallel. Please clean this up manually.");
                } else {
                    $this->log("Updating enrichment key '$oldName' -> '$newName' ...");
                    $enrichmentKey->setName($newName);
                    $enrichmentKey->store();
                    $this->getLogger()->info("Enrichment key '$oldName' updated to '$newName'.");
                }
            }
        }
    }
}
