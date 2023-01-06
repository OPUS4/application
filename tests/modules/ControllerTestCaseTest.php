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

use Opus\Common\Document;
use Opus\Common\DocumentFinderInterface;
use Opus\Common\Model\NotFoundException;
use Opus\Common\Security\Realm;

/**
 * TODO LAMINAS use appropriate assertions instead of generic assertTrue
 */
class ControllerTestCaseTest extends ControllerTestCase
{
    /** @var string[] */
    protected $additionalResources = ['view', 'translation'];

    /** @var bool */
    protected $configModifiable = true;

    public function tearDown(): void
    {
        $this->restoreSecuritySetting();
        parent::tearDown();
    }

    /**
     * Prüft, ob der User eingeloggt wurde.
     *
     * Dient der Vorbereitung von Test "testTearDownDidLogout".
     */
    public function testLoginAdmin()
    {
        $this->enableSecurity();
        $this->loginUser('admin', 'adminadmin');
        $realm = Realm::getInstance();

        $this->assertContains(
            'administrator',
            $realm->getRoles(),
            Zend_Debug::dump($realm->getRoles(), null, false)
        );
    }

    /**
     * Prüft, ob der User vom Test "testLoginAdmin" nicht mehr eingeloggt ist.
     *
     * Regression Test für OPUSVIER-3283
     */
    public function testTearDownDidLogout()
    {
        $this->enableSecurity();
        $realm = Realm::getInstance();
        $this->assertNotContains('administrator', $realm->getRoles());
    }

    public function testSetHostname()
    {
        $view = $this->getView();

        $this->assertEquals('http://', $view->serverUrl());

        $this->setHostname('localhost');

        $this->assertEquals('http://localhost', $view->serverUrl());
    }

    public function testSetBaseUrlNotSet()
    {
        $view = $this->getView();

        $this->assertEquals('', $view->baseUrl());

        // base Url must be set before first baseUrl() call, won't be changed afterwards
        $this->setBaseUrl('opus4');
        $this->assertEquals('', $view->baseUrl());
    }

    public function testSetBaseUrlSet()
    {
        $view = $this->getView();

        $this->setBaseUrl('opus4');

        $this->assertEquals('opus4', $view->baseUrl());
    }

    /**
     * Test removing document using identifier.
     */
    public function testRemoveDocumentById()
    {
        $doc   = Document::new();
        $docId = $doc->store();

        $this->removeDocument($docId);

        $this->expectException(NotFoundException::class);

        Document::get($docId);
    }

    /**
     * Test removing document using object.
     */
    public function testRemoveDocument()
    {
        $doc   = Document::new();
        $docId = $doc->store();

        $this->removeDocument($doc);

        $this->expectException(NotFoundException::class);

        Document::get($docId);
    }

    /**
     * Test removing document that has not been stored.
     */
    public function testRemoveDocumentNotStored()
    {
        $doc = Document::new();

        $this->removeDocument($doc);
    }

    public function testGetTempFile()
    {
        $tempFile = $this->getTempFile();

        $this->assertFileExists($tempFile);

        $tempFile2 = $this->getTempFile();

        $this->assertFileExists($tempFile2);

        $this->assertNotEquals($tempFile, $tempFile2);

        $this->deleteTempFiles();

        $this->assertFileNotExists($tempFile);
        $this->assertFileNotExists($tempFile2);
    }

    public function testDisableEnableTranslation()
    {
        $defaultTranslator = Application_Translate::getInstance();
        $this->assertTrue($defaultTranslator->isTranslated('LastName'));

        $this->disableTranslation();

        $translator = Application_Translate::getInstance();
        $this->assertFalse($translator->isTranslated('LastName'));

        $this->enableTranslation();

        $translator = Application_Translate::getInstance();
        $this->assertTrue($translator->isTranslated('LastName'));

        $this->assertSame($defaultTranslator, $translator);
    }

    public function testGetWorkspacePath()
    {
        $workspacePath = $this->getWorkspacePath();

        $this->assertTrue(is_dir($workspacePath));
        $this->assertTrue(is_writable($workspacePath));
    }

