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
 * Unit Tests für Validator der prüft, ob eine Sprache mehrfach verwendet wurde.
 */
class Application_Form_Validate_LanguageUsedOnceOnlyTest extends ControllerTestCase
{
    /** @var string */
    protected $additionalResources = 'translation';

    /** @var string[] */
    private $selectedLanguages;

    public function setUp(): void
    {
        parent::setUp();
        $this->selectedLanguages = ['deu', 'fra', 'rus', 'deu', 'eng', 'deu'];
    }

    /**
     * Validierung für 3. Unterformular (index = 2) mit Sprache 'rus' ist TRUE.
     */
    public function testIsValidTrue()
    {
        $validator = new Application_Form_Validate_LanguageUsedOnceOnly($this->selectedLanguages, 2);

        $this->assertTrue($validator->isValid('rus'));
    }

    /**
     * Validierung für 4. Unterformular (index = 3) mit Sprache 'deu' ist FALSE, weil Sprache schon verwendet wurde.
     */
    public function testIsValidFalse()
    {
        $validator = new Application_Form_Validate_LanguageUsedOnceOnly($this->selectedLanguages, 3);

        $this->assertFalse($validator->isValid('deu'));
    }

    public function testIsValidFalse3rdOccurence()
    {
        $validator = new Application_Form_Validate_LanguageUsedOnceOnly($this->selectedLanguages, 5);

        $this->assertFalse($validator->isValid('deu'));
    }

    /**
     * Wenn kein Array mit den Sprachen übergeben wurde soll der Validator ignoriert werden. Sollte nie passieren.
     */
    public function testLanguagesNullAlwaysReturnsTrue()
    {
        $validator = new Application_Form_Validate_LanguageUsedOnceOnly(null, 0);

        $this->assertTrue($validator->isValid('deu'));
    }

    /**
     * Wenn Array mit Sprachen zu kurz ist soll der Validator ignoriert werden, nachdem alle vorhanden Positionen
     * geprüft wurden.
     */
    public function testLanguagesTooShortReturnsTrue()
    {
        $validator = new Application_Form_Validate_LanguageUsedOnceOnly(['eng', 'deu', 'spa'], 4);

        $this->assertTrue($validator->isValid('rus'));
    }

    public function testLanguagesTooShortReturnsFalse()
    {
        $validator = new Application_Form_Validate_LanguageUsedOnceOnly(['eng', 'deu', 'spa'], 4);

        $this->assertFalse($validator->isValid('deu'));
    }

    public function testGetPosition()
    {
        $validator = new Application_Form_Validate_LanguageUsedOnceOnly(['eng', 'deu', 'spa'], 4);

        $this->assertEquals(4, $validator->getPosition());
    }

    public function testGetLanguages()
    {
        $validator = new Application_Form_Validate_LanguageUsedOnceOnly(['eng', 'deu', 'spa'], 4);

        $this->assertEquals(['eng', 'deu', 'spa'], $validator->getLanguages());
    }
}
