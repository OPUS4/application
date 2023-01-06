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
 * @copyright   Copyright (c) 2020, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * TODO require default language value ?
 */
class Setup_Form_TranslationValuesTest extends ControllerTestCase
{
    /** @var string */
    protected $additionalResources = 'Translation';

    public function testInit()
    {
        $form = $this->getForm();

        $languages = Application_Configuration::getInstance()->getSupportedLanguages();

        $elements = $form->getElements();

        $this->assertSameSize($languages, $elements);
    }

    public function testPopulate()
    {
        $form = $this->getForm();

        $form->setName('Translation');

        $form->populate([
            'Translation' => [
                'en' => 'English',
                'de' => 'Deutsch',
            ],
        ]);

        $this->assertEquals('English', $form->getElement('en')->getValue());
        $this->assertEquals('Deutsch', $form->getElement('de')->getValue());
    }

    public function testPopulateUnknownLanguage()
    {
    }

    public function testIsValidTrue()
    {
        $form = $this->getForm();

        $this->assertTrue($form->isValid([]));
    }

    public function testIsValidEmptyValues()
    {
        $form = $this->getForm();

        $this->assertTrue($form->isValid([
            'en' => '',
            'de' => '',
        ]));
    }

    public function testGetTranslations()
    {
        $form = $this->getForm();

        $data = [
            'en' => 'English',
            'de' => 'Deutsch',
        ];

        $form->populate($data);

        $translations = $form->getTranslations();

        $this->assertEquals($data, $translations);
    }

    public function testGetTranslationsNullValue()
    {
        $form = $this->getForm();

        $data = [
            'en' => 'English',
            'de' => null,
        ];

        $form->populate($data);

        $translations = $form->getTranslations();

        $this->assertEquals($data, $translations);
    }

    public function testGetTranslationsTrimValue()
    {
        $form = $this->getForm();

        $data = [
            'en' => ' English ',
            'de' => null,
        ];

        $form->populate($data);

        $translations = $form->getTranslations();

        $this->assertEquals([
            'en' => 'English',
            'de' => null,
        ], $translations);
    }

    public function testGetTranslationsTrimValueLineBreaks()
    {
        $form = $this->getForm();

        $data = [
            'en' => ' English ' . PHP_EOL . ' ',
            'de' => ' ' . PHP_EOL . ' Deutsch',
        ];

        $form->populate($data);

        $translations = $form->getTranslations();

        $this->assertEquals([
            'en' => 'English',
            'de' => 'Deutsch',
        ], $translations);
    }

    public function testSetTranslations()
    {
        $form = $this->getForm();

        $data = [
            'en' => 'English',
            'de' => 'Deutsch',
        ];

        $form->setTranslations($data);

        $this->assertEquals('English', $form->getElement('en')->getValue());
        $this->assertEquals('Deutsch', $form->getElement('de')->getValue());
    }

    public function testSetTranslationsUnknownLanguage()
    {
        $this->markTestIncomplete('no handling of this situation yet');
    }

    /**
     * @return Setup_Form_TranslationValues
     */
    protected function getForm()
    {
        return new Setup_Form_TranslationValues();
    }
}
