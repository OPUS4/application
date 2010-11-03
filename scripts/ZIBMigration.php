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
 * @version     $Id: Opus3Migration.php 5890 2010-09-26 17:13:48Z tklein $
 */

// Configure include path.
set_include_path('.' . PATH_SEPARATOR
        . PATH_SEPARATOR . dirname(dirname(__FILE__)) . '/import/importer'
        . PATH_SEPARATOR . dirname(dirname(__FILE__)) . '/import/stylesheets'
        . PATH_SEPARATOR . dirname(dirname(__FILE__)) . '/library'
        . PATH_SEPARATOR . dirname(dirname(__FILE__)) . '/modules/import'
        . PATH_SEPARATOR . dirname(dirname(__FILE__)) . '/modules'
        . PATH_SEPARATOR . get_include_path());

// Define path to application directory
defined('APPLICATION_PATH')
       || define('APPLICATION_PATH', realpath(dirname(dirname(__FILE__))));

define('APPLICATION_ENV', 'testing');

require_once 'Zend/Application.php';
require_once 'ZIBMigration_Base.php';
require_once 'ZIBCollectionsImport.php';
require_once 'ZIBInstituteImport.php';
require_once 'ZIBLicenceImport.php';
require_once 'ZIBXMLImport.php';
require_once 'ZIBBibtexImport.php';
require_once 'simplehtmldom/simple_html_dom.php';

class ZIBMigration extends ZIBMigration_Base {

    private $importData;
    private $fileinput;
    private $doclist = array();

    // Set XMl-Dump-Impor
    public function init($file = null) {
        $this->setStylesheet();
        $importFilePath = $this->importfile;
        while (false === file_exists($file)) {
            $file = readline('Please type the path to your OPUS3 database export file (a dumpfile of the database in XML format e.g. /usr/local/opus/complete_database.xml): ');
        }
        $this->importfile = $file;
        $this->importData = $this->loadImportFile();
    }

    // Import collections and series
    public function load_collections() {
        $input = readline('Do you want to import collections and series from OPUS3? (y/n) ');
        if ($input === 'y' || $input === 'yes') {
            $import = new ZIBCollectionsImport($this->importData);
        }

    }

    // Import faculties and institutes
    public function load_institutes() {
        $input = readline('Do you want to import faculties and institutes from OPUS3? (y/n) ');
        if ($input === 'y' || $input === 'yes') {
            $import = new ZIBInstituteImport($this->importData);
        }
    }

    // Import Licences
    public function load_licences() {
        $input = readline('Do you want to import licences from OPUS3? (y/n) ');
        if ($input === 'y' || $input === 'yes') {
            $import= new ZIBLicenceImport($this->importData);
        }
    }

   // Import Documents
    public function load_documents($start = null, $end = null) {
        $input = readline('Do you want to import metadata of documents from OPUS3? (y/n) ');
        if ($input === 'y' || $input === 'yes') {
            $import = new ZIBXMLImport($this->xslt, $this->stylesheet);
            $toImport = $import->initImportFile($this->importData);
            $logfile = '../workspace/tmp/importerrors.xml';

            // TODO: Add error handling to fopen()
            //$f = fopen($logfile, 'w');
            $totalCount = 0;
            $successCount = 0;
            $failureCount = 0;

            foreach ($toImport as $document) {
                //echo "Memory amount: " . round(memory_get_usage() / 1024 / 1024, 2) . " (MB), peak memory " . round(memory_get_peak_usage() / 1024 / 1024, 2) . " (MB)\n";
                $totalCount++;
                if (!(is_null($start)) && ($totalCount < $start)) { continue; }
                if (!(is_null($end)) && ($totalCount > $end)) { break; }

                $result = $import->import($document);
                if ($result['result'] === 'success') {
                    echo "Successfully imported old ID " . $result['oldid'] . " with new ID " . $result['newid'] . "\n";
                    $import->log("Successfully imported old ID " . $result['oldid'] . " with new ID " . $result['newid'] . "\n");
                    array_push($this->doclist, $result['newid']);
                    $successCount++;
                } else if ($result['result'] === 'failure') {
                    echo "ERROR: " . $result['message'] . " for old ID " . $result['oldid'] . "\n";
                    $import->log("ERROR: " . $result['message'] . " for old ID " . $result['oldid'] . "\n");
                    $import->log("ERROR: " .  $result['entry'] . "\n");
                    //fputs($f, $result['entry'] . "\n");
                    $failureCount++;
                }
                flush();
            }
            //fclose($f);
            $import->finalize();
            echo "Imported " . $successCount . " documents successfully.\n";
            echo $failureCount . " documents have not been imported due to failures listed above. See $logfile for details about failed entries.\n";
        }
    }

