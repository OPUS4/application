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
 * @package     Controller
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
class Controller_Plugin_SecurityRealm extends Zend_Controller_Plugin_Abstract {

    /**
     * Determine the current User's security role and set up Opus_Security_Realm.
     *
     * @param Zend_Controller_Request_Abstract $request The current request.
     * @return void
     */
    public function routeStartup(Zend_Controller_Request_Abstract $request) {
        // Create a Realm instance
        $realm = Opus_Security_Realm::getInstance();
        $acl = new Opus_Security_Acl;

        // Detect role of currently logged on user (if any)
        $identity = Zend_Auth::getInstance()->getIdentity();
        if (true === empty($identity)) {
            $identityRole = 'guest';
        } else {
            // Check if the logged in identity has a specific role assigned
            $identityRole = 'admin';            
        }

        // Set up standard guest role as defined in the database
        $guest = $acl->getRole($identityRole);
        
        // Set up master Resource object
        $masterResource = $acl->get('PUBLIC');
        $realm->setResourceMaster($masterResource);
        
        // Start permission checks with assigning the Acl 
        $realm->setRole($guest);
        $realm->setAcl($acl);
    }   
    
}
