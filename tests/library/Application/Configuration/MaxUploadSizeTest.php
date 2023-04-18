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
 * @copyright   Copyright (c) 2016, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

class Application_Configuration_MaxUploadSizeTest extends TestCase
{
    /**
     * Der Wert von sword:maxUploadSize ist als das Minimum von den folgenden
     * drei Werten definiert:
     *
     * 1. Konfigurationsparameter publish.maxfilesize
     * 2. PHP-Laufzeitkonfiguration post_max_size
     * 3. PHP-Laufzeitkonfiguration upload_max_filesize
     */
    public function testMaxUploadSize()
    {
        $config = $this->getConfig();

        $configMaxFileSize = intval($config->publish->maxfilesize);
        $postMaxSize       = $this->convertToKbyte(ini_get('post_max_size'));
        $uploadMaxFilesize = $this->convertToKbyte(ini_get('upload_max_filesize'));

        $maxUploadSizeHelper = new Application_Configuration_MaxUploadSize();

        $maxUploadSizeByte  = $maxUploadSizeHelper->getMaxUploadSizeInByte();
        $maxUploadSizeKbyte = $maxUploadSizeHelper->getMaxUploadSizeInKB();

        $this->assertTrue($maxUploadSizeByte <= $configMaxFileSize, "cond1: $maxUploadSizeByte is greater than $configMaxFileSize");
        $this->assertTrue($maxUploadSizeKbyte <= $postMaxSize, "cond2: $maxUploadSizeKbyte is greater than $postMaxSize");
        $this->assertTrue($maxUploadSizeKbyte <= $uploadMaxFilesize, "cond3: $maxUploadSizeKbyte is greater than $uploadMaxFilesize");
    }

    /**
     * @param string $val
     * @return int
     *
     * TODO this should go to utility functions (isn't it useful beyond testing?)
     */
    private function convertToKbyte($val)
    {
        $valTrim = trim($val);
        $valInt  = intval($valTrim);
        $last    = strtolower($valTrim[strlen($valTrim) - 1]);
        switch ($last) {
            // The 'G' modifier is available since PHP 5.1.0
            case 'g':
                $valInt *= 1024;
                // fall through is intended
            case 'm':
                $valInt *= 1024;
                // fall through is intended
            case 'k':
                // do nothing
                break;
            default:
                $valInt /= 1024;
        }

        return $valInt;
    }
}
