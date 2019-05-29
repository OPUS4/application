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
 * @category    Application
 * @package     Application_Security
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2018, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Erzeugt das Zend_Acl object für die Prüfung von Nutzerprivilegien.
 *
 * Für den aktuellen Nutzer werden die Rollen ermittelt. Anschließend wird für jede Rolle geprüft, ob es eine
 * Konfigurationsdatei gibt. Diese wird gegebenenfalls geladen und für die Konstruktion der ACLs verwendet.
 * Gibt es keine Datei hat der Nutzer keine Einschränkungen beim Zugriff.
 */
class Application_Security_AclProvider
{

    /**
     * Name der Role, die für ACL Prüfungen verwendet wird.
     *
     * Dieser Name wird anstatt des eigentlich Nutzernamens verwendet.
     */
    const ACTIVE_ROLE = '_user';

    private $_logger;

    /**
     * Ressourcen, die in Datei application/configs/navigationModules.xml referenziert werden.
     *
     * TODO resources should be declared in modules and controllers (decentralising)
     */
    public static $resourceNames = [
        'admin' => [
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
            'persons'
        ],
        'review' => [
            'reviewing'
        ],
        'setup' => [
            'helppages',
            'staticpages',
            'translations'
        ],
        'doi' => [
            'doi_notification'
        ]
    ];

    public static function init()
    {
        $aclProvider = new Application_Security_AclProvider();

        $acl = $aclProvider->getAcls();

        $aclProvider->getLogger()->debug('ACL: bootrapping');

        Zend_Registry::set('Opus_Acl', $acl);

        Zend_View_Helper_Navigation_HelperAbstract::setDefaultAcl($acl);
        Zend_View_Helper_Navigation_HelperAbstract::setDefaultRole(
            Application_Security_AclProvider::ACTIVE_ROLE
        );
    }

    /**
    Zend_Debug::dump   * Liefert ein Zend_Acl Objekt für den aktuellen Nutzer zurück.
     */
    public function getAcls()
    {
        $logger = $this->getLogger();

        $acl = new Zend_Acl();

        $this->loadResources($acl);

        $realm = Opus_Security_Realm::getInstance();

        if (isset($_SERVER['REMOTE_ADDR']) and preg_match('/:/', $_SERVER['REMOTE_ADDR']) === 0) {
            $realm->setIp($_SERVER['REMOTE_ADDR']);
        }

        $user = Zend_Auth::getInstance()->getIdentity();

        if (!is_null($user)) {
            try {
                $realm->setUser($user);
            }
            catch (Opus_Security_Exception $ose) {
                // unknown user -> invalidate session (logout)
                Zend_Auth::getInstance()->clearIdentity();
                $user = null;
            }
        }

        $parents = $realm->getRoles();

        $this->loadRoles($acl, $parents);

        // create role for user on-the-fly with assigned roles as parents
        if (Zend_Registry::get('LOG_LEVEL') >= Zend_LOG::DEBUG) {
                $logger->debug(
                    "ACL: Create role '" . $user . "' with parents " . "(" . implode(", ", $parents) . ")"
                );
        }

        // Add role for current user
        $acl->addRole(new Zend_Acl_Role(self::ACTIVE_ROLE), $parents);

        return $acl;
    }

    /**
     * Erzeugt die notwendigen Zend_Acl_Resource Objekte.
     */
    public function loadResources($acl)
    {
        $modules = Application_Security_AclProvider::$resourceNames;

        foreach ($modules as $module => $resources) {
            $acl->addResource(new Zend_Acl_Resource($module));
            foreach ($resources as $resource) {
                $acl->addResource(new Zend_Acl_Resource($resource), $module);
            }
        }

        $this->loadWorkflowResources($acl);
    }

    public function loadWorkflowResources($acl)
    {
        $resources = Application_Controller_Action_Helper_Workflow::getWorkflowResources();

        $acl->addResource(new Zend_Acl_Resource('workflow'));

        foreach ($resources as $resource) {
            $acl->addResource(new Zend_Acl_Resource($resource), 'workflow');
        }
    }

    public function getAllResources()
    {
        $modules = Application_Security_AclProvider::$resourceNames;

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
     */
    public function loadRoles($acl, $roles)
    {
        // Feste Rollen, die immer existieren
        $acl->addRole(new Zend_Acl_Role('guest'));
        $acl->addRole(new Zend_Acl_Role('administrator'));

        $acl->allow('administrator');

        foreach ($roles as $role) {
            if (!$acl->hasRole($role)) {
                $acl->addRole(new Zend_Acl_Role($role));
            }

            $roleConfig = new Application_Security_RoleConfig($role);

            $roleConfig->applyPermissions($acl);
        }
    }

    public function getLogger()
    {
        if (is_null($this->_logger)) {
            $this->_logger = Zend_Registry::get('Zend_Log');
        }
        return $this->_logger;
    }

    public function setLogger($logger)
    {
        $this->_logger = $logger;
    }
}
