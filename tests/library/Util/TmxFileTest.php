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
 * @category    Tmx
 * @package     Util
 * @author      Edouard Simon (edouard.simon@zib.de)
 * @copyright   Copyright (c) 2008-2012, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * 
 */
class TmxFileTest extends ControllerTestCase {

    protected $testFile;

    public function setUp() {
        parent::setUp();
        $this->testFile = APPLICATION_PATH . "/tests/test.tmx";
        $this->assertTrue(is_file($this->testFile), 'Test data changed');
    }

    public function testLoad() {
        $tmxFile = new Util_TmxFile($this->testFile);
        $tmxArray = $tmxFile->toArray();

        $this->assertArrayHasKey('home_index_contact_pagetitle', $tmxArray);
        $this->assertArrayHasKey('home_index_contact_title', $tmxArray);
    }

    public function testLoadMultipleFiles() {
        $file1Path = APPLICATION_PATH . DIRECTORY_SEPARATOR . "tests/workspace/tmp/test1.tmx";
        $tmxFile1 = new Util_TmxFile($this->testFile);
        $tmxFile1->setVariantSegment('test_unit', 'de', 'Testdaten');
        $tmxFile1->setVariantSegment('test_unit', 'en', 'Test Data');
        $tmxFile1->save($file1Path);

        $tmxFile = new Util_TmxFile($this->testFile);
        $tmxFile->load($file1Path);
        $tmxArray = $tmxFile->toArray();

        $this->assertArrayHasKey('home_index_contact_pagetitle', $tmxArray);
        $this->assertArrayHasKey('home_index_contact_title', $tmxArray);
        $this->assertArrayHasKey('test_unit', $tmxArray);
        unlink($file1Path);
    }

    public function testSave() {
        $tmpFilename = APPLICATION_PATH . "/tests/workspace/tmp/test.tmx";
        $tmxFile = new Util_TmxFile($this->testFile);
        $tmxFile->save($tmpFilename);
        $this->assertTrue(file_exists($tmpFilename));
        $savedFile = new Util_TmxFile($tmpFilename);
        $this->assertEquals($tmxFile->toArray(), $savedFile->toArray());
    }

    public function testToDomDocument() {
        $tmxFile = new Util_TmxFile($this->testFile);
        $tmxDom = $tmxFile->toDomDocument();
        $tuElements = $tmxDom->getElementsByTagName('tu');

        $expectedTuids = array('home_index_contact_pagetitle', 'home_index_contact_title');
        for ($i = 0; $i < 2; $i++) {
            $this->assertEquals($expectedTuids[$i], $tuElements->item($i)->getAttribute('tuid'));
        }
    }

    public function testSetVariantSegment() {
        $tmxFile = new Util_TmxFile();
        $tmxFile->setVariantSegment('test_unit', 'de', 'Test Deutsch');
        $tmxFile->setVariantSegment('test_unit', 'en', 'Test English');
        $tmxArray = $tmxFile->toArray();
        $this->assertArrayHasKey('test_unit', $tmxArray);
        $this->assertEquals(array('de', 'en'), array_keys($tmxArray['test_unit']));
        $this->assertEquals('Test Deutsch', $tmxArray['test_unit']['de']);
        $this->assertEquals('Test English', $tmxArray['test_unit']['en']);
    }

    public function testFromArray() {
        $tmxSource = new Util_TmxFile($this->testFile);

        $tmxArray = $tmxSource->toArray();

        $this->assertArrayHasKey('home_index_contact_pagetitle', $tmxArray);
        $this->assertArrayHasKey('home_index_contact_title', $tmxArray);

        $tmxFile = new Util_TmxFile();

        $tmxFile->fromArray($tmxArray);

        $this->assertEquals($tmxArray, $tmxFile->toArray());
    }

    public function testFromMultipleArrays() {
        $tmxSource1 = new Util_TmxFile($this->testFile);
        $tmxArray = $tmxSource1->toArray();
        $this->assertArrayHasKey('home_index_contact_pagetitle', $tmxArray);
        $this->assertArrayHasKey('home_index_contact_title', $tmxArray);

        $tmxArray2 = array('test_unit' => array(
                'de' => 'Test Deutsch',
                'en' => 'Test English'
                ));
        $tmxFile = new Util_TmxFile();
        $tmxFile->fromArray($tmxArray)
                ->fromArray($tmxArray2);
        
        $tmxResultArray = $tmxFile->toArray();
        $this->assertArrayHasKey('home_index_contact_pagetitle', $tmxResultArray);
        $this->assertArrayHasKey('home_index_contact_title', $tmxResultArray);
        $this->assertArrayHasKey('test_unit', $tmxResultArray);
    }

}
