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

use Opus\Common\Account;
use Opus\Common\Date;
use Opus\Common\Document;
use Opus\Common\File;
use Opus\Common\UserRole;

class Oai_Model_ContainerTest extends ControllerTestCase
{
    /** @var string[] */
    protected $additionalResources = ['database'];

    /** @var string */
    private $workspacePath;

    /** @var int */
    private $roleId;

    /** @var int */
    private $userId;

    public function setUp(): void
    {
        parent::setUp();
        $config = $this->getConfig();
        if (! isset($config->workspacePath)) {
            throw new Exception("config key 'workspacePath' not defined in config file");
        }
        $this->workspacePath = $config->workspacePath;
    }

    public function tearDown(): void
    {
        if ($this->roleId !== null) {
            $testRole = UserRole::get($this->roleId);
            $testRole->delete();
        }
        if ($this->userId !== null) {
            $userAccount = Account::get($this->userId);
            $userAccount->delete();
        }
        parent::tearDown();
    }

    public function testConstructorWithNullArgument()
    {
        $model = null;
        try {
            $model = new Oai_Model_Container(null);
        } catch (Oai_Model_Exception $e) {
            $this->assertEquals('missing parameter docId', $e->getMessage());
        }
        $this->assertNull($model);
    }

    public function testConstructorWithInvalidArgument()
    {
        $model = null;
        try {
            $model = new Oai_Model_Container('foo');
        } catch (Oai_Model_Exception $e) {
            $this->assertEquals('invalid value for parameter docId', $e->getMessage());
        }
        $this->assertNull($model);
    }

    public function testConstructorWithUnknownDocId()
    {
        $model = null;
        try {
            $model = new Oai_Model_Container('123456789');
        } catch (Oai_Model_Exception $e) {
            $this->assertEquals('requested docId does not exist', $e->getMessage());
        }
        $this->assertNull($model);
    }

    public function testConstructorWithUnpublishedDocument()
    {
        $r = UserRole::fetchByName('guest');

        $modules            = $r->listAccessModules();
        $addOaiModuleAccess = ! in_array('oai', $modules);
        if ($addOaiModuleAccess) {
            $r->appendAccessModule('oai');
            $r->store();
        }

        // enable security
        $config           = $this->getConfig();
        $config->security = self::CONFIG_VALUE_TRUE;

        $doc = $this->createTestDocument();
        $doc->setServerState('unpublished');
        $doc->store();

        $model   = new Oai_Model_Container($doc->getId());
        $tarball = null;
        try {
            $tarball = $model->getFileHandle();
        } catch (Oai_Model_Exception $e) {
            $this->assertEquals('access to requested document is forbidden', $e->getMessage());
        }
        $this->assertNull($tarball);

        if ($addOaiModuleAccess) {
            $r->removeAccessModule('oai');
            $r->store();
        }
    }

    public function testConstructorWithPublishedDocumentWithoutAnyFiles()
    {
        $doc = $this->createTestDocument();
        $doc->setServerState('published');
        $doc->store();

        $model   = new Oai_Model_Container($doc->getId());
        $tarball = null;
        try {
            $tarball = $model->getFileHandle();
        } catch (Oai_Model_Exception $e) {
            $this->assertEquals('requested document does not have any associated readable files', $e->getMessage());
        }
        $this->assertNull($tarball);
    }

    public function testGetName()
    {
        $doc = $this->createTestDocument();
        $doc->setServerState('published');
        $file = File::new();
        $file->setPathName('foo.pdf');
        $file->setVisibleInOai(false);
        $doc->addFile($file);
        $doc->store();

        $container = new Oai_Model_Container($doc->getId());
        $this->assertEquals($doc->getId(), $container->getName());
    }

