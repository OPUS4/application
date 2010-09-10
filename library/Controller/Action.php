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
 * @copyright   Copyright (c) 2009, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Controller for Opus Applications.
 *
 * @category    Application
 * @package     Controller
 */
class Controller_Action extends Zend_Controller_Action {

    /**
     * Holds the Layout Helper.
     *
     * @var Zend_Layout
     */
    private $__layout = null;

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
     * Redirects to an action / controller / module, sets a message for the redirect target view.
     *
     * @param  mixed  $message    The message to be displayed
     * @param  array  $action     The redirect target action
     * @param  mixed  $controller The redirect target controller
     * @param  mixed  $module     The redirect target model
     * @param  mixed  $params     Parameters for the redirect target action
     * @return void
     */
    protected function _redirectTo($message = '', $action, $controller = null, $module = null, $params = array()) {
        $this->__flashMessenger->addMessage($message);
        $this->__redirector->gotoSimple($action, $controller, $module, $params);
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
     * Do some initialization on startup of every action
     *
     * @return void
     */
    public function init() {
        $this->view->title = $this->_request->getModuleName() . '_' . $this->_request->getParam('controller') . '_' . $this->_request->getParam('action');
        // $this->__layout = Zend_Layout::getMvcInstance()->getView();
        $this->__redirector = $this->_helper->getHelper('Redirector');
        $this->__flashMessenger = $this->_helper->getHelper('FlashMessenger');
    }

    /**
     * Actions to be performed after every action
     *
     * @return void
     */
    public function postDispatch() {

        if(isset($this->__layout))
            $this->__layout->placeholder('messages')->set(join("",$this->__flashMessenger->getMessages()));

        parent::postDispatch();
    }

    /**
     * Helper method that redirects to another <b>internal</b> url
     * @param String $url url to redirect to
     */
    protected function redirectTo($url) {
        $redirector = $this->_helper->getHelper('Redirector');
        $redirector->setPrependBase(false);
        $redirector->setGotoUrl('');
        $redirector->setExit(false);
        $redirector->gotoUrl($url);
    }
}
