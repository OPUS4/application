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
 * @package     Module_Sword
 * @author      Sascha Szott
 * @copyright   Copyright (c) 2016
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
class Sword_Model_PackageHandler
{

    private $additionalEnrichments;

    private $packageType;

    const PACKAGE_TYPE_ZIP = 'zip';

    const PACKAGE_TYPE_TAR = 'tar';

    public function __construct($contentType)
    {
        $this->setPackageType($contentType);
    }

    public function setAdditionalEnrichments($additionalEnrichments)
    {
        $this->additionalEnrichments = $additionalEnrichments;
    }

    private function setPackageType($contentType)
    {
        if (is_null($contentType) || $contentType === false) {
            throw new Exception('Content-Type header is required');
        }

        switch ($contentType) {
            case 'application/zip':
                $this->packageType = self::PACKAGE_TYPE_ZIP;
                break;
            case 'application/tar':
                $this->packageType = self::PACKAGE_TYPE_TAR;
                break;
            default:
                throw new Exception('Content-Type ' . $contentType . ' is currently not supported');
        }
    }

    public function handlePackage($payload)
    {
        $tmpFileName = $this->getTmpFileName($payload);
        $this->savePackage($payload, $tmpFileName);
        $packageReader = $this->getPackageReader($this->packageType);
        try {
            $statusDoc = $packageReader->readPackage($tmpFileName);
        } finally {
            unlink($tmpFileName);
        }

        return $statusDoc;
    }

    /**
     * @param $packageType
     * @return null
     *
     * TODO make types configurable and remove explicit TAR/ZIP declarations in this class (use factory class?)
     */
    private function getPackageReader($packageType)
    {
        $packageReader = null;
        switch ($packageType) {
            case self::PACKAGE_TYPE_ZIP:
                $packageReader = new Application_Import_ZipPackageReader();
                break;
            case self::PACKAGE_TYPE_TAR:
                $packageReader = new Application_Import_TarPackageReader();
                break;
            default:
                // TODO do some error handling
                break;
        }
        $packageReader->setAdditionalEnrichments($this->additionalEnrichments);
        return $packageReader;
    }

    private function savePackage($payload, $tmpFileName)
    {
        file_put_contents($tmpFileName, $payload);
    }

    private function getTmpFileName($payload)
    {
        $dirName = Application_Configuration::getInstance()->getTempPath();
        $fileName = md5($payload) . '-' . time() . '-' . rand(10000, 99999) . '.' . $this->packageType;
        $tmpFileName = $dirName . $fileName;
        $suffix = 0;
        while (file_exists($tmpFileName)) {
            // add suffix to make file name unique (even if collision events are not very likely)
            $tmpFileName .= "-$suffix";
            $suffix++;
        }
        return $tmpFileName;
    }
}