    // Import Fulltext-Files
    public function load_fulltext($path = null) {
        $this->fileinput = readline('Do you want to import the files from OPUS3? Note: this script needs to have direct physical reading access to the files in your OPUS3 directory tree! Import via HTTP is not possible! (y/n) ');
        $input = null;
        if ($this->fileinput  === 'y' || $this->fileinput  === 'yes') {
            do {
               while (false === file_exists($path)) {
                    $path = readline('Please type the path to your OPUS3 fulltext files (e.g. /usr/local/opus/htdocs/volltexte): ');
                }
                //echo "Please specify the access rights for this fulltext path (Opus3 ranges)!\n";
                //$ipStart = readline('The IP-range starts at (e.g. 192.168.1.1): ');
               // $ipEnd = readline('The IP-range ends at (e.g. 192.168.1.10): ');
                $this->path = $path;
                //$this->importFiles($ipStart, $ipEnd, $this->doclist);
                //$this->importFiles($ipStart, $ipEnd);
                $this->importFiles();
                $input = readline('Do you want to enter another fulltext path for files from another Opus3-area? (y/n) ');
                $path = null;
            } while ($input === 'y');
        }
    }

    // Import signatures
    public function load_signatures() {
        $input = readline('If you used signatures (GPG-Extension) in OPUS 3.x, do you want the signatures to be imported? (y/n) ');
        if ($input === 'y' || $input === 'yes') {
            $signaturePath = '';
            while (false === file_exists($signaturePath)) {
                $signaturePath = readline('Please type the path to your OPUS3 signature files (e.g. /usr/local/opus/htdocs/signatures): ');
            }
            $this->signaturePath = $signaturePath;
            $this->importSignatures();
        }
    }

    // Signing publications is only possible if files have been imported
    public function sign_publications() {
        if ($this->fileinput  === 'y' || $this->fileinput  === 'yes') {
            $input = readline('Do you want files to get signed automatically? (You need to have an internal key already) (y/n) ');
            if ($input === 'y' || $input === 'yes') {
                $newsigpass = readline('Please type the password for your signature key: ');
                echo "Signing publications ";
                $this->autosign($newsigpass);
                echo "finished!\n";
            }
        }
    }

    // Hide some Classifications
    public function hide_classifications($roles) {
        foreach ($roles as $r) {
            $role = Opus_CollectionRole::fetchByName($r);
            $role->setVisible(1);
            //$role->setDisplayBrowsing('Number, Name');
            $role->setVisibleBrowsingStart(0);
            $role->setVisibleFrontdoor(0);
            $role->store();
        }
    }

