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
 * @package     Scripts
 * @author      Gunar Maiwald <maiwald@zib.de>
 * @copyright   Copyright (c) 2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
// Configure include path.
set_include_path('.' . PATH_SEPARATOR
        . PATH_SEPARATOR . dirname(__FILE__)
        . PATH_SEPARATOR . dirname(dirname(__FILE__)) . '/library'
        . PATH_SEPARATOR . get_include_path());

// Define path to application directory
defined('APPLICATION_PATH')
        || define('APPLICATION_PATH', realpath(dirname(dirname(__FILE__))));

define('APPLICATION_ENV', 'testing');

require_once('simplehtmldom/simple_html_dom.php');


class ZIBPublicationLists {
    
    private $timestamp;
    private $base_url = "http://localhost/opus4-zib/publications/index/index/id/";
    private $base_path;
    private $modified_doc_ids = array();
    private $type;	// typo3 or file (reguliert einbindung von headersowie Autorenlinks)
    private $lang; 	// de or eng
    private $header = "<html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" /><link rel=\"stylesheet\" type=\"text/css\" href=\"http://gum.zib.de/opus4-zib/layouts/opus4/css/pl.css\" media=\"all\" /></head><body>";
    private $footer = "</body></html>";
    private $person = array();
     
    /// Parameter theme entf�llt, da OPus -header automatisch entfernt werden kann 
     
    /**
     * Constructur.
     *
     * @param array $options Array with input options.
     */
    function __construct($options) {
        if ((array_key_exists('h', $options) !== false) && (is_numeric($options["h"]))) {
            $this->timestamp = date('U') - ($options["h"]*3600);
            echo "OPTIONS_H:".$options['h']."\n";
        }
        else if ((array_key_exists('d', $options) !== false) && (is_numeric($options["d"]))) {
            $this->timestamp = date('U') - ($options["d"]*3600*24);
            echo "OPTIONS_D:".$options['d']."\n";
        }
        else $this->timestamp = 0;

        echo "TIMESTAMP:".date("d.m.Y H:i",$this->timestamp)."\n";

        if (array_key_exists('p', $options) !== false) { 
            $this->base_path =  rtrim($options["p"],"/") . "/publications_".date("Y_md_His");
            echo "OPTIONS_P:".$options['p']."\n";
	} else {
            throw new Exception("Path is not valid! \n");
        }
	
	echo "BP:".$this->base_path."\n";
	
	if (array_key_exists('t', $options) !== false) { 
            if ($options['t'] === 'typo3') { $this->type = "typo3"; }
            else if ($options['t'] === 'file') { $this->type = "file"; }
	} else {
            $this->type = "file";
	}
	
	echo "TYPE:".$this->type."\n";
	
        $personrole = Opus_CollectionRole::fetchByName('persons');
        if (!is_null($personrole)) { $personcolls = Opus_Collection::fetchCollectionsByRoleId($personrole->getId()); }
	
	foreach ($personcolls as $pc) {
		$this->person[$pc->getId()] = $pc->getNumber();
	}

        /* TIMEOUT */
        ini_set('default_socket_timeout', 120);
    }

    public function run() {
        
        mkdir($this->base_path);
        if ($this->timestamp > 0) {
            $this->modified_doc_ids = $this->getModifiedDocIds();
        }
   
        $roles = array();
        array_push($roles, array('zib', 'collections'));
        array_push($roles, array('abteilungen', 'institutes'));
        array_push($roles, array('mitarbeiter', 'persons'));
        array_push($roles, array('arbeitsgruppen', 'institutes'));
   
        $languages = array();
        array_push($languages, "de");
        array_push($languages, "eng");

        foreach ($languages as $l) {
            if (!file_exists($this->base_path."/".$l)) { mkdir($this->base_path."/".$l); }
            foreach ($roles as $r) {
                if (!file_exists($this->base_path."/".$l."/".$r[0])) { mkdir($this->base_path."/".$l."/".$r[0]); }
                $this->createPublicationLists($r[1], $this->base_path."/".$l."/".$r[0], $l);
            }
        }
    }

