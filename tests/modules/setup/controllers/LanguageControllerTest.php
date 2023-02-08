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

use Opus\Common\Translate\UnknownTranslationKeyException;
use Opus\Translate\Dao;

/**
 * @covers Setup_LanguageController
 */
class Setup_LanguageControllerTest extends ControllerTestCase
{
    /** @var bool */
    protected $configModifiable = true;

    /** @var string */
    protected $additionalResources = 'all';

    public function tearDown(): void
    {
        $database = $this->getTranslationManager();
        $database->removeAll();
        parent::tearDown();
    }

    /**
     * Regression Test for OPUSVIER-2971
     */
    public function testMissingConfigMessageIsDisplayedRed()
    {
        $this->markTestSkipped('Needs to be updated for no modules allowed.');

        $this->adjustConfiguration(['setup' => ['translation' => ['modules' => ['allowed' => null]]]]);

        $this->getRequest()->setPost(['Anzeigen' => 'Anzeigen', 'search' => 'test', 'sort' => 'unit']);
        $this->dispatch('/setup/language/show');

        $this->assertAction('show');
        $this->assertController('language');
        $this->assertModule('setup');

        $this->assertResponseCode(302);

        $this->assertRedirectTo('/setup/language/error');

        $this->verifyFlashMessage('setup_language_translation_modules_missing');
    }

    public function testStoringUpdatedTranslationForKeyWithDashes()
    {
        $this->markTestSkipped('Needs to be updated for new form.');

        $translations = [
            'de' => 'Gehe zu (Edited)',
            'en' => 'Jump to (Edited)',
        ];

        $key = 'admin-actionbox-goto-section';

        $post = [
            'adminactionboxgotosection' => $translations,
            'Save'                      => 'Save',
        ];

        $request = $this->getRequest();
        $request->setPost($post);
        $request->setMethod('POST');

        $this->dispatch("/setup/language/edit/key/$key");

        $database = $this->getTranslationManager();

        $storedTranslations = $database->getTranslation($key);

        $this->assertEquals($translations, $storedTranslations);
    }

    public function testIndexAction()
    {
        $this->dispatch('/setup/language');

        $this->assertResponseCode(200);
        $this->assertXpath('//form[@id = "filter"]');
        $this->assertXpath('//input[@type = "submit" and @id = "show"]');
        $this->assertXpath('//table[@class = "table-translations"]');
    }

    public function testIndexActionRedirectPost()
    {
        $request = $this->getRequest();

        $request->setMethod('POST');
        $request->setPost([
            'modules' => 'all',
            'scope'   => 'all',
            'state'   => 'all',
            'show'    => 'Anzeigen',
        ]);

        $this->dispatch('/setup/language');

        $this->assertNotResponseCode(200);
        $this->assertRedirectTo('/setup/language/index/sort/key');

        // TODO test form is s
    }

    public function testIndexActionModulesAll()
    {
        $this->dispatch('/setup/language/index/modules/all');

        $this->assertXpathCountMin('//td[@class = "key"]', 1000);
    }

    public function testIndexActionAllPost()
    {
        $request = $this->getRequest();

        $request->setMethod('POST');
        $request->setPost([
            'modules' => 'all',
            'scope'   => 'all',
            'state'   => 'all',
        ]);

        $this->dispatch('/setup/language/index');

        $this->assertNotResponseCode(200);
        $this->assertRedirectTo('/setup/language/index/sort/key');
    }

    public function testIndexActionPost()
    {
        $request = $this->getRequest();

        $request->setMethod('POST');
        $request->setPost([
            'modules' => 'account',
            'scope'   => 'keys',
            'state'   => 'edited',
        ]);

        $this->dispatch('/setup/language');

        $this->assertNotResponseCode(200);
        $this->assertRedirectTo('/setup/language/index/sort/key/modules/account/state/edited/scope/keys');
    }

