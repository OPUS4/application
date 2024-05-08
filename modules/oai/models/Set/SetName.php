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
 * @copyright   Copyright (c) 2023 OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Class to represent an oai set name.
 */
class Oai_Model_Set_SetName
{
    /** Regexp pattern to check if a full set name is valid. */
    const SET_PATTERN = "/^([A-Za-z0-9\-_\.!~\*'\(\)]+)(:[A-Za-z0-9\-_\.!~\*'\(\)]+)*$/";

    /** Regexp pattern to check if a set name or subset name is valid. */
    const SET_PART_PATTERN = '/^[A-Za-z0-9\-_\.!~\*\'\(\)]+$/';

    /** @var array */
    private $setParts;

    /**
     * @param string $set The full set name (set:subset)
     * @throws Oai_Model_Exception
     */
    public function __construct($set)
    {
        if (! self::isValidSetName($set)) {
            throw new Oai_Model_Exception(
                "Invalid SetSpec ($set): Must be in format 'set:subset'.",
                Oai_Model_Error::BADARGUMENT
            );
        }

        $this->setParts = explode(':', $set);
    }

    /**
     * Returns set name.
     *
     * @return string|null
     */
    public function getSetName()
    {
        return $this->setParts[0] ?? null;
    }

    /**
     * Returns the subset name.
     *
     * @return string|null
     */
    public function getSubsetName()
    {
        return $this->setParts[1] ?? null;
    }

    /**
     * Returns the full set name
     *
     * @return string|null
     */
    public function getFullSetName()
    {
        return implode(':', $this->setParts);
    }

    /**
     * Returns the number of set parts
     *
     * @return int
     */
    public function getSetPartsCount()
    {
        return count($this->setParts);
    }

    /**
     * Checks a set name
     *
     * @param string $set Set name (set:subset or set)
     * @return bool
     */
    public static function isValidSetName($set)
    {
        return preg_match(self::SET_PATTERN, $set) === 1;
    }

    /**
     * Checks a subset name
     *
     * @param string $subset Subset name
     * @return bool
     */
    public static function isValidSubsetName($subset)
    {
        return preg_match(self::SET_PART_PATTERN, $subset) === 1;
    }
}
