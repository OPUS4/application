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

use Opus\Common\Date;

class Application_View_Helper_FormatDateTest extends ControllerTestCase
{
    /** @var string[] */
    protected $additionalResources = ['translation'];

    public function testFormatDate()
    {
        $helper = new Application_View_Helper_FormatDate();

        $this->useEnglish(); // rendering depends on language set in session

        $this->assertEquals('2017/10/21', $helper->formatDate('21', '10', '2017'));
        $this->assertEquals('2017/10/21', $helper->formatDate(21, 10, 2017));

        $this->useGerman();

        $this->assertEquals('21.10.2017', $helper->formatDate('21', '10', '2017'));
        $this->assertEquals('21.10.2017', $helper->formatDate(21, 10, 2017));
    }

    public function testFormatOpusDate()
    {
        $helper = new Application_View_Helper_FormatDate();

        $date = new Date(DateTime::createFromFormat('Y/m/d  H:i', '2017/03/10 14:51'));

        $this->useEnglish();

        $this->assertEquals('2017/03/10', $helper->formatOpusDate($date));

        $this->useGerman();

        $this->assertEquals('10.03.2017', $helper->formatOpusDate($date));
    }

    public function testFormatOpusDateWithTime()
    {
        $helper = new Application_View_Helper_FormatDate();

        $date = new Date(DateTime::createFromFormat('Y/m/d  H:i', '2017/03/10 14:51'));

        $this->useEnglish();

        $this->assertEquals('2017/03/10 14:51', $helper->formatOpusDate($date, true));

        $this->useGerman();

        $this->assertEquals('10.03.2017 14:51', $helper->formatOpusDate($date, true));
    }

    public function testFormatDateWithoutParameters()
    {
        $helper = new Application_View_Helper_FormatDate();

        $this->assertSame($helper, $helper->formatDate());
    }

    public function testFormatOpusDateWithNull()
    {
        $helper = new Application_View_Helper_FormatDate();

        $this->assertEquals('', $helper->formatOpusDate(null));
    }
}
