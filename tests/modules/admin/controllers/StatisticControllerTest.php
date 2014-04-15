<?php
/*
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
 * @category    TODO
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class Admin_StatisticControllerTest extends ControllerTestCase {

    public function testIndexAction() {
        $this->dispatch('/admin/statistic');
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('statistic');
        $this->assertAction('index');
    }

    public function testShowAction() {
        $this->request
                ->setMethod('POST')
                ->setPost(array('selectedYear' => '2010'));
        $this->dispatch('/admin/statistic/show');
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('statistic');
        $this->assertAction('show');
    }

    public function testShowActionWithWrongYear() {
        $this->request
            ->setMethod('POST')
            ->setPost(array('selectedYear' => '1337'));
        $this->dispatch('/admin/statistic/index');
        $this->assertAction('index');
    }

    public function testStatisticControllersWithEmptyCollections() {
        $this->request
            ->setMethod('POST')
            ->setPost(array('selectedYear' => '2010'));
        $statistics = new Admin_Model_Statistics();
        $this->assertCollectionNotEmpty($statistics->getInstituteStatistics(2010), 'There are no institutes set.');
        $this->assertCollectionNotEmpty($statistics->getMonthStatistics(2010), 'There is no month collection returned');
        $this->assertCollectionNotEmpty($statistics->getTypeStatistics(2010), 'There is no type collection returned');
    }

    private function assertCollectionNotEmpty($collection, $message = '') {

        $this->_incrementAssertionCount();
        if (!isset($collection)) {
            $msg = sprintf('Failed asserting collection is not null', $collection);
            if (!empty($message)) {
                $msg = $message . "\n" . $msg;
            }
            $this->fail($msg);
        }
    }
}