    private function createPublicationLists($rolename, $filepath, $language) {

        $colls = array();

        $role = Opus_CollectionRole::fetchByName($rolename);
        if (!is_null($role)) { $colls = Opus_Collection::fetchCollectionsByRoleId($role->getId()); }

        foreach ($colls as $c) {

	    //echo "Collection: '".$role."' '".$c->getName()."'\n";
            $file = null;
	    $content = "";
            $url = $this->base_url.$c->getId()."/lang/".$language;

            /* Filter empty Collections */
            if (is_null($c->getName())) { continue; }
            if (count($c->getDocumentIds()) === 0) {
                //echo "Collection empty '".$role."' '".$c->getName()."'\n";
                continue;
            }

            /* Filter unmodified Collections */
            if ($this->timestamp > 0) {
                $modified = false;
                foreach($c->getDocumentIds() as $doc) {
                    //echo "check id ".$doc["document_id"]."\n";
                    //if (in_array($doc["document_id"], $this->modified_doc_ids)) { $modified = true; }
                    //if (in_array($doc["document_id"], $this->modified_doc_ids)) { $modified = true; }
                   }
                if ($modified === false) {
                    echo "Collection not modified '".$role."' '".$c->getName()."'\n";
                    continue;
                }
            }


            
            if ($rolename === 'institutes' && substr_count($filepath, "abteilungen") > 0) {
                
                //$abteilung = $c->getName();
		//echo "ABTEILUNG:".$c->getName()."\n";
                $name = "";

                if ($c->getNumber() === 'Numerik') {  $name = 'Numerische'; }
                else if ($c->getNumber() === 'visual') {  $name = 'Visualisierung'; }
                else if ($c->getNumber() === 'opt') {  $name = 'Optimierung'; }
                else if ($c->getNumber() === 'sis') {  $name = 'Wissenschaftliche'; }
                else if ($c->getNumber() === 'parallel') {  $name = 'Parallele'; }
                // Computer Science Research && ZIB Allgemeien
                else if (is_null($c->getNumber())) { continue; }
                else { continue; }
                //
                //else { $abteilung = $c->getNumber(); }
                $file = $filepath."/".$name.".html";
                $this->writeContentToFile($url, $file, $language);

            } else if  ($rolename === 'institutes' && substr_count($filepath, "arbeitsgruppen") > 0) {
                $name = "";#
                // Numerik:
                if ($c->getNumber() === 'CompMedicine') {  $name = 'CompMedicine'; }
                else if ($c->getNumber() === 'DrugDesign') {  $name = 'DrugDesign'; }
                else if ($c->getNumber() === 'NanoOptik') {  $name = 'NanoOptik'; }
                else if ($c->getNumber() === 'CompSysBio') {  $name = 'CompSysBio'; }
                // Visualisierung:
                else if ($c->getNumber() === 'scivis') {  $name = 'VisAlgo'; }
                else if ($c->getNumber() === 'systems') {  $name = 'VisSystem'; }
                else if ($c->getNumber() === 'medical') {  $name = 'MedPlan'; }
                else if ($c->getNumber() === 'compvis') {  $name = 'CompVis'; }
                else if ($c->getNumber() === 'geom') {  $name = 'MathGeom'; }
                // Optimierung
                else if ($c->getNumber() === 'mip') {  $name = 'Mip'; }
                else if ($c->getNumber() === 'tele') {  $name = 'Tele'; }
                else if ($c->getNumber() === 'traffic') {  $name = 'Traffic'; }
                else { continue; }

                $file = $filepath."/".$name.".html";
                $this->writeContentToFile($url, $file, $language);

            } else if ($rolename === 'persons') {

                $relevant = false;
                foreach ($c->_fetchParents() as $p) {
                    if ($p->getName() === 'Numerische Analysis und Modellierung') {  $relevant = true;}
                    else if ($p->getName() === 'Visualisierung und Datenanalyse') {  $relevant = true; }
                    else if ($p->getName() === 'Parallele und Verteilte Algorithmen') {  $relevant = true; }
                    else if ($p->getName() === 'Wissenschaftliche Information') {  $relevant = true; }
                    else if ($p->getName() === 'Optimierung') {  $relevant = true; }
                }
		
		if ($c->getNumber() === 'peters-kottig') { $relevant = false; }

                if (is_null($c->getNumber())) { continue; }
                if ($relevant === false) {
                    //echo "Collection not relevant '".$role."' '".$c->getName()."'\n";
                    continue;
                }
                
                $file = $filepath."/".$c->getNumber().".html";
                $this->writeContentToFile($url, $file, $language);

            } else if ($rolename === 'collections') {

                if ($c->getName() === 'Jahresbericht') { continue; }
                else if ($c->getName() === 'Dissertationen') { continue; }
                else if ($c->getName() === 'Studienabschlussarbeiten') { continue; }

                for ($year = 2000; $year <= 2011; $year++) {
                    $file = $filepath."/reportzib_".$year.".html";
                    $url2 = $url."/year/" .$year. "/doctype/reportzib";
                    $this->writeContentToFile($url2, $file, $language);
                }
            }
         }
    }
 
