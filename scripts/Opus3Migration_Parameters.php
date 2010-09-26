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

class Opus3Migration_Parameters extends Opus3Migration_Base {

    /**
     * Holding a list of evaluated paramters, which specify what exectly the importer should do
     *
     * @var array
     */
    protected $whatToDo = array();

    /**
     * Analyses the parameters given to the script
     */
    public function analyseParameters($argv) {
        $failure = false;

        // The last argument should be the importfile
        if (count($argv) < 2) {
            $failure = true;
            echo "Not enough arguments - please specify at least the importfile.\n";
        }

        if (count($argv) < 2 || true === in_array('--help', $argv) || true === in_array('-h', $argv)) {
            $failure = true;
            echo "Usage: " . $argv[0] . " [options] importfile [start-at-id] [stop-at-id]\n";
            echo "Options:\n";
            echo "--without-collections Do not import collections and series\n";
            echo "--without-institutes Do not import the faculties and institutes\n";
            echo "--without-licences Do not import the licences\n";
            echo "--without-metadata Do not import the metadata of the documents (if you do not import the metadata, the database will be read)\n";
            echo "--with-files=path-to-files Import the files using the given base path of Opus 3 fulltexts\n";
            echo "--with-signatures=path-to-files Import the signatures using the given base path of Opus 3 signatures\n";
            echo "--autosign=password-of-internal-key Sign all files automatically using the internal key and the passphrase given\n";
            echo "--with-magic=path-to-magic-file Use another path for magic file (to avoid problems importing the files). Default value is ' . $this->magicPath . '\n";
        }

        // The last argument should be the importfile
        for ($n = count($argv) - 1; $n > 0; $n--) {
            // The first non-integer argument is the filename
            if (false === is_numeric($argv[$n])) {
                $importFilePath = $argv[$n];
                if (array_key_exists($n + 1, $argv))
                    $this->startAtId = $argv[$n + 1];
                if (array_key_exists($n + 2, $argv))
                    $this->stopAtId = $argv[$n + 2];
                break;
            }
        }

        if (false === file_exists($importFilePath) && $failure !== true) {
            $failure = true;
            echo "The importfile " . $importFilePath . " you specified does not exist!\n";
        }
        $this->importfile = $importFilePath;

        foreach ($argv as $arg) {
            // Import files?
            if ('--with-files' === substr($arg, 0, 12)) {
                $path = split('=', $arg);
                $this->whatToDo[] = "files";
                if ($path[1] === '') {
                    $failure = true;
                    echo "Please specify a fulltextpath by giving --with-files=fulltext-path!\n";
                }
                if (false === file_exists($path[1])) {
                    $failure = true;
                    echo "The fulltext path " . $path[1] . " you specified does not exist!\n";
                }
                $this->path = $path[1];
            }

            // Import signatures?
            if ('--with-signatures' === substr($arg, 0, 17)) {
                $sigpath = split('=', $arg);
                $this->whatToDo[] = "signatures";
                if ($sigpath[1] === '') {
                    $failure = true;
                    echo "Please specify a signaturepath by giving --with-signatures=signature-path!\n";
                }
                if (false === file_exists($sigpath[1])) {
                    $failure = true;
                    echo "The signature path " . $sigpath[1] . " you specified does not exist!\n";
                }
                $this->signaturePath = $sigpath[1];
            }

            // Automatically sign the files?
            if ('--autosign' === substr($arg, 0, 10)) {
                $signpass = split('=', $arg);
                $this->whatToDo[] = "autosign";
                if ($signpass[1] === '') {
                    $failure = true;
                    echo "Please specify your passphrase for the internal key by giving --autosign=passphrase!\n";
                }
                $this->signaturePassword = $signpass[1];
            }

            // Path to magic file
            if ('--with-magic' === substr($arg, 0, 12)) {
                $magic = split('=', $arg);
                $this->magicPath = $magic[1];
            }
        }

        // Analyse the other parameters
        // Import collections and series?
        if (false === in_array("--without-collections", $argv)) {
            $this->whatToDo[] = "collections";
        }

        // Import faculties and instituites?
        if (false === in_array("--without-institutes", $argv)) {
            $this->whatToDo[] = "institutes";
        }

        // Import Licences?
        if (false === in_array("--without-licences", $argv)) {
            $this->whatToDo[] = "licences";
        }

        // Import documents metadata?
        if (false === in_array("--without-metadata", $argv)) {
            $this->whatToDo[] = "metadata";
        }

        if ($failure === false)
            return true;
        return false;
    }

    /**
     * Migrates OPUS3 to OPUS4 using commandline parameters
     *
     * @return void
     */
    public function run() {
        $this->setStylesheet();

        $importData = $this->loadImportFile();

        // Import classification systems and classes
        if (true === in_array('collections', $this->whatToDo)) {
            $importCollections = new Import_Model_CollectionsImport($importData);
        }

        // Import faculties and institutes
        if (true === in_array('institutes', $this->whatToDo)) {
            $importInstitutes = new InstituteImport($importData);
        }

        // Import Licences
        if (true === in_array('licences', $this->whatToDo)) {
            $importLicences = new LicenceImport($importData);
        }

        // Import documents metadata
        // TODO: Remove code duplication @see Opus3MigrationReadline::_run()
        if (true === in_array('metadata', $this->whatToDo)) {
            $import = new XMLImport($this->xslt, $this->stylesheet);
            $toImport = $import->initImportFile($importData);
            $logfile = '../workspace/tmp/importerrors.xml';

            // TODO: Add error handling to fopen()
            $f = fopen($logfile, 'w');

            $successCount = 0;
            $failureCount = 0;
            $counter = 0;
            foreach ($toImport as $document) {

                if ($this->startAtId === null || ($this->startAtId !== null && $counter >= $this->startAtId)) {
                    echo "Memory amount: " . round(memory_get_usage() / 1024 / 1024, 2) . " (MB), peak memory " . round(memory_get_peak_usage() / 1024 / 1024, 2) . " (MB)\n";
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
                }
                if ($this->stopAtId !== null && $counter >= $this->stopAtId) {
                    break;
                }
                $counter++;
            }
            fclose($f);
            $import->finalize();
            echo "Imported " . $successCount . " documents successfully.\n";
            echo $failureCount . " documents have not been imported due to failures listed above. See $logfile for details about failed entries.\n";
            if ($this->stopAtId !== null && $counter >= $this->stopAtId) {
                die('Aborted execution, please proceed import at position ' . $counter + 1);
            }
        }
        // if no metadata is imported use now the metadata already stored in database
        #else {
        #	$this->readDocsFromDatabase();
        #}
        // Import files
        if (true === in_array('files', $this->whatToDo)) {
            $this->importFiles();
        }

        // Import signatures
        if (true === in_array('signatures', $this->whatToDo)) {
            $this->importSignatures();
        }

        if (true === in_array('autosign', $this->whatToDo) && true === in_array('files', $this->whatToDo)) {
            echo "Signing publications ";
            $this->autosign($this->signaturePassword);
            echo "finished!\n";
        } else if (true === in_array('autosign', $this->whatToDo) && false === in_array('files', $this->whatToDo)) {
            echo "You have to specify --with-files=<path-to-opus3-files> if you want to sign the files automatically!\n";
        }

        //$this->cleanup();
    }

}