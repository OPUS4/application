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

class Admin_Model_Collections extends Application_Model_Abstract
{
    /** @var Zend_View_Interface */
    private $view;

    /**
     * Returns array with information about collection roles.
     *
     * @param int|null $documentId
     * @return array
     *
     * TODO using view in action helper is not best design
     */
    public function getCollectionRolesInfo($documentId = null)
    {
        $collections = [];

        $collectionRoles = CollectionRole::fetchAll();

        foreach ($collectionRoles as $collectionRole) {
            $rootCollection = $collectionRole->getRootCollection();
            if ($rootCollection !== null) {
                $collection = [
                    'id'          => $rootCollection->getId(),
                    'name'        => $this->view->translate('default_collection_role_' . $collectionRole->getDisplayName()),
                    'hasChildren' => $rootCollection->hasChildren(),
                    'visible'     => $collectionRole->getVisible(),
                    'isRoot'      => true,
                    'role'        => $collectionRole,
                    'collection'  => $rootCollection,
                ];

                if ($documentId !== null) {
                    $collection['assigned'] = $rootCollection->holdsDocumentById($documentId);
                } else {
                    $collection['assigned'] = false;
                }

                array_push($collections, $collection);
            }
        }

        return $collections;
    }

    /**
     * @param Zend_View_Interface $view
     */
    public function setView($view)
    {
        $this->view = $view;
    }
}
