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
 * @author      Jens Schwidder (schwidder@zib.de)
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\Config;
use Opus\Security\Realm;

/**
 * The LoginBar View Helper returns a link to an actual logout controller and action
 * or to a login action respectivly. By default it uses "login" and "logout" actions of
 * the controller "auth" in module "default".
 *
 * @category    Application
 * @package     View
 */
class Application_View_Helper_LoginBar extends \Zend_View_Helper_Abstract
{

    /**
     * Default login action.
     *
     * @var array
     */
    protected $_loginUrl = ['action' => 'login', 'controller' => 'auth', 'module' => 'default'];

    /**
     * Default logout action.
     *
     * @var array
     */
    protected $_logoutUrl = ['action' => 'logout', 'controller' => 'auth', 'module' => 'default'];

    /**
     * Set the action (controller and module) to perform a login.
     *
     * @param string $action     Login action name.
     * @param string $controller (Optional) Login controller name.
     * @param string $module     (Optional) Login module name.
     * @return void
     */
    public function setLoginAction($action, $controller = null, $module = null)
    {
        $this->_loginUrl['action'] = $action;
        if (is_null($controller) === false) {
            $this->_loginUrl['controller'] = $controller;
        }
        if (is_null($module) === false) {
            $this->_loginUrl['module'] = $module;
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
    public function setLogoutAction($action, $controller = null, $module = null)
    {
        $this->_logoutUrl['action'] = $action;
        if (is_null($controller) === false) {
            $this->_logoutUrl['controller'] = $controller;
        }
        if (is_null($module) === false) {
            $this->_logoutUrl['module'] = $module;
        }
    }

    /**
     * Return an instance of the view helper.
     *
     * @return Application_View_Helper_LoginBar
     */
    public function loginBar()
    {
        return $this;
    }

    /**
     * Return view helper output. Depending on if a user is logged on, an login link or an logout link
     * is returned respectivly.
     *
     * @return string
     */
    public function __toString()
    {
        $returnParams = \Zend_Controller_Action_HelperBroker::getStaticHelper('ReturnParams');
        $identity = \Zend_Auth::getInstance()->getIdentity();
        if (empty($identity) === true) {
            $url = $this->view->url(array_merge($this->_loginUrl, $returnParams->getReturnParameters()));
            return '<a rel="nofollow" href="' . $url . '">' . $this->view->translate('default_auth_index') . '</a>';
        }

        // Default setting for edit own account: allow and add link.
        $addAccountLink = false;

        // Prüfe, ob Nutzer Zugriff auf Account Modul hat
        $realm = Realm::getInstance();

        if ($realm->checkModule('account') == true) {
            // Prüfe, ob Nutzer ihren Account editieren dürfen
            $config = Config::get();
            if (isset($config) and isset($config->account->editOwnAccount)) {
                $addAccountLink = filter_var($config->account->editOwnAccount, FILTER_VALIDATE_BOOLEAN);
            }
        }

        $url = $this->view->url(array_merge($this->_logoutUrl, $returnParams->getReturnParameters()));
        $logoutLink = '<a rel="nofollow" href="' . $url . '">' . $this->view->translate('default_auth_logout')
            . ' (' . htmlspecialchars($identity) . ')</a>';

        if ($addAccountLink) {
            $accountUrl = $this->view->url(['module' => 'account'], null, true);
            return '<a rel="nofollow" style="padding-right: 1em" href="' . $accountUrl .
            '">' . $this->view->translate('default_auth_account') . '</a> ' . $logoutLink;
        }

        return $logoutLink;
    }
}
