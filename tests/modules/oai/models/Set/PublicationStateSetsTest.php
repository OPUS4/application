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

use Opus\Common\Document;
use Opus\Common\PublicationState;
use Opus\Common\Repository;

class Oai_Model_Set_PublicationStateSetsTest extends ControllerTestCase
{
    /** @var string[] */
    protected $additionalResources = ['database'];

    public function testGetSets()
    {
        $model = new Oai_Model_Set_PublicationStateSets();

        $sets = $model->getSets();

        $this->assertIsArray($sets);
        $this->assertCount(3, $sets);
        $this->assertEquals([
            'status-type:draft'          => 'draft',
            'status-type:'               => null,
            'status-type:updatedVersion' => 'updatedVersion',
        ], $sets);
    }

    public function testGetSetsForDocument()
    {
        $model = new Oai_Model_Set_PublicationStateSets();

        $doc = $this->createTestDocument();
        $doc->setServerState(Document::STATE_PUBLISHED);
        $doc->setPublicationState(PublicationState::ACCEPTED);
        $doc->store();

        $sets = $model->getSets($doc);

        $this->assertIsArray($sets);
        $this->assertCount(1, $sets);
        $this->assertEquals([
            'status-type:acceptedVersion' => 'acceptedVersion',
        ], $sets);
    }

    public function testGetSetsForUnpublishedDocument()
    {
        $model = new Oai_Model_Set_PublicationStateSets();

        $doc = $this->createTestDocument();
        $doc->setServerState(Document::STATE_UNPUBLISHED);
        $doc->setPublicationState(PublicationState::ACCEPTED);
        $doc->store();

        // TODO should ServerState be checked and no set be returned?
        $sets = $model->getSets($doc);

        $this->assertIsArray($sets);
        $this->assertCount(1, $sets);
        $this->assertEquals([
            'status-type:acceptedVersion' => 'acceptedVersion',
        ], $sets);
    }

    public function testGetSetsNoSetForProof()
    {
        $model = new Oai_Model_Set_PublicationStateSets();

        $doc = $this->createTestDocument();
        $doc->setServerState(Document::STATE_PUBLISHED);
        $doc->setPublicationState(PublicationState::PROOF);
        $doc->store();

        $sets = $model->getSets($doc);

        $this->assertIsArray($sets);
        $this->assertCount(0, $sets);
    }

    /**
     * @return array[]
     */
    public function setSpecDataProvider()
    {
        return [
            ['status-type:draft', true],
            ['status-type:acceptedVersion', true],
            ['status-type', false],
            ['status-type:proof', false],
        ];
    }

    /**
     * @param string $setSpec
     * @param bool   $expected
     * @dataProvider setSpecDataProvider
     */
    public function testSupports($setSpec, $expected)
    {
        $model = new Oai_Model_Set_PublicationStateSets();

        $this->assertEquals($expected, $model->supports(new Oai_Model_Set_SetName($setSpec)));
    }

    public function testConfigureFinder()
    {
        $model = new Oai_Model_Set_PublicationStateSets();

        $finder = Repository::getInstance()->getDocumentFinder();

        $setName = new Oai_Model_Set_SetName('status-type:draft');
        $model->configureFinder($finder, $setName);
        $this->assertGreaterThan(0, $finder->getIds());

        $setName = new Oai_Model_Set_SetName('status-type:submittedVersion');
        $model->configureFinder($finder, $setName);
        $this->assertCount(0, $finder->getIds());
    }

    public function testConfigureFinderUnsupportedSet()
    {
        $model = new Oai_Model_Set_PublicationStateSets();

        $finder  = Repository::getInstance()->getDocumentFinder();
        $setName = new Oai_Model_Set_SetName('pub-status:draft');

        $this->expectException(Oai_Model_Exception::class);
        $model->configureFinder($finder, $setName);
    }

    public function testConfigureFinderUnsupportedSubset()
    {
        $model = new Oai_Model_Set_PublicationStateSets();

        $finder  = Repository::getInstance()->getDocumentFinder();
        $setName = new Oai_Model_Set_SetName('status-type:proof');

        $this->expectException(Oai_Model_Exception::class);
        $model->configureFinder($finder, $setName);
    }

    public function testGetSubsetName()
    {
        $model = new Oai_Model_Set_PublicationStateSets();

        $this->assertEquals('submittedVersion', $model->getSubsetName(PublicationState::SUBMITTED));
        $this->assertEquals('updatedVersion', $model->getSubsetName(PublicationState::CORRECTED));
        $this->assertEquals('updatedVersion', $model->getSubsetName(PublicationState::ENHANCED));
        $this->assertNull($model->getSubsetName(PublicationState::PROOF));
        $this->assertNull($model->getSubsetName(PublicationState::AUTHORS_VERSION));
    }

    public function testGetPublicationStates()
    {
        $model = new Oai_Model_Set_PublicationStateSets();

        $states = $model->getPublicationStates('updatedVersion');

        $this->assertIsArray($states);
        $this->assertCount(2, $states);
        $this->assertEqualsCanonicalizing([
            PublicationState::ENHANCED,
            PublicationState::CORRECTED,
        ], $states);
    }
}
