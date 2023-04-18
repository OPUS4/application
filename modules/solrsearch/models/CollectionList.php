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
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\Collection;
use Opus\Common\CollectionInterface;
use Opus\Common\CollectionRole;
use Opus\Common\CollectionRoleInterface;
use Opus\Common\Model\NotFoundException;

class Solrsearch_Model_CollectionList
{
    /** @var CollectionInterface */
    private $collection;

    /** @var CollectionRoleInterface */
    private $collectionRole;

    /**
     * @param int $collectionId
     * @throws Solrsearch_Model_Exception
     */
    public function __construct($collectionId)
    {
        if ($collectionId === null) {
            throw new Solrsearch_Model_Exception('Could not browse collection due to missing id parameter.', 400);
        }

        $collection = null;
        try {
            $collection = Collection::get((int) $collectionId);
        } catch (NotFoundException $e) {
            throw new Solrsearch_Model_Exception("Collection with id '" . $collectionId . "' does not exist.", 404);
        }

        // check if an invisible collection exists along the path to the root collection
        foreach ($collection->getParents() as $parent) {
            if (! $parent->isRoot() && ! $parent->getVisible()) {
                throw new Solrsearch_Model_Exception("Collection with id '" . $collectionId . "' is not visible.", 404);
            }
        }

        $collectionRole = null;
        try {
            $collectionRole = CollectionRole::get($collection->getRoleId());
        } catch (NotFoundException $e) {
            throw new Solrsearch_Model_Exception(
                "Collection role with id '" . $collection->getRoleId() . "' does not exist."
            );
        }

        if (! ($collectionRole->getVisible() && $collectionRole->getVisibleBrowsingStart())) {
            throw new Solrsearch_Model_Exception(
                "Collection role with id '" . $collectionRole->getId() . "' is not visible."
            );
        }

        // additional root collection check
        $rootCollection = $collectionRole->getRootCollection();
        if ($rootCollection !== null) {
            // check if at least one visible child exists or current collection has at least one associated document
            if (! $rootCollection->hasVisibleChildren() && count($rootCollection->getPublishedDocumentIds()) === 0) {
                throw new Solrsearch_Model_Exception(
                    "Collection role with id '" . $collectionRole->getId()
                    . "' is not clickable and therefore not displayed."
                );
            }
        }

        $this->collectionRole = $collectionRole;
        $this->collection     = $collection;
    }

    /**
     * @return bool
     */
    public function isRootCollection()
    {
        return count($this->collection->getParents()) === 1;
    }

    /**
     * Returns an array of Collection objects along the path to the root.
     * In case the current collection is a root collection an empty array is returned.
     *
     * @return array
     */
    public function getParents()
    {
        $parents      = $this->collection->getParents();
        $numOfParents = count($parents);
        if ($numOfParents < 2) {
            return [];
        }
        // remove the first array element and reverse order of remaining array elements
        $results = [];
        for ($i = 1; $i < $numOfParents; $i++) {
            $results[] = $parents[$numOfParents - $i];
        }
        return $results;
    }

    /**
     * @return array of Collection
     */
    public function getChildren()
    {
        $children = $this->collection->getVisibleChildren();

        if ($this->collectionRole->getHideEmptyCollections()) {
            // Collection ausblenden, wenn ihr selbst und den Kind-Collections keine Dokumente zugeordnet
            $children = array_filter($children, function (CollectionInterface $collection) {
                return $collection->getNumSubtreeEntries() > 0;
            });
        }

        return $children;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->collection->getDisplayNameForBrowsingContext($this->collectionRole);
    }

    /**
     * @return string|null
     */
    public function getTheme()
    {
        return $this->collection->getTheme();
    }

    /**
     * @return int
     */
    public function getCollectionId()
    {
        return $this->collection->getId();
    }

    /**
     * @return string
     */
    public function getCollectionRoleTitle()
    {
        return 'default_collection_role_' . $this->getCollectionRoleTitlePlain();
    }

    /**
     * @return string
     */
    public function getCollectionRoleTitlePlain()
    {
        return $this->collectionRole->getDisplayName('browsing');
    }

    /**
     * @return CollectionRoleInterface
     */
    public function getCollectionRole()
    {
        return $this->collectionRole;
    }
}
