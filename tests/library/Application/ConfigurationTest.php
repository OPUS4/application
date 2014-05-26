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
 * @copyright   Copyright (c) 2008-2014, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class Application_ConfigurationTest extends ControllerTestCase {
    
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
        $config = Zend_Registry::get('Zend_Config');
        $this->assertInternalType('array', $data);
        $this->assertArrayHasKey('admin_info_version', $data);
        $this->assertEquals($config->version, $data['admin_info_version']);
    }
}
