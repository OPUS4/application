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
 * @package     View
 * @author      Ralf Claussnitzer (ralf.claussnitzer@slub-dresden.de)
 * @author      Pascal-Nicolas Becker <becker@zib.de>
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * The LoginBar View Helper returns a link to an actual logout controller and action
 * or to a login action respectivly. By default it uses "login" and "logout" actions of
 * the controller "auth" in module "default".
 *
 * @category    Application
 * @package     View
 */
class View_Helper_LoginBar {

    /**
     * Default login action.
     *
     * @var array
     */
    protected $_login_url = array('action' => 'login', 'controller' => 'auth', 'module' => 'default');

    /**
     * Default logout action.
     *
     * @var array
     */
    protected $_logout_url = array('action' => 'logout', 'controller' => 'auth', 'module' => 'default');

    /**
     * Holds the current view object.
     *
     * @var Zend_View_Interface
     */
    protected $_view = null;

    public function setView(Zend_View_Interface $view) {
        $this->_view = $view;
    }

    /**
     * Set the action (controller and module) to perform a login.
     *
     * @param string $action     Login action name.
     * @param string $controller (Optional) Login controller name.
     * @param string $module     (Optional) Login module name.
     * @return void
     */
    public function setLoginAction($action, $controller = null, $module = null) {
        $this->_login_url['action'] = $action;
        if (is_null($controller) === false) {
            $this->_login_url['controller'] = $controller;
        }
        if (is_null($module) === false) {
            $this->_login_url['module'] = $module;
        }
    }

    /**
     * Set the action (controller and module) to perform a logout.
     *
     * @param string $action     Logout action name.
     * @param string $controller (Optional) Logout controller name.
     * @param string $module     (Optional) Logout module name.
     * @return void
     */
    public function setLogoutAction($action, $controller = null, $module = null) {
        $this->_logout_url['action'] = $action;
        if (is_null($controller) === false) {
            $this->_logout_url['controller'] = $controller;
        }
        if (is_null($module) === false) {
            $this->_logout_url['module'] = $module;
        }
    }

    /**
     * Return an instance of the view helper.
     *
     * @return Opus_View_Helper_LoginBar
     */
    public function loginBar() {
        return $this;
    }

    /**
     * Return view helper output. Depending on if a user is logged on, an login link or an logout link
     * is returned respectivly.
     *
     * @return unknown
     */
    public function __toString() {
        $returnParams = Zend_Controller_Action_HelperBroker::getStaticHelper('ReturnParams');
        $identity = Zend_Auth::getInstance()->getIdentity();
        if (empty($identity) === true) {
            $url = $this->_view->url(array_merge($this->_login_url, $returnParams->getReturnParameters()));
            return '<a href="' . $url . '">Login</a>';
        } else {
            $config = Zend_Registry::get('Zend_Config');
            if (isset($config->account->editOwnAccount)) {
                $addAccountLink = $config->account->editOwnAccount;
            }
            else {
                $addAccountLink = true;
            }

            if ($addAccountLink) {
                $accountUrl = $this->_view->url(array('module' => 'account'), null, true);
                $url = $this->_view->url(array_merge($this->_logout_url, $returnParams->getReturnParameters()));
                return '<a style="padding-right: 1em" href="' . $accountUrl . '">Account</a> <a href="' . $url . '">Logout</a>';
            }
            else {
                $url = $this->_view->url(array_merge($this->_logout_url, $returnParams->getReturnParameters()));
                return '<a href="' . $url . '">Logout</a>';
            }
        }
    }

}
