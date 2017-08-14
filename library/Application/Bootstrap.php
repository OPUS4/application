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
 */

/**
 * Provide methods to setup and run the application. It also provides a couple of static
 * variables for quicker access to application components like the front controller.
 *
 * @category    Application
 * @package     Application
 * @author      Ralf Claussnitzer (ralf.claussnitzer@slub-dresden.de)
 * @author      Simone Finkbeiner (simone.finkbeiner@ub.uni-stuttgart.de)
 * @author      Jens Schwidder <schwidder@zib.de>
 * @author      Michael Lang <lang@zib.de>
 * @copyright   Copyright (c) 2008-2017, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 *
 * TODO unit test bootstrap
 */
class Application_Bootstrap extends Opus_Bootstrap_Base {

    /**
     * Setup a front controller instance with error options and module
     * directory.
     *
     * @return void
     *
     * TODO rename to _initControllerPlugins
     */
    protected function _initOpusFrontController() {
        $this->bootstrap(array('frontController'));

        $frontController = $this->getResource('frontController'); // Zend_Controller_Front::getInstance();

        /*
         * Add a custom front controller plugin for setting up an appropriate
         * include path to the form classes of modules.
         */
        $moduleprepare = new Application_Controller_Plugin_LoadTranslation();
        $frontController->registerPlugin($moduleprepare);

        // Add security realm initialization
        // the SWORD module uses a different auth mechanism
        $realmSetupPlugin = new Application_Controller_Plugin_SecurityRealm('sword');
        $frontController->registerPlugin($realmSetupPlugin);

        // Add navigation initialization plugin
        $navigationPlugin = new Application_Controller_Plugin_Navigation();
        $frontController->registerPlugin($navigationPlugin);

        // Get Name of Module, Controller and Action for Use in View
        $viewSetup = new Application_Controller_Plugin_ViewSetup();
        $frontController->registerPlugin($viewSetup);

        $router = $frontController->getRouter();

        // add default route for regular module/controller/action requests
        $router->addDefaultRoutes();

        // specity the SWORD module as RESTful
        $restRoute = new Zend_Rest_Route($frontController, array(), array('sword'));
        $router->addRoute('rest', $restRoute);

        $documentRoute = new Application_Controller_Route_Redirect(
            '^(\d+)/?$',
            array('module' => 'frontdoor', 'controller' => 'index', 'controller' => 'index', 'docId' => 1),
            array( 1 => 'docId'),
            'document/%s'
        );

        $router->addRoute('document', $documentRoute);

        // Simplify access to sitelinks, since crawlers module does not have a IndexController
        $crawlersRoute = new Application_Controller_Route_Redirect(
            'crawlers',
            array('module' => 'crawlers', 'controller' => 'sitelinks', 'action' => 'index')
        );

        $router->addRoute('crawlers', $crawlersRoute);
    }

    /**
     * Configure view with UTF-8 options and ViewRenderer action helper.
     * The Zend_Layout component also gets initialized here.
     *
     * @return Zend_View
     */
    protected function _initView() {
        $this->bootstrap(array('Configuration','OpusFrontController'));

        $config = $this->getResource('Configuration');

        $theme = $config->theme;
        if (empty($theme)) {
            $theme = 'opus4';
        }

        $layoutpath = APPLICATION_PATH . '/public/layouts/' . $theme;
        if (false === is_dir($layoutpath)) {
            throw new Exception('Requested theme "' . $theme . '" not found.');
        }

        Zend_Layout::startMvc(
            array(
                'layoutPath'=> $layoutpath,
                'layout'=>'common')
        );

        // Initialize view with custom encoding and global view helpers.
        $view = new Zend_View;
        $view->setEncoding('UTF-8');

        // Set doctype to XHTML1 strict
        $view->doctype('XHTML1_STRICT');

        // Set path to Zend extension view helpers to be accessible in other
        // modules too.
        $libRealPath = realpath(APPLICATION_PATH . '/library');

        $view->addHelperPath($libRealPath . '/Application/View/Helper', 'Application_View_Helper');

        // Set path to shared view partials
        $view->addScriptPath($libRealPath . '/Application/View/Partial');

        // Breadcrumbs View Helper global ersetzen
        $breadcrumbsHelper = new Application_View_Helper_Breadcrumbs();
        $view->registerHelper($breadcrumbsHelper, 'breadcrumbs');

        $viewRenderer = new Zend_Controller_Action_Helper_ViewRenderer($view);

        Zend_Controller_Action_HelperBroker::addHelper($viewRenderer);

        // Make View available to unit test (TODO maybe there is a better way?)
        Zend_Registry::set('Opus_View', $view);

        return $view;
    }

    /**
     * Setup Zend_Cache for caching application data and register under 'Zend_Cache_Page'.
     *
     * @return void
     */
    protected function _setupPageCache() {
        $config = $this->getResource('Configuration');

        $pagecache = null;
        $frontendOptions = array(
            'lifetime' => 600, // in seconds
            'debug_header' => false,
            // turning on could slow down caching
            'automatic_serialization' => false,
            'default_options' => array(
                'cache_with_get_variables' => true,
                'cache_with_post_variables' => true,
                'cache_with_session_variables' => true,
                'cache_with_files_variables' => true,
                'cache_with_cookie_variables' => true,
                'make_id_with_get_variables' => true,
                'make_id_with_post_variables' => true,
                'make_id_with_session_variables' => true,
                'make_id_with_files_variables' => true,
                'make_id_with_cookie_variables' => true,
                'cache' => true)
        );

        $backendOptions = array(
            // Directory where to put the cache files. Must be writeable for application server
            'cache_dir' => $config->workspacePath . '/cache/'
            );

        $pagecache = Zend_Cache::factory('Page', 'File', $frontendOptions, $backendOptions);
        Zend_Registry::set('Zend_Cache_Page', $pagecache);
    }

