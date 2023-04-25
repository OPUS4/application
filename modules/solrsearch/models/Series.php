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

use Opus\Common\Model\NotFoundException;
use Opus\Common\Series;
use Opus\Common\SeriesInterface;

class Solrsearch_Model_Series
{
    /** @var SeriesInterface */
    private $series;

    /**
     * @param int $seriesId
     * @throws Solrsearch_Model_Exception
     */
    public function __construct($seriesId)
    {
        if ($seriesId === null) {
            throw new Solrsearch_Model_Exception('Could not browse series due to missing id parameter.', 400);
        }

        $s = null;
        try {
            $s = Series::get($seriesId);
        } catch (NotFoundException $e) {
            throw new Solrsearch_Model_Exception("Series with id '" . $seriesId . "' does not exist.", 404);
        }

        if (! $s->getVisible()) {
            throw new Solrsearch_Model_Exception("Series with id '" . $seriesId . "' is not visible.", 404);
        }

        if ($s->getNumOfAssociatedPublishedDocuments() === 0) {
            throw new Solrsearch_Model_Exception(
                "Series with id '" . $seriesId . "' does not have any published documents.",
                404
            );
        }
        $this->series = $s;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->series->getId();
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->series->getTitle();
    }

    /**
     * @return string
     */
    public function getInfobox()
    {
        return $this->series->getInfobox();
    }

    /**
     * @return string|null
     */
    public function getLogoFilename()
    {
        $logoDir = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'series_logos'
            . DIRECTORY_SEPARATOR . $this->series->getId();
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
}
