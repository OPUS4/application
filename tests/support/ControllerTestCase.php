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
 * @category    Application Unit Test
 * @author      Jens Schwidder <schwidder@zib.de>
 * @author      Thoralf Klein <thoralf.klein@zib.de>
 * @author      Michael Lang <lang@zib.de>
 * @copyright   Copyright (c) 2008-2020, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Base class for controller tests.
 *
 * @preserveGlobalState disabled
 */
class ControllerTestCase extends TestCase
{
    const MESSAGE_LEVEL_NOTICE = 'notice';

    const MESSAGE_LEVEL_FAILURE = 'failure';

    const CONFIG_VALUE_FALSE = ''; // Zend_Config übersetzt false in den Wert ''

    const CONFIG_VALUE_TRUE = '1'; // Zend_Config übersetzt true in den Wert '1'

    use \Opus\LoggingTrait;

    private $securityEnabled;

    private $testDocuments;

    private $testFiles;

    private $testFolders;

    private $tempFiles = [];

    private $logger = null;

    private $translatorBackup = null;

    private $workspacePath = null;

    /**
     * Method to initialize Zend_Application for each test.
     */
    public function setUpWithEnv($applicationEnv)
    {
        $this->applicationEnv = $applicationEnv;

        // added to ensure that application log messages are written to opus.log when running unit tests
        // if not set messages are written to opus-console.log
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.0';

        parent::setUp();
    }

    public function setUp()
    {
        $this->setUpWithEnv(APPLICATION_ENV);
    }

    /**
     * Clean up database instances.
     */
    public function tearDown()
    {
        $this->logoutUser();
        $this->resetSearch();

        $this->cleanupDatabase();
        $this->deleteTestFiles();
        $this->deleteTempFiles();
        $this->cleanupTestFolders();

        $this->additionalChecks();

        $this->logger = null;

        Application_Configuration::clearInstance(); // reset Application_Configuration

        parent::tearDown();
    }

    public function getApplication()
    {
        return new Zend_Application(
            $this->applicationEnv,
            ["config" => [
                APPLICATION_PATH . '/application/configs/application.ini',
                APPLICATION_PATH . '/application/configs/config.ini',
                APPLICATION_PATH . '/application/configs/console.ini',
                APPLICATION_PATH . '/tests/tests.ini',
                APPLICATION_PATH . '/tests/config.ini'
            ]]
        );
    }

    public function cleanupBefore()
    {
        // Reducing memory footprint by forcing garbage collection runs
        // WARNING: Did not work on CI-System (PHP 5.3.14, PHPnit 3.5.13)
        // gc_collect_cycles();

        $this->closeDatabaseConnection();

        // Resetting singletons or other kinds of persistent objects.
        Opus_Db_TableGateway::clearInstances();

        // Clean-up possible artifacts in $_SERVER of previous test.
        unset($_SERVER['REMOTE_ADDR']);

        parent::cleanupBefore();
    }

    public function cleanupDatabase()
    {
        if (! is_null(Zend_Db_Table::getDefaultAdapter())) {
            $this->deleteTestDocuments();

            // data integrity checks TODO should be made unnecessary
            $this->checkDoc146();
            $this->checkDoc1();
        }
    }

    public function additionalChecks()
    {
        /* ONLY FOR DEBUGGING
        $checker = new AssumptionChecker($this);
        $checker->checkYearFacetAssumption();
        */
    }

    /**
     * Used for debugging. Document 146 should never be modified in a test.
     *
     * In the past side effects because of bugs modified documents unintentionally.
     */
    protected function checkDoc146()
    {
        $doc = new Opus_Document(146);
        $modified = $doc->getServerDateModified();

        $this->assertEquals(2012, $modified->getYear());
    }

    protected function checkDoc1()
    {
        $doc = new Opus_Document(1);
        $modified = $doc->getServerDateModified();

        $this->assertEquals(2010, $modified->getYear());
    }

    protected function closeDatabaseConnection()
    {
        $adapter = Zend_Db_Table::getDefaultAdapter();
        if ($adapter) {
            $adapter->closeConnection();
        }
    }

