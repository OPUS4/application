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
 * @copyright   Copyright (c) 2024, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\EnrichmentKey;
use Opus\Enrichment\SelectType;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class Application_Console_Tool_EnrichmentImportCommandTest extends ControllerTestCase
{
    /** @var string[] */
    protected $additionalResources = ['database'];

    /** @var CommandTester */
    protected $tester;

    public function setUp(): void
    {
        parent::setUp();

        $app = new Application();

        $command = new Application_Console_Model_EnrichmentImportCommand();
        $command->setApplication($app);

        $this->tester = new CommandTester($command);
    }

    public function testImportSingleEnrichment()
    {
        $yamlFile = APPLICATION_PATH . '/tests/resources/enrichments/singleKey.yml';

        $this->tester->execute([
            Application_Console_Model_EnrichmentImportCommand::ARGUMENT_FILE => $yamlFile,
        ]);

        $output = $this->tester->getDisplay();

        $enrichment = EnrichmentKey::fetchByName('singleEnrichmentKey');
        $this->assertNotNull($enrichment);
        $this->addModelToCleanup($enrichment);

        $this->assertStringContainsString('Created enrichment key \'singleEnrichmentKey\'', $output);
    }

    public function testImportEnrichmentAlreadyExists()
    {
        $enrichment = EnrichmentKey::new();
        $enrichment->setName('singleEnrichmentKey');
        $enrichment->store();

        $yamlFile = APPLICATION_PATH . '/tests/resources/enrichments/singleKey.yml';

        $this->tester->execute([
            Application_Console_Model_EnrichmentImportCommand::ARGUMENT_FILE => $yamlFile,
        ]);

        $output = $this->tester->getDisplay();

        $enrichment = EnrichmentKey::fetchByName('singleEnrichmentKey');
        $this->assertNotNull($enrichment);
        $this->addModelToCleanup($enrichment);

        $this->assertStringContainsString('Enrichment \'singleEnrichmentKey\' already exists', $output);
    }

    public function testImportMultipleEnrichments()
    {
        $yamlFile = APPLICATION_PATH . '/tests/resources/enrichments/multipleKeys.yml';

        $this->tester->execute([
            Application_Console_Model_EnrichmentImportCommand::ARGUMENT_FILE => $yamlFile,
        ]);

        $keys = [
            'testKey1',
            'testKey2',
            'testKey3',
            'testKey4',
        ];

        $output = $this->tester->getDisplay();

        foreach ($keys as $key) {
            $enrichment = EnrichmentKey::fetchByName($key);
            $this->assertNotNull($enrichment);
            $this->addModelToCleanup($enrichment);
        }

        foreach ($keys as $key) {
            $this->assertStringContainsString("Created enrichment key '{$key}'", $output);
        }
    }

    public function testImportMultipleEnrichmentsOneAlreadyExists()
    {
        $enrichment = EnrichmentKey::new();
        $enrichment->setName('testKey4');
        $enrichment->store();

        $yamlFile = APPLICATION_PATH . '/tests/resources/enrichments/multipleKeys.yml';

        $this->tester->execute([
            Application_Console_Model_EnrichmentImportCommand::ARGUMENT_FILE => $yamlFile,
        ]);

        $keys = [
            'testKey1',
            'testKey2',
            'testKey3',
            'testKey4',
        ];

        $output = $this->tester->getDisplay();

        foreach ($keys as $key) {
            $enrichment = EnrichmentKey::fetchByName($key);
            $this->assertNotNull($enrichment);
            $this->addModelToCleanup($enrichment);
        }

        array_pop($keys);

        foreach ($keys as $key) {
            $this->assertStringContainsString("Created enrichment key '{$key}'", $output);
        }

        $this->assertStringContainsString('Enrichment \'testKey4\' already exists', $output);
    }

    public function testImportFileNotFound()
    {
        $yamlFile = APPLICATION_PATH . '/tests/resources/enrichments/unknownFile.yml';

        $this->tester->execute([
            Application_Console_Model_EnrichmentImportCommand::ARGUMENT_FILE => $yamlFile,
        ]);

        $output = $this->tester->getDisplay();

        $this->assertStringContainsString('Input file not found', $output);
    }

    public function testImportEnrichmentWithOptions()
    {
        $yamlFile = APPLICATION_PATH . '/tests/resources/enrichments/simpleOption.yml';

        $this->tester->execute([
            Application_Console_Model_EnrichmentImportCommand::ARGUMENT_FILE => $yamlFile,
        ]);

        $enrichment = EnrichmentKey::fetchByName('conferenceType');
        $this->assertNotNull($enrichment);
        $this->addModelToCleanup($enrichment);

        $selectType = new SelectType();

        $selectType->setOptions($enrichment->getOptions());

        $this->assertEquals('none', $selectType->getValidation());
        $this->assertEquals([
            'Konferenzband',
            'Konferenzartikel',
            'Konferenz-Poster',
            'Konferenz-Abstract',
            'Sonstiges',
        ], $selectType->getValues());

        $output = $this->tester->getDisplay();

        $this->assertStringContainsString("Created enrichment key 'conferenceType'", $output);
    }

    public function testImportEnrichmentWithTranslations()
    {
        $yamlFile = APPLICATION_PATH . '/tests/resources/enrichments/simpleOption.yml';

        $this->tester->execute([
            Application_Console_Model_EnrichmentImportCommand::ARGUMENT_FILE => $yamlFile,
        ]);

        $enrichment = EnrichmentKey::fetchByName('conferenceType');
        $this->assertNotNull($enrichment);
        $this->addModelToCleanup($enrichment);

        $output = $this->tester->getDisplay();

        $this->assertStringContainsString("Created enrichment key 'conferenceType'", $output);

        $helper = new Admin_Model_EnrichmentKeys();

        $translations = $helper->getTranslations('conferenceType');

        $this->assertNotEmpty($translations);
        $this->assertEquals([
            'de' => 'Art der Konferenzveröffentlichung',
            'en' => 'Conference Type',
        ], $translations['label']);
    }
}
