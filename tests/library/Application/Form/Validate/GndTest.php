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
        $this->validator = new Application_Form_Validate_Gnd();

        parent::setUp();
    }

    public function testIsValidFalseFormat()
    {
        $this->assertFalse($this->validator->isValid(''));
        $this->assertFalse($this->validator->isValid('Hallo'));
        $this->assertFalse($this->validator->isValid('12345AB--6789012'));
        $this->assertFalse($this->validator->isValid('123456789012'));
        $this->assertFalse($this->validator->isValid('009598X4798'));
        $this->assertFalse($this->validator->isValid('0095980479X'));
        $this->assertFalse($this->validator->isValid('00040303187'));
        $this->assertArrayHasKey('notValidFormat', $this->validator->getMessages());
        $this->assertCount(1, $this->validator->getMessages());
    }

    public function testIsValidFalseChecksum()
    {
        $this->assertFalse($this->validator->isValid('118768582'));
        $this->assertFalse($this->validator->isValid('959804798'));
        $this->assertArrayHasKey('notValidChecksum', $this->validator->getMessages());
        $this->assertCount(1, $this->validator->getMessages());
    }

    public function testIsValidTrue()
    {
        $this->assertTrue($this->validator->isValid('118768581'));
        $this->assertTrue($this->validator->isValid('95980479X'));
        $this->assertTrue($this->validator->isValid('40303187'));
        $this->assertTrue($this->validator->isValid('123050421')); // Spinner, Kasper H.
        $this->assertTrue($this->validator->isValid('136704425')); // Süselbeck, Kirsten
    }

    public function testIsValidTrueForShortNumber()
    {
        $this->assertTrue($this->validator->isValid('136307396'));
        $this->assertTrue($this->validator->isValid('40303187'));
    }

    public function testGenerateCheckDigit()
    {
        $digit = Application_Form_Validate_Gnd::generateCheckDigit('0095980479');
        $this->assertEquals('X', $digit);

        $digit = Application_Form_Validate_Gnd::generateCheckDigit('4030318');
        $this->assertEquals('7', $digit);
    }

    public function testNoLeadingZerosAllowed()
    {
        $this->assertFalse($this->validator->isValid('00040303187'));
        $this->assertFalse($this->validator->isValid('0095980479X'));
        $this->assertFalse($this->validator->isValid('00040303187'));
    }
}