    /**
     * Method to check response for "bad" strings.
     *
     * TODO mache $body optional (als zweiten Parameter) - hole aktuallen Body automatisch
     * TODO erlaube einfachen String als $badStrings Parameter
     */
    protected function checkForCustomBadStringsInHtml($body, array $badStrings)
    {
        $bodyLowerCase = strtolower($body);
        foreach ($badStrings as $badString) {
            $this->assertNotContains(
                strtolower($badString),
                $bodyLowerCase,
                "Response must not contain '$badString'"
            );
        }
    }

    /**
     * Method to check response for "bad" strings.
     *
     * TODO mache $body optional
     */
    protected function checkForBadStringsInHtml($body)
    {
        $badStrings = ["Exception", "Error", "Fehler", "Stacktrace", "badVerb"];
        $this->checkForCustomBadStringsInHtml($body, $badStrings);
    }

    /**
     * Login user.
     *
     * @param string $login
     * @param string $password
     *
     * TODO should be possible to be just 'guest' (see also enableSecurity)
     */
    public function loginUser($login, $password)
    {
        $adapter = new Opus_Security_AuthAdapter();
        $adapter->setCredentials($login, $password);

        $auth = Zend_Auth::getInstance();
        $auth->authenticate($adapter);
        $this->assertTrue($auth->hasIdentity());

        $config = Zend_Registry::get('Zend_Config');
        if (isset($config->security) && filter_var($config->security, FILTER_VALIDATE_BOOLEAN)) {
            Application_Security_AclProvider::init();

            // make sure ACLs are not cached in action helper TODO find better solution
            try {
                $accessControl = Zend_Controller_Action_HelperBroker::getExistingHelper('accessControl');
                $accessControl->setAcl(null);
            } catch (Zend_Controller_Action_Exception $excep) {
            }
        }
    }

    public function logoutUser()
    {
        $instance = Zend_Auth::getInstance();
        if (! is_null($instance)) {
            $instance->clearIdentity();
        }
        $realm = Opus_Security_Realm::getInstance();
        $realm->setUser(null);
        $realm->setIp(null);

        // make sure ACLs are not cached in action helper TODO find better solution
        try {
            $accessControl = Zend_Controller_Action_HelperBroker::getExistingHelper('accessControl');
            $accessControl->setAcl(null);
        } catch (Zend_Controller_Action_Exception $excep) {
        }
    }

    /**
     * Check if Solr-Config is given, otherwise skip the tests.
     *
     * TODO check behavior of getServiceConfiguration
     */
    protected function requireSolrConfig()
    {
        $config = Opus\Search\Config::getServiceConfiguration(Opus\Search\Service::SERVICE_TYPE_INDEX);

        if (is_null($config)) {
            $this->markTestSkipped('No solr-config given.  Skipping test.');
        }
    }

