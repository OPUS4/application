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

use Opus\Common\Date;

/**
 * Unit Test for class Admin_Model_Workflow.
 */
class Application_Controller_Action_Helper_DatesTest extends ControllerTestCase
{
    /** @var string */
    protected $additionalResources = 'translation';

    /** @var Application_Controller_Action_Helper_Dates */
    private $datesHelper;

    public function setUp(): void
    {
        parent::setUp();

        $this->datesHelper = new Application_Controller_Action_Helper_Dates();
    }

    public function testIsValidGermanTrue()
    {
        $this->useGerman();
        $this->assertTrue($this->datesHelper->isValid('20.3.2005'));
    }

    public function testIsValidGermanFalse()
    {
        $this->useGerman();
        $this->assertFalse($this->datesHelper->isValid('2005'));
    }

    public function testIsValidEnglishTrue()
    {
        $this->useEnglish();
        $this->assertTrue($this->datesHelper->isValid('2005/03/20'));
    }

    public function testIsValidEnglishFalse()
    {
        $this->useEnglish();
        $this->assertFalse($this->datesHelper->isValid('2005'));
    }

    public function testGetOpusDateGerman()
    {
        $this->useGerman();
        $date = $this->datesHelper->getOpusDate('25.3.2005');
        $this->assertNotNull($date);
        $this->assertEquals('25.03.2005', $this->datesHelper->getDateString($date));
    }

    public function testGetOpusDateEnglish()
    {
        $this->useEnglish();
        $date = $this->datesHelper->getOpusDate('2005/03/25');
        $this->assertNotNull($date);
        // Check read back in German (just for fun)
        $this->useGerman();
        $this->assertEquals('25.03.2005', $this->datesHelper->getDateString($date));
    }

    public function testGetOpusDateInvalidGerman()
    {
        $this->useGerman();
        $date = $this->datesHelper->getOpusDate('2005');
        $this->assertNull($date);
    }

    public function testGetOpusDateInvalidEnglish()
    {
        $this->useEnglish();
        $date = $this->datesHelper->getOpusDate('2005');
        $this->assertNull($date);
    }

    public function testGetOpusDateIsDateOnly()
    {
        $this->useEnglish();
        $date = $this->datesHelper->getOpusDate('2010/01/14');
        $this->assertTrue($date->isDateOnly());
        $this->assertEquals('2010-01-14', $date->__toString());
    }

    public function testGetDateStringGerman()
    {
        $this->useGerman();
        $date = new Date('2005-03-25');
        $this->assertEquals('25.03.2005', $this->datesHelper->getDateString($date));
    }

    public function testGetDateStringEnglish()
    {
        $this->useEnglish();
        $date = new Date('2005-03-25');
        $this->assertEquals('2005/03/25', $this->datesHelper->getDateString($date));
    }

    public function testGetDateStringForInvalidDate()
    {
        $this->useGerman();
        $date = new Date('2005');
        $this->assertFalse($date->isValid());
        $this->assertEquals(null, $this->datesHelper->getDateString($date));
    }

    /**
     * @return string[][]
     */
    public function dateValuesProvider()
    {
        return [
            ['1998-07-22'],
            ['2010-2-5T0:00:00CET'],
            ['2003-6-2T0:00:00CEST'],
            ['2002-8-10T0:00:00CEST'],
            ['1999-01-12T00:00:00CET'],
            ['2000-07-05T00:00:00CEST'],
            ['2016-02-01T00:00:00+01:00'],
            ['2017-07-27'],
            ['2016-05-04T00:00:00CEST'],
            ['2014-08-27T14:45:19+02:00'],
        ];
    }

    /**
     * @dataProvider dateValuesProvider
     * @param string $datestr
     */
    public function testDatesAreNotChangedByTimestampFormat($datestr)
    {
        $this->useEnglish();

        $date = new Date($datestr);

        $dateParts = mb_split('-', (mb_split('T', $datestr))[0]);
        $dateOnly  = sprintf('%04d/%02d/%02d', $dateParts[0], $dateParts[1], $dateParts[2]);

        $output = $this->datesHelper->getDateString($date);

        $this->assertEquals($dateOnly, $output);
    }
}
