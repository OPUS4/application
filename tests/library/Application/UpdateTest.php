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
 * @package     Application
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2017, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

class Application_UpdateTest extends ControllerTestCase
{

    public function testGetUpdateScripts()
    {
        $update = new Application_Update();

        $scripts = $update->getUpdateScripts();

        $this->assertGreaterThan(2, $scripts);

        $this->assertContains(APPLICATION_PATH . '/scripts/update/001-Add-import-collection.php', $scripts);
        $this->assertContains(APPLICATION_PATH . '/scripts/update/002-Add-CC-4.0-licences.php', $scripts);
    }

    public function testGetUpdateScriptsSorting()
    {
        $update = new Application_Update();

        $scripts = $update->getUpdateScripts();

        $this->assertNotNull($scripts);
        $this->assertInternalType('array', $scripts);
        $this->assertGreaterThan(1, count($scripts));

        $lastNumber = 0;

        foreach ($scripts as $script) {
            $number = substr(basename($script), 0, 3);
            $this->assertGreaterThan($lastNumber, $number);
            $lastNumber = $number;
        }
    }

    public function testGetUpdateScriptsFromVersion()
    {
        $update = new Application_Update();

        $scripts = $update->getUpdateScripts(1);

        $this->assertGreaterThan(1, $scripts);

        $this->assertNotContains(APPLICATION_PATH . '/scripts/update/001-Add-import-collection.php', $scripts);
        $this->assertContains(APPLICATION_PATH . '/scripts/update/002-Add-CC-4.0-licences.php', $scripts);
    }

    public function testGetUpdateScriptsForTargetVersion()
    {
        $update = new Application_Update();

        $scripts = $update->getUpdateScripts(null, 2);

        $this->assertCount(2, $scripts);

        $this->assertContains(APPLICATION_PATH . '/scripts/update/001-Add-import-collection.php', $scripts);
        $this->assertContains(APPLICATION_PATH . '/scripts/update/002-Add-CC-4.0-licences.php', $scripts);
    }

    public function testGetUpdateScriptsFromVersionToTargetVersion()
    {
        $update = new Application_Update();

        $scripts = $update->getUpdateScripts(1, 2);

        $this->assertCount(1, $scripts);

        $this->assertContains(APPLICATION_PATH . '/scripts/update/002-Add-CC-4.0-licences.php', $scripts);
    }

    public function testGetVersion()
    {
        $update = new Application_Update();

        $version = $update->getVersion();

        $this->assertNotNull($version);
        $this->assertInternalType('int', $version);
        $this->assertGreaterThanOrEqual(2, $version);
    }

    public function testSetVersion()
    {
        $update = new Application_Update();

        $version = $update->getVersion();

        $this->assertNotNull($version);

        $update->setVersion(999);

        $newVersion = $update->getVersion();

        $update->setVersion($version);

        $this->assertEquals(999, $newVersion);
    }

    public function updateScriptProvider()
    {
        $update = new Application_Update();
        $scripts = $update->getUpdateScripts();

        $data = array_map(function ($script) {
            return [$script];
        }, $scripts);

        return $data;
    }

    /**
     * @dataProvider updateScriptProvider
     */
    public function testUpdateScriptsExecutable($script)
    {
        $filename = basename($script);

        $this->assertFileExists($script);
        $this->assertTrue(is_executable($script), "Script \"$filename\" not executable.");
    }
}
