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
 * @author      Michael Lang <lang@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class Admin_Model_StatisticsTest extends ControllerTestCase {

    /*
     * tests, if publication count of institute statistics is correct
     */
    public function testInstituteStatistics() {
        $statistics = new Admin_Model_Statistics();
        $institutes = $statistics->getInstituteStatistics(2010);
        $this->assertTrue( $institutes['Technische Universität Hamburg-Harburg'] == 94, 'wrong publication count of Technische Universität Hamburg-Harburg returned' );
    }

    /*
     * tests, if publication count of month statistics is correct
     */
    public function testMonthStatistics() {
        $statistics = new Admin_Model_Statistics();
        $months = $statistics->getMonthStatistics(2010);
        $this->assertTrue( $months[1] == 16, 'wrong publication count of month Jan returned');
    }

    /*
     * tests, if publication count of type statistics is correct
     */
    public function testTypeStatistics() {
        $statistics = new Admin_Model_Statistics();
        $types = $statistics->getTypeStatistics(2010);
        $this->assertTrue( $types['article'] == 15, 'wrong publication count of Article returned' );
    }

    /*
     * tests, if the right number of documents has been published until 2010
     */
    public function testNumDocsUntil() {
        $statistics = new Admin_Model_Statistics();
        $numOfDocsUntil2010 = $statistics->getNumDocsUntil(2010);
        $this->assertTrue( $numOfDocsUntil2010 == 107, 'wrong publication count of documents from the first year to 2010');
    }
}
 