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
 * @package     Module_Admin
 * @author      Sascha Szott <szott@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class Admin_Model_CollectionRole {

    private $collectionRole = null;

    public function  __construct($id = null) {
        if ($id === '') {
            throw new Admin_Model_Exception('missing parameter roleid');
        }
        if (is_null($id)) {
            $this->initNewCollectionRole();
            return;
        }
        try {
            $this->collectionRole = new Opus_CollectionRole((int) $id);
            $this->setDisplayOptions($this->collectionRole->getPosition());
        }
        catch (Opus_Model_NotFoundException $e) {
            throw new Admin_Model_Exception('roleid parameter value unknown');
        }
    }

    private function initNewCollectionRole() {
        $this->collectionRole = new Opus_CollectionRole();
        foreach (array('Visible', 'VisibleBrowsingStart', 'VisibleFrontdoor', 'VisibleOai') as $field) {
            $this->collectionRole->getField($field)->setValue('1');
        }
        $this->setDisplayOptions();
    }

    private function setDisplayOptions($position = null) {
        $allCollectionRoles = Opus_CollectionRole::fetchAll();
        $selectValues = array(0);
        foreach ($allCollectionRoles as $collectionRole) {
            array_push($selectValues, $collectionRole->getPosition());
        }
        $countRoles = count($allCollectionRoles);
        if (is_null($position)) {
            $countRoles++;
            $lastPosition = $selectValues[count($selectValues) - 1];
            array_push($selectValues, 1 + $lastPosition);
        }
        $pos_field = $this->collectionRole->getField('Position');
        $options = range(0, $countRoles);
        $pos_field->setDefault(array_combine($selectValues, $options))->setSelection(true);
        if (!is_null($position)) {
            $pos_field->setValue($position);
        }
        else {
            $pos_field->setValue($countRoles);
        }
        foreach (array('DisplayBrowsing', 'DisplayFrontdoor', 'DisplayOai') as $fieldname) {
            $field = $this->collectionRole->getField($fieldname);
            $field->setDefault(array('Name' => 'Name', 'Number' => 'Number', 'Name, Number' => 'NameNumber', 'Number, Name' => 'NumberName'))
                    ->setSelection(true)                    
                    ->setMandatory(true);
            if (is_null($position)) {
                $field->setValue('Name');
            }
        }
    }

    public function getObject() {
        return $this->collectionRole;
    }

    public function delete() {
        $this->collectionRole->delete();
    }

    public function setVisibility($visibility) {
        $this->collectionRole->setVisible($visibility);
        $this->collectionRole->store();
    }

    public function move($position) {
        if (is_null($position)) {
            return;
        }
        $position = (int) $position;
        if ($position < 1) {
            throw new Admin_Model_Exception('cannot move collection role');
        }
        $this->collectionRole->setPosition($position);
        $this->collectionRole->store();
    }    
}
?>