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
 * @author      Felix Ostrowski <ostrowski@hbz-nrw.de>
 * @author      Sascha Szott <szott@zib.de>
 * @copyright   Copyright (c) 2009, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class Controller_Action extends Zend_Controller_Action {

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
     * @param  array  $action     The redirect target action
     * @param  mixed  $message    The message to be displayed
     * @param  mixed  $controller The redirect target controller
     * @param  mixed  $module     The redirect target model
     * @param  mixed  $params     Parameters for the redirect target action
     * @return void
     */
    protected function _redirectTo($action, $message = '', $controller = null, $module = null, $params = array()) {
        $this->performRedirect($action, $message, $controller, $module, $params);
    }

    protected function _redirectToAndExit($action, $message = '', $controller = null, $module = null, $params = array()) {
        $this->performRedirect($action, $message, $controller, $module, $params, true);
    }

    private function performRedirect($action, $message = '', $controller = null, $module = null, $params = array(), $exit = false) {
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
            else if (is_string($message)) {
                $this->__flashMessenger->addMessage(array('level' => 'notice', 'message' => $message));
            }
        }
        else
        $this->_logger->debug("redirect to module: $module controller: $controller action: $action");
        $this->__redirector->gotoSimple($action, $controller, $module, $params);
        $this->__redirector->setExit($exit);
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
}