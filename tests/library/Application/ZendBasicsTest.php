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
 * @copyright   Copyright (c) 2019, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

class ZendBasicsTest extends ControllerTestCase
{
    /**
     * @return array[]
     */
    public function optionProvider()
    {
        return [
            ['option1', 'true', true],
            ['option2', true, true],
            ['option3', '1', true],
            ['option4', 1, true],
            ['option5', 'false', false],
            ['option6', false, false],
            ['option7', '0', false],
            ['option8', 0, false],
            ['option9', 'FaLsE', false],
            ['option10', 'tRuE', true],
            ['option11', self::CONFIG_VALUE_TRUE, true],
            ['option12', self::CONFIG_VALUE_FALSE, false],
        ];
    }

    /**
     * @dataProvider optionProvider
     * @param string $name
     * @param mixed  $value
     * @param bool   $result
     */
    public function testZendConfigBooleanOption($name, $value, $result)
    {
        $this->assertTrue(filter_var($value, FILTER_VALIDATE_BOOLEAN) === $result);
    }

    /**
     * @dataProvider optionProvider
     * @param string $name
     * @param mixed  $value
     * @param bool   $result
     */
    public function testZendConfigBooleanOptionLoadedFromIni($name, $value, $result)
    {
        $config  = $this->getConfig();
        $options = $config->tests->config;
        $value   = $options->$name;

        $this->assertTrue(filter_var($value, FILTER_VALIDATE_BOOLEAN) === $result);
    }

    public function testIssetForOptionIfConfigObjectIsNull()
    {
        $config = null; // this is on purpose

        $this->assertFalse(isset($config->test->option));
    }
}
