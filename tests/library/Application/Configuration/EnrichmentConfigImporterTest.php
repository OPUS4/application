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
use Symfony\Component\Console\Output\BufferedOutput;

class Application_Configuration_EnrichmentConfigImporterTest extends ControllerTestCase
{
    /** @var string */
    protected $additionalResources = 'database';

    /** @var Application_Configuration_EnrichmentConfigImporter */
    private $importer;

    public function setUp(): void
    {
        parent::setUp();

        $this->importer = new Application_Configuration_EnrichmentConfigImporter();
    }

    public function testImportSingleEnrichmentFromString()
    {
        $yaml = <<<YAML
name: testKey1
YAML;

        $this->importer->importYaml($yaml);

        $enrichment = EnrichmentKey::fetchByName('testKey1');

        $this->assertNotNull($enrichment);
        $this->addModelToCleanup($enrichment);
    }

    public function testImportSelectEnrichmentConfig()
    {
        $keyName = 'conferenceType';

        $yamlFile = APPLICATION_PATH . '/tests/resources/enrichments/conferenceType.yml';

        $this->importer->import($yamlFile);

        $enrichmentKey = EnrichmentKey::fetchByName($keyName);

        $this->assertNotNull($enrichmentKey);

        $this->addModelToCleanup($enrichmentKey);

        $this->assertEquals($keyName, $enrichmentKey->getName());
        $this->assertEquals('SelectType', $enrichmentKey->getType());

        $options = $enrichmentKey->getOptions();

        $enrichmentType = new SelectType();
        $enrichmentType->setOptions($options);

        $this->assertEquals('strict', $enrichmentType->getValidation());
        $this->assertEquals([
            'Konferenzband',
            'Konferenzartikel',
            'Konferenz-Poster',
            'Konferenz-Abstract',
            'Sonstiges',
        ], $enrichmentType->getValues());

        $helper       = new Admin_Model_EnrichmentKeys();
        $translations = $helper->getTranslations($keyName);
        $helper->removeTranslations($keyName);

        $this->assertCount(7, $translations);
        $this->assertEquals([
            'de' => 'Art der Konferenzveröffentlichung',
            'en' => 'Conference Type',
        ], $translations['label']);
        $this->assertEquals([
            'de' => 'conferenceType',
            'en' => 'conferenceType',
        ], $translations['hint']);
    }

    public function testImportMultipleEnrichmentConfigs()
    {
        $yamlFile = APPLICATION_PATH . '/tests/resources/enrichments/multipleKeys.yml';

        $this->importer->import($yamlFile);

        $keys = [
            'testKey1',
            'testKey2',
            'testKey3',
            'testKey4',
        ];

        foreach ($keys as $key) {
            $enrichment = EnrichmentKey::fetchByName($key);
            $this->assertNotNull($enrichment);
            $this->addModelToCleanup($enrichment);
        }
    }

    public function testKeysAreHandledCaseInsensitive()
    {
        $yaml = <<<YAML
NAME: enrichmentKey1
TRANSLATIONS:
  LABEL:
    DE: EnrichmentKey1de
    EN: EnrichmentKey1en
YAML;

        $this->importer->importYaml($yaml);

        $enrichment = EnrichmentKey::fetchByName('enrichmentKey1');
        $this->assertNotNull($enrichment);
        $this->addModelToCleanup($enrichment);

        $helper       = new Admin_Model_EnrichmentKeys();
        $translations = $helper->getTranslations('enrichmentKey1');
        $helper->removeTranslations('enrichmentKey1');

        $this->assertCount(7, $translations);
        $this->assertEquals([
            'de' => 'EnrichmentKey1de',
            'en' => 'EnrichmentKey1en',
        ], $translations['label']);
    }

    public function testEnrichmentAlreadyExists()
    {
        $this->assertNotNull(EnrichmentKey::fetchByName('Country'));

        $yaml = <<<YAML
name: Country
YAML;

        $this->importer->importYaml($yaml);

        $enrichment = EnrichmentKey::fetchByName('Country');

        $this->assertNotNull($enrichment);
    }

    public function testUseDifferentEnrichmentKeyName()
    {
        $yaml = <<<YAML
name: enrichmentKey1
YAML;

        $this->importer->importYaml($yaml, 'enrichmentKey2');

        $enrichment = EnrichmentKey::fetchByName('enrichmentKey1');
        $this->assertNull($enrichment);

        $enrichment = EnrichmentKey::fetchByName('enrichmentKey2');
        $this->assertNotNull($enrichment);
        $this->addModelToCleanup($enrichment);
    }

    public function testUseKeyNameWhenImportingMultipleKeys()
    {
        $yaml = <<<YAML
enrichments:
  - name: enrichmentKey1
  - name: enrichmentKey2
YAML;

        $this->importer->importYaml($yaml, 'enrichmentKey3');

        $enrichment = EnrichmentKey::fetchByName('enrichmentKey1');
        $this->assertNotNull($enrichment);
        $this->addModelToCleanup($enrichment);

        $enrichment = EnrichmentKey::fetchByName('enrichmentKey2');
        $this->assertNotNull($enrichment);
        $this->addModelToCleanup($enrichment);

        $enrichment = EnrichmentKey::fetchByName('enrichmentKey3');
        $this->assertNull($enrichment);
    }

    public function testImportSelectEnrichmentWithSimpleOptions()
    {
        $yaml = <<<YAML
name: color
type: Select
options: |
  red
  green
  blue
  yellow
YAML;

        $this->importer->importYaml($yaml);

        $enrichment = EnrichmentKey::fetchByName('color');
        $this->assertNotNull($enrichment);
        $this->addModelToCleanup($enrichment);

        $enrichmentType = new SelectType();
        $enrichmentType->setOptions($enrichment->getOptions());

        $this->assertEquals('none', $enrichmentType->getValidation());
        $this->assertEquals([
            'red',
            'green',
            'blue',
            'yellow',
        ], $enrichmentType->getValues());
    }

    public function testTranslationWithUnknownKey()
    {
        $yaml = <<<YAML
name: color
translations:
  category:
    de: Kategorie
    en: Category
YAML;

        $output = new BufferedOutput();

        $this->importer->setOutput($output);
        $this->importer->importYaml($yaml);

        $enrichment = EnrichmentKey::fetchByName('color');
        $this->assertNotNull($enrichment);
        $this->addModelToCleanup($enrichment);

        $helper       = new Admin_Model_EnrichmentKeys();
        $translations = $helper->getTranslations('color');

        $this->assertCount(6, $translations); // TODO label should be added automatically

        $this->assertStringContainsString('Unsupported translation key: category', $output->fetch());
    }
}
