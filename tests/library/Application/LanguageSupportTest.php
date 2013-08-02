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
 * @copyright   Copyright (c) 2008-2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class Application_LanguageSupportTest extends ControllerTestCase {

    public function tearDown() {
        parent::tearDown();
        Application_LanguageSupport::getInstance()->setLogger(null); // zur Sicherheit wegen MockLogger und Singleton
    }

    public function testGetInstance() {
        $languageSupport = Application_LanguageSupport::getInstance();

        $this->assertNotNull($languageSupport);
        $this->assertInstanceOf('Application_LanguageSupport', $languageSupport);
    }

    public function testSingleton() {
        $languageSupport = Application_LanguageSupport::getInstance();

        $this->assertEquals($languageSupport, Application_LanguageSupport::getInstance());
    }

    public function testInit() {
        Zend_Registry::getInstance()->offsetUnset('Zend_Translate');

        $this->assertFalse(Zend_Registry::isRegistered('Zend_Translate'));

        $languageSupport = Application_LanguageSupport::getInstance();

        $this->assertNotNull(Zend_Registry::get('Zend_Translate'));

        $this->assertNotNull($languageSupport);
        $this->assertInstanceOf('Application_LanguageSupport', $languageSupport);
    }

    public function testLoadModule() {
        $languageSupport = Application_LanguageSupport::getInstance();

        $translate = Zend_Registry::get('Zend_Translate');

        $this->assertTrue($translate->isTranslated('home_menu_label')); // default bereits geladen
        $this->assertFalse($translate->isTranslated('admin_document_index'));

        $languageSupport->loadModule('admin');

        $this->assertTrue($translate->isTranslated('home_menu_label')); // default immer noch geladen
        $this->assertTrue($translate->isTranslated('admin_document_index'));
    }

    public function testLoadModuleUnknown() {
        $languageSupport = Application_LanguageSupport::getInstance();

        $logger = new MockLogger();

        $languageSupport->setLogger($logger);

        $languageSupport->loadModule('unknown');

        $languageSupport->setLogger(null);

        $translate = Zend_Registry::get('Zend_Translate');

        $this->assertTrue($translate->isTranslated('home_menu_label')); // default immer noch geladen

        $messages = $logger->getMessages();

        $this->assertEquals(2, count($messages));
        $this->assertContains('not found', $messages[0]);
        $this->assertContains('not found', $messages[1]);
    }

    public function testLoadModuleTwice() {
        $languageSupport = Application_LanguageSupport::getInstance();

        $translate = Zend_Registry::get('Zend_Translate');

        $this->assertTrue($translate->isTranslated('home_menu_label')); // default bereits geladen
        $this->assertFalse($translate->isTranslated('admin_document_index'));

        $languageSupport->loadModule('admin');

        $this->assertTrue($translate->isTranslated('home_menu_label')); // default immer noch geladen
        $this->assertTrue($translate->isTranslated('admin_document_index'));

        // Geladene Übersetzungen entfernen
        Zend_Registry::set('Zend_Translate', new Zend_Translate(array_merge(
            array('content' => APPLICATION_PATH . '/modules/default/language/default.tmx'),
            Application_LanguageSupport::getInstance()->getOptions())));

        $logger = new MockLogger();

        $languageSupport->setLogger($logger);
        $languageSupport->loadModule('admin'); // wird nicht noch einmal geladen
        $languageSupport->setLogger(null);

        $translate = Zend_Registry::get('Zend_Translate'); // Neues Zend_Translate verwenden

        $this->assertFalse($translate->isTranslated('admin_document_index'));

        $messages = $logger->getMessages();

        $this->assertEquals(1, count($messages));
        $this->assertEquals('Already loaded translations for module \'admin\'.', $messages[0]);
    }

    public function testGetOptions() {
        $options = Application_LanguageSupport::getInstance()->getOptions();

        $this->assertInternalType('array', $options);
        $this->assertEquals(9, count($options));
        $this->assertArrayHasKey('log', $options);
        $this->assertInstanceOf('Zend_Log', $options['log']);
    }

    public function testGetLogger() {
        $logger = Application_LanguageSupport::getInstance()->getLogger();

        $this->assertNotNull($logger);
        $this->assertInstanceOf('Zend_Log', $logger);
    }

    public function testSetLogger() {
        $logger = new MockLogger();

        Application_LanguageSupport::getInstance()->setLogger($logger);

        $this->assertNotNull(Application_LanguageSupport::getInstance()->getLogger());
        $this->assertInstanceOf('MockLogger', Application_LanguageSupport::getInstance()->getLogger());

        Application_LanguageSupport::getInstance()->setLogger(null);
    }

    public function testLoadLanguageDirectory() {
        $languageSupport = Application_LanguageSupport::getInstance();

        $translator = Zend_Registry::get('Zend_Translate');

        $this->assertFalse($translator->isTranslated('admin_document_index'));

        $languageSupport->loadLanguageDirectory(APPLICATION_PATH . '/modules/admin/language');

        $this->assertTrue($translator->isTranslated('admin_document_index'));
    }

    public function testLoadLanguageDirectoryNotFound() {
        $languageSupport = Application_LanguageSupport::getInstance();

        $logger = new MockLogger();

        $languageSupport->setLogger($logger);
        $this->assertFalse($languageSupport->loadLanguageDirectory(APPLICATION_PATH . '/unknown'));
        $languageSupport->setLogger(null);

        $messages = $logger->getMessages();

        $this->assertEquals(1, count($messages));
        $this->assertContains('not found', $messages[0]);
    }

    public function testLoadLanguageDirectoryNoFiles() {
        $languageSupport = Application_LanguageSupport::getInstance();

        $this->assertTrue($languageSupport->loadLanguageDirectory(APPLICATION_PATH . '/modules'));
    }

    public function testLoadLanguageDirectoryNoZendTranslate() {
        $languageSupport = Application_LanguageSupport::getInstance();

        Zend_Registry::getInstance()->offsetUnset('Zend_Translate');

        $this->assertTrue($languageSupport->loadLanguageDirectory(APPLICATION_PATH . '/modules'));

        $this->assertTrue(Zend_Registry::isRegistered('Zend_Translate'));
    }


}
