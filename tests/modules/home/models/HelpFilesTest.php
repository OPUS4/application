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

/**
 * Unit tests for Home_Model_HelpFiles class.
 */
class Home_Model_HelpFilesTest extends ControllerTestCase
{
    /** @var string */
    protected $additionalResources = 'translation';

    /** @var bool */
    protected $configModifiable = true;

    /** @var Home_Model_HelpFiles */
    private $help;

    public function setUp(): void
    {
        parent::setUp();

        $this->help = new Home_Model_HelpFiles();
    }

    public function testGetFiles()
    {
        $helpFiles = $this->help->getFiles();

        $this->assertNotNull($helpFiles);
        $this->assertEquals(18, count($helpFiles));
        $this->assertTrue(in_array('contact.de.txt', $helpFiles));
    }

    public function testGetFileContent()
    {
        $this->adjustConfiguration([
            'help' => [
                'useFiles' => true,
            ],
        ]);

        $content = $this->help->getContent('contact.de.txt');

        $this->assertNotEmpty($content);
        $this->assertTrue(strpos('Tragen Sie Ihre Kontaktinformationen ein.', $content) >= 0);
    }

    public function testGetFileContentBadFile()
    {
        $content = $this->help->getContent('dummy-contact.de.txt');

        $this->assertNull($content);
    }

    public function testGetFileContentNull()
    {
        $content = $this->help->getContent(null);

        $this->assertNull($content);
    }

    public function testGetFileContentForAllFiles()
    {
        $this->adjustConfiguration([
            'help' => [
                'useFiles' => true,
            ],
        ]);

        $helpFiles = $this->help->getFiles();

        foreach ($helpFiles as $file) {
            $content = $this->help->getContent($file);
            $this->assertNotEmpty($content, "Could not get content of file '$file'.");
        }
    }

    public function testGetHelpEntries()
    {
        $entries = $this->help->getHelpEntries();

        $this->assertNotNull($entries);
        $this->assertEquals(5, count(array_keys($entries)));

        $this->assertTrue(array_key_exists('help_index_general', $entries));
        $this->assertTrue(array_key_exists('help_index_misc', $entries));

        $this->assertTrue(in_array('policies', $entries['help_index_misc']));
        $this->assertTrue(in_array('documentation', $entries['help_index_misc']));
    }

    public function testHelpFileExists()
    {
        $this->markTestIncomplete("File names are translated, but translation resources not yet accessible here.");
        $entries = $this->help->getHelpEntries();

        /* TODO implement
        foreach ($entries as $section) {
            foreach ($section as $file) {
                // TODO $this->assertTrue();
            }
        }
        */
    }

    public function testIsContentAvailable()
    {
        $help = $this->help;

        $this->assertTrue($help->isContentAvailable('searchtipps'));
        $this->assertFalse($help->isContentAvailable('someUnknownKey'));
    }
}
