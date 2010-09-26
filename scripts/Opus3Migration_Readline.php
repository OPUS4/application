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
 * @package     Module_Import
 * @author      Oliver Marahrens <o.marahrens@tu-harburg.de>
 * @author      Gunar Maiwald <maiwald@zib.de>
 * @copyright   Copyright (c) 2009, 2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

require_once 'Opus3Migration_Base.php';

class Opus3Migration_Readline extends Opus3Migration_Base {

    /**
     * Migrates OPUS3 to OPUS4 using readline
     *
     * @return void
     */
    public function run() {


        $this->setStylesheet();
        $importFilePath = $this->importfile;
        while (false === file_exists($importFilePath)) {
            //$importFilePath = readline('Please type the path to your OPUS3 database export file (a dumpfile of the database in XML format e.g. /usr/local/opus/complete_database.xml): ');
            $importFilePath = '/home/gunar/data/opuszib_20100914.xml';

        }
        $this->importfile = $importFilePath;

        $importData = $this->loadImportFile();

        // Import collections and series
        $collectionsinput = readline('Do you want to import all the collections from OPUS3? (y/n) ');
        if ($collectionsinput === 'y' || $collectionsinput === 'yes') {
            require_once 'import/models/CollectionsImport.php';
            $importCollections = new Import_Model_CollectionsImport($importData);
        }
        unset($importCollections);

        // Import faculties and institutes
        $input = readline('Do you want to import all the faculties and institutes from OPUS3? (y/n) ');
        if ($input === 'y' || $input === 'yes') {
            require_once 'import/models/InstituteImport.php';
            $importInstitutes = new Import_Model_InstituteImport($importData);
        }
        unset($importInstitutes);

        // Import Licences
        $licenceinput = readline('Do you want to import the licences from OPUS3? (y/n) ');
        if ($licenceinput === 'y' || $licenceinput === 'yes') {
            require_once 'import/models/LicenceImport.php';
            $importLicences = new Import_Model_LicenceImport($importData);
        }
        unset($importLicences);

        // Import documents
        $metadatainput = readline('Do you want to import the metadata of all documents from OPUS3? (y/n) ');
        // TODO: Remove code duplication @see Opus3MigrationParameters::_run();
        if ($metadatainput === 'y' || $metadatainput === 'yes') {
            require_once 'import/models/XMLImport.php';

            $import = new Import_Model_XMLImport($this->xslt, $this->stylesheet);
            $toImport = $import->initImportFile($importData);
            $logfile = '../workspace/tmp/importerrors.xml';

            // TODO: Add error handling to fopen()
            $f = fopen($logfile, 'w');

            $successCount = 0;
            $failureCount = 0;
            foreach ($toImport as $document) {
                //echo "Memory amount: " . round(memory_get_usage() / 1024 / 1024, 2) . " (MB), peak memory " . round(memory_get_peak_usage() / 1024 / 1024, 2) . " (MB)\n";
                $result = $import->import($document);
                if ($result['result'] === 'success') {
                    #$this->docStack[]['document'] = $result['document'];
                    echo "Successfully imported old ID " . $result['oldid'] . "\n";
                    $import->log("Successfully imported old ID " . $result['oldid'] . "\n");
                    $successCount++;
                } else if ($result['result'] === 'failure') {
                    echo "ERROR: " . $result['message'] . " for old ID " . $result['oldid'] . "\n";
                    $import->log("ERROR: " . $result['message'] . " for old ID " . $result['oldid'] . "\n");
                    fputs($f, $result['entry'] . "\n");
                    $failureCount++;
                }
                unset($result);
                unset($document);
                flush();
            }
            fclose($f);
            $import->finalize();
            echo "Imported " . $successCount . " documents successfully.\n";
            echo $failureCount . " documents have not been imported due to failures listed above. See $logfile for details about failed entries.\n";
        }
        // if no metadata is imported use now the metadata already stored in database
        #if ($metadatainput !== 'y' && $metadatainput !== 'yes') {
        #	$this->readDocsFromDatabase();
        #}
        // Import files
        $fileinput = readline('Do you want to import the files of all documents from OPUS3? Note: this script needs to have direct physical reading access to the files in your OPUS3 directory tree! Import via HTTP is not possible! (y/n) ');
        if ($fileinput === 'y' || $fileinput === 'yes') {
            do {
                $fulltextPath = $this->path;
                while (false === file_exists($fulltextPath)) {
                    $fulltextPath = readline('Please type the path to your OPUS3 fulltext files (e.g. /usr/local/opus/htdocs/volltexte): ');
                }
                echo "Please specify the access rights for this fulltext path (Opus3 ranges)!\n";
                $ipStart = readline('The IP-range starts at (e.g. 192.168.1.1): ');
                $ipEnd = readline('The IP-range ends at (e.g. 192.168.1.10): ');
                $this->path = $fulltextPath;
                $this->importFiles($ipStart, $ipEnd);
                $anotherPath = readline('Do you want to enter another fulltext path for files from another Opus3-area? (y/n) ');
                $this->path = '';
            } while ($anotherPath === 'y');
        }

        // Import signatures
        $siginput = readline('If you used signatures (GPG-Extension) in OPUS 3.x, do you want the signatures to be imported? (y/n) ');
        if ($siginput === 'y' || $siginput === 'yes') {
            $signaturePath = '';
            while (false === file_exists($signaturePath)) {
                $signaturePath = readline('Please type the path to your OPUS3 signature files (e.g. /usr/local/opus/htdocs/signatures): ');
            }
            $this->signaturePath = $signaturePath;
            $this->importSignatures();
        }

        // Signing publications is only possible if files have been imported
        if ($fileinput === 'y' || $fileinput === 'yes') {
            $newsiginput = readline('Do you want all files to get signed automatically? (You need to have an internal key already) (y/n) ');
            if ($newsiginput === 'y' || $newsiginput === 'yes') {
                $newsigpass = readline('Please type the password for your signature key: ');
                echo "Signing publications ";
                $this->autosign($newsigpass);
                echo "finished!\n";
            }
        }

        //$this->cleanup();
    }

}