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

use Opus\Common\Translate\TranslateException;
use Opus\Common\Translate\UnknownTranslationKeyException;
use Opus\Translate\Dao;

/**
 * Test class for Setup_Model_Language_TranslationManager.
 */
class Application_Translate_TranslationManagerTest extends ControllerTestCase
{
    /** @var string[] */
    protected $additionalResources = ['database', 'translation'];

    /** @var bool */
    protected $configModifiable = true;

    /** @var Application_Translate_TranslationManager */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->object = new Application_Translate_TranslationManager();
    }

    public function tearDown(): void
    {
        $translationDb = $this->getStorageInterface();
        $translationDb->removeAll();

        parent::tearDown();
    }

    public function testGetFiles()
    {
        $this->adjustConfiguration([
            'setup' => ['translation' => ['modules' => ['allowed' => 'default,publish']]],
        ]);

        $files = $this->object->getFiles();

        $this->assertCount(2, $files);

        $this->object->setModules(['default']);
        $files = $this->object->getFiles();

        $this->assertCount(1, $files, 'Expected non empty result with module set');
        $this->assertArrayHasKey('default', $files);
    }

    public function testGetTranslations()
    {
        $sortKeys = [
            Application_Translate_TranslationManager::SORT_DIRECTORY,
            Application_Translate_TranslationManager::SORT_FILENAME,
            Application_Translate_TranslationManager::SORT_MODULE,
            Application_Translate_TranslationManager::SORT_UNIT,
        ];

        $this->object->setModules(['default']);

        foreach ([SORT_ASC, SORT_DESC] as $sortOrder) {
            foreach ($sortKeys as $sortKey) {
                $actualValues = [];
                $translations = $this->object->getTranslations($sortKey, $sortOrder);

                foreach ($translations as $translation) {
                    $actualValues[] = $translation[$sortKey];
                }

                $sortedValues = $actualValues;

                if ($sortOrder === SORT_ASC) {
                    sort($sortedValues, SORT_STRING);
                } elseif ($sortOrder === SORT_DESC) {
                    rsort($sortedValues, SORT_STRING);
                }

                $this->assertEquals($sortedValues, $actualValues);
            }
        }
    }

    public function testGetTranslationsModulesNull()
    {
        $manager = $this->object;

        $manager->setModules(null);

        $translations = $manager->getTranslations();

        $this->assertNotNull($translations);
        $this->assertInternalType('array', $translations);
        $this->assertGreaterThan(0, count($translations));
    }

    public function testSetModules()
    {
        $this->object->setModules(['default']);
        $files = $this->object->getFiles();
        $this->assertEquals(['default'], array_keys($files));

        $this->object->setModules(['default', 'home']);
        $files = $this->object->getFiles();
        $this->assertEquals(['default', 'home'], array_keys($files));
    }

    public function testSetFilter()
    {
        $filter = 'error_';

        $this->object->setModules(['default']);
        $allTranlsations = $this->object->getTranslations();

        $this->object->setFilter($filter);
        $filteredTranlsations = $this->object->getTranslations();

        $this->assertLessThan(
            count($allTranlsations),
            count($filteredTranlsations),
            'Expected count of filtered subset of translations to be less than all translations'
        );

        foreach ($filteredTranlsations as $translation) {
            $this->assertTrue(
                stripos($translation['key'], $filter) !== false,
                'Expected filtered translation unit to contain filter string'
            );
        }
    }

    public function testGetDuplicateKeys()
    {
        $manager = $this->object;

        $this->adjustConfiguration([
            'setup' => ['translation' => ['modules' => ['allowed' => null]]],
        ]);

        $duplicateKeys = $manager->getDuplicateKeys();

        $message = 'Duplicate translation keys found:' . PHP_EOL;

        foreach ($duplicateKeys as $key => $entries) {
            $modules  = implode(',', array_map(function ($value) {
                return $value['module'];
            }, $entries));
            $message .= "  $key ($modules)" . PHP_EOL;
        }

        $this->assertCount(0, $duplicateKeys, $message);
    }

    /**
     * Checks maximum length of translation keys.
     *
     * The limit is 100 for storing key in the database. There should not be a reason for longer keys.
     * The longest currently known key is 58 characters long.
     */
    public function testKeyMaxLength()
    {
        $translations = $this->object;

        $this->adjustConfiguration([
            'setup' => ['translation' => ['modules' => ['allowed' => null]]],
        ]);

        $translations->setModules(null);

        $all = $translations->getTranslations();

        $maxLength = 0;

        foreach ($all as $key => $entry) {
            $length = strlen($key);
            if ($length > $maxLength) {
                $maxLength = $length;
            }
        }

        $this->assertLessThan(100, $maxLength);
    }

    public function testFilterByValue()
    {
        $this->object->setModules(['default']);

        $result = $this->object->findTranslations('embargo');

        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);
        $this->assertEquals('EmbargoDate', $result[0]['key']);
    }

    public function testGetMergedTranslations()
    {
        $manager = $this->object;
        $manager->setModules(['default']);
        $manager->setFilter('yes');

        $translations = $manager->getMergedTranslations('key');

        $this->assertInternalType('array', $translations);
        $this->assertCount(2, $translations);
        $this->assertArrayHasKey('answer_yes', $translations);
        $this->assertArrayHasKey('Field_Value_True', $translations);

        // TODO check translations from TMX, TMX+DB and just DB

        $database = $this->getStorageInterface();
        $database->setTranslation('yes', ['de' => 'Ja', 'en' => 'Yes']);

        $translations = $manager->getMergedTranslations('key');

        $this->assertInternalType('array', $translations);
        $this->assertCount(3, $translations);
        $this->assertArrayHasKey('answer_yes', $translations);
        $this->assertArrayHasKey('yes', $translations);
        $this->assertArrayHasKey('Field_Value_True', $translations);

        $database->setTranslation('answer_yes', ['de' => 'JA', 'en' => 'YES']);

        $translations = $manager->getMergedTranslations('key');

        $this->assertInternalType('array', $translations);
        $this->assertCount(3, $translations);
        $this->assertArrayHasKey('answer_yes', $translations);
        $this->assertArrayHasKey('yes', $translations);
        $this->assertArrayHasKey('Field_Value_True', $translations);
        $this->assertArrayHasKey('translationsTmx', $translations['answer_yes']);
    }

    public function testGetMergedTranslationDatabaseFiltered()
    {
        $manager = $this->object;
        $manager->setModules(['default']);
        $manager->setFilter('answer_no');

        $translations = $manager->getMergedTranslations('key');

        $this->assertInternalType('array', $translations);
        $this->assertCount(1, $translations);
        $this->assertArrayHasKey('answer_no', $translations);

        $database = $this->getStorageInterface();

        $database->setTranslation('answer_yes', ['de' => 'JA', 'en' => 'YES']);

        $translations = $manager->getMergedTranslations('key');

        $this->assertInternalType('array', $translations);
        $this->assertCount(1, $translations);
        $this->assertArrayHasKey('answer_no', $translations);
    }

    public function testFilterTranslationsWithSpecialCharacters()
    {
        $manager = $this->object;
        $manager->setModules(['default']);
        $manager->setFilter('*in');

        $translations = $manager->getTranslations('key');

        $this->assertNotNull($translations);
        $this->assertGreaterThan(0, count($translations));
    }

    public function testFilterTranslationsAcrossAllLanguages()
    {
        $manager = $this->object;
        $manager->setModules(['default']);
        $manager->setFilter('Gutachter');

        $translations = $manager->getTranslations('key');

        $this->assertNotNull($translations);
        $this->assertGreaterThan(0, count($translations));
    }

    public function testFilterEditedTranslations()
    {
        $manager = $this->object;
        $manager->setModules(['default']);
        $manager->setFilter('Nein');

        $translations = $manager->getMergedTranslations('key');

        $this->assertNotNull($translations);
        $this->assertCount(2, $translations);
        $this->assertArrayHasKey('answer_no', $translations);
        $this->assertArrayHasKey('Field_Value_False', $translations);

        $database = $this->getStorageInterface();
        $database->setTranslation('answer_no', ['de' => 'Nicht', 'en' => 'No']);

        $translations = $manager->getMergedTranslations('key');

        $this->assertNotNull($translations);
        $this->assertCount(1, $translations);
        $this->assertArrayHasKey('Field_Value_False', $translations);
    }

    public function testReset()
    {
        $manager = $this->object;

        $testKey = 'answer_yes';

        $dao = $this->getStorageInterface();

        $dao->setTranslation($testKey, [
            'en' => 'YesTest',
            'de' => 'JaTest',
        ]);

        $translation = $manager->getTranslation($testKey);

        $this->assertArrayHasKey('state', $translation);
        $this->assertEquals('edited', $translation['state']);

        $manager->reset($testKey);

        $translation = $manager->getTranslation($testKey);

        $this->assertArrayNotHasKey('state', $translation);
        $this->assertEquals([
            'en' => 'Yes',
            'de' => 'Ja',
        ], $translation['translations']);
    }

    /**
     * Deletes translation that was added to database.
     *
     * The content of TMX-Dateien is not modified using this function. Basically there is a read-only part.
     */
    public function testDelete()
    {
        $manager = $this->object;

        $dao = $this->getStorageInterface();

        $key = 'customTestKey';

        $dao->setTranslation($key, [
            'en' => 'English',
            'de' => 'Deutsch',
        ]);

        $this->assertNotNull($dao->getTranslation($key));

        $manager->delete($key);

        $this->assertNull($dao->getTranslation($key));
    }

    public function testGetExportTmxFile()
    {
        $manager = $this->object;

        $database = $manager->getDatabase();

        $database->setTranslation('translationKey', [
            'en' => 'Translation',
            'de' => 'Übersetzung',
        ], 'home');

        $tmxFile = $manager->getExportTmxFile();

        $this->assertNotNull($tmxFile);
        $this->assertInstanceOf('Application_Translate_TmxFile', $tmxFile);

        $dom = $tmxFile->getDomDocument();

        $output = $dom->saveXML();

        $this->getResponse()->setBody($output);

        $this->assertXpathCount('//tu', 1);
        $this->assertXpath('//tu[@tuid = "translationKey"]');
        $this->assertXpath('//tu[@creationtool = "home"]');
        $this->assertXpathContentContains('//tu/tuv/seg', 'Übersetzung');
        $this->assertXpathContentContains('//tu/tuv/seg', 'Translation');
    }

    public function testGetExportTmxFileWithDefaultModuleTranslations()
    {
        $manager = $this->object;

        $database = $manager->getDatabase();

        $database->setTranslation('customtestkey', [
            'en' => 'English',
            'de' => 'Deutsch',
        ]);

        $tmxFile = $manager->getExportTmxFile();

        $this->assertNotNull($tmxFile);
        $this->assertInstanceOf('Application_Translate_TmxFile', $tmxFile);

        $dom    = $tmxFile->getDomDocument();
        $output = $dom->saveXML();
        $this->getResponse()->setBody($output);

        $this->assertXpathCount('//tu', 1);
        $this->assertXpath('//tu[@tuid = "customtestkey"]');
        $this->assertXpath('//tu[@creationtool = "default"]');
        $this->assertXpathContentContains('//tu/tuv[@xml:lang = "de"]/seg', 'Deutsch');
        $this->assertXpathContentContains('//tu/tuv[@xml:lang = "en"]/seg', 'English');
    }

    public function testGetExportTmxFileFiltered()
    {
        $manager = $this->object;

        $database = $manager->getDatabase();

        $database->setTranslation('defaultCustomKey', [
            'en' => 'Default Test Key',
            'de' => 'Default-Testschluessel',
        ]);

        $database->setTranslation('homeCustomKey', [
            'en' => 'Home Test Key',
            'de' => 'Home-Testschluessel',
        ], 'home');

        $manager->setModules('home');
        $tmxFile = $manager->getExportTmxFile();

        $this->assertNotNull($tmxFile);
        $this->assertInstanceOf('Application_Translate_TmxFile', $tmxFile);

        $dom    = $tmxFile->getDomDocument();
        $output = $dom->saveXML();
        $this->getResponse()->setBody($output);

        $this->assertXpathCount('//tu', 1);
        $this->assertXpath('//tu[@tuid = "homeCustomKey"]');
        $this->assertXpath('//tu[@creationtool = "home"]');
        $this->assertXpathContentContains('//tu/tuv[@xml:lang = "de"]/seg', 'Home-Testschluessel');
        $this->assertXpathContentContains('//tu/tuv[@xml:lang = "en"]/seg', 'Home Test Key');
    }

    public function testGetExportTmxFileIncludingUnmodified()
    {
        $manager = $this->object;

        $database = $manager->getDatabase();

        $database->setTranslation('customtestkey', [
            'en' => 'test key',
            'de' => 'Testschluessel',
        ], 'crawlers');

        $manager->setModules('crawlers');
        $tmxFile = $manager->getExportTmxFile(true);

        $this->assertNotNull($tmxFile);
        $this->assertInstanceOf('Application_Translate_TmxFile', $tmxFile);

        $dom    = $tmxFile->getDomDocument();
        $output = $dom->saveXML();
        $this->getResponse()->setBody($output);

        $this->assertXpathCount('//tu', 3);
        $this->assertXpath('//tu[@tuid = "customtestkey"]');
        $this->assertXpathCount('//tu[@creationtool = "crawlers"]', 3);
        $this->assertXpathContentContains('//tu/tuv[@xml:lang = "de"]/seg', 'Testschluessel');
        $this->assertXpathContentContains('//tu/tuv[@xml:lang = "en"]/seg', 'test key');
    }

    public function testImportTmxFile()
    {
        $tmxFile = new Application_Translate_TmxFile(APPLICATION_PATH . '/tests/resources/tmx/opus2.tmx');

        $manager = $this->object;

        $manager->importTmxFile($tmxFile);

        $database = $manager->getDatabase();

        $translations = $database->getTranslationsWithModules();

        $this->assertCount(3, $translations);
        $this->assertEquals([
            'home_index_contact_pagetitle' => [
                'module' => 'home',
                'values' => [
                    'en' => 'ContactEdited',
                    'de' => 'KontaktEdited',
                ],
            ],
            'browsing_menu_label'          => [
                'module' => 'default',
                'values' => [
                    'en' => 'BrowseEdited',
                    'de' => 'BrowsenEdited',
                ],
            ],
            'publish_controller_index'     => [
                'module' => 'publish',
                'values' => [
                    'en' => 'PublishEdited',
                    'de' => 'VeröffentlichenEdited',
                ],
            ],
        ], $translations);
    }

    public function testImportTmxFileDoNotStoreUnmodified()
    {
        $tmxFile = new Application_Translate_TmxFile(APPLICATION_PATH . '/tests/resources/tmx/opus.tmx');

        $manager = $this->object;

        $manager->importTmxFile($tmxFile);

        $database = $manager->getDatabase();

        $translations = $database->getTranslationsWithModules();

        $this->assertCount(1, $translations);
        $this->assertEquals([
            'customtestkey' => [
                'module' => 'crawlers',
                'values' => [
                    'en' => 'Test key',
                    'de' => 'Testschlüssel',
                ],
            ],
        ], $translations);
    }

    public function testImportTmxFileDoesNotChangeModuleOfKey()
    {
        $tmxFile = new Application_Translate_TmxFile(APPLICATION_PATH . '/tests/resources/tmx/opus4.tmx');

        $manager = $this->object;

        $manager->importTmxFile($tmxFile);

        $database = $manager->getDatabase();

        $translations = $database->getTranslationsWithModules();

        $this->assertCount(1, $translations);
        $this->assertEquals([
            'home_index_contact_pagetitle' => [
                'module' => 'home',
                'values' => [
                    'en' => 'ContactEdited',
                    'de' => 'KontaktEdited',
                ],
            ],
        ], $translations);
    }

    public function testImportTmxFileWithoutModuleInformation()
    {
        $tmxFile = new Application_Translate_TmxFile(APPLICATION_PATH . '/tests/resources/tmx/opus3.tmx');

        $manager = $this->object;

        $manager->importTmxFile($tmxFile);

        $database = $manager->getDatabase();

        $translations = $database->getTranslationsWithModules();

        $this->assertCount(4, $translations);
        $this->assertEquals([
            'home_index_contact_pagetitle' => [
                'module' => 'home',
                'values' => [
                    'en' => 'ContactEdited',
                    'de' => 'KontaktEdited',
                ],
            ],
            'browsing_menu_label'          => [
                'module' => 'default',
                'values' => [
                    'en' => 'BrowseEdited',
                    'de' => 'BrowsenEdited',
                ],
            ],
            'publish_controller_index'     => [
                'module' => 'publish',
                'values' => [
                    'en' => 'PublishEdited',
                    'de' => 'VeröffentlichenEdited',
                ],
            ],
            'customtestkey'                => [
                'module' => '',
                'values' => [
                    'en' => 'Test key',
                    'de' => 'Testschlüssel',
                ],
            ],
        ], $translations);
    }

    public function testImportTmxFileAdditionalLanguageLocal()
    {
        $this->markTestIncomplete('implement when additional languages are supported');
    }

    public function testImportTmxFileAdditionalLanguageImport()
    {
        $this->markTestIncomplete('implement when additional languages are supported');
    }

    public function testFilterByStateEdited()
    {
        $manager = $this->object;

        $database = $this->getStorageInterface();

        $database->setTranslation('testkey', [
            'en' => 'Testvalue',
            'de' => 'Testwert',
        ]);

        $database->setTranslation('home_menu_label', [
            'en' => 'Label',
            'de' => 'Titel',
        ]);

        $manager->setModules('default');
        $manager->setFilter(null);
        $manager->setState(Application_Translate_TranslationManager::STATE_EDITED);

        $translations = $manager->getMergedTranslations();

        $this->assertCount(1, $translations);
        $this->assertArrayHasKey('home_menu_label', $translations);
    }

    public function testFilterByStateAdded()
    {
        $manager = $this->object;

        $database = $this->getStorageInterface();

        $database->setTranslation('testkey', [
            'en' => 'Testvalue',
            'de' => 'Testwert',
        ]);

        $manager->setModules('default');
        $manager->setFilter(null);
        $manager->setState(Application_Translate_TranslationManager::STATE_ADDED);

        $translations = $manager->getMergedTranslations();

        $this->assertCount(1, $translations);
        $this->assertArrayHasKey('testkey', $translations);
    }

    public function testFilterByScope()
    {
        $manager = $this->object;

        $database = $this->getStorageInterface();

        $database->setTranslation('dummykey', [
            'en' => 'EN text',
            'de' => 'DE Text',
        ]);

        $database->setTranslation('key2', [
            'en' => 'dummy key',
            'de' => 'Dummy',
        ]);

        $manager->setModules('default');
        $manager->setFilter('dummy');
        $manager->setScope(Application_Translate_TranslationManager::SCOPE_KEYS);

        $translations = $manager->getMergedTranslations();

        $this->assertCount(1, $translations);
        $this->assertArrayHasKey('dummykey', $translations);

        $manager->setScope(Application_Translate_TranslationManager::SCOPE_TEXT);

        $translations = $manager->getMergedTranslations();

        $this->assertCount(1, $translations);
        $this->assertArrayHasKey('key2', $translations);

        $manager->setScope(null);

        $translations = $manager->getMergedTranslations();

        $this->assertCount(2, $translations);
        $this->assertArrayHasKey('dummykey', $translations);
        $this->assertArrayHasKey('key2', $translations);
    }

    public function testGetModules()
    {
        $manager = $this->object;

        $this->adjustConfiguration([
            'setup' => ['translation' => ['modules' => ['allowed' => 'default,publish']]],
        ]);

        $modules = $manager->getModules();

        $this->assertEquals([
            'default',
            'publish',
        ], $modules);
    }

    public function testGetModulesNoRestrictions()
    {
        $this->adjustConfiguration([
            'setup' => ['translation' => ['modules' => ['allowed' => null]]],
        ]);

        $manager = $this->object;

        $modules = $manager->getModules();

        $modulesManager = Application_Modules::getInstance();

        $this->assertEquals(array_keys($modulesManager->getModules()), $modules);
    }

    public function testGetModulesRestrictionForUnknownModules()
    {
        $manager = $this->object;

        $this->adjustConfiguration([
            'setup' => ['translation' => ['modules' => ['allowed' => 'default,publish,unknown1']]],
        ]);

        $modules = $manager->getModules();

        $this->assertEquals([
            'default',
            'publish',
        ], $modules);
    }

    public function testGetAllowedModules()
    {
        $manager = $this->object;

        $this->adjustConfiguration([
            'setup' => ['translation' => ['modules' => ['allowed' => 'default,home,publish']]],
        ]);

        $modules = $manager->getAllowedModules();

        $this->assertEquals([
            'default',
            'home',
            'publish',
        ], $modules);
    }

    public function testGetAllowedModulesHandlingSpaces()
    {
        $manager = $this->object;

        $this->adjustConfiguration([
            'setup' => ['translation' => ['modules' => ['allowed' => 'default, home , publish ']]],
        ]);

        $modules = $manager->getAllowedModules();

        $this->assertEquals([
            'default',
            'home',
            'publish',
        ], $modules);
    }

    public function testGetAllowedModulesUnknownModule()
    {
        $manager = $this->object;

        $this->adjustConfiguration([
            'setup' => ['translation' => ['modules' => ['allowed' => 'default,unknown1']]],
        ]);

        $logger = new MockLogger();

        $manager->setLogger($logger);

        $modules = $manager->getAllowedModules();

        $this->assertEquals([
            'default',
        ], $modules);

        $messages = $logger->getMessages();

        $this->assertCount(1, $messages);
        $this->assertContains('setup.translation.modules.allowed', $messages[0]);
        $this->assertContains('unknown1', $messages[0]);
    }

    public function testUpdateTranslation()
    {
        $manager = $this->object;

        $oldKey = 'oldkey';

        $database = $this->getStorageInterface();

        $database->setTranslation($oldKey, [
            'en' => 'English',
            'de' => 'Deutsch',
        ], 'publish');

        $translation = $manager->getTranslation($oldKey);

        $this->assertEquals([
            'key'          => $oldKey,
            'module'       => 'publish',
            'translations' => [
                'en' => 'English',
                'de' => 'Deutsch',
            ],
            'state'        => 'added',
        ], $translation);

        $newKey = 'newkey';

        $manager->updateTranslation($newKey, [
            'en' => 'EnglishEdited',
            'de' => 'DeutschEditiert',
        ], 'admin', $oldKey);

        $translation = $manager->getTranslation($newKey);

        $this->assertEquals([
            'key'          => $newKey,
            'module'       => 'admin',
            'translations' => [
                'en' => 'EnglishEdited',
                'de' => 'DeutschEditiert',
            ],
            'state'        => 'added',
        ], $translation);

        $failed = true;

        try {
            $translation = $manager->getTranslation($oldKey);
        } catch (UnknownTranslationKeyException $ex) {
            $failed = false;
        }

        if ($failed) {
            $this->fail("Translation key '$oldKey' should have been removed.");
        }
    }

    public function testUpdateTranslationForEditedKey()
    {
        $manager = $this->object;

        $this->expectException(TranslateException::class);
        $this->expectExceptionMessage('default_add');

        $manager->updateTranslation('default_add_new', [], 'default', 'default_add');
    }

    public function testUpdateTranslationCannotModifyModuleForEditedKey()
    {
        $manager = $this->object;

        $this->expectException(TranslateException::class);
        $this->expectExceptionMessage('Module of key \'default_add\' cannot be changed.');

        $manager->updateTranslation('default_add', null, 'publish');
    }

    public function testUpdateTranslationKeepValues()
    {
        $manager = $this->object;

        $oldKey = 'oldkey';

        $database = $this->getStorageInterface();

        $database->setTranslation($oldKey, [
            'en' => 'English',
            'de' => 'Deutsch',
        ], 'publish');

        $translation = $manager->getTranslation($oldKey);

        $this->assertEquals([
            'key'          => $oldKey,
            'module'       => 'publish',
            'translations' => [
                'en' => 'English',
                'de' => 'Deutsch',
            ],
            'state'        => 'added',
        ], $translation);

        $newKey = 'newkey';

        $manager->updateTranslation($newKey, null, null, $oldKey);

        $translation = $manager->getTranslation($newKey);

        $this->assertEquals([
            'key'          => $newKey,
            'module'       => 'publish',
            'translations' => [
                'en' => 'English',
                'de' => 'Deutsch',
            ],
            'state'        => 'added',
        ], $translation);

        $failed = true;

        try {
            $translation = $manager->getTranslation($oldKey);
        } catch (UnknownTranslationKeyException $ex) {
            $failed = false;
        }

        if ($failed) {
            $this->fail("Translation key '$oldKey' should have been removed.");
        }
    }

    public function testUpdateTranslationWithoutChangingModule()
    {
        $manager = $this->object;

        $values = [
            'en' => 'Contact information',
            'de' => 'Kontaktinformationen',
        ];

        // key is part of home module
        $manager->updateTranslation('help_content_contact', $values, 'home');

        $translation = $manager->getTranslation('help_content_contact');

        $this->assertEquals($values, $translation['translations']);
    }

    public function testIsEditedTrue()
    {
        $manager = $this->object;

        $dao = $this->getStorageInterface();

        $key = 'default_add';

        $dao->setTranslation($key, [
            'en' => 'AddEdited',
            'de' => 'AnlegenEdited',
        ]);

        $this->assertTrue($manager->isEdited($key));
    }

    public function testIsEditedFalse()
    {
        $manager = $this->object;

        $this->assertFalse($manager->isEdited('default_add'));
    }

    public function testIsEditedFalseForAddedKey()
    {
        $manager = $this->object;

        $dao = $this->getStorageInterface();

        $key = 'customtestkey';

        $dao->setTranslation($key, [
            'en' => 'test key',
            'de' => 'Testschluessel',
        ]);

        $this->assertFalse($manager->isEdited($key));
    }

    public function testDeleteAll()
    {
        $manager = $this->object;

        $dao = $this->getStorageInterface();

        $addedKey  = 'customtestkey';
        $editedKey = 'default_add';

        $dao->setTranslation($addedKey, [
            'en' => 'Added key',
            'de' => 'Angelegter Schluessel',
        ]);

        $dao->setTranslation($editedKey, [
            'en' => 'Edited key',
            'de' => 'Angepasster Schluessel',
        ]);

        $this->assertNotNull($dao->getTranslation($addedKey));
        $this->assertNotNull($dao->getTranslation($editedKey));

        $manager->deleteAll();

        $this->assertNull($dao->getTranslation($addedKey));
        $this->assertNull($dao->getTranslation($editedKey));
    }

    public function testDeleteMatches()
    {
        $manager = $this->object;

        $dao = $this->getStorageInterface();

        $addedKey  = 'customtestkey';
        $editedKey = 'default_add';

        $dao->setTranslation($addedKey, [
            'en' => 'Added key',
            'de' => 'Angelegter Schluessel',
        ]);

        $dao->setTranslation($editedKey, [
            'en' => 'Edited key',
            'de' => 'Angepasster Schluessel',
        ]);

        $this->assertNotNull($dao->getTranslation($addedKey));
        $this->assertNotNull($dao->getTranslation($editedKey));

        $manager->setFilter('testkey');
        $manager->deleteMatches();

        $this->assertNull($dao->getTranslation($addedKey));
        $this->assertNotNull($dao->getTranslation($editedKey));
    }

    public function testDeleteMatchesByModule()
    {
        $manager = $this->object;

        $dao = $this->getStorageInterface();

        $addedKey  = 'customtestkey';
        $editedKey = 'default_add';

        $dao->setTranslation($addedKey, [
            'en' => 'Added key',
            'de' => 'Angelegter Schluessel',
        ]);

        $dao->setTranslation($editedKey, [
            'en' => 'Edited key',
            'de' => 'Angepasster Schluessel',
        ], 'home');

        $this->assertNotNull($dao->getTranslation($addedKey));
        $this->assertNotNull($dao->getTranslation($editedKey));

        $manager->setModules('default');
        $manager->deleteMatches();

        $this->assertNull($dao->getTranslation($addedKey));
        $this->assertNotNull($dao->getTranslation($editedKey));
    }

    public function testDeleteMatchesByState()
    {
        $manager = $this->object;

        $dao = $this->getStorageInterface();

        $addedKey  = 'customtestkey';
        $editedKey = 'default_add';

        $dao->setTranslation($addedKey, [
            'en' => 'Added key',
            'de' => 'Angelegter Schluessel',
        ]);

        $dao->setTranslation($editedKey, [
            'en' => 'Edited key',
            'de' => 'Angepasster Schluessel',
        ], 'home');

        $this->assertNotNull($dao->getTranslation($addedKey));
        $this->assertNotNull($dao->getTranslation($editedKey));

        $manager->setState($manager::STATE_EDITED);
        $manager->deleteMatches();

        $this->assertNotNull($dao->getTranslation($addedKey));
        $this->assertNull($dao->getTranslation($editedKey));
    }

    public function testDeleteMatchesByScope()
    {
        $manager = $this->object;

        $dao = $this->getStorageInterface();

        $addedKey  = 'customtestkey';
        $editedKey = 'default_add';

        $dao->setTranslation($addedKey, [
            'en' => 'Added key',
            'de' => 'Angelegter Schluessel',
        ]);

        $dao->setTranslation($editedKey, [
            'en' => 'Edited key',
            'de' => 'Angepasster Schluessel',
        ]);

        $this->assertNotNull($dao->getTranslation($addedKey));
        $this->assertNotNull($dao->getTranslation($editedKey));

        $manager->setFilter('key'); // appears in added key and in edited key en value
        $manager->setScope($manager::SCOPE_KEYS); // only look at keys
        $manager->deleteMatches();

        $this->assertNull($dao->getTranslation($addedKey));
        $this->assertNotNull($dao->getTranslation($editedKey));
    }

    public function testSetTranslation()
    {
        $database = $this->getStorageInterface();

        $manager = $this->object;

        $key    = 'customttestkey';
        $values = [
            'en' => 'English',
            'de' => 'Deutsch',
        ];

        $manager->setTranslation($key, $values, 'home');

        $translation = $database->getTranslation($key, null, 'home');

        $this->assertEquals($values, $translation);
    }

    public function testSetTranslationForDefaultKey()
    {
        $manager = $this->object;

        $values = [
            'en' => 'AddEdited',
            'de' => 'AnlegenEdited',
        ];

        $manager->setTranslation('default_add', $values);

        $translation = $manager->getTranslation('default_add');

        $this->assertArrayHasKey('state', $translation);
        $this->assertEquals('edited', $translation['state']);
        $this->assertEquals($values, $translation['translations']);
    }

    public function testGetLanguageOrderRef()
    {
        $manager = $this->object;

        $class  = new ReflectionClass(get_class($manager));
        $method = $class->getMethod('getLanguageOrderRef');
        $method->setAccessible(true);

        $order = $method->invoke($manager);

        $this->assertEquals([
            'de' => 0,
            'en' => 1,
        ], $order);
    }

    public function testGetLanguageOrder()
    {
        $this->markTestIncomplete();
    }

    public function testSetLanguageOrder()
    {
        $this->markTestIncomplete();
    }

    public function testSortLanguages()
    {
        $this->adjustConfiguration([
            'supportedLanguages' => 'de,en,fr',
        ]);

        $manager = $this->object;

        $class  = new ReflectionClass(get_class($manager));
        $method = $class->getMethod('sortLanguages');
        $method->setAccessible(true);

        $sorted = $method->invoke($manager, [
            'en' => 'English',
            'de' => 'Deutsch',
        ]);

        $this->assertEquals([
            'de' => 'Deutsch',
            'en' => 'English',
        ], $sorted);

        $this->assertTrue(array_values($sorted)[0] === 'Deutsch');
    }

    /**
     * @return Dao
     */
    protected function getStorageInterface()
    {
        return new Dao();
    }
}
