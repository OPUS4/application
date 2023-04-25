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
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\Series;

class Solrsearch_Model_SeriesUtilTest extends ControllerTestCase
{
    /** @var string[] */
    protected $additionalResources = ['database'];

    /** @var array */
    private $visibilities = [];

    /** @var Solrsearch_Model_SeriesUtil */
    private $model;

    public function setUp(): void
    {
        parent::setUp();

        foreach (Series::getAll() as $seriesItem) {
            $this->visibilities[$seriesItem->getId()] = $seriesItem->getVisible();
        }

        $this->model = new Solrsearch_Model_SeriesUtil();
    }

    public function tearDown(): void
    {
        $this->restoreVisiblitySettings();

        parent::tearDown();
    }

    public function testHasDisplayableSeries()
    {
        $this->assertTrue($this->model->hasDisplayableSeries());

        $this->setAllSeriesToUnvisible();
        $this->assertFalse($this->model->hasDisplayableSeries());

        $this->restoreVisiblitySettings();
        $this->assertTrue($this->model->hasDisplayableSeries());
    }

    public function testGetVisibleNonEmptySeriesSortedBySortKey()
    {
        $this->assertTrue(count($this->model->getVisibleNonEmptySeriesSortedBySortKey()) === 5);

        $this->setAllSeriesToUnvisible();
        $this->assertTrue(count($this->model->getVisibleNonEmptySeriesSortedBySortKey()) === 0);

        $this->restoreVisiblitySettings();
        $this->assertTrue(count($this->model->getVisibleNonEmptySeriesSortedBySortKey()) === 5);
    }

    public function testGetVisibleSeriesSortedBySortKey()
    {
        $series = $this->model->getVisibleSeries();

        $order = [1, 4, 2, 5, 6];

        $this->assertCount(5, $series);

        foreach ($order as $index => $seriesId) {
            $this->assertEquals($seriesId, $series[$index]['id']);
        }
    }

    public function testGetVisibleSeriesSortedAlphabetically()
    {
        $this->adjustConfiguration([
            'browsing' => [
                'series' => [
                    'sortByTitle' => self::CONFIG_VALUE_TRUE,
                ],
            ],
        ]);

        $series = $this->model->getVisibleSeries();

        $order = [2, 1, 6, 5, 4];

        $this->assertCount(5, $series);

        foreach ($order as $index => $seriesId) {
            $this->assertEquals($seriesId, $series[$index]['id']);
        }
    }

    private function setAllSeriesToUnvisible()
    {
        foreach (Series::getAll() as $seriesItem) {
            $seriesItem->setVisible(0);
            $seriesItem->store();
        }
    }

    private function restoreVisiblitySettings()
    {
        foreach (Series::getAll() as $seriesItem) {
            $seriesItem->setVisible($this->visibilities[$seriesItem->getId()]);
            $seriesItem->store();
        }
    }
}
