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

//require_once('MatheonMigration_Base.php');
require_once('simplehtmldom/simple_html_dom.php');


/**
 *
 */
class ZIBPublicationLists {

 	public function run() {

	/**
	 *  INSTITIUTES
	 */
	 $base_url = "http://maiwald.zib.de/opus4-zib2/publicationList/index/index/id/";

	 $languages = array('de', 'eng');
	 foreach ($languages as $lang) {
             /* Abteilungen */
            $filepath = "../../publicationlists/".$lang."/abteilungen/";
            //mkdir($filepath);
            $role = Opus_CollectionRole::fetchByName('institutes');
            $colls = array();
            if (!is_null($role)) {
                $colls = Opus_Collection::fetchCollectionsByRoleId($role->getId());
            }
            foreach ($colls as $c) {
                if ($c->getName() == 'ZIB Allgemein') { continue; }
                if ($c->getName() === 'Computer Science Research') { continue; }

                if (count($c->getDocumentIds()) > 0) {
 

                    $mem_now = round(memory_get_usage() / 1024 / 1024);
                    $mem_peak = round(memory_get_peak_usage() / 1024 / 1024);

                    $url = $base_url.$c->getId()."/theme/plain/lang/".$lang;
                    $file = $filepath.preg_replace('/\s.*/', '', $c->getName()).".html";
                    echo $file." -- memory $mem_now MB, peak memory $mem_peak (MB)\n";
                    $f = fopen($file, 'w+');
                    $request = fopen($url,'rb');
                    $content = stream_get_contents($request);
                    //$content = preg_replace('/\t/', ' ', $content);
                    //$content = preg_replace('/\s{2,}/', ' ', $content);
		    $content = preg_replace('/maiwald\.zib\.de\/opus4-zib2/', 'opus4web.zib.de/opus4-zib', $content);
		    $content = preg_replace('/maiwald\.zib\.de\/documents-opus4zib2/', 'opus4web.zib.de/documents-zib', $content);

                    fputs($f, $content . "\n");
                    fclose($request);
                    fclose($f);
                }

            }

            /* Mitarbeiter */
            $filepath = "../../publicationlists/".$lang."/mitarbeiter/";
            //mkdir($filepath);
            $role = Opus_CollectionRole::fetchByName('persons');
            $colls = array();
            if (!is_null($role)) {
                $colls = Opus_Collection::fetchCollectionsByRoleId($role->getId());
            }
            foreach ($colls as $c) {
                if (count($c->getDocumentIds()) > 0) {

                    $mem_now = round(memory_get_usage() / 1024 / 1024);
                    $mem_peak = round(memory_get_peak_usage() / 1024 / 1024);

                    $url = $base_url.$c->getId()."/theme/plain/lang/".$lang;
                    $file = $filepath.$c->getNumber().".html";
                    echo $file." -- memory $mem_now MB, peak memory $mem_peak (MB)\n";
                    $f = fopen($file, 'w+');
                    $request = fopen($url,'rb');
                    $content = stream_get_contents($request);
		    $content = preg_replace('/maiwald\.zib\.de\/opus4-zib2/', 'opus4web.zib.de/opus4-zib', $content);
		    $content = preg_replace('/maiwald\.zib\.de\/documents-opus4zib2/', 'opus4web.zib.de/documents-zib', $content);
                    //$content = preg_replace('/\t/', ' ', $content);
                    //$content = preg_replace('/\s{2,}/', ' ', $content);
                    fputs($f, $content . "\n");
                    fclose($request);
                    fclose($f);
                }

            }

             /* Schriftenreihen (ZR, TR, Preprints, Jahresbericht) */
            $filepath = "../../publicationlists/".$lang."/zib/";
            //mkdir($filepath);
            $role = Opus_CollectionRole::fetchByName('series');
            $colls = array();
            if (!is_null($role)) {
                $colls = Opus_Collection::fetchCollectionsByRoleId($role->getId());
            }
            foreach ($colls as $c) {
                if (count($c->getDocumentIds()) > 0) {

                    $mem_now = round(memory_get_usage() / 1024 / 1024);
                    $mem_peak = round(memory_get_peak_usage() / 1024 / 1024);

                    $url = $base_url.$c->getId()."/theme/plain/lang/".$lang;
                    $file = $filepath.$c->getName().".html";
                    $file = preg_replace('/\s/', '-', $file);
                    echo $file." -- memory $mem_now MB, peak memory $mem_peak (MB)\n";
                    $f = fopen($file, 'w+');
                    $request = fopen($url,'rb');
                    $content = stream_get_contents($request);
		    $content = preg_replace('/maiwald\.zib\.de\/opus4-zib2/', 'opus4web.zib.de/opus4-zib', $content);
		    $content = preg_replace('/maiwald\.zib\.de\/documents-opus4zib2/', 'opus4web.zib.de/documents-zib', $content);
                    $content = preg_replace('/<h3>/', '<h1> ', $content);
                    $content = preg_replace('/<\/h3>/', '</h1> ', $content);
                    //$content = preg_replace('/\s{2,}/', ' ', $content);
                    fputs($f, $content . "\n");
                    fclose($request);
                    fclose($f);
                }

            }

            /* Sammlungen (Dissertation, Studienabschlussarbeiten) */
            $role = Opus_CollectionRole::fetchByName('collections');
            $colls = array();
            if (!is_null($role)) {
                $colls = Opus_Collection::fetchCollectionsByRoleId($role->getId());
            }
            foreach ($colls as $c) {
                if (count($c->getDocumentIds()) > 0) {

                    $mem_now = round(memory_get_usage() / 1024 / 1024);
                    $mem_peak = round(memory_get_peak_usage() / 1024 / 1024);

                    $url = $base_url.$c->getId()."/theme/plain/lang/".$lang;
                    $file = $filepath.$c->getName().".html";
                    $file = preg_replace('/\s/', '-', $file);
                    echo $file." -- memory $mem_now MB, peak memory $mem_peak (MB)\n";
                    $f = fopen($file, 'w+');
                    $request = fopen($url,'rb');
                    $content = stream_get_contents($request);
		    $content = preg_replace('/maiwald\.zib\.de\/opus4-zib2/', 'opus4web.zib.de/opus4-zib', $content);
		    $content = preg_replace('/maiwald\.zib\.de\/documents-opus4zib2/', 'opus4web.zib.de/documents-zib', $content);
                    $content = preg_replace('/<h3>/', '<h1> ', $content);
                    $content = preg_replace('/<\/h3>/', '</h1> ', $content);
                    //$content = preg_replace('/\t/', ' ', $content);
                    //$content = preg_replace('/\s{2,}/', ' ', $content);
                    fputs($f, $content . "\n");
                    fclose($request);
                    fclose($f);
                }

            }

          }


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

/**
 * Run import script.
 */
$migrate = new ZIBPublicationLists();
$migrate->run();
