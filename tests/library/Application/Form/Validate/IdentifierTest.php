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
 * @author      Maximilian Salomon <salomon@zib.de>
 * @copyright   Copyright (c) 2017, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Unittests for  Class Application_Form_Validate_IdentifierTest
 * @coversDefaultClass Application_Form_Validate_Identifier
 */
class Application_Form_Validate_IdentifierTest extends ControllerTestCase
{
    /**
     * Represents an validator-object for identifier-elements.
     */
    private $_validator;

    /**
     * Represents an Zend_Form_Element for identifier with type ISBN.
     */
    private $_element;

    /**
     * set up variables.
     */
    public function setUp()
    {
        parent::setUp();
        $this->_element = new Application_Form_Element_Identifier('Element');
        $this->_element->setValue('ISBN');
        $this->_validator = new Application_Form_Validate_Identifier($this->_element);
    }

    /**
     * Test for an empty argument in an ISBN-identifier.
     * @covers ::isValid
     */

    public function testIsValidEmpty()
    {
        $this->assertFalse($this->_validator->isValid(''));
    }

    /**
     * Test for an true ISBN.
     * @covers ::isValid
     */

    public function testIsValidTrue()
    {
        $this->assertTrue($this->_validator->isValid('978-3-86680-192-9'));
        $this->assertTrue($this->_validator->isValid('978 3 86680 192 9'));
        $this->assertTrue($this->_validator->isValid('978-0-13235-088-4'));
        $this->assertTrue($this->_validator->isValid('0 13235 088 2'));
        $this->assertTrue($this->_validator->isValid('0-13235-088-2'));
    }

    /**
     * Test for an wrong ISBN-checksum in an ISBN-identifier.
     * @covers ::isValid
     */

    public function testIsValidWrongIsbnchecksum()
    {
        $this->assertFalse($this->_validator->isValid('978-3-86680-192-13'));
        $this->assertFalse($this->_validator->isValid('978-3-86680-192-34'));
    }

    /**
     * Test for an wrong ISBN-form in an ISBN-identifier.
     * @covers ::isValid
     */

    public function testIsValidWrongIsbnform()
    {
        $this->assertFalse($this->_validator->isValid('978-3-86680-192'));
        $this->assertFalse($this->_validator->isValid('978-3-8668X-192'));
        $this->assertFalse($this->_validator->isValid('978-3-866800-1942-34'));
        $this->assertFalse($this->_validator->isValid('9748-3-866800-1942-34'));
        $this->assertFalse($this->_validator->isValid('978-378-866800-1942'));
        $this->assertFalse($this->_validator->isValid('978386680192'));
        $this->assertFalse($this->_validator->isValid('978-0 13235 088 4'));
    }

    /**
     * Test for an NULL argument in an ISBN-identifier.
     * @covers ::isValid
     */

    public function testIsValidIsbnNull()
    {
        $this->assertFalse($this->_validator->isValid(null));
    }

    /**
     * Test for an empty argument in an DOI-identifier -> identifier without validation.
     * @covers ::isValid
     */

    public function testIsValidDoiEmpty()
    {
        $this->_element->setValue('DOI');
        $this->_validator = new Application_Form_Validate_Identifier($this->_element);
        $this->assertFalse($this->_validator->isValid(''));
    }

    /**
     * Test for an argument in an DOI-identifier -> identifier without validation.
     * @covers ::isValid
     */

    public function testIsValidDoi()
    {
        $this->_element->setValue('DOI');
        $this->_validator = new Application_Form_Validate_Identifier($this->_element);
        $this->assertTrue($this->_validator->isValid('23356'));
        $this->assertTrue($this->_validator->isValid('233dfsfsf'));
        $this->assertTrue($this->_validator->isValid('23fdt45356'));
        $this->assertTrue($this->_validator->isValid('233_:()$&56'));
        $this->assertTrue($this->_validator->isValid('23!"356'));
    }

    /**
     * Test for null as element.
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Argument must not be NULL
     * @covers ::isValid
     */

    public function testIsValidElementNull()
    {
        $this->_validator = new Application_Form_Validate_Identifier(null);
    }

    /**
     * Test for an NULL argument in an DOI-identifier-> identifier without validation.
     * @covers ::isValid
     */

    public function testIsValidDoiNull()
    {
        $this->_element->setValue('DOI');
        $this->_validator = new Application_Form_Validate_Identifier($this->_element);
        $this->assertFalse($this->_validator->isValid(null));
    }

    /**
     * Test for an unknown type as identifier -> same result as empty in type without validation.
     * @covers ::isValid
     */

    public function testIsValidUnknownType()
    {
        $this->_element->setValue('unknown');
        $this->_validator = new Application_Form_Validate_Identifier($this->_element);
        $this->assertFalse($this->_validator->isValid(''));
    }
}
