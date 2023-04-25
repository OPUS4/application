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
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\Config;
use Opus\Common\Security\Realm;

/**
 * The LoginBar View Helper returns a link to an actual logout controller and action
 * or to a login action respectivly. By default it uses "login" and "logout" actions of
 * the controller "auth" in module "default".
 */
class Application_View_Helper_LoginBar extends Zend_View_Helper_Abstract
{
    /**
     * Default login action.
     *
     * @var array
     */
    protected $loginUrl = ['action' => 'login', 'controller' => 'auth', 'module' => 'default'];

    /**
     * Default logout action.
     *
     * @var array
     */
    protected $logoutUrl = ['action' => 'logout', 'controller' => 'auth', 'module' => 'default'];

    /**
     * Set the action (controller and module) to perform a login.
     *
     * @param string      $action     Login action name.
     * @param null|string $controller (Optional) Login controller name.
     * @param null|string $module (Optional) Login module name.
     */
    public function setLoginAction($action, $controller = null, $module = null)
    {
        $this->loginUrl['action'] = $action;
        if ($controller !== null) {
            $this->loginUrl['controller'] = $controller;
        }
        if ($module !== null) {
            $this->loginUrl['module'] = $module;
        }
    }

    /**
     * Set the action (controller and module) to perform a logout.
     *
     * @param string      $action     Logout action name.
     * @param null|string $controller (Optional) Logout controller name.
     * @param null|string $module (Optional) Logout module name.
     */
    public function setLogoutAction($action, $controller = null, $module = null)
    {
        $this->logoutUrl['action'] = $action;
        if ($controller !== null) {
            $this->logoutUrl['controller'] = $controller;
        }
        if ($module !== null) {
            $this->logoutUrl['module'] = $module;
        }
    }

    /**
     * Return an instance of the view helper.
     *
     * @return $this
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
        $returnParams = Zend_Controller_Action_HelperBroker::getStaticHelper('ReturnParams');
        $identity     = Zend_Auth::getInstance()->getIdentity();
        if (empty($identity) === true) {
            $url = $this->view->url(array_merge($this->loginUrl, $returnParams->getReturnParameters()));
            return '<a rel="nofollow" href="' . $url . '">' . $this->view->translate('default_auth_index') . '</a>';
        }

        // Default setting for edit own account: allow and add link.
        $addAccountLink = false;

        // Prüfe, ob Nutzer Zugriff auf Account Modul hat
        $realm = Realm::getInstance();

        if ($realm->checkModule('account') === true) {
            // Prüfe, ob Nutzer ihren Account editieren dürfen
            $config = Config::get();
            if (isset($config) && isset($config->account->editOwnAccount)) {
                $addAccountLink = filter_var($config->account->editOwnAccount, FILTER_VALIDATE_BOOLEAN);
            }
        }

        $url        = $this->view->url(array_merge($this->logoutUrl, $returnParams->getReturnParameters()));
        $logoutLink = '<a rel="nofollow" href="' . $url . '">' . $this->view->translate('default_auth_logout')
            . ' (' . htmlspecialchars($identity) . ')</a>';

        if ($addAccountLink) {
            $accountUrl = $this->view->url(['module' => 'account'], null, true);
            return '<a rel="nofollow" style="padding-right: 1em" href="' . $accountUrl
            . '">' . $this->view->translate('default_auth_account') . '</a> ' . $logoutLink;
        }

        return $logoutLink;
    }
}
