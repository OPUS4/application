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

use Opus\Common\Config;
use Opus\Common\Log;
use Opus\Common\Security\Realm;
use Opus\Common\Security\SecurityException;

/**
 * Identify the Role of the current User and set up Realm with
 * the approriate Role.
 */
class Application_Controller_Plugin_SecurityRealm extends Zend_Controller_Plugin_Abstract
{
    /**
     * Determine the current User's security role and set up Realm.
     *
     * @param Zend_Controller_Request_Abstract $request The current request.
     */
    public function routeStartup(Zend_Controller_Request_Abstract $request)
    {
        // Create a Realm instance.  Initialize privileges to empty.
        $realm = Realm::getInstance();
        $realm->setUser(null);
        $realm->setIp(null);

        // Overwrite default user if current user is logged on.
        $auth     = Zend_Auth::getInstance();
        $identity = $auth->getIdentity();

        if (false === empty($identity)) {
            try {
                $realm->setUser($identity);
            } catch (SecurityException $e) {
                // unknown account -> clean identity (e.g. session of deleted user - OPUSVIER-3214)
                $auth->clearIdentity();
            } catch (Exception $e) {
                // unexpected exception -> clear identity and throw
                $auth->clearIdentity();
                throw new Exception($e);
            }
        }

        if ($request instanceof Zend_Controller_Request_Http) {
            $clientIp = $request->getClientIp(false);

            Log::get()->debug("Client-IP: $clientIp");

            // OPUS_Security does not support IPv6.  Skip setting IP address, if
            // IPv6 address has been detected.  This means, that authentication by
            // IPv6 address does not work, but username-password still does.
            if ($clientIp !== null && preg_match('/:/', $clientIp) === 0) {
                $realm->setIp($clientIp);
            }
        }

        $config = Config::get();

        if (isset($config->security) && filter_var($config->security, FILTER_VALIDATE_BOOLEAN)) {
            Application_Security_AclProvider::init();
        } else {
            Zend_View_Helper_Navigation_HelperAbstract::setDefaultAcl(null);
            Zend_View_Helper_Navigation_HelperAbstract::setDefaultRole(null);
        }

        $this->setupExportFormats();
    }

    /**
     * adjustments to enable different authentication mechanism for SWORD module
     *
     * @param array $groups
     */
    public function __construct($groups = [])
    {
        $this->groups = [];
        foreach ((array) $groups as $id => $modules) {
            $this->groups[$id] = (array) $modules;
        }
    }

    /**
     * @param string $moduleName
     * @return string
     */
    private function getModuleMemberName($moduleName)
    {
        $member = Zend_Auth_Storage_Session::MEMBER_DEFAULT;
        // try to find group of module
        foreach ($this->groups as $id => $modules) {
            if (in_array($moduleName, $modules)) {
                // return group's member name
                return $member . $id;
            }
        }
        // return fallback member name
        return $member;
    }

    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $namespace = Zend_Auth_Storage_Session::NAMESPACE_DEFAULT;
        $member    = $this->getModuleMemberName($request->getModuleName());
        $storage   = new Zend_Auth_Storage_Session($namespace, $member);
        Zend_Auth::getInstance()->setStorage($storage);
    }

    /**
     * @throws Zend_Exception
     *
     * TODO LAMINAS temporary hack for https://github.com/OPUS4/application/issues/516
     */
    protected function setupExportFormats()
    {
        if (! Zend_Registry::isRegistered('Opus_Exporter')) {
            Log::get()->warn(__METHOD__ . ' exporter not found');
            return;
        }

        $exporter = Zend_Registry::get('Opus_Exporter');

        if ($exporter === null) {
            Log::get()->warn(__METHOD__ . ' exporter not found');
            return;
        }

        if (Realm::getInstance()->checkModule('admin')) {
            // add admin-only format(s) to exporter
            // hiermit wird nur die Sichtbarkeit des Export-Buttons gesteuert
            $exporter->addFormats([
                'datacite' => [
                    'name'        => 'DataCite',
                    'description' => 'Export DataCite-XML',
                    'module'      => 'export',
                    'controller'  => 'index',
                    'action'      => 'datacite',
                    'search'      => false,
                ],
            ]);

            $exporter->addFormats([
                'marc21' => [
                    'name'        => 'MARC21-XML',
                    'description' => 'Export MARC21-XML',
                    'module'      => 'export',
                    'controller'  => 'index',
                    'action'      => 'marc21',
                    'search'      => false,
                    'params'      => [
                        'searchtype' => 'id',
                    ],
                ],
            ]);
        }
    }
}
