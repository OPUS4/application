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
 * @author      Sascha Szott <opus-development@saschaszott.de>
 * @copyright   Copyright (c) 2016-2019
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
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

    /**
     * Verarbeitet die mit dem SWORD-Request übergebene Paketdatei.
     *
     * @param string $payload der Inhalt der Paketdatei
     * @return mixed
     */
    public function handlePackage($payload)
    {
        $packageReader = $this->getPackageReader();
        if (is_null($packageReader)) {
            // TODO improve error handling
            return null;
        }

        $tmpDirName = null;
        $statusDoc = null;
        try {
            $tmpDirName = $this->createTmpDir($payload);
            $this->savePackage($payload, $tmpDirName);
            $statusDoc = $packageReader->readPackage($tmpDirName);
        } finally {
            if (! is_null($tmpDirName)) {
                $this->cleanupTmpDir($tmpDirName);
            }
        }
        return $statusDoc;
    }

    /**
     * Entfernt das zuvor erzeugte temporäre Verzeichnis für die Extraktion des Paketinhalts.
     * Das Verzeichnis enthält Dateien und ein Unterverzeichnis. Daher ist ein rekursives Löschen
     * erforderlich.
     *
     * @param string $tmpDirName
     */
    private function cleanupTmpDir($tmpDirName)
    {
        $it = new RecursiveDirectoryIterator($tmpDirName, RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($files as $file) {
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }
        rmdir($tmpDirName);
    }

    /**
     * Liefert in Abhängigkeit vom zu verarbeitenden Pakettyp ein passendes Objekt zum Einlesen des Pakets zurück.
     * Liefert null zurück, wenn der Pakettyp nicht verarbeitet werden kann.
     *
     * @return Application_Import_PackageReader
     *
     * TODO make types configurable and remove explicit TAR/ZIP declarations in this class (use factory class?)
     */
    private function getPackageReader()
    {
        $packageReader = null;
        switch ($this->packageType) {
            case self::PACKAGE_TYPE_ZIP:
                $packageReader = new Application_Import_ZipPackageReader();
                break;
            case self::PACKAGE_TYPE_TAR:
                $packageReader = new Application_Import_TarPackageReader();
                break;
            default:
                break;
        }
        $packageReader->setAdditionalEnrichments($this->additionalEnrichments);
        return $packageReader;
    }

    /**
     * Speichert die übergebene Payload als Datei im übergebenen Verzeichnis ab.
     *
     * @param string $payload
     * @param string $tmpDir
     */
    private function savePackage($payload, $tmpDir)
    {
        $tmpFileName = $tmpDir . DIRECTORY_SEPARATOR . 'package.' . $this->packageType;
        file_put_contents($tmpFileName, $payload);
    }

    /**
     * Erzeugt ein temporäres Verzeichnis, in dem die mit dem SWORD-Request übergebene Datei zwischengespeichert werden
     * kann. Die Methode gibt den absoluten Pfad des Verzeichnisses zurück.
     *
     * @param string $payload der Inhalt des SWORD-Packages
     * @return string absoluter Pfad des temporären Ablageverzeichnisses
     * @throws Application_Exception
     */
    private function createTmpDir($payload)
    {
        $baseDirName = Application_Configuration::getInstance()->getTempPath()
            . DIRECTORY_SEPARATOR . md5($payload) . '-' . time() . '-' . rand(10000, 99999);
        $suffix = 0;
        $dirName = "$baseDirName-$suffix";
        while (file_exists($dirName)) {
            // add another suffix to make file name unique (even if collision events are not very likely)
            $suffix++;
            $dirName = "$baseDirName-$suffix";
        }
        mkdir($dirName);
        return $dirName;
    }
}
