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
 * @author      Edouard Simon <edouard.simon@zib.de>
 * @copyright   Copyright (c) 2008-2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
class Setup_Model_AbstractTest extends ControllerTestCase {

    protected $object;
    protected $testFile;

    public function setUp() {
        parent::setUp();
        $this->object = $this->getMockForAbstractClass("Setup_Model_Abstract");
        $this->object->expects($this->any())
                ->method('toArray')
                ->will($this->returnValue(array()));
        $this->object->expects($this->any())
                ->method('fromArray')
                ->will($this->returnValue(true));
        $this->testFile = APPLICATION_PATH . '/tests/workspace/tmp/setuptest.txt';
        $touched = touch($this->testFile);
        $this->assertTrue($touched, "Failed creating test file '{$this->testFile}'");
    }

    public function tearDown() {
        parent::tearDown();
        unlink($this->testFile);
    }

    public function testSetConfigWithInvalidParams() {
        $this->setExpectedException('Setup_Model_Exception');
        $this->object->setConfig(array('bogusConfigOption' => 'Invalid Config Value'));
    }

    public function testSetConfigWithValidParams() {
        chmod($this->testFile, 0600);
        file_put_contents($this->testFile, "Test Data");
        $this->object->setConfig(array('contentSources' => array($this->testFile)));
        $this->assertEquals("Test Data", $this->object->getContent($this->testFile));
    }

    public function testVerifyReadAccess() {
        chmod($this->testFile, 0400);
        try {
            $this->object->verifyReadAccess($this->testFile);
        } catch (Setup_Model_FileNotReadableException $exc) {
            $this->fail('Unexpected Setup_Model_FileNotReadableException');
        }
        chmod($this->testFile, 0000);
        try {
            $this->object->verifyReadAccess($this->testFile);
            $this->fail('Expected Setup_Model_FileNotReadableException');
        } catch (Setup_Model_FileNotReadableException $exc) {
            
        }
    }

    public function testVerifyWriteAccess() {
        chmod($this->testFile, 0200);
        try {
            $this->object->verifyWriteAccess($this->testFile);
        } catch (Setup_Model_FileNotReadableException $exc) {
            $this->fail('Unexpected Setup_Model_FileNotWriteableException');
        }
        chmod($this->testFile, 0000);
        try {
            $this->object->verifyReadAccess($this->testFile);
            $this->fail('Expected Setup_Model_FileNotWriteableException');
        } catch (Setup_Model_FileNotReadableException $exc) {
            
        }
    }

    public function testWriteData() {
        chmod($this->testFile, 0600);
        $this->object->addContentSource($this->testFile);
        $this->object->setContent(array($this->testFile => "Test Data"));
        $this->object->store();
        $this->assertEquals('Test Data', file_get_contents($this->testFile));
    }

    public function testReadWriteTmx() {
        $tmxSourceFile = APPLICATION_PATH . "/tests/test.tmx";
        $tmxTargetFile = APPLICATION_PATH . "/tests/workspace/tmp/test.tmx";
        $this->object->setTranslationSources(array($tmxSourceFile));
        $this->object->setTranslationTarget($tmxTargetFile);
        $translationArray = $this->object->getTranslation();
        $this->assertEquals(array('home_index_contact_pagetitle', 'home_index_contact_title'), array_keys($translationArray));
        $translationArray['test_key'] = array('de' => 'Test (deutsch)', 'en' => 'Test (english)');
        $this->object->setTranslation($translationArray, false);
        $this->object->store();
        $this->assertFileExists($tmxTargetFile);
        $tmxDom = new DomDocument();
        $tmxDom->load($tmxTargetFile);
        $tuElements = $tmxDom->getElementsByTagName('tu');
        $this->assertEquals(3, $tuElements->length, "Expected 3 elements in DomNodeList");
        foreach ($tuElements as $tu) {
            $this->assertArrayHasKey($tu->attributes->getNamedItem('tuid')->nodeValue, $translationArray);
        }
        unlink($tmxTargetFile);
    }

    public function testGetTranslationDiff() {
        $tmxSourceFile = APPLICATION_PATH . "/tests/test.tmx";
        $tmxTargetFile = APPLICATION_PATH . "/tests/workspace/tmp/test.tmx";
        $this->object->setTranslationSources(array($tmxSourceFile));
        $this->object->setTranslationTarget($tmxTargetFile);


        $changedTranslation = array('home_index_contact_pagetitle' => array(
                'de' => 'Kontaktaufnahme',
                ));

        $expectedChangeDiff = array(
            'home_index_contact_pagetitle' =>
            array(
                'en' => 'Contact',
                'de' => 'Kontaktaufnahme',
                ));

        $this->assertEquals($expectedChangeDiff, $this->object->getTranslationDiff($changedTranslation));



        $expectedAddDiff = $addedTranslation = array('test_unit' =>
            array('de' => 'testen'),
            array('en' => 'to test')
        );

        $this->assertEquals($expectedAddDiff, $this->object->getTranslationDiff($addedTranslation));

//        var_export($this->object->getTranslationDiff($changed));
//        var_export($this->object->getTranslationDiff(array('test_unit' => array('de' => 'fritz'))));
    }

}
