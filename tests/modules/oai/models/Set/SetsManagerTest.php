<?php

use Opus\Common\CollectionRole;

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

class Oai_Model_Set_SetsManagerTest extends ControllerTestCase
{
    /** @var string[] */
    protected $additionalResources = ['database'];

    public function testGetSets()
    {
        $this->markTestIncomplete(
            'The number of sets depends on the filling of the DB, which results from the existing '
            . 'tests and may also depend on the test sequence.'
        );

        $setsManager = new Oai_Model_Set_SetsManager();

        $sets = $setsManager->getSets();

        $this->assertEquals(69, count($sets));

        $setPattern = '(bibliography|doc-type|bk|ccs|ddc|frontdoor-test-1|frontdoor-test-2|jel|msc|openaire|pacs|publists)';
        $this->assertEquals(69, count(preg_grep("/^$setPattern:?.*$/i", array_keys($sets))));
    }

    public function testGetSetsWithDocument()
    {
        $setsManager = new Oai_Model_Set_SetsManager();

        $document = $this->createTestDocument();
        $document->setBelongsToBibliography(true);

        $sets = $setsManager->getSets($document);
        $this->assertEquals(['bibliography:true', 'doc-type:Other'], array_keys($sets));
    }

    public function testGetSetsWithDocumentOpenAcces()
    {
        $setsManager = new Oai_Model_Set_SetsManager();

        $openAccessRole = CollectionRole::fetchByOaiName('open_access');
        $rootCollection = $openAccessRole->getRootCollection();
        $document       = $this->createTestDocument();
        $document->setServerState('published');
        $document->addCollection($rootCollection);
        $document->store();

        $sets = $setsManager->getSets($document);
        $this->assertEquals(['bibliography:false', 'doc-type:Other', 'open_access'], array_keys($sets));
    }

    public function testGetSetType()
    {
        $setsManager = new Oai_Model_Set_SetsManager();

        $setName = new Oai_Model_Set_SetName('doc-type:article');

        $setType = $setsManager->getSetType($setName);

        $this->assertInstanceOf(Oai_Model_Set_DocumentTypeSets::class, $setType);
    }

    public function testGetSetTypeUnkownSet()
    {
        $setsManager = new Oai_Model_Set_SetsManager();

        $setName = new Oai_Model_Set_SetName('unknownSet:article');

        $setType = $setsManager->getSetType($setName);

        $this->assertNull($setType);
    }

    public function testGetSetTypeOpenAccess()
    {
        $setsManager = new Oai_Model_Set_SetsManager();

        $setName = new Oai_Model_Set_SetName('open_access');

        $setType = $setsManager->getSetType($setName);

        $this->assertInstanceOf(Oai_Model_Set_CollectionRoleSingleSet::class, $setType);
    }

    public function testGetSetTypeOpenAccessSubset()
    {
        $setsManager = new Oai_Model_Set_SetsManager();

        $setName = new Oai_Model_Set_SetName('open_access:open_access');

        $setType = $setsManager->getSetType($setName);

        $this->assertInstanceOf(Oai_Model_Set_CollectionRoleSingleSet::class, $setType);
    }

    public function testConfigureFinder()
    {
        $this->markTestIncomplete('Actual search results should be checked.');
    }

    public function testConfigureFinderExcludeOpenAccess()
    {
        $this->adjustConfiguration([
            'oai' => ['set' => ['collection' => ['exclude' => 'open_access,ddc']]],
        ]);

        $this->expectExceptionMessage('The given set results in an empty list: open_access');
        $this->expectException(Oai_Model_Exception::class);

        $openAccessRole = CollectionRole::fetchByOaiName('open_access');
        $rootCollection = $openAccessRole->getRootCollection();
        $document       = $this->createTestDocument();
        $document->setServerState('published');
        $document->addCollection($rootCollection);
        $document->store();

        $config      = $this->getConfig();
        $finderClass = $config->documentFinderClass;
        $finder      = new $finderClass();

        $setsManager = new Oai_Model_Set_SetsManager();
        $setName     = new Oai_Model_Set_SetName('open_access');
        $setTypes    = $setsManager->getSetTypeObjects();
        $setTypes['collection']->configureFinder($finder, $setName);
    }

    public function testConfigureFinderExcludeDdc()
    {
        $this->adjustConfiguration([
            'oai' => ['set' => ['collection' => ['exclude' => 'open_access,ddc']]],
        ]);

        $this->expectExceptionMessage('The given set results in an empty list: ddc');
        $this->expectException(Oai_Model_Exception::class);

        $openAccessRole = CollectionRole::fetchByOaiName('ddc');
        $rootCollection = $openAccessRole->getRootCollection();
        $document       = $this->createTestDocument();
        $document->setServerState('published');
        $document->addCollection($rootCollection);
        $document->store();

        $config      = $this->getConfig();
        $finderClass = $config->documentFinderClass;
        $finder      = new $finderClass();

        $setsManager = new Oai_Model_Set_SetsManager();
        $setName     = new Oai_Model_Set_SetName('ddc');
        $setTypes    = $setsManager->getSetTypeObjects();
        $setTypes['collection']->configureFinder($finder, $setName);
    }
}
