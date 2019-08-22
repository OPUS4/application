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
 * @category    Application
 * @package     Application_Import
 * @author      Sascha Szott <opus-development@saschaszott.de>
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2018-2019
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

class Application_Import_TarPackageReader extends Application_Import_PackageReader
{
    protected function extractPackage($dirName)
    {
        $filename = $dirName . DIRECTORY_SEPARATOR . 'package.tar';
        $this->getLogger()->info('processing TAR package in file system ' . $filename);

        if (! is_readable($filename)) {
            $errMsg = 'TAR package ' . $filename . ' is not readable!';
            $this->getLogger()->err($errMsg);
            throw new Exception($errMsg);
        }

        $phar = new PharData($filename);
        $phar->extractTo($dirName . DIRECTORY_SEPARATOR . self::EXTRACTION_DIR_NAME);
    }
}