    // Create Collections for Persons
    public function create_person_collections($url = null) {
        $input = readline('Do you want to create collections for Persons? (y/n) ');
        if ($input === 'y' || $input === 'yes') {
            $html = file_get_html($url);

            $role = new Opus_CollectionRole();
            $role->setName('persons');
            $role->setOaiName('persons');
            $role->setPosition(12);
            $role->setVisible(1);
            $role->setVisibleBrowsingStart(1);
            $role->setDisplayBrowsing('Name');
            $role->setVisibleFrontdoor(1);
            $role->setDisplayFrontdoor('Name');
            $role->setVisibleOai(1);
            $role->setDisplayOai('Name');
            $role->store();

            //$role = Opus_CollectionRole::fetchByName('collections');
            //$role->store();
            $root = $role->addRootCollection()->setVisible(1);
            $root->store();

            foreach($html->find('tr') as $tr) {
        	//if ($tr->find('td[class=linie]',0)) {
		if ($tr->find('td',1)) {
		    if ($tr->find('td[class="aussen"]')) { continue; }

                    $abteilung = $tr->find('td',2)->find('a',0)->plaintext;

                    if ($abteilung === 'IT-Service') { continue; }
                    if ($abteilung === 'Supercomputing') { continue; }
                    if ($abteilung === 'Verwaltung') { continue; }

                    $name = $tr->find('td',1)->find('a',0)->plaintext;

                    $name = str_replace("&auml;","ä",$name);
                    $name = str_replace("&ouml;","ö",$name);
                    $name = str_replace("&uuml;","ü",$name);
                    $name = str_replace("&aacute;","á",$name);
                    $name = str_replace("&eacute;","é",$name);
                    $name = str_replace("&oacute;","ó",$name);
                    $name = str_replace("&szlig;","ß",$name);

                    $name = preg_replace('/Dr\.\-Ing\./', '', $name);
                    $name = preg_replace('/Prof\./', '', $name);
                    $name = preg_replace('/PD/', '', $name);
                    $name = preg_replace('/Dr\./', '', $name);
                    $name = preg_replace('/h\.c\./', '', $name);
                    $name = preg_replace('/mult\./', '', $name);
                    $name = preg_replace('/\s+/', ' ', $name);

                    $identifier = $tr->find('td',1)->find('a',0)->href;
                    $identifier = preg_replace('/.*member\.html\?mail=/','',$identifier);
                    $identifier = str_replace("&amp;L=0","",$identifier);

                    $coll = $root->addLastChild()->setVisible(1);
                    $coll->setName($name);
                    $coll->setNumber($identifier);
                    $root->store();

                    echo "Person-Collection for ".$name." with id ".$identifier." created\n";
		}
            }
       }
    }


    // Create Collections for Projects
    public function create_project_collections($url = null) {
        $input = readline('Do you want to create collections for Projects? (y/n) ');
        if ($input === 'y' || $input === 'yes') {
            $html = file_get_html($url);

            $role = Opus_CollectionRole::fetchByName('projects');
            $root = $role->getRootCollection();
            $number=0;

            foreach($html->find('div[class=news-list-item]') as $nli) {
                if ($nli->find('h3',0)) {
                    if ($nli->find('h3',0)->find('a',0)) {
			$name = $nli->find('h3',0)->find('a',0)->plaintext;
                        $coll = $root->addLastChild();
                        $coll->setVisible(1);
                        $coll->setName($name);
                        //$coll->setNumber($identifier);
                        $root->store();
                        echo "Project-Collection for ".$name." created\n";
                    }
                }
            }

        }
    }

