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
 * @package     Module_Remotecontrol
 * @author      Pascal-Nicolas Becker <becker@zib.de>
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Check permissions before any remotecontrole controllers will be started.
 */

$log = Zend_Registry::get('Zend_Log');
$msg = 'remotecontrol -- REMOTE_ADDR: ' . $_SERVER['REMOTE_ADDR'];
$log->debug($msg);

// check, if we are allowed to use remotecontrol
if (true !== Opus_Security_Realm::getInstance()->check('remotecontrol')) {
    
    $identity = Zend_Auth::getInstance()->getIdentity();
    $message = null;
    if (empty($identity) === true) {
        $message = "You must be logged in to use module remotecontrol.";
    } else {
        $message = "You need another identity to use module remotecontrol.";
    }
	
    // get all parameters to return after login.
    $params = Zend_Controller_Action_HelperBroker::getStaticHelper('ReturnParams')->getReturnParameters();
	
    // Forward to module auth
    Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger')->addMessage(array('level' => 'failure', 'message' => $message));
    Zend_Controller_Action_HelperBroker::getStaticHelper('redirector')->gotoSimple('index', 'auth', 'default', $params);
}
