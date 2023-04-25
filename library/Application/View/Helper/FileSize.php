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

class Application_View_Helper_FileSize extends Zend_View_Helper_Abstract
{
    /** @var string[] */
    private $labels = ["Byte", "KB", "MB", "GB"];

    /**
     * Formats file size for output.
     *
     * @param string     $name
     * @param mixed      $value
     * @param array|null $options
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter) // Variables part of interface
     */
    public function fileSize($name, $value = 0, $options = null)
    {
        $labelIndex = 0;
        $maxIndex   = count($this->labels) - 1;

        while ($value >= 1024 && $labelIndex < $maxIndex) {
            $labelIndex++;
            $value /= 1024;
        }

        return sprintf('%1$s %2$s', round($value, 2), $this->labels[$labelIndex]);
    }
}
