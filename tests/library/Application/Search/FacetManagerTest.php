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
 * @copyright   Copyright (c) 2020, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

class Application_Search_FacetManagerTest extends ControllerTestCase
{
    /** @var string[] */
    protected $additionalResources = ['translation'];

    public function testGetFacetConfig()
    {
        $manager = new Application_Search_FacetManager();
        $config  = $manager->getFacetConfig('doctype');

        $this->assertEquals('1', $config->translated);
    }

    public function testGetFacet()
    {
        $manager = new Application_Search_FacetManager();

        $facet = $manager->getFacet('subject');

        $this->assertEquals('subject', $facet->getName());
    }

    public function testGetFacetUnknown()
    {
        $manager = new Application_Search_FacetManager();

        $facet = $manager->getFacet('unknown');

        $this->assertNull($facet);
    }

    public function testGetFacetLanguage()
    {
        $this->useGerman();

        $manager = new Application_Search_FacetManager();

        $facet = $manager->getFacet('language');

        $this->assertInstanceOf('Application_Search_Facet_Language', $facet);
        $this->assertFalse($facet->isTranslated());
        $this->assertEquals('Deutsch', $facet->getLabel('deu'));
    }

    public function testGetFacetDocType()
    {
        $manager = new Application_Search_FacetManager();

        $facet = $manager->getFacet('doctype');

        $this->assertNotNull($facet);
        $this->assertInstanceOf('Application_Search_Facet', $facet);
        $this->assertTrue($facet->isTranslated());
    }

    public function testGetActiveFacets()
    {
        $manager = new Application_Search_FacetManager();

        $facets = $manager->getActiveFacets();

        $this->assertCount(10, $facets);
        $this->assertContains('server_state', $facets); // TODO does this make sense?
    }

    public function testGetFacetEnrichment()
    {
        $this->adjustConfiguration([
            'searchengine' => ['solr' => ['facets' => 'enrichment_Audience']],
        ]);

        $manager = new Application_Search_FacetManager();

        $facet = $manager->getFacet('enrichment_Audience');

        $this->assertNotNull($facet);
        $this->assertInstanceOf('Application_Search_Facet', $facet);
        $this->assertFalse($facet->isTranslated());
    }

    public function testGetFacetEnrichmentTranslated()
    {
        $this->adjustConfiguration([
            'searchengine' => ['solr' => ['facets' => 'enrichment_Audience']],
        ]);

        $manager = new Application_Search_FacetManager();

        $facet = $manager->getFacet('enrichment_Audience');

        $this->assertNotNull($facet);
        $this->assertInstanceOf('Application_Search_Facet', $facet);
        $this->assertFalse($facet->isTranslated());
    }

    public function testGetFacetEnrichmentForAdmin()
    {
        $this->markTestIncomplete();
    }

    public function testGetFacetEnrichmentForDocumentsAdmin()
    {
        $this->markTestIncomplete();
    }

    public function testGetFacetEnrichmentBoolean()
    {
        $this->markTestIncomplete();
    }

    public function testGetFacetConfigForFacetteWithDotInName()
    {
        $this->adjustConfiguration([
            'search' => ['facet' => ['enrichment_opus-source' => ['heading' => 'EnrichmentOpusSource']]],
        ]);

        $manager = new Application_Search_FacetManager();

        $config = $manager->getFacetConfig('enrichment_opus.source');

        $this->assertEquals('EnrichmentOpusSource', $config->heading);
    }

    public function testFacetLimit()
    {
        $manager = new Application_Search_FacetManager();

        $facet = $manager->getFacet('year');

        $this->assertEquals(10, $facet->getLimit());
    }

    public function testFacetSortCrit()
    {
        $this->adjustConfiguration([
            'search' => ['facet' => ['subject' => ['sort' => 'lexi']]],
        ]);

        $manager = new Application_Search_FacetManager();

        $facet = $manager->getFacet('subject');

        $this->assertEquals('lexi', $facet->getSort());
    }
}
