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
 * @author      Thoralf Klein <thoralf.klein@zib.de>
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
	 $base_url = "http://maiwald.zib.de/opus4-devel/publicationList/index/index/id/";

	 $languages = array('de', 'eng');
	 foreach ($languages as $lang) {
            $filepath = "/home/gunar/opus4/publicationlists/".$lang."/abteilungen/";
            $role = Opus_CollectionRole::fetchByName('institutes');
            $colls = Opus_Collection::fetchCollectionsByRoleId($role->getId());
            foreach ($colls as $c) {
                if (count($c->getDocumentIds()) == 0) { continue; }
                $url = $base_url.$c->getId()."/theme/plain/lang/".$lang;
                $file = $filepath.preg_replace('/\s.*/', '', $c->getName()).".html";
                echo $file."\n";
                $f = fopen($file, 'w');
                $request = fopen($url,'rb');
                $content = stream_get_contents($request);
                fputs($f, $content . "\n");
                fclose($request);
                fclose($f);

            }

            $filepath = "/home/gunar/opus4/publicationlists/".$lang."/mitarbeiter/";
            $role = Opus_CollectionRole::fetchByName('collections');
            $colls = Opus_Collection::fetchCollectionsByRoleId($role->getId());
            foreach ($colls as $c) {
                if (count($c->getDocumentIds()) == 0) { continue; }
                $url = $base_url.$c->getId()."/theme/plain/lang/".$lang;
                $file = $filepath.$c->getNumber().".html";
                echo $file."\n";
                $f = fopen($file, 'w');
                $request = fopen($url,'rb');
                $content = stream_get_contents($request);
                fputs($f, $content . "\n");
                fclose($request);
                fclose($f);

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
 * Parse options.
 */
$short_options = "";
$long_options = array(
    "dumps-dir:",
    "files-dir:",
);
$options = getopt("", $long_options);

if (empty($options)) {
    echo "required options: --files-dir=./files/ --dumps-dir=./dumps/ \n";
}

/**
 * Run import script.
 */
$migrate = new ZIBPublicationLists();
$migrate->run();
