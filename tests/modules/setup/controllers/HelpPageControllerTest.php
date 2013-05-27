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
 * @package     Module_Solrsearch
 * @author      Sascha Szott <szott@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
class Setup_HelpPageControllerTest extends ControllerTestCase {

    /**
     * original file modes, needed for restoring after test
     */
    protected $origFileModes = array();
    protected $config;

    public function setUp() {
        parent::setUp();
        $this->config = new Zend_Config_Ini(APPLICATION_PATH . '/modules/setup/setup.ini', 'help');
    }

    public function tearDown() {
        parent::tearDown();
        $this->resetFileModes();
    }

    public function testIndexSucceedsWithAccessPermissions() {
        $this->setPermissions('0500', '0400', '0300');
        $this->dispatch('/setup/help-page/edit');
        $this->assertResponseCode(200);
    }

    public function testIndexFailsWithoutWritePermissions() {
        $this->setPermissions('0500', '0400', '0000');
        $this->dispatch('/setup/help-page/edit');
        $this->assertResponseCode(302);
    }

    public function testIndexFailsWithoutDataReadPermissions() {
        $this->setPermissions('0000', '0400', '0000');
        $this->dispatch('/setup/help-page/edit');
        $this->assertResponseCode(302);
    }

    public function testExceptionThrownWithoutTmxReadPermissions() {
        $this->setPermissions('0500', '0000', '0300');
        $this->dispatch('/setup/help-page/edit');
        $exceptions = $this->getResponse()->getExceptionByType('Setup_Model_Exception');
        $this->assertTrue(is_array($exceptions) && $exceptions[0] instanceOf Setup_Model_Exception);
        $this->assertEquals("No tmx data found.", $exceptions[0]->getMessage());
        $this->assertResponseCode(500);
    }

    /**
     * Set permissions for data base dir, translations sources and translation target dir
     */
    protected function setPermissions($contentBasepathPerms, $translationSourcesPerms, $translationTargetPerms) {
        $this->changeFileMode($this->config->contentBasepath, "$contentBasepathPerms");
        foreach ($this->config->translationSources->toArray() as $tmxSource) {
            if (file_exists($tmxSource))
                $this->changeFileMode($tmxSource, "$translationSourcesPerms");
        }
        // target file should not exist, make sure parent directory is accessible
        $this->assertFileNotExists($this->config->translationTarget, 'test data changed');
        $targetDir = dirname($this->config->translationTarget);
        $this->assertFileExists($targetDir);
        $this->changeFileMode($targetDir, "$translationTargetPerms");
    }

    protected function changeFileMode($path, $mode) {
        if (!(is_file($path) || is_dir($path))) {
            $this->fail("File or Directory $path not found");
        }
        if (!isset($this->origFileModes[$path]))
            $this->origFileModes[$path] = substr(decoct(fileperms($path)), -4);
        @chmod($path, octdec("$mode"));
        clearstatcache();
        $this->assertEquals("$mode", substr(decoct(fileperms($path)), -4), "Failed setting mode $mode for $path");
    }

    protected function resetFileModes() {
        foreach ($this->origFileModes as $path => $mode) {
            $this->changeFileMode($path, $mode);
        }
    }

}