    /**
     * Modifies Solr configuration to un unknown core to simulate connection failure.
     * @throws Zend_Exception
     */
    protected function disableSolr()
    {
        $config = Zend_Registry::get('Zend_Config');
        // TODO old config path still needed?
        // $config->searchengine->index->app = 'solr/corethatdoesnotexist';
        $config->merge(new Zend_Config([
            'searchengine' => [
                'solr' => [
                    'default' => [
                        'service' => [
                            'endpoint' => [
                                'localhost' => [
                                    'path' => '/solr/corethatdoesnotexist'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]));
    }

    /**
     *
     * @param Zend_Controller_Response_Abstract $response
     * @param string $location
     */
    protected function assertResponseLocationHeader($response, $location)
    {
        $locationActual = null;
        foreach ($response->getHeaders() as $header) {
            if ($header['name'] === 'Location') {
                $locationActual = $header['value'];
            }
        }
        $this->assertNotNull($locationActual);
        $this->assertEquals($location, $locationActual);
    }

    public function enableSecurity()
    {
        $config = Zend_Registry::get('Zend_Config');
        $this->securityEnabled = $config->security;
        $config->security = self::CONFIG_VALUE_TRUE;
        Zend_Registry::set('Zend_Config', $config);
    }

    public function restoreSecuritySetting()
    {
        $config = Zend_Registry::get('Zend_Config');
        $config->security = $this->securityEnabled;
        Zend_Registry::set('Zend_Config', $config);
    }

    /**
     * Stellt die Übersetzungen auf Deutsch um.
     */
    public function useGerman()
    {
        $session = new Zend_Session_Namespace();
        $session->language = 'de';
        Zend_Registry::get('Zend_Translate')->setLocale('de');
        Application_Form_Element_Language::initLanguageList();
    }

    /**
     * Stellt die Übersetzungen auf English um.
     */
    public function useEnglish()
    {
        $session = new Zend_Session_Namespace();
        $session->language = 'en';
        Zend_Registry::get('Zend_Translate')->setLocale('en');
        Application_Form_Element_Language::initLanguageList();
    }

    /**
     * Prüft, ob das XHTML valide ist.
     * @param string $body
     *
     * TODO die DTD von W3C zu holen ist sehr langsam; sollte aus lokaler Datei geladen werden
     */
    public function validateXHTML($body = null)
    {
        if (is_null($body)) {
            $body = $this->getResponse()->getBody();
        }

        if (is_null($body) || strlen(trim($body)) === 0) {
            $this->fail('No XHTML Body to validate.');
            return;
        }

        libxml_clear_errors();
        libxml_use_internal_errors(true);

        $dom = new DOMDocument();

        // Setze HTTP Header damit W3C Request nicht verweigert
        $opts = ['http' => [
            'user_agent' => 'PHP libxml agent',
        ]];


        $context = stream_context_create($opts);
        libxml_set_streams_context($context);

        $mapping = [
             'http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd' => 'xhtml1-strict.dtd'
        ];

        /* TODO erst ab PHP >= 5.4.0 unterstützt; Alternative Lösung?
         * - momentan verwenden wir xmlcatalog für lokales Caching
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

        $ignored = [
            'No declaration for attribute class of element html',
            'No declaration for attribute placeholder of element input',
            'No declaration for attribute target of element a'
        ];

        $filteredErrors = [];

        foreach ($errors as $error) {
            if (! in_array(trim($error->message), $ignored)) {
                $filteredErrors[] = $error;
            }
        }

        $errors = $filteredErrors;

        // Array mit Fehlern ausgeben
        if (count($errors) !== 0) {
            $output = Zend_Debug::dump($errors, 'XHTML Fehler', false);
        } else {
            $output = '';
        }

        $this->assertEquals(
            0,
            count($errors),
            'XHTML Schemaverletzungen gefunden (' . count($errors) . ')' . PHP_EOL . $output
        );

        libxml_use_internal_errors(false);
        libxml_clear_errors();
    }

    /**
     * Prüft, ob ein Kommando auf den System existiert (Mac OS-X, Linux)
     * @param string $command Name des Kommandos
     * @return boolean TRUE - wenn Kommando existiert
     */
    public function isCommandAvailable($command)
    {
        $this->getLogger()->debug("Checking command $command");
        $this->getLogger()->debug('User: ' . get_current_user());
        $result = shell_exec("which $command");
        return (empty($result) ? false : true);
    }

    /**
     * Prüft, ob Kommando existiert und markiert Test als Fail oder Skipped.
     *
     * @param string $command Name des Kommandos
     */
    public function verifyCommandAvailable($command)
    {
        if (! $this->isCommandAvailable($command)) {
            if ($this->isFailTestOnMissingCommand()) {
                $this->fail("Command '$command' not installed.");
            } else {
                $this->markTestSkipped("Skipped because '$command' is not installed.");
            }
        }
    }

    /**
     * Liefert true wenn Tests mit fehlenden Kommandos mit Fail markiert werden sollten.
     * @return boolean
     */
    public function isFailTestOnMissingCommand()
    {
        $config = Zend_Registry::get('Zend_Config');
        return (isset($config->tests->failTestOnMissingCommand) &&
                filter_var($config->tests->failTestOnMissingCommand, FILTER_VALIDATE_BOOLEAN));
    }

    /**
     * Funktion zum Prüfen von FlashMessenger Nachrichten.
     *
     * Fuer gruene Nachrichten Level muss self::MESSAGE_LEVEL_NOTICE verwendet werden.
     *
     * @param $message Übersetzungsschlüssel bzw. Nachricht
     * @param string $level 'notice' oder 'failure'
     */
    public function verifyFlashMessage($message, $level = self::MESSAGE_LEVEL_FAILURE)
    {
        $flashMessenger = Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger');
        $flashMessages = $flashMessenger->getCurrentMessages();

        $this->assertEquals(1, count($flashMessages), 'Expected one flash message in queue.');
        $flashMessage = $flashMessages[0];

        $this->assertEquals($message, $flashMessage['message']);
        $this->assertEquals($level, $flashMessage['level']);
    }

    /**
     * Funktion zum Prüfen von FlashMessenger Nachrichten.
     *
     * Fuer gruene Nachrichten Level muss self::MESSAGE_LEVEL_NOTICE verwendet werden.
     *
     * @param $message Übersetzungsschlüssel bzw. Nachricht
     * @param string $level 'notice' oder 'failure'
     */
    public function verifyNotFlashMessageContains($message, $level = self::MESSAGE_LEVEL_FAILURE)
    {
        $flashMessenger = Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger');
        $flashMessages = $flashMessenger->getCurrentMessages();

        $this->assertEquals(1, count($flashMessages), 'Expected one flash message in queue.');
        $flashMessage = $flashMessages[0];

        $this->assertNotContains($message, $flashMessage['message']);
        $this->assertEquals($level, $flashMessage['level']);
    }

    /**
     * Liefert den Inhalt des Response Location Header.
     * @return string|null
     */
    public function getLocation()
    {
        $headers = $this->getResponse()->getHeaders();
        foreach ($headers as $header) {
            if (isset($header['name']) && $header['name'] == 'Location') {
                return isset($header['value']) ? $header['value'] : null;
            }
        }
        return null;
    }

    /**
     * Prueft, ob eine Seite in navigationModules.xml definiert wurde.
     *
     *
     * @param null $location
     */
    public function verifyBreadcrumbDefined($location = null)
    {
        if (is_null($location)) {
            $location = $this->getLocation(); // liefert null wenn es kein redirect war
            if (is_null($location)) {
                // ansonsten Request-URI verwenden
                $location = $this->getRequest()->getRequestUri();
            }
        }

        $view = Zend_Registry::get('Opus_View');

        $path = explode('/', $location);

        array_shift($path);
        $module = array_shift($path);
        $controller = array_shift($path);
        $action = array_shift($path);

        $navigation = $view->navigation()->getContainer();

        $pages = $navigation->findAllByModule($module);

        $breadcrumbDefined = false;

        foreach ($pages as $page) {
            if ($page->getController() == $controller && $page->getAction() == $action) {
                if (! $breadcrumbDefined) {
                    $breadcrumbDefined = true;

                    $translate = Zend_Registry::get('Zend_Translate');

                    $label = $page->getLabel();

                    $this->assertTrue(
                        $translate->isTranslated($label),
                        "Label '$label' für Seite '$location' nicht übersetzt."
                    );
                } else {
                    $this->fail("Seite '$location' mehr als einmal in navigationModules.xml definiert.");
                }
            };
        }

        $this->assertTrue($breadcrumbDefined, "Seite '$location' nicht in navigationModules.xml definiert.");
    }

    /**
     * TODO add configuration parameter to enabled/disable (default = false)
     */
    public function dumpBody()
    {
        Zend_Debug::dump($this->getResponse()->getBody());
    }

    /**
     * Removes a test document from the database.
     *
     * @param $value Opus_Document|int
     * @throws Opus_Model_Exception
     */
    public function removeDocument($value)
    {
        if (is_null($value)) {
            return;
        }

        $doc = $value;
        if (! ($value instanceof Opus_Document)) {
            try {
                $doc = new Opus_Document($value);
            } catch (Opus_Model_NotFoundException $e) {
                // could not find document -> no cleanup operation required: exit silently
                return;
            }
        }

        $docId = $doc->getId();
        if (is_null($docId)) {
            // Dokument wurde (noch) nicht in DB persistiert
            return;
        }

        try {
            new Opus_Document($docId);
            $doc->deletePermanent();
        } catch (Opus_Model_NotFoundException $omnfe) {
            // Model nicht gefunden -> alles gut (hoffentlich)
            $this->getLogger()->debug("Test document {$docId} was deleted successfully by test.");
            return;
        } catch (Exception $ex) {
            $this->getLogger()->err('unexpected exception while cleaning document ' . $docId . ': ' . $ex);
            throw $ex;
        }

        // make sure test documents have been deleted
        try {
            new Opus_Document($docId);
            $this->getLogger()->debug("Test document {$docId} was not deleted.");
        } catch (Opus_Model_NotFoundException $omnfe) {
            // ignore - document was deleted successfully
            $this->getLogger()->debug("Test document {$docId} was deleted successfully.");
        }
    }

    protected function deleteTestDocuments()
    {
        if (is_null($this->testDocuments)) {
            return;
        }

        foreach ($this->testDocuments as $key => $doc) {
            $this->removeDocument($doc);
        }

        $this->testDocuments = null;
    }

    protected function getDocument($docId)
    {
        return new Opus_Document($docId);
    }

    /**
     * Erzeugt ein Testdokument, das nach der Testausführung automatisch aufgeräumt wird.
     *
     * @return Opus_Document
     * @throws Opus_Model_Exception
     */
    protected function createTestDocument()
    {
        $doc = new Opus_Document();
        $this->addTestDocument($doc);
        return $doc;
    }

    /**
     * Adds a document to the cleanup queue.
     */
    protected function addTestDocument($document)
    {
        if (is_null($this->testDocuments)) {
            $this->testDocuments = [];
        }
        array_push($this->testDocuments, $document);
    }

    protected function getTempFile($prefix = 'Opus4TestTemp')
    {
        $filePath = tempnam(sys_get_temp_dir(), $prefix);
        $this->tempFiles[] = $filePath;
        return $filePath;
    }

    protected function deleteTempFiles()
    {
        if (is_array($this->tempFiles)) {
            foreach ($this->tempFiles as $path) {
                if (file_exists($path)) {
                    unlink($path);
                }
            }
        }
    }

    /**
     * @param string $filename
     * @param string $filepath
     * @return Opus_File
     * @throws Opus_Model_Exception
     * @throws Zend_Exception
     */
    protected function createOpusTestFile($filename, $filepath = null)
    {
        if (is_null($this->testFiles)) {
            $this->testFiles = [];
        }

        $workspacePath = $this->getWorkspacePath();

        if (is_null($filepath)) {
            $path = $this->createTestFolder();
            $filepath = $path . DIRECTORY_SEPARATOR . $filename;
            touch($filepath);
        }

        $this->assertTrue(is_readable($filepath));
        $file = new Opus_File();
        $file->setPathName(basename($filepath));
        $file->setTempFile($filepath);
        if (array_key_exists($filename, $this->testFiles)) {
            throw Exception('filenames should be unique');
        }
        $this->testFiles[$filename] = $filepath;
        return $file;
    }

    protected function deleteTestFiles()
    {
        if (! is_null($this->testFiles)) {
            foreach ($this->testFiles as $key => $filepath) {
                try {
                    if (is_writeable($filepath)) {
                        unlink($filepath);
                    }
                } catch (Exception $e) {
                }
            }
        }
    }

    protected function createTestFile($filename, $content = null, $path = null)
    {
        if (is_null($path)) {
            $filePath = $this->createTestFolder();
        } else {
            $filePath = $path;
        }

        $filePath .= DIRECTORY_SEPARATOR . $filename;

        if (! is_null($content)) {
            file_put_contents($filePath, $content);
        } else {
            touch($filePath);
        }

        if (is_null($this->testFiles)) {
            $this->testFiles = [];
        }
        $this->testFiles[$filename] = $filePath;

        return $filePath;
    }

    protected function getWorkspacePath()
    {
        if (is_null($this->workspacePath)) {
            $config = $this->getConfig();
            if (! isset($config->workspacePath)) {
                throw new Exception('config key \'workspacePath\' not defined in config file');
            }
            $this->workspacePath = $config->workspacePath;
        }

        return $this->workspacePath;
    }

    protected function setWorkspacePath($path)
    {
        $this->workspacePath = $path;
    }

    protected function getConfig()
    {
        return Zend_Registry::get('Zend_Config');
    }

    protected function createTestFolder()
    {
        $workspacePath = $this->getWorkspacePath();
        $path = $workspacePath . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . uniqid('test');
        if (! is_array($this->testFolders)) {
            $this->testFolders = [];
        }
        $this->testFolders[] = $path;
        mkdir($path, 0777, true);
        return $path;
    }

    protected function cleanupTestFolders()
    {
        if (! is_array($this->testFolders)) {
            return;
        }

        foreach ($this->testFolders as $path) {
            if (is_dir($path)) {
                $this->deleteFolder($path);
            }
        }
    }

    /**
     * @param $src
     * @param $dest
     * @throws Exception
     *
     * TODO recursive copying of subdirectories?
     */
    protected function copyFiles($src, $dest)
    {
        if (! is_dir($src) || ! is_dir($dest)) {
            throw new Exception('Parameters need to be folders.');
        }

        $dest = rtrim($dest, '/') . '/';

        $iterator = new RecursiveDirectoryIterator($src, FilesystemIterator::SKIP_DOTS);
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                copy($file->getPathname(), $dest . $file->getFilename());
            }
        }
    }

    /**
     * Deletes entire test folders.
     *
     * Normally does not delete files outside of configured workspace folder.
     */
    protected function deleteFolder($path, $deleteOutsideWorkspace = false)
    {
        if (is_dir($path)) {
            $workspacePath = $this->getWorkspacePath();

            if (strpos($path, $workspacePath) === 0 || $deleteOutsideWorkspace) {
                $iterator = new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS);
                foreach ($iterator as $file) {
                    if ($file->isDir()) {
                        $this->deleteFolder($file->getPathname());
                    } else {
                        $pathname = $file->getPathname();
                        if (strpos($pathname, $workspacePath) === 0 || $deleteOutsideWorkspace) {
                            unlink($file->getPathname());
                        }
                    }
                }
                rmdir($path);
            }
        }
    }

    public function assertSecurityConfigured()
    {
        $this->assertEquals('1', Zend_Registry::get('Zend_Config')->security);
        $this->assertTrue(
            Zend_Registry::isRegistered('Opus_Acl'),
            'Expected registry key Opus_Acl to be set'
        );
        $acl = Zend_Registry::get('Opus_Acl');
        $this->assertTrue($acl instanceof Zend_Acl, 'Expected instance of Zend_Acl');
    }

    public function resetSearch()
    {
        \Opus\Search\Config::dropCached();
        \Opus\Search\Service::dropCached();
    }

    /**
     * Sets the hostname for a test.
     * @param $host string Hostname for tests
     * @throws Zend_Exception
     */
    public function setHostname($host)
    {
        $view = Zend_Registry::get('Opus_View');
        $view->getHelper('ServerUrl')->setHost($host);
    }

    /**
     * Sets base URL for tests.
     *
     * A lot of tests fail if the base URL is set because they verify URLs from the server root, like '/auth' instead
     * of 'opus4/auth' (base URL = 'opus4').
     *
     * @param $baseUrl string Base URL for tests
     * @throws Zend_Controller_Exception
     */
    public function setBaseUrl($baseUrl)
    {
        Zend_Controller_Front::getInstance()->setBaseUrl($baseUrl);
    }

    /**
     * Disables translation until the next bootstrapping.
     *
     * This can be used to check for translation keys instead of translated strings for instance when testing forms.
     * Otherwise you have to specify the language for the test first, so you get the expected translation.
     *
     * Stores the original translation object only the first time the function is called.
     */
    public function disableTranslation()
    {
        if (is_null($this->translatorBackup)) {
            $this->translatorBackup = Zend_Registry::get('Zend_Translate');
        }

        Zend_Registry::set('Zend_Translate', new Application_Translate([
            'adapter' => 'array',
            'content' => [],
            'locale' => 'auto'
        ]));
    }

    /**
     * Resets translations with original (bootstrap) translation object.
     *
     * This function restores translation if disableTranslation has been called before.
     */
    public function enableTranslation()
    {
        if (! is_null($this->translatorBackup)) {
            Zend_Registry::set('Zend_Translate', $this->translatorBackup);
        }
    }

    /**
     * Allow the given user (identified by his or her name) to access the given module.
     * Returns true if access permission was added; otherwise false.
     *
     * @param $moduleName module name
     * @param $userName user name
     * @return bool
     * @throws \Opus\Model\Exception
     */
    protected function addModuleAccess($moduleName, $userName)
    {
        $r = Opus_UserRole::fetchByName($userName);
        $modules = $r->listAccessModules();
        if (! in_array($moduleName, $modules)) {
            $r->appendAccessModule($moduleName);
            $r->store();
            return true;
        }
        return false;
    }

    /**
     * Disallow the given user (identified by his or her name) to access the given module.
     * Returns true if access permission was removed; otherwise false.
     *
     * @param $moduleName module name
     * @param $userName user name
     * @return bool
     * @throws \Opus\Model\Exception
     */
    protected function removeModuleAccess($moduleName, $userName)
    {
        $r = Opus_UserRole::fetchByName($userName);
        $modules = $r->listAccessModules();
        if (in_array($moduleName, $modules)) {
            $r->removeAccessModule($moduleName);
            $r->store();
            return true;
        }
        return false;
    }
}
