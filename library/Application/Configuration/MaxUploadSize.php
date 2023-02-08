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

use Opus\Common\Config;
use Opus\Common\Log;

class Application_Configuration_MaxUploadSize
{
    /**
     * The element sword:maxUploadSize indicates the maximum size (in kB) of
     * a package that can be uploaded to the SWORD service.
     *
     * @return int maximum upload size in kilobyte
     */
    public function getMaxUploadSizeInKB()
    {
        $minSize = $this->getMaxUploadSizeInByte();
        return floor($minSize / 1024);
    }

    /**
     * @return int
     * @throws Zend_Exception
     */
    public function getMaxUploadSizeInByte()
    {
        $logger = Log::get();

        $config         = Config::get();
        $maxFileSizeInt = intval($config->publish->maxfilesize);
        $logger->debug('publish.maxfilesize (Byte) = ' . $maxFileSizeInt);

        $postMaxSizeInt = $this->convertToBytes(ini_get('post_max_size'));
        $logger->debug('post_max_size (Byte) = ' . $postMaxSizeInt);

        $minSize = $maxFileSizeInt < $postMaxSizeInt ? $maxFileSizeInt : $postMaxSizeInt;

        $uploadMaxFilesizeInt = $this->convertToBytes(ini_get('upload_max_filesize'));
        $logger->debug('upload_max_filesize (Byte) = ' . $uploadMaxFilesizeInt);
        if ($uploadMaxFilesizeInt < $minSize) {
            $minSize = $uploadMaxFilesizeInt;
        }

        return $minSize;
    }

    /**
     * @param string $val
     * @return int
     */
    private function convertToBytes($val)
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
                $valInt *= 1024;
                // fall through is intended
        }

        return $valInt;
    }
}
