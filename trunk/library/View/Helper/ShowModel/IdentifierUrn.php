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
 * @category    TODO
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class View_Helper_ShowModel_IdentifierUrn extends View_Helper_ShowModel_Abstract {

    /**
     * An urn field need a special handling for display.
     *
     * @param string $field  Urn field for displaying
     * @param array  &$value Value of urn field
     * @return string
     */
    public function display($field, $value) {
        $result = '';
        if (false === @is_array($value[0])) {
            $result = $this->__urnHelper($field, $value);
        } else {
            foreach ($value as $number => $val) {
                $result .= $this->__urnHelper($field, $val);
            }
        }
        return $result;
    }

    /**
     * Helper method for displaying urn values.
     *
     * @param string $field  Field to display
     * @param array  &$value Value of field
     * @return string
     */
    private function __urnHelper($field, array &$value) {
        $result = '';
        $urn_value = @$value['Value'];
        if (($this->_saef === false) or (empty($urn_value) === false)) {
            // TODO resolving URI should configurable
            $output_string = 'http://nbn-resolving.de/urn/resolver.pl?' . htmlspecialchars($urn_value);
            $iterim_value = '<a href="' . $output_string . '">' . $output_string . '</a>';
            $data = $this->__skeleton($field, $iterim_value);
            $result = $this->_view->partial($this->_partial, $data);
        }
        return $result;
    }

}

?>
