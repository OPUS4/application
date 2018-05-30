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
 * @author      Maximilian Salomon <salomon@zib.de>
 * @copyright   Copyright (c) 2017, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

class Application_View_Helper_LanguageWebFormTest extends ControllerTestCase
{
    /**
     * @var Application_View_Helper_LanguageWebForm
     */
    private $_helper;

    public function setUp()
    {
        parent::setUp();
        $this->useEnglish();
        $this->_helper = new Application_View_Helper_LanguageWebForm();
    }

    /**
     * @return data-provider with long and short language form.
     */
    public function langProvider()
    {
        return array(
            array('deu', 'de'),
            array('eng', 'en'),
            array('spa', 'es'),
            array('ita', 'it'),
            array('fra', 'fr'),
            array('rus', 'ru')
        );
    }

    /**
     * Unittest for languageWebform.
     * @covers ::languageWebform
     * @dataProvider langProvider
     */
    public function testLanguageWebForm($long, $short)
    {
        $this->assertEquals($this->_helper->languageWebForm($long), $short);
    }

}