    // Import collections and series
    public function import_bibtex($file = null, $num = null) {
        //$input = readline('Do you want to import bibtex from file? (y/n) ');
        $input = 'y';

        if ($input === 'y' || $input === 'yes') {
        
            //$dedup_input = readline('Do you want to ignore deduplicated documents? (yes/no/new) ');
            $dedup_input = 'yes';

            while (false === file_exists($file)) {
                $file = readline('Please type the path to your Bibtex-File: ');
            }

            $this->stylesheet = '../import/stylesheets';
            $this->xslt = 'zib_bibtex.xslt';
            $this->importfile = $file;
	    
            $importData = $this->loadImportFile();
            $import = new ZIBBibtexImport($this->xslt, $this->stylesheet, $this->importfile);
            $toImport = $import->initImportFile($importData);
            $logfile = '../workspace/tmp/importerrors.xml';

            $totalCount = 0;
            $successCount = 0;
            $failureCount = 0;
            
	    $opus_titles = array();
	    foreach (Opus_Document::getAllIds() as $id) {
		$doc = new Opus_Document($id);
		if (!is_null($doc->getTitleMain())){
                    if (!is_null($doc->getTitleMain(0))) {
			$title = $doc->getTitleMain(0)->getValue();
			array_push($opus_titles, $title);
                    }
		}
	    }


	    foreach ($toImport as $document) {

                $totalCount++;
                $doctitle = null;
                $doctitle = $import->getTitle($document);
                $docid = $import->getId($document);

                if (!(is_null($num)) && ($totalCount > $num)) { break; }
                
                if ($dedup_input === 'new') {

                    $doctitle = strtolower($doctitle);
                    $doctitle = preg_replace('/\s{2,}/',' ', $doctitle);
                    $doctitle = preg_replace('/[^a-zA-Z0-9\s]/', '', $doctitle);
                    $doctitle = trim($doctitle);
	
                    // Semiautomatic Deduplication with Levenstein-Distance
                     if (!is_null($doctitle)) {
	    		$shortest = -1;
			$closest = null;
			
			foreach($opus_titles as $opustitle) {
				
				$opustitle = strtolower($opustitle);
				$opustitle = preg_replace('/\s{2,}/',' ', $opustitle);
				$opustitle = preg_replace('/[^a-zA-Z0-9\s]/', '', $opustitle);
				$opustitle = trim($opustitle);
				
				$lev = levenshtein($doctitle, $opustitle);
				if (($lev <= $shortest) || ($shortest < 0)) {
					$closest  = $opustitle;
					$shortest = $lev;
				}
			}
			
                       
			if (($shortest < 5) && ($shortest > 0)) {
				echo "THIS:".$doctitle."#\n";
				echo "OPUS:".$closest."#\n";
				echo "LEV: ".$shortest."\n";
				$input = readline('Do you want to skip this title ? (y/n) ');
				if ($input === 'y' || $input === 'yes') {
                                    $fp = fopen('../workspace/tmp/bibtexduplicates.map', 'a');
                                    fputs($fp, $docid . "_". $import->getTitle($document) . "\n");
                                    fclose($fp);
                                    echo "SKIP:".$docid."_".$doctitle."\n";
                                    continue;
				}
			}

			if ($shortest == 0) {
				echo "SKIP:".$docid."_".$doctitle."\n";
                                $fp = fopen('../workspace/tmp/bibtexduplicates.map', 'a');
                                fputs($fp, $docid . "_". $import->getTitle($document) . "\n");
                                fclose($fp);
				continue;
			}
                     }

		}
                //echo $docid ."_" . $doctitle."\n";
                if ($dedup_input === 'yes') {
                    $fp = fopen('../workspace/tmp/bibtexduplicates.map', 'r');
                    $duplicate = '';
                    while (! feof ($fp)) {
                        $line= fgets ($fp);
                        
                        if ($line === $docid ."_" . $doctitle."\n") {
                            $duplicate = 'y';
                            //echo $line;
                        }
                    }
                    fclose($fp);

                    if ($duplicate === 'y') {
                        echo "SKIP:".$docid."_".$doctitle."\n";
                        continue;
                    }
                    
                }

               
                $result = $import->import($document);
                if ($result['result'] === 'success') {
                    echo "Successfully imported " . $result['newid'] . " Bibtex-Entry " . $result['oldid'] . " \n";
		    //echo "Successfully imported Bibtex-Entry\n";
		    array_push($opus_titles, $doctitle);
                    $successCount++;
                }
                else if ($result['result'] === 'failure') {
                    echo "Failure while importing Bibtex-Entry " . $result['oldid'] . " :: ";
                    echo $result['message'] . "\n";
                    //echo $result['entry'] . "\n";
                    $failureCount ++;
                }
            }

            echo "Imported " . $successCount . " documents successfully.\n";
            echo $failureCount . " documents have not been imported due to failures listed above. See $logfile for details about failed entries.\n";
        }
    }
    
    
    public function fill_person_collections() {
        $input = readline('Do you want to fill person-collections with documents? (y/n) ');
	if ($input === 'y' || $input === 'yes') {
	    /* Mitarbeiter nutzen die Colelction 'collections' */
            $role = Opus_CollectionRole::fetchByName('persons');
            $colls = Opus_Collection::fetchCollectionsByRoleId($role->getId());
	    
	    foreach (Opus_Document::getAllIds() as $id) {
		//echo "Check Document ".$id."\n";
		$doc = new Opus_Document($id);
		foreach ($colls as $c) { 
				
			$names = explode(", ", $c->getName());
			foreach ($doc->getPersonAuthor() as $author) {
				//echo "Doc-Author#".$author->getLastName()."#\n";
				// Compare Lastname auf Author an Collection 
				if (strcmp($names[0], $author->getLastName()) != 0) { continue; }
				//echo "Check:#".$names[0]."# and #".$author->getLastName()."#\n";
				$firstname = trim(str_replace(".","",$author->getFirstName()));
		
				// Compare Firtname auf Author an Collection 	
				//echo "Check:#".$names[1]."# and #".$firstname."#\n";
				if (stripos($names[1], $firstname) === 0) {
					$doc->addCollection($c);
                                        $doc->store();
                                        echo "Add Document " .$id. " from ".$author->getLastName().",".$author->getFirstName()." to Mitarbeiter-Collection ".$c->getName()."\n";
				}
			}
		}
	   }	
	}
   }
	
