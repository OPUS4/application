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
 * @package     Controller
 * @author      Thoralf Klein <thoralf.klein@zib.de>
 * @author      Felix Ostrowski <ostrowski@hbz-nrw.de>
 * @author      Sascha Szott <szott@zib.de>
 * @copyright   Copyright (c) 2009-2011, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class Controller_Action extends Controller_ModuleAccess {

    /**
     * Holds the Redirector Helper.
     *
     * @var Zend_Controller_Action_Helper_Redirector
     */
    private $__redirector = null;

    /**
     * Holds the FlashMessenger Helper.
     *
     * @var Zend_Controller_Action_Helper_Messenger
     */
    private $__flashMessenger = null;

    /**
     * Logger instance.
     * @var Zend_Log
     */
    protected $_logger;

    /**
     * Do some initialization on startup of every action
     *
     * @return void
     */
    public function init() {
        $this->_logger = Zend_Registry::get('Zend_Log');
        $this->view->title = $this->_request->getModuleName() . '_' . $this->_request->getParam('controller') . '_' . $this->_request->getParam('action');
        $this->__redirector = $this->_helper->getHelper('Redirector');
        $this->__flashMessenger = $this->_helper->getHelper('FlashMessenger');
        $this->view->flashMessenger = $this->__flashMessenger;
    }

    /**
     * Redirects to an action / controller / module, sets a message for the redirect target view.
     *
     * @param  string $action     The redirect target action
     * @param  string $message    The message to be displayed
     * @param  string $controller The redirect target controller
     * @param  string $module     The redirect target model
     * @param  array  $params     Parameters for the redirect target action
     * @return void
     */
    protected function _redirectTo($action, $message = null, $controller = null, $module = null, $params = array()) {
        $this->performRedirect($action, $message, $controller, $module, $params);
    }

    /**
     *
     * Performs a permanent (301) redirect.
     *
     * @param string $action        The target action.
     * @param string $message       The message to be displayed.
     * @param string $controller    The target controller.
     * @param string $module        The target module.
     * @param array $params         Optional request parameters.
     */
    protected function _redirectToPermanent($action, $message = null, $controller = null, $module = null, $params = array()) {
        $this->__redirector->setCode(301);
        $this->performRedirect($action, $message, $controller, $module, $params);
    }
    
    protected function _redirectToAndExit($action, $message = null, $controller = null, $module = null, $params = array()) {
        $this->performRedirect($action, $message, $controller, $module, $params, true);
    }

    protected function _redirectToPermanentAndExit($action, $message = null, $controller = null, $module = null, $params = array()) {
        $this->__redirector->setCode(301);
        $this->performRedirect($action, $message, $controller, $module, $params, true);
    }

    private function performRedirect($action, $message = null, $controller = null, $module = null, $params = array(), $exit = false) {
        if (!is_null($message)) {
            if (is_array($message) && count($message) !==  0) {
                $keys = array_keys($message);
                $key = $keys[0];
                if ($key === 'failure' || $key === 'notice') {
                    $this->__flashMessenger->addMessage(array ('level' => $key, 'message' => $message[$key]));
                }
                else {
                    $this->__flashMessenger->addMessage(array ('level' => 'notice', 'message' => $message[$key]));
                }
            }
            else if (is_string($message) && $message != '') {
                $this->__flashMessenger->addMessage(array('level' => 'notice', 'message' => $message));
            }
        }
        $this->_logger->debug("redirect to module: $module controller: $controller action: $action");
        $this->__redirector->gotoSimple($action, $controller, $module, $params);
        $this->__redirector->setExit($exit);

        return;
    }

    /**
     * Forward request to a different action.
     *
     * Sets the 'action' parameter so title key is correct.
     * @return void
     */
    protected function _forwardToAction($action) {
        $this->_request->setParam('action', $action);
        $this->_forward($action);
    }

    /**
     * Method called when access to module has been denied.
     */
    protected function rejectRequest() {
        // we are not allowed to access this module -- but why?
        $identity = Zend_Auth::getInstance()->getIdentity();

        $errorcode = 'no_identity_error';
        if (!empty($identity)) {
            $errorcode = 'wrong_identity_error';
        }

        // Forward to module auth
        $this->__flashMessenger->addMessage(array('level' => 'failure', 'message' => $errorcode));
        $this->__redirector->gotoSimple('index', 'auth', 'default');
    }

}