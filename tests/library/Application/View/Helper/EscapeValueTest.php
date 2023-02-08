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

class Application_View_Helper_EscapeValueTest extends ControllerTestCase
{
    /** @var string[] */
    protected $additionalResources = ['view', 'translation'];

    /** @var Application_View_Helper_EscapeValue */
    private $helper;

    public function setUp(): void
    {
        parent::setUp();

        $this->helper = new Application_View_Helper_EscapeValue();
        $this->helper->setView($this->getView());
    }

    public function testEscapeValueNull()
    {
        $this->assertEquals('', $this->helper->escapeValue(null));
    }

    public function testEscapeValueSimple()
    {
        $this->assertEquals('SimpleValue', $this->helper->escapeValue('SimpleValue'));
    }

    public function testEscapeValueHtml()
    {
        $this->assertEquals('&lt;b&gt;HTML&lt;/b&gt;', $this->helper->escapeValue('<b>HTML</b>'));
    }

    public function testEscapeValueArray()
    {
        $this->assertEquals(['Value1', '&lt;b&gt;Value2&lt;/b&gt;'], $this->helper->escapeValue([
            'Value1',
            '<b>Value2</b>',
        ]));
    }

    public function testEscapeValueArrayRecursive()
    {
        $this->assertEquals([
            '&lt;b&gt;Value1&lt;/b&gt;',
            ['Value2a', '&lt;i&gt;Value2b&lt;/i&gt;'],
            'Value3',
        ], $this->helper->escapeValue([
            '<b>Value1</b>',
            ['Value2a', '<i>Value2b</i>'],
            'Value3',
        ]));
    }

    public function testEscapeValueHighlightNullEnglish()
    {
        $this->useEnglish();
        $this->assertEquals('<span class="null">NULL</span>', $this->helper->escapeValue(null, true));
    }

    public function testEscapeValueHighlightNullGerman()
    {
        $this->useGerman();
        $this->assertEquals('<span class="null">LEER</span>', $this->helper->escapeValue(null, true));
    }
}
