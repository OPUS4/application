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
 * @category    TODO
 * @author      Sascha Szott <szott@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * a value object holding all data that is required to reconstruct the user input
 */
class FormValues {

    /**
     * maps each select box (identified by its id attribute) to its selection
     * @var array
     */
    private $select_boxes;

    /**
     * maps each input field (identified by its id attribute) to its value
     * @var array
     */
    private $input_boxes;

    /**
     * the selected sort field
     * @var string
     */
    private $sort_field;

    /**
     * the selected sort order
     * @var string
     */
    private $sort_order;

    /**
     * the selected number of results per page
     * @var int
     */
    private $rows;


    public function getSelectBoxes() {
        return $this->select_boxes;
    }

    public function setSelectBoxes($select_boxes) {
        $this->select_boxes = $select_boxes;
    }

    public function getInputBoxes() {
        return $this->input_boxes;
    }

    public function setInputBoxes($input_boxes) {
        $this->input_boxes = $input_boxes;
    }

    public function getSortField() {
        return $this->sort_field;
    }

    public function setSortField($sort_field) {
        $this->sort_field = $sort_field;
    }

    public function getSortOrder() {
        return $this->sort_order;
    }

    public function setSortOrder($sort_order) {
        $this->sort_order = $sort_order;
    }

    public function getRows() {
        return $this->rows;
    }

    public function setRows($rows) {
        $this->rows = $rows;
    }
}
?>