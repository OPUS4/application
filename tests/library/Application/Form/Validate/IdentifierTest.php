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
 */
class Application_Form_Validate_IdentifierTest extends ControllerTestCase
{
    /**
     * declaration area
     */
    private $_validator;
    private $_element;

    /**
     * set up variables
     */
    public function setUp()
    {
        parent::setUp();
        $this->_element = new Application_Form_Element_Identifier('Element');
        $this->_element->setValue('ISBN');
        $this->_validator = new Application_Form_Validate_Identifier($this->_element);
    }

    /**
     * Test for an false argument in an ISBN-identifier
     */
    public function testIsValidFalseArgument() {
        $this->assertFalse($this->_validator->isValid('123test'));
    }
    /**
     * Test for an empty argument in an ISBN-identifier
     */
    public function testIsValidEmptyArgument() {
        $this->assertFalse($this->_validator->isValid(''));
    }
    /**
     * Test for an true argument in an ISBN-identifier
     */
    public function testIsValidTrueArgument() {
        $this->assertTrue($this->_validator->isValid('978-3-86680-192-9'));
    }
    /**
     * Test for an wrong ISBN in an ISBN-identifier
     */
    public function testIsValidWrongISBN() {
        $this->assertFalse($this->_validator->isValid('978-3-86680-192-13'));
    }
    /**
     * Test for an NULL argument in an ISBN-identifier
     */
    public function testIsValidISBNNULL() {
        $this->assertFalse($this->_validator->isValid(null));
    }
    /**
     * Test for an empty argument in an DOI-identifier -> identifier without validation
     */
    public function testIsValidDOIEmpty() {
        $this->_element->setValue('DOI');
        $this->_validator = new Application_Form_Validate_Identifier($this->_element);
        $this->assertFalse($this->_validator->isValid(''));
    }
    /**
     * Test for an argument in an DOI-identifier -> identifier without validation
     */
    public function testIsValidDOI() {
        $this->_element->setValue('DOI');
        $this->_validator = new Application_Form_Validate_Identifier($this->_element);
        $this->assertTrue($this->_validator->isValid('23356'));
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Argument must not be NULL
     * Test for null as element
     */
    public function testIsValidElementNULL() {
        $this->_validator = new Application_Form_Validate_Identifier(null);
    }
    /**
     * Test for an NULL argument in an DOI-identifier-> identifier without validation
     */
    public function testIsValidDOINULL() {
        $this->_element->setValue('DOI');
        $this->_validator = new Application_Form_Validate_Identifier($this->_element);
        $this->assertFalse($this->_validator->isValid(null));
    }
    /**
     * Test for an unknown type as identifier -> same result as empty in type without validation
     */
    public function testIsValidUnknownType() {
        $this->_element->setValue('unknown');
        $this->_validator = new Application_Form_Validate_Identifier($this->_element);
        $this->assertFalse($this->_validator->isValid(''));
    }
}
