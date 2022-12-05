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
use Opus\Common\Model\NotFoundException;

class Solrsearch_Model_CollectionList
{

    private $_collection;

    private $_collectionRole;

    public function __construct($collectionId)
    {
        if (is_null($collectionId)) {
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
            if (! $parent->isRoot() && $parent->getVisible() !== '1') {
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

        if (! ($collectionRole->getVisible() === '1' && $collectionRole->getVisibleBrowsingStart() === '1')) {
            throw new Solrsearch_Model_Exception(
                "Collection role with id '" . $collectionRole->getId() . "' is not visible."
            );
        }

        // additional root collection check
        $rootCollection = $collectionRole->getRootCollection();
        if (! is_null($rootCollection)) {
            // check if at least one visible child exists or current collection has at least one associated document
            if (! $rootCollection->hasVisibleChildren() && count($rootCollection->getPublishedDocumentIds()) == 0) {
                throw new Solrsearch_Model_Exception(
                    "Collection role with id '" . $collectionRole->getId()
                    . "' is not clickable and therefore not displayed."
                );
            }
        }

        $this->_collectionRole = $collectionRole;
        $this->_collection = $collection;
    }

    public function isRootCollection()
    {
        return count($this->_collection->getParents()) === 1;
    }

    /**
     * Returns an array of Collection objects along the path to the root.
     * In case the current collection is a root collection an empty array is returned.
     *
     * @return array
     */
    public function getParents()
    {
        $parents = $this->_collection->getParents();
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
     *
     * @return array of Collection
     */
    public function getChildren()
    {
        $children = $this->_collection->getVisibleChildren();

        if ($this->_collectionRole->getHideEmptyCollections()) {
            // Collection ausblenden, wenn ihr selbst und den Kind-Collections keine Dokumente zugeordnet
            $children = array_filter($children, function (CollectionInterface $collection) {
                return $collection->getNumSubtreeEntries() > 0;
            });
        }

        return $children;
    }

    public function getTitle()
    {
        return $this->_collection->getDisplayNameForBrowsingContext($this->_collectionRole);
    }

    public function getTheme()
    {
        return $this->_collection->getTheme();
    }

    public function getCollectionId()
    {
        return $this->_collection->getId();
    }

    public function getCollectionRoleTitle()
    {
        return 'default_collection_role_' . $this->getCollectionRoleTitlePlain();
    }

    public function getCollectionRoleTitlePlain()
    {
        return $this->_collectionRole->getDisplayName('browsing');
    }

    public function getCollectionRole()
    {
        return $this->_collectionRole;
    }
}
