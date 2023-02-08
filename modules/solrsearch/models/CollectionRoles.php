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

use Opus\Common\CollectionRole;
use Opus\Common\CollectionRoleInterface;

class Solrsearch_Model_CollectionRoles
{
    /** @var CollectionRoleInterface[] */
    private $collectionRoles;

    /**
     * Returns visible collection roles.
     *
     * @return array of CollectionRole objects
     */
    public function getAllVisible()
    {
        if ($this->collectionRoles === null) {
            $this->collectionRoles = [];
            foreach (CollectionRole::fetchAll() as $collectionRole) {
                if (
                    $this->isVisible($collectionRole)
                    && ($this->hasVisibleChildren($collectionRole)
                    || $this->hasPublishedDocs($collectionRole))
                ) {
                    array_push($this->collectionRoles, $collectionRole);
                }
            }
        }

        return $this->collectionRoles;
    }

    /**
     * Return true if the given collection role has at least one
     * first-level collection that is visible.
     *
     * @param CollectionRoleInterface $collectionRole
     * @return bool
     */
    private function hasVisibleChildren($collectionRole)
    {
        $rootCollection = $collectionRole->getRootCollection();
        if ($rootCollection === null) {
            return false;
        }
        return $rootCollection->hasVisibleChildren();
    }

    /**
     * Returns true if the given collection role has at least one associated document
     * in server_state published.
     *
     * @param CollectionRoleInterface $collectionRole
     * @return bool
     */
    private function hasPublishedDocs($collectionRole)
    {
        $rootCollection = $collectionRole->getRootCollection();
        if ($rootCollection === null) {
            return false;
        }
        $publishedDocIds = $rootCollection->getPublishedDocumentIds();
        return is_array($publishedDocIds) && ! empty($publishedDocIds);
    }

    /**
     * Returns true if collection role is visible in browsing.
     *
     * @param CollectionRoleInterface $collectionRole
     * @return bool
     */
    private function isVisible($collectionRole)
    {
        return $collectionRole->getVisible() && $collectionRole->getVisibleBrowsingStart();
    }
}
