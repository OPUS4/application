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
 * @category    Application Unit Test
 * @package     Application
 * @author      Jens Schwidder <schwidder@zib.de>
 * @author      Michael Lang <lang@zib.de
 * @copyright   Copyright (c) 2008-2015, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

class Application_ConfigurationTest extends ControllerTestCase {

    /**
     * @var Application_Configuration
     */
    private $config;

    public function setUp() {
        parent::setUp();
        $this->config = new Application_Configuration();
    }

    public function testGetConfig() {
        $zendConfig = $this->config->getConfig();
        $this->assertNotNull($zendConfig);
        $this->assertInstanceOf('Zend_Config', $zendConfig);
    }

    public function testGetLogger() {
        $logger = $this->config->getLogger();

        $this->assertNotNull($logger);
        $this->assertInstanceOf('Zend_Log', $logger);
    }

    public function testSetLogger() {
        $logger = new MockLogger();

        $this->config->setLogger($logger);

        $this->assertNotNull($this->config->getLogger());
        $this->assertInstanceOf('MockLogger', $this->config->getLogger());
    }

    public function testGetSupportedLanguages() {
        $this->assertEquals(array('de', 'en'), $this->config->getSupportedLanguages());
    }

    public function testIsLanguageSupportedTrue() {
        $this->assertTrue($this->config->isLanguageSupported('en'));
    }

    public function testIsLanguageSupportedFalse() {
        $this->assertFalse($this->config->isLanguageSupported('ru'));
    }

    public function testIsLanguageSupportedFalseNull() {
        $this->assertFalse($this->config->isLanguageSupported(null));
    }

    public function testIsLanguageSupportedFalseEmpty() {
        $this->assertFalse($this->config->isLanguageSupported(''));
    }

    public function testGetOpusVersion()  {
        $config = Zend_Registry::get('Zend_Config');
        $this->assertEquals($config->version, Application_Configuration::getOpusVersion());
    }

    public function testGetOpusInfo() {
        $data = Application_Configuration::getOpusInfo();
        $this->assertInternalType('array', $data);
        /* OPUSVIER-3542 Version not working the same way with git
        $this->assertArrayHasKey('admin_info_version', $data);
        $this->assertEquals($config->version, $data['admin_info_version']);
        */
    }

    public function testIsLanguageSelectionEnabledTrue() {
        $this->assertEquals(array('de', 'en'), $this->config->getSupportedLanguages());
        $this->assertTrue($this->config->isLanguageSelectionEnabled());
    }

    public function testIsLanguageSelectionEnabledFalse() {
        Zend_Registry::get('Zend_Config')->supportedLanguages = 'de';
        $this->assertEquals(array('de'), $this->config->getSupportedLanguages());
        $this->assertFalse($this->config->isLanguageSelectionEnabled());
    }

    public function testGetDefaultLanguage() {
        $this->assertEquals('de', $this->config->getDefaultLanguage());
    }

    public function testGetDefaultLanguageIfOnlyOneIsSupported() {
        Zend_Registry::get('Zend_Config')->supportedLanguages = 'de';
        $this->assertEquals('de', $this->config->getDefaultLanguage());
    }

    public function testGetDefaultLanguageUnsupportedConfigured() {
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
    public function testGetWorkspacePath() {
        $workspacePath = $this->config->getWorkspacePath();

        $this->assertEquals(APPLICATION_PATH . '/tests/workspace/', $workspacePath);
    }

    /**
     * Checks path if setting already provides '/' at the end.
     */
    public function testGetWorkspacePathSetWithSlash() {
        Zend_Registry::get('Zend_Config')->merge(new Zend_Config(array(
            'workspacePath' => APPLICATION_PATH . '/tests/workspace/'
        )));

        $workspacePath = $this->config->getWorkspacePath();

        $this->assertEquals(APPLICATION_PATH . '/tests/workspace/', $workspacePath);
    }

    public function testGetFilesPath() {
        $this->assertEquals(APPLICATION_PATH . '/tests/workspace/files/', $this->config->getFilesPath());
    }

    public function testGetTempPath() {
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

    public function testGetInstance() {
        $config = Application_Configuration::getInstance();
        $this->assertNotNull($config);
        $this->assertInstanceOf('Application_Configuration', $config);
        $this->assertSame($config, Application_Configuration::getInstance());
    }

    public function testGetName() {
        $config = Application_Configuration::getInstance();
        $this->assertEquals('OPUS 4', $config->getName());

        Zend_Registry::get('Zend_Config')->merge(new Zend_Config(array('name' => 'OPUS Test')));
        $this->assertEquals('OPUS Test', $config->getName());

        $zendConfig = Zend_Registry::get('Zend_Config');
        unset($zendConfig->name);
        $this->assertEquals('OPUS 4', $config->getName());
    }

    public function testClearInstance()
    {
        $config = Application_Configuration::getInstance();
        $this->assertInstanceOf('Application_Configuration', $config);

        Application_Configuration::clearInstance();

        $config2 = Application_Configuration::getInstance();
        $this->assertInstanceOf('Application_Configuration', $config2);

        $this->assertNotSame($config, $config2);
    }

    public function testGetValue()
    {
        $config = Application_Configuration::getInstance();

        $this->assertEquals('https://orcid.org/', $config->getValue('orcid.baseUrl'));
    }

    public function testGetValueForUnknownKey()
    {
        $config = Application_Configuration::getInstance();

        $this->assertNull($config->getValue('unknownKey'));
        $this->assertNull($config->getValue('unknownScope.unknownKey'));
        $this->assertNull($config->getValue('unknownScope.unknownKey.thirdLevel'));
    }

    public function testGetValueForArray()
    {
        $config = Application_Configuration::getInstance();

        $subconfig = $config->getValue('orcid');

        $this->assertInstanceOf('Zend_Config', $subconfig);
    }

    public function testGetValueForNull()
    {
        $config = Application_Configuration::getInstance();

        $this->assertNull($config->getValue(null));
        $this->assertNull($config->getValue(''));
    }

}
