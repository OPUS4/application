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

// TODO LAMINAS still necessary? probably not -> remove

// Provide boolval function for PHP <5.5
if (! function_exists('boolval')) {
    /**
     * @param mixed $value
     * @return bool
     */
    function boolval($value)
    {
        return (bool) $value;
    }
}

// mb_strlen is required to get the total number of bytes in a given string
// fall back to strlen even if we retrieve the number of characters instead of bytes
// in PHP installation with multibyte character support
if (! function_exists('mb_strlen')) {
    /**
     * @param string $str
     * @param string $encoding
     * @return int
     */
    function mb_strlen($str, $encoding)
    {
        return strlen($str);
    }
}

/**
 * Function for dividing integers used in PersonController.
 */
if (! function_exists('intdiv')) {
    /**
     * @param int $divided
     * @param int $divisor
     * @return float|int
     */
    function intdiv($divided, $divisor)
    {
        return ($divided - $divided % $divisor) / $divisor;
    }
}
