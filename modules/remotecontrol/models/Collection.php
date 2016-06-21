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
class Remotecontrol_Model_Collection {

    private $_role = null;

    private $_collection = null;

    public function __construct($roleName, $collectionNumber = null) {

        $this->_role = Opus_CollectionRole::fetchByName($roleName);
        if (is_null($this->_role)) {
            throw new Remotecontrol_Model_Exception("Model_Collection: Role with name '$roleName' not found");
        }

        $root = $this->_role->getRootCollection();
        if (is_null($root)) {
            throw new Remotecontrol_Model_Exception(
                "Model_Collection: Root Collection for role '$roleName' does not exist."
            );
        }

        if (!isset($collectionNumber)) {
            $this->_collection = $root;
        }
        else {
            $this->_collection = $this->findByNumber($collectionNumber);
        }

    }

    public function getId() {
        return $this->_collection->getId();
    }

    public function findByNumber($number) {
        $collections = Opus_Collection::fetchCollectionsByRoleNumber($this->_role->getId(), $number);

        if (count($collections) > 1) {
            throw new Remotecontrol_Model_Exception(
                "Model_Collection: Found more than one collection with number '$number'.",
                Remotecontrol_Model_Exception::COLLECTION_IS_NOT_UNIQUE
            );
        }
        elseif (count($collections) != 1) {
            throw new Remotecontrol_Model_Exception(
                "Model_Collection: Collection with number '$number' does not exist."
            );
        }

        return $collections[0];
    }

    public function appendChild($number, $title) {
        $collections = Opus_Collection::fetchCollectionsByRoleNumber($this->_role->getId(), $number);
        if (count($collections) > 0) {
            throw new Remotecontrol_Model_Exception(
                "Model_Collection: Collection with number '$number' already exists."
            );
        }

        $newChild = $this->_collection->addLastChild();
        $newChild->setNumber($number)
                ->setName($title)
                ->setVisible(1)
                ->store();

        return $newChild;
    }

    public function rename($title) {
        $this->_collection->setName($title)->store();
        return $this->_collection;
    }

}
