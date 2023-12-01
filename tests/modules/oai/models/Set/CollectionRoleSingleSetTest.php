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
 * @copyright   Copyright (c) 2023, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\Collection;
use Opus\Common\CollectionRole;
use Opus\Common\Document;

class Oai_Model_Set_CollectionRoleSingleSetTest extends ControllerTestCase
{
    /** @var string[] */
    protected $additionalResources = ['database'];

    /** @var bool */
    private $openAccessRoleVisibleOai;

    /** @var Collection[] */
    private $collections;

    public function setUp(): void
    {
        $this->collections = [];

        parent::setUp();
        $openAccessRole                 = CollectionRole::fetchByOaiName('open_access');
        $this->openAccessRoleVisibleOai = $openAccessRole->getVisibleOai();
    }

    public function tearDown(): void
    {
        foreach ($this->collections as $collection) {
            $collection->delete();
        }

        $openAccessRole = CollectionRole::fetchByOaiName('open_access');
        $openAccessRole->setVisibleOai($this->openAccessRoleVisibleOai);
        $openAccessRole->store();
        parent::tearDown();
    }

    public function testSupports()
    {
        $openAccessSet = new Oai_Model_Set_CollectionRoleSingleSet();
        $openAccessSet->setRoleOaiName('open_access');

        $setName = new Oai_Model_Set_SetName('open_access');
        $this->assertTrue($openAccessSet->supports($setName));

        $setName = new Oai_Model_Set_SetName('open_access:open_access');
        $this->assertTrue($openAccessSet->supports($setName));

        $setName = new Oai_Model_Set_SetName('open_access:closed_access');
        $this->assertTrue($openAccessSet->supports($setName));
    }

    public function testDoesNotSupport()
    {
        $openAccessSet = new Oai_Model_Set_CollectionRoleSingleSet();
        $openAccessSet->setRoleOaiName('open_access');

        $setName = new Oai_Model_Set_SetName('unknownCollectionRole:02');
        $this->assertFalse($openAccessSet->supports($setName));

        $setName = new Oai_Model_Set_SetName('ddc:unknownCollection');
        $this->assertFalse($openAccessSet->supports($setName));
    }

    public function testGetSets()
    {
        $openAccessSet = new Oai_Model_Set_CollectionRoleSingleSet();
        $openAccessSet->setRoleOaiName('open_access');

        $sets = $openAccessSet->getSets();
        $this->assertEquals(1, count($sets));

        $setSpec = array_keys($sets)[0];
        $setName = new Oai_Model_Set_SetName($setSpec);
        $this->assertEquals('open_access', $setName->getSetName());
        $collectionRole = CollectionRole::fetchByOaiName($setName->getSetName());
        $this->assertNotNull($collectionRole);
        $this->assertEquals(1, $collectionRole->getVisibleOai());
        $this->assertEquals(1, $collectionRole->getVisible());
    }

    public function testGetSetsWithDocumentRootCollection()
    {
        $openAccessRole = CollectionRole::fetchByOaiName('open_access');
        $rootCollection = $openAccessRole->getRootCollection();

        $document = $this->createTestDocument();
        $document->setServerState('published');
        $document->addCollection($rootCollection);
        $document->store();

        $openAccessSet = new Oai_Model_Set_CollectionRoleSingleSet();
        $openAccessSet->setRoleOaiName('open_access');

        $sets = $openAccessSet->getSets($document);

        $this->assertEquals(1, count($sets));
        $this->assertEquals(['open_access'], array_keys($sets));
    }

    public function testGetSetsWithDocumentOaiSubset()
    {
        $openAccessRole = CollectionRole::fetchByOaiName('open_access');
        $rootCollection = $openAccessRole->getRootCollection();

        $collection = Collection::new();
        $collection->setVisible(1);
        $collection->setOaiSubset('VisibleOaiSubset');
        $rootCollection->addLastChild($collection);

        $this->collections[] = $collection;

        $document = $this->createTestDocument();
        $document->setServerState('published');
        $document->addCollection($collection);
        $docId = $document->store();

        $document = Document::get($docId);

        $openAccessSet = new Oai_Model_Set_CollectionRoleSingleSet();
        $openAccessSet->setRoleOaiName('open_access');

        $sets = $openAccessSet->getSets($document);

        $this->assertEquals(1, count($sets));
        $this->assertEquals(['open_access'], array_keys($sets));
    }

