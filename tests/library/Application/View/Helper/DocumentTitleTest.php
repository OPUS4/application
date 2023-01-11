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
 * @copyright   Copyright (c) 2017, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

class Application_View_Helper_DocumentTitleTest extends ControllerTestCase
{
    /** @var string[] */
    protected $additionalResources = ['database', 'view', 'translation'];

    /** @var Application_View_Helper_DocumentTitle */
    private $helper;

    public function setUp(): void
    {
        parent::setUp();

        $this->helper = new Application_View_Helper_DocumentTitle();
        $this->helper->setView($this->getView());
    }

    public function testDocumentTitle()
    {
        $doc = $this->createTestDocument();
        $doc->setLanguage('deu');

        $title = $doc->addTitleMain();
        $title->setValue('Deutsch');
        $title->setLanguage('deu');

        $title = $doc->addTitleMain();
        $title->setValue('English');
        $title->setLanguage('eng');

        $doc->store();

        $this->assertEquals('Deutsch', $this->helper->documentTitle($doc));
    }

    public function testDocumentTitleEscaped()
    {
        $doc = $this->createTestDocument();
        $doc->setLanguage('deu');

        $title = $doc->addTitleMain();
        $title->setValue('<b>Deutsch</b>');
        $title->setLanguage('deu');

        $doc->store();

        $this->assertEquals('&lt;b&gt;Deutsch&lt;/b&gt;', $this->helper->documentTitle($doc));
    }

    public function testDocumentTitleNoTitle()
    {
        $doc = $this->createTestDocument();
        $doc->store();

        $this->assertNull($this->helper->documentTitle($doc));
    }

    public function testDocumentNoLanguage()
    {
        $doc = $this->createTestDocument();

        $title = $doc->addTitleMain();
        $title->setValue('Deutsch');
        $title->setLanguage('deu');

        $doc->store();

        $this->assertEquals('Deutsch', $this->helper->documentTitle($doc));
    }

    public function testDocumentTitleUserInterfaceLanguage()
    {
        $this->adjustConfiguration(
            [
                'search' => [
                    'result' => [
                        'display' => [
                            'preferUserInterfaceLanguage' => self::CONFIG_VALUE_TRUE,
                        ],
                    ],
                ],
            ]
        );

        $this->assertTrue($this->helper->isPreferUserInterfaceLanguage());

        $doc = $this->createTestDocument();
        $doc->setLanguage('deu');

        $title = $doc->addTitleMain();
        $title->setValue('Deutsch');
        $title->setLanguage('deu');

        $title = $doc->addTitleMain();
        $title->setValue('English');
        $title->setLanguage('eng');

        $doc->store();

        $this->useEnglish();

        $this->assertEquals('English', $this->helper->documentTitle($doc));

        $this->useGerman();

        $this->assertEquals('Deutsch', $this->helper->documentTitle($doc));
    }

    public function testIsPreferUserInterfaceLanguage()
    {
        $this->assertFalse($this->helper->isPreferUserInterfaceLanguage());

        $this->helper->setPreferUserInterfaceLanguage(true);

        $this->assertTrue($this->helper->isPreferUserInterfaceLanguage());
    }

    public function testSetPreferUserInterfaceLanguage()
    {
        // true
        $this->helper->setPreferUserInterfaceLanguage(true);
        $this->assertTrue($this->helper->isPreferUserInterfaceLanguage());

        $this->helper->setPreferUserInterfaceLanguage('true');
        $this->assertTrue($this->helper->isPreferUserInterfaceLanguage());

        $this->helper->setPreferUserInterfaceLanguage(1);
        $this->assertTrue($this->helper->isPreferUserInterfaceLanguage());

        $this->helper->setPreferUserInterfaceLanguage('1');
        $this->assertTrue($this->helper->isPreferUserInterfaceLanguage());

        $this->helper->setPreferUserInterfaceLanguage(self::CONFIG_VALUE_TRUE);
        $this->assertTrue($this->helper->isPreferUserInterfaceLanguage());

        // false
        $this->helper->setPreferUserInterfaceLanguage('bla');
        $this->assertFalse($this->helper->isPreferUserInterfaceLanguage());

        $this->helper->setPreferUserInterfaceLanguage(false);
        $this->assertFalse($this->helper->isPreferUserInterfaceLanguage());

        $this->helper->setPreferUserInterfaceLanguage(0);
        $this->assertFalse($this->helper->isPreferUserInterfaceLanguage());

        $this->helper->setPreferUserInterfaceLanguage(self::CONFIG_VALUE_FALSE);
        $this->assertFalse($this->helper->isPreferUserInterfaceLanguage());
    }
}
