<?php
/*
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
 * @category    Application Unit Tests
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2019, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * FIXME Tests only if methods throw exceptions.
 */
class Application_View_Helper_LanguageSelectorTest extends ControllerTestCase
{

    protected $additionalResources = ['view', 'translation', 'mainMenu'];

    private $_helper;

    public function setUp()
    {
        parent::setUp();

        $this->useEnglish();

        $this->dispatch('/home'); // TODO needed for proper routing setup (avoidable?)

        $this->_helper = new Application_View_Helper_LanguageSelector();

        $this->_helper->setView($this->getView());
    }

    public function testLanguageConfiguredAndInResourcesGerman()
    {
        $result = $this->_helper->languageSelector();

        $this->assertCount(1, $result);

        $lang = $result[0];

        $this->assertEquals('Deutsch', $lang['name']);
        $this->assertEquals(
            '/home/index/language/language/de/rmodule/home/rcontroller/index/raction/index',
            $lang['url']
        );
    }

    public function testLanguageConfiguredAndInResourcesEnglish()
    {
        $this->useGerman();

        $result = $this->_helper->languageSelector();

        $this->assertCount(1, $result);

        $lang = $result[0];

        $this->assertEquals('English', $lang['name']);
        $this->assertEquals(
            '/home/index/language/language/en/rmodule/home/rcontroller/index/raction/index',
            $lang['url']
        );
    }

    /**
     * Only 'de' should show up in result since 'ru' is not present in TMX files.
     */
    public function testLanguageConfiguredButNotInResources()
    {
        $this->adjustConfiguration(['supportedLanguages' => 'de,en,ru']);

        $result = $this->_helper->languageSelector();

        $this->assertCount(1, $result);

        $lang = $result[0];

        $this->assertEquals('Deutsch', $lang['name']);
        $this->assertEquals(
            '/home/index/language/language/de/rmodule/home/rcontroller/index/raction/index',
            $lang['url']
        );
    }

    /**
     * Result should be empty since only one language is supported, so no other can be selected.
     */
    public function testOnlyOneLanguageConfigured()
    {
        $this->adjustConfiguration(['supportedLanguages' => 'en']);

        $result = $this->_helper->languageSelector();

        $this->assertCount(0, $result);
    }

    public function testLanguageInResourcesButNotConfigured()
    {
        $this->markTestIncomplete('Find way to add TMX with additional language during test.');
    }
}