   /**
     * Migrates OPUS3 to OPUS4 using readline
     *
     * @return void
     */
    public function run() {

        echo "Start ZIB-Migration\n";

        // Load Opus3-mySQL-XML-dump
        //$this->init();
        $this->init('../../dumps/opus3_zib.new.xml');
        
        // Load Collections
        $this->load_collections();

        // Load Institutes
        $this->load_institutes();

        // Load Institutes
        $this->load_licences();

        // Load Institutes
        $this->load_documents();
        //$this->load_documents(1, 20);

        // Import files
        //$this->load_fulltext();
        $this->load_fulltext('../../volltexte');

        // Import Signatures
        //$this->load_signatures();

        // Sign Files
        //$this->sign_publications();
	
	// Create Person-Collections
	$this->create_person_collections('http://www.zib.de/de/menschen/mitarbeiter.html');

	// Create Project-Collections
	//$this->create_project_collections("http://www.zib.de/de/projekte/aktuelle-projekte.html");
	//$this->create_project_collections("http://www.zib.de/de/projekte/aktuelle-projekte/browse/1.html");

	// Import Bibtex-Files
       /*
        $this->import_bibtex('../../bibtex/Numerische.bib.xml', 20);
	$this->import_bibtex('../../bibtex/Optimierung.bib.xml', 20);
        $this->import_bibtex('../../bibtex/Parallele.bib.xml', 20);
	$this->import_bibtex('../../bibtex/Visualisierung.bib.xml', 20);
*/
         
        $this->import_bibtex('../../bibtex/Numerische.bib.xml');
	$this->import_bibtex('../../bibtex/Optimierung.bib.xml');
        $this->import_bibtex('../../bibtex/Parallele.bib.xml');
	$this->import_bibtex('../../bibtex/Visualisierung.bib.xml');

  

	// Fill Person-Colelctiosn
        $this->fill_person_collections();
        // Be Careful: cleanup will delete all Mapping Files for Institutes, Faculties etc.
        //$this->cleanup();
    }	
	
}

/**
 * Bootstrap application.
 */


$application = new Zend_Application(
    APPLICATION_ENV,
    array(
        "config"=>array(
            APPLICATION_PATH . '/application/configs/application.ini',
            APPLICATION_PATH . '/application/configs/config.ini'
        )
    )
);

$application->bootstrap(array('Configuration', 'Logging', 'Database'));

$import = new ZIBMigration;
$import->run();