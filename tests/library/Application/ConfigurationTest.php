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

use Opus\Application\Configuration;
use Opus\Common\Config;
use Opus\Common\Document;
use Opus\Document\Plugin\IdentifierDoi;
use Opus\Document\Plugin\IdentifierUrn;
use Opus\Document\Plugin\XmlCache;
use Opus\Search\Plugin\Index;

class ConfigurationTest extends ControllerTestCase
{
    /** @var string[] */
    protected $additionalResources = ['database', 'locale'];

    /** @var Configuration */
    private $config;

    public function setUp(): void
    {
        parent::setUp();
        $this->makeConfigurationModifiable();
        $this->config = new Configuration();
    }

    public function testGetConfig()
    {
        $zendConfig = $this->config->getConfig();
        $this->assertNotNull($zendConfig);
        $this->assertInstanceOf(Zend_Config::class, $zendConfig);
    }

    public function testGetLogger()
    {
        $logger = $this->config->getLogger();

        $this->assertNotNull($logger);
        $this->assertInstanceOf(Zend_Log::class, $logger);
    }

    public function testSetLogger()
    {
        $logger = new MockLogger();

        $this->config->setLogger($logger);

        $this->assertNotNull($this->config->getLogger());
        $this->assertInstanceOf(MockLogger::class, $this->config->getLogger());
    }

    public function testGetSupportedLanguages()
    {
        $this->assertEquals(['de', 'en'], $this->config->getSupportedLanguages());
    }

    public function testIsLanguageSupportedTrue()
    {
        $this->assertTrue($this->config->isLanguageSupported('en'));
    }

    public function testIsLanguageSupportedFalse()
    {
        $this->assertFalse($this->config->isLanguageSupported('ru'));
    }

    public function testIsLanguageSupportedFalseNull()
    {
        $this->assertFalse($this->config->isLanguageSupported(null));
    }

    public function testIsLanguageSupportedFalseEmpty()
    {
        $this->assertFalse($this->config->isLanguageSupported(''));
    }

    public function testGetOpusVersion()
    {
        $config = $this->getConfig();
        $this->assertEquals($config->version, Configuration::getOpusVersion());
    }

    public function testGetOpusInfo()
    {
        $data = Configuration::getOpusInfo();
        $this->assertIsArray($data);
        /* OPUSVIER-3542 Version not working the same way with git
        $this->assertArrayHasKey('admin_info_version', $data);
        $this->assertEquals($config->version, $data['admin_info_version']);
        */
    }

    public function testIsLanguageSelectionEnabledTrue()
    {
        $this->assertEquals(['de', 'en'], $this->config->getSupportedLanguages());
        $this->assertTrue($this->config->isLanguageSelectionEnabled());
    }

    public function testGetSupportedLanguagesValuesAreTrimmed()
    {
        $this->adjustConfiguration([
            'supportedLanguages' => 'en, de',
        ]);

        $this->assertEquals(['en', 'de'], $this->config->getSupportedLanguages());
    }

    public function testIsLanguageSelectionEnabledFalse()
    {
        Config::get()->supportedLanguages = 'de';
        $this->assertEquals(['de'], $this->config->getSupportedLanguages());
        $this->assertFalse($this->config->isLanguageSelectionEnabled());
    }

    public function testGetDefaultLanguage()
    {
        $this->assertEquals('de', $this->config->getDefaultLanguage());
    }

    public function testGetDefaultLanguageIfOnlyOneIsSupported()
    {
        $this->getConfig()->supportedLanguages = 'de';
        $this->assertEquals('de', $this->config->getDefaultLanguage());
    }

    public function testGetDefaultLanguageUnsupportedConfigured()
    {
        // because bootstrapping already happened locale needs to be manipulated directly
        $locale = new Zend_Locale();
        $locale->setDefault('fr');
        $this->assertEquals('de', $this->config->getDefaultLanguage());

        $locale->setDefault('de');
        $this->assertEquals('de', $this->config->getDefaultLanguage());
    }

    /**
     * Checks that the path is correct with '/' at the end.
     */
    public function testGetWorkspacePath()
    {
        $workspacePath = $this->config->getWorkspacePath();

        $this->assertEquals(APPLICATION_PATH . '/tests/workspace/', $workspacePath);
    }

    /**
     * Checks path if setting already provides '/' at the end.
     */
    public function testGetWorkspacePathSetWithSlash()
    {
        $this->adjustConfiguration([
            'workspacePath' => APPLICATION_PATH . '/tests/workspace/',
        ]);

        $workspacePath = $this->config->getWorkspacePath();

        $this->assertEquals(APPLICATION_PATH . '/tests/workspace/', $workspacePath);
    }

    public function testGetFilesPath()
    {
        $this->assertEquals(APPLICATION_PATH . '/tests/workspace/files/', $this->config->getFilesPath());
    }

    public function testGetTempPath()
    {
        $this->assertEquals(APPLICATION_PATH . '/tests/workspace/tmp/', $this->config->getTempPath());
    }

    public function testSetTempPath()
    {
        $newTempPath = $this->config->getTempPath() . 'subdir';

        $this->config->setTempPath($newTempPath);

        $this->assertEquals($newTempPath, $this->config->getTempPath());

        $this->config->setTempPath(null);

        $this->assertEquals(APPLICATION_PATH . '/tests/workspace/tmp/', $this->config->getTempPath());
    }

    public function testGetInstance()
    {
        $config = Configuration::getInstance();
        $this->assertNotNull($config);
        $this->assertInstanceOf(Configuration::class, $config);
        $this->assertSame($config, Configuration::getInstance());
    }

    public function testGetName()
    {
        $config = Configuration::getInstance();
        $this->assertEquals('OPUS 4', $config->getName());

        $this->adjustConfiguration(['name' => 'OPUS Test']);
        $this->assertEquals('OPUS Test', $config->getName());

        $zendConfig = $this->getConfig();
        unset($zendConfig->name);
        $this->assertEquals('OPUS 4', $config->getName());
    }

    public function testClearInstance()
    {
        $config = Configuration::getInstance();
        $this->assertInstanceOf(Configuration::class, $config);

        Configuration::clearInstance();

        $config2 = Configuration::getInstance();
        $this->assertInstanceOf(Configuration::class, $config2);

        $this->assertNotSame($config, $config2);
    }

    public function testGetValue()
    {
        $config = Configuration::getInstance();

        $this->assertEquals('https://orcid.org/', $config->getValue('orcid.baseUrl'));
    }

    public function testGetValueForUnknownKey()
    {
        $config = Configuration::getInstance();

        $this->assertNull($config->getValue('unknownKey'));
        $this->assertNull($config->getValue('unknownScope.unknownKey'));
        $this->assertNull($config->getValue('unknownScope.unknownKey.thirdLevel'));
    }

    public function testGetValueForArray()
    {
        $config = Configuration::getInstance();

        $subconfig = $config->getValue('orcid');

        $this->assertInstanceOf(Zend_Config::class, $subconfig);
    }

    public function testGetValueForNull()
    {
        $config = Configuration::getInstance();

        $this->assertNull($config->getValue(null));
        $this->assertNull($config->getValue(''));
    }

    public function testDocumentPlugins()
    {
        $document = Document::new();

        $this->assertEquals([
            Index::class,
            XmlCache::class,
            IdentifierUrn::class,
            IdentifierDoi::class,
        ], $document->getDefaultPlugins());
    }
}
