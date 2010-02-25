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
 * @package     Application
 * @author      Ralf Claussnitzer (ralf.claussnitzer@slub-dresden.de)
 * @author      Simone Finkbeiner (simone.finkbeiner@ub.uni-stuttgart.de)
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Autoloader not yet initialized.
 */
require_once 'Opus/Bootstrap/Base.php';

/**
 * Provide methods to setup and run the application. It also provides a couple of static
 * variables for quicker access to application components like the front controller.
 *
 * @category    Application
 * @package     Application
 *
 */
class Application_Bootstrap extends Opus_Bootstrap_Base {

    /**
     * Start bootstrapped application.
     *
     * @return void
     */
    protected function _run() {
        if (Zend_Registry::isRegistered('Zend_Cache_Page')) {
            $pagecache = Zend_Registry::get('Zend_Cache_Page');
            $pagecache->start();
        }
        $response = $this->_frontController->dispatch();
        $response->sendResponse();
    }

    /**
     * Override application frontend setup routine to setup a front controller instance.
     *
     * @return void
     */
    protected function _setupFrontend() {
        parent::_setupFrontend();
        $this->_setupTranslation();
        $this->_setupLanguageList();
        $this->_setupFrontController();
        $this->_setupView();
    }

    /**
     * Set up custom caching engines.
     *
     * @return void
     */
    protected function _setupFrontendCaching() {
        parent::_setupFrontendCaching();
        $this->_setupTranslationCache();
        //$this->_setupPageCache();
    }

    /**
     * Set up custom caching engines for any backend functionality.
     *
     * @return void
     */
    protected function _setupBackendCaching() {
        $this->_setupDatabaseCache();
    }


    /**
     * Setup translation cache.
     *
     * @return void
     */
    protected function _setupTranslationCache() {
        $cache = null;
        $frontendOptions = array(
            // Set cache lifetime to 5 minutes (in seconds)
            'lifetime' => 600,
            'automatic_serialization' => true,
        );

        $backendOptions = array(
            // Directory where to put the cache files. Must be writeable for application server
            'cache_dir' => $this->_applicationWorkspaceDirectory . '/cache/'
            );

        $cache = Zend_Cache::factory('Core', 'File', $frontendOptions, $backendOptions);
        Zend_Translate::setCache($cache);
    }


    /**
     * Setup a front controller instance with error options and module
     * directory.
     *
     * @return void
     *
     */
    protected function _setupFrontController()
    {
        $this->_frontController = Zend_Controller_Front::getInstance();
        // If you want to use the error controller, disable throwExceptions
        $this->_frontController->throwExceptions(true);
        $this->_frontController->returnResponse(true);
        $this->_frontController->addModuleDirectory($this->_applicationRootDirectory . '/modules');

        /*
         * Add a custom front controller plugin for setting up an appropriate
         * include path to the form classes of modules.
         */
        $moduleprepare = new Controller_Plugin_ModulePrepare($this->_applicationRootDirectory . '/modules');
        $moduleprepare->appendClassPath('models')
        ->appendClassPath('forms');
        $this->_frontController->registerPlugin($moduleprepare);

		// Checks the current requeste module's directory for an initFile and runs it before controller is loaded.
		$moduleInit = new Controller_Plugin_ModuleInit();
		$this->_frontController->registerPlugin($moduleInit);

        /*
         * Add a custorm front controller plugin of manipulating routing information
         * for webapi REST requests.
         */
        $restRouterPlugin = new Controller_Plugin_RestManipulation();
        $this->_frontController->registerPlugin($restRouterPlugin);

        // Add security realm initialization
        $realmSetupPlugin = new Controller_Plugin_SecurityRealm();
        $this->_frontController->registerPlugin($realmSetupPlugin);

        /*
         * Add a front controller plugin for oai-requests because of
         * DNB-validation
         */
        $oaiPlugin = new Controller_Plugin_DnbXmlPostprocess();
        $this->_frontController->registerPlugin($oaiPlugin);
    }

