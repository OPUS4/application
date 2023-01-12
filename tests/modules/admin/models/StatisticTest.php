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

class Admin_Model_StatisticsTest extends ControllerTestCase
{
    /** @var string[] */
    protected $additionalResources = ['database'];

    /**
     * Tests, if publication count of institute statistics is correct.
     */
    public function testInstituteStatistics()
    {
        $statistics = new Admin_Model_Statistics();
        $institutes = $statistics->getInstituteStatistics(2010);
        $this->assertEquals(
            29,
            $institutes['Technische Universität Hamburg-Harburg'],
            'wrong publication count of Technische Universität Hamburg-Harburg returned'
        );
        $this->assertEquals(1, $institutes['Bauwesen'], 'wrong publicatin count of "Bauwesen" returned');
        $this->assertEquals(1, $institutes['Maschinenbau'], 'wrong publication count of "Maschinenbau" returned');
        $this->assertNotContains('Massivbau B-7', $institutes, 'Institutes should not contain data with 0 documents');

        $institutes = $statistics->getInstituteStatistics(2009);
        $this->assertEquals(
            50,
            $institutes['Technische Universität Hamburg-Harburg'],
            'wrong publication count of Technische Universität Hamburg-Harburg returned'
        );
        $this->assertNotContains('Bauwesen', $institutes, 'Institutes should not contain data with 0 documents');
        $this->assertEquals(3, $institutes['Maschinenbau'], 'wrong publication count of "Maschinenbau" returned');
        $this->assertNotContains('Massivbau B-7', $institutes, 'Institutes should not contain data with 0 documents');
    }

    /**
     * Tests, if publication count of month statistics is correct.
     */
    public function testMonthStatistics()
    {
        $statistics = new Admin_Model_Statistics();
        $months     = $statistics->getMonthStatistics(2010);
        $this->assertEquals(16, $months[1], 'wrong publication count of month Jan returned');
        $this->assertEquals(6, $months[2], 'wrong publication count of month Feb returned');
        $this->assertEquals(25, $months[3], 'wrong publication count of month March returned');
        $this->assertEquals(0, $months[5], 'wrong publication count of month May returned');

        $months = $statistics->getMonthStatistics(2009);
        $this->assertEquals(0, $months[1], 'wrong publication count of month Jan returned');
        $this->assertEquals(12, $months[8], 'wrong publication count of month Feb returned');
        $this->assertEquals(9, $months[9], 'wrong publication count of month March returned');
        $this->assertEquals(15, $months[12], 'wrong publication count of month May returned');
    }

    /**
     * Tests, if publication count of type statistics is correct.
     */
    public function testTypeStatistics()
    {
        $statistics = new Admin_Model_Statistics();
        $types      = $statistics->getTypeStatistics(2010);
        $this->assertEquals(15, $types['article'], 'wrong publication count of Article returned');
        $this->assertNotContains('masterthesis', $types, 'Document types should not contain data with 0 documents');
        $this->assertEquals(2, $types['conferenceobject'], 'wrong publication count of conferenceobject returned');

        $types = $statistics->getTypeStatistics(2009);
        $this->assertEquals(4, $types['article'], 'wrong publication count of Article returned');
        $this->assertEquals(1, $types['masterthesis'], 'wrong publication count of masterthesis returned');
        $this->assertNotContains('conferenceobject', $types, 'Document types should not contain data with 0 documents');
    }

    /**
     * Tests, if the right number of documents has been published until 2010.
     */
    public function testGetNumDocsUntil()
    {
        $statistics = new Admin_Model_Statistics();
        $this->assertEquals(0, $statistics->getNumDocsUntil(1900), 'wrong publication count of documents until 1900');
        $this->assertEquals(10, $statistics->getNumDocsUntil(2008), 'wrong publication count of documents until 2008');
        $this->assertEquals(107, $statistics->getNumDocsUntil(2010), 'wrong publication count of documents until 2010');
        $this->assertEquals(142, $statistics->getNumDocsUntil(2013), 'wrong publication count of documents until 2013');
    }

    /**
     * tests getYears() of Statistic Model
     */
    public function testGetYears()
    {
        $statistics = new Admin_Model_Statistics();
        $years      = $statistics->getYears();
        $this->assertContains('2002', $years);
        $this->assertContains('2003', $years);
        $this->assertContains('2004', $years);
        $this->assertContains('2009', $years);
        $this->assertContains('2010', $years);
        $this->assertContains('2012', $years);
        $this->assertContains('2013', $years);
    }
}
