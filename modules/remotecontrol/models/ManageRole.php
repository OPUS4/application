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
 * @category    Application
 * @package     Module_Collection
 * @author      Thoralf Klein <thoralf.klein@zib.de>
 * @copyright   Copyright (c) 2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
class Remotecontrol_Model_ManageRole {

    private $role = null;

    public function __construct($role_name) {

        $this->role = Opus_CollectionRole::fetchByName($role_name);
        if (is_null($this->role)) {
            throw new Remotecontrol_Model_Exception("ManageRole: Role with name '$role_name' not found");
        }

        if (is_null($this->role->getRootCollection())) {
            throw new Remotecontrol_Model_Exception("ManageRole: Root Collection for role does not exist.");
        }
    }

    public function findCollectionByNumber($number) {
        return Opus_Collection::fetchCollectionsByRoleNumber($this->role->getId(), $number);

    }

    public function addCollection($number, $title) {
        $collections = $this->findCollectionByNumber($number);
        if (count($collections) > 0) {
            throw new Remotecontrol_Model_Exception("ManageRole: Collection with number '$number' already exists.");
        }

        $root = $this->role->getRootCollection();
        $collection = $root->addLastChild();
        $collection->setNumber($number)
                ->setName($title)
                ->store();

        return $collection;

    }

    public function renameCollectionByNumber($number, $title) {
        $collections = $this->findCollectionByNumber($number);
        if (count($collections) > 1) {
            throw new Remotecontrol_Model_Exception("ManageRole: Found more than one collection with number '$number'.");
        }
        else if (count($collections) < 1) {
            throw new Remotecontrol_Model_Exception("ManageRole: Collection with number '$number' does not exist.");
        }

        $collection = $collections[0];
        $collection->setName($title)->store();

        return $collection;

    }

}
