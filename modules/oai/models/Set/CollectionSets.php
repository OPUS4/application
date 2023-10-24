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

/**
 * Class for the "collection" set type
 */
class Oai_Model_Set_CollectionSets extends Application_Model_Abstract implements Oai_Model_Set_SetTypeInterface
{
    /**
     * Returns oai sets for collections.
     *
     * @return array
     */
    public function getSets()
    {
        $sets = [];

        $logger = $this->getLogger();

        $setSpecPattern = Oai_Model_Set_SetSpec::SET_SPEC_PATTERN;

        $oaiRolesSets = CollectionRole::fetchAllOaiEnabledRoles();

        foreach ($oaiRolesSets as $result) {
            if ($result['oai_name'] === 'doc-type') {
                continue;
            }

            if (0 === preg_match("/^$setSpecPattern$/", $result['oai_name'])) {
                $msg = "Invalid SetSpec (oai_name='" . $result['oai_name'] . "'). "
                    . " Please check collection role " . $result['id'] . ". "
                    . " Allowed characters are $setSpecPattern.";
                $logger->err("OAI-PMH: $msg");
                continue;
            }

            $setSpec = $result['oai_name'];
            // $count = $result['count'];
            $sets[$setSpec] = "Set for collection '" . $result['oai_name'] . "'";

            $sets = array_merge($sets, $this->getSetsForCollectionRole($setSpec, $result['id']));
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
        $setTypeName = $setName->getSetTypeName();
        $subsetName  = $setName->getSubsetName();

        // Trying to locate collection role and filter documents.
        $role = CollectionRole::fetchByOaiName($setTypeName);
        if ($role === null) {
            $msg = "Invalid SetSpec: Top level set does not exist.";
            throw new Oai_Model_Exception($msg);
        }
        $finder->setCollectionRoleId($role->getId());

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

        $setSpecPattern = Oai_Model_Set_SetSpec::SET_SPEC_PATTERN;

        $role = CollectionRole::get($roleId);
        foreach ($role->getOaiSetNames() as $subset) {
            $subSetSpec = "$setSpec:" . $subset['oai_subset'];
            // $subSetCount = $subset['count'];

            if (0 === preg_match("/^$setSpecPattern$/", $subset['oai_subset'])) {
                $msg = "Invalid SetSpec (oai_name='" . $subset['oai_subset'] . "')."
                    . " Please check collection " . $subset['id'] . ". "
                    . " Allowed characters are [$setSpecPattern].";
                $logger->err("OAI-PMH: $msg");
                continue;
            }

            $sets[$subSetSpec] = "Subset '" . $subset['oai_subset'] . "'"
                . " for collection '" . $setSpec . "'"
                . ': "' . trim($subset['name']) . '"';
        }

        return $sets;
    }
}
