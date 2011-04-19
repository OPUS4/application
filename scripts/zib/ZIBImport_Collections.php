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
require_once 'simplehtmldom/simple_html_dom.php';

class ZIBImport_Collections {

    protected $projects = array();

    protected $persons = array();

    // Create Collections
    public function create_collections() {
        $roles = array(
            "Mitarbeiter" => array("name" => "persons", "position" => 10),
            "Projekte" => array("name" => "projects", "position" => 11)
        );

        foreach ($roles as $r) {
            $role = new Opus_CollectionRole();
            $role->setName($r["name"]);
            $role->setOaiName($r["name"]);
            $role->setPosition($r["position"]);
            $role->setVisible(1);
            $role->setVisibleBrowsingStart(1);
            $role->setDisplayBrowsing('Name');
            $role->setVisibleFrontdoor(1);
            $role->setDisplayFrontdoor('Name');
            $role->setVisibleOai(1);
            $role->setDisplayOai('Name');
            $role->store();

            $root = $role->addRootCollection()->setVisible(1);
            $root->store();
        }

    }


    // Create Collections for Persons
    public function get_persons($url = null) {

        $html = file_get_html($url);

        foreach($html->find('tr') as $tr) {
            //if ($tr->find('td[class=linie]',0)) {
            if ($tr->find('td',1)) {
                if ($tr->find('td[class="aussen"]')) { continue; }

                $abteilung = trim($tr->find('td',2)->find('a',0)->plaintext);
                /*
                if ($abteilung === 'IT-Service') { continue; }
                if ($abteilung === 'Supercomputing') { continue; }
                if ($abteilung === 'Verwaltung') { continue; }
                */
                $name = trim($tr->find('td',1)->find('a',0)->plaintext);

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

                $identifier = trim($tr->find('td',1)->find('a',0)->href);
                $identifier = preg_replace('/.*member\.html\?mail=/','',$identifier);
                $identifier = str_replace("&amp;L=0","",$identifier);

                $person = array($abteilung, $name, $identifier);
                array_push($this->persons, $person);
            }
        }
    }

    // Create Collections for Projects
    public function import_persons() {
        $role = Opus_CollectionRole::fetchByName('persons');
        $root = $role->getRootCollection();

        foreach ($this->persons as $p) {
            $colls = Opus_Collection::fetchCollectionsByRoleId($role->getId());
            $coll = null;

            foreach ($colls as $c) {
                 if ($c->getName() === $p[0]) {
                     $coll = $c;
                 }
            }

            if (is_null($coll)) {
                $coll = $root->addLastChild();
                $coll->setVisible(1);
                $coll->setName($p[0]);
                $root->store();
            }

            $this->addSubCollection($coll, $p[1], 0, $p[2]);


        }
    }

