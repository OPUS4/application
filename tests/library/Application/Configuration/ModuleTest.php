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
 * @copyright   Copyright (c) 2017, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

class Application_Configuration_ModuleTest extends ControllerTestCase
{
    /** @var string */
    protected $additionalResources = 'database';

    public function testIsRegistered()
    {
        $module = new Application_Configuration_Module('frontdoor');

        $this->assertFalse($module->isRegistered());

        Application_Modules::registerModule($module);

        $this->assertTrue($module->isRegistered());
    }

    public function testConstruct()
    {
        $module = new Application_Configuration_Module('frontdoor');

        $this->assertEquals('frontdoor', $module->getName());
        $this->assertNull($module->getDescription());
    }

    public function testConstructWithDescription()
    {
        $module = new Application_Configuration_Module('frontdoor', 'Shows document metadata.');

        $this->assertEquals('frontdoor', $module->getName());
        $this->assertEquals('Shows document metadata.', $module->getDescription());
    }

    public function testIsPublic()
    {
        $module = new Application_Configuration_Module('frontdoor');

        $this->assertTrue($module->isPublic());

        $module = new Application_Configuration_Module('admin');

        $this->assertFalse($module->isPublic());
    }

    public function testIsPublicBadName()
    {
        $module = new Application_Configuration_Module('badname');

        $this->assertFalse($module->isPublic());
    }
}
