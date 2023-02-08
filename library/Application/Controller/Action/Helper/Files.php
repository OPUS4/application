<?php

/**
 * This file is part of OPUS. The software OPUS has been originally developed
 * at the University of Stuttgart with funding from the German Research Net,
 * the Federal Department of Higher Education and Research and the Ministry
 * of Science, Research and the Arts of the State of Baden-Wuerttemberg.
 *
 * OPUS 4 is a complete rewrite of the original OPUS software and was developed
 * by the Stuttgart University Library, the Library Service Center
 * Baden-Wuerttemberg, the North Rhine-Westphalian Library Service Center,
 * the Cooperative Library Network Berlin-Brandenburg, the Saarland University
 * and State Library, the Saxon State Library - Dresden State and University
 * Library, the Bielefeld University Library and the University Library of
 * Hamburg University of Technology with funding from the German Research
 * Foundation and the European Regional Development Fund.
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

use Opus\Common\Config;
use Opus\Common\Log;

/**
 * Helper for basic file and folder operations.
 *
 * Can be used to list files in a directory matching a pattern.
 *
 * TODO implement as Controller Helper (nicht soviel static)
 */
class Application_Controller_Action_Helper_Files extends Zend_Controller_Action_Helper_Abstract
{
    /**
     * Lists files in import folder. If $ignoreAllowedFiletypes is set to true
     * all files will be returned. Otherwise only files of allowed types will
     * be considered.
     *
     * @param string $folder
     * @param bool   $ignoreAllowedTypes
     * @return array
     */
    public function listFiles($folder, $ignoreAllowedTypes = false)
    {
        if (! is_dir($folder) || ! is_readable($folder)) {
            throw new Application_Exception("Directory '$folder' is not readable.");
        }

        $result = [];
        foreach (new DirectoryIterator($folder) as $file) {
            if (self::checkFile($file, $ignoreAllowedTypes)) {
                array_push(
                    $result,
                    [
                        'name' => $file->getFilename(),
                        'size' => number_format($file->getSize() / 1024.0, 2, '.', ''),
                    ]
                );
            }
        }
        return $result;
    }

    /**
     * @return false|string[]|null
     */
    private function getAllowedFileTypes()
    {
        $config = Config::get();

        if (! isset($config->publish->filetypes->allowed)) {
            return null;
        }

        $allowed = explode(',', $config->publish->filetypes->allowed);
        Application_Util_Array::trim($allowed);
        return $allowed;
    }

    /**
     * @param DirectoryIterator $file
     * @param bool              $ignoreAllowedTypes
     * @return bool
     * @throws Zend_Exception
     */
    private function checkFile($file, $ignoreAllowedTypes)
    {
        $log        = Log::get();
        $logMessage = 'check for file: ' . $file->getPathname();

        if (! $ignoreAllowedTypes) {
            $allowedFileTypes = self::getAllowedFileTypes();
            if ($allowedFileTypes === null || empty($allowedFileTypes)) {
                $log->debug('no filetypes are allowed');
                return false;
            }
        }

        // ignore . and ..
        if ($file->isDot()) {
            return false;
        }

        // filter links and directories
        if (! $file->isFile()) {
            $log->debug($logMessage . ' : is not a regular file');
            return false;
        }

        // filter unreadable files
        if (! $file->isReadable()) {
            $log->debug($logMessage . ' : is not readable');
            return false;
        }

        // filter hidden files
        if (strpos($file->getFilename(), '.') === 0) {
            $log->debug($logMessage . ' : is a hidden file');
            return false;
        }

        if ($ignoreAllowedTypes) {
            return true;
        }

        foreach ($allowedFileTypes as $fileType) {
            if (fnmatch('*.' . $fileType, $file->getFilename())) {
                $log->debug($logMessage . ' : OK');
                return true;
            }
        }
        $log->debug($logMessage . ' : filetype is not allowed');
        return false;
    }
}
