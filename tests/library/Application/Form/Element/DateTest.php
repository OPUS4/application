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

class Application_Form_Element_DateTest extends FormElementTestCase
{
    /** @var string */
    protected $additionalResources = 'translation';

    public function setUp(): void
    {
        $this->formElementClass       = 'Application_Form_Element_Date';
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

    public function testValidationEnglish()
    {
        $this->useEnglish();

        $element = $this->getElement();

        $this->assertFalse($element->isValid('10000/02/30'));
        $this->assertFalse($element->isValid('2010/02/30'));
        $this->assertFalse($element->isValid('2010-10-12'));
        $this->assertFalse($element->isValid('2010'));
        $this->assertFalse($element->isValid('  '));
        $this->assertFalse($element->isValid('2011/02/29')); // kein Schaltjahr

        $this->assertTrue($element->isValid(null));
        $this->assertTrue($element->isValid(''));
        $this->assertTrue($element->isValid('2010/10/12'));
        $this->assertTrue($element->isValid('9999/12/31'));
        $this->assertTrue($element->isValid('2012/02/29')); // Schaltjahr
    }

    public function testValidationGerman()
    {
        $this->useGerman();

        $element = $this->getElement();

        $this->assertFalse($element->isValid('30.02.10000'));
        $this->assertFalse($element->isValid('30.02.2010'));
        $this->assertFalse($element->isValid('12-10-2010'));
        $this->assertFalse($element->isValid('2010'));
        $this->assertFalse($element->isValid('  '));
        $this->assertFalse($element->isValid('29.02.2011')); // kein Schaltjahr

        $this->assertTrue($element->isValid(null));
        $this->assertTrue($element->isValid(''));
        $this->assertTrue($element->isValid('12.10.2010'));
        $this->assertTrue($element->isValid('31.12.9999'));
        $this->assertTrue($element->isValid('29.02.2012')); // Schaltjahr
    }

    public function testTranslation()
    {
        $translator = $this->getElement()->getTranslator();

        $this->assertTrue($translator->isTranslated('validation_error_date_invalid'));
        $this->assertTrue($translator->isTranslated('validation_error_date_invaliddate'));
        $this->assertTrue($translator->isTranslated('validation_error_date_falseformat'));
        $this->assertTrue($translator->isTranslated('date_format'));
    }
}