    public function testIndexActionStateEdited()
    {
        $manager = $this->getTranslationManager();

        $manager->setTranslation('default_add', [
            'en' => 'AddTest',
            'de' => 'NeuTest',
        ]);

        $this->dispatch('/setup/language/index/state/edited');

        $this->assertXpathCount('//td[@class = "key"]', 1);
    }

    public function testIndexActionStateAdded()
    {
        $manager = $this->getTranslationManager();

        $manager->setTranslation('custom_test_key', [
            'en' => 'English',
            'de' => 'Deutsch',
        ]);

        $this->dispatch('/setup/language/index/state/added');

        $this->assertXpathCount('//td[@class = "key"]', 1);
    }

    public function testIndexActionScopeKey()
    {
        $dao = $this->getTranslationManager();

        $key1 = 'testentry';
        $key2 = 'customkey2';

        $dao->setTranslation($key1, [
            'en' => 'Test key',
            'de' => 'Testschluessel',
        ]);

        $dao->setTranslation($key2, [
            'en' => 'English',
            'de' => 'Deutsch',
        ]);

        $this->dispatch('/setup/language/index/scope/key/search/key/state/added');

        $this->assertResponseCode(200);
        $this->assertNotXpath("//a[@href = \"/setup/language/edit/scope/key/search/key/state/added/key/$key1/sort/key\"]");
        $this->assertXpath("//a[@href = \"/setup/language/edit/scope/key/search/key/state/added/key/$key2/sort/key\"]");
    }

    public function testIndexActionScopeTranslation()
    {
        $dao = $this->getTranslationManager();

        $key1 = 'testentry';
        $key2 = 'customkey2';

        $dao->setTranslation($key1, [
            'en' => 'Test key',
            'de' => 'Testschluessel',
        ]);

        $dao->setTranslation($key2, [
            'en' => 'English',
            'de' => 'Deutsch',
        ]);

        $this->dispatch('/setup/language/index/scope/text/search/key/state/added');

        $this->assertResponseCode(200);
        $this->assertXpath("//a[@href = \"/setup/language/edit/scope/text/search/key/state/added/key/$key1/sort/key\"]");
        $this->assertNotXpath("//a[@href = \"/setup/language/edit/scope/text/search/key/state/added/key/$key2/sort/key\"]");
    }

    public function testResetButtonForEditedKey()
    {
        $this->useEnglish();

        $manager = $this->getTranslationManager();

        $manager->setTranslation('default_add', [
            'en' => 'AddEdited',
            'de' => 'AnlegenEdited',
        ]);

        $this->dispatch('/setup/language/index/modules/default');

        $this->assertResponseCode(200);

        $this->assertXpath('//a[@href = "/setup/language/delete/modules/default/key/default_add/sort/key"]');
        $this->assertXpathContentContains(
            '//a[@href = "/setup/language/delete/modules/default/key/default_add/sort/key"]',
            'Reset'
        );
    }

    public function testDeleteButtonForAddedKey()
    {
        $this->useEnglish();

        $manager = $this->getTranslationManager();

        $manager->setTranslation('customtestkey', [
            'en' => 'CustomKey',
            'de' => 'Testschluessel',
        ], 'home');

        $this->dispatch('/setup/language/index/modules/home');

        $this->assertResponseCode(200);

        $this->assertXpath('//a[@href = "/setup/language/delete/modules/home/key/customtestkey/sort/key"]');
        $this->assertXpathContentContains(
            '//a[@href = "/setup/language/delete/modules/home/key/customtestkey/sort/key"]',
            'Remove'
        );
    }

    public function testAddTranslationShowForm()
    {
        $this->useEnglish();

        $this->dispatch('/setup/language/add');

        $this->assertResponseCode(200);

        $this->assertQueryContentContains('//head/title', 'Add Key');
        $this->assertXpath('//input[@type=\'submit\' and @id=\'Save\']');
    }

