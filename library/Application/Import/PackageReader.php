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
 * @author      Sascha Szott
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2016-2018
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
     * Removes the directory used for the extracted OPUS import package.
     * @param $dirName
     */
    protected function removeExtractionDir($dirName)
    {
        $files = array_diff(scandir($dirName), array('.','..'));
        foreach ($files as $file) {
            if (is_dir($dirName . DIRECTORY_SEPARATOR . $file)) {
                $this->removeExtractionDir($dirName . DIRECTORY_SEPARATOR . $file);
            }
            else {
                unlink($dirName . DIRECTORY_SEPARATOR . $file);
            }
        }
        rmdir($dirName);
    }

    /**
     * Creates directory for extracting content of OPUS 4 import package.
     * @param $filename
     * @param $packageType
     * @return string
     * @throws Application_Exception
     */
    protected function createExtractionDir($filename, $packageType)
    {
        $tempPath = Application_Configuration::getInstance()->getTempPath();

        $dirName = $tempPath . uniqid() . '-import-' . basename($filename, $packageType);
        mkdir($dirName);
        return $dirName;
    }

    protected function processOpusXML($xml, $dirName)
    {
        $logger = Zend_Registry::get('Zend_Log');

        $importer = new Application_Import_Importer($xml, false, $logger);
        $importer->enableSwordContext();
        $importer->setImportDir($dirName);

        $importer->setAdditionalEnrichments($this->additionalEnrichments);
        $importCollection = new Sword_Model_ImportCollection();
        $importer->setImportCollection($importCollection->getCollection());

        $importer->run();
        return $importer->getStatusDoc();
    }

    abstract public function readPackage($filename);

    public function getLogger() {
        return Zend_Registry::get('Zend_Log');
    }
}
