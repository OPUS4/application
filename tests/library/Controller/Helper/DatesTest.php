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
 * @category    Application Unit Test
 * @package     Controller_Helper
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Unit Test for class Admin_Model_Workflow.
 */
class Controller_Helper_DatesTest extends ControllerTestCase {

    private $__datesHelper;

    public function setUp() {
        parent::setUp();

        $this->__datesHelper = new Controller_Helper_Dates();
    }

    private function __setUpGerman() {
        $session = new Zend_Session_Namespace();
        $session->language = 'de';
    }

    private function __setUpEnglish() {
        $session = new Zend_Session_Namespace();
        $session->language = 'en';
    }

    public function testIsValidGermanTrue() {
        $this->__setUpGerman();
        $this->assertTrue($this->__datesHelper->isValid('20.3.2005'));
    }

    public function testIsValidGermanFalse() {
        $this->__setUpGerman();
        $this->assertFalse($this->__datesHelper->isValid('2005'));
    }

    public function testIsValidEnglishTrue() {
        $this->__setUpEnglish();
        $this->assertTrue($this->__datesHelper->isValid('2005/03/20'));
    }

    public function testIsValidEnglishFalse() {
        $this->__setUpEnglish();
        $this->assertFalse($this->__datesHelper->isValid('2005'));
    }

    public function testGetOpusDateGerman() {
        $this->__setUpGerman();
        $date = $this->__datesHelper->getOpusDate('25.3.2005');
        $this->assertNotNull($date);
        $this->assertEquals('25.03.2005', $this->__datesHelper->getDateString($date));
    }

    public function testGetOpusDateEnglish() {
        $this->__setUpEnglish();
        $date = $this->__datesHelper->getOpusDate('2005/03/25');
        $this->assertNotNull($date);
        // Check read back in German (just for fun)
        $this->__setUpGerman();
        $this->assertEquals('25.03.2005', $this->__datesHelper->getDateString($date));
    }

    public function testGetOpusDateInvalidGerman() {
        $this->__setUpGerman();
        $date = $this->__datesHelper->getOpusDate('2005');
        $this->assertNull($date);
    }

    public function testGetOpusDateInvalidEnglish() {
        $this->__setUpEnglish();
        $date = $this->__datesHelper->getOpusDate('2005');
        $this->assertNull($date);

    }

    public function testGetDateStringGerman() {
        $this->__setUpGerman();
        $date = new Opus_Date('2005-03-25');
        $this->assertEquals('25.03.2005', $this->__datesHelper->getDateString($date));
    }

    public function testGetDateStringEnglish() {
        $this->__setUpEnglish();
        $date = new Opus_Date('2005-03-25');
        $this->assertEquals('2005/03/25', $this->__datesHelper->getDateString($date));
    }

    public function testGetDateStringForInvalidDate() {
        $this->__setUpGerman();
        $date = new Opus_Date('2005');
        $this->assertFalse($date->isValid());
        $this->assertEquals(null, $this->__datesHelper->getDateString($date));
    }

}
