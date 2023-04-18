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

class Solrsearch_Model_PaginationUtilTest extends ControllerTestCase
{
    public function testGetFirstPageUrlArray()
    {
        $util  = new Solrsearch_Model_PaginationUtil(10, 100, 0, null, 'testSearchType');
        $array = $util->getFirstPageUrlArray();
        $this->assertUrlArray(0, 10, 'testSearchType', null, $array);
    }

    public function testGetFirstPageUrlArrayAfterApplyingPagination()
    {
        $util  = new Solrsearch_Model_PaginationUtil(10, 100, 10, null, 'testSearchType');
        $array = $util->getFirstPageUrlArray();
        $this->assertUrlArray(0, 10, 'testSearchType', null, $array);
    }

    public function testGetFirstPageUrlArrayAfterApplyingPaginationTwice()
    {
        $util  = new Solrsearch_Model_PaginationUtil(10, 100, 20, null, 'testSearchType');
        $array = $util->getFirstPageUrlArray();
        $this->assertUrlArray(0, 10, 'testSearchType', null, $array);
    }

    public function testGetFirstPageUrlArrayAfterApplyingUserDefinedPagination()
    {
        $util  = new Solrsearch_Model_PaginationUtil(10, 100, 44, null, 'testSearchType');
        $array = $util->getFirstPageUrlArray();
        $this->assertUrlArray(0, 10, 'testSearchType', null, $array);
    }

    public function testGetNextPageUrlArray()
    {
        $util  = new Solrsearch_Model_PaginationUtil(10, 100, 0, null, 'testSearchType');
        $array = $util->getNextPageUrlArray();
        $this->assertUrlArray(10, 10, 'testSearchType', null, $array);
    }

    public function testGetNextPageUrlArrayAfterApplyingPagination()
    {
        $util  = new Solrsearch_Model_PaginationUtil(10, 100, 10, null, 'testSearchType');
        $array = $util->getNextPageUrlArray();
        $this->assertUrlArray(20, 10, 'testSearchType', null, $array);
    }

    public function testGetNextPageUrlArrayAfterApplyingPaginationTwice()
    {
        $util  = new Solrsearch_Model_PaginationUtil(10, 100, 20, null, 'testSearchType');
        $array = $util->getNextPageUrlArray();
        $this->assertUrlArray(30, 10, 'testSearchType', null, $array);
    }

    public function testGetNextPageUrlArrayAfterApplyingUserDefinedPagination()
    {
        $util  = new Solrsearch_Model_PaginationUtil(10, 100, 44, null, 'testSearchType');
        $array = $util->getNextPageUrlArray();
        $this->assertUrlArray(54, 10, 'testSearchType', null, $array);
    }

    public function testGetPreviousPageUrlArray()
    {
        $util  = new Solrsearch_Model_PaginationUtil(10, 100, 20, null, 'testSearchType');
        $array = $util->getPreviousPageUrlArray();
        $this->assertUrlArray(10, 10, 'testSearchType', null, $array);
    }

    public function testGetPreviousPageUrlArrayApplyingPagination()
    {
        $util  = new Solrsearch_Model_PaginationUtil(10, 100, 30, null, 'testSearchType');
        $array = $util->getPreviousPageUrlArray();
        $this->assertUrlArray(20, 10, 'testSearchType', null, $array);
    }

    public function testGetPreviousPageUrlArrayApplyingUserDefinedPagination()
    {
        $util  = new Solrsearch_Model_PaginationUtil(10, 100, 44, null, 'testSearchType');
        $array = $util->getPreviousPageUrlArray();
        $this->assertUrlArray(34, 10, 'testSearchType', null, $array);
    }

    public function testGetLastPageUrlArray()
    {
        $util  = new Solrsearch_Model_PaginationUtil(10, 100, 0, null, 'testSearchType');
        $array = $util->getLastPageUrlArray();
        $this->assertUrlArray(90, 10, 'testSearchType', null, $array);
    }

    public function testGetLastPageUrlArrayApplyingPagination()
    {
        $util  = new Solrsearch_Model_PaginationUtil(10, 100, 10, null, 'testSearchType');
        $array = $util->getLastPageUrlArray();
        $this->assertUrlArray(90, 10, 'testSearchType', null, $array);
    }

    public function testGetLastPageUrlArrayApplyingPaginationTwice()
    {
        $util  = new Solrsearch_Model_PaginationUtil(10, 100, 20, null, 'testSearchType');
        $array = $util->getLastPageUrlArray();
        $this->assertUrlArray(90, 10, 'testSearchType', null, $array);
    }

    public function testGetLastPageUrlArrayApplyingUserDefinedPagination()
    {
        $util  = new Solrsearch_Model_PaginationUtil(10, 100, 44, null, 'testSearchType');
        $array = $util->getLastPageUrlArray();
        $this->assertUrlArray(90, 10, 'testSearchType', null, $array);
    }

    public function testAssignmentOfQueryTerm()
    {
        $util  = new Solrsearch_Model_PaginationUtil(10, 100, 0, 'queryTerm', 'testSearchType');
        $array = $util->getFirstPageUrlArray();
        $this->assertUrlArray(0, 10, 'testSearchType', 'queryTerm', $array);
    }

    /**
     * @param int    $startIndex
     * @param int    $rows
     * @param string $searchType
     * @param string $query
     * @param array  $array
     */
    private function assertUrlArray($startIndex, $rows, $searchType, $query, $array)
    {
        $this->assertEquals($array['start'], $startIndex);
        $this->assertEquals($array['rows'], $rows);
        $this->assertEquals($array['searchtype'], $searchType);
        if ($query === null) {
            $this->assertTrue(! isset($array['query']));
        } else {
            $this->assertEquals($array['query'], $query);
        }
    }
}
