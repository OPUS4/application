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
 * @copyright   Copyright (c) 2017, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

class Export_BibtexExportTest extends ControllerTestCase
{
    /** @var bool */
    protected $configModifiable = true;

    /** @var string */
    protected $additionalResources = 'all';

    public function setUp(): void
    {
        parent::setUp();

        $this->adjustConfiguration([
            'searchengine' => ['solr' => ['numberOfDefaultSearchResults' => '10']],
        ]);
    }

    /**
     * @misc{Doe2009,
     * type      = {Master Thesis},
     * author    = {Doe, John},
     * title     = {KOBV},
     * series = {Parent Title},
     * volume    = {2},
     * journal   = {Parent Title},
     * number    = {3},
     * editor    = {Doe, Jane},
     * edition   = {1},
     * publisher = {Foo Publishing},
     * address   = {Timbuktu},
     * organization    = {Bar University},
     * isbn      = {123},
     * issn      = {123},
     * doi       = {123},
     * url       = {http://nbn-resolving.de/urn:nbn:op:123},
     * number    = {123},
     * school      = {Foobar Universit{\"a}tsbibliothek},
     * pages     = {1 -- 4},
     * year      = {2009},
     * abstract  = {Die KOBV-Zentrale in Berlin-Dahlem.},
     * subject      = {Berlin},
     * language  = {de}
     * }
     */
    public function testExportSingleDocument()
    {
        $this->adjustConfiguration([
            'export' => ['download' => self::CONFIG_VALUE_FALSE],
        ]);

        $this->dispatch('/export/index/bibtex/searchtype/id/docId/146');

        $this->assertResponseCode(200);

        $body = $this->getResponse()->getBody();

        $this->assertContains('@misc{Doe2009,', $body);
        $this->assertContains('author    = {Doe, John},', $body);
    }

    /**
     * @throws Zend_Exception
     */
    public function testExportLatestDocuments()
    {
        $this->adjustConfiguration([
            'export'       => ['download' => self::CONFIG_VALUE_FALSE],
            'searchengine' => ['solr' => ['numberOfDefaultSearchResults' => '10']],
        ]);

        $this->dispatch('/export/index/bibtex/searchtype/latest');

        $this->assertResponseCode(200);

        $body = $this->getResponse()->getBody();

        $this->assertEquals(10, substr_count($body, '@'));
    }

    public function testExportLatestDocumentsWithCustomRows()
    {
        $this->adjustConfiguration([
            'export' => ['download' => self::CONFIG_VALUE_FALSE],
        ]);

        $this->dispatch('/export/index/bibtex/searchtype/latest/rows/12');

        $this->assertResponseCode(200);

        $body = $this->getResponse()->getBody();

        $this->assertEquals(12, substr_count($body, '@'));
    }
}
