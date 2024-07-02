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

class Oai_Model_Set_CollectionSetsTest extends ControllerTestCase
{
    /** @var string[] */
    protected $additionalResources = ['database'];

    public function testSupports()
    {
        $collectionSets = new Oai_Model_Set_CollectionSets();

        $setName = new Oai_Model_Set_SetName('ddc');

        $this->assertTrue($collectionSets->supports($setName));
    }

    public function testSupportsWithSubset()
    {
        $collectionSets = new Oai_Model_Set_CollectionSets();

        $setName = new Oai_Model_Set_SetName('ddc:02');

        $this->assertTrue($collectionSets->supports($setName));
    }

    public function testDoesNotSupport()
    {
        $openAccessRole = CollectionRole::fetchByOaiName('open_access');
        $rootCollection = $openAccessRole->getRootCollection();
        $document       = $this->createTestDocument();
        $document->setServerState('published');
        $document->addCollection($rootCollection);
        $document->store();

        $collectionSets = new Oai_Model_Set_CollectionSets();
        $collectionSets->setExcludedSets('open_access, ddc');

        $setName = new Oai_Model_Set_SetName('unknownCollectionRole:02');
        $this->assertFalse($collectionSets->supports($setName));

        $setName = new Oai_Model_Set_SetName('ddc:unknownCollection');
        $this->assertFalse($collectionSets->supports($setName));

        $setName = new Oai_Model_Set_SetName('open_access');
        $this->assertFalse($collectionSets->supports($setName));

        $setName = new Oai_Model_Set_SetName('ddc');
        $this->assertFalse($collectionSets->supports($setName));

        $setName = new Oai_Model_Set_SetName('ddc:02');
        $this->assertFalse($collectionSets->supports($setName));
    }

    public function testGetSets()
    {
        $this->markTestIncomplete(
            'The number of sets depends on the filling of the DB, which results from the existing '
            . 'tests and may also depend on the test sequence.'
        );

        $collectionSets = new Oai_Model_Set_CollectionSets();

        $sets = $collectionSets->getSets();
        $this->assertEquals(46, count($sets));

        $setPattern = '(bk|ccs|ddc|frontdoor-test-1|frontdoor-test-2|jel|msc|openaire|pacs|publists)';
        $this->assertEquals(46, count(preg_grep("/^$setPattern:?.*$/i", array_keys($sets))));
    }

    public function testGetSetsExcludeOpenAccess()
    {
        $openAccessRole = CollectionRole::fetchByOaiName('open_access');
        $rootCollection = $openAccessRole->getRootCollection();
        $document       = $this->createTestDocument();
        $document->setServerState('published');
        $document->addCollection($rootCollection);
        $document->store();

        $collectionSets = new Oai_Model_Set_CollectionSets();
        $collectionSets->setExcludedSets('open_access');
        $sets = $collectionSets->getSets();

        foreach ($sets as $setSpec => $set) {
            $setName = new Oai_Model_Set_SetName($setSpec);
            $this->assertNotEquals('open_access', $setName->getSetName());
        }
    }

    public function testGetSetsWithDocumentOpenAccess()
    {
        $openAccessRole = CollectionRole::fetchByOaiName('open_access');
        $rootCollection = $openAccessRole->getRootCollection();
        $document       = $this->createTestDocument();
        $document->setServerState('published');
        $document->addCollection($rootCollection);
        $document->store();

        $collectionSets = new Oai_Model_Set_CollectionSets();
        $collectionSets->setExcludedSets('open_access');
        $sets = $collectionSets->getSets($document);

        foreach ($sets as $setSpec => $set) {
            $setName = new Oai_Model_Set_SetName($setSpec);
            $this->assertNotEquals('open_access', $setName->getSetName());
        }
    }

    public function testGetSetsWithDocumentOpenAccessSubCollection()
    {
        $openAccessRole = CollectionRole::fetchByOaiName('open_access');
        $rootCollection = $openAccessRole->getRootCollection();

        $subCollection = Collection::new();
        $subCollection->setVisible(1);
        $subCollection->setOaiSubset('OaiSubsetCollection');
        $rootCollection->addLastChild($subCollection);

        $document = $this->createTestDocument();
        $document->setServerState('published');
        $document->addCollection($subCollection);
        $document->store();

        $collectionSets = new Oai_Model_Set_CollectionSets();
        $collectionSets->setExcludedSets('open_access');
        $sets = $collectionSets->getSets($document);

        foreach ($sets as $setSpec => $set) {
            $setName = new Oai_Model_Set_SetName($setSpec);
            $this->assertNotEquals('open_access', $setName->getSetName());
        }
    }

    public function testGetSetsWithDocument()
    {
        $this->markTestIncomplete('do more testing');
    }

    public function testConfigureFinder()
    {
        $this->markTestIncomplete('Actual search results should be checked.');
    }

    public function testConfigureFinderExcludeOpenAccess()
    {
        $openAccessRole = CollectionRole::fetchByOaiName('open_access');
        $rootCollection = $openAccessRole->getRootCollection();
        $document       = $this->createTestDocument();
        $document->setServerState('published');
        $document->addCollection($rootCollection);
        $document->store();

        $this->expectExceptionMessage('The given set results in an empty list: open_access');
        $this->expectException(Oai_Model_Exception::class);

        $config      = $this->getConfig();
        $finderClass = $config->documentFinderClass;
        $finder      = new $finderClass();

        $collectionSets = new Oai_Model_Set_CollectionSets();
        $collectionSets->setExcludedSets('open_access');
        $setName = new Oai_Model_Set_SetName('open_access');
        $collectionSets->configureFinder($finder, $setName);
    }
}
