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

/**
 * Unit Tests von Formularelement fuer die Auswahl von Rollen.
 */
class Application_Form_Element_SupportedLanguagesTest extends FormElementTestCase
{
    /** @var string */
    protected $additionalResources = 'translation';

    public function setUp(): void
    {
        $this->formElementClass       = 'Application_Form_Element_SupportedLanguages';
        $this->expectedDecoratorCount = 6;
        $this->expectedDecorators     = [
            'ViewHelper',
            'Description',
            'Errors',
            'ElementHtmlTag',
            'LabelNotEmpty',
            'dataWrapper',
        ];
        $this->staticViewHelper       = 'viewFormMultiCheckbox';
        parent::setUp();
    }

    public function testGetValue()
    {
        $element = $this->getElement();

        $element->setValue(['en', 'de']);

        $this->assertEquals(['en', 'de'], $element->getValue());
    }

    public function testSetValue()
    {
        $element = $this->getElement();

        $element->setValue('en,de');

        $this->assertEquals(['en', 'de'], $element->getValue());
    }

    public function testSetValueWithSpaces()
    {
        $element = $this->getElement();

        $element->setValue(' en , de ');

        $this->assertEquals(['en', 'de'], $element->getValue());
    }

    public function testGetLanguageOptionsInGerman()
    {
        $this->useGerman();

        $element = $this->getElement();

        $options = $element->getLanguageOptions();

        $this->assertNotNull($options);
        $this->assertInternalType('array', $options);
        $this->assertEquals([
            'en' => 'Englisch',
            'de' => 'Deutsch',
        ], $options);
    }

    public function testGetLanguageOptionsInEnglish()
    {
        $this->useEnglish();

        $element = $this->getElement();

        $options = $element->getLanguageOptions();

        $this->assertNotNull($options);
        $this->assertInternalType('array', $options);
        $this->assertEquals([
            'en' => 'English',
            'de' => 'German',
        ], $options);
    }

    public function testValidation()
    {
        $element = $this->getElement();

        $this->assertTrue($element->isValid(['de', 'en']));
        $this->assertTrue($element->isValid(['en', 'de']));
        $this->assertTrue($element->isValid(['de']));
        $this->assertTrue($element->isValid(['en']));
        $this->assertFalse($element->isValid([]));
        $this->assertFalse($element->isValid(['ru']));
    }
}