    public function testGetSetsWithDocumentInvisibleOaiSubset()
    {
        $openAccessRole = CollectionRole::fetchByOaiName('open_access');
        $rootCollection = $openAccessRole->getRootCollection();

        $collection = Collection::new();
        $collection->setVisible(0);
        $collection->setOaiSubset('InvisibleOaiSubset');
        $rootCollection->addLastChild($collection);

        $this->collections[] = $collection;

        $document = $this->createTestDocument();
        $document->setServerState('published');
        $document->addCollection($collection);
        $document->store();

        $openAccessSet = new Oai_Model_Set_CollectionRoleSingleSet();
        $openAccessSet->setRoleOaiName('open_access');

        $sets = $openAccessSet->getSets($document);

        $this->assertEquals(0, count($sets));
    }

    public function testGetSetsWithDocumentSubCollection()
    {
        $openAccessRole = CollectionRole::fetchByOaiName('open_access');
        $rootCollection = $openAccessRole->getRootCollection();

        $subCollection       = Collection::new();
        $this->collections[] = $subCollection;
        $subCollection->setVisible(1);
        $subCollection->setOaiSubset('SubOaiSubset');

        $collection          = Collection::new();
        $this->collections[] = $collection;
        $collection->setVisible(1);
        $collection->setOaiSubset('OaiSubset');
        $collection->addLastChild($subCollection);
        $rootCollection->addLastChild($collection);

        $document = $this->createTestDocument();
        $document->setServerState('published');
        $document->addCollection($collection);
        $docId = $document->store();

        $document = Document::get($docId);

        $openAccessSet = new Oai_Model_Set_CollectionRoleSingleSet();
        $openAccessSet->setRoleOaiName('open_access');

        $sets = $openAccessSet->getSets($document);

        $this->assertEquals(1, count($sets));
        $this->assertEquals(['open_access'], array_keys($sets));
    }

    public function testGetSetsWithDocumentNotOpenAccess()
    {
        $openAccessRole = CollectionRole::fetchByOaiName('institutes');
        $rootCollection = $openAccessRole->getRootCollection();

        $document = $this->createTestDocument();
        $document->setServerState('published');
        $document->addCollection($rootCollection);
        $document->store();

        $openAccessSet = new Oai_Model_Set_CollectionRoleSingleSet();
        $openAccessSet->setRoleOaiName('open_access');

        $sets = $openAccessSet->getSets($document);

        $this->assertEmpty($sets);
    }

    public function testConfigureFinder()
    {
        $openAccessRole = CollectionRole::fetchByOaiName('open_access');
        $rootCollection = $openAccessRole->getRootCollection();

        $document = $this->createTestDocument();
        $document->addCollection($rootCollection);
        $document->store();

        $subCollection       = Collection::new();
        $this->collections[] = $subCollection;
        $subCollection->setVisible(1);
        $subCollection->setOaiSubset('SubOaiSubset');

        $collection          = Collection::new();
        $this->collections[] = $collection;
        $collection->setVisible(1);
        $collection->setOaiSubset('OaiSubset');
        $collection->addLastChild($subCollection);
        $rootCollection->addLastChild($collection);
        $collection->store();

        $document = $this->createTestDocument();
        $document->addCollection($subCollection);
        $document->store();

        $config      = $this->getConfig();
        $finderClass = $config->documentFinderClass;
        $finder      = new $finderClass();

        $openAccessSet = new Oai_Model_Set_CollectionRoleSingleSet();
        $openAccessSet->setRoleOaiName('open_access');

        $setName = new Oai_Model_Set_SetName('open_access');
        $openAccessSet->configureFinder($finder, $setName);

        $docIds = $finder->getIds();

        $this->assertEquals(2, count($docIds));

        foreach ($docIds as $docId) {
            $doc          = Document::get($docId);
            $isOpenAccess = false;
            foreach ($doc->getCollection() as $collection) {
                if ($collection->getRole()->getOaiName() === 'open_access') {
                    $isOpenAccess = true;
                    break;
                }
            }

            $this->assertTrue($isOpenAccess, 'Only open access documents expected: Document id=' . $docId);
        }
    }
}
