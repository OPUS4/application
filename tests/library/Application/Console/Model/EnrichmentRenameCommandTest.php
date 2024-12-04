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
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Tester\CommandTester;

class Application_Console_Model_EnrichmentRenameCommandTest extends ControllerTestCase
{
    /** @var string[] */
    protected $additionalResources = ['database'];

    /** @var CommandTester */
    protected $tester;

    public const TEST_ENRICHMENT_KEY = 'MyTestEnrichmentKey';

    public function setUp(): void
    {
        parent::setUp();

        $app = new Application();

        $command = new Application_Console_Model_EnrichmentRenameCommand();
        $command->setApplication($app);

        $this->tester = new CommandTester($command);

        $enrichmentKey = EnrichmentKey::new();
        $enrichmentKey->setName(self::TEST_ENRICHMENT_KEY);
        $enrichmentKey->store();
    }

    public function tearDown(): void
    {
        $enrichmentKey = EnrichmentKey::fetchByName(self::TEST_ENRICHMENT_KEY);
        if ($enrichmentKey !== null) {
            $enrichmentKey->delete();
        }

        parent::tearDown();
    }

    public function testRenameEnrichment()
    {
        $newKey = 'newEnrichmentKey';

        $this->assertNull(EnrichmentKey::fetchByName($newKey));

        $this->tester->execute([
            Application_Console_Model_EnrichmentRenameCommand::ARGUMENT_KEY     => self::TEST_ENRICHMENT_KEY,
            Application_Console_Model_EnrichmentRenameCommand::ARGUMENT_NEW_KEY => $newKey,
        ]);

        $this->assertNull(EnrichmentKey::fetchByName(self::TEST_ENRICHMENT_KEY));

        $enrichmentKey = EnrichmentKey::fetchByName($newKey);
        $this->assertNotNull($enrichmentKey);
        $enrichmentKey->delete();

        $output = $this->tester->getDisplay();

        $oldKey = self::TEST_ENRICHMENT_KEY;

        $this->assertStringContainsString("Renaming key \"$oldKey\" to \"{$newKey}\"", $output);
        $this->assertStringContainsString("Renaming translations for enrichment key \"{$newKey}\"", $output);
    }

    public function testMissingArguments()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Not enough arguments (missing: "key, newKey")');

        $this->tester->execute([]);
    }

    public function testArgumentKeyIsUnknown()
    {
        $this->tester->execute([
            Application_Console_Model_EnrichmentRenameCommand::ARGUMENT_KEY     => 'unknownEnrichmentKey',
            Application_Console_Model_EnrichmentRenameCommand::ARGUMENT_NEW_KEY => 'newEnrichmentKey',
        ]);

        $output = $this->tester->getDisplay();

        $this->assertStringContainsString('Enrichment key "unknownEnrichmentKey" not found', $output);
    }

    public function testArgumentNewKeyMissing()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Not enough arguments (missing: "newKey")');

        $this->tester->execute([
            Application_Console_Model_EnrichmentRenameCommand::ARGUMENT_KEY => self::TEST_ENRICHMENT_KEY,
        ]);
    }

    public function testNewKeyAlreadyExists()
    {
        $this->tester->execute([
            Application_Console_Model_EnrichmentRenameCommand::ARGUMENT_KEY     => self::TEST_ENRICHMENT_KEY,
            Application_Console_Model_EnrichmentRenameCommand::ARGUMENT_NEW_KEY => 'Country',
        ]);

        $output = $this->tester->getDisplay();

        $this->assertStringContainsString('Enrichment key "Country" already exists', $output);
    }

    public function testTranslationsAreRenamed()
    {
        $newKey = 'newEnrichmentKey';

        $this->assertNull(EnrichmentKey::fetchByName($newKey));

        $helper = new Admin_Model_EnrichmentKeys();
        $helper->createTranslations(self::TEST_ENRICHMENT_KEY);

        $helper->removeTranslations($newKey); // just in case
        $this->assertCount(0, $helper->getTranslations($newKey));

        $this->tester->execute([
            Application_Console_Model_EnrichmentRenameCommand::ARGUMENT_KEY     => self::TEST_ENRICHMENT_KEY,
            Application_Console_Model_EnrichmentRenameCommand::ARGUMENT_NEW_KEY => $newKey,
        ]);

        $this->assertNull(EnrichmentKey::fetchByName(self::TEST_ENRICHMENT_KEY));

        $enrichmentKey = EnrichmentKey::fetchByName($newKey);
        $this->assertNotNull($enrichmentKey);
        $enrichmentKey->delete();

        $this->assertGreaterThan(1, count($helper->getTranslations($newKey)));
        $this->assertCount(0, $helper->getTranslations(self::TEST_ENRICHMENT_KEY));
    }
}
