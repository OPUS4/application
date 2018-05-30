$this->_object->check()<?php
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
 * @package     Application_Configuration_Check
 * @author      Maximilian Salomon
 * @copyright   Copyright (c) 2018
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

class Application_Configuration_Check_WorkspaceTest extends ControllerTestCase
{
    private $_path;
    private $_directories;
    private $_object;

    public function setUp()
    {
        parent::setUp();
        $this->_path = uniqid(Application_Configuration::getInstance()->getWorkspacePath() . 'tmp' . '/');
        mkdir($this->_path);
        $this->_directories = array('cache', 'export', 'files', 'incoming', 'log', 'tmp', 'tmp/resumption');
        foreach ($this->_directories as $value) {
            mkdir($this->_path . '/' . $value);
        }
        $this->_object = new Application_Configuration_Check_Workspace();
    }

    public function tearDown()
    {
        Opus_Util_File::deleteDirectory($this->_path);
        parent::tearDown();
    }

    public function pathProvider()
    {
        return array(
            array('/' . 'cache'),
            array('/' . 'export'),
            array('/' . 'files'),
            array('/' . 'incoming'),
            array('/' . 'log'),
            array('/' . 'tmp'),
            array('/' . 'tmp/resumption')
        );
    }

    public function testCheck()
    {
        $this->_object->setWorkspacePath($this->_path);
        $this->assertTrue($this->_object->check());
    }

    /**
     * @dataProvider pathProvider
     */
    public function testCheckMissingPath($arg)
    {
        $this->_object->setWorkspacePath($this->_path);
        Opus_Util_File::deleteDirectory($this->_path . $arg);
        $this->assertFalse($this->_object->check());
        mkdir($this->_path . $arg);
    }

    /**
     * @dataProvider pathProvider
     */
    public function testCheckMissingReadpermissions($arg)
    {
        chmod($this->_path . $arg, 0333);
        $this->_object->setWorkspacePath($this->_path);
        $this->assertFalse($this->_object->check());
        chmod($this->_path . $arg, 0777);
    }

    /**
     * @dataProvider pathProvider
     */
    public function testCheckMissingWritepermissions($arg)
    {
        chmod($this->_path . $arg, 0444);
        $this->_object->setWorkspacePath($this->_path);
        $this->assertFalse($this->_object->check());
        chmod($this->_path . $arg, 0777);
    }

    public function testCheckMessages()
    {
        chmod($this->_path . '/' . 'cache', 0444);
        Opus_Util_File::deleteDirectory($this->_path . '/' . 'tmp/resumption');
        $this->_object->setWorkspacePath($this->_path);
        $this->assertFalse($this->_object->check());
        $key = $this->_object->getErrors();
        foreach ($key as $value) {
            $message[] = $this->_object->getMessage($value);
        }
        $this->assertContains($key[0], "permission");
        $this->assertContains($message[1], "Some workspace-directories do not exist.");
        chmod($this->_path . '/' . 'cache', 0777);
        mkdir($this->_path . '/' . 'tmp/resumption');
    }

}