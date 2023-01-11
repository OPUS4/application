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
 * @copyright   Copyright (c) 2019, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Translate\Dao;

class Application_Update_ImportCustomTranslationsTest extends ControllerTestCase
{
    /** @var string */
    protected $additionalResources = 'database';

    /** @var string */
    private $testPath;

    public function setUp(): void
    {
        parent::setUp();

        $path = APPLICATION_PATH . '/modules/default/language_custom';

        if (! is_dir($path)) {
            mkdir($path);
        }

        $this->testPath = $path;

        $database = new Dao();
        $database->removeAll();
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

    public function testRunImportCustomTranslation()
    {
        // Setup file and folder for import test
        $tmxFile = new Application_Translate_TmxFile();
        $tmxFile->fromArray([
            'test_admin_title' => [
                'de' => 'Deutscher Titel',
                'en' => 'English Title',
            ],
            'test_description' => [
                'de' => 'Beschreibung',
                'en' => 'Description',
            ],
        ]);

        $filePath = $this->testPath . '/test.tmx';

        $tmxFile->save($filePath);

        $database = new Dao();

        // Check translations not in database
        $this->assertNull($database->getTranslation('test_admin_title'));
        $this->assertNull($database->getTranslation('test_description'));

        // Perform import operation
        $update = new Application_Update_ImportCustomTranslations();
        $update->setRemoveFilesEnabled(false);
        $update->setQuietMode(true);
        $update->run();

        // Check file and folder removed
        $this->assertFileNotExists($filePath);
        $this->assertFileNotExists($this->testPath);

        // Check translations in database
        $translation = $database->getTranslation('test_admin_title');

        $this->assertEquals([
            'de' => 'Deutscher Titel',
            'en' => 'English Title',
        ], $translation);

        $translation = $database->getTranslation('test_description');

        $this->assertEquals([
            'de' => 'Beschreibung',
            'en' => 'Description',
        ], $translation);
    }
}
