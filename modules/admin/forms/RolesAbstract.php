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
 * Abstract class for supporting editing of Opus roles in form.
 */
abstract class Admin_Form_RolesAbstract extends Zend_Form {

    protected $roleGroupLegendKey = 'admin_form_group_roles';

    protected $alwaysCheckAndDisableGuest = true;

    /**
     * Adds display group for roles.
     */
    protected function _addRolesGroup() {
        $roles = Opus_UserRole::getAll();

        $rolesGroup = array();

        foreach ($roles as $role) {
            $roleName = $role->getDisplayName();
            $roleCheckbox = $this->createElement('checkbox',
                    'role' . $roleName)->setLabel($roleName);
            
            // TODO special code to handle role 'guest': Is that good?
            if ($roleName === 'guest' && $this->alwaysCheckAndDisableGuest) {
                $roleCheckbox->setValue(1);
                $roleCheckbox->setAttrib('disabled', true);
            }
            $this->addElement($roleCheckbox);
            $rolesGroup[] = $roleCheckbox->getName();
        }

        $this->addDisplayGroup($rolesGroup, 'Roles', array('legend' => $this->roleGroupLegendKey));
    }

    /**
     * Parses post data and returns array with Opus_UserRole instances.
     * @param array $postData
     * @return array of Opus_UserRole instances
     */
    public static function parseSelectedRoles($postData) {
        $selectedRoles = array();
        foreach (self::parseSelectedRoleNames($postData) as $roleName) {
            $selectedRoles[] = Opus_UserRole::fetchByName($roleName);
        }
        return $selectedRoles;
    }

    public static function parseSelectedRoleNames($postData) {
        $roles = Opus_UserRole::getAll();

        $selectedRoles = array();

        foreach ($roles as $roleName) {
            $keyName = 'role' . $roleName;

            // FIXME: Kludge to avoid undefined array indices.
            if (!array_key_exists($keyName, $postData)) {
                continue;
            }

            // If role-checkbox is activated, add role to returned array.
            if ($postData[$keyName]) {
                $selectedRoles[] = $roleName;
            }
        }

        return $selectedRoles;
    }

    /**
     * Sets checkboxes for roles according to provided array.
     * @param array $roles
     *
     * TODO uncheck all others (expected behaviour?)
     */
    public function setSelectedRoles($roles) {
        foreach ($roles as $roleName) {
            $role = $this->getElement('role' . $roleName);
            $role->setValue(1);
        }
    }

}

?>
