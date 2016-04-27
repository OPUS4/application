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
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2016, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 */
class Solrsearch_Model_SeriesUtil extends Application_Model_Abstract
{

    /**
     * Checks if any series is available for display in browsing.
     * @return bool Number of displayable series
     */
    public function hasDisplayableSeries()
    {
        return count($this->getVisibleNonEmptySeriesSortedBySortKey()) > 0;
    }

    /**
     * Return all non-empty visible Opus_Series objects in sorted order.
     *
     * @return array an array of Opus_Series objects
     */
    public function getVisibleNonEmptySeriesSortedBySortKey()
    {
        $visibleSeries = array();
        foreach (Opus_Series::getAllSortedBySortKey() as $series)
        {
            if ($series->getVisible() == '1' && $series->getNumOfAssociatedPublishedDocuments() > 0)
            {
                array_push($visibleSeries, $series);
            }
        }

        return $visibleSeries;
    }

    /**
     * @return array
     */
    public function getVisibleSeries()
    {
        $visibleSeries = $this->getVisibleNonEmptySeriesSortedBySortKey();

        $allSeries = array();

        foreach ($visibleSeries as $series)
        {
            array_push($allSeries, array('id' => $series->getId(), 'title' => $series->getTitle()));
        }

        $config = $this->getConfig();

        if (isset($config->browsing->series->sortByTitle) && boolval($config->browsing->series->sortByTitle))
        {
            usort($allSeries, function ($value1, $value2) {
                    return strnatcmp($value1['title'], $value2['title']);
                }
            );
        }

        return $allSeries;
    }

}
