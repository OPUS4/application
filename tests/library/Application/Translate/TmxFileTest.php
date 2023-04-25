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

class Application_Translate_TmxFileTest extends ControllerTestCase
{
    public function testConstruction()
    {
        $tmxFile = new Application_Translate_TmxFile();

        $this->assertEmpty($tmxFile->toArray());
    }

    public function testConstructionWithPath()
    {
        $tmxFile = new Application_Translate_TmxFile(APPLICATION_PATH . '/tests/resources/tmx/test.tmx');

        $data = $tmxFile->toArray();

        $this->assertInternalType('array', $data);
        $this->assertNotEmpty($data);
        $this->assertCount(2, $data);
        $this->assertArrayHasKey('home_index_contact_pagetitle', $data);
        $this->assertArrayHasKey('home_index_contact_title', $data);

        foreach ($data as $key => $translations) {
            $this->assertInternalType('array', $translations);
            $this->assertArrayHasKey('en', $translations);
            $this->assertArrayHasKey('de', $translations);
            $this->assertNotEmpty($translations['en']);
            $this->assertNotEmpty($translations['de']);
        }

        $this->assertEquals('Contact', $data['home_index_contact_pagetitle']['en']);
        $this->assertEquals('Kontakt', $data['home_index_contact_pagetitle']['de']);
    }

    public function testSave()
    {
        $tmxFile = new Application_Translate_TmxFile();

        $tmxFile->setTranslation('translation_key', 'en', 'Translation Key');
        $tmxFile->setTranslation('translation_key', 'de', 'Übersetzungsschlüssel');

        $filePath = $this->getTempFile('TmxFileTest');

        $tmxFile->save($filePath);

        $tmxFile = new Application_Translate_TmxFile($filePath);

        $this->assertEquals([
            'translation_key' => [
                'en' => 'Translation Key',
                'de' => 'Übersetzungsschlüssel',
            ],
        ], $tmxFile->toArray());
    }

    public function testLoadingTmxFile()
    {
        $tmxFile = new Application_Translate_TmxFile();

        $this->assertEmpty($tmxFile->toArray());

        $tmxFile->load(APPLICATION_PATH . '/tests/resources/tmx/test.tmx');

        $this->verifyTestData($tmxFile->toArray());
    }

    public function testFromArray()
    {
        $tmxFile = new Application_Translate_TmxFile();

        $tmxFile->fromArray([
            'home_index_contact_pagetitle' => [
                'en' => 'Contact',
                'de' => 'Kontakt',
            ],
            'home_index_contact_title'     => [
                'en' => 'Contact us...',
                'de' => 'Nehmen Sie Kontakt mit uns auf...',
            ],
        ]);

        $this->verifyTestData($tmxFile->toArray());
    }

    public function testToArray()
    {
        $tmxFile = new Application_Translate_TmxFile(APPLICATION_PATH . '/tests/resources/tmx/test.tmx');

        $this->verifyTestData($tmxFile->toArray());
    }

    /**
     * @param array $data
     */
    protected function verifyTestData($data)
    {
        $this->assertEquals([
            'home_index_contact_pagetitle' => [
                'en' => 'Contact',
                'de' => 'Kontakt',
            ],
            'home_index_contact_title'     => [
                'en' => 'Contact us...',
                'de' => 'Nehmen Sie Kontakt mit uns auf...',
            ],
        ], $data);
    }

    public function testSetTranslation()
    {
        $tmxFile = new Application_Translate_TmxFile();

        $tmxFile->setTranslation('translation_key', 'en', 'Translation Key');

        $this->assertEquals([
            'translation_key' => ['en' => 'Translation Key'],
        ], $tmxFile->toArray());

        $tmxFile->setTranslation('translation_key', 'de', 'Übersetzungsschlüssel');

        $this->assertEquals([
            'translation_key' => [
                'en' => 'Translation Key',
                'de' => 'Übersetzungsschlüssel',
            ],
        ], $tmxFile->toArray());
    }

