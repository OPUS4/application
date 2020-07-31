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
 * @category    Tests
 * @package     Application_Update
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2020, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

class Application_Update_ImportHelpFilesTest extends ControllerTestCase
{

    protected $additionalResources = 'translation';

    public function testRun()
    {
        $database = new Opus_Translate_Dao();
        $database->removeAll();

        $update = new Application_Update_ImportHelpFiles();
        $update->setRemoveFilesEnabled(false);
        $update->setQuietMode(true);
        $update->run();

        $translations = $database->getAll();

        // nothing should get stored in the database because default files should match TMX
        $this->assertCount(0, $translations);
    }

    public function testRunWithModifiedTranslation()
    {
        $database = new Opus_Translate_Dao();
        $database->removeAll();

        $folder = $this->createTestFolder();

        $database->setTranslation('help_content_searchtipps', [
            'en' => 'searchtipps.en.txt',
            'de' => 'searchtipps.de.txt'
        ], 'home');

        $helpFiles = new Home_Model_HelpFiles();
        $helpPath = $helpFiles->getHelpPath();

        $this->copyFiles($helpPath, $folder);

        $content = 'test content';

        file_put_contents($folder . DIRECTORY_SEPARATOR . 'searchtipps.de.txt', $content);

        $manager = new Application_Translate_TranslationManager();
        $manager->clearCache();

        $update = new Application_Update_ImportHelpFiles();
        $update->setHelpPath($folder);
        $update->setRemoveFilesEnabled(false);
        $update->setQuietMode(true);
        $update->run();

        $translations = $database->getAll();

        // one key should be stored because content was customized
        $this->assertCount(1, $translations);
        $this->assertArrayHasKey('help_content_searchtipps', $translations);
        $this->assertEquals($content, $translations['help_content_searchtipps']['de']);
    }

    public function testRunForCustomizedHelpFiles()
    {
        $database = new Opus_Translate_Dao();
        $database->removeAll();

        $helpPath = $this->createTestFolder();

        $generalDe = $this->createTestFile('general.de.txt', 'Allgemein', $helpPath);
        $generalEn = $this->createTestFile('general.en.txt', 'General', $helpPath);
        $miscDe = $this->createTestFile('misc.de.txt', 'Sonstiges', $helpPath);
        $miscEn = $this->createTestFile('misc.en.txt', 'Miscellaneous', $helpPath);

        $helpConfig = 'help_index_general[] = \'general\'' . PHP_EOL;
        $helpConfig .= 'help_index_misc[] = \'misc\'' . PHP_EOL;
        $helpIni = $this->createTestFile('help.ini', $helpConfig, $helpPath);

        $database->setTranslation('help_content_general', [
            'en' => 'general.en.txt',
            'de' => 'general.de.txt'
        ], 'help');
        $database->setTranslation('help_content_misc', [
            'en' => 'misc.en.txt',
            'de' => 'misc.de.txt'
        ], 'help');

        $update = new Application_Update_ImportHelpFiles();
        $update->setHelpPath($helpPath);
        $update->setQuietMode(true);
        $update->run();

        $this->assertFileNotExists($generalDe);
        $this->assertFileExists($generalDe . '.imported');
        $this->assertFileNotExists($generalEn);
        $this->assertFileExists($generalEn . '.imported');
        $this->assertFileNotExists($miscDe);
        $this->assertFileExists($miscDe . '.imported');
        $this->assertFileNotExists($miscEn);
        $this->assertFileExists($miscEn . '.imported');

        $translations = $database->getTranslations();
        $this->assertArrayHasKey('help_content_general', $translations);
        $this->assertArrayHasKey('help_content_misc', $translations);

        $translations = $database->getTranslation('help_content_general');
        $this->assertEquals([
            'en' => 'General',
            'de' => 'Allgemein'
        ], $translations);

        $translations = $database->getTranslation('help_content_misc');
        $this->assertEquals([
            'en' => 'Miscellaneous',
            'de' => 'Sonstiges'
        ], $translations);
    }

    public function testMoveKeysToHelp()
    {
        $update = new Application_Update_ImportHelpFiles();
        $update->setRemoveFilesEnabled(false);
        $update->setQuietMode(true);

        $database = new Opus_Translate_Dao();
        $database->removeAll();

        $database->setTranslation('help_content_misc', [
            'en' => 'MiscContentEn',
            'de' => 'MiscContentDe'
        ], 'home');
        $database->setTranslation('help_index_misc', [
            'en' => 'MiscIndexEn',
            'de' => 'MiscIndexDe'
        ], 'home');
        $database->setTranslation('help_content_imprint', [
            'en' => 'ImprintContentEn',
            'de' => 'ImprintContentDe'
        ], 'home');
        $database->setTranslation('help_content_contact', [
            'en' => 'ContactContentEn',
            'de' => 'ContactContentDe'
        ], 'home');

        $update->moveKeysToHelp();

        $translations = $database->getTranslationsWithModules();

        $this->assertCount(4, $translations);
        $this->assertArrayHasKey('help_content_misc', $translations);
        $this->assertEquals('help', $translations['help_content_misc']['module']);
        $this->assertArrayHasKey('help_index_misc', $translations);
        $this->assertEquals('help', $translations['help_index_misc']['module']);

        $this->assertEquals('home', $translations['help_content_contact']['module']);
        $this->assertEquals('home', $translations['help_content_imprint']['module']);
    }

