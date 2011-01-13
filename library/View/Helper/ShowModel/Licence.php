<?php
/*
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
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class View_Helper_ShowModel_Licence extends View_Helper_ShowModel_Abstract {

    public function display($field, $value) {
        $result = '';
        if (false === @is_array($value[0])) {
            if (($this->_saef === false) or (empty($value) === false)) {
                $result = $this->__licenceHelper($field, $value);
            }
        } else {
            foreach ($value as $number => $val) {
                if (($this->_saef === false) or (empty($val) === false)) {
                    $prefix = (++$number) . '. ';
                    $result .= $this->__licenceHelper($field, $val, $prefix);
                }
            }
        }
        return $result;

    }

    /**
     * Helper method for displaying licence field.
     *
     * @param string $field  Contains field name.
     * @param string &$value Contains licence informations.
     * @param string $prefix (Optional) Prefix for multiple licence fields.
     * @return string
     */
    private function __licenceHelper($field, &$value, $prefix = null) {
        $result = '';
        // we "know" that the licence name is in NameLong
        $display_name = @$value['NameLong'];
        $licence_link = @$value['LinkLicence'];
        if (false === empty($licence_link)) {
            $iterim_value = '<a href="' . htmlspecialchars($licence_link) . '">' . htmlspecialchars($display_name) . '</a>';
        } else {
            $iterim_value = htmlspecialchars($display_name);
        }
        if (($this->_saef === false) or (empty($iterim_value) === false)) {
            $data = $this->__skeleton($field, $iterim_value, $prefix);
            $result = $this->_view->partial($this->_partial, $data);
        }
        return $result;
    }

}

?>
