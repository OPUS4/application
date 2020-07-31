<?php
/*
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
 * @author      Michael Lang <lang@zib.de>
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2014, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class Application_Form_Validate_OrcidTest extends ControllerTestCase
{

    private $_validator;

    public function setUp()
    {
        parent::setUp();
        $this->_validator = new Application_Form_Validate_Orcid();
    }

    public function testIsValidFalseFormat()
    {
        $this->assertFalse($this->_validator->isValid(''));
        $this->assertFalse($this->_validator->isValid('Hallo'));
        $this->assertFalse($this->_validator->isValid('1234567890'));
        $this->assertFalse($this->_validator->isValid('0000000218250097'));
        $this->assertFalse($this->_validator->isValid('0000-00X0-0000-0000'));
        $this->assertArrayHasKey('notValidFormat', $this->_validator->getMessages());
        $this->assertCount(1, $this->_validator->getMessages());
    }

    public function testIsValidFalseChecksum()
    {
        $this->assertFalse($this->_validator->isValid('0000-0002-1825-009X'));
        $this->assertArrayHasKey('notValidChecksum', $this->_validator->getMessages());
        $this->assertCount(1, $this->_validator->getMessages());
    }

    public function testIsValidTrue()
    {
        $this->assertTrue($this->_validator->isValid('0000-0002-1825-0097'));
        $this->assertTrue($this->_validator->isValid('0000-0002-1825-010X'));
    }

    public function testGenerateCheckDigit()
    {
        $this->assertEquals('7', Application_Form_Validate_Orcid::generateCheckDigit('0000-0002-1825-009'));
        $this->assertEquals('X', Application_Form_Validate_Orcid::generateCheckDigit('0000-0002-1825-010'));
    }
}
