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

use Opus\Common\Date;
use Opus\Common\File;
use Opus\Common\FileInterface;

class Frontdoor_Model_FileTest extends ControllerTestCase
{
    /** @var string[] */
    protected $additionalResources = ['database'];

    public const FILENAME                 = 'test.xhtml';
    public const FILENAME_DELETED_DOC     = 'foo.html';
    public const FILENAME_UNPUBLISHED_DOC = 'bar.html';
    public const EXPECTED_EXCEPTION       = "Test failed: expected Exception";

    public function setUp(): void
    {
        parent::setUpWithEnv('production');
        $this->assertSecurityConfigured();
    }

    public function testGetFileObjectSuccessfulCase()
    {
        $file     = new Frontdoor_Model_File(92, self::FILENAME);
        $realm    = new MockRealm(true, true);
        $opusFile = $file->getFileObject($realm);
        $this->assertEquals(self::FILENAME, $opusFile->getPathName());
    }

    public function testGetFileObjectDocumentNotFoundException()
    {
        $this->expectException(Frontdoor_Model_DocumentNotFoundException::class);
        $this->expectExceptionCode(404);
        new Frontdoor_Model_File(99999999999, self::FILENAME);
    }

    public function testGetFileObjectDocumentDeletedExceptionIfDocForbidden()
    {
        $file  = new Frontdoor_Model_File(123, self::FILENAME_DELETED_DOC);
        $realm = new MockRealm(true, false);

        $this->expectException(Frontdoor_Model_DocumentDeletedException::class);
        $this->expectExceptionCode(404);
        $file->getFileObject($realm);
    }

    public function testGetFileObjectFileAccessNotAllowedExceptionIfFileForbidden()
    {
        $file  = new Frontdoor_Model_File(123, self::FILENAME_DELETED_DOC);
        $realm = new MockRealm(false, true);

        $this->expectException(Frontdoor_Model_FileAccessNotAllowedException::class);
        $this->expectExceptionCode(403);
        $file->getFileObject($realm);
    }

    public function testGetFileObjectNoDocumentDeletedExceptionIfAccessAllowed()
    {
        $file     = new Frontdoor_Model_File(123, self::FILENAME_DELETED_DOC);
        $realm    = new MockRealm(true, true);
        $opusFile = $file->getFileObject($realm);

        $this->assertTrue($opusFile instanceof FileInterface);
        $this->assertEquals(self::FILENAME_DELETED_DOC, $opusFile->getPathName());
    }

    public function testGetFileObjectAccessAllowedForUserWithAccessToDocumentsResource()
    {
        $file = new Frontdoor_Model_File(92, self::FILENAME);
        $file->setAclHelper(new MockAccessControl(true));
        $realm    = new MockRealm(false, false); // sollte egal sein
        $opusFile = $file->getFileObject($realm);
        $this->assertTrue($opusFile instanceof FileInterface);
    }

    public function testGetFileObjectAccessNotAllowedForUser()
    {
        $file = new Frontdoor_Model_File(92, self::FILENAME);
        $file->setAclHelper(new MockAccessControl(false));
        $realm = new MockRealm(false, false); // sollte egal sein

        $this->expectException(Frontdoor_Model_FileAccessNotAllowedException::class);
        $file->getFileObject($realm);
    }

    public function testGetFileObjectDocumentAccessNotAllowedException()
    {
        $file  = new Frontdoor_Model_File(124, self::FILENAME_UNPUBLISHED_DOC);
        $realm = new MockRealm(true, false);

        $this->expectException(Frontdoor_Model_DocumentAccessNotAllowedException::class);
        $file->getFileObject($realm);
    }

    public function testGetFileObjectFileNotFoundException()
    {
        $file  = new Frontdoor_Model_File(92, 'this_file_does_not_exist.file');
        $realm = new MockRealm(true, true);

        $this->expectException(Frontdoor_Model_FileNotFoundException::class);
        $file->getFileObject($realm);
    }

    public function testGetFileObjectFileAccessNotAllowedException()
    {
        $file  = new Frontdoor_Model_File(92, self::FILENAME);
        $realm = new MockRealm(false, true);

        $this->expectException(Frontdoor_Model_FileAccessNotAllowedException::class);
        $file->getFileObject($realm);
    }

