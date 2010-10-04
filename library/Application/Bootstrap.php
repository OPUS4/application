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
// require_once 'Opus/Bootstrap/Base.php';

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
     * Setup translation cache.
     *
     * @return void
     */
    protected function _initTranslationCache() {
        $this->bootstrap('Backend');

        $config = $this->getResource('Configuration');

        $cache = null;
        $frontendOptions = array(
            'lifetime' => 600, // in seconds
            'automatic_serialization' => true,
        );

        $backendOptions = array(
            // Directory where to put the cache files. Must be writeable for application server
            'cache_dir' => $config->workspacePath . '/cache/'
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
    protected function _initOpusFrontController() {
        $this->bootstrap(array('LanguageList','frontController'));

        $frontController = $this->getResource('frontController'); // Zend_Controller_Front::getInstance();

        /*
         * Add a custom front controller plugin for setting up an appropriate
         * include path to the form classes of modules.
         */
        $moduleprepare = new Controller_Plugin_ModulePrepare(APPLICATION_PATH . '/modules');
        $moduleprepare->appendClassPath('models')
        ->appendClassPath('forms');
        $frontController->registerPlugin($moduleprepare);

        // Checks the current requeste module's directory for an initFile and runs it before controller is loaded.
        $moduleInit = new Controller_Plugin_ModuleInit();
        $frontController->registerPlugin($moduleInit);

        // Add security realm initialization
        $realmSetupPlugin = new Controller_Plugin_SecurityRealm();
        $frontController->registerPlugin($realmSetupPlugin);

        /*
         * Add a front controller plugin for oai-requests because of
         * DNB-validation
         */
        $oaiPlugin = new Controller_Plugin_DnbXmlPostprocess();
        $frontController->registerPlugin($oaiPlugin);
    }

    /**
     * Configure view with UTF-8 options and ViewRenderer action helper.
     * The Zend_Layout component also gets initialized here.
     *
     * @return void
     *
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
        $libRealPath = realpath(APPLICATION_PATH . '/library');

        $view->addHelperPath($libRealPath . '/View/Helper', 'View_Helper');

        // Set path to shared view partials
        $view->addScriptPath($libRealPath . '/View/Partials');

        $viewRenderer = new Zend_Controller_Action_Helper_ViewRenderer($view);

        Zend_Controller_Action_HelperBroker::addHelper($viewRenderer);

        return $view;
    }

    /**
     * Setup Zend_Cache for caching application data and register under 'Zend_Cache_Page'.
     *
     * @return void
     *
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
     * @return void
     *
     */
    protected function _initTranslation()  {
        $this->bootstrap(array('Logging','TranslationCache'));

        $logger = $this->getResource('Logging');

        Zend_Session::setOptions(array(
            'cookie_path' => Zend_Controller_Front::getInstance()->getBaseUrl()
        ));        

        $options = array(
            'clear' => false,
            'scan' => Zend_Translate::LOCALE_FILENAME,
            'ignore' => '.',
            'disableNotices' => true
            );
        $translate = new Zend_Translate(
            Zend_Translate::AN_TMX,
            APPLICATION_PATH . '/modules/default/language/default.tmx',
            'auto',
            $options
            );

        if (file_exists(APPLICATION_PATH . '/modules/default/language/') === true) {
            $handle = opendir(APPLICATION_PATH . '/modules/default/language/');
            if ($handle) {
                while (false !== ($file = readdir($handle))) {
                    // ignore directories
                    if (is_dir($file) === true) continue;
                    // ignore files with leading dot and files without extension tmx
                    if (preg_match('/^[^.].*\.tmx$/', $file) === 0) continue;
                    $translate->addTranslation(APPLICATION_PATH . '/modules/default/language/' . $file, 'auto', $options);
                }
            }
        }

        $sessiondata = new Zend_Session_Namespace();
        if (empty($sessiondata->language)) {
            $language = 'en';
            $logger->debug("language need to be set");
            $supportedLanguages = array();
            $config = $this->getResource('configuration');            
            if (isset($config->supportedLanguages)) {
                $supportedLanguages = explode(",", $config->supportedLanguages);
                $logger->debug(count($supportedLanguages) . " supported languages: " . $config->supportedLanguages);
            }
            $currentLocale = new Zend_Locale();
            $currentLanguage = $currentLocale->getLanguage();
            $logger->debug("current locale: " . $currentLocale);
            foreach ($supportedLanguages as $supportedLanguage) {
                if ($currentLanguage === $supportedLanguage) {
                    $language = $currentLanguage;
                    break;
                }
            }
            $sessiondata->language = $language;
        }
        $logger->info('Set language to "' . $sessiondata->language . '".');
        $translate->setLocale($sessiondata->language);
        Zend_Registry::set('Zend_Translate', $translate);
        $this->translate = $translate;
    }

    /**
     * Setup language list.
     *
     * @return void
     */
    protected function _initLanguageList() {
        $this->bootstrap('Translation');

        $logger = $this->getResource('Logging');
        Zend_Session::setOptions(array(
            'cookie_path' => Zend_Controller_Front::getInstance()->getBaseUrl()
        ));
        $sessiondata = new Zend_Session_Namespace();

        $languages = array();
        try {
            $availableLanguages = Opus_Language::getAllActive();

            foreach ($availableLanguages as $availableLanguage) {
                $trans = $availableLanguage->getPart1();
                if (true === empty($trans)) {
                    $languages[$availableLanguage->getPart2T()] = $availableLanguage->getPart2T();
                } else {
                    try {
                        $locale = new Zend_Locale($sessiondata->language);
                        $languages[$availableLanguage->getPart2T()] = $locale->getTranslation($trans, 'language', $locale);
                    } catch (Zend_Locale_Exception $zle) {
                        $logger->warn('Caught Zend_Locale_Exception while loading ' . $trans . ': ' . $zle->getMessage());
                        $logger->warn('Ignoring language with ID ' . $availableLanguage->getId());
                    }
                }
            }
            Zend_Registry::set('Available_Languages', $languages);
        }
        catch (Exception $ex) {
            $logger->err('Error getting languages from database.');
            $logger->err($ex);
            throw new Exception('Opus: Error accessing database.');
        }
    }

    /**
     * Initializes general navigation as configured in navigationModules.xml'
     *
     * @return void
     */
    protected function _initOpusNavigation() {
        $this->bootstrap('Logging', 'View');

        $log = $this->getResource('Logging');

        $log->debug('Initializing Zend_Navigation');

        $config = $this->getResource('configuration');

        $log->debug("config path: " . $config->configPath);
        if (is_null($config)) {
            $log->debug("config is null");
        }
        else {
            $log->debug("config is not null");
        }

        $navigationConfigFile = $config->configPath . '/navigationModules.xml';

        $navConfig = new Zend_Config_Xml($navigationConfigFile, 'nav');

        $log->debug('Navigation config file is: ' . $navigationConfigFile);

        $container = new Zend_Navigation($navConfig);

        $view = $this->getResource('View');

        $view->navigation($container);

        // TODO Find better way without Zend_Registry
 //       Zend_Registry::set('Opus_Navigation', $container);

        $log->debug('Zend_Navigation initialization completed');

        return $container;
    }

    /**
     * Initializes navigation container for main menu.
     * @return Zend_Navigation
     */
    protected function _initMainMenu() {
        $this->bootstrap('Logging', 'View');

        $config = $this->getResource('configuration');

        $navigationConfigFile = $config->configPath . '/navigation.xml';

        $navConfig = new Zend_Config_Xml($navigationConfigFile, 'nav');

        $container = new Zend_Navigation($navConfig);

        $view = $this->getResource('View');

        $view->navigationMainMenu = $container;

        // TODO Find better way without Zend_Registry
        Zend_Registry::set('Opus_Navigation', $container);

        return $container;
    }

}