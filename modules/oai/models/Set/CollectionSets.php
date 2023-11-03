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

use Opus\Common\CollectionRole;
use Opus\Common\DocumentInterface;

/**
 * Class for the "collection" set type
 */
class Oai_Model_Set_CollectionSets extends Application_Model_Abstract implements Oai_Model_Set_SetTypeInterface
{
    /** Regexp pattern to check if a full set name is valid. */
    private const SET_PATTERN = "/^([A-Za-z0-9\-_\.!~\*'\(\)]+)(:[A-Za-z0-9\-_\.!~\*'\(\)]+)*$/";

    /** Regexp pattern to check if a set name or subset name is valid. */
    private const SET_PART_PATTERN = '/^[A-Za-z0-9\-_\.!~\*\'\(\)]+$/';

    /**
     * Returns oai sets for collections.
     *
     * @param DocumentInterface|null $document
     * @return array
     */
    public function getSets($document = null)
    {
        $sets   = [];
        $logger = $this->getLogger();

        if ($document) {
            $setSpecs = $this->getSetsFromCollections($document->getCollection());
            foreach ($setSpecs as $setSpec => $name) {
                if (preg_match(self::SET_PATTERN, $setSpec)) {
                    $sets[$setSpec] = $name;
                    continue;
                }
                $logger->info("skipping invalid setspec: " . $setSpec);
            }
        } else {
            $oaiRolesSets = CollectionRole::fetchAllOaiEnabledRoles();

            foreach ($oaiRolesSets as $result) {
                if ($result['oai_name'] === 'doc-type') {
                    continue;
                }

                if (0 === preg_match(self::SET_PART_PATTERN, $result['oai_name'])) {
                    $msg = "Invalid SetSpec (oai_name='" . $result['oai_name'] . "'). "
                        . " Please check collection role " . $result['id'] . ". "
                        . " Allowed characters are " . self::SET_PART_PATTERN . ".";
                    $logger->err("OAI-PMH: $msg");
                    continue;
                }

                $setSpec = $result['oai_name'];
                // $count = $result['count'];
                $sets[$setSpec] = "Set for collection '" . $result['oai_name'] . "'";

                $sets = array_merge($sets, $this->getSetsForCollectionRole($setSpec, $result['id']));
            }
        }

        return $sets;
    }

    /**
     * Configures the passed Finder according to the specified set.
     *
     * @param DocumentFinderInterface $finder
     * @param Oai_Model_Set_SetName   $setName
     * @throws Oai_Model_Exception
     */
    public function configureFinder($finder, $setName)
    {
        // TODO Behavior with invalid set names should be reconsidered.
        if ($setName->getSetPartsCount() < 1 || $setName->getSetPartsCount() > 2) {
            $msg = "Invalid SetSpec: Must be in format 'set:subset'.";
            throw new Oai_Model_Exception($msg);
        }

        // Trying to locate collection role and filter documents.
        $role = CollectionRole::fetchByOaiName($setName->getSetName());
        if ($role === null) {
            $msg = "Invalid SetSpec: Top level set does not exist.";
            throw new Oai_Model_Exception($msg);
        }
        $finder->setCollectionRoleId($role->getId());

        $subsetName = $setName->getSubsetName();

        // Trying to locate given collection and filter documents.
        if ($subsetName !== null) {
            $foundSubsets = array_filter(
                $role->getOaiSetNames(),
                function ($s) use ($subsetName) {
                    return $s['oai_subset'] === $subsetName;
                }
            );

            if (count($foundSubsets) < 1) {
                $emptySubsets = array_filter($role->getAllOaiSetNames(), function ($s) use ($subsetName) {
                    return $s['oai_subset'] === $subsetName;
                });

                if (count($emptySubsets) === 1) {
                    throw new Oai_Model_Set_SetException('Empty subset: ' . $subsetName);
                } else {
                    $msg = "Invalid SetSpec: Subset does not exist.";
                    throw new Oai_Model_Exception($msg);
                }
            }

            foreach ($foundSubsets as $subset) {
                if ($subset['oai_subset'] !== $subsetName) {
                    $msg = "Invalid SetSpec: Internal error.";
                    throw new Oai_Model_Exception($msg);
                }
                $finder->setCollectionId($subset['id']);
            }
        }
    }

    /**
     * Returns sets for collections of a collection role.
     *
     * @param string $setSpec OAI name for collection role
     * @param int    $roleId int Database ID of role
     * @return array
     */
    private function getSetsForCollectionRole($setSpec, $roleId)
    {
        $logger = $this->getLogger();

        $sets = [];

        $role = CollectionRole::get($roleId);
        foreach ($role->getOaiSetNames() as $subset) {
            $subSetSpec = "$setSpec:" . $subset['oai_subset'];
            // $subSetCount = $subset['count'];

            if (0 === preg_match(self::SET_PART_PATTERN, $subset['oai_subset'])) {
                $msg = "Invalid SetSpec (oai_name='" . $subset['oai_subset'] . "')."
                    . " Please check collection " . $subset['id'] . ". "
                    . " Allowed characters are [" . self::SET_PART_PATTERN . "].";
                $logger->err("OAI-PMH: $msg");
                continue;
            }

            $sets[$subSetSpec] = "Subset '" . $subset['oai_subset'] . "'"
                . " for collection '" . $setSpec . "'"
                . ': "' . trim($subset['name']) . '"';
        }

        return $sets;
    }

    /**
     * @param CollectionInterface[] $collections
     * @return array
     */
    protected function getSetsFromCollections($collections)
    {
        $sets = [];

        foreach ($collections as $collection) {
            if (! $collection->getVisible()) {
                continue;
            }

            $oaiSubsetName = $collection->getOaiSubset();
            if (empty($oaiSubsetName)) {
                continue;
            }

            $role = $collection->getRole();

            if (! $role->getVisibleOai() || ! $role->getVisible()) {
                continue;
            }

            $oaiSetName = $role->getOaiName();
            if (empty($oaiSetName)) {
                continue;
            }

            $sets[urlencode($oaiSetName)] = "Set for collection '" . trim($role->getName()) . "'";

            $sets[urlencode($oaiSetName) . ':' . urlencode($oaiSubsetName)] = "Subset '" . $oaiSubsetName . "'"
                . " for collection '" . $oaiSetName . "'"
                . ': "' . trim($collection->getName()) . '"';
        }

        return $sets;
    }

    /**
     * Returns if the set type class supports the handling of given set name.
     *
     * Set type class is always responsible for an entire collection role, therefore only the set name and not
     * the subset name is checked.
     *
     * @param Oai_Model_Set_SetName $setName
     * @return bool
     */
    public function supports($setName)
    {
        $roleOaiNames = array_column(CollectionRole::fetchAllOaiEnabledRoles(), 'oai_name');
        return in_array($setName->getSetName(), $roleOaiNames);
    }
}