    private function getModifiedDocIds() {
        echo "Check modified Documents \n";
        $modified_doc_ids = array();
        foreach (Opus_Document::getAllIds() as $id) {
            //echo "chec id".$id."\n";
            $doc = new Opus_Document($id);
            if (strtotime($doc->getServerDateModified()) > $this->timestamp) {
                array_push($modified_doc_ids, $id);
                
            }
        }
        return $modified_doc_ids;
    }
    
    private function writeContentToFile($url, $file, $lang) {

            $mem_now = round(memory_get_usage() / 1024 / 1024);
            $mem_peak = round(memory_get_peak_usage() / 1024 / 1024);

            echo $file. " -- memory $mem_now MB, peak memory $mem_peak (MB)\n";
            echo $url. "\n";

	    $html = file_get_html($url);
/*
            $f = fopen($file.".sav", 'w+');
            fputs($f, $html . "\n");
            fclose($f);
*/
            $publications = $html->find('#publication_lists',0);
            $first = $publications->find('.first');



            foreach ($first as $f) {
                $links = $f->find('a');
                foreach ($links as $l) {
                    $id = preg_replace('/\/opus4-zib\/publications\/index\/index\/id\//', '', $l->href);
                    if ($this->type === 'file') {
                        $l->href = "../mitarbeiter/".$this->person[$id].".html";
                    }

                    if ($this->type === 'typo3') {
                        if ($lang === 'de') {
                            $l->href =  "http://www.zib.de/de/menschen/mitarbeiter/member.html?mail=".$this->person[$id];
                        }
                        if ($lang === 'eng') {
                            $l->href =  "http://www.zib.de/en/people/staff/member.html?mail=".$this->person[$id];
                        }
                    }
                }
            }


	   $content = $publications;

           if ($this->type === "file") {
                $content = $this->header.$content;
                $content = $content.$this->footer;
           }


           $f = fopen($file, 'w+');
           fputs($f, $content . "\n");
           fclose($f);


           $html->clear();
           
           unset ($content);
	   unset ($html);
	   unset ($f);
      }
}


/**
 * Bootstrap application.
 */
require_once 'Zend/Application.php';
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


$options = getopt("h:d:p:t:s");
//$migrate = new ZIBPublicationLists($options);



/**
 * Run import script.
 */

try {
    $migrate = new ZIBPublicationLists($options);
    $migrate->run();
} catch (Exception $e) {
    echo "ERROR: ". $e->getMessage() . "\n";
}
