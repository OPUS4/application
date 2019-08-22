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
 * @category    Application Unit Test
 * @package     Application
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2019, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

class Application_SearchExceptionTest extends ControllerTestCase
{

    public function testConstructForServerUnreachable()
    {
        $cause = new Opus_Search_Exception('test', Opus_Search_Exception::SERVER_UNREACHABLE);

        $exception = new Application_SearchException($cause, false);

        $this->assertEquals('error_search_unavailable', $exception->getMessage());
        $this->assertEquals(503, $exception->getHttpResponseCode());
    }

    public function testConstructForServerUnreachablePlainMessage()
    {
        $cause = new Opus_Search_Exception('test', Opus_Search_Exception::SERVER_UNREACHABLE);

        $exception = new Application_SearchException($cause, true);

        $this->assertEquals('search server is not responding -- try again later', $exception->getMessage());
        $this->assertEquals(503, $exception->getHttpResponseCode());
    }

    public function testConstructForInvalidQuery()
    {
        $cause = new Opus_Search_Exception('test', Opus_Search_Exception::INVALID_QUERY);

        $exception = new Application_SearchException($cause, false);

        $this->assertEquals('error_search_invalidquery', $exception->getMessage());
        $this->assertEquals(500, $exception->getHttpResponseCode());
    }

    public function testConstructForInvalidQueryPlainMessage()
    {
        $cause = new Opus_Search_Exception('test', Opus_Search_Exception::INVALID_QUERY);

        $exception = new Application_SearchException($cause, true);

        $this->assertEquals('search query is invalid -- check syntax', $exception->getMessage());
        $this->assertEquals(500, $exception->getHttpResponseCode());
    }

    public function testContructPlainMessage()
    {
        $cause = new Opus_Search_Exception('test');

        $exception = new Application_SearchException($cause, true);

        $this->assertEquals('unknown error while executing search query', $exception->getMessage());
        $this->assertEquals(500, $exception->getHttpResponseCode());
    }

    public function testConstruct()
    {
        $cause = new Opus_Search_Exception('test');

        $exception = new Application_SearchException($cause, false);

        $this->assertEquals('error_search_unknown', $exception->getMessage());
        $this->assertEquals(500, $exception->getHttpResponseCode());
    }
}