    /**
     * Configure view with UTF-8 options and ViewRenderer action helper.
     * The Zend_Layout component also gets initialized here.
     *
     * @return void
     *
     */
    protected function _setupView()
    {
        $config = Zend_Registry::get('Zend_Config');
        $theme = $config->theme;
        if (true === empty($theme)) {
            $theme = 'default';
        }

        $layoutpath = $this->_applicationRootDirectory . '/public/layouts/' . $theme;

        if (false === is_dir($layoutpath)) {
            throw new Exception('Requested theme "' . $theme . '" not found.');
        }

        Zend_Layout::startMvc(array(
                'layoutPath'=> $layoutpath,
                'layout'=>'common'));

        // Initialize view with custom encoding and global view helpers.
        $view = new Zend_View;
        $view->setEncoding('UTF-8');

        // Set doctype to XHTML1 strict
        $view->doctype('XHTML1_STRICT');

        // Set path to Zend extension view helpers to be accessible in other
        // modules too.
        $libRealPath = realpath($this->_applicationRootDirectory . '/library');
        $view->addHelperPath($libRealPath . '/View/Helper', 'View_Helper');

        // Set path to shared view partials
        $view->addScriptPath($libRealPath . '/View/Partials');

        $viewRenderer = new Zend_Controller_Action_Helper_ViewRenderer($view);
        Zend_Controller_Action_HelperBroker::addHelper($viewRenderer);
    }

    /**
     * Setup Zend_Cache for caching application data and register under 'Zend_Cache_Page'.
     *
     * @return void
     *
     */
    protected function _setupPageCache()
    {
        $pagecache = null;
        $frontendOptions = array(
            // Set cache lifetime to 5 minutes (in seconds)
            'lifetime' => 600,
            'debug_header' => false,
            // turning on could slow down caching
            'automatic_serialization' => false,
            'default_options' => array(
                // standard value false
                'cache_with_get_variables' => true,
                // standard value false
                'cache_with_post_variables' => true,
                // standard value false
                'cache_with_session_variables' => true,
                // standard value false
                'cache_with_files_variables' => true,
                // standard value false
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
            'cache_dir' => $this->_applicationWorkspaceDirectory . '/cache/'
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
     * @return void
     *
     */
    protected function _setupTranslation()
    {
        $sessiondata = new Zend_Session_Namespace();
        $options = array(
            'clear' => false,
            'scan' => Zend_Translate::LOCALE_FILENAME,
            'ignore' => '.',
            'disableNotices' => true
            );
        $translate = new Zend_Translate(
            Zend_Translate::AN_TMX,
            $this->_applicationRootDirectory . '/modules/',
            'auto',
            $options
            );

        if (empty($sessiondata->language) === false) {
            // Example for logging something
            $logger = Zend_Registry::get('Zend_Log');
            $logger->info('Switching to language "' . $sessiondata->language . '".');
            $translate->setLocale($sessiondata->language);
        } else {
            $sessiondata->language = $translate->getLocale();
        }

        $registry = Zend_Registry::getInstance();
        $registry->set('Zend_Translate', $translate);
    }

    /**
     * Setup language list.
     *
     * @return void
     */
    protected function _setupLanguageList() {
        $registry = Zend_Registry::getInstance();

        $sessiondata = new Zend_Session_Namespace();
        if (false === empty($sessiondata->language)) {
            $locale = new Zend_Locale($sessiondata->language);
        } else {
            $locale = $registry->get('Zend_Translate')->getLocale();
        }

        $languages = array();
        $availableLanguages = Opus_Language::getAllActive();

        foreach ($availableLanguages as $availableLanguage) {
            $trans = $availableLanguage->getPart1();
            if (true === empty($trans)) {
                $languages[$availableLanguage->getPart2T()] = $availableLanguage->getDisplayName();
            } else {
                $languages[$availableLanguage->getPart2T()] = $locale->getTranslation($trans, 'language', $trans);
            }
        }
        $registry->set('Available_Languages', $languages);
    }

}
