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
    protected $magicPath = '/usr/share/file/magic'; # on Ubuntu-Systems this is the magic path

    public function setImportfile($importfile) {
    	$this->importfile = $importfile;
    }
    
    public function setFulltextPath($path) {
    	$this->path = $path;
    }

    public function setMagicPath($path) {
    	$this->magicPath = $path;
    }
    
    public function setFormat($format) {
    	$this->format = $format;
    }
    
    /**
     * Starts an Opus console.
     *
     * @return void
     */
    public function _run() {
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
		
        $importFilePath = $this->importfile;
        while (false === file_exists($importFilePath)) {
    		$importFilePath = readline('Please type the path to your OPUS3 database export file (a dumpfile of the database in XML format e.g. /usr/local/opus/complete_database.xml): '); 
		}
		$this->importfile = $importFilePath;
		$importData = new DOMDocument;
		$importData->load($this->importfile);
		
		// Import classification systems and classes 
		$input = readline('Do you want to import all the classifications from OPUS3? Note: Only BK, APA, CCS, MSC and PACS are supported and detected automatically! (y/n) ');
		if ($input === 'y' || $input === 'yes') {
		    $importCollections = new CollectionsImport($importData);
		}
		
		// Import Licences
		$licenceinput = readline('Do you want to import the licences from OPUS3? (y/n) ');
		if ($licenceinput === 'y' || $licenceinput === 'yes') {
		    $importLicences = new LicenceImport($importData);
		}
		
		// Import documents
		$metadatainput = readline('Do you want to import the metadata of all documents from OPUS3? (y/n) ');
		if ($metadatainput === 'y' || $metadatainput === 'yes') {
		    $import = new XMLImport($xslt, $stylesheetPath);
		    $result = $import->import($importData);
		}
		// if no metadata is imported use now the metadata already stored in database
   		if ($metadatainput !== 'y' && $metadatainput !== 'yes') {
    	    echo "Reading existing metadata from database, this could take some time";
    	    $result['success'] = array();
    		$docList = Opus_Document::getAllIds();
    		foreach ($docList as $id) {
    			$result['success'][]['document'] = new Opus_Document($id);
    			echo ".";
    		}
    		echo "finished!\n";
    	}
		
		// Import files
		$fileinput = readline('Do you want to import the files of all documents from OPUS3? Note: this script needs to have direct physical reading access to the files in your OPUS3 directory tree! Import via HTTP is not possible! (y/n) ');
		if ($fileinput === 'y' || $fileinput === 'yes') {
            $fulltextPath = $this->path;
            while (false === file_exists($fulltextPath)) {
    		    $fulltextPath = readline('Please type the path to your OPUS3 fulltext files (e.g. /usr/local/opus/htdocs/volltexte): '); 
		    }
		    $this->path = $fulltextPath;
    		
    		echo "Importing files";
	    	$fileImporter = new Opus3FileImport($this->path, $this->magicPath);
    		foreach ($result['success'] as $imported)
	    	{
	    		echo ".";
			    $opus3Id = $imported['document']->getIdentifierOpus3()->getValue();
			    $documentFiles = $fileImporter->loadFiles($imported['document']);
			    #print_r($documentFiles->toXml()->saveXml());
			    $documentFiles->store();
			    echo count($imported['document']->getField('File')->getValue()) . " file(s) have been imported successfully for document ID " . $imported['document']->getId() . "!\n";
		    }
		    echo "finished!\n";
		}
		
		// Import signatures
		// not yet implemented (class is only a copy of Opus3FileImport)
		// TODO: implement it ;-)
		#$siginput = readline('If you used signatures in OPUS 3.x, do you want the signatures to be imported? (y/n) ');
		$siginput = '';
		if ($siginput === 'y' || $siginput === 'yes') {
            $signaturePath = '';
            while (false === file_exists($signaturePath)) {
    		    $signaturePath = readline('Please type the path to your OPUS3 signature files (e.g. /usr/local/opus/htdocs/signatures): '); 
		    }
    		
	    	$sigImporter = new Opus3SignatureImport($signaturePath);
    		foreach ($result['success'] as $imported)
	    	{
			    $opus3Id = $imported['document']->getIdentifierOpus3()->getValue();
			    $documentSignatures = $sigImporter->loadFiles($imported['document']);
			    #print_r($documentFiles->toXml()->saveXml());
			    $documentSignatures->store();
		    }
		}

		$newsiginput = readline('Do you want all files to get signed automatically? (You need to have an internal key already) (y/n) ');
		if ($newsiginput === 'y' || $newsiginput === 'yes') {
			$gpg = new OpusGPG();
			$newsigpass = readline('Please type the password for your signature key: ');
			echo "Signing publications ";
    		foreach ($result['success'] as $imported)
	    	{
	    		$doc = $imported['document'];
    	        foreach ($doc->getFile() as $file) 
    	        {
    	        	$gpg->signPublicationFile($file, $newsigpass);
    	        	echo ".";
    	        }
		    }
		    echo "finished!\n";
		}

		// cleaning: remove licence mapping file
		if ($licenceinput === 'y' || $licenceinput === 'yes') {
		    unlink('../workspace/licenseMapping.txt');
		}
    }
}

// Start migration
$import = new Opus3Migration;
if ($argc >= 2) $import->setImportfile($argv[1]);
if ($argc >= 3) $import->setFulltextPath($argv[2]);
if ($argc >= 4) $import->setMagicPath($argv[3]);
if ($argc === 5) $import->setFormat($argv[4]);
$import->run(dirname(dirname(__FILE__)), Opus_Bootstrap_Base::CONFIG_TEST,
    dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'config');