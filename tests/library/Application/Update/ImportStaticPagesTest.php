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
 * @copyright   Copyright (c) 2020, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Translate\Dao;

class Application_Update_ImportStaticPagesTest extends ControllerTestCase
{
    /** @var string[] */
    protected $additionalResources = ['database', 'translation'];

    public function setUp(): void
    {
        parent::setUp();

        $dao = new Dao();
        $dao->removeAll();
    }

    public function tearDown(): void
    {
        $dao = new Dao();
        $dao->removeAll();

        parent::tearDown();
    }

    public function testRun()
    {
        $database = new Dao();

        $update = new Application_Update_ImportStaticPages();
        $update->setRemoveFilesEnabled(false);
        $update->setQuietMode(true);
        $update->run();

        $translations = $database->getAll();

        // nothing should be in database because content matches TMX
        $this->assertCount(0, $translations);
    }

    public function testImportFilesAsKey()
    {
        $update = new Application_Update_ImportStaticPages();
        $update->setQuietMode(true);
        $update->setRemoveFilesEnabled(false);

        $update->importFilesAsKey('contact', 'testkey', 'home');

        $database = new Dao();

        $translations = $database->getTranslationsWithModules();

        $this->assertCount(1, $translations);
        $this->assertArrayHasKey('testkey', $translations);
        $this->assertEquals('home', $translations['testkey']['module']);
    }

    public function testGetFiles()
    {
        $update = new Application_Update_ImportStaticPages();

        $files = $update->getFiles('contact');

        $this->assertCount(2, $files);
        $this->assertContains('contact.en.txt', $files);
        $this->assertContains('contact.de.txt', $files);
    }

    public function testGetTranslations()
    {
        $update = new Application_Update_ImportStaticPages();

        $translations = $update->getTranslations('contact');

        $this->assertNotNull($translations);
    }
}
