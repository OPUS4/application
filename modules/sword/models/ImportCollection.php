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
 * @package     Module_Sword
 * @author      Sascha Szott
 * @copyright   Copyright (c) 2016
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
class Sword_Model_ImportCollection {
    
    private $importCollection;
    
    private $roleName;
    
    public function __construct() {
        $logger = Zend_Registry::get('Zend_Log');
        $config = Zend_Registry::get('Zend_Config');

        $collectionNumber = $config->sword->collection->default->number;
        if (trim($collectionNumber) == '') {
            $logger->warn('configuration key sword.collection.default.number is not defined -- documents that are imported via SWORD API will not be associated to OPUS collection');
            return;
        }
        
        $collectionRole = Opus_CollectionRole::fetchByName('Import');
        if (is_null($collectionRole)) {
            $logger->warn('collection role "Import" does not exist -- documents that are imported via SWORD API will not be associated to OPUS collection');
            return;
        }            
        
        $collectionList = Opus_Collection::fetchCollectionsByRoleNumber($collectionRole->getId(), $collectionNumber);
        if (empty($collectionList)) {
            $logger->warn('could not find collection with number ' . $collectionNumber . ' and collection role ' . $collectionRole->getId() . ' -- documents that are imported via SWORD API will not be associated to OPUS collection');
            return;
        }
        
        if (count($collectionList) > 1) {
            $logger->warn('there are multiple collections with number ' . $collectionNumber . ' and collection role ' . $collectionRole->getId() . ' -- documents that are imported via SWORD API will not be associated to OPUS collection');
            return;
        }
        
        $this->importCollection = $collectionList[0];
        $this->roleName = $collectionRole->getName();
    }

    
    public function exists() {
        return !is_null($this->importCollection);
    }
    
    public function getName() {
        return $this->importCollection->getName();
    }
    
    public function getNumber() {
        return $this->importCollection->getNumber();
    }
    
    public function getRoleName() {
        return $this->roleName;
    }
    
    public function getCollection() {
        return $this->importCollection;
    }
}
