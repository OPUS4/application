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
 * @package     Application_Form_Element
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2017, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

class Application_Form_Element_ComboboxTest extends FormElementTestCase
{

    public function setUp()
    {
        $this->_formElementClass = 'Application_Form_Element_Combobox';
        $this->_expectedDecoratorCount = 6;
        $this->_expectedDecorators = array(
            'ViewHelper', 'Errors', 'Description', 'ElementHtmlTag', 'LabelNotEmpty', 'dataWrapper'
        );
        parent::setUp();
    }

    public function testSetAutocompleteValues()
    {
        $element = $this->getElement();

        $values = array('Berlin', 'München', 'Hamburg');

        $element->setAutocompleteValues($values);

        $options = $element->getMultiOptions();

        $this->assertEquals(array('Berlin' => 'Berlin', 'München' => 'München', 'Hamburg' => 'Hamburg'), $options);
    }

    public function testSetAutocompleteValuesWithNullValueInArray()
    {
        $element = $this->getElement();

        $values = array('Berlin', 'München', null, 'Hamburg');

        $element->setAutocompleteValues($values);

        $options = $element->getMultiOptions();

        $this->assertEquals(array('Berlin' => 'Berlin', 'München' => 'München', 'Hamburg' => 'Hamburg'), $options);
    }

    public function testIsValid()
    {
        $element = $this->getElement();

        $values = array('Berlin', 'München', 'Hamburg');

        $element->setAutocompleteValues($values);

        $this->assertTrue($element->isValid('Berlin'));

        // combo box should accept new values
        $this->assertTrue($element->isValid('Bremen'));

        // combo box should accept empty values
        $this->assertTrue($element->isValid(null));
        $this->assertTrue($element->isValid(''));
        $this->assertTrue($element->isValid('  '));
    }

    public function testSetValue()
    {
        $element = $this->getElement();

        $values = array('Berlin', 'München', 'Hamburg');

        $element->setAutocompleteValues($values);

        $element->setValue('Bremen');

        $this->assertEquals('Bremen', $element->getValue());
    }

    public function testCustomValidation()
    {
        $this->useEnglish();

        $element = $this->getElement();

        $values = array('2010/05/23', '2012/08/03', '2017/04/29');

        $element->setAutocompleteValues($values);
        $element->addValidator(new Application_Form_Validate_Date());

        $this->assertTrue($element->isValid('2015/04/11'));
        $this->assertFalse($element->isValid('04.11.2015'));
        $this->assertFalse($element->isValid('2016/14/11'));
        $this->assertFalse($element->isValid('2016/11/32'));
        $this->assertFalse($element->isValid('2016/02/30'));

        $this->useGerman();

        $element->setValidators(array(new Application_Form_Validate_Date()));

        $this->assertTrue($element->isValid('04.11.2015'));
        $this->assertFalse($element->isValid('2015/04/11'));
    }

    public function testSetSingleValue()
    {
        $element = $this->getElement();

        $element->setAutocompleteValues('Berlin');

        $options = $element->getMultiOptions();

        $this->assertEquals(array('Berlin' => 'Berlin'), $options);
    }

}