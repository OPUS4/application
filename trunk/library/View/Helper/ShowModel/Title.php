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

class View_Helper_ShowModel_Title extends View_Helper_ShowModel_Abstract {

    /**
     * General method for displaying titles or abstracts
     *
     * @param string $field   Field to display
     * @param mixed  &$values Value of field
     * @return string
     */
    public function display($field, $values) {
        $result = '';
        // silence decision about multi values or not
        if (@is_array($values[0]) === false) {
            // only one element to display
            if (($this->_saef === false) or (empty($values) === false)) {
                $result = $this->__titleHelper($field, $values);
            }
        } else {
            // more than one element to display
            foreach ($values as $number => $value) {
                if (($this->_saef === false) or (empty($value) === false)) {
                    $prefix = (++$number) . '. ';
                    $result .= $this->__titleHelper($field, $value, $prefix);
                }
            }
        }
        return $result;
    }

    /**
     * Helper method for displaying titles or abstracts
     *
     * @param string $field  Field for displaying
     * @param array  &$value Value of field
     * @param string $prefix (Optional) Prefix for displaying field
     * @return string
     */
    private function __titleHelper($field, array &$value, $prefix = null) {
        $data = array();
        // title language
        if (true === array_key_exists('Language', $value)) {
            $language = $value['Language'];
            $language_list = Zend_Registry::get('Available_Languages');
            if (true === array_key_exists($language, $language_list)) {
               $language = $language_list[$language];
            }
            $data[] = $this->__skeleton('Language', htmlspecialchars($language));
        }
        // title value
        $iterim_value = array_key_exists('Value', $value) ? $value['Value'] : '';
        $data[] = $this->__skeleton($field . 'Value', htmlspecialchars($iterim_value));
        $iterim_data = $this->_view->partialLoop($this->_partial, $data);
        $outer = $this->__skeleton($field, $iterim_data, $prefix);
        return $this->_view->partial($this->_partial, $outer);
    }

}
