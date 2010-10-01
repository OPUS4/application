<?php
/**
 * This file is part of OPUS. The software OPUS has been originally developed
 * at the University of Stuttgart with funding from the German Research Net,
 * the Federal Department of Higher Education and Research and the Ministry
 * of Science, Research and the Arts of the State of Baden-Wuerttemberg.
 *
 * OPUS 4 is a complete rewrite of the original OPUS software and was developed
 * by the Stuttgart University Library, the Library Service Center
 * Baden-Wuerttemberg, the North Rhine-Westphalian Library Service Center,
 * the Cooperative Library Network Berlin-Brandenburg, the Saarland University
 * and State Library, the Saxon State Library - Dresden State and University
 * Library, the Bielefeld University Library and the University Library of
 * Hamburg University of Technology with funding from the German Research
 * Foundation and the European Regional Development Fund.
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
 * @package     Module_Publish
 * @author      Pascal-Nicolase Becker <becker@zib.de>
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Check permissions before any Publish controller will be started.
 *
 * @category    Application
 * @package     Module_Publish
 */
$logger = Zend_Registry::get('Zend_Log');
// $logger->info("starting autorisitation check for module publish!");

// check, if we are allowed to publish new documents
if (true !== Opus_Security_Realm::getInstance()->check('publish')) {
    // we are not allowed to publish
    $identity = Zend_Auth::getInstance()->getIdentity();
    $translate = Zend_Registry::get('Zend_Translate');
    if (is_null($translate) === false) {
        if (empty($identity) === true) {
            $message = $translate->getAdapter()->translate('publish_no_identity_error');
        } else {
            $message = $translate->getAdapter()->translate('publish_wrong_identity_error');
        }
    } else {
        if (empty($identity) === true) {
            $message = "You must be logged in to publish new documents.";
        } else {
            $message = "You need another identity to publish new documents.";
        }
    }
    
    // get all parameters to return after login.
    $params = Zend_Controller_Action_HelperBroker::getStaticHelper('ReturnParams')->getReturnParameters();
    
    // Forward to module auth
    Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger')->addMessage($message);
    Zend_Controller_Action_HelperBroker::getStaticHelper('redirector')->gotoSimple('index', 'auth', 'default', $params);

}

Zend_Controller_Action_HelperBroker::getStaticHelper('MainMenu')->setActive('publish');

