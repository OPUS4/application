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
 * @copyright   Copyright (c) 2019, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Translate\Dao;

/**
 * Unit tests for translation form element.
 */
class Application_Form_Element_TranslationTest extends ControllerTestCase
{
    /** @var string[] */
    protected $additionalResources = ['translation', 'view'];

    public function testConstruct()
    {
        $element = new Application_Form_Element_Translation('DisplayName');

        $this->assertEquals('DisplayName', $element->getName());

        $options = $element->getMultiOptions();

        $this->assertCount(2, $options);
        $this->assertArrayHasKey('de', $options);
        $this->assertArrayHasKey('en', $options);
        $this->assertNull($options['de']);
        $this->assertNull($options['en']);
    }

    public function testPopulateFromTranslations()
    {
        $element = new Application_Form_Element_Translation('DisplayName');

        $element->populateFromTranslations('default_collection_role_ddc');

        $options = $element->getMultiOptions();

        $this->assertCount(2, $options);
        $this->assertArrayHasKey('de', $options);
        $this->assertArrayHasKey('en', $options);
        $this->assertEquals('DDC-Klassifikation', $options['de']);
        $this->assertEquals('Dewey Decimal Classification', $options['en']);
    }

    public function testUpdateTranslations()
    {
        $element = new Application_Form_Element_Translation('DisplayName');

        $key = 'testkey';

        $dao = new Dao();

        $dao->remove($key);

        $this->assertNull($dao->getTranslation($key));

        $data = [
            'en' => 'test key',
            'de' => 'Testschlüssel',
        ];

        $element->setValue($data);

        $element->updateTranslations($key);

        $this->assertEquals($data, $dao->getTranslation($key));
    }

    public function testUpdateTranslationsOnlyIfChanged()
    {
        $key = 'default_collection_role_ddc';

        $element = new Application_Form_Element_Translation('DisplayName');

        $translate = Application_Translate::getInstance();
        $dao       = new Dao();

        $dao->remove($key);

        $this->assertNull($dao->getTranslation($key));

        $translations = $translate->getTranslations($key);

        $element->setValue($translations);
        $element->updateTranslations($key);

        // translations should not be stored in database because values did not change
        $this->assertNull($dao->getTranslation($key));
    }

    public function testIsValidTrue()
    {
        $element = new Application_Form_Element_Translation('DisplayName');

        $this->assertTrue($element->isValid([
            'en' => 'English',
            'de' => 'Englisch',
        ]));
    }

    public function testIsValidEmpty()
    {
        $element = new Application_Form_Element_Translation('DisplayName');

        $this->assertTrue($element->isValid([
            'en' => '',
            'de' => '',
        ]));
    }

    public function testGetValue()
    {
        $element = new Application_Form_Element_Translation('DisplayName');

        $value = [
            'en' => 'test key',
            'de' => 'Testschlüssel',
        ];

        $element->setValue($value);

        $this->assertEquals($value, $element->getValue());
    }

    public function testSetValueUpdatesMultiOptions()
    {
        $element = new Application_Form_Element_Translation('DisplayName');

        $value = [
            'en' => 'test key',
            'de' => 'Testschlüssel',
        ];

        $element->setValue($value);

        $this->assertEquals($value, $element->getMultiOptions());
    }

    public function testKeepModuleWhenUpdatingTranslation()
    {
        $element = new Application_Form_Element_Translation('Content');

        $dao = new Dao();
        $dao->removeAll();

        $key = 'help_content_contact';

        $data = [
            'en' => 'Content',
            'de' => 'Inhalt',
        ];

        $element->setValue($data);

        $element->updateTranslations($key);

        $manager = new Application_Translate_TranslationManager();

        $translation = $manager->getTranslation($key);

        $this->assertEquals($data, $translation['translations']);
        $this->assertEquals('home', $translation['module']);
    }

    /**
     * TODO improve test? check directly that translation of values is disabled
     */
    public function testValuesAreNotTranslated()
    {
        $this->useGerman();

        $element = new Application_Form_Element_Translation('DisplayName');

        $key = 'default_collection_role_institutes';

        $element->populateFromTranslations($key);

        $output = $element->render();

        // do not translate 'Institute' into 'Institut'
        $this->assertNotContains('id="DisplayName-de" value="Institut"', $output);
        $this->assertContains('id="DisplayName-de" value="Institute"', $output);
    }

    public function testUpdateTranslationForDuplicateKey()
    {
        $database = new Dao();
        $database->removeAll();

        $key = 'duplicateTestKey';

        $database->setTranslation($key, [
            'en' => 'AdminEn',
            'de' => 'AdminDe',
        ], 'admin');

        $database->setTranslation($key, [
            'en' => 'HomeEn',
            'de' => 'HomeDe',
        ], 'home');

        $element = new Application_Form_Element_Translation('DuplicateTest');

        $data = [
            'en' => 'NewEn',
            'de' => 'NewDe',
        ];

        $element->setValue($data);

        $element->updateTranslations($key);

        $translations = $database->getTranslationsWithModules();

        // var_dump($translations);
        $this->markTestIncomplete('no assertions');
    }
}
