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

use Opus\Translate\Dao;

class Application_TranslateTest extends ControllerTestCase
{
    /** @var string[] */
    protected $additionalResources = ['database', 'translation'];

    /** @var Application_Translate */
    private $translate;

    public function setUp(): void
    {
        parent::setUp();
        $this->translate = new Application_Translate();
    }

    public function tearDown(): void
    {
        $dao = new Dao();
        $dao->removeAll();
        Zend_Translate::clearCache();
        parent::tearDown();
    }

    public static function tearDownAfterClass(): void
    {
        $translate = Application_Translate::getInstance();
        $translate->loadTranslations(true);

        parent::tearDownAfterClass();
    }

    public function testConstruct()
    {
        $translate = new Application_Translate([
            'content' => APPLICATION_PATH . '/modules/default/language/default.tmx',
        ]);

        $this->assertTrue($translate->isTranslated('home_menu_label'));
    }

    public function testLoadModules()
    {
        $this->assertFalse($this->translate->isTranslated('home_menu_label')); // 'default' module
        $this->assertFalse($this->translate->isTranslated('home_index_contact_pagetitle')); // 'home' module
        $this->assertFalse($this->translate->isTranslated('admin_index_title')); // 'admin' module

        $this->translate->loadModules();

        $this->assertTrue($this->translate->isTranslated('home_menu_label')); // 'default' module
        $this->assertTrue($this->translate->isTranslated('home_index_contact_pagetitle')); // 'home' module
        $this->assertTrue($this->translate->isTranslated('admin_index_title')); // 'admin' module
    }

    public function testGetLogger()
    {
        $logger = $this->translate->getLogger();

        $this->assertNotNull($logger);
        $this->assertInstanceOf(Zend_Log::class, $logger);
    }

    public function testSetLogger()
    {
        $logger = new MockLogger();

        $this->translate->setLogger($logger);

        $this->assertNotNull($this->translate->getLogger());
        $this->assertInstanceOf('MockLogger', $this->translate->getLogger());
    }

    public function testLoadLanguageDirectory()
    {
        $this->assertFalse($this->translate->isTranslated('admin_document_index'));

        $this->translate->loadLanguageDirectory(APPLICATION_PATH . '/modules/admin/language');

        $this->assertTrue($this->translate->isTranslated('admin_document_index'));
    }

    public function testLoadLanguageDirectoryNotFound()
    {
        $logger = new MockLogger();

        $this->translate->setLogger($logger);
        $this->assertFalse($this->translate->loadLanguageDirectory(APPLICATION_PATH . '/unknown'));

        $messages = $logger->getMessages();

        $this->assertEquals(1, count($messages));
        $this->assertContains('not found', $messages[0]);
    }

    public function testLoadLanguageDirectoryNoFiles()
    {
        $this->assertTrue($this->translate->loadLanguageDirectory(APPLICATION_PATH . '/modules'));
    }

    /**
     * Für Unit Tests ist das Logging von Untranslated normalerweise eingeschaltet.
     */
    public function testIsLogUntranslatedEnabledTrue()
    {
        $config                    = $this->getConfig();
        $config->log->untranslated = self::CONFIG_VALUE_TRUE;
        $this->assertTrue($this->translate->isLogUntranslatedEnabled());
    }

    public function testIsLogUntranslatedEnabledFalse()
    {
        $config                    = $this->getConfig();
        $config->log->untranslated = self::CONFIG_VALUE_FALSE;
        $this->assertFalse($this->translate->isLogUntranslatedEnabled());
    }

    public function testGetOptionsLogEnabled()
    {
        $config                    = $this->getConfig();
        $config->log->untranslated = self::CONFIG_VALUE_TRUE;

        $options = $this->translate->getOptions();

        $this->assertInternalType('array', $options);
        $this->assertEquals(10, count($options));
        $this->assertArrayHasKey('log', $options);
        $this->assertInstanceOf(Zend_Log::class, $options['log']);
        $this->assertTrue($options['logUntranslated']);
    }

    public function testGetOptionsLogDisabled()
    {
        $config                    = $this->getConfig();
        $config->log->untranslated = self::CONFIG_VALUE_FALSE;

        $options = $this->translate->getOptions();

        $this->assertInternalType('array', $options);
        $this->assertEquals(10, count($options));
        $this->assertFalse($options['logUntranslated']);
    }

    public function testLoggingEnabled()
    {
        $config                    = $this->getConfig();
        $config->log->untranslated = self::CONFIG_VALUE_TRUE;

        $logger = new MockLogger();

        $translate = new Application_Translate(['log' => $logger]);
        $translate->loadModules();
        $translate->setLocale('de');

        $key = 'nottranslated123';

        $this->assertFalse($translate->isTranslated($key));
        $this->assertEquals($key, $translate->translate($key));

        $messages = $logger->getMessages();
        $this->assertEquals(1, count($messages));
        $this->assertContains('Unable to translate', $messages[0]);

        // German is the fallback language for English by default. Since the key cannot be translated in English or
        // German two messages are generated.
        $translate->setLocale('en');

        $logger->clear();
        $this->assertEquals($key, $translate->translate($key));

        $messages = $logger->getMessages();
        $this->assertEquals(2, count($messages));
        $this->assertContains("Unable to translate key '$key' into locale 'en'", $messages[0]);
        $this->assertContains("Unable to translate key '$key' into locale 'de'", $messages[1]);
    }