    public function testSetTranslationWithModule()
    {
        $tmxFile = new Application_Translate_TmxFile();

        $tmxFile->setTranslation('translationKey', 'en', 'Translation');

        $this->assertNull($tmxFile->getModuleForKey('translationKey'));

        $tmxFile->setTranslation('translationKey', 'de', 'Übersetzung', 'default');

        $this->assertEquals([
            'translationKey' => [
                'en' => 'Translation',
                'de' => 'Übersetzung',
            ],
        ], $tmxFile->toArray());

        $this->assertEquals('default', $tmxFile->getModuleForKey('translationKey'));
    }

    public function testFindTranslation()
    {
        $this->markTestIncomplete('not implemented yet');
    }

    public function testRemoveTranslation()
    {
        $path = APPLICATION_PATH . '/tests/resources/tmx/test.tmx';

        $tmxFile = new Application_Translate_TmxFile($path);

        $data = $tmxFile->toArray();

        $this->assertCount(2, $data);
        $this->assertArrayHasKey('home_index_contact_pagetitle', $data);

        $tmxFile->removeTranslation('home_index_contact_pagetitle');

        $data = $tmxFile->toArray();

        $this->assertCount(1, $data);
        $this->assertArrayNotHasKey('home_index_contact_pagetitle', $data);
    }

    public function testRemoveTranslationUnknownKey()
    {
        $path = APPLICATION_PATH . '/tests/resources/tmx/test.tmx';

        $tmxFile = new Application_Translate_TmxFile($path);

        $tmxFile->removeTranslation('unknown_key');

        $data = $tmxFile->toArray();

        $this->assertCount(2, $data);
    }

    public function testHasTranslation()
    {
        $tmxFile = new Application_Translate_TmxFile();

        $tmxFile->setTranslation('test_key', 'en', 'test value');

        $this->assertTrue($tmxFile->hasTranslation('test_key'));
    }

    public function testHasTranslationWithLanguage()
    {
        $tmxFile = new Application_Translate_TmxFile();

        $tmxFile->setTranslation('test_key', 'en', 'test value');

        $this->assertTrue($tmxFile->hasTranslation('test_key', 'en'));
        $this->assertFalse($tmxFile->hasTranslation('test_key', 'de'));
    }

    public function testHasTranslationUnknownKey()
    {
        $tmxFile = new Application_Translate_TmxFile();

        $tmxFile->setTranslation('test_key', 'en', 'test value');

        $this->assertFalse($tmxFile->hasTranslation('unknown_key'));
    }

    public function testHasTranslationUnknownLanguage()
    {
        $tmxFile = new Application_Translate_TmxFile();

        $tmxFile->setTranslation('test_key', 'en', 'test value');

        $this->assertFalse($tmxFile->hasTranslation('test_key', 'ru'));
    }

    public function testHasTranslationUnknownKeyAndLanguage()
    {
        $tmxFile = new Application_Translate_TmxFile();

        $tmxFile->setTranslation('test_key', 'en', 'test value');

        $this->assertFalse($tmxFile->hasTranslation('unknown_key', 'ru'));
    }

    public function testLoadTranslationsWithTagsAndEntities()
    {
        $tmxFile = new Application_Translate_TmxFile();

        $file = APPLICATION_PATH . '/tests/resources/tmx/testWithTags.tmx';

        $tmxFile->load($file);

        $translations = $tmxFile->toArray();

        $this->assertCount(3, $translations);
        $this->assertEquals([
            'testkey_cdata'      => [
                'en' => '<span>Translation</span>',
                'de' => '&Uuml;bersetzung',
            ],
            'testkey'            => [
                'en' => '<span class="highlight" name="title">Translation</span>',
                'de' => '&Uuml;bersetzung',
            ],
            'testkey_whitespace' => [
                'en' => "line1\nline2\n  line3",
                'de' => "Zeile1\nZeile2\n  Zeile3",
            ],
        ], $translations);
    }
}
