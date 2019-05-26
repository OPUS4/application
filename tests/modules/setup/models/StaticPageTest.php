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
 * @package     Module_Setup
 * @author      Edouard Simon <edouard.simon@zib.de>
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2013-2018, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 *
 */
class Setup_Model_StaticPageTest extends ControllerTestCase
{

    protected $object;

    protected $contentBasePath = APPLICATION_PATH . '/workspace/tmp';

    protected $contentFiles = ['de' => 'test.de.txt', 'en' => 'test.en.txt'];

    protected $tmxTarget = 'workspace/tmp/test.tmx';

    public function setUp()
    {
        parent::setUp();

        foreach ($this->contentFiles as $contentFile) {
            $this->createFile($this->contentBasePath . DIRECTORY_SEPARATOR . $contentFile);
        }

        $testConfig = [
            'contentBasepath' => $this->contentBasePath,
            'translationTarget' => APPLICATION_PATH . DIRECTORY_SEPARATOR . $this->tmxTarget,
            'translationSources' => [APPLICATION_PATH . DIRECTORY_SEPARATOR . $this->tmxTarget]
        ];

        $this->object = new Setup_Model_StaticPage('test', $testConfig);
    }

    public function tearDown()
    {
        parent::tearDown();
        foreach ($this->contentFiles as $contentFile) {
            unlink($this->contentBasePath . DIRECTORY_SEPARATOR . $contentFile);
        }
        if (is_file(APPLICATION_PATH . DIRECTORY_SEPARATOR . $this->tmxTarget))
            unlink(APPLICATION_PATH . DIRECTORY_SEPARATOR . $this->tmxTarget);
    }

    public function testToArray()
    {
        $tmxFile = new Application_Translate_TmxFile();
        $tmxFile->setTranslation('test_translation_unit', 'de', 'Testübersetzung');
        $tmxFile->setTranslation('test_translation_unit', 'en', 'Test translation');
        $tmxFile->save(APPLICATION_PATH . DIRECTORY_SEPARATOR . $this->tmxTarget);

        foreach ($this->contentFiles as $contentFile) {
            file_put_contents($this->contentBasePath . DIRECTORY_SEPARATOR . $contentFile, 'Test Data');
        }

        $array = $this->object->toArray();

        foreach ($this->contentFiles as $lang => $contentFile) {
//            $this->assertTrue
            $this->assertEquals(
                $contentFile, $array[$lang]['file']['filename'], "Expected file '$contentFile' in array."
            );
            $this->assertEquals('Test Data', $array[$lang]['file']['contents']);
        }

        $this->assertTrue(
            isset($array['de']['key']['test_translation_unit']),
            "Expected translation unit 'test_translation_unit'"
        );
        $this->assertTrue(
            isset($array['en']['key']['test_translation_unit']),
            "Expected translation unit 'test_translation_unit'"
        );
        $this->assertEquals('Testübersetzung', $array['de']['key']['test_translation_unit']);
        $this->assertEquals('Test translation', $array['en']['key']['test_translation_unit']);
    }

    public function testFromArray()
    {
        $data = [
            'de' => [
                'file' => [
                    'filename' => 'test.de.txt',
                    'contents' => 'Testdaten',
                ],
                'key' => [
                    'test_translation_unit' => 'Testübersetzung',
                ],
            ],
            'en' => [
                'file' => [
                    'filename' => 'test.en.txt',
                    'contents' => 'Test Data',
                ],
                'key' => [
                    'test_translation_unit' => 'Test translation',
                ],
            ],
        ];

        $this->object->fromArray($data);

        if (!$this->object->store()) {
            $this->fail("storing failed");
        }
        $tmxFile = new Application_Translate_TmxFile();
        $tmxFile->load(APPLICATION_PATH . DIRECTORY_SEPARATOR . $this->tmxTarget);
        $tmxArray = $tmxFile->toArray();

        $this->assertTrue(
            isset($tmxArray['test_translation_unit']),
            "Expected translation unit 'test_translation_unit'"
        );

        $this->assertEquals('Testübersetzung', $tmxArray['test_translation_unit']['de']);
        $this->assertEquals('Test translation', $tmxArray['test_translation_unit']['en']);

        $this->assertEquals(
            'Testdaten',
            file_get_contents($this->contentBasePath . DIRECTORY_SEPARATOR . 'test.de.txt')
        );

        $this->assertEquals(
            'Test Data',
            file_get_contents($this->contentBasePath . DIRECTORY_SEPARATOR . 'test.en.txt')
        );
    }

    /**
     * Regression Test for OPUSVIER-2908
     */
    public function testUnsetUseContentFile()
    {
        $fileArray = $this->object->toArray();
        $this->assertTrue(isset($fileArray['en']['file']));
        $this->assertTrue(isset($fileArray['de']['file']));
        $this->object->setUseContentFile(false);
        $noFileArray = $this->object->toArray();
        $this->assertFalse(isset($noFileArray['en']['file']));
        $this->assertFalse(isset($noFileArray['de']['file']));
    }

    protected function createFile($name)
    {
        $touched = touch($name);
        $this->assertTrue(($touched && is_file($name)), "Failed creating test file '$name'");
    }
}
