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
 * @category    Tests
 * @package     Module_Setup
 * @author      Edouard Simon <edouard.simon@zib.de>
 * @copyright   Copyright (c) 2013-2018, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Class Setup_StaticPageControllerTest.
 *
 * @covers Setup_StaticPageController
 */
class Setup_StaticPageControllerTest extends SetupControllerTestCase {

    /**
     * original file modes, needed for restoring after test
     */
    protected $origFileModes = array();
    protected $configSection = 'static-page';

    public function testEditHomeSucceedsWithAccessPermissions() {
        $this->setPermissions('0700', '0700', '0700');
        $this->dispatch('/setup/static-page/edit/page/home');
        $this->assertResponseCode(200);
    }

    public function testEditHomeFailsWithoutWritePermissions() {
        $this->setPermissions('0500', '0400', '0000');
        $this->dispatch('/setup/static-page/edit/page/home');
        $this->assertResponseCode(302);
    }

    public function testEditHomeFailsWithoutDataReadPermissions() {
        $this->setPermissions('0000', '0400', '0000');
        $this->dispatch('/setup/static-page/edit/page/home');
        $this->assertResponseCode(302);
    }

    public function testEditContactSucceedsWithAccessPermissions() {
        $this->setPermissions('0700', '0700', '0700');
        $this->dispatch('/setup/static-page/edit/page/contact');
        $this->assertResponseCode(200);
    }

    public function testEditContactFailsWithoutWritePermissions() {
        $this->setPermissions('0500', '0400', '0000');
        $this->dispatch('/setup/static-page/edit/page/contact');
        $this->assertResponseCode(302);
    }

    public function testEditContactFailsWithoutDataReadPermissions() {
        $this->setPermissions('0000', '0400', '0000');
        $this->dispatch('/setup/static-page/edit/page/contact');
        $this->assertResponseCode(302);
    }
    
    public function testEditImprintSucceedsWithAccessPermissions() {
        $this->setPermissions('0700', '0700', '0700');
        $this->dispatch('/setup/static-page/edit/page/imprint');
        $this->assertResponseCode(200);
    }

    public function testEditImprintFailsWithoutWritePermissions() {
        $this->setPermissions('0500', '0400', '0000');
        $this->dispatch('/setup/static-page/edit/page/imprint');
        $this->assertResponseCode(302);
    }

    public function testEditImprintFailsWithoutDataReadPermissions() {
        $this->setPermissions('0000', '0400', '0000');
        $this->dispatch('/setup/static-page/edit/page/imprint');
        $this->assertResponseCode(302);
    }

    public function testExceptionThrownWithoutTmxReadPermissions() {
        $this->markTestSkipped('test results in false negative, needs debugging.');
        $this->setPermissions('0500', '0000', '0300');
        $this->dispatch('/setup/static-page/edit/page/home');
        $exceptions = $this->getResponse()->getExceptionByType('Setup_Model_Exception');
        $this->assertTrue(is_array($exceptions) && $exceptions[0] instanceOf Setup_Model_Exception);
        $this->assertEquals("No tmx data found.", $exceptions[0]->getMessage());
        $this->assertResponseCode(500);
    }

    /**
     * Set permissions for data base dir, translations source dirs and translation target dir
     */
    protected function setPermissions($contentBasepathPerms, $translationSourcesPerms, $translationTargetPerms) {
        $this->changeFileMode($this->config->contentBasepath, "$contentBasepathPerms");
        foreach ($this->config->translationSourcePaths->toArray() as $tmxSource) {
            if(is_dir($tmxSource))
                $this->changeFileMode($tmxSource, "$translationSourcesPerms");
        }
        // target file should not exist, make sure parent directory is accessible
        $this->assertFileExists($this->config->translationTargetPath);
        $this->changeFileMode($this->config->translationTargetPath, "$translationTargetPerms");
    }

}