    public function testConstructorDocIdEmpty()
    {
        try {
            new Frontdoor_Model_File("", "");
            $this->fail(self::EXPECTED_EXCEPTION);
        } catch (Frontdoor_Model_FrontdoorDeliveryException $e) {
            $this->assertEquals(
                Frontdoor_Model_File::ILLEGAL_DOCID_MESSAGE_KEY,
                $e->getTranslateKey()
            );
        }
    }

    public function testConstructorDocIdNoNumber()
    {
        try {
            new Frontdoor_Model_File('xx', "");
            $this->fail(self::EXPECTED_EXCEPTION);
        } catch (Frontdoor_Model_FrontdoorDeliveryException $e) {
            $this->assertEquals(
                Frontdoor_Model_File::ILLEGAL_DOCID_MESSAGE_KEY,
                $e->getTranslateKey()
            );
        }
    }

    public function testConstructorDocId()
    {
        try {
            new Frontdoor_Model_File(null, self::FILENAME);
            $this->fail(self::EXPECTED_EXCEPTION);
        } catch (Frontdoor_Model_FrontdoorDeliveryException $e) {
            $this->assertEquals(
                Frontdoor_Model_File::ILLEGAL_DOCID_MESSAGE_KEY,
                $e->getTranslateKey()
            );
        }
    }

    public function testConstructorFilenameEmpty()
    {
        try {
            new Frontdoor_Model_File('1', '');
            $this->fail(self::EXPECTED_EXCEPTION);
        } catch (Frontdoor_Model_FrontdoorDeliveryException $e) {
            $this->assertEquals(
                Frontdoor_Model_File::ILLEGAL_FILENAME_MESSAGE_KEY,
                $e->getTranslateKey()
            );
        }
    }

    public function testConstructorFilenameHigherLevelDir()
    {
        try {
            new Frontdoor_Model_File('1', '../*');
            $this->fail(self::EXPECTED_EXCEPTION);
        } catch (Frontdoor_Model_FrontdoorDeliveryException $e) {
            $this->assertEquals(
                Frontdoor_Model_File::ILLEGAL_FILENAME_MESSAGE_KEY,
                $e->getTranslateKey()
            );
        }
    }

    public function testWrongTypeOfRealmNoDocAccess()
    {
        $file  = new Frontdoor_Model_File(124, self::FILENAME_UNPUBLISHED_DOC);
        $realm = 'this is an invalid realm object';

        $this->expectException(Frontdoor_Model_DocumentAccessNotAllowedException::class);
        $file->getFileObject($realm);
    }

    public function testWrongTypeOfRealmNoFileAccess()
    {
        $file  = new Frontdoor_Model_File(92, self::FILENAME);
        $realm = 'this is an invalid realm object';

        $this->expectException(Frontdoor_Model_FileAccessNotAllowedException::class);
        $file->getFileObject($realm);
    }

    public function testGetAclHelper()
    {
        $file = new Frontdoor_Model_File(92, self::FILENAME);

        $helper = $file->getAclHelper();

        $this->assertNotNull($helper);
        $this->assertInstanceOf('Application_Controller_Action_Helper_AccessControl', $helper);
    }

    public function testSetAclHelper()
    {
        $file = new Frontdoor_Model_File(92, self::FILENAME);

        $mock = new MockAccessControl();

        $file->setAclHelper($mock);

        $this->assertEquals($mock, $file->getAclHelper());

        $file->setAclHelper(null);

        $helper = $file->getAclHelper();

        $this->assertNotNull($helper);
        $this->assertInstanceOf('Application_Controller_Action_Helper_AccessControl', $helper);
    }

    /**
     * Dateien, die nicht sichtbar sind in Frontdoor dürfen nicht heruntergeladen werden.
     */
    public function testFileAccessDeniedIfNotVisibleInFrontdoorForGuest()
    {
        $model = new Frontdoor_Model_File(91, "frontdoor_invisible.txt");

        $realm = new MockRealm(true, true);

        $opusFile = File::get(128);

        $this->assertEquals(0, $opusFile->getVisibleInFrontdoor(), "Testdaten geändert.");
        $this->assertEquals("frontdoor_invisible.txt", $opusFile->getPathName(), "Testdaten geändert.");

        $this->expectException(Frontdoor_Model_FileAccessNotAllowedException::class);
        $model->getFileObject($realm);
    }