    public function testDocumentWithRestrictedFile()
    {
        $filename = 'foo.pdf';
        $file     = $this->createOpusTestFile($filename);

        $doc = $this->createTestDocument();
        $doc->setServerState('published');
        $file->setVisibleInOai(false);
        $doc->addFile($file);
        $doc->store();

        $this->assertTrue($file->isReadable());

        $model   = new Oai_Model_Container($doc->getId());
        $tarball = null;
        try {
            $tarball = $model->getFileHandle();
        } catch (Oai_Model_Exception $e) {
            $this->assertEquals(
                'access denied on all files that are associated to the requested document',
                $e->getMessage()
            );
        }
        $this->assertNull($tarball);
    }

    public function testDocumentWithNonExistentFile()
    {
        $doc = $this->createTestDocument();
        $doc->setServerState('published');
        $file = File::new();
        $file->setPathName('test.pdf');
        $file->setVisibleInOai(true);
        $doc->addFile($file);
        $doc->store();

        $model   = new Oai_Model_Container($doc->getId());
        $tarball = null;
        try {
            $tarball = $model->getFileHandle();
        } catch (Oai_Model_Exception $e) {
            $this->assertEquals('requested document does not have any associated readable files', $e->getMessage());
        }
        $this->assertNull($tarball);
    }

    public function testDocumentWithSingleUnrestrictedFile()
    {
        $filename = 'test.txt';
        $file     = $this->createOpusTestFile($filename);

        $doc = $this->createTestDocument();
        $doc->setServerState('published');
        $file->setVisibleInOai(true);
        $doc->addFile($file);
        $doc->store();

        $this->assertTrue($file->isReadable());

        $model = new Oai_Model_Container($doc->getId());
        $file  = $model->getFileHandle();

        $path      = $file->getPath();
        $extension = $file->getExtension();
        $mimeType  = $file->getMimeType();
        //clean up File Handle
        $this->assertTrue(is_readable($path));
        unlink($path);

        $this->assertEquals('.txt', $extension);
        // TODO OPUSVIER-2503
        $this->assertTrue($mimeType === 'application/x-empty' || $mimeType === 'inode/x-empty');
    }

    public function testDocumentWithTwoUnrestrictedFiles()
    {
        $filename1 = 'foo.pdf';
        $filename2 = 'bar.pdf';

        $file1 = $this->createOpusTestFile($filename1);
        $file1->setVisibleInOai(true);

        $doc = $this->createTestDocument();
        $doc->setServerState('published');
        $doc->addFile($file1);

        $file2 = $this->createOpusTestFile($filename2);
        $file2->setVisibleInOai(true);
        $doc->addFile($file2);
        $doc->store();

        $this->assertTrue($file1->isReadable());
        $this->assertTrue($file2->isReadable());

        $model = new Oai_Model_Container($doc->getId());
        $file  = $model->getFileHandle();

        $path      = $file->getPath();
        $extension = $file->getExtension();
        $mimeType  = $file->getMimeType();
        //clean up File Handle
        $this->assertTrue(is_readable($path));
        unlink($path);

        $this->assertEquals('.tar', $extension);
        $this->assertEquals('application/x-tar', $mimeType);
    }

    public function testDeleteContainerTarFile()
    {
        $filename1 = 'test.pdf';
        $filename2 = 'foo.html';

        $file1 = $this->createOpusTestFile($filename1);
        $file1->setVisibleInOai(true);

        $doc = $this->createTestDocument();
        $doc->setServerState('published');
        $doc->addFile($file1);

        $file2 = $this->createOpusTestFile($filename2);
        $file2->setVisibleInOai(true);
        $doc->addFile($file2);
        $doc->store();

        $this->assertTrue($file1->isReadable());
        $this->assertTrue($file2->isReadable());

        $model   = new Oai_Model_Container($doc->getId());
        $tarball = $model->getFileHandle();
        $this->assertTrue(is_readable($tarball->getPath()));

        $tarball->delete();
        $this->assertFalse(file_exists($tarball->getPath()));
    }

