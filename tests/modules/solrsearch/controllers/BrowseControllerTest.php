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
 * @category    Application
 * @package     Module_Solrsearch
 * @author      Sascha Szott <szott@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class Solrsearch_BrowseControllerTest extends ControllerTestCase {

    public function setUp() {
        parent::setUp();
        $this->requireSolrConfig();
    }

    public function testIndexAction() {
        $this->dispatch('/solrsearch/browse');
        $this->assertResponseCode(200);
    }

    public function testDoctypesAction() {
        $this->dispatch('/solrsearch/browse/doctypes');
        $this->assertResponseCode(200);
    }

    public function testSeriesAction() {
        $this->dispatch('/solrsearch/browse/series');
        $responseBody = $this->getResponse()->getBody();
        $this->assertContains('/solrsearch/index/search/searchtype/series/id/1', $responseBody);
        $this->assertContains('/solrsearch/index/search/searchtype/series/id/2', $responseBody);
        $this->assertContains('/solrsearch/index/search/searchtype/series/id/5', $responseBody);
        $this->assertContains('/solrsearch/index/search/searchtype/series/id/6', $responseBody);
        $this->assertNotContains('/solrsearch/index/search/searchtype/series/id/3', $responseBody);
        $this->assertNotContains('/solrsearch/index/search/searchtype/series/id/4', $responseBody);
        $this->assertResponseCode(200);
    }

    public function testSeriesActionWithUnvisibleSeries() {
        $visibilities = $this->setAllSeriesToUnvisible();

        $this->dispatch('/solrsearch/browse/series');
        $this->assertRedirect();
        $this->assertResponseLocationHeader($this->getResponse(), '/solrsearch/browse');

        $this->restoreSeriesVisibility($visibilities);
    }

    public function testSeriesActionWithOneVisibleSeriesWithoutAnyPublishedDocument() {
        $visibilities = $this->setAllSeriesToUnvisible();

        $d = new Opus_Document();
        $d->setServerState('unpublished');
        $d->store();

        $s = new Opus_Series(3);
        $d->addSeries($s)->setNumber('testSeriesAction-3');
        $s = new Opus_Series(4);
        $s->setVisible('1');
        $s->store();
        $d->addSeries($s)->setNumber('testSeriesAction-4');
        $d->store();

        $this->dispatch('/solrsearch/browse/series');
        $this->assertRedirect();
        $this->assertResponseLocationHeader($this->getResponse(), '/solrsearch/browse');

        $this->restoreSeriesVisibility($visibilities);
        $d->deletePermanent();        
    }

    public function testSeriesActionWithOneVisibleSeriesWithOnePublishedDocument() {
        $visibilities = $this->setAllSeriesToUnvisible();

        $d = new Opus_Document();
        $d->setServerState('published');
        $d->store();

        $s = new Opus_Series(3);
        $d->addSeries($s)->setNumber('testSeriesAction-3');
        $s = new Opus_Series(4);
        $s->setVisible('1');
        $s->store();
        $d->addSeries($s)->setNumber('testSeriesAction-4');
        $d->store();

        $this->dispatch('/solrsearch/browse/series');
        $this->assertNotContains('/solrsearch/index/search/searchtype/series/id/3', $this->getResponse()->getBody());
        $this->assertContains('/solrsearch/index/search/searchtype/series/id/4', $this->getResponse()->getBody());        
        $this->assertResponseCode(200);

        $this->restoreSeriesVisibility($visibilities);
        $d->deletePermanent();
    }

    private function setAllSeriesToUnvisible() {
        $visibilities = array();
        foreach (Opus_Series::getAll() as $seriesItem) {
            $visibilities[$seriesItem->getId()] = $seriesItem->getVisible();
            $seriesItem->setVisible(0);
            $seriesItem->store();
        }
        return $visibilities;
    }

    private function restoreSeriesVisibility($visibilities) {
        foreach (Opus_Series::getAll() as $seriesItem) {
            $seriesItem->setVisible($visibilities[$seriesItem->getId()]);
            $seriesItem->store();
        }
    }
}
