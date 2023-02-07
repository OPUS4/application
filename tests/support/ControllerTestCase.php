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
use Opus\Common\Document;
use Opus\Common\DocumentFinderInterface;
use Opus\Common\DocumentInterface;
use Opus\Common\File;
use Opus\Common\FileInterface;
use Opus\Common\LoggingTrait;
use Opus\Common\Model\ModelException;
use Opus\Common\Model\ModelInterface;
use Opus\Common\Model\NotFoundException;
use Opus\Common\Repository;
use Opus\Common\Security\Realm;
use Opus\Common\Security\SecurityException;
use Opus\Common\UserRole;
use Opus\Db\TableGateway;
use Opus\Doi\DoiManager;
use Opus\Search\Service;
use Opus\Security\AuthAdapter;

/**
 * Base class for controller tests.
 *
 * @preserveGlobalState disabled
 */
class ControllerTestCase extends TestCase
{
    use LoggingTrait;

    public const MESSAGE_LEVEL_NOTICE = 'notice';

    public const MESSAGE_LEVEL_FAILURE = 'failure';

    public const CONFIG_VALUE_FALSE = ''; // Zend_Config übersetzt false in den Wert ''

    public const CONFIG_VALUE_TRUE = '1'; // Zend_Config übersetzt true in den Wert '1'

    /** @var bool */
    private $securityEnabled;

    /** @var array */
    private $testDocuments;

    /** @var array */
    private $testFiles;

    /** @var array */
    private $testFolders;

    /** @var array */
    private $tempFiles = [];

    /** @var Application_Translate|null */
    private $translatorBackup;

    /** @var string */
    private $workspacePath;

    /** @var array|null */
    private $cleanupModels;

    /** @var Zend_Config */
    private $baseConfig;

    /**
     * Method to initialize Zend_Application for each test.
     *
     * @param string $applicationEnv
     */
    public function setUpWithEnv($applicationEnv)
    {
        $this->applicationEnv = $applicationEnv;

        // added to ensure that application log messages are written to opus.log when running unit tests
        // if not set messages are written to opus-console.log
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.0';

        parent::setUp();
    }

    public function setUp(): void
    {
        $this->setUpWithEnv(APPLICATION_ENV);

        $this->getRequest()->setRequestUri(''); // prevents PHP warnings because request URI is null
    }

    /**
     * Clean up database instances.
     */
    public function tearDown(): void
    {
        $this->logoutUser();
        $this->resetSearch();

        $this->cleanupModels();
        $this->cleanupDatabase();
        $this->deleteTestFiles();
        $this->deleteTempFiles();
        $this->cleanupTestFolders();

        $this->additionalChecks();

        $this->setLogger(null);

        DoiManager::setInstance(null);
        Application_Configuration::clearInstance(); // reset Application_Configuration
        Application_Translate::setInstance(null);
        Application_Security_AclProvider::clear();

        parent::tearDown();
    }

    /**
     * Overwrites selected properties of current configuration.
     *
     * @note A test doesn't need to backup and recover replaced configuration as
     *       this is done in setup and tear-down phases.
     * @param array         $overlay properties to overwrite existing values in configuration
     * @param null|callable $callback callback to invoke with adjusted configuration before enabling e.g. to delete some options
     * @return Zend_Config reference on updated configuration
     */
    public function adjustConfiguration($overlay, $callback = null)
    {
        $previous = Config::get();

        if ($this->baseConfig === null) {
            $this->baseConfig = $previous;
        }

        $updated = new Zend_Config($previous->toArray(), true);

        $updated->merge(new Zend_Config($overlay));

        if (is_callable($callback)) {
            $updated = call_user_func($callback, $updated);
        }

        Config::set($updated);

        return $updated;
    }

    protected function resetConfiguration()
    {
        if ($this->baseConfig !== null) {
            Config::set($this->baseConfig);
        }
    }

    /**
     * @return Zend_Application
     */
    public function getApplication()
    {
        return new Zend_Application(
            $this->applicationEnv,
            [
                "config" => [
                    APPLICATION_PATH . '/application/configs/application.ini',
                    APPLICATION_PATH . '/application/configs/config.ini',
                    APPLICATION_PATH . '/application/configs/console.ini',
                    APPLICATION_PATH . '/tests/tests.ini',
                    APPLICATION_PATH . '/tests/config.ini',
                ],
            ]
        );
    }

