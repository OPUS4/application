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
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2016, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

class Solrsearch_Model_Series {

    private $_series;

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
            throw new Solrsearch_Model_Exception(
                "Series with id '" . $seriesId . "' does not have any published documents."
            );
        }
        $this->_series = $s;
    }

    public function getId() {
        return $this->_series->getId();
    }

    public function getTitle() {
        return $this->_series->getTitle();
    }

    public function getInfobox() {
        return $this->_series->getInfobox();
    }

    public function getLogoFilename() {
        $logoDir = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'series_logos'
            . DIRECTORY_SEPARATOR . $this->_series->getId();
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

