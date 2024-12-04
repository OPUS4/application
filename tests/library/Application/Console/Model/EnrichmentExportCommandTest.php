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

class Application_Console_Model_EnrichmentExportCommandTest extends ControllerTestCase
{
    /** @var string[] */
    protected $additionalResources = ['database'];

    /** @var CommandTester */
    protected $tester;

    public function setUp(): void
    {
        parent::setUp();

        $app = new Application();

        $command = new Application_Console_Model_EnrichmentExportCommand();
        $command->setApplication($app);

        $this->tester = new CommandTester($command);
    }

    public function testExportEnrichment()
    {
        $this->tester->execute([
            Application_Console_Model_EnrichmentExportCommand::ARGUMENT_KEYS => ['opus.import.checksum'],
        ]);

        $output = $this->tester->getDisplay();

        $expected = <<<EOT
name: opus.import.checksum
EOT;

        $this->assertEquals($expected, trim($output)); // trim additional line breaks at end of output
    }

    public function testExportEnrichmentWithTranslations()
    {
        $this->tester->execute([
            Application_Console_Model_EnrichmentExportCommand::ARGUMENT_KEYS => ['Country'],
        ]);

        $output = $this->tester->getDisplay();

        $expected = <<<EOT
name: Country
translations:
  header:
    de: Land
    en: Country
  label:
    de: 'Land der Veranstaltung'
    en: 'Country of event'
EOT;

        $this->assertEquals($expected, trim($output)); // trim additional line breaks at end of output
    }

    public function testExportMultipleEnrichmentKeys()
    {
        $this->tester->execute([
            Application_Console_Model_EnrichmentExportCommand::ARGUMENT_KEYS => [
                'opus.import.date',
                'opus.import.file',
            ],
        ]);

        $output = $this->tester->getDisplay();

        $this->assertStringContainsString('enrichments:', $output);
        $this->assertStringContainsString('  - name: opus.import.date', $output);
        $this->assertStringContainsString('  - name: opus.import.file', $output);
    }

    public function testExportAllEnrichments()
    {
        $this->tester->execute([]);

        $output = $this->tester->getDisplay();

        $this->assertStringContainsString('enrichments:', $output);

        $exportCount = substr_count($output, '- name:');

        $allKeys = EnrichmentKey::getKeys();

        $this->assertEquals(count($allKeys), $exportCount);
    }

    public function testUnknownEnrichmentKey()
    {
        $this->tester->execute([
            Application_Console_Model_EnrichmentExportCommand::ARGUMENT_KEYS => [
                'UnknownKey',
            ],
        ]);

        $output = $this->tester->getDisplay();

        $this->assertStringContainsString('Enrichment key "UnknownKey" not found', $output);
    }

    public function testExportMultipleEnrichmentKeysWithUnknownKeys()
    {
        $this->tester->execute([
            Application_Console_Model_EnrichmentExportCommand::ARGUMENT_KEYS => [
                'Country',
                'UnknownKey1',
                'UnknownKey2',
            ],
        ]);

        $output = $this->tester->getDisplay();

        $this->assertStringContainsString('Enrichment key "UnknownKey1" not found', $output);
        $this->assertStringNotContainsString('Enrichment key "UnknownKey2" not found', $output);
    }

    public function testExportSelectEnrichment()
    {
        $enrichmentKey = EnrichmentKey::new();
        $enrichmentKey->setName('testSelect');
        $enrichmentKey->setType('SelectType');

        $selectType = new SelectType();
        $selectType->setOptions([
            'validation' => 'strict',
            'values'     => [
                'Item1',
                'Item2',
                'Item3',
            ],
        ]);

        $enrichmentKey->setOptions($selectType->getOptions());
        $enrichmentKey->store();

        $this->tester->execute([
            Application_Console_Model_EnrichmentExportCommand::ARGUMENT_KEYS => ['testSelect'],
        ]);

        // cleanup before checks
        $enrichmentKey->delete();

        $output = $this->tester->getDisplay();

        $expected = <<<EOT
name: testSelect
type: Select
options:
  values:
    - Item1
    - Item2
    - Item3
  validation: strict
EOT;

        $this->assertEquals($expected, trim($output));
    }

    public function testExportEnrichmentToOutputFile()
    {
        $tempFile = $this->getTempFile();

        $this->tester->execute([
            Application_Console_Model_EnrichmentExportCommand::ARGUMENT_KEYS => ['Country'],
            '--outputFile'                                                   => $tempFile,
        ]);

        $output = $this->tester->getDisplay();

        $this->assertEmpty($output);

        $yaml = file_get_contents($tempFile);

        $expected = <<<EOT
name: Country
translations:
  header:
    de: Land
    en: Country
  label:
    de: 'Land der Veranstaltung'
    en: 'Country of event'
EOT;

        $this->assertEquals($expected, trim($yaml));
    }
}
