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

class Application_Form_Element_LoginTest extends FormElementTestCase
{
    /** @var string */
    protected $additionalResources = 'translation';

    public function setUp(): void
    {
        $this->formElementClass       = 'Application_Form_Element_Login';
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

    public function testValidationSuccess()
    {
        $element = $this->getElement();

        $this->assertTrue($element->isValid('user'));
        $this->assertTrue($element->isValid('user-1'));
        $this->assertTrue($element->isValid('user@bla'));
        $this->assertTrue($element->isValid('user_2'));
        $this->assertTrue($element->isValid('user.3'));
        $this->assertTrue($element->isValid('user4'));
        $this->assertTrue($element->isValid('5user'));
        $this->assertTrue($element->isValid('_user'));
        $this->assertTrue($element->isValid('-user'));
        $this->assertTrue($element->isValid('@user'));
        $this->assertTrue($element->isValid('.user'));
        $this->assertTrue($element->isValid('use'));
        $this->assertTrue($element->isValid('1234'));
    }

    public function testValidationFailure()
    {
        $element = $this->getElement();

        $this->assertFalse($element->isValid(''));
        $this->assertFalse($element->isValid(' '));
        $this->assertFalse($element->isValid('user!'));
        $this->assertFalse($element->isValid('user%'));
        $this->assertFalse($element->isValid('ur'));
    }

    public function testRegexValidationTranslated()
    {
        $this->useEnglish();
        $element = $this->getElement();

        $validator = $element->getValidator('Regex');

        $validator->isValid('');
        $messages = $validator->getMessages();

        $this->assertCount(1, $messages);
        $this->assertArrayHasKey('regexNotMatch', $messages);
        $this->assertContains('letters, numbers', $messages['regexNotMatch']);

        $this->useGerman();

        $validator->isValid('');
        $messages = $validator->getMessages();

        $this->assertCount(1, $messages);
        $this->assertArrayHasKey('regexNotMatch', $messages);
        $this->assertContains('Buchstaben, Zahlen', $messages['regexNotMatch']);
    }

    public function testStringLengthValidationTranslated()
    {
        $this->useEnglish();
        $element = $this->getElement();

        $validator = $element->getValidator('stringLength');

        $validator->isValid('12');
        $messages = $validator->getMessages();

        $this->assertCount(1, $messages);
        $this->assertArrayHasKey('stringLengthTooShort', $messages);
        $this->assertContains('less than 3 characters', $messages['stringLengthTooShort']);

        $this->useGerman();

        $validator->isValid('12');
        $messages = $validator->getMessages();

        $this->assertCount(1, $messages);
        $this->assertArrayHasKey('stringLengthTooShort', $messages);
        $this->assertContains('weniger als 3 Zeichen', $messages['stringLengthTooShort']);
    }
}
