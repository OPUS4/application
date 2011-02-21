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
 * @package     Module_PublicationList
 * @author      Gunar Maiwald <maiwald@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class PublicationList_Model_PublicationSite {

    private $singleList = array();
    private $name;
    private $nameGerman = "Publikationen";
    private $nameEnglish = "Publications";

    public function __construct() {
    }


    public function addSingleList($singleList) {
        array_push($this->singleList, $singleList);
    }

    public function getSingleList() {
        return $this->singleList;
    }

    public function getNameGerman() {
        return $this->nameGerman;
    }

    public function getNameEnglish() {
        return $this->nameEnglish;
    }

    public function orderSingleLists() {
        $newList = array();
        while (count($this->singleList) != 0) {
            $compare = 0;
            $sltop;
            $index = 0;
            foreach ($this->singleList as $sl) {
                if ($sl->getYear() >= $compare) {
                    $sltop = $sl;
                    $compare = $sl->getYear();
                }
            }
            array_push($newList, $sltop);
            $key = array_search($sltop, $this->singleList);
            unset ($this->singleList[$key]);
        }
        $this->singleList = $newList;
    }
}
?>