    public function testMoveKeysToHelpForEditedKey()
    {
        $update = new Application_Update_ImportHelpFiles();
        $update->setRemoveFilesEnabled(false);
        $update->setQuietMode(true);

        $database = new Opus_Translate_Dao();
        $database->removeAll();

        $database->setTranslation('help_content_metadata', [
            'en' => 'MetadataContentEn',
            'de' => 'MetadataContentDe'
        ], 'home');

        $update->moveKeysToHelp();

        $translations = $database->getTranslationsWithModules();

        $this->assertCount(1, $translations);
        $this->assertArrayHasKey('help_content_metadata', $translations);
        $this->assertEquals('help', $translations['help_content_metadata']['module']);
    }

    public function testGetHelpFiles()
    {
        $contactFile = 'contact'; // contact files should be ignored
        $imprintFile = 'imprint'; // imprint files should be ignored
        $infoFile = 'info'; // info files (EN und DE) should be imported
        $searchFileEn = 'search.en.txt'; // lang files for key 'help_content_search' have different basenames
        $searchFileDe = 'suche.de.txt';

        $folder = $this->createTestFolder();

        // setup help content files
        $this->createTestFile("$contactFile.de.txt", 'contact DE', $folder);
        $this->createTestFile("$contactFile.en.txt", 'contact EN', $folder);
        $this->createTestFile("$imprintFile.de.txt", 'imprint DE', $folder);
        $this->createTestFile("$imprintFile.en.txt", 'imprint EN', $folder);

        $this->createTestFile("$infoFile.de.txt", 'info DE', $folder);
        $this->createTestFile("$infoFile.en.txt", 'info EN', $folder);

        $this->createTestFile($searchFileDe, 'search DE', $folder);
        $this->createTestFile($searchFileEn, 'search EN', $folder);

        // setup help.ini
        $help = 'help_index_general[] = \'search\'' . PHP_EOL;
        $help .= 'help_index_authorhelp[] = \'contact\'' . PHP_EOL;
        $help .= 'help_index_misc[] = \'imprint\'' . PHP_EOL;
        $help .= 'help_index_misc[] = \'info\'' . PHP_EOL;
        $helpIni = $this->createTestFile('help.ini', $help, $folder);

        // setup translations
        $database = new Opus_Translate_Dao();
        $database->setTranslation('help_content_contact', [
            'en' => "$contactFile.en.txt",
            'de' => "$contactFile.de.txt"
        ], 'help');
        $database->setTranslation('help_content_imprint', [
            'en' => "$imprintFile.en.txt",
            'de' => "$imprintFile.de.txt"
        ], 'help');
        $database->setTranslation('help_content_info', [
            'en' => "$infoFile.en.txt",
            'de' => "$infoFile.de.txt"
        ], 'help');
        $database->setTranslation('help_content_search', [
            'en' => $searchFileEn,
            'de' => $searchFileDe
        ], 'help');

        $update = new Application_Update_ImportHelpFiles();
        $update->setHelpPath($folder);
        $update->setRemoveFilesEnabled(false);
        $update->setQuietMode(true);

        $files = $update->getHelpFiles();

        $this->assertCount(4, $files);
        $this->assertArrayHasKey('help_content_contact', $files);
        $this->assertEquals([
            'en' => "$contactFile.en.txt",
            'de' => "$contactFile.de.txt"
        ], $files['help_content_contact']);
        $this->assertArrayHasKey('help_content_imprint', $files);
        $this->assertEquals([
            'en' => "$imprintFile.en.txt",
            'de' => "$imprintFile.de.txt"
        ], $files['help_content_imprint']);
        $this->assertArrayHasKey('help_content_info', $files);
        $this->assertEquals([
            'en' => "$infoFile.en.txt",
            'de' => "$infoFile.de.txt"
        ], $files['help_content_info']);
        $this->assertArrayHasKey('help_content_search', $files);
        $this->assertEquals([
            'en' => $searchFileEn,
            'de' => $searchFileDe
        ], $files['help_content_search']);
    }

    /**
     * In default setup content should be directly stored in translations and therefore should not point to files.
     */
    public function testGetHelpFilesForDefaultSetup()
    {
        $database = new Opus_Translate_Dao();
        $database->removeAll();

        $update = new Application_Update_ImportHelpFiles();
        $files = $update->getHelpFiles();

        $helpPath = $update->getHelpPath();

        foreach ($files as $key => $lang) {
            foreach ($lang as $file) {
                $this->assertFileNotExists($helpPath . $file);
            }
        }
    }
}
