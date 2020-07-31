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
 * @package     Application_Controller_Action_Helper
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2019, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 *
 * TODO test checkFile
 */
class Application_Controller_Action_Helper_FilesTest extends ControllerTestCase
{

    private $helper;

    private $folder;

    private $localTestFiles = ['test.pdf', 'test.txt', 'test.png'];

    public function setUp()
    {
        parent::setUp();

        $this->helper = Zend_Controller_Action_HelperBroker::getStaticHelper('Files');

        $this->assertNotNull($this->helper, 'Files Action Helper not available.');

        $this->folder = APPLICATION_PATH . '/tests/workspace/incoming';

        foreach ($this->localTestFiles as $file) {
            file_put_contents($this->folder . '/' . $file, $file);
        }
    }

    public function tearDown()
    {
        foreach ($this->localTestFiles as $file) {
            unlink($this->folder . '/' . $file);
        }

        parent::tearDown();
    }

    public function testListFiles()
    {
        $files = $this->helper->listFiles($this->folder);

        $this->assertInternalType('array', $files);
        $this->assertEquals(2, count($files));

        foreach ($files as $file) {
            $fileName = $file['name'];
            $this->assertContains($fileName, ['test.pdf', 'test.txt'], "Unexpected file '$fileName'.");
        }
    }

    public function testListFilesAllFileTypes()
    {
        $files = $this->helper->listFiles($this->folder, true);

        $this->assertInternalType('array', $files);
        $this->assertEquals(3, count($files));

        foreach ($files as $file) {
            $fileName = $file['name'];
            $this->assertContains($fileName, $this->localTestFiles, "Unexpected file '$fileName'.");
        }
    }

    public function testGetAllowedFileTypes()
    {
        $method = new ReflectionMethod('Application_Controller_Action_Helper_Files', 'getAllowedFileTypes');
        $method->setAccessible(true);

        $fileTypes = $method->invoke($this->helper);

        $this->assertEquals(4, count($fileTypes));
        $this->assertEmpty(array_diff(['pdf', 'txt', 'html', 'htm'], $fileTypes));
    }
}
