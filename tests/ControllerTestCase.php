<?php
/*
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
 * @category    Application Unit Test
 * @author      Jens Schwidder <schwidder@zib.de>
 * @author      Thoralf Klein <thoralf.klein@zib.de>
 * @copyright   Copyright (c) 2008-2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Base class for controller tests.
 */
class ControllerTestCase extends Zend_Test_PHPUnit_ControllerTestCase {

    private $securityEnabled;

    /**
     * Method to initialize Zend_Application for each test.
     */
    public function setUpWithEnv($applicationEnv) {
        // Reducing memory footprint by forcing garbage collection runs
        // WARNING: Did not work on CI-System (PHP 5.3.14, PHPnit 3.5.13)
        // gc_collect_cycles();

        $this->closeLogfile();

        $this->closeDatabaseConnection();

        // Resetting singletons or other kinds of persistent objects.
        Opus_Db_TableGateway::clearInstances();

        // FIXME Does it help with the mystery bug?
        Zend_Registry::_unsetInstance();

        // Reset autoloader to fix huge memory/cpu-time leak
        Zend_Loader_Autoloader::resetInstance();
        $autoloader = Zend_Loader_Autoloader::getInstance();
        $autoloader->suppressNotFoundWarnings(false);
        $autoloader->setFallbackAutoloader(true);

        // Clean-up possible artifacts in $_SERVER of previous test.
        unset($_SERVER['REMOTE_ADDR']);

        $this->bootstrap = new Zend_Application(
            $applicationEnv,
            array(
                "config" => array(
                    APPLICATION_PATH . '/application/configs/application.ini',
                    APPLICATION_PATH . '/tests/tests.ini',
                    APPLICATION_PATH . '/tests/config.ini'
                )
            )
        );

        // added to ensure that application log messages are written to opus.log when running unit tests
        // if not set messages are written to opus-console.log
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.0';

        parent::setUp();
    }

    public function setUp() {
        $this->setUpWithEnv(APPLICATION_ENV);
    }

    /**
     * Clean up database instances.
     */
    protected function tearDown() {
        $this->logoutUser();

        parent::tearDown();
    }

    /**
     * Close logfile to prevent plenty of open logfiles.
     */
    protected function closeLogfile() {
        if (!Zend_Registry::isRegistered('Zend_Log')) {
            return;
        }

        $log = Zend_Registry::get('Zend_Log');
        if (isset($log)) {
            $log->__destruct();
            Zend_Registry::set('Zend_Log', null);
        }
    }

    protected function closeDatabaseConnection() {
        $adapter = Zend_Db_Table::getDefaultAdapter();
        if ($adapter) {
            $adapter->closeConnection();
        }
    }

    /**
     * Method to check response for "bad" strings.
     */
    protected function checkForCustomBadStringsInHtml($body, array $badStrings) {
        $bodyLowerCase = strtolower($body);
        foreach ($badStrings AS $badString)
            $this->assertNotContains(
                strtolower($badString),
                $bodyLowerCase,
                "Response must not contain '$badString'");
    }

    /**
     * Method to check response for "bad" strings.
     */
    protected function checkForBadStringsInHtml($body) {
        $badStrings = array("Exception", "Error", "Fehler", "Stacktrace", "badVerb");
        $this->checkForCustomBadStringsInHtml($body, $badStrings);
    }

    /**
     * Login user.
     * 
     * @param string $login
     * @param string $password
     */
    public function loginUser($login, $password) {
        $adapter = new Opus_Security_AuthAdapter();
        $adapter->setCredentials($login, $password);
        $auth = Zend_Auth::getInstance();
        $result = $auth->authenticate($adapter);
        $this->assertTrue($auth->hasIdentity());
    }

    public function logoutUser() {
        $instance = Zend_Auth::getInstance();
        if (!is_null($instance)) {
            $instance->clearIdentity();
        }
    }

    /**
     * Check if Solr-Config is given, otherwise skip the tests.
     */
    protected function requireSolrConfig() {
        $config = Zend_Registry::get('Zend_Config');
        if (!isset($config->searchengine->index->host) ||
            !isset($config->searchengine->index->port) ||
            !isset($config->searchengine->index->app)) {
            $this->markTestSkipped('No solr-config given.  Skipping test.');
        }
    }

