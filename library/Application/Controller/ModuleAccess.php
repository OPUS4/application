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
 * @copyright   Copyright (c) 2011, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\LoggingTrait;
use Opus\Common\Security\Realm;

/**
 * Module-access-checking controller for Opus Applications.
 */
class Application_Controller_ModuleAccess extends Zend_Controller_Action
{
    use LoggingTrait;

    public const ACCESS_DENIED_ACTION = 'module-access-denied';

    /**
     * Konfigurationsobjekt.
     *
     * @var Zend_Config
     */
    private $config;

    /**
     * Use pre-dispatch to check user access rights *before* action is called.
     */
    public function preDispatch()
    {
        parent::preDispatch();
        $this->checkAccessModulePermissions();
    }

    public function init()
    {
        parent::init();

        // Wählt Hauptmenueeintrag nach Modul aus
        // Fuer einige Module muss das ueberschrieben werden (Review, Search).
        $this->getHelper('MainMenu')->setActive($this->_request->getModuleName());
    }

    /**
     * Checks if the user is allowed to access the given module.
     */
    protected function checkAccessModulePermissions()
    {
        $logger = $this->getLogger();
        $module = $this->_request->getModuleName();

        $action = $this->_request->getActionName();
        if ($action === self::ACCESS_DENIED_ACTION) {
            $logger->debug("forwarding to unchecked action $module ($action)");
            return;
        }

        $logger->debug("starting authorization check for module '$module'");

        $realm = Realm::getInstance();

        if (! $realm->skipSecurityChecks()) {
            // Check, if the user has accesss to the module...
            if (true !== $realm->checkModule($module)) {
                $logger->debug("FAILED authorization check for module '$module'");
                $this->_forward(self::ACCESS_DENIED_ACTION);
                return;
            }

            // Check, if the user has the right permission...
            if (true !== $this->checkPermissions()) {
                $logger->debug("FAILED authorization through ACLs");
                $this->_forward(self::ACCESS_DENIED_ACTION);
                return;
            }
        }

        // Check, controller-specific constraints...
        if (true !== $this->customAccessCheck()) {
            $logger->debug("FAILED custom authorization check for module '$module'");
            $this->_forward(self::ACCESS_DENIED_ACTION);
            return;
        }

        $logger->debug("authorization check for module '$module' successful");
    }

    /**
     * @return bool
     *
     * TODO Kann ein Teil davon vielleicht schon im Bootstrap passieren?
     */
    protected function checkPermissions()
    {
        $logger = $this->getLogger();

        $navigation = $this->getNavigation();
        $acl        = $navigation->getAcl();

        if ($acl === null) {
            return true;
        }

        $activePage = $navigation->findActive($navigation->getContainer());

        if (! empty($activePage)) {
            $logger->debug('ACL: active page found');
            $activePage = $activePage['page'];

            $resource = $this->findResourceForPage($activePage);

            return $resource === null || $acl->isAllowed(Application_Security_AclProvider::ACTIVE_ROLE, $resource);
        } else {
            $logger->debug('ACL: active page not found');
            // Entweder die Seite ist nicht erfasst oder Zugriff ist nicht erlaubt.
            $pageInNav = $this->isPageForRequestInNavigation($navigation);

            $logger->debug('ACL: page configured = ' . $pageInNav);

            return ! $pageInNav;
        }
    }

    /**
     * @return Zend_Navigation|object
     */
    protected function getNavigation()
    {
        return $this->view->getHelper('navigation');
    }

    /**
     * Searches navigation for resource definition for current request.
     *
     * @param Zend_Navigation_Page $activePage
     * @return string
     */
    protected function findResourceForPage($activePage)
    {
        $resource = null;

        if ($activePage instanceof Zend_Navigation_Page) {
            $resource = $activePage->getResource();

            $page = $activePage->getParent();

            while ($page !== null && $page instanceof Zend_Navigation_Page && $resource === null) {
                $resource = $page->getResource();
                $page     = $page->getParent();
            }
        }

        return $resource;
    }

    /**
     * Prüft ob die Seite in der Navigation definiert ist.
     *
     * @param Zend_Navigation $navigation
     * @return bool
     */
    protected function isPageForRequestInNavigation($navigation)
    {
        $module     = $this->_request->getModuleName();
        $controller = $this->_request->getControllerName();
        $action     = $this->_request->getActionName();

        if ($module !== null) {
            $pages = $navigation->getContainer()->findAllBy('module', $this->_request->getModuleName());

            if ($controller !== null && $pages !== null) {
                // found pages for module
                foreach ($pages as $page) {
                    if ($page->getController() === $controller) {
                        // found pages for controller
                        if ($action !== null && $page->getAction() === $action) {
                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }

    /**
     * Method stub to be overridden by controllers.  Enables checks for custom
     * properties.
     *
     * @return true
     */
    protected function customAccessCheck()
    {
        return true;
    }

    /**
     * Method called when access to module has been denied.
     */
    public function moduleAccessDeniedAction()
    {
        $this->_forward('login', 'auth', 'default');
    }

    /**
     * Returns configuration object or null if none can be found.
     *
     * @return null|Zend_Config
     */
    public function getConfig()
    {
        if ($this->config === null) {
            $this->config = Application_Configuration::getInstance()->getConfig();
        }
        return $this->config;
    }

    /**
     * TODO move to parent class (redundant code)
     */
    protected function disableViewRendering()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
    }
}
