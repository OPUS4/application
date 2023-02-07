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

class Application_Util_ShellScriptTest extends ControllerTestCase
{
    /** @var string */
    private $scriptPath;

    public function setUp(): void
    {
        parent::setUp();

        $this->scriptPath = APPLICATION_PATH . '/tests/resources/shellscript.txt';
    }

    public function testGetPropertiesFromScript()
    {
        $properties = Application_Util_ShellScript::getPropertiesFromScript($this->scriptPath);

        $this->assertCount(8, $properties);

        $this->assertArrayHasKey('user', $properties);
        $this->assertEquals('opus4admin', $properties['user']);

        $this->assertArrayHasKey('password', $properties);
        $this->assertEquals('abc456%pwd', $properties['password']);

        $this->assertArrayHasKey('port', $properties);
        $this->assertEquals('3308', $properties['port']);

        $this->assertArrayHasKey('dbname', $properties);
        $this->assertEquals('opusdb', $properties['dbname']);

        $this->assertArrayHasKey('mysql_bin', $properties);
        $this->assertEquals('/usr/bin/mysql', $properties['mysql_bin']);

        $this->assertArrayHasKey('schema_file', $properties);
        $this->assertEquals('schema/opus4schema.sql', $properties['schema_file']);

        $this->assertArrayHasKey('master_dir', $properties);
        $this->assertEquals('masterdata/', $properties['master_dir']);

        $this->assertArrayHasKey('mysql', $properties);
        $this->assertEquals('${mysql} --password=`printf %q "${password}"`', $properties['mysql']);
    }
}
