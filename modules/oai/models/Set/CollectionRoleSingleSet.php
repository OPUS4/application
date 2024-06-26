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
use Opus\Common\DocumentFinderInterface;
use Opus\Common\DocumentInterface;

/**
 * Single set for an entire CollectionRole.
 *
 * The OAI set contains all documents visible in OAI from an entire CollectionRole
 * independent of the collections containing the documents.
 *
 * If a collection of the CollectionRole is not visible in OAI, documents that are
 * only linked with that collection are not present in the OAI set.
 *
 * This can be used to have multiple collections for different open access variants
 * as well as a "closed_access" collections that is not visible in OAI.
 */
class Oai_Model_Set_CollectionRoleSingleSet extends Oai_Model_Set_CollectionSets
{
    /**
     * The OAI name of the collection role for which the class is responsible.
     *
     * @var string
     */
    private $roleOaiName;

    /**
     * Returns a single set if it contains documents.
     *
     * @param DocumentInterface|null $document
     * @return array
     */
    public function getSets($document = null)
    {
        $sets = [];

        $setName = $this->getRoleOaiName();
        $role    = CollectionRole::fetchByOaiName($setName);

        if ($document) {
            if ($role->isDocumentVisibleInOai($document->getId())) {
                $sets = [$setName => "Set for collection '" . $setName . "'"];
            }
        } else {
            // Return set if the collection role contains documents visible in OAI
            if ($role->hasOaiDocuments()) {
                $sets = [$setName => "Set for collection '" . $setName . "'"];
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
        $visibleCollections = $role->getVisibleCollections();
        $finder->setCollectionId($visibleCollections);
    }

    /**
     * Returns if the set type class supports the handling of given set name.
     *
     * @param Oai_Model_Set_SetName $setName
     * @return bool
     */
    public function supports($setName)
    {
        return $setName->getSetName() === $this->getRoleOaiName();
    }

    /**
     * Returns the role oai name.
     *
     * @return string
     */
    public function getRoleOaiName()
    {
        return $this->roleOaiName;
    }

    /**
     * Sets the role oai name.
     *
     * @param string|null $roleOaiName
     */
    public function setRoleOaiName($roleOaiName)
    {
        $this->roleOaiName = $roleOaiName;
    }
}
