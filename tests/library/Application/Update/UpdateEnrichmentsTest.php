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
 * @copyright   Copyright (c) 2025, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\Document;
use Opus\Common\Enrichment;
use Opus\Common\EnrichmentKey;
use Symfony\Component\Console\Output\NullOutput;

class Application_Update_UpdateEnrichmentsTest extends ControllerTestCase
{
    /** @var string */
    protected $additionalResources = 'database';

    /** @var string[] */
    private $cleanupKeys = [
        'testOldKey',
        'testNewKey',
    ];

    /** @var Application_Update_UpdateEnrichments */
    private $updater;

    public function setUp(): void
    {
        parent::setUp();

        $enrichmentKey = EnrichmentKey::new();
        $enrichmentKey->setName('testOldKey');
        $enrichmentKey->store();

        $this->updater = new Application_Update_UpdateEnrichments();
        $this->updater->setOutput(new NullOutput());
    }

    public function tearDown(): void
    {
        foreach ($this->cleanupKeys as $keyName) {
            $enrichmentKey = EnrichmentKey::fetchByName($keyName);
            if ($enrichmentKey) {
                $enrichmentKey->delete();
            }
        }

        parent::tearDown();
    }

    public function testUpdate()
    {
        $this->updater->update([
            'testOldKey' => 'testNewKey',
        ]);

        $this->assertNull(EnrichmentKey::fetchByName('testOldKey'));
        $this->assertNotNull(EnrichmentKey::fetchByName('testNewKey'));
    }

    public function testUpdateOldKeyDoesNotExist()
    {
        $this->updater->update([
            'testUnknownKey' => 'testNewKey',
        ]);

        $this->assertNull(EnrichmentKey::fetchByName('testUnknownKey'));
        $this->assertNotNull(EnrichmentKey::fetchByName('testNewKey'));
    }

    public function testUpdateNewKeyExists()
    {
        $enrichmentKey = EnrichmentKey::new();
        $enrichmentKey->setName('testNewKey');
        $enrichmentKey->store();

        $this->updater->update([
            'testOldKey' => 'testNewKey',
        ]);

        $this->assertNotNull(EnrichmentKey::fetchByName('testOldKey'));
        $this->assertNotNull(EnrichmentKey::fetchByName('testNewKey'));
    }

    public function testUpdateTranslations()
    {
        $this->markTestIncomplete('implement');
    }

    public function testUpdateTranslationsDoNotExist()
    {
        $this->markTestIncomplete('implement');
    }

    public function testUpdateDocuments()
    {
        $document = $this->createTestDocument();

        $enrichment = Enrichment::new();
        $enrichment->setKeyName('testOldKey');
        $enrichment->setValue('testValue');
        $document->addEnrichment($enrichment);

        $docId = $document->store();

        $this->updater->update([
            'testOldKey' => 'testNewKey',
        ]);

        $this->assertNull(EnrichmentKey::fetchByName('testOldKey'));
        $this->assertNotNull(EnrichmentKey::fetchByName('testNewKey'));

        $document   = Document::get($docId);
        $enrichment = $document->getEnrichment(0);

        $this->assertNotNull($enrichment);
        $this->assertEquals('testNewKey', $enrichment->getKeyName());
    }

    public function testQuietModeOn()
    {
        // TODO no output should be written to console
        $this->markTestIncomplete('implement');
    }
}
