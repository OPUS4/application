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
 * Filter that replace newline characters with whitespaces.
 *
 * This filter is used for document titles that are entered using a textarea,
 * since the titles can be rather long.
 */
class Application_Form_Filter_ReplaceNewlines implements Zend_Filter_Interface
{
    /**
     * Returns value with newline characters replaced by whitespaces.
     *
     * The replacing happens in two steps to avoid multiple whitespaces for each
     * line break.
     *
     * @param string $value Value that should be filtered
     * @return string Filtered string (newlines => whitespaces)
     */
    public function filter($value)
    {
        if ($value === null) {
            return ''; // TODO DESIGN this preserves old behaviour, but does it make sense?
        }
        $newValue = str_replace(["\r\n"], ' ', $value);
        return str_replace(["\r", "\n"], ' ', $newValue);
    }
}
