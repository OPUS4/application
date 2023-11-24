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
 * @copyright   Copyright (c) 2023, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\CollectionRole;

/**
 * Class for the "open_access" set type.
 */
class Oai_Model_Set_OpenAccessTypeSet implements Oai_Model_Set_SetTypeInterface
{
    private const SET_NAME = 'open_access';

    /**
     * Returns all sets of the set type.
     *
     * @param DocumentInterface|null $document
     * @return array
     */
    public function getSets($document = null)
    {
        $sets = [];

        if ($document) {
            foreach ($document->getCollection() as $collection) {
                $role = CollectionRole::get($collection->getRoleId());
                // TODO Why does $collection->getRole() return null for new collections in test methods?
                //$role          = $collection->getRole();
                $oaiSetName = $role->getOaiName();

                if (
                    $oaiSetName === self::SET_NAME &&
                    $collection->getVisible() &&
                    $role->getVisibleOai() &&
                    $role->getVisible() &&
                    $document->getServerState() === 'published'
                ) {
                    $sets[urlencode($oaiSetName)] = "Set for collection '" . trim($role->getName()) . "'";
                    break;
                }
            }
        } else {
            $sets = [self::SET_NAME => "Set for collection '" . self::SET_NAME . "'"];
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
        if ($setName->getSetPartsCount() > 1) {
            throw new Oai_Model_Exception(
                'The given set results in an empty list: ' . $setName->getFullSetName(),
                Oai_Model_Error::NORECORDSMATCH
            );
        }

        $role = CollectionRole::fetchByOaiName($setName->getSetName());
        if ($role === null) {
            throw new Oai_Model_Exception(
                'The given set results in an empty list: ' . $setName->getFullSetName(),
                Oai_Model_Error::NORECORDSMATCH
            );
        }
        $finder->setCollectionRoleId($role->getId());
    }

    /**
     * Returns if the set type class supports the handling of given set name.
     *
     * @param Oai_Model_Set_SetName $setName
     * @return bool
     */
    public function supports($setName)
    {
        return $setName->getSetName() === self::SET_NAME;
    }
}