    /**
     *
     * @param Zend_Controller_Response_Abstract $response
     * @param string $location
     */
    protected function assertResponseLocationHeader($response, $location) {
        $locationActual = null;
        foreach ($response->getHeaders() as $header) {
            if ($header['name'] === 'Location') {
                $locationActual = $header['value'];
            }
        }
        $this->assertNotNull($locationActual);
        $this->assertEquals($location, $locationActual);
    }
    
    public function enableSecurity() {
        $config = Zend_Registry::get('Zend_Config');
        $this->securityEnabled = $config->security;
        $config->security = '1';
        Zend_Registry::set('Zend_Config', $config);
    }
    
    public function restoreSecuritySetting() {
        $config = Zend_Registry::get('Zend_Config');
        $config->security = $this->securityEnabled;
        Zend_Registry::set('Zend_Config', $config);
    }

    /**
     * Setzt Session Attribut für Deutsch.
     * 
     * Dies beeinflusst momentan nicht die verwendete Sprache bei Übersetzungen, sondern wirkt sich nur auf Klassen aus,
     * die dieses Session-Attribut verwenden.
     */
    public function setUpGerman() {
        $session = new Zend_Session_Namespace();
        $session->language = 'de';
    }

    /**
     * Setzt Session-Attribut für Englisch.
     * 
     * Dies beeinflusst momentan nicht die verwendete Sprache bei Übersetzungen, sondern wirkt sich nur auf Klassen aus,
     * die dieses Session-Attribut verwenden.
     */
    public function setUpEnglish() {
        $session = new Zend_Session_Namespace();
        $session->language = 'en';
    }
    
    /**
     * Prüft, ob das XHTML valide ist.
     * @param string $body
     * 
     * TODO die DTD von W3C zu holen ist sehr langsam; sollte aus lokaler Datei geladen werden
     */
    public function validateXHTML($body) {        
        libxml_clear_errors();
        libxml_use_internal_errors(true);
        
        $dom = new DOMDocument();
        
        // Setze HTTP Header damit W3C Request nicht verweigert
        $opts = array(
            'http' => array(
                'user_agent' => 'PHP libxml agent',
            )
        );

        
        $context = stream_context_create($opts);
        libxml_set_streams_context($context);

        $mapping = array(
             'http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd' => 'xhtml1-strict.dtd'
        );        
        
        /* TODO erst ab PHP >= 5.4.0 unterstützt; Alternative Lösung?
        libxml_set_external_entity_loader(
            function ($public, $system, $context) use ($mapping) {
                if (is_file($system)) {
                    return $system;
                }

                if (isset($mapping[$system])) {
                    return APPICATION_PATH . DIRECTORY_SEPARATOR . 'schema' . DIRECTORY_SEPARATOR . $mapping[$system];
                }

                $message = sprintf(
                    "Failed to load external entity: Public: %s; System: %s; Context: %s",
                    var_export($public, 1), var_export($system, 1),
                    strtr(var_export($context, 1), array(" (\n  " => '(', "\n " => '', "\n" => ''))
                );

                throw new RuntimeException($message);
            }
        );*/

        $dom->validateOnParse = true;
        $dom->loadXML($body);
        
        $errors = libxml_get_errors();
        
        $ignored = array(
            'No declaration for attribute class of element html',
            'No declaration for attribute placeholder of element input'
        );
        
        $filteredErrors = array();
        
        foreach ($errors as $error) {
            if (!in_array(trim($error->message), $ignored)) {
                $filteredErrors[] = $error;
            }
        }
        
        $errors = $filteredErrors;
        
        // Array mit Fehlern ausgeben
        if (count($errors) !== 0) {
            $output = Zend_Debug::dump($errors, 'XHTML Fehler', false);
        }
        else {
            $output = '';
        }
        
        $this->assertEquals(0, count($errors), 'XHTML Schemaverletzungen gefunden (' . count($errors) . ')' . PHP_EOL 
                . $output);
        
        libxml_use_internal_errors(false);
        libxml_clear_errors();
    }
        
}