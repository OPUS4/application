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
use Symfony\Component\Console\Tester\CommandTester;

class Application_Console_Model_EnrichmentDeleteCommandTest extends ControllerTestCase
{
    /** @var string[] */
    protected $additionalResources = ['database'];

    /** @var CommandTester */
    protected $tester;

    public const TEST_ENRICHMENT_KEY = 'MyTestEnrichment';

    public function setUp(): void
    {
        parent::setUp();

        $app = new Application();

        $command = new Application_Console_Model_EnrichmentDeleteCommand();
        $command->setApplication($app);

        $this->tester = new CommandTester($command);

        $enrichment = EnrichmentKey::new();
        $enrichment->setName(self::TEST_ENRICHMENT_KEY);
        $enrichment->store();
    }

    public function tearDown(): void
    {
        $enrichment = EnrichmentKey::fetchByName(self::TEST_ENRICHMENT_KEY);
        if ($enrichment !== null) {
            $enrichment->delete();
        }

        parent::tearDown();
    }

    public function testDeleteWithoutEnrichmentKey()
    {
        $this->tester->execute([]);

        $output = $this->tester->getDisplay();

        $this->assertStringContainsString('Enrichment key is required', $output);
    }

    public function testDeleteEnrichmentWithoutConfirmation()
    {
        $this->tester->execute([
            Application_Console_Model_EnrichmentDeleteCommand::ARGUMENT_KEY => self::TEST_ENRICHMENT_KEY,
            '-f' => true,
        ]);

        $this->assertNull(EnrichmentKey::fetchByName(self::TEST_ENRICHMENT_KEY));
    }

    public function testDeleteEnrichmentWithoutConfirmationUsingLongOption()
    {
        $this->tester->execute([
            Application_Console_Model_EnrichmentDeleteCommand::ARGUMENT_KEY => self::TEST_ENRICHMENT_KEY,
            '--force' => true,
        ]);

        $this->assertNull(EnrichmentKey::fetchByName(self::TEST_ENRICHMENT_KEY));
    }

    public function testDeleteEnrichmentWithConfirmation()
    {
        $this->tester->setInputs(['y']);

        $this->tester->execute([
            Application_Console_Model_EnrichmentDeleteCommand::ARGUMENT_KEY => self::TEST_ENRICHMENT_KEY,
        ]);

        $this->assertNull(EnrichmentKey::fetchByName(self::TEST_ENRICHMENT_KEY));
    }

    public function testDeleteEnrichmentWithConfirmationYesIsDefault()
    {
        $this->markTestSkipped('Breaks with PHP 7 and Symfony Console 4');

        $this->tester->setInputs(['']); // pressing enter at confirmation step

        $this->tester->execute([
            Application_Console_Model_EnrichmentDeleteCommand::ARGUMENT_KEY => self::TEST_ENRICHMENT_KEY,
        ]);

        $this->assertNull(EnrichmentKey::fetchByName(self::TEST_ENRICHMENT_KEY));
    }

    public function testCancelDeletionAtConfirmation()
    {
        $this->tester->setInputs(['n']); // pressing enter at confirmation step

        $this->tester->execute([
            Application_Console_Model_EnrichmentDeleteCommand::ARGUMENT_KEY => self::TEST_ENRICHMENT_KEY,
        ]);

        $this->assertNotNull(EnrichmentKey::fetchByName(self::TEST_ENRICHMENT_KEY));
    }

    public function testDeleteUnknownEnrichment()
    {
        $this->tester->execute([
            Application_Console_Model_EnrichmentDeleteCommand::ARGUMENT_KEY => 'unknownEnrichmentKey',
            '-f' => true,
        ]);

        $output = $this->tester->getDisplay();

        $this->assertStringContainsString('Enrichment key "unknownEnrichmentKey" not found', $output);
        $this->assertNotNull(EnrichmentKey::fetchByName(self::TEST_ENRICHMENT_KEY)); // no side effects
    }

    public function testDeletionRemovesTranslations()
    {
        $helper = new Admin_Model_EnrichmentKeys();

        $helper->createTranslations(self::TEST_ENRICHMENT_KEY);

        $translations = $helper->getTranslations(self::TEST_ENRICHMENT_KEY);

        $this->assertGreaterThan(1, count($translations));

        $this->tester->execute([
            Application_Console_Model_EnrichmentDeleteCommand::ARGUMENT_KEY => self::TEST_ENRICHMENT_KEY,
            '-f' => true,
        ]);

        $this->assertNull(EnrichmentKey::fetchByName(self::TEST_ENRICHMENT_KEY)); // no side effects

        $translations = $helper->getTranslations(self::TEST_ENRICHMENT_KEY);

        $this->assertCount(0, $translations);
    }
}
