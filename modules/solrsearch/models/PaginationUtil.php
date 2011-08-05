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
 * @author      Julian Heise <heise@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id:$
 */
class Solrsearch_Model_PaginationUtil {

    // prequisites
    private $rows;
    private $numHits;
    private $query;
    private $searchType;
    private $startIndex;

    // results
    private $lastPageStartIndex;
    private $prevPageStartIndex;
    private $nextPageStartIndex;

    public function  __construct($rows, $numHits, $startIndex, $query, $searchType) {
        $this->rows = $rows;
        $this->numHits = $numHits;
        $this->query = $query;
        $this->searchType = $searchType;
        $this->startIndex = $startIndex;
        $this->compute();
    }

    private function compute() {
        $this->lastPageStartIndex = 0;
        $this->lastPageStartIndex =(int)(($this->numHits - 1) / $this->rows  * $this->rows);
        $this->prevPageStartIndex = $this->startIndex - $this->rows;
        $this->nextPageStartIndex = $this->startIndex + $this->rows;
    }

    public function getFirstPageUrlArray() {
        return $this->constructUrlArrayWithStartIndex('0');
    }

    private function constructUrlArrayWithStartIndex($pageStartIndex) {
        $array =  array(
            'searchtype'=>$this->searchType,
            'start'=>$pageStartIndex,
            'rows'=>$this->rows);
        if($this->query != null) {
            $array['query'] = $this->query;
        }
        return $array;
    }

    public function getNextPageUrlArray() {
        return $this->constructUrlArrayWithStartIndex($this->nextPageStartIndex);
    }

    public function getPreviousPageUrlArray() {
        return $this->constructUrlArrayWithStartIndex($this->prevPageStartIndex);
    }

    public function getLastPageUrlArray() {
        return $this->constructUrlArrayWithStartIndex($this->lastPageStartIndex);
    }
}
?>
