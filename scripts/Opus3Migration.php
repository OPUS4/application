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
 * @copyright   Copyright (c) 2009, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
// Configure include path.
set_include_path('.' . PATH_SEPARATOR
            . PATH_SEPARATOR . dirname(__FILE__)
            . PATH_SEPARATOR . dirname(dirname(__FILE__)) . '/library'
            . PATH_SEPARATOR . dirname(dirname(__FILE__)) . '/modules/import/models'
            . PATH_SEPARATOR . dirname(dirname(__FILE__)) . '/modules/pkm/models'
            . PATH_SEPARATOR . get_include_path());

// Zend_Loader is'nt available yet. We have to do a require_once
// in order to find the bootstrap class.
require_once 'Application/Bootstrap.php';

/**
 * Bootstraps and runs an import from Opus3
 *
 * @category    Import
 */
class Opus3Migration extends Application_Bootstrap {

    protected $importfile;
    protected $path;
    protected $format = 'mysqldump';
    protected $magicPath = '/usr/share/file/magic'; # on Ubuntu-Systems this should be the magic path
    protected $stylesheet;
    protected $xslt;
    protected $docStack = array();
    protected $signaturePath;
    protected $signaturePassword;
    protected $startAtId = null;
    protected $stopAtId = null;

    protected function setStylesheet() {
    	$stylesheetPath = '../modules/import/views/scripts/opus3';
		$stylesheet = $this->format;
    	// Set the stylesheet to use for XML-input transformation
    	switch ($stylesheet)
    	{
    		case 'mysqldump':
    			$xslt = 'opus3.xslt';
    			break;
    		case 'phpmyadmin':
                $xslt = 'opus3.phpmyadmin.xslt';
                break;
    		default:
    			$xslt = 'opus3.xslt';
    			break;
    	}
    	$this->stylesheet = $stylesheetPath;
    	$this->xslt = $xslt;
    }

    protected function loadImportFile() {
		$importData = new DOMDocument;
		$importData->load($this->importfile);
		return $importData;
    }

    protected function autosign($pass) {
       	$gpg = new Opus_GPG();
   		$docList = Opus_Document::getAllIds();
   		foreach ($docList as $id)
    	{
    		$doc = new Opus_Document($id);
    	    foreach ($doc->getFile() as $file)
    	    {
    	      	echo "Signing " . $file->getPathName();
    	      	try {
    	      	    $gpg->signPublicationFile($file, $pass);
    	      	    echo "... done!\n";
    	      	}
    	       	catch (Exception $e) {
    	       		echo $e->getMessage();
    	       	}
    	    }
		}
    }

    protected function readDocsFromDatabase() {
    	    echo "Reading existing metadata from database, this could take some time";
    		$docList = Opus_Document::getAllIds();
    		foreach ($docList as $id) {
    			$this->docStack[]['document'] = new Opus_Document($id);
    			echo ".";
    		}
    		echo "finished!\n";
    }

    protected function importFiles($ipstart, $ipend) {
    		echo "Importing files\n";
    		$iprange = null;
    		$rolename = null;
    		$accessRole = null;
    		// create IP-range for these documents
    		if (empty($ipstart) !== true) {
    			$rolename = $ipstart;
    			if (empty($ipend) === true) {
    				$ipend = $ipstart;
    			}
    			else {
    				$rolename .= '-' . $ipend;
    			}
    		    $iprange = new Opus_Iprange();
    		    $ip1 = explode(".", $ipstart);
    		    $ip2 = explode(".", $ipend);
    		    $iprange->setIp1byte1($ip1[0])->setIp1byte2($ip1[1])->setIp1byte3($ip1[2])->setIp1byte4($ip1[3]);
    		    $iprange->setIp2byte1($ip2[0])->setIp2byte2($ip2[1])->setIp2byte3($ip2[2])->setIp2byte4($ip2[3]);
    		    $iprange->setName('IP-' . $rolename);
    		    $ipid = $iprange->store();
    		}
    		
    		if (empty($iprange) === false) {
        		$accessRole = new Opus_Role();
        		$accessRole->setName('IP-' . $rolename);
    	    	$accessRole->store();
    		    $iprange->addRole($accessRole);
    		    $iprange->store();
    		}
    		else {
        		$guestId = 0;
        		// open document to the great bad internet
        		// by assigning it to guest role
        		$roles = Opus_Role::getAll();
        		foreach ($roles as $role) {
        			if ($role->getDisplayName() === 'guest') {
        				$guestId = $role->getId();
        			}
        		}
    	    	if ($guestId > 0) {
    	    	    $accessRole = new Opus_Role($guestId);
    	    	}
        		if ($accessRole === null) {
        			echo "Warning: no guest user has been found in database! Documents without IP-range will be imported without access permissions, so only the admin can view them!";
    	    	}
    		}
    		
	    	$fileImporter = new Opus3FileImport($this->path, $this->magicPath, $accessRole);
    		$docList = Opus_Document::getAllIds();
    		foreach ($docList as $id)
	    	{
	    		$doc = new Opus_Document($id);
			    $opus3Id = $doc->getIdentifierOpus3()->getValue();
			    $documentFiles = $fileImporter->loadFiles($doc);
			    if ($documentFiles !== false) {
			        $documentFiles->store();
			        echo count($doc->getFile()) . " file(s) have been imported successfully for document ID " . $doc->getId() . "!\n";
			    }
			    unset($doc);
			    unset($documentFiles);
		    }
		    echo "finished!\n";
    }