    public function testDeleteContainerSingleFile()
    {
        $filename1 = 'test.pdf';

        $file = $this->createOpusTestFile($filename1);

        $doc = $this->createTestDocument();
        $doc->setServerState('published');
        $file->setVisibleInOai(true);
        $doc->addFile($file);
        $doc->store();

        $this->assertTrue(is_readable($this->workspacePath . '/files/' . $doc->getId() . '/' . $file->getPathName()));

        $model   = new Oai_Model_Container($doc->getId());
        $tarball = $model->getFileHandle();
        $this->assertTrue(is_readable($tarball->getPath()));

        $tarball->delete();
        $this->assertFalse(file_exists($tarball->getPath()));
    }

    /**
     * tests document access for three user roles (admin, user with access rights, user without access rights)
     */
    public function testAdminAccessToFileRegression3281()
    {
        $this->enableSecurity();

        // test document access as admin
        $this->loginUser('admin', 'adminadmin');

        $doc = $this->createTestDocument();
        $doc->setServerState('published');
        $docId = $doc->store();
        $this->tryAccessForDocument($docId, true);

        $doc = Document::get($docId);
        $doc->setServerState('unpublished');
        $docId = $doc->store();
        $this->tryAccessForDocument($docId, true);
    }

    public function testAccessUserToFileRegression3281()
    {
        $this->enableSecurity();

        // test document access as user with document access rights
        $doc = $this->createTestDocument();
        $doc->setServerState('published');
        $publishedDocId = $doc->store();

        $doc = $this->createTestDocument();
        $doc->setServerState('unpublished');
        $unpublishedDocId = $doc->store();

        $testRole = UserRole::new();
        $testRole->setName('test_access');
        $testRole->appendAccessDocument($unpublishedDocId);
        $testRole->appendAccessDocument($publishedDocId);
        $this->roleId = $testRole->store();

        $userAccount = Account::new();
        $userAccount->setLogin('test_account')->setPassword('role_tester_user2');
        $userAccount->setRole($testRole);
        $this->userId = $userAccount->store();

        $this->loginUser('test_account', 'role_tester_user2');
        $this->tryAccessForDocument($publishedDocId, true);
        $this->tryAccessForDocument($unpublishedDocId, true);
        $this->logoutUser();
    }

    public function testGuestAccessToFileRegression3281()
    {
        $this->enableSecurity();

        // test document access as user without access rights
        $doc = $this->createTestDocument();
        $doc->setServerState('published');
        $docId = $doc->store();
        $this->tryAccessForDocument($docId, true);

        $doc = Document::get($docId);
        $doc->setServerState('unpublished');
        $docId = $doc->store();
        $this->tryAccessForDocument($docId, false);
    }

    /**
     * @param int  $docId
     * @param bool $accessAllowed
     */
    private function tryAccessForDocument($docId, $accessAllowed)
    {
        $model            = new Oai_Model_Container($docId);
        $tarball          = null;
        $exceptionMessage = null;
        try {
            $tarball = $model->getFileHandle();
        } catch (Oai_Model_Exception $e) {
            $exceptionMessage = $e->getMessage();
        }
        if ($accessAllowed === true) {
            $this->assertEquals('requested document does not have any associated readable files', $exceptionMessage);
        } else {
            $this->assertEquals('access to requested document is forbidden', $exceptionMessage);
        }
    }

    public function testGetAccessibleFilesForEmbargoedDocument()
    {
        $this->enableSecurity();

        $doc = $this->createTestDocument();
        $doc->setServerState('published');

        // set embargo date to tomorrow
        $date = new Date();
        $date->setDateTime(new DateTime('tomorrow'));
        $doc->setEmbargoDate($date);

        // add a file visible in OAI
        $file = $this->createOpusTestFile('foo.pdf');
        $file->setVisibleInOai(true);
        $doc->addFile($file);

        $doc->store();

        $this->assertFalse($doc->hasEmbargoPassed()); // not yet passed

        $container = new Oai_Model_Container($doc->getId());

        $this->expectException(Oai_Model_Exception::class);
        $this->expectExceptionMessage('access to requested document files is embargoed');
        $container->getAccessibleFiles();
    }
}
