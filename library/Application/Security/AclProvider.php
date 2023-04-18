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

use Opus\Common\LoggingTrait;
use Opus\Common\Security\Realm;

/**
 * Erzeugt das Zend_Acl object für die Prüfung von Nutzerprivilegien.
 *
 * Für den aktuellen Nutzer werden die Rollen ermittelt. Anschließend wird für jede Rolle geprüft, ob es eine
 * Konfigurationsdatei gibt. Diese wird gegebenenfalls geladen und für die Konstruktion der ACLs verwendet.
 * Gibt es keine Datei hat der Nutzer keine Einschränkungen beim Zugriff.
 */
class Application_Security_AclProvider
{
    use LoggingTrait;

    /**
     * Name der Role, die für ACL Prüfungen verwendet wird.
     *
     * Dieser Name wird anstatt des eigentlich Nutzernamens verwendet.
     */
    public const ACTIVE_ROLE = '_user';

    /**
     * Ressourcen, die in Datei application/configs/navigationModules.xml referenziert werden.
     *
     * TODO resources should be declared in modules and controllers (decentralising)
     *
     * @var array
     */
    public static $resourceNames = [
        'admin'  => [
            'documents',
            'accounts',
            'security',
            'licences',
            'collections',
            'series',
            'languages',
            'statistics',
            'institutions',
            'enrichments',
            'systeminfo',
            'indexmaintenance',
            'job',
            'options',
            'persons',
        ],
        'review' => [
            'reviewing',
        ],
        'setup'  => [
            'helppages',
            'staticpages',
            'translations',
        ],
        'doi'    => [
            'doi_notification',
        ],
    ];

    /** @var Zend_Acl */
    private static $acl;

    public static function init()
    {
        $aclProvider = new Application_Security_AclProvider();

        $acl = $aclProvider->getAcls();

        $aclProvider->getLogger()->debug('ACL: bootrapping');

        self::$acl = $acl;

        Zend_View_Helper_Navigation_HelperAbstract::setDefaultAcl($acl);
        Zend_View_Helper_Navigation_HelperAbstract::setDefaultRole(
            self::ACTIVE_ROLE
        );
    }

    /**
     * @return Zend_Acl
     */
    public static function getAcl()
    {
        return self::$acl;
    }

    public static function clear()
    {
        self::$acl = null;
    }

    /**
     * Liefert ein Zend_Acl Objekt für den aktuellen Nutzer zurück.
     *
     * @return Zend_Acl
     */
    public function getAcls()
    {
        $logger = $this->getLogger();

        $acl = new Zend_Acl();

        $this->loadResources($acl);

        $realm = Realm::getInstance();

        $parents = $realm->getRoles();

        $this->loadRoles($acl, $parents);

        // create role for user on-the-fly with assigned roles as parents
        if ($logger->getLevel() >= Zend_LOG::DEBUG) {
            $user = Zend_Auth::getInstance()->getIdentity();
            $logger->debug(
                "ACL: Create role '" . $user . "' with parents (" . implode(", ", $parents) . ")"
            );
        }

        // Add role for current user
        $acl->addRole(new Zend_Acl_Role(self::ACTIVE_ROLE), $parents);

        return $acl;
    }

    /**
     * Erzeugt die notwendigen Zend_Acl_Resource Objekte.
     *
     * @param Zend_Acl $acl
     */
    public function loadResources($acl)
    {
        $modules = self::$resourceNames;

        foreach ($modules as $module => $resources) {
            $acl->addResource(new Zend_Acl_Resource($module));
            foreach ($resources as $resource) {
                $acl->addResource(new Zend_Acl_Resource($resource), $module);
            }
        }

        $this->loadWorkflowResources($acl);
    }

    /**
     * @param Zend_Acl $acl
     */
    public function loadWorkflowResources($acl)
    {
        $resources = Application_Controller_Action_Helper_Workflow::getWorkflowResources();

        $acl->addResource(new Zend_Acl_Resource('workflow'));

        foreach ($resources as $resource) {
            $acl->addResource(new Zend_Acl_Resource($resource), 'workflow');
        }
    }

    /**
     * @return array
     */
    public function getAllResources()
    {
        $modules = self::$resourceNames;

        $allResources = [];

        foreach ($modules as $resources) {
            $allResources = array_merge($allResources, $resources);
        }

        return $allResources;
    }

    /**
     * Lädt die konfigurierten Rollen.
     *
     * TODO load from database and from configuration files
     *
     * @param Zend_Acl $acl
     * @param string[] $roles
     */
    public function loadRoles($acl, $roles)
    {
        // Feste Rollen, die immer existieren
        $acl->addRole(new Zend_Acl_Role('guest'));
        $acl->addRole(new Zend_Acl_Role('administrator'));

        $acl->allow('administrator');

        foreach ($roles as $role) {
            if (! $acl->hasRole($role)) {
                $acl->addRole(new Zend_Acl_Role($role));
            }

            $roleConfig = new Application_Security_RoleConfig($role);

            $roleConfig->applyPermissions($acl);
        }
    }
}