    public function testAddTranslation()
    {
        $request = $this->getRequest();

        $request->setMethod('POST');
        $request->setPost([
            'Key'         => 'customkey',
            'KeyModule'   => 'home',
            'Translation' => [
                'en' => 'English',
                'de' => 'Deutsch',
            ],
            'Save'        => 'Speichern',
        ]);

        $this->dispatch('/setup/language/add');

        $this->assertRedirectTo('/setup/language');

        $manager = new Application_Translate_TranslationManager();

        $translation = $manager->getTranslation('customkey');

        $this->assertEquals([
            'key'          => 'customkey',
            'module'       => 'home',
            'translations' => [
                'en' => 'English',
                'de' => 'Deutsch',
            ],
            'state'        => 'added',
        ], $translation);
    }

    public function testAddTranslationCancel()
    {
        $request = $this->getRequest();

        $request->setMethod('POST');
        $request->setPost([
            'Key'         => 'customkey',
            'KeyModule'   => 'account',
            'Translation' => [
                'en' => 'English',
                'de' => 'Deutsch',
            ],
            'Cancel'      => 'Abbrechen',
        ]);

        $this->dispatch('/setup/language/add');

        $this->assertRedirectTo('/setup/language');

        $manager = new Application_Translate_TranslationManager();

        $keyFound = true;

        try {
            $manager->getTranslation('customkey');
        } catch (UnknownTranslationKeyException $ex) {
            $keyFound = false;
        }

        $this->assertFalse($keyFound, 'Key should not have been created.');
    }

    public function testResetTranslationShowForm()
    {
        $database = $this->getTranslationManager();

        $database->setTranslation('default_add', [
            'en' => 'AddEdited',
            'de' => 'HinzufuegenEdited',
        ]);

        $this->dispatch('/setup/language/delete/key/default_add');

        $this->assertXpathContentContains('//div[@class = "key"]', 'default_add');
        $this->assertXpathContentContains('//div[@class = "default"]', 'Add');
        $this->assertXpathContentContains('//div[@class = "current"]', 'AddEdited');
        $this->assertXpathContentContains('//div[@class = "default"]', 'Hinzufügen');
        $this->assertXpathContentContains('//div[@class = "current"]', 'HinzufuegenEdited');

        $this->assertXpath('//input[@type = "hidden" and @value = "default_add"]');

        // TODO test appropriate output for reset operation
    }

    public function testResetTranslationConfirmNo()
    {
        $database = $this->getTranslationManager();

        $database->setTranslation('default_add', [
            'en' => 'AddTest',
            'de' => 'AnlegenTest',
        ]);

        $request = $this->getRequest();

        $request->setMethod('POST');
        $request->setPost([
            'Id'        => 'default_add',
            'ConfirmNo' => 'No',
        ]);

        $this->dispatch('/setup/language/delete');

        $this->assertRedirectTo('/setup/language');

        $translation = $database->getTranslation('default_add');

        $this->assertEquals([
            'en' => 'AddTest',
            'de' => 'AnlegenTest',
        ], $translation);
    }

    public function testResetTranslationConfirmYes()
    {
        $database = $this->getTranslationManager();

        $database->setTranslation('default_add', [
            'en' => 'AddTest',
            'de' => 'AnlegenTest',
        ]);

        $request = $this->getRequest();

        $request->setMethod('POST');
        $request->setPost([
            'Id'         => 'default_add',
            'ConfirmYes' => 'Yes',
        ]);

        $this->assertNotNull($database->getTranslation('default_add'));

        $this->dispatch('/setup/language/delete');

        $this->assertRedirectTo('/setup/language');

        $translation = $database->getTranslation('default_add');

        $this->assertNull($translation);
    }

    public function testDeleteTranslationShowForm()
    {
        $this->useEnglish();

        $manager = $this->getTranslationManager();

        $key = 'customtestkey';

        $manager->setTranslation($key, [
            'en' => 'test key',
            'de' => 'Testschluessel',
        ]);

        $this->dispatch("/setup/language/delete/key/$key");

        $this->assertResponseCode(200);

        $this->assertNotXpath('//div[@class = "key-info"]//span[@class = "filename"]');
        $this->assertNotXpath('//div[@class = "default"]');
        $this->assertXpathContentContains('//h1', 'Delete translation key?');
    }

