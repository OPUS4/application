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
 * @copyright   Copyright (c) 2016-2019
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 *
 * Reads an OPUS import package containing one or more documents and imports
 * the documents.
 *
 * Currently ZIP and TAR files are supported by extending classes.
 */
abstract class Application_Import_PackageReader
{
    const METADATA_FILENAME = 'opus.xml';

    const EXTRACTION_DIR_NAME = 'extracted';

    private $additionalEnrichments;

    /**
     * Sets additional enrichments that will be added to every imported document.
     * @param $additionalEnrichments
     */
    public function setAdditionalEnrichments($additionalEnrichments)
    {
        $this->additionalEnrichments = $additionalEnrichments;
    }

    /**
     * Verarbeitet das XML-Metadatendokument, dessen Inhalt in $xml übergeben wird.
     * Zugehörige Volltextdateien werden aus dem Verzeichnis $dirName gelesen.
     *
     * @param $xml Ausgelesener Inhalt der XML-Metadatendatei
     * @param $dirName Pfad zum Extraktionsverzeichnis
     * @return Application_Import_ImportStatusDocument Statusdokument mit Informationen zum Ergebnis des Imports
     * @throws Application_Import_MetadataImportInvalidXmlException
     * @throws Application_Import_MetadataImportSkippedDocumentsException
     * @throws Opus_Model_Exception
     * @throws Opus_Security_Exception
     * @throws Zend_Exception
     */
    private function processOpusXML($xml, $dirName)
    {
        $importer = new Application_Import_Importer($xml, false, $this->getLogger());
        $importer->enableSwordContext();
        $importer->setImportDir($dirName);

        $importer->setAdditionalEnrichments($this->additionalEnrichments);
        $importCollection = new Sword_Model_ImportCollection();
        $importer->setImportCollection($importCollection->getCollection());

        $importer->run();
        return $importer->getStatusDoc();
    }

    abstract protected function extractPackage($dirName);

    /**
     * Verarbeitet das Paket im übergebenen Verzeichnis $dirName. Entpackt das Verzeichnis in ein Unterverzeichnis
     * mit dem Namen EXTRACTION_DIR_NAME. Anschließend wird die entpackte Metadaten-Datei opus.xml verarbeitet.
     * Bei der Verarbeitung der XML-Datei werden entpackte Volltextdateien verarbeitet.
     *
     * @param string $dirName
     * @return Application_Import_ImportStatusDocument
     * @throws Zend_Exception
     */
    public function readPackage($dirName)
    {
        $this->getLogger()->info('processing package in directory ' . $dirName);

        if (! (is_dir($dirName) && is_readable($dirName))) {
            $errMsg = 'directory ' . $dirName . ' is not readable!';
            $this->getLogger()->err($errMsg);
            throw new Exception($errMsg);
        }

        $extractDir = $this->createExtractionDir($dirName);
        if (is_null($extractDir)) {
            $errMsg = 'could not create extraction directory ' . $dirName;
            $this->getLogger()->err($errMsg);
            throw new Exception($errMsg);
        }

        $this->extractPackage($dirName);

        return $this->processPackage($extractDir);
    }

    public function getLogger()
    {
        return Zend_Registry::get('Zend_Log');
    }

    /**
     * Prozessiert den Paketinhalt und liefert ein Status-Dokument zurück.
     * Liefert null, wenn das Paket kein Metadaten-Dokument enthält oder
     * dieses leer ist.
     *
     * @param string $extractDir Pfad zum Extraktionsverzeichnis
     * @return Application_Import_ImportStatusDocument
     * @throws Zend_Exception
     */
    private function processPackage($extractDir)
    {
        $metadataFile = $extractDir . DIRECTORY_SEPARATOR . self::METADATA_FILENAME;
        if (! is_readable($metadataFile)) {
            $this->getLogger()->err('missing metadata file ' . $metadataFile);
            return null;
        }

        $content = file_get_contents($metadataFile);
        if ($content === false || trim($content) == '') {
            $this->getLogger()->err('could not get non-empty content from metadata file ' . $metadataFile);
            return null;
        }

        $statusDoc = $this->processOpusXML($content, $extractDir);
        return $statusDoc;
    }

    /**
     * Erzeugt ein Verzeichnis, in dem der Paketinhalt extrahiert werden kann.
     * Liefert den Pfad des Extraktionsverzeichnisses zurück oder null, wenn
     * es nicht erzeugt werden konnte;
     *
     * @param string $baseDir Basisverzeichnis, in das Extraktionsverzeichnis angelegt werden soll.
     * @return string|null Absoluter Pfad zum Extraktionsverzeichnis
     * @throws Zend_Exception
     */
    private function createExtractionDir($baseDir)
    {
        $extractDir = $baseDir . DIRECTORY_SEPARATOR . self::EXTRACTION_DIR_NAME;
        if (mkdir($extractDir) === false) {
            $this->getLogger()->err('could not create extraction directory ' . $extractDir);
            return null;
        }

        if (! is_readable($extractDir)) {
            $this->getLogger()->err('extraction directory is not readable: ' . $extractDir);
            return null;
        }

        if (! is_writable($extractDir)) {
            $this->getLogger()->err('extraction directory is not writable: ' . $extractDir);
            return null;
        }

        return $extractDir;
    }
}
