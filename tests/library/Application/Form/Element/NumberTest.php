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

class Application_Form_Element_NumberTest extends FormElementTestCase
{
    /** @var string */
    protected $additionalResources = 'translation';

    public function setUp(): void
    {
        $this->formElementClass       = 'Application_Form_Element_Number';
        $this->expectedDecoratorCount = 8;
        $this->expectedDecorators     = [
            'ViewHelper',
            'Placeholder',
            'Description',
            'ElementHint',
            'Errors',
            'ElementHtmlTag',
            'LabelNotEmpty',
            'dataWrapper',
        ];
        $this->staticViewHelper       = 'viewFormDefault';
        parent::setUp();
    }

    public function testValidation()
    {
        $element = $this->getElement();

        $this->assertTrue($element->getValidator('Int') !== false, 'Validator Int is missing.');
        $this->assertTrue($element->getValidator('GreaterThan') !== false, 'Validator GreaterThan is missing.');
        $this->assertEquals(-1, $element->getValidator('GreaterThan')->getMin());
    }

    public function testDefaultSize()
    {
        $element = $this->getElement();

        $this->assertEquals(6, $element->getAttrib('size'));
    }

    public function testCustomSize()
    {
        $element = $this->getElement(['size' => 10]);

        $this->assertEquals(10, $element->getAttrib('size'));
    }

    public function testMessagesTranslated()
    {
        $translator = Application_Translate::getInstance();

        $this->assertTrue($translator->isTranslated('validation_error_number_tooSmall'));
        $this->assertTrue($translator->isTranslated('validation_error_number_notBetween'));
    }

    public function testSettingMinAndMax()
    {
        $element = $this->getElement(['min' => 10, 'max' => 100]);

        $this->assertNotFalse($element->getValidator('Between'), 'Validator Between missing.');
        $this->assertFalse($element->getValidator('GreaterThan'));

        $validator = $element->getValidator('Between');

        $this->assertEquals(10, $validator->getMin());
        $this->assertEquals(100, $validator->getMax());

        $this->assertTrue($element->isValid(10));
        $this->assertTrue($element->isValid(100));
        $this->assertTrue($element->isValid(50));
        $this->assertFalse($element->isValid(9));
        $this->assertFalse($element->isValid(101));
    }

    public function testSettingMinOnly()
    {
        $element = $this->getElement(['min' => 10]);

        $this->assertFalse($element->getValidator('Between'), 'Validator Between present.');
        $this->assertNotFalse($element->getValidator('GreaterThan'));

        $validator = $element->getValidator('GreaterThan');

        $this->assertEquals(9, $validator->getMin()); // because 'inclusive' not supported in ZF1

        $this->assertTrue($element->isValid(10));
        $this->assertFalse($element->isValid(9));
    }
}
