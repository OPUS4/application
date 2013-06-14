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
 * @package     Tests
 * @author      Julian Heise <heise@zib.de>
 * @copyright   Copyright (c) 2008-2011, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class Frontdoor_Model_FileTest extends ControllerTestCase {

    const FILENAME = 'test.xhtml';
    const FILENAME_DELETED_DOC = 'foo.html';
    const FILENAME_UNPUBLISHED_DOC = 'bar.html';
    const EXPECTED_EXCEPTION = "Test failed: expected Exception";
    
    public function setUp() {
        parent::setUpWithEnv('production');
        $this->assertEquals(1, Zend_Registry::get('Zend_Config')->security);
        $this->assertTrue(Zend_Registry::isRegistered('Opus_Acl'), 'Expected registry key Opus_Acl to be set');
        $acl = Zend_Registry::get('Opus_Acl');
        $this->assertTrue($acl instanceof Zend_Acl, 'Expected instance of Zend_Acl');
    }

    public function testGetFileObjectSuccessfulCase() {
        $file = new Frontdoor_Model_File(92, self::FILENAME);
        $realm = new MockRealm(true, true);
        $opusFile = $file->getFileObject($realm);
        $this->assertEquals(self::FILENAME, $opusFile->getPathName());
    }

    /**
     * @expectedException Frontdoor_Model_DocumentNotFoundException
     */
    public function testGetFileObjectDocumentNotFoundException() {
        $file = new Frontdoor_Model_File(99999999999, self::FILENAME);
        $realm = new MockRealm(true, true);
        $opusFile = $file->getFileObject($realm);
    }

    /**
     * @expectedException Frontdoor_Model_DocumentDeletedException
     */
    public function testGetFileObjectDocumentDeletedExceptionIfDocForbidden() {
        $file = new Frontdoor_Model_File(123, self::FILENAME_DELETED_DOC);
        $realm = new MockRealm(true, false);
        $opusFile = $file->getFileObject($realm);
    }

    /**
     * @expectedException Frontdoor_Model_FileAccessNotAllowedException
     */
    public function testGetFileObjectFileAccessNotAllowedExceptionIfFileForbidden() {
        $file = new Frontdoor_Model_File(123, self::FILENAME_DELETED_DOC);
        $realm = new MockRealm(false, true);
        $opusFile = $file->getFileObject($realm);
    }

    public function testGetFileObjectNoDocumentDeletedExceptionIfAccessAllowed() {
        $file = new Frontdoor_Model_File(123, self::FILENAME_DELETED_DOC);
        $realm = new MockRealm(true, true);
        $opusFile = $file->getFileObject($realm);

        $this->assertTrue($opusFile instanceof Opus_File);
        $this->assertEquals(self::FILENAME_DELETED_DOC, $opusFile->getPathName());
    }

    public function testGetFileObjectAccessAllowedForUserWithAccessToDocumentsResource() {
        $this->markTestSkipped('Funktioniert noch nicht, da beim Login die ACLs (für Nutzer) schon geladen sind.');
        $this->loginUser('security8', 'security8pwd');
        $file = new Frontdoor_Model_File(92, self::FILENAME);
        $realm = new MockRealm(false, false); // sollte egal sein
        $opusFile = $file->getFileObject($realm);
        $this->assertTrue($opusFile instanceof Opus_File);
    }

    /**
     * @expectedException Frontdoor_Model_DocumentAccessNotAllowedException
     */
    public function testGetFileObjectDocumentAccessNotAllowedException() {
        $file = new Frontdoor_Model_File(124, self::FILENAME_UNPUBLISHED_DOC);
        $realm = new MockRealm(true, false);
        $opusFile = $file->getFileObject($realm);
    }

    /**
     * @expectedException Frontdoor_Model_FileNotFoundException
     */
    public function testGetFileObjectFileNotFoundException() {
        $file = new Frontdoor_Model_File(92, 'this_file_does_not_exist.file');
        $realm = new MockRealm(true, true);
        $opusFile = $file->getFileObject($realm);
    }

    /**
     * @expectedException Frontdoor_Model_FileAccessNotAllowedException
     */
    public function testGetFileObjectFileAccessNotAllowedException() {
        $file = new Frontdoor_Model_File(92, self::FILENAME);
        $realm = new MockRealm(false, true);
        $opusFile = $file->getFileObject($realm);
    }

    public function testConstructorDocIdEmpty() {
        try {
            new Frontdoor_Model_File("", "");
            $this->fail(self::EXPECTED_EXCEPTION);
        } catch(Frontdoor_Model_FrontdoorDeliveryException $e) {
            $this->assertEquals(
                    Frontdoor_Model_File::ILLEGAL_DOCID_MESSAGE_KEY,
                    $e->getTranslateKey());
        }
    }

    public function testConstructorDocIdNoNumber() {
        try {
            new Frontdoor_Model_File('xx', "");
            $this->fail(self::EXPECTED_EXCEPTION);
        } catch(Frontdoor_Model_FrontdoorDeliveryException $e) {
            $this->assertEquals(
                    Frontdoor_Model_File::ILLEGAL_DOCID_MESSAGE_KEY,
                    $e->getTranslateKey());
        }
    }

    public function testConstructorDocId() {
        try {
            new Frontdoor_Model_File(null, self::FILENAME);
            $this->fail(self::EXPECTED_EXCEPTION);
        } catch(Frontdoor_Model_FrontdoorDeliveryException $e) {
            $this->assertEquals(
                    Frontdoor_Model_File::ILLEGAL_DOCID_MESSAGE_KEY,
                    $e->getTranslateKey());
        }
    }

    public function testConstructorFilenameEmpty() {
        try {
            new Frontdoor_Model_File('1', '');
            $this->fail(self::EXPECTED_EXCEPTION);
        } catch(Frontdoor_Model_FrontdoorDeliveryException $e) {
            $this->assertEquals(
                    Frontdoor_Model_File::ILLEGAL_FILENAME_MESSAGE_KEY,
                    $e->getTranslateKey());
        }
    }

    public function testConstructorFilenameHigherLevelDir() {
        try {
            new Frontdoor_Model_File('1', '../*');
            $this->fail(self::EXPECTED_EXCEPTION);
        } catch(Frontdoor_Model_FrontdoorDeliveryException $e) {
            $this->assertEquals(
                    Frontdoor_Model_File::ILLEGAL_FILENAME_MESSAGE_KEY,
                    $e->getTranslateKey());
        }
    }

    /**
     * @expectedException Frontdoor_Model_DocumentAccessNotAllowedException
     */
    public function testWrongTypeOfRealmNoDocAccess() {
        $file = new Frontdoor_Model_File(124, self::FILENAME_UNPUBLISHED_DOC);
        $realm = 'this is an invalid realm object';
        $opusFile = $file->getFileObject($realm);
    }

    /**
     * @expectedException Frontdoor_Model_FileAccessNotAllowedException
     */
    public function testWrongTypeOfRealmNoFileAccess() {
        $file = new Frontdoor_Model_File(92, self::FILENAME);
        $realm = 'this is an invalid realm object';
        $opusFile = $file->getFileObject($realm);
    }
}

class MockRealm implements Opus_Security_IRealm {

    private $fileAllowed;
    private $docAllowed;

    public function  __construct($fileAllowed, $docAllowed) {
        $this->fileAllowed = $fileAllowed;
        $this->docAllowed = $docAllowed;
    }

    public function checkDocument($document_id = null) {
        return $this->docAllowed;
    }

    public function checkFile($file_id = null) {
        return $this->fileAllowed;
    }

    public function checkModule($module_name = null) {
        return true;
    }

    public function getRoles() {
        return array('guest');
    }

    public function setUser($username){}
    public function setIp($ipaddress){}
    public function check($privilege, $documentServerState = null, $fileId = null){}
    
}

