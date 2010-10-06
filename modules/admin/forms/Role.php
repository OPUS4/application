<?php
/*
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
 * @category    TODO
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Form for creating and editing a role.
 */
class Admin_Form_Role extends Zend_Form {

    private static $protectedRoles = array('administrator', 'guest');

    /**
     * Names of basic privileges.
     */
    private static $basicPrivileges = array('administrate', 'clearance', 
        'publish', 'publishUnvalidated');

    /**
     * Name of read metadata privilege.
     */
    const READ_METADATA_PRIVILEGE = "readMetadata";

    /**
     * Names of server states for read metadata privilege.
     */
    private static $serverStates = array('published', 'unpublished', 'deleted');

    /**
     * Name of read file privilege.
     */
    const READ_FILE_PRIVILEGE = "readFile";

    /**
     * Constructs form.
     * @param int $id
     */
    public function __construct($id = null) {
        $section = empty($id) ? 'new' : 'edit';

        $config = new Zend_Config_Ini(APPLICATION_PATH .
                '/modules/admin/forms/role.ini', $section);

        parent::__construct($config->form->role);

        if (!empty($id)) {
            $role = new Opus_Role($id);
            $this->populateFromRole($role);
        }
    }

    public function init() {
        parent::init();

        $this->getElement('name')->addValidator(new Form_Validate_RoleAvailable());
        $this->_addBasicPrivilegesGroup();
        $this->_addMetadataPrivilegesGroup();
    }

    /**
     * Add display group with basic privileges like 'administrate'.
     */
    protected function _addBasicPrivilegesGroup() {
        $group = array();

        foreach (self::$basicPrivileges as $privilege) {
            $element = $this->createElement('checkbox', 'privilege' . $privilege)->setLabel($privilege);
            $this->addElement($element);
            $group[] = $element->getName();
        }

        $this->addDisplayGroup($group, 'basic', array('legend' => 'admin_role_group_basic'));
    }

    /**
     * Add showMetadata privileges group.
     */
    protected function _addMetadataPrivilegesGroup() {
        $group = array();

        foreach (self::$serverStates as $state) {
            $element = $this->createElement('checkbox', 'metadata' . $state)->setLabel($state);
            $this->addElement($element);
            $group[] = $element->getName();
        }

        $this->addDisplayGroup($group, 'metadata', array('legend' => 'admin_role_group_metadata'));
    }

    public function populateFromRole($role) {
        $nameElement = $this->getElement('name');
        $roleName = $role->getName();
        $nameElement->setValue($roleName);
        if (in_array($roleName, self::$protectedRoles)) {
            $nameElement->setAttrib('disabled', 'true');
        }

        $privileges = $role->getPrivilege();

        foreach ($privileges as $privilege) {
            $this->_populatePrivilege($privilege);
        }
    }

    protected function _populatePrivilege($privilege) {
        switch ($privilege->getPrivilege()) {
        case 'readFile':
            break;
        case 'readMetadata':
            $serverState = $privilege->getDocumentServerState();
            $this->getElement('metadata' . $serverState)->setValue(true);
            break;
        default:
            $basicPrivilege = $privilege->getPrivilege();
            $this->getElement('privilege' . $basicPrivilege)->setValue(true);
            break;
        }
    }

    public static function parseSelectedPrivileges($postData) {
        $privileges = array();

        foreach (self::$basicPrivileges as $name) {
            if (isset($postData['privilege' . $name])) {
                $value = $postData['privilege' . $name];
                if ($value) {
                    $privileges[] = $name;
                }
            }
        }

        foreach (self::$serverStates as $state) {
            if (isset($postData['metadata' . $state])) {
                $value = $postData['metadata' . $state];
                if ($value) {
                    $privileges[] = 'readMetadata.' . $state;
                }
            }
        }

        return $privileges;
    }



}


?>