    public function testDeleteTranslationConfirmYes()
    {
        $dao = $this->getTranslationManager();

        $key = 'customtestkey';

        $dao->setTranslation($key, [
            'en' => 'test key',
            'de' => 'Testschluessel',
        ]);

        $request = $this->getRequest();

        $request->setMethod('POST');
        $request->setPost([
            'Id'         => $key,
            'ConfirmYes' => 'Ja',
        ]);

        $this->dispatch('/setup/language/delete');

        $this->assertRedirectTo('/setup/language');

        $this->assertNull($dao->getTranslation($key));
    }

    public function testDeleteTranslationConfirmNo()
    {
        $dao = $this->getTranslationManager();

        $key = 'customtestkey';

        $dao->setTranslation($key, [
            'en' => 'test key',
            'de' => 'Testschluessel',
        ]);

        $request = $this->getRequest();

        $request->setMethod('POST');
        $request->setPost([
            'Id'        => $key,
            'ConfirmNo' => 'Nein',
        ]);

        $this->dispatch('/setup/language/delete');

        $this->assertRedirectTo('/setup/language');

        $this->assertEquals([
            'en' => 'test key',
            'de' => 'Testschluessel',
        ], $dao->getTranslation($key));
    }

    public function testDeleteAllShowForm()
    {
        $this->useEnglish();

        $this->dispatch('/setup/language/deleteall');

        $this->assertResponseCode(200);
        $this->assertXpathContentContains('//h1', 'Remove translations?');
        $this->assertXpath('//input[@type = "radio" and @name = "DeleteAll"]');
        $this->assertXpath('//input[@type = "submit" and @name = "ConfirmYes"]');
    }

    public function testDeleteAllConfirmYes()
    {
        $database = $this->getTranslationManager();

        $database->setTranslation('default_add', [
            'en' => 'CreateTest',
            'de' => 'AnlegenTest',
        ]);

        $database->setTranslation('home_menu_label', [
            'en' => 'HomeTest',
            'de' => 'StartseiteTest',
        ]);

        $this->assertNotNull($database->getTranslation('default_add'));
        $this->assertNotNull($database->getTranslation('home_menu_label'));

        $request = $this->getRequest();

        $request->setMethod('POST');
        $request->setPost([
            'DeleteAll'  => 'all',
            'ConfirmYes' => 'Yes',
        ]);

        $this->dispatch('/setup/language/deleteall');

        $this->assertRedirectTo('/setup/language');
        $this->assertNull($database->getTranslation('default_add'));
        $this->assertNull($database->getTranslation('home_menu_label'));
    }

    public function testDeleteAllConfirmYesMatchingEntriesOnly()
    {
        $database = $this->getTranslationManager();

        $database->setTranslation('default_add', [
            'en' => 'CreateTest',
            'de' => 'AnlegenTest',
        ]);

        $database->setTranslation('home_menu_label', [
            'en' => 'HomeTest',
            'de' => 'StartseiteTest',
        ]);

        $this->assertNotNull($database->getTranslation('default_add'));
        $this->assertNotNull($database->getTranslation('home_menu_label'));

        $request = $this->getRequest();

        $request->setMethod('POST');
        $request->setPost([
            'DeleteAll'  => 'filter',
            'ConfirmYes' => 'Yes',
        ]);

        $this->dispatch('/setup/language/deleteall/search/add');

        $this->assertNull($database->getTranslation('default_add'));
        $this->assertNotNull($database->getTranslation('home_menu_label'));
    }

    public function testDeleteAllConfirmNo()
    {
        $dao = $this->getTranslationManager();

        $key = 'customtestkey';

        $dao->setTranslation($key, [
            'en' => 'English',
            'de' => 'Deutsch',
        ]);

        $request = $this->getRequest();

        $request->setMethod('POST');
        $request->setPost([
            'DeleteAll' => 'all',
            'ConfirmNo' => 'No',
        ]);

        $this->dispatch('/setup/language/deleteall');

        $this->assertRedirectTo('/setup/language');

        $this->assertNotNull($dao->getTranslation($key));
    }

