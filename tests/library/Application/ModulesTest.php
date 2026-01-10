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

use Opus\App\Common\Config\Module;
use Opus\App\Common\Modules;

/**
 * TODO move test to opus4-app-common (however with Laminas the Modules class becomes obsolete)
 */
class Application_ModulesTest extends ControllerTestCase
{
    public function testGetInstance()
    {
        $modules = Modules::getInstance();

        $this->assertNotNull($modules);
        $this->assertInstanceOf(Modules::class, $modules);

        $this->assertSame($modules, Modules::getInstance());
    }

    public function testRegisterModule()
    {
        Modules::setInstance(null);

        $module = new Module('frontdoor');

        $this->assertFalse(Modules::getInstance()->isRegistered('frontdoor'));

        Modules::registerModule($module);

        $this->assertTrue(Modules::getInstance()->isRegistered('frontdoor'));

        Modules::setInstance(null);
    }

    public function testGetModulesPath()
    {
        $path = Modules::getInstance()->getModulesPath();

        $this->assertEquals(APPLICATION_PATH . DIRECTORY_SEPARATOR . 'modules', $path);
    }

    public function testGetModules()
    {
        $modules = Modules::getInstance()->getModules();

        $this->assertCount(18, $modules);

        // some expected modules
        $expectedModules = ['admin', 'frontdoor', 'default', 'export', 'publish', 'solrsearch'];

        foreach ($expectedModules as $name) {
            $this->assertArrayHasKey($name, $modules, "Module [$name] is missing");
            $this->assertInstanceOf(Module::class, $modules[$name]);
        }
    }

    public function testIsPublic()
    {
        $module = new Module('frontdoor');

        $this->assertTrue($module->isPublic());

        $module = new Module('admin');

        $this->assertFalse($module->isPublic());
    }

    public function testIsPublicBadName()
    {
        $module = new Module('badname');

        $this->assertFalse($module->isPublic());
    }
}
