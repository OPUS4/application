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
use Opus\Common\Model\ModelException;
use Opus\Common\Person;

class Application_Orcid_ValidateAllIdentifierOrcidTest extends ControllerTestCase
{
    /** @var string[] */
    protected $additionalResources = ['database', 'locale', 'indexPlugin'];

    /** @var int */
    private $docId1;

    /** @var int */
    private $docId2;

    public function setUp(): void
    {
        parent::setUp();

        $doc    = $this->createTestDocument();
        $person = Person::new();
        $person->setLastName('Tester');
        $person->setIdentifierOrcid('1111-2222-3333-4444');
        $doc->addPersonAuthor($person);
        $person = Person::new();
        $person->setLastName('Tester2');
        $person->setIdentifierOrcid('2222-2222-3333-4444');
        $doc->addPersonEditor($person);
        $enrichment = Enrichment::new();
        $enrichment->setKeyName('City');
        $enrichment->setValue('Berlin');
        $doc->addEnrichment($enrichment);
        $this->docId1 = $doc->store();

        $doc    = $this->createTestDocument();
        $person = Person::new();
        $person->setLastName('Tester');
        $person->setIdentifierOrcid('0000-0002-1825-0097');
        $doc->addPersonAuthor($person);
        $enrichment = Enrichment::new();
        $enrichment->setKeyName(Application_Orcid_ValidateAllIdentifierOrcid::ERROR_ENRICHMENT);
        $enrichment->setValue(Application_Orcid_ValidateAllIdentifierOrcid::ORCID_ERROR_CODE);
        $doc->addEnrichment($enrichment);
        $enrichment = Enrichment::new();
        $enrichment->setKeyName('City');
        $enrichment->setValue('Berlin');
        $doc->addEnrichment($enrichment);
        $this->docId2 = $doc->store();
    }

    public function testRun()
    {
        $validate = new Application_Orcid_ValidateAllIdentifierOrcid();
        $validate->setTaggingEnabled(true);
        $validate->run();

        $doc = Document::get($this->docId1);

        $enrichments = $doc->getEnrichment();
        $this->assertCount(2, $enrichments);

        $tag = $doc->getEnrichmentValue(Application_Orcid_ValidateAllIdentifierOrcid::ERROR_ENRICHMENT);
        $this->assertNotNull($tag);
        $this->assertEquals(Application_Orcid_ValidateAllIdentifierOrcid::ORCID_ERROR_CODE, $tag);

        $doc = Document::get($this->docId2);

        $enrichments = $doc->getEnrichment();
        $this->assertCount(1, $enrichments);
        $value = $doc->getEnrichmentValue('City');
        $this->assertNotNull($value);
    }

    public function testAddTag()
    {
        $doc = $this->createTestDocument();

        $enrichment = Enrichment::new();
        $enrichment->setKeyName(Application_Orcid_ValidateAllIdentifierOrcid::ERROR_ENRICHMENT);
        $enrichment->setValue('DOC_ERROR_FULLTEXT');
        $doc->addEnrichment($enrichment);
        $docId = $doc->store();

        $validate = new Application_Orcid_ValidateAllIdentifierOrcid();
        $validate->addTag(Document::get($docId));

        $doc         = Document::get($docId);
        $enrichments = $doc->getEnrichment();
        $this->assertCount(2, $enrichments);
    }

    public function testAddTagAlreadyTagged()
    {
        $doc = $this->createTestDocument();

        $enrichment = Enrichment::new();
        $enrichment->setKeyName(Application_Orcid_ValidateAllIdentifierOrcid::ERROR_ENRICHMENT);
        $enrichment->setValue(Application_Orcid_ValidateAllIdentifierOrcid::ORCID_ERROR_CODE);
        $doc->addEnrichment($enrichment);
        $docId = $doc->store();

        $validate = new Application_Orcid_ValidateAllIdentifierOrcid();
        $validate->addTag(Document::get($docId));

        $doc         = Document::get($docId);
        $enrichments = $doc->getEnrichment();
        $this->assertCount(1, $enrichments);
        $this->assertEquals($validate::ORCID_ERROR_CODE, $enrichments[0]->getValue());
    }