    public function testEditTranslations()
    {
        $request = $this->getRequest();

        $key = 'crawlers_sitelinks_index';

        $request->setMethod('POST');
        $request->setPost([
            'Id'          => $key,
            'Translation' => [
                'en' => 'SitelinksEdited',
                'de' => 'SitelinksEdited',
            ],
            'Save'        => 'Speichern',
        ]);

        $this->dispatch('/setup/language/edit');

        $this->assertNotResponseCode(200);
        $this->assertRedirectTo('/setup/language');

        $manager = $this->getTranslationManager();

        $translation = $manager->getTranslation($key);

        $this->assertEquals([
            'en' => 'SitelinksEdited',
            'de' => 'SitelinksEdited',
        ], $translation);
    }

    public function testEditInvalidExistingKey()
    {
        $request = $this->getRequest();

        $key = 'Opus_Identifier_Type_Value_Cris-link'; // invalid because of '-'

        $request->setMethod('POST');
        $request->setPost([
            'Id'          => $key,
            'Translation' => [
                'en' => 'CRIS-LinkEdited',
                'de' => 'CRIS-LinkEdited',
            ],
            'Save'        => 'Speichern',
        ]);

        $this->dispatch('/setup/language/edit');

        $this->assertNotResponseCode(200);
        $this->assertRedirectTo('/setup/language');

        $manager = $this->getTranslationManager();

        $translation = $manager->getTranslation($key);

        $this->assertEquals([
            'en' => 'CRIS-LinkEdited',
            'de' => 'CRIS-LinkEdited',
        ], $translation);
    }

    public function testChangeNameOfAddedKey()
    {
        $database = $this->getTranslationManager();

        $oldKey = 'customkey';
        $newKey = 'renamedkey';

        $database->setTranslation($oldKey, [
            'en' => 'English',
            'de' => 'Deutsch',
        ], 'crawlers');

        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost([
            'Id'          => $oldKey,
            'Key'         => $newKey,
            'KeyModule'   => 'crawlers',
            'Translation' => [
                'en' => 'English',
                'de' => 'Deutsch',
            ],
            'Save'        => 'Speichern',
        ]);

        $this->dispatch('/setup/language/edit');

        $this->assertRedirectTo('/setup/language');

        $this->assertNull($database->getTranslation($oldKey));
        $this->assertEquals([
            'en' => 'English',
            'de' => 'Deutsch',
        ], $database->getTranslation($newKey));
    }

    public function testChangeModuleOfAddedKey()
    {
        $dao = $this->getTranslationManager();

        $key = 'customtestkey';

        $dao->setTranslation($key, [
            'en' => 'English',
            'de' => 'Deutsch',
        ], 'home');

        $manager = new Application_Translate_TranslationManager();

        $translation = $manager->getTranslation($key);

        $this->assertNotNull($translation);
        $this->assertArrayHasKey('state', $translation);
        $this->assertEquals('added', $translation['state']);
        $this->assertArrayHasKey('module', $translation);
        $this->assertEquals('home', $translation['module']);

        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost([
            'Id'          => $key,
            'Key'         => $key,
            'KeyModule'   => 'admin',
            'Translation' => [
                'en' => 'English',
                'de' => 'Deutsch',
            ],
            'Save'        => 'Speichern',
        ]);

        $this->dispatch('/setup/language/edit');

        $this->assertRedirectTo('/setup/language');

        $translation = $manager->getTranslation($key);

        $this->assertNotNull($translation);
        $this->assertArrayHasKey('state', $translation);
        $this->assertEquals('added', $translation['state']);
        $this->assertArrayHasKey('module', $translation);
        $this->assertEquals('admin', $translation['module']);
    }

