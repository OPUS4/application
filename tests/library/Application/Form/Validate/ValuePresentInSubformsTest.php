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
 * @copyright   Copyright (c) 2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Unit Tests für Validator, der prüft, ob ein Wert in Unterformularen vorkommt.
 */
class Application_Form_Validate_ValuePresentInSubformsTest extends ControllerTestCase
{
    /** @var string[][] */
    private $postData;

    public function setUp(): void
    {
        parent::setUp();

        $this->postData = [
            'TitleMain0' => [
                'Language' => 'deu',
                'Value'    => 'Titel 1',
            ],
            'TitleMain1' => [
                'Language' => 'eng',
                'Value'    => 'Title 2',
            ],
            'TitleMain2' => [
                'Language' => 'fra',
                'Value'    => 'Titel 3',
            ],
        ];
    }

    public function testConstruct()
    {
        $validator = new Application_Form_Validate_ValuePresentInSubforms('Language');

        $this->assertEquals('Language', $validator->getElementName());
    }

    public function testIsValidTrue()
    {
        $validator = new Application_Form_Validate_ValuePresentInSubforms('Language');

        $this->assertTrue($validator->isValid('eng', $this->postData));
    }

    public function testIsValidFalse()
    {
        $validator = new Application_Form_Validate_ValuePresentInSubforms('Language');

        $this->assertFalse($validator->isValid('rus', $this->postData));
    }

    public function testIsValidFalseForContextNull()
    {
        $validator = new Application_Form_Validate_ValuePresentInSubforms('Language');

        $this->assertFalse($validator->isValid('rus', null));
    }

    public function testIsValidFalseForElementNull()
    {
        $validator = new Application_Form_Validate_ValuePresentInSubforms(null);

        $this->assertFalse($validator->isValid('rus', $this->postData));
    }
}