    public function cleanupBefore()
    {
        // Reducing memory footprint by forcing garbage collection runs
        // WARNING: Did not work on CI-System (PHP 5.3.14, PHPnit 3.5.13)
        // gc_collect_cycles();

        $this->closeDatabaseConnection();

        // Resetting singletons or other kinds of persistent objects.
        TableGateway::clearInstances();

        // Clean-up possible artifacts in $_SERVER of previous test.
        unset($_SERVER['REMOTE_ADDR']);

        parent::cleanupBefore();
    }

    public function cleanupDatabase()
    {
        if (Zend_Db_Table::getDefaultAdapter() !== null) {
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
        $doc      = Document::get(146);
        $modified = $doc->getServerDateModified();

        $this->assertEquals(2012, $modified->getYear());
    }

    protected function checkDoc1()
    {
        $doc      = Document::get(1);
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
     *
     * @param string   $body
     * @param string[] $badStrings
     */
    protected function checkForCustomBadStringsInHtml($body, $badStrings)
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
     *
     * @param string $body
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
        $adapter = new AuthAdapter();
        $adapter->setCredentials($login, $password);

        $auth = Zend_Auth::getInstance();
        $auth->authenticate($adapter);
        $this->assertTrue($auth->hasIdentity());

        $user = Zend_Auth::getInstance()->getIdentity();

        if ($user !== null) {
            try {
                $realm = Realm::getInstance();
                $realm->setUser($user);
            } catch (SecurityException $ose) {
                // unknown user -> invalidate session (logout)
                Zend_Auth::getInstance()->clearIdentity();
                $user = null;
            }
        }

        $config = Config::get();
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
        if ($instance !== null) {
            $instance->clearIdentity();
        }
        $realm = Realm::getInstance();
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
        $config = Opus\Search\Config::getServiceConfiguration(Service::SERVICE_TYPE_INDEX);

        if ($config === null) {
            $this->markTestSkipped('No solr-config given.  Skipping test.');
        }
    }

    /**
     * Modifies Solr configuration to un unknown core to simulate connection failure.
     *
     * @throws Zend_Exception
     */
    protected function disableSolr()
    {
        // TODO old config path still needed?
        // $config->searchengine->index->app = 'solr/corethatdoesnotexist';
        $this->adjustConfiguration([
            'searchengine' => [
                'solr' => [
                    'default' => [
                        'service' => [
                            'endpoint' => [
                                'localhost' => [
                                    'path' => '/solr/corethatdoesnotexist',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }

    /**
     * @param Zend_Controller_Response_Abstract $response
     * @param string                            $location
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
        $config                = $this->getConfig();
        $this->securityEnabled = $config->security;
        $config->security      = self::CONFIG_VALUE_TRUE;
        Application_Security_AclProvider::init();
    }

    /**
     * TODO is this needed for tearDown?
     */
    public function restoreSecuritySetting()
    {
        $config           = $this->getConfig();
        $config->security = $this->securityEnabled;
    }

    /**
     * Stellt die Übersetzungen auf Deutsch um.
     */
    public function useGerman()
    {
        $session           = new Zend_Session_Namespace();
        $session->language = 'de';
        Application_Translate::getInstance()->setLocale('de');
        Application_Form_Element_Language::initLanguageList();
    }

    /**
     * Stellt die Übersetzungen auf English um.
     */
    public function useEnglish()
    {
        $session           = new Zend_Session_Namespace();
        $session->language = 'en';
        Application_Translate::getInstance()->setLocale('en');
        Application_Form_Element_Language::initLanguageList();
    }

    /**
     * Prüft, ob das XHTML valide ist.
     *
     * @param string|null $body
     *
     * TODO die DTD von W3C zu holen ist sehr langsam; sollte aus lokaler Datei geladen werden
     */
    public function validateXHTML($body = null)
    {
        if ($body === null) {
            $body = $this->getResponse()->getBody();
        }

        if ($body === null || strlen(trim($body)) === 0) {
            $this->fail('No XHTML Body to validate.');
            return;
        }

        libxml_clear_errors();
        libxml_use_internal_errors(true);

        $dom = new DOMDocument();

        // Setze HTTP Header damit W3C Request nicht verweigert
        $opts = [
            'http' => [
                'user_agent' => 'PHP libxml agent',
            ],
        ];

        $context = stream_context_create($opts);
        libxml_set_streams_context($context);

        $mapping = [
            'http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd' => 'xhtml1-strict.dtd',
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
            'No declaration for attribute target of element a',
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

        $this->assertCount(
            0,
            $errors,
            'XHTML Schemaverletzungen gefunden (' . count($errors) . ')' . PHP_EOL . $output
        );

        libxml_use_internal_errors(false);
        libxml_clear_errors();
    }

    /**
     * Prüft, ob ein Kommando auf den System existiert (Mac OS-X, Linux)
     *
     * @param string $command Name des Kommandos
     * @return bool TRUE - wenn Kommando existiert
     */
    public function isCommandAvailable($command)
    {
        $this->getLogger()->debug("Checking command $command");
        $this->getLogger()->debug('User: ' . get_current_user());
        $result = shell_exec("which $command");
        return empty($result) ? false : true;
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
     *
     * @return bool
     */
    public function isFailTestOnMissingCommand()
    {
        $config = Config::get();
        return isset($config->tests->failTestOnMissingCommand) &&
                filter_var($config->tests->failTestOnMissingCommand, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Funktion zum Prüfen von FlashMessenger Nachrichten.
     *
     * Fuer gruene Nachrichten Level muss self::MESSAGE_LEVEL_NOTICE verwendet werden.
     *
     * @param string $message Übersetzungsschlüssel bzw. Nachricht
     * @param string $level 'notice' oder 'failure'
     */
    public function verifyFlashMessage($message, $level = self::MESSAGE_LEVEL_FAILURE)
    {
        $flashMessenger = Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger');
        $flashMessages  = $flashMessenger->getCurrentMessages();

        $this->assertCount(1, $flashMessages, 'Expected one flash message in queue.');
        $flashMessage = $flashMessages[0];

        $this->assertEquals($message, $flashMessage['message']);
        $this->assertEquals($level, $flashMessage['level']);
    }

    /**
     * Funktion zum Prüfen von FlashMessenger Nachrichten.
     *
     * Fuer gruene Nachrichten Level muss self::MESSAGE_LEVEL_NOTICE verwendet werden.
     *
     * @param string $message Übersetzungsschlüssel bzw. Nachricht
     * @param string $level 'notice' oder 'failure'
     */
    public function verifyNotFlashMessageContains($message, $level = self::MESSAGE_LEVEL_FAILURE)
    {
        $flashMessenger = Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger');
        $flashMessages  = $flashMessenger->getCurrentMessages();

        $this->assertCount(1, $flashMessages, 'Expected one flash message in queue.');
        $flashMessage = $flashMessages[0];

        $this->assertNotContains($message, $flashMessage['message']);
        $this->assertEquals($level, $flashMessage['level']);
    }

    /**
     * Liefert den Inhalt des Response Location Header.
     *
     * @return string|null
     */
    public function getLocation()
    {
        $headers = $this->getResponse()->getHeaders();
        foreach ($headers as $header) {
            if (isset($header['name']) && $header['name'] === 'Location') {
                return $header['value'] ?? null;
            }
        }
        return null;
    }

    /**
     * Prueft, ob eine Seite in navigationModules.xml definiert wurde.
     *
     * @param string|null $location
     */
    public function verifyBreadcrumbDefined($location = null)
    {
        if ($location === null) {
            $location = $this->getLocation(); // liefert null wenn es kein redirect war
            if ($location === null) {
                // ansonsten Request-URI verwenden
                $location = $this->getRequest()->getRequestUri();
            }
        }

        $view = $this->getView();

        $path = explode('/', $location);

        array_shift($path);
        $module     = array_shift($path);
        $controller = array_shift($path);
        $action     = array_shift($path);

        $navigation = $view->navigation()->getContainer();

        $pages = $navigation->findAllByModule($module);

        $breadcrumbDefined = false;

        foreach ($pages as $page) {
            if ($page->getController() === $controller && $page->getAction() === $action) {
                if (! $breadcrumbDefined) {
                    $breadcrumbDefined = true;

                    $translate = Application_Translate::getInstance();

                    $label = $page->getLabel();

                    $this->assertTrue(
                        $translate->isTranslated($label),
                        "Label '$label' für Seite '$location' nicht übersetzt."
                    );
                } else {
                    $this->fail("Seite '$location' mehr als einmal in navigationModules.xml definiert.");
                }
            }
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
     * @param DocumentInterface|int $value
     * @throws ModelException
     */
    public function removeDocument($value)
    {
        if ($value === null) {
            return;
        }

        $doc = $value;
        if (! $value instanceof DocumentInterface) {
            try {
                $doc = Document::get($value);
            } catch (NotFoundException $e) {
                // could not find document -> no cleanup operation required: exit silently
                return;
            }
        }

        $docId = $doc->getId();
        if ($docId === null) {
            // Dokument wurde (noch) nicht in DB persistiert
            return;
        }

        try {
            Document::get($docId);
            $doc->delete();
        } catch (NotFoundException $omnfe) {
            // Model nicht gefunden -> alles gut (hoffentlich)
            $this->getLogger()->debug("Test document {$docId} was deleted successfully by test.");
            return;
        } catch (Exception $ex) {
            $this->getLogger()->err('unexpected exception while cleaning document ' . $docId . ': ' . $ex);
            throw $ex;
        }

        // make sure test documents have been deleted
        try {
            Document::get($docId);
            $this->getLogger()->debug("Test document {$docId} was not deleted.");
        } catch (NotFoundException $omnfe) {
            // ignore - document was deleted successfully
            $this->getLogger()->debug("Test document {$docId} was deleted successfully.");
        }
    }

    protected function deleteTestDocuments()
    {
        if ($this->testDocuments === null) {
            return;
        }

        foreach ($this->testDocuments as $key => $doc) {
            $this->removeDocument($doc);
        }

        $this->testDocuments = null;
    }

    /**
     * @param int $docId
     * @return DocumentInterface
     */
    protected function getDocument($docId)
    {
        return Document::get($docId);
    }

    /**
     * Returns finder for documents.
     *
     * @return DocumentFinderInterface
     */
    protected function getDocumentFinder()
    {
        return Repository::getInstance()->getDocumentFinder();
    }

    /**
     * Erzeugt ein Testdokument, das nach der Testausführung automatisch aufgeräumt wird.
     *
     * @return DocumentInterface
     * @throws ModelException
     */
    protected function createTestDocument()
    {
        $doc = Document::new();
        $this->addTestDocument($doc);
        return $doc;
    }

    /**
     * Adds a document to the cleanup queue.
     *
     * @param DocumentInterface $document
     */
    protected function addTestDocument($document)
    {
        if ($this->testDocuments === null) {
            $this->testDocuments = [];
        }
        array_push($this->testDocuments, $document);
    }

    /**
     * @param string $prefix
     * @return string
     */
    protected function getTempFile($prefix = 'Opus4TestTemp')
    {
        $filePath          = tempnam(sys_get_temp_dir(), $prefix);
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
     * @param string      $filename
     * @param null|string $filepath
     * @return FileInterface
     * @throws ModelException
     * @throws Zend_Exception
     *
     * TODO allow same filename in different locations
     */
    protected function createOpusTestFile($filename, $filepath = null)
    {
        if ($this->testFiles === null) {
            $this->testFiles = [];
        }

        $workspacePath = $this->getWorkspacePath();

        if ($filepath === null) {
            $path     = $this->createTestFolder();
            $filepath = $path . DIRECTORY_SEPARATOR . $filename;
            touch($filepath);
        }

        $this->assertTrue(is_readable($filepath));
        $file = File::new();
        $file->setPathName(basename($filepath));
        $file->setTempFile($filepath);
        if (array_key_exists($filename, $this->testFiles)) {
            throw new Exception('filenames should be unique');
        }
        $this->testFiles[$filename] = $filepath;
        return $file;
    }

    /**
     * @param string $filePath
     */
    public function addFileToCleanup($filePath)
    {
        if ($this->testFiles === null) {
            $this->testFiles = [];
        }

        $fileName = basename($filePath);

        $this->testFiles[$fileName] = $filePath;
    }

    protected function deleteTestFiles()
    {
        if ($this->testFiles !== null) {
            foreach ($this->testFiles as $key => $filepath) {
                try {
                    if (is_writable($filepath)) {
                        unlink($filepath);
                    }
                } catch (Exception $e) {
                }
            }
        }
    }

    /**
     * @param string      $filename
     * @param string|null $content
     * @param string|null $path
     * @return string
     */
    protected function createTestFile($filename, $content = null, $path = null)
    {
        if ($path === null) {
            $filePath = $this->createTestFolder();
        } else {
            $filePath = $path;
        }

        $filePath .= DIRECTORY_SEPARATOR . $filename;

        if ($content !== null) {
            file_put_contents($filePath, $content);
        } else {
            touch($filePath);
        }

        if ($this->testFiles === null) {
            $this->testFiles = [];
        }
        $this->testFiles[$filename] = $filePath;

        return $filePath;
    }

    /**
     * @return string
     */
    protected function getWorkspacePath()
    {
        if ($this->workspacePath === null) {
            $config = $this->getConfig();
            if (! isset($config->workspacePath)) {
                throw new Exception('config key \'workspacePath\' not defined in config file');
            }
            $this->workspacePath = $config->workspacePath;
        }

        return $this->workspacePath;
    }

    /**
     * @param string $path
     */
    protected function setWorkspacePath($path)
    {
        $this->workspacePath = $path;
    }

    /**
     * @return string
     */
    protected function createTestFolder()
    {
        $workspacePath = $this->getWorkspacePath();
        $path          = $workspacePath . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . uniqid('test');
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
     * @param string $src
     * @param string $dest
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
     *
     * @param string $path
     * @param bool   $deleteOutsideWorkspace
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
        $this->assertEquals('1', Config::get()->security);
        Application_Security_AclProvider::init();
        $acl = Application_Security_AclProvider::getAcl();
        $this->assertTrue($acl instanceof Zend_Acl, 'Expected instance of Zend_Acl');
    }

    public function resetSearch()
    {
        Opus\Search\Config::dropCached();
        Service::dropCached();
    }

    /**
     * Sets the hostname for a test.
     *
     * @param string $host Hostname for tests
     * @throws Zend_Exception
     */
    public function setHostname($host)
    {
        $view = $this->getView();
        $view->getHelper('ServerUrl')->setHost($host);
    }

    /**
     * Sets base URL for tests.
     *
     * A lot of tests fail if the base URL is set because they verify URLs from the server root, like '/auth' instead
     * of 'opus4/auth' (base URL = 'opus4').
     *
     * @param string $baseUrl Base URL for tests
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
        if ($this->translatorBackup === null) {
            $this->translatorBackup = Application_Translate::getInstance();
        }

        $translate = new Application_Translate([
            'adapter' => 'array',
            'content' => [],
            'locale'  => 'auto',
        ]);

        Application_Translate::setInstance($translate);
    }

    /**
     * Resets translations with original (bootstrap) translation object.
     *
     * This function restores translation if disableTranslation has been called before.
     */
    public function enableTranslation()
    {
        if ($this->translatorBackup !== null) {
            Application_Translate::setInstance($this->translatorBackup);
        }
    }

    /**
     * Allow the given user (identified by his or her name) to access the given module.
     * Returns true if access permission was added; otherwise false.
     *
     * @param string $moduleName module name
     * @param string $userName user name
     * @return bool
     * @throws ModelException
     */
    protected function addModuleAccess($moduleName, $userName)
    {
        $r       = UserRole::fetchByName($userName);
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
     * @param string $moduleName module name
     * @param string $userName user name
     * @return bool
     * @throws ModelException
     */
    protected function removeModuleAccess($moduleName, $userName)
    {
        $r       = UserRole::fetchByName($userName);
        $modules = $r->listAccessModules();
        if (in_array($moduleName, $modules)) {
            $r->removeAccessModule($moduleName);
            $r->store();
            return true;
        }
        return false;
    }

    /**
     * @param ModelInterface $model
     */
    protected function addModelToCleanup($model)
    {
        if (! is_array($this->cleanupModels)) {
            $this->cleanupModels = [];
        }

        $this->cleanupModels[] = $model;
    }

    protected function cleanupModels()
    {
        if (! is_array($this->cleanupModels)) {
            return;
        }

        foreach ($this->cleanupModels as $model) {
            try {
                $model->delete();
            } catch (Exception $ex) {
                // TODO logging?
            }
        }
    }

    /**
     * @return Zend_View
     */
    protected function getView()
    {
        return $this->application->getBootstrap()->getResource('view');
    }
}