    /**
     * User mit Zugriff auf "documents" kann unsichtbare Dateien herunterladen.
     */
    public function testFileAccessAllowedWhenNotVisibleInFrontdoorForDocumentsAdmin()
    {
        $this->loginUser('security8', 'security8pwd');

        $model = new Frontdoor_Model_File(91, "frontdoor_invisible.txt");

        $realm = new MockRealm(true, true);

        $opusFile = $model->getFileObject($realm);

        $this->assertEquals(0, $opusFile->getVisibleInFrontdoor(), "Testdaten geändert.");
        $this->assertEquals("frontdoor_invisible.txt", $opusFile->getPathName(), "Testdaten geändert.");
    }

    /**
     * Administrator kann unsichtbare Dateien herunterladen.
     */
    public function testFileAccessAllowedWhenNotVisibleInFrontdoorForAdmin()
    {
        $this->loginUser('admin', 'adminadmin');

        $model = new Frontdoor_Model_File(91, "frontdoor_invisible.txt");

        $realm = new MockRealm(true, true);

        $opusFile = $model->getFileObject($realm);

        $this->assertEquals(0, $opusFile->getVisibleInFrontdoor(), "Testdaten geändert.");
        $this->assertEquals("frontdoor_invisible.txt", $opusFile->getPathName(), "Testdaten geändert.");
    }

    /**
     * Dateien dürfen nicht heruntergeladen werden, wenn das Embargo-Datum nicht vergangen ist.
     * Regressiontest for OPUSVIER-3313.
     */
    public function testAccessDeniedForEmbargoedDocument()
    {
        $file = $this->createOpusTestFile('test.pdf');
        $doc  = $this->createTestDocument();
        $doc->setServerState('published');
        $doc->addFile($file);

        $date = new Date();
        $date->setYear('2100')->setMonth('00')->setDay('01');
        $doc->setEmbargoDate($date);

        $docId = $doc->store();

        $model = new Frontdoor_Model_File($docId, "test.pdf");
        $realm = new MockRealm(true, true);

        $this->expectException(Frontdoor_Model_FileAccessNotAllowedException::class);
        $model->getFileObject($realm);
    }

    /**
     * Dateien dürfen vom DocumentsAdmin heruntergeladen werden, auch wenn das Embargo-Datum nicht vergangen ist.
     * Regressiontest for OPUSVIER-3313.
     */
    public function testAccessForEmbargoedDocumentForDocumentsAdmin()
    {
        $this->loginUser('security8', 'security8pwd');
        $file = $this->createOpusTestFile('test.pdf');
        $doc  = $this->createTestDocument();
        $doc->setServerState('published');
        $doc->addFile($file);

        $date = new Date();
        $date->setYear('2100')->setMonth('00')->setDay('01');
        $doc->setEmbargoDate($date);

        $docId = $doc->store();

        $model    = new Frontdoor_Model_File($docId, "test.pdf");
        $realm    = new MockRealm(true, true);
        $opusFile = $model->getFileObject($realm);

        $this->assertEquals("test.pdf", $opusFile->getPathName());
    }

    /**
     * Dateien dürfen vom Admin heruntergeladen werden, auch wenn das Embargo-Datum nicht vergangen ist.
     * Regressiontest for OPUSVIER-3313.
     */
    public function testAccessForEmbargoedDocumentForAdmin()
    {
        $this->loginUser('admin', 'adminadmin');
        $file = $this->createOpusTestFile('test.pdf');
        $doc  = $this->createTestDocument();
        $doc->setServerState('published');
        $doc->addFile($file);

        $date = new Date();
        $date->setYear('2100')->setMonth('00')->setDay('01');
        $doc->setEmbargoDate($date);

        $docId = $doc->store();

        $model = new Frontdoor_Model_File($docId, "test.pdf");
        $realm = new MockRealm(true, true);
        $file  = $model->getFileObject($realm);
        $this->assertEquals('test.pdf', $file->getPathName());
    }

    public function testGetFileObjectForUnpublishedFileForDocumentsAdmin()
    {
        $this->loginUser('security8', 'security8pwd');
        $file = $this->createOpusTestFile('test.pdf');
        $doc  = $this->createTestDocument();
        $doc->setServerState('unpublished');
        $doc->addFile($file);

        $date = new Date();
        $date->setYear('2100')->setMonth('00')->setDay('01');
        $doc->setEmbargoDate($date);

        $docId = $doc->store();

        $model    = new Frontdoor_Model_File($docId, "test.pdf");
        $realm    = new MockRealm(true, true);
        $opusFile = $model->getFileObject($realm);

        $this->assertEquals("test.pdf", $opusFile->getPathName());
    }
}