    public function testLoggingDisabled()
    {
        $config                    = $this->getConfig();
        $config->log->untranslated = self::CONFIG_VALUE_FALSE;

        $logger = new MockLogger();

        $translate = new Application_Translate(['log' => $logger]);
        $translate->loadModules();

        $this->assertFalse($translate->isTranslated('nottranslated123'));
        $this->assertEquals('nottranslated123', $translate->translate('nottranslated123'));

        $messages = $logger->getMessages();
        $this->assertEquals(0, count($messages));
    }

    /**
     * @return string[][]
     */
    public function enLanguageDataProvider()
    {
        return [
            ['deu', 'German'],
            ['eng', 'English'],
            ['fra', 'French'],
            ['rus', 'Russian'],
            ['spa', 'Spanish'],
            ['ita', 'Italian'],
            ['por', 'Portuguese'],
            ['mul', 'Multiple languages'],
        ];
    }

    /**
     * @return string[][]
     */
    public function deLanguageDataProvider()
    {
        return [
            ['deu', 'Deutsch'],
            ['eng', 'Englisch'],
            ['fra', 'Französisch'],
            ['rus', 'Russisch'],
            ['spa', 'Spanisch'],
            ['ita', 'Italienisch'],
            ['por', 'Portugiesisch'],
            ['mul', 'Mehrsprachig'],
        ];
    }

    /**
     * @dataProvider enLanguageDataProvider
     * @param string $langId
     * @param string $translation
     */
    public function testTranslateLanguageEnglish($langId, $translation)
    {
        $this->translate->setLocale('en');
        $this->assertEquals(strtolower($translation), strtolower($this->translate->translateLanguage($langId)));
    }

    /**
     * @dataProvider deLanguageDataProvider
     * @param string $langId
     * @param string $translation
     */
    public function testTranslateLanguageGerman($langId, $translation)
    {
        $this->translate->setLocale('de');
        $this->assertEquals($translation, $this->translate->translateLanguage($langId));
    }

    public function testMixedTranslations()
    {
        $key = 'admin_title_configuration';

        // clear custom translations from database
        $database = new Dao();
        $database->removeAll();

        $translate = Application_Translate::getInstance();

        $translate->clearCache();
        $translate->loadTranslations();

        $result = $translate->translate($key, 'de');
        $this->assertNotEquals($key, $result);
        $this->assertEquals('Konfiguration', $result);

        $result = $translate->translate($key, 'en');
        $this->assertNotEquals($key, $result);
        $this->assertEquals('Configure', $result);

        // clear cache again before modifying translations in database
        $translate->clearCache();

        // add database to translation mix
        $database->setTranslation($key, [
            'en' => 'Configuration',
            'de' => 'Einstellungen',
        ], 'admin');

        // load module again with changes in database
        Zend_Translate::clearCache();

        $translate = new Application_Translate();
        $translate->loadTranslations();

        $result = $translate->translate($key, 'en');
        $this->assertEquals('Configuration', $result);

        $result = $translate->translate($key, 'de');
        $this->assertEquals('Einstellungen', $result);
    }

    public function testGetTranslations()
    {
        $database = new Dao();
        $database->removeAll();

        Zend_Translate::clearCache();

        $translate = Application_Translate::getInstance();
        $translate->loadTranslations(true);

        $key = 'default_collection_role_ddc';

        $translations = $translate->getTranslations($key);

        $this->assertEquals([
            'de' => 'DDC-Klassifikation',
            'en' => 'Dewey Decimal Classification',
        ], $translations);

        $custom = [
            'de' => 'DDC-Sachgruppen',
            'en' => 'DDC',
        ];

        $database->setTranslation($key, $custom, 'default');

        // new object necessary, because translation have already been loaded
        Zend_Translate::clearCache();
        $translate->loadDatabase();

        $translations = $translate->getTranslations($key);

        $this->assertEquals($custom, $translations);
    }

    public function testGetTranslationsUnknownKey()
    {
        $translate = Application_Translate::getInstance();

        $this->assertNull($translate->getTranslations('unknownkey9999'));
    }

    public function testSetTranslations()
    {
        $dao = new Dao();

        $dao->remove('testkey');

        $this->assertNull($dao->getTranslation('testkey'));

        $translate = Application_Translate::getInstance();

        $data = [
            'en' => 'test key',
            'de' => 'Testschüssel',
        ];

        $translate->setTranslations('testkey', $data);

        $this->assertEquals($data, $dao->getTranslation('testkey'));
    }

    public function testLoadingPerformance()
    {
        $this->markTestSkipped('Used for manual performance testing.');

        $translate = new Application_Translate();

        for ($i = 0; $i < 1000; $i++) {
            Zend_Translate::clearCache();
            $translate->loadTranslations();
        }
    }

    public function testFallbackToDefaultLanguage()
    {
        $this->useGerman();

        $translate = Application_Translate::getInstance();

        $this->assertInstanceOf('Application_Translate', $translate);

        $key = 'test_fallback';

        $dao = new Dao();

        $dao->setTranslation($key, [
            'en' => 'English',
        ]);

        Zend_Translate::clearCache();

        $translate->loadTranslations();

        // TODO this is currently necessary (not sure why)
        Application_Translate::getInstance()->setLocale('de');

        $this->assertTrue($translate->isTranslated($key));

        $translation = $translate->translate($key);

        $this->assertEquals('English', $translation);
    }
}