    public function testGetWorkspacePathNotDefined()
    {
        $this->adjustConfiguration([
            'workspacePath' => null,
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('config key \'workspacePath\' not defined in config file');
        $this->getWorkspacePath();
    }

    public function testSetWorkspacePath()
    {
        $path    = $this->getWorkspacePath();
        $newPath = $path . DIRECTORY_SEPARATOR . 'tmp';

        $this->setWorkspacePath($newPath);
        $this->assertEquals($newPath, $this->getWorkspacePath());

        $this->setWorkspacePath(null);
        $this->assertEquals($path, $this->getWorkspacePath());
    }

    public function testCreateTestFolder()
    {
        $path = $this->createTestFolder();

        $this->assertTrue(is_dir($path));
        $this->assertTrue(is_writable($path));
    }

    public function testCleanupTestFolders()
    {
        $path = $this->createTestFolder();

        $this->assertTrue(is_dir($path));
        $this->assertTrue(is_writable($path));

        $this->cleanupTestFolders();

        $this->assertFalse(file_exists($path));
    }

    public function testCleanupTestFoldersWithFiles()
    {
        $path = $this->createTestFolder();

        $this->assertTrue(is_dir($path));
        $this->assertTrue(is_writable($path));

        $file1 = $this->createTestFile('test1.txt', null, $path);
        $file2 = $this->createTestFile('test2.txt', null, $path);

        $this->assertFileExists($file1);
        $this->assertFileExists($file2);

        $this->cleanupTestFolders();

        $this->assertFileNotExists($file1);
        $this->assertFileNotExists($file2);
        $this->assertFileNotExists($path);
    }

    public function testCleanupTestFoldersWithSubfolders()
    {
        $path = $this->createTestFolder();

        $subfolder = $path . DIRECTORY_SEPARATOR . 'sub1';
        mkdir($subfolder);

        $file1 = $this->createTestFile('test1.txt', null, $subfolder);

        $this->assertFileExists($file1);

        $this->cleanupTestFolders();

        $this->assertFileNotExists($file1);
        $this->assertFileNotExists($path);
    }

    public function testDeleteFolder()
    {
        $folder = $this->createTestFolder();

        $subfolder = $folder . DIRECTORY_SEPARATOR . 'sub1';
        mkdir($subfolder);

        $file1 = $this->createTestFile('test1.txt', null, $folder);
        $file2 = $this->createTestFile('test2.txt', null, $subfolder);

        $this->assertFileExists($file1);
        $this->assertFileExists($file2);

        $this->deleteFolder($folder);

        $this->assertFileNotExists($folder);
    }

    public function testDeleteFolderOutsideWorkspace()
    {
        $path = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . uniqid();

        mkdir($path);

        $this->assertFileExists($path);
        $this->deleteFolder($path);
        $this->assertFileExists($path); // not deleted because outside workspace

        $this->deleteFolder($path, true);
        $this->assertFileNotExists($path);
    }

    public function testDeleteFolderDoNotFollowSymLinks()
    {
        $temp = sys_get_temp_dir();
        $path = $temp . DIRECTORY_SEPARATOR . uniqid('opustest');
        mkdir($path);

        $file1 = $this->createTestFile('test1.txt', null, $path);

        $folder = $this->createTestFolder();
        $file2  = $this->createTestFile('test2.txt', null, $folder);

        $link = $folder . DIRECTORY_SEPARATOR . 'link.txt';
        symlink($file1, $link);

        $this->deleteFolder($folder);

        $this->assertFileNotExists($folder);
        $this->assertFileExists($file1);

        $this->deleteFolder($path, true);
        $this->assertFileNotExists($path);
    }

    public function testCreateTestFile()
    {
        $path = $this->createTestFile('test1.txt');

        $this->assertFileExists($path);
    }

    public function testCreateTestFileCleanup()
    {
        $path = $this->createTestFile('test1.txt');

        $this->assertFileExists($path);

        $this->deleteTestFiles();

        $this->assertFileNotExists($path);
    }

    public function testCreateTestFileWithContent()
    {
        $content = 'Test file content';

        $file = $this->createTestFile('opus1.txt', $content);

        $this->assertFileExists($file);

        $actual = file_get_contents($file);

        $this->assertEquals($content, $actual);
    }

    public function testCreateTestFileWithPath()
    {
        $path = $this->createTestFolder();

        $file = $this->createTestFile('opus1.txt', null, $path);

        $this->assertFileExists($file);
        $this->assertTrue(strpos($file, $path) === 0, 'File was not created with path.');
    }

    public function testDeleteTestFiles()
    {
        $file = $this->createTestFile('test1.txt');

        $this->assertFileExists($file);
        $this->deleteTestFiles();
        $this->assertFileNotExists($file);
    }

    public function testDeleteTestFilesForOpusFile()
    {
        $file = $this->createOpusTestFile('opus1.txt');

        $path = $file->getTempFile();

        $this->assertFileExists($path);
        $this->deleteTestFiles();
        $this->assertFileNotExists($path);
    }

    public function testDeleteTestFilesAlreadyDeleted()
    {
        $file = $this->createTestFile('opus1.txt');

        $this->assertFileExists($file);
        unlink($file);
        $this->assertFileNotExists($file);

        $this->deleteTestFiles(); // no exceptions
    }

    public function testCreateOpusTestFileTempFolderCleanup()
    {
        $file = $this->createOpusTestFile('opus1.txt');

        $path = $file->getTempFile();

        $this->assertFileExists($path);
        $this->deleteTestFiles();
        $this->assertFileNotExists($path);

        $dir = dirname($path);

        $this->assertFileExists($dir);
        $this->cleanupTestFolders();
        $this->assertFileNotExists($dir);
    }

    public function testCopyFiles()
    {
        $folder = $this->createTestFolder();

        $helpFiles = new Home_Model_HelpFiles();
        $helpPath  = $helpFiles->getHelpPath();

        $this->copyFiles($helpPath, $folder);

        $files = scandir($folder);

        $this->assertCount(21, $files);
        $this->assertContains('help.ini', $files);
        $this->assertContains('imprint.de.txt', $files);
        $this->assertContains('metadata.en.txt', $files);
    }

    public function testGetDocumentFinder()
    {
        $finder = $this->getDocumentFinder();

        $this->assertInstanceOf(DocumentFinderInterface::class, $finder);
    }
}
