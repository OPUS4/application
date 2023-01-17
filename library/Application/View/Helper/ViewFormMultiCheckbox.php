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

/**
 * Gibt Select-Element als Ansicht aus (nur Wert).
 */
class Application_View_Helper_ViewFormMultiCheckbox extends Zend_View_Helper_FormMultiCheckbox
{
    /**
     * @param string     $name
     * @param mixed|null $value
     * @param array|null $attribs
     * @param array|null $options
     * @param string     $listsep
     * @return string
     */
    public function viewFormMultiCheckbox($name, $value = null, $attribs = null, $options = null, $listsep = "<br />\n")
    {
        $info = $this->_getInfo($name, $value, $attribs, $options, $listsep);
        // @phpcs:disable
        extract($info);
        // @phpcs:enable

        if (is_array($value)) {
            $value = implode(', ', $value);
        }

        return '<div'
            . ' id="' . $this->view->escape($id) . '"'
            . ' class="field">'
            . $this->view->escape($value)
            . '</div>';
    }
}
