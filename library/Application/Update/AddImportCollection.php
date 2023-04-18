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
 * @copyright   Copyright (c) 2017, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\Collection;
use Opus\Common\CollectionRole;
use Opus\Common\EnrichmentKey;

/**
 * Add import collection if necessary.
 */
class Application_Update_AddImportCollection extends Application_Update_PluginAbstract
{
    public function run()
    {
        // add enrichment keys for imports
        $keys = ['opus.import.checksum', 'opus.import.date', 'opus.import.file', 'opus.import.user'];

        foreach ($keys as $key) {
            $this->addEnrichmentKey($key);
        }

        // add collection role for imports
        // add default collection for imports
        $this->addCollection();
    }

    /**
     * @param string $name
     */
    public function addEnrichmentKey($name)
    {
        $enrichmentKey = EnrichmentKey::fetchByName($name);

        if ($enrichmentKey === null) {
            $this->log("Creating enrichment key '$name' ... ");
            $enrichmentKey = EnrichmentKey::new();
            $enrichmentKey->setName($name);
            $enrichmentKey->store();
        }
    }

    public function addCollection()
    {
        $collectionRole = CollectionRole::fetchByName('Import');

        if ($collectionRole === null) {
            $this->log("Creating collection role 'Import' ... ");

            $collectionRole = CollectionRole::new();

            $collectionRole->setName('Import');
            $collectionRole->setOaiName('import');
            $collectionRole->setVisible(0);
            $collectionRole->setVisibleBrowsingStart(0);
            $collectionRole->setDisplayBrowsing('Number');
            $collectionRole->setDisplayFrontdoor('Number');
            $collectionRole->setVisibleOai(0);
            $collectionRole->setPosition(CollectionRole::getLastPosition() + 1);
            $root = $collectionRole->addRootCollection();
            $collectionRole->store();
        } else {
            $this->log("Collection role 'Import' already exists.");
        }

        $root = $collectionRole->getRootCollection();

        $collection = $collectionRole->getCollectionByOaiSubset('import');

        if ($collection === null) {
            $this->log("Creating collection 'import' ... ");

            $collection = Collection::new();
            $collection->setName('Import');
            $collection->setNumber('import');
            $collection->setOaiSubset('import');
            $root->addFirstChild($collection);
            $collectionRole->store();
        } else {
            $this->log("Collection 'import' already exists.");
        }
    }
}
