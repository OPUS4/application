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
 * @package     View_Helper
 * @author      Maximilian Salomon <salomon@zib.de>
 * @copyright   Copyright (c) 2018, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

class Application_View_Helper_JavascriptTranslationTest extends ControllerTestCase
{
    private $helper;

    public function setUp()
    {
        parent::setUp();

        $this->useEnglish();

        $this->helper = new Application_View_Helper_JavascriptTranslation();

        $this->helper->setView(Zend_Registry::get('Opus_View'));
    }

    /**
     * Tests, if the correct code-snippet for the translation will be generated.
     */
    public function testJavascriptTranslation()
    {
        $translations = [
            'key1' => 'message1',
            'key2' => 'message2'
        ];
        $this->helper->setTranslations($translations);

        $expectation = '<script type="text/javascript">' . "\n"
            . '            messages.key1 = "message1";' . "\n"
            . '            messages.key2 = "message2";' . "\n"
            . '        </script>';

        $reality = $this->helper->javascriptTranslation();
        $this->assertEquals($expectation, $reality);
    }

    /**
     * Tests, if the addTranslation-function translates in the correct way and deliver the correct key-message pairs.
     */
    public function testAddTranslation()
    {
        $this->helper->addTranslation('key1', 'message1');
        $this->helper->addTranslation('identifierInvalidFormat');
        $this->helper->addTranslation('testkey');

        $translations = [
            'key1' => 'message1',
            'identifierInvalidFormat' => "'%value%' is malformed.",
            'testkey' => 'testkey'
        ];

        $this->assertEquals($translations, $this->helper->getTranslations());
    }

    public function testSetTranslations()
    {
        $translations = [
            'key1' => 'message1',
            'key2' => 'message2'
        ];
        $this->helper->setTranslations($translations);
        $this->assertEquals($translations, $this->helper->getTranslations());
    }

    /**
     * If the translations are empty or set to null, the code snippet for the translation without any key-message pairs
     * should be delivered. This is tested here.
     */
    public function testSetNullTranslations()
    {
        $this->helper->setTranslations(null);
        $reality = $this->helper->javascriptTranslation();
        $expectation = '<script type="text/javascript">' . "\n"
            . '        </script>' . "\n";
        $this->assertEquals($expectation, $reality);
    }

    public function testGetTranslations()
    {
        $this->assertEquals([], $this->helper->getTranslations());
    }
}
