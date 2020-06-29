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
 * @package     Controller/Plugin
 * @author      Pascal-Nicolas Becker <becker@zib.de>
 * @author      Ralf Claussnitzer (ralf.claussnitzer@slub-dresden.de)
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Identify the Role of the current User and set up Opus_Security_Realm with
 * the approriate Role.
 *
 * @category    Application
 * @package     Controller
 */
class Application_Controller_Plugin_SecurityRealm extends Zend_Controller_Plugin_Abstract
{

    /**
     * Determine the current User's security role and set up Opus_Security_Realm.
     *
     * @param Zend_Controller_Request_Abstract $request The current request.
     * @return void
     */
    public function routeStartup(Zend_Controller_Request_Abstract $request)
    {

        // Create a Realm instance.  Initialize privileges to empty.
        $realm = Opus_Security_Realm::getInstance();
        $realm->setUser(null);
        $realm->setIp(null);

        // Overwrite default user if current user is logged on.
        $auth = Zend_Auth::getInstance();
        $identity = $auth->getIdentity();

        if (false === empty($identity)) {
            try {
                $realm->setUser($identity);
            } catch (Opus_Security_Exception $e) {
                // unknown account -> clean identity (e.g. session of deleted user - OPUSVIER-3214)
                $auth->clearIdentity();
            } catch (Exception $e) {
                // unexpected exception -> clear identity and throw
                $auth->clearIdentity();
                throw new Exception($e);
            }
        }

        // OPUS_Security does not support IPv6.  Skip setting IP address, if
        // IPv6 address has been detected.  This means, that authentication by
        // IPv6 address does not work, but username-password still does.
        if (isset($_SERVER['REMOTE_ADDR']) and preg_match('/:/', $_SERVER['REMOTE_ADDR']) === 0) {
            $realm->setIp($_SERVER['REMOTE_ADDR']);
        }
    }


    // adjustments to enable different authentication mechanism for SWORD module
    public function __construct($groups = [])
    {
        $this->groups = [];
        foreach ((array) $groups as $id => $modules) {
            $this->groups[$id] = (array) $modules;
        }
    }

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
        $member = $this->getModuleMemberName($request->getModuleName());
        $storage = new Zend_Auth_Storage_Session($namespace, $member);
        Zend_Auth::getInstance()->setStorage($storage);
    }
}