    protected function importSignatures() {
    		echo "Importing signatures\n";
	    	$sigImporter = new Opus3SignatureImport($this->signaturePath);
    		$docList = Opus_Document::getAllIds();
    		foreach ($docList as $id)
	    	{
	    		$doc = new Opus_Document($id);
	    		echo ".";
			    $sigImporter->loadSignatureFiles($doc->getId());
			    unset($doc);
		    }
		    echo "finished!";
    }

    /**
     * Removes all Mapping files needed for Import
     */
    protected function cleanup() {
    	$filereader = opendir('../workspace/tmp/');
    	while (false !== ($file = readdir($filereader))) {
    		if (substr($file, -4) === '.map') {
    			unlink('../workspace/tmp/' . $file);
    		}
    	}
    }
}

class Opus3MigrationParameters extends Opus3Migration
{
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
    	for ($n = count($argv)-1; $n > 0; $n--) {
    		// The first non-integer argument is the filename
    		if (false === is_numeric($argv[$n])) {
    			$importFilePath = $argv[$n];
    	        if (array_key_exists($n+1, $argv)) $this->startAtId = $argv[$n+1];
    	        if (array_key_exists($n+2, $argv)) $this->stopAtId = $argv[$n+2];
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

		if ($failure === false) return true;
		return false;
    }

    /**
     * Migrates OPUS3 to OPUS4 using commandline parameters
     *
     * @return void
     */
    public function _run() {
    	$this->setStylesheet();

		$importData = $this->loadImportFile();

		// Import classification systems and classes
		if (true === in_array('collections', $this->whatToDo)) {
		    $importCollections = new CollectionsImport($importData);
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
		if (true === in_array('metadata', $this->whatToDo)) {
		    $import = new XMLImport($this->xslt, $this->stylesheet);
		    $toImport = $import->initImportFile($importData);
		    $logfile = '../workspace/tmp/importerrors.xml';
		    $f = fopen($logfile, 'w');
		    $successCount = 0;
		    $failureCount = 0;
		    $counter = 0;
		    foreach ($toImport as $document) {
		        if ($this->startAtId === null || ($this->startAtId !== null && $counter >= $this->startAtId)) {
		            $result = $import->import($document);
		            if ($result['result'] === 'success') {
		                #$this->docStack[]['document'] = $result['document'];
                        echo "Successfully imported old ID " . $result['oldid'] . "\n";
                        $import->log("Successfully imported old ID " . $result['oldid'] . "\n");
                        $successCount++;
		            }
		            else if ($result['result'] === 'failure') {
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
		    	die('Aborted execution, please proceed import at position ' . $counter+1);
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
		}
		else if (true === in_array('autosign', $this->whatToDo) && false === in_array('files', $this->whatToDo)) {
			echo "You have to specify --with-files=<path-to-opus3-files> if you want to sign the files automatically!\n";
		}

		//$this->cleanup();
    }
}

class Opus3MigrationReadline extends Opus3Migration {
    /**
     * Migrates OPUS3 to OPUS4 using readline
     *
     * @return void
     */
    public function _run() {
    	$this->setStylesheet();
        $importFilePath = $this->importfile;
        while (false === file_exists($importFilePath)) {
    		$importFilePath = readline('Please type the path to your OPUS3 database export file (a dumpfile of the database in XML format e.g. /usr/local/opus/complete_database.xml): ');
		}
		$this->importfile = $importFilePath;

		$importData = $this->loadImportFile();

		// Import collections and series
		$collectionsinput = readline('Do you want to import all the collections from OPUS3? (y/n) ');
		if ($collectionsinput === 'y' || $collectionsinput === 'yes') {
		    $importCollections = new CollectionsImport($importData);
		}
		unset($importCollections);

		// Import faculties and institutes
		$input = readline('Do you want to import all the faculties and institutes from OPUS3? (y/n) ');
		if ($input === 'y' || $input === 'yes') {
		    $importInstitutes = new InstituteImport($importData);
		}
		unset($importInstitutes);

		// Import Licences
		$licenceinput = readline('Do you want to import the licences from OPUS3? (y/n) ');
		if ($licenceinput === 'y' || $licenceinput === 'yes') {
		    $importLicences = new LicenceImport($importData);
		}
		unset($importLicences);

		// Import documents
		$metadatainput = readline('Do you want to import the metadata of all documents from OPUS3? (y/n) ');
		if ($metadatainput === 'y' || $metadatainput === 'yes') {
		    $import = new XMLImport($this->xslt, $this->stylesheet);
		    $toImport = $import->initImportFile($importData);
		    $logfile = '../workspace/tmp/importerrors.xml';
		    $f = fopen($logfile, 'w');
		    $successCount = 0;
		    $failureCount = 0;
		    foreach ($toImport as $document) {
		        echo "Memory amount: " . round(memory_get_usage()/1024/1024, 2) . " (MB)\n";
		        $result = $import->import($document);
		        if ($result['result'] === 'success') {
		            #$this->docStack[]['document'] = $result['document'];
                    echo "Successfully imported old ID " . $result['oldid'] . "\n";
                    $import->log("Successfully imported old ID " . $result['oldid'] . "\n");
                    $successCount++;
		        }
		        else if ($result['result'] === 'failure') {
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
            }
            while ($anotherPath === 'y');
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

// Start migration
if ($argc === 1) {
    $import = new Opus3MigrationReadline;
}
else {
	$import = new Opus3MigrationParameters;
	$analyse = $import->analyseParameters($argv);
	if ($analyse === false)
	{
		echo "There is at least one failure in the paramters - aborting\n";
		exit;
	}
}
$import->run(dirname(dirname(__FILE__)), Opus_Bootstrap_Base::CONFIG_TEST,
    dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'config');