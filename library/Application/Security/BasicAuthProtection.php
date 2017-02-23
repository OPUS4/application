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
 * @package     Application_Security 
 * @author      Sascha Szott
 * @copyright   Copyright (c) 2016
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
class Application_Security_BasicAuthProtection {
    
    static public function accessAllowed($request, $response) {
        $adapter = new Zend_Auth_Adapter_Http(array(
            'accept_schemes' => 'basic',
            'realm' => 'opus-sword'            
        ));
        
        $log = Zend_Registry::get('Zend_Log');
        $config = Zend_Registry::get('Zend_Config');        
        
        $passwordFile = $config->sword->authFile;
        if (!file_exists($passwordFile)) {            
            $log->info('could not load auth file of SWORD module from ' . $passwordFile . ' -- check configuration sword.authFile');
            return false;
        }
        
        $adapter->setBasicResolver(new Zend_Auth_Adapter_Http_Resolver_File($passwordFile));
        $adapter->setRequest($request);
        $adapter->setResponse($response);
        
        $auth = Zend_Auth::getInstance();
        $result = $auth->authenticate($adapter);
        
        if (!$result->isValid()) {            
            return false;
        }
        
        $userName = $result->getIdentity()['username'];
        $auth->clearIdentity();
        return $userName;        
    }
}