    /**
     * Setup Zend_Translate with language resources of all existent modules.
     *
     * It is assumed that all modules are stored under modules/. The search
     * pattern Zend_Translate gets configured with is to look for a
     * folder and file structure similar to:
     *
     * language/
     *         index.tmx
     *         loginform.tmx
     *         ...
     *
     * Sprache verwenden
     * - Session (if supported)
     * - Locale (if supported)
     * - Default
     *
     * @return Zend_Translate
     */
    protected function _initTranslation() {
        $this->bootstrap(array('Configuration', 'Session', 'Logging', 'ZendCache'));

        $logger = $this->getResource('Logging');

        $translate = new Application_Translate();

        Zend_Registry::set(Application_Translate::REGISTRY_KEY, $translate);

        $configHelper = new Application_Configuration();

        $session = $this->getResource('Session');

        $language = $session->language;

        // check if language is supported; if not, use language from locale
        if (!$configHelper->isLanguageSupported($language)) {
            $locale = new Zend_Locale();
            $language = $locale->getLanguage();
            $logger->debug("Current locale = '$language'");
            // check if locale is supported; if not, use default language
            if (!$configHelper->isLanguageSupported($language)) {
                $language = $configHelper->getDefaultLanguage();
            }
        }

        $logger->debug("Language set to '$language'.");
        $session->language = $language;
        $translate->setLocale($language);
        $translate->loadModule('default'); // immer die Übersetzungen aus Default-Modul laden

        return $translate;
    }

    /**
     * Setup session.
     *
     * @return Zend_Session_Namespace
     */
    protected function _initSession() {
        $this->bootstrap(array('Database'));
        return new Zend_Session_Namespace();
    }

    /**
     * Initializes general navigation as configured in navigationModules.xml'
     *
     * @return void
     *
     * TODO possible to cache? performance improvement?
     */
    protected function _initNavigation() {
        $this->bootstrap('Logging', 'View');

        $log = $this->getResource('Logging');
        $log->debug('Initializing Zend_Navigation');

        $navigationConfigFile = APPLICATION_PATH . '/application/configs/navigationModules.xml';
        $navConfig = new Zend_Config_Xml($navigationConfigFile, 'nav');

        $log->debug('Navigation config file is: ' . $navigationConfigFile);

        $container = null;
        try {
            $container = new Zend_Navigation($navConfig);
        }
        catch (Zend_Navigation_Exception $e) {
            /* TODO This suppresses the "Mystery Bug" that is producing errors
             * in unit tests sometimes. So far we haven't figured out the real
             * reason behind the errors. In regular Opus instances the error
             * has not appeared (as far as we know).
             */
            $log->err($e);
        }

        $view = $this->getResource('View');
        $view->navigation($container);

        $log->debug('Zend_Navigation initialization completed');

        return $container;
    }

    /**
     * Initialisiert Zend_Acl für die Authorization in OPUS.
     *
     * TODO use Application_Security_AclProvider
     */
    protected function _initAuthz() {
        $this->bootstrap('Logging', 'Navigation', 'view');

        $config = $this->getResource('configuration');

        if (isset($config->security) && $config->security == 1) {
            Application_Security_AclProvider::init();
        }
        else {
            Zend_View_Helper_Navigation_HelperAbstract::setDefaultAcl(null);
            Zend_View_Helper_Navigation_HelperAbstract::setDefaultRole(null);
        }
    }

    /**
     * Initializes navigation container for main menu.
     * @return Zend_Navigation
     */
    protected function _initMainMenu() {
        $this->bootstrap('Logging', 'View', 'Navigation');

        $navigationConfigFile = APPLICATION_PATH . '/application/configs/navigation.xml';

        $navConfig = new Zend_Config_Xml($navigationConfigFile, 'nav');

        $container = new Zend_Navigation($navConfig);

        $view = $this->getResource('View');

        $view->navigationMainMenu = $container;

        // TODO Find better way without Zend_Registry
        Zend_Registry::set('Opus_Navigation', $container);

        // return $container;
    }

    /**
     * writes Opus-Version in html header
     */
    protected  function _initVersionInfo() {
        $this->bootstrap('View');
        $view = $this->getResource('View');
        $view->headMeta()->appendName('Opus-Version', Application_Configuration::getOpusVersion());
    }

    /**
     * Creates exporter for registering export services.
     *
     * @return Application_Export_Exporter
     */
    protected function _initExporter()
    {
        $this->bootstrap('Configuration');

        $exporter = new Application_Export_Exporter();

        Zend_Registry::set('Opus_Exporter', $exporter);

        $exportService = new Application_Export_ExportService();

        // TODO merge ExportService with Exporter class (?)
        Zend_Registry::set('Opus_ExportService', $exportService);

        return $exporter;
    }

}
