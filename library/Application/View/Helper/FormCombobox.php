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
 * @copyright   Copyright (c) 2017, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * View helper for rendering a combobox (text input + select).
 */
class Application_View_Helper_FormCombobox extends Zend_View_Helper_FormElement
{
    /**
     * @param string     $name
     * @param mixed|null $value
     * @param array|null $attribs
     * @param array|null $options
     * @param string     $listsep
     * @return string
     */
    public function formCombobox($name, $value = null, $attribs = null, $options = null, $listsep = "<br />\n")
    {
        $info = $this->_getInfo($name, $value, $attribs, $options, $listsep);

        // @phpcs:disable
        extract($info);
        // @phpcs:enable

        $xhtml = "<div class=\"ui-widget\">\n    ";

        $xhtml .= "<select name=\"$name\" class=\"combobox\">\n";

        if ($value !== null && strlen(trim($value)) > 0 && ! in_array($value, $options)) {
            $xhtml .= "<option value=\"$value\">$value</option>\n";
        }

        foreach ((array) $options as $optionValue => $optionLabel) {
            $xhtml .= "<option value=\"$optionValue\"";
            if ($optionValue === $value) {
                $xhtml .= ' selected="selected"';
            }
            $xhtml .= ">$optionLabel</option>\n";
        }

        $xhtml .= "</select>\n";
        $xhtml .= "</div>\n";

        return $xhtml;
    }
}
