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
 * @author      Sascha Szott <szott@zib.de>
 * @copyright   Copyright (c) 2008-2012, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class Solrsearch_Model_Series {

    private $series;

    public function  __construct($seriesId) {
        if (is_null($seriesId)) {
            throw new Solrsearch_Model_Exception('Could not browse series due to missing id parameter.');
        }

        $s = null;
        try {
            $s = new Opus_Series($seriesId);
        }
        catch (Opus_Model_NotFoundException $e) {
            throw new Solrsearch_Model_Exception("Series with id '" . $seriesId . "' does not exist.");            
        }

        if ($s->getVisible() !== '1') {
            throw new Solrsearch_Model_Exception("Series with id '" . $seriesId . "' is not visible.");
        }

        if ($s->getNumOfAssociatedPublishedDocuments() === 0) {
            throw new Solrsearch_Model_Exception("Series with id '" . $seriesId . "' does not have any published documents.");
        }
        $this->series = $s;
    }

    public function getId() {
        return $this->series->getId();
    }

    public function getTitle() {
        return $this->series->getTitle();
    }

    public function getInfobox() {
        return $this->series->getInfobox();
    }

    public function getLogoFilename() {
        $logoDir = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'series_logos' . DIRECTORY_SEPARATOR . $this->series->getId();
        if (is_readable($logoDir)) {
            $iterator = new DirectoryIterator($logoDir);
            foreach ($iterator as $fileinfo) {
                if ($fileinfo->isFile()) {
                    return $fileinfo->getFilename();
                }
            }
        }
        return null;        
    }

    public static function hasDisplayableSeries() {
        return count(self::getVisibleNonEmptySeriesSortedBySortKey()) > 0;
    }

    /**
     * Return all non-empty visible Opus_Series objects in sorted order. 
     *
     * @return array an array of Opus_Series objects
     */
    public static function getVisibleNonEmptySeriesSortedBySortKey() {
        $visibleSeries = array();
        foreach (Opus_Series::getAllSortedBySortKey() as $series) {
            if ($series->getVisible() == '1' && $series->getNumOfAssociatedPublishedDocuments() > 0) {
                array_push($visibleSeries, $series);
            }
        }
        return $visibleSeries;       
    }
}