    public function testRemoveTag()
    {
        $doc = $this->createTestDocument();

        $enrichment = Enrichment::new();
        $enrichment->setKeyName(Application_Orcid_ValidateAllIdentifierOrcid::ERROR_ENRICHMENT);
        $enrichment->setValue(Application_Orcid_ValidateAllIdentifierOrcid::ORCID_ERROR_CODE);
        $doc->addEnrichment($enrichment);
        $enrichment = Enrichment::new();
        $enrichment->setKeyName(Application_Orcid_ValidateAllIdentifierOrcid::ERROR_ENRICHMENT);
        $enrichment->setValue('DOC_ERROR_FULLTEXT');
        $doc->addEnrichment($enrichment);
        $docId = $doc->store();

        $validate = new Application_Orcid_ValidateAllIdentifierOrcid();
        $validate->removeTag(Document::get($docId));

        $doc         = Document::get($docId);
        $enrichments = $doc->getEnrichment();
        $this->assertCount(1, $enrichments);
        $this->assertEquals('DOC_ERROR_FULLTEXT', $enrichments[0]->getValue());
    }

    /**
     * This test just verifies behaviour for adding two identical enrichments to document.
     *
     * TODO move to tests in Framework
     */
    public function testDuplicateEnrichments()
    {
        $doc = $this->createTestDocument();

        $enrichment = Enrichment::new();
        $enrichment->setKeyName(Application_Orcid_ValidateAllIdentifierOrcid::ERROR_ENRICHMENT);
        $enrichment->setValue(Application_Orcid_ValidateAllIdentifierOrcid::ORCID_ERROR_CODE);
        $doc->addEnrichment($enrichment);
        $enrichment = Enrichment::new();
        $enrichment->setKeyName(Application_Orcid_ValidateAllIdentifierOrcid::ERROR_ENRICHMENT);
        $enrichment->setValue(Application_Orcid_ValidateAllIdentifierOrcid::ORCID_ERROR_CODE);
        $doc->addEnrichment($enrichment);
        $docId = $doc->store();

        $doc         = Document::get($docId);
        $enrichments = $doc->getEnrichment();
        $this->assertCount(2, $enrichments);
        $this->assertEquals(Application_Orcid_ValidateAllIdentifierOrcid::ERROR_ENRICHMENT, $enrichments[0]->getKeyName());
        $this->assertEquals(Application_Orcid_ValidateAllIdentifierOrcid::ERROR_ENRICHMENT, $enrichments[1]->getKeyName());
    }

    public function testAddTagDoesNotUpdateServerDateModified()
    {
        $doc = Document::get($this->docId1);

        $lastModified = $doc->getServerDateModified()->getUnixTimestamp();

        sleep(2);

        $validate = new Application_Orcid_ValidateAllIdentifierOrcid();
        $validate->addTag($doc);

        $doc = Document::get($this->docId1);

        $this->assertEquals($lastModified, $doc->getServerDateModified()->getUnixTimestamp());
    }

    public function testRemoveTagDoesNotUpdateServerDateModified()
    {
        $doc = Document::get($this->docId2);

        $lastModified = $doc->getServerDateModified()->getUnixTimestamp();

        sleep(2);

        $validate = new Application_Orcid_ValidateAllIdentifierOrcid();
        $validate->removeTag($doc);

        $doc = Document::get($this->docId2);

        $this->assertEquals($lastModified, $doc->getServerDateModified()->getUnixTimestamp());

        $this->expectException(ModelException::class);
        $this->expectExceptionMessage('unknown enrichment key');
        $this->assertNull($doc->getEnrichmentValue(Application_Orcid_ValidateAllIdentifierOrcid::ERROR_ENRICHMENT));
    }

    /**
     * TODO move somewhere more appropriate
     */
    public function testIndexingWithoutLifecycleListener()
    {
        $doc        = $this->createTestDocument();
        $doc        = Document::get($doc->store());
        $enrichment = Enrichment::new();
        $enrichment->setKeyName(Application_Orcid_ValidateAllIdentifierOrcid::ERROR_ENRICHMENT);
        $enrichment->setValue(Application_Orcid_ValidateAllIdentifierOrcid::ORCID_ERROR_CODE);
        $doc->addEnrichment($enrichment);
        $doc->setLifecycleListener(null);
        $doc->store();
        $this->markTestIncomplete('Verify indexing');
    }
}
