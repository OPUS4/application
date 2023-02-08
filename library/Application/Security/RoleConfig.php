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

use Opus\Common\Log;
use Opus\Common\UserRole;

/**
 * Lädt die Konfigurationsdatei für eine Rolle.
 *
 * In der Datei sind die Privilegien für die Rolle definiert. Existiert keine Datei gibt es keine weiteren
 * Einschränkungen.
 *
 * TODO Beschreibung überprüfen und ergänzen.
 */
class Application_Security_RoleConfig
{
    /** @var string */
    private $roleName;

    /**
     * @param string $roleName
     */
    public function __construct($roleName)
    {
        $this->roleName = $roleName;
    }

    /**
     * Fügt Rechte zu Zend_Acl Instanz hinzu.
     *
     * @param Zend_Acl $acl
     */
    public function applyPermissions($acl)
    {
        $this->getRolePermissions($acl, $this->roleName);
    }

    /**
     * @param Zend_Acl $acl
     * @param string   $roleName
     * @throws Zend_Exception
     *
     * TODO BUG doesn't return anything
     */
    public function getRolePermissions($acl, $roleName)
    {
        $role = UserRole::fetchByName($roleName);

        if ($role === null) {
             Log::get()->err("Attempt to load unknown role '$roleName'.");
            return;
        }

        $resources = $role->listAccessModules();

        $resourcesConfigured = false;

        $accessibleModules = [];

        foreach ($resources as $resource) {
            if (! strncmp('resource_', $resource, 9)) {
                // resource (like languages);
                $resource = new Zend_Acl_Resource(substr($resource, 9));
                $acl->allow($roleName, $resource);
                $resourcesConfigured = true;
            } elseif (! strncmp('workflow_', $resource, 9)) {
                // workflow permission
                $resource = new Zend_Acl_Resource($resource);
                $acl->allow($roleName, $resource);
            } else {
                // module access
                $accessibleModules[] = $resource;
            }
        }

        if (! $resourcesConfigured) {
            foreach ($accessibleModules as $module) {
                if ($acl->has(new Zend_Acl_Resource($module))) {
                    $acl->allow($roleName, $module);
                }
            }
        }
    }

    /**
     * @param string $role
     * @return Zend_Config|null TODO not used yet
     */
    public function getRoleConfig($role)
    {
        $path = APPLICATION_PATH . '/application/configs/security/' . $role . '.ini';

        return is_readable($path) ? new Zend_Config_Ini($path) : null;
    }
}
