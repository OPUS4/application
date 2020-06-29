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
 * @category    Unit Tests
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2019, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Tests language resources across modules.
 *
 * @author Jens Schwidder <schwidder@zib.de>
 */
class LanguageTest extends ControllerTestCase
{

    public function setUp()
    {
        parent::setUp();
        $this->verifyCommandAvailable('xmllint');
    }

    public function getTmxFiles()
    {
        $dir = APPLICATION_PATH . '/modules';
        $DirIter = new RecursiveDirectoryIterator($dir);
        $Iterator = new RecursiveIteratorIterator($DirIter);
        $Regex = new RegexIterator($Iterator, '/^.+\.tmx$/i', RecursiveRegexIterator::GET_MATCH);

        $files = [];
        foreach ($Regex as $file) {
            $files[] = [$file[0]];
        }

        return $files;
    }

    /**
     * @dataProvider getTmxFiles
     */
    public function testTmxFileValid($filePath)
    {
        $xmllintCall = "xmllint --valid --noout {$filePath}";
        exec(escapeshellcmd($xmllintCall), $xmllintOutput, $xmllintFeedback);
        $this->assertEquals('0', $xmllintFeedback, "File '$filePath' not valid. (Check with 'xmllint'!)");
    }

    /**
     * Test für das Umschalten der Sprache in Unit Tests.
     */
    public function testSetLanguage()
    {
        $this->application->bootstrap('translation');

        $translator = Zend_Registry::get('Zend_Translate');

        $this->useGerman();

        $this->assertEquals('Startseite', $translator->translate('home_menu_label'));

        $this->useEnglish();

        $this->assertEquals('Home', $translator->translate('home_menu_label'));

        $this->useGerman();

        $this->assertEquals('Startseite', $translator->translate('home_menu_label'));
    }
}