    // Create Collections for Projects
    public function import_workinggroups() {

        $role = Opus_CollectionRole::fetchByName('institutes');
        $colls = Opus_Collection::fetchCollectionsByRoleId($role->getId());
        
        foreach ($colls as $c) {
        
            if ($c->getName() === 'Numerische Analysis und Modellierung') {
                $c->setNumber('Numerik');
                $this->addSubCollection($c, 'Virtuelle Medizin', 1, 'CompMedicine');
                $this->addSubCollection($c, 'Mathematischer Molekülentwurf', 1, 'DrugDesign');
                $this->addSubCollection($c, 'Mathematische Nano-Optik', 1, 'NanoOptik');
                $this->addSubCollection($c, 'Mathematische Systembiologie', 1, 'CompSysBio');
            }
            else if ($c->getName() === 'Visualisierung und Datenanalyse') {
                $c->setNumber('visual');
                $this->addSubCollection($c, 'Visualisierungsalgorithmen', 1 ,'scivis');
                $this->addSubCollection($c, 'Visualisierungssysteme', 1 , 'systems');
                $this->addSubCollection($c, 'Medizinische Planung', 1 , 'medical');
                $this->addSubCollection($c, 'Vergleichende Visualisierung', 1 , 'compvis');
                $this->addSubCollection($c, 'Mathematical Geometry Processing', 1 , 'geom');
            }
            
            else if ($c->getName() === 'Optimierung') {
                $c->setNumber('opt');
                $this->addSubCollection($c, 'Lineare und Nichtlineare Ganzzahlige Optimierung', 1, 'mip');
                $this->addSubCollection($c, 'Telekommunikation', 1 ,'tele');
                $this->addSubCollection($c, 'Verkehr und Logistik', 1, 'traffic');
            } 
          
            else if ($c->getName() === 'Wissenschaftliche Informationssysteme') {
                $c->setNumber('sis');
                $this->addSubCollection($c, 'Information und Kommunikation', 1);
                $this->addSubCollection($c, 'KOBV', 1);
                $this->addSubCollection($c, 'Museums-Software', 1);
            }      
            
            else if ($c->getName() === 'Parallele und Verteilte Algorithmen') {
                $c->setNumber('parallel');
                $this->addSubCollection($c, 'Verteiltes Datenmanagement', 1);
                $this->addSubCollection($c, 'Skalierbare Algorithmen', 1);
                $this->addSubCollection($c, 'Hardwarenahe Algorithmen', 1);
            }   
            /*
            else if ($c->getName() === 'Computer Science Research') {
                $this->addSubCollection($c, 'Verteiltes Datenmanagement');
                $this->addSubCollection($c, 'Skalierbare Algorithmen');
                $this->addSubCollection($c, 'Hardwarenahe Algorithmen');
            }
             *
             */
        }
    }
    public function get_projects($url = null) {
        $html = file_get_html($url);

        echo "Get Projects from '".$url."'\n";

        foreach($html->find('div[class=news-list-item]') as $nli) {
            $name = null;
            $identifier = null;

            if ($nli->find('p',1)) {
                $name = trim($nli->find('p',1)->plaintext);
            }

            if ($nli->find('h3',0)->find('a',0)) {
                $identifier = trim($nli->find('h3',0)->find('a',0)->plaintext);
            }

            if (!is_null($identifier) && !is_null($name)) {
                array_push($this->projects, $identifier);
            }
        }


    }

    // Create Collections for Projects
    public function import_projects() {
        $role = Opus_CollectionRole::fetchByName('projects');
        $root = $role->getRootCollection();

        //sort($this->projects);
        $sorted = array_unique($this->projects);
        sort($sorted);
        foreach ($sorted as $p) {
            $this->addSubCollection($root, $p, 0);
        }
    }

   // Add Subcollection to Colelction
   private function addSubCollection($coll, $name, $visible, $number = null) {
        $subcoll = $coll->addLastChild();
        $subcoll->setVisible($visible);
        $subcoll->setName($name);
        if (!is_null($number)) { $subcoll->setNumber($number); }
        //$subcoll->store();
        $coll->store();

        if (!is_null($number)) { echo "Collection in ".$coll->getName()." with Name \"$name\" and identifier \"$number\" created \n"; }
        else { echo "Collection in ".$coll->getName()." with Name \"$name\" created \n"; }

        return $subcoll;
   }


	
   /**
     * Migrates OPUS3 to OPUS4 using readline
     *
     * @return void
     */
    public function run() {
        
        // Create Workinggroup-Collections
        $this->import_workinggroups();

	// Create Person-Project-RootCollections
        $this->create_collections();

        $this->get_persons('http://www.zib.de/de/menschen/mitarbeiter.html');
        $this->import_persons();

	// 94 laufende Projekte
	$this->get_projects("http://www.zib.de/de/projekte/aktuelle-projekte.html");
	$this->get_projects("http://www.zib.de/de/projekte/aktuelle-projekte/browse/1.html");

        // 216 abgeschlossene Projekte
        $this->get_projects("http://www.zib.de/de/projekte/projektarchiv.html");
        $this->get_projects("http://www.zib.de/de/projekte/projektarchiv/browse/1.html");
        $this->get_projects("http://www.zib.de/de/projekte/projektarchiv/browse/2.html");
        $this->get_projects("http://www.zib.de/de/projekte/projektarchiv/browse/3.html");
        $this->get_projects("http://www.zib.de/de/projekte/projektarchiv/browse/4.html");

        $this->import_projects();
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

$import = new ZIBImport_Collections();
$import->run();