    public function testExportShowPage()
    {
        $this->dispatch('/setup/language/export/modules/account');

        $this->assertResponseCode(200);
        $this->assertXpathCount(
            '//div[contains(@class, "setup_language_export")]//li/a[@class = "download-button"]',
            4
        );

        $this->assertXpath('//a[@href="/setup/language/export/modules/account/filename/opus.tmx"]');
        $this->assertXpath('//a[@href="/setup/language/export/modules/account/filename/opus.tmx/unmodified/true"]');
        $this->assertXpath('//a[@href="/setup/language/export/filename/opus.tmx"]');
        $this->assertXpath('//a[@href="/setup/language/export/filename/opus.tmx/unmodified/true"]');
    }

    public function testExportFiltered()
    {
        $dao = $this->getTranslationManager();

        $dao->setTranslation('customtestkey', [
            'en' => 'English',
            'de' => 'Deutsch',
        ], 'crawlers');

        $this->dispatch('/setup/language/export/filename/opus.tmx/modules/crawlers');

        $this->assertResponseCode(200);
        $this->assertHeaderContains('content-type', 'text/xml');
        $this->assertHeaderContains('content-disposition', 'attachment');

        $this->assertXpathCount('//tu', 1);
        $this->assertXpathCount('//tu[@creationtool = "crawlers"]', 1);
        $this->assertXpath('//tu[@tuid = "customtestkey"]');
    }

    public function testExportFilteredWithUnmodified()
    {
        $this->dispatch('/setup/language/export/filename/opus.tmx/unmodified/true/modules/crawlers');

        $this->assertResponseCode(200);
        $this->assertHeaderContains('content-type', 'text/xml');
        $this->assertHeaderContains('content-disposition', 'attachment');

        $this->assertXpathCount('//tu', 2);
        $this->assertXpathCount('//tu[@creationtool = "crawlers"]', 2);
    }

    public function testExportAll()
    {
        $dao = $this->getTranslationManager();

        $dao->setTranslation('testkey1', [
            'en' => 'Test key 1',
            'de' => 'Testschluessel 1',
        ]);

        $dao->setTranslation('testkey2', [
            'en' => 'Test key 2',
            'de' => 'Testschluessel 2',
        ], 'crawlers');

        $this->dispatch('/setup/language/export/filename/opus.tmx');

        $this->assertResponseCode(200);
        $this->assertHeaderContains('content-type', 'text/xml');
        $this->assertHeaderContains('content-disposition', 'attachment');

        $this->assertXpathCount('//tu', 2);
        $this->assertXpathCount('//tu[@creationtool = "default"]', 1);
        $this->assertXpathCount('//tu[@creationtool = "crawlers"]', 1);
        $this->assertXpath('//tu[@tuid = "testkey1"]');
        $this->assertXpath('//tu[@tuid = "testkey2"]');
    }

    public function testExportAllWithUnmodified()
    {
        $dao = $this->getTranslationManager();

        $dao->setTranslation('testkey1', [
            'en' => 'Test key 1',
            'de' => 'Testschluessel 1',
        ]);

        $dao->setTranslation('testkey2', [
            'en' => 'Test key 2',
            'de' => 'Testschluessel 2',
        ], 'crawlers');

        $this->dispatch('/setup/language/export/filename/opus.tmx/unmodified/true');

        $this->assertResponseCode(200);
        $this->assertHeaderContains('content-type', 'text/xml');
        $this->assertHeaderContains('content-disposition', 'attachment');

        $this->assertXpathCountMin('//tu', 2000);
        $this->assertXpathCountMin('//tu[@creationtool = "default"]', 500);
        $this->assertXpathCount('//tu[@creationtool = "crawlers"]', 3);
        $this->assertXpath('//tu[@tuid = "testkey1"]');
        $this->assertXpath('//tu[@tuid = "testkey2"]');
    }

    public function testImportShowForm()
    {
        $this->markTestIncomplete();
    }

    public function testImportFile()
    {
        $this->markTestIncomplete('Can a upload file be added to request object for test?');
    }

    /**
     * @return Dao
     * TODO really use translation manager (be independent of database)
     */
    protected function getTranslationManager()
    {
        return new Dao();
    }
}
