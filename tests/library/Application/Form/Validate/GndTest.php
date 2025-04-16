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

class Application_Form_Validate_GndTest extends TestCase
{
    /** @var Application_Form_Validate_Gnd */
    private $validator;

    public function setUp(): void
    {
        parent::setUp();

        $this->validator = new Application_Form_Validate_Gnd();
    }

    /**
     * @return array[]
     */
    public static function invalidFormatGndProvider()
    {
        return [
            [''],
            ['Hallo'],
            ['12345AB--6789012'],
            ['123456789012'],
            ['009598X4798'],
            ['0095980479X'],
            ['00040303187'],
        ];
    }

    /**
     * @param string $value
     * @dataProvider invalidFormatGndProvider
     */
    public function testIsValidFalseFormat($value)
    {
        $this->assertFalse($this->validator->isValid($value));
        $this->assertArrayHasKey('notValidFormat', $this->validator->getMessages());
        $this->assertCount(1, $this->validator->getMessages());
    }

    /**
     * @return array[]
     */
    public static function invalidGndProvider()
    {
        return [
            ['118768582'],
            ['959804798'],
        ];
    }

    /**
     * @param string $value
     * @dataProvider invalidGndProvider
     */
    public function testIsValidFalseChecksum($value)
    {
        $this->assertFalse($this->validator->isValid($value));
        $this->assertArrayHasKey('notValidChecksum', $this->validator->getMessages());
        $this->assertCount(1, $this->validator->getMessages());
    }

    /**
     * @return array[]
     */
    public static function validGndProvider()
    {
        return [
            ['118768581'],
            ['95980479X'],
            ['40303187'],
            ['123050421'], // Spinner, Kasper H.
            ['136704425'], // Süselbeck, Kirsten
            ['136307396'],
            ['4034724-2'],
            ['4030318-7'],
        ];
    }

    /**
     * @param string $gndValue
     * @dataProvider validGndProvider
     */
    public function testIsValidTrue($gndValue)
    {
        $this->assertTrue($this->validator->isValid($gndValue));
    }

    public function testGenerateCheckDigit()
    {
        $validator = new Application_Form_Validate_Gnd();
        $digit     = $validator->generateCheckDigit('0095980479');
        $this->assertEquals('X', $digit);

        $digit = $validator->generateCheckDigit('4030318');
        $this->assertEquals('7', $digit);
    }
}
