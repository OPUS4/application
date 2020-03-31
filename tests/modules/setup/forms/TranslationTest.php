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
 * @category    Application
 * @package     Module_Setup
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2020, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 */
class Setup_Form_TranslationTest extends ControllerTestCase
{

    protected $additionalResources = 'Translation';

    public function testInit()
    {
        $form = $this->getForm();

        $elements = $form->getElements();

        $this->assertCount(4, $elements);

        $subforms = $form->getSubForms();

        $this->assertCount(1, $subforms);
    }

    public function testProcessPostSave()
    {
        $form = $this->getForm();

        $result = $form->processPost([
            'Save' => 'Speichern'
        ], null);

        $this->assertEquals($form::RESULT_SAVE, $result);
    }

    public function testProcessPostCancel()
    {
        $form = $this->getForm();

        $result = $form->processPost([
            'Cancel' => 'Abbrechen'
        ], null);

        $this->assertEquals($form::RESULT_CANCEL, $result);
    }

    public function testIsValidTrue()
    {
        $form = $this->getForm();

        $this->assertTrue($form->isValid([
            'Key' => 'translation_key',
            'KeyModule' => 'default',
            'Translation' => [
                'en' => 'Englisch',
                'de' => 'Deutsch'
            ],
            'Save' => 'Speichern'
        ]));
    }

    public function testIsValidUnknownModule()
    {
        $form = $this->getForm();

        $result = $form->isValid([
            'Key' => 'translation_key',
            'KeyModule' => 'defaultUnknown',
            'Translation' => [
                'en' => 'English',
                'de' => 'Deutsch'
            ],
            'Save' => 'Speichern'
        ]);

        $this->assertFalse($result);

        $errors = $form->getErrors('KeyModule');
        $this->assertContains('notInArray', $errors);
        $this->assertCount(1, $errors);
    }

    public function testIsValidEmptyValues()
    {
        $form = $this->getForm();

        $languages = Application_Configuration::getInstance()->getSupportedLanguages();
        $this->assertCount(2, $languages);
        $this->assertEquals(['de', 'en'], $languages);

        $result = $form->isValid([
            'Key' => '',
            'KeyModule' => '',
            'Translation' => [
                'en' => '',
                'de' => ''
            ],
            'Save' => 'Speichern'
        ]);

        $this->assertFalse($result);

        $errors = $form->getErrors('Key');
        $this->assertContains('isEmpty', $errors);
        $this->assertCount(1, $errors);

        $errors = $form->getErrors('KeyModule');
        $this->assertContains('isEmpty', $errors);
        $this->assertContains('notInArray', $errors);
        $this->assertCount(2, $errors);

        $errors = $form->getSubForm($form::SUBFORM_TRANSLATION)->getErrors('de');
        $this->assertCount(0, $errors);

        $errors = $form->getSubForm($form::SUBFORM_TRANSLATION)->getErrors('en');
        $this->assertCount(0, $errors);
    }

    public function testIsValidDuplicateKey()
    {
        $this->markTestIncomplete();
    }

    protected function getForm()
    {
        return new Setup_Form_Translation();
    }
}
