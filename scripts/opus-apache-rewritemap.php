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
 * @category   Application
 * @package    Opus_Deliver
 * @author     Pascal-Nicolas Becker <becker@zib.de>
 * @copyright  Copyright (c) 2009, OPUS 4 development team
 * @license    http://www.gnu.org/licenses/gpl.html General Public License
 * @version    $Id$
 */

// Configure include path.
set_include_path('.' . PATH_SEPARATOR
            . PATH_SEPARATOR . dirname(__FILE__)
            . PATH_SEPARATOR . dirname(dirname(__FILE__)) . '/library'
            . PATH_SEPARATOR . get_include_path());

defined('APPLICATION_PATH')
        || define('APPLICATION_PATH', realpath(dirname(dirname(__FILE__))));

define('APPLICATION_ENV', 'testing');

require_once 'Zend/Loader/Autoloader.php';
$autoloader = Zend_Loader_Autoloader::getInstance();
$autoloader->suppressNotFoundWarnings(false);
$autoloader->setFallbackAutoloader(true);

// Zend_Loader is'nt available yet. We have to do a require_once
// in order to find the bootstrap class.
//require_once 'Opus/Bootstrap/Base.php';

/**
 * Bootstraps and runs the application.
 *
 * @category    Application
 */
class OpusApacheRewritemap { // extends Opus_Bootstrap_Base {

    /**
     * Holds command line arguments passed to the script.
     *
     * @var array
     */
    private $_arguments = array();

    /**
     * Initialise with command line arguments.
     *
     * @param array $arguments Command line arguments passed to the script.
     */
    public function __construct(array $arguments) {
        $this->_arguments = $arguments;
    }


    /**
     * Setup configuration, database and translation.
     *
     * @return void
     */
    protected function _setupBackend() {
        $this->_setupLogging();
        $this->_setupDatabase();
        # $this->_setupTranslation();
        # $this->_setupLanguageList();
    }


    /**
     * FIXME This is frontend setup needed in backend to instanciate models.
     *
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
//    protected function _setupTranslation()
//    {
//        $sessiondata = new Zend_Session_Namespace();
//        $options = array(
//            'clear' => false,
//            'scan' => Zend_Translate::LOCALE_FILENAME,
//            'ignore' => '.'
//            );
//        $translate = new Zend_Translate(
//            Zend_Translate::AN_TMX,
//            $this->_applicationRootDirectory . '/modules/',
//            'auto',
//            $options
//            );
//
//        if (empty($sessiondata->language) === false) {
//            // Example for logging something
//            $logger = Zend_Registry::get('Zend_Log');
//            $logger->info('Switching to language "' . $sessiondata->language . '".');
//            $translate->setLocale($sessiondata->language);
//        } else {
//            $sessiondata->language = $translate->getLocale();
//        }
//
//        $registry = Zend_Registry::getInstance();
//        $registry->set('Zend_Translate', $translate);
//    }

    /**
     * FIXME This is frontend setup needed in backend to instanciate models.
     *
     * Setup language list.
     *
     * @return void
     */
//    protected function _setupLanguageList() {
//        $registry = Zend_Registry::getInstance();
//
//        $sessiondata = new Zend_Session_Namespace();
//        if (false === empty($sessiondata->language)) {
//            $locale = new Zend_Locale($sessiondata->language);
//        } else {
//            $locale = $registry->get('Zend_Translate')->getLocale();
//        }
//
//        $languages = array();
//        $availableLanguages = Opus_Language::getAllActive();
//
//        foreach ($availableLanguages as $availableLanguage) {
//            $trans = $availableLanguage->getPart1();
//            if (true === empty($trans)) {
//                $languages[$availableLanguage->getId()] = $availableLanguage->getDisplayName();
//            } else {
//                $languages[$availableLanguage->getId()] = $locale->getLanguageTranslation($trans);
//            }
//        }
//        $registry->set('Available_Languages', $languages);
//    }


    /**
     * Starts an Opus console.
     *
     * @return void
     */
    public function run() {
        $log = Zend_Registry::get('Zend_Log');
        $config = Zend_Registry::get('Zend_Config');

        // set targetprefix
        if (empty($config->deliver->target->prefix) === true) {
            $log->warn('No target prefix defined in configuration. Using "/files"!');
            $targetPrefix = '/files';
        } else {
            $targetPrefix = $config->deliver->target->prefix;
        }

        // check input
        if (count($this->_arguments) < 2) {
            $line = '';
        } else {
            $line = $this->_arguments[1];
        }
        if (preg_match('/\t.*\t/', $line) === 0) {
            $log->err('Internal fatal error! Input from Apache was not as predicted, unparsable by RewriteMap!');
            $log->err('Apache Input: \'' . $line . '\'');
            return $targetPrefix ."/error/send500.php";
        }

        // instantiate cookie
        $cookie = null;
        // split input
        list($path, $remoteAddress, $cookie) = preg_split('/\t/', $line, 3);
        // remove leading 'COOKIE=', set to prevent eating of second tab by the shell
        preg_replace('/^COOKIES=/', '', $cookie, 1);

        // issue rewriting
        $rwmap = new Rewritemap_Apache($targetPrefix, $log);
        echo $rwmap->rewriteRequest($path, $remoteAddress, $cookie) . "\n";
    }

}

require_once 'Zend/Application.php';

// environment initializiation

$application = new Zend_Application(
    APPLICATION_ENV,
    array(
        "config"=>array(
            APPLICATION_PATH . '/application/configs/application.ini',
            APPLICATION_PATH . '/config/config.ini'
        )
    )
);

$application->bootstrap();

// Bootstrap Zend
$rwmap = new OpusApacheRewritemap($argv);
//echo dirname(dirname(__FILE__));
$rwmap->run();

