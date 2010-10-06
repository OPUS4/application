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

require_once('MatheonMigration_Base.php');

/**
 *
 */
class MatheonMigration_Preprints extends MatheonMigration_Base {

    private $files_dir     = null;
    private $dumps_dir     = null;
    private $workspace_dir = null;

    /**
     * Associative array maps user_ids to Opus_Person objects.
     *
     * @var array
     */
    private $persons = array();

    /**
     * Associative array maps preprint_ids to arrays of Opus_Person.
     *
     * @var array
     */
    private $preprint_authors = array();

    /**
     * Associative array maps preprint_ids to arrays of file-arrays.
     *
     * @var array
     */
    private $preprint_files = array();

    /**
     * Associative array maps preprint_ids to arrays of project-arrays.
     *
     * @var array
     */
    private $preprint_projects = array();

    /**
     * Associative array maps preprint_ids to arrays of institute-arrays.
     *
     * @var array
     */
    private $preprint_institutes = array();

    /**
     * Constructur.
     *
     * @param array $options Array with input options.
     */
    function __construct($options) {
        $this->dumps_dir = $options['dumps-dir'];
        $this->files_dir = $options['files-dir'];

        $config = Zend_Registry::get('Zend_Config');
        $this->workspace_dir = $config->workspacePath;
    }

    /**
     * Parses freeform MSC string and returns associative array with parsing
     * results.  The hash contains three keys:
     *
     * - rest_string: string with all unparsed parts of the input.
     * - mscs: array with all found MSC values.
     * - msc_string_clean: string with clean and trimmed input string.
     *
     * @param string $msc
     * @return array
     */
    public static function parse_msc($msc = '') {

        $msc = str_replace("\n", " ", $msc);
        $msc = str_replace("\r", " ", $msc);
        $msc = str_replace("\t", " ", $msc);
        $msc = str_replace("(IGNORED)", " ", $msc);
        $msc = trim ($msc);
        $msc_string_clean = $msc;

        $mscs = array();
        preg_match_all("/[0-9][0-9]([A-Z-][0-9][0-9]|-XX)/i", $msc, $mscs);
        $mscs = $mscs[0];

        foreach ($mscs AS $m) {
            $msc = str_replace($m, " ", $msc);
        }

        return array(
            'rest_string'       => trim(preg_replace("/(^[ ,;\.]+|[ ,;\.]+$)/", " ", $msc)),
            'mscs'              => $mscs,
            'msc_string_clean'  => $msc_string_clean,
        );
    }

    /**
     * Parses freeform keyword string and returns array with parsing results.
     *
     * @param string $keywords
     * @return array
     */
    public static function parse_keywords($keywords = '') {

        $keywords = str_replace("\n", " ", $keywords);
        $keywords = str_replace("\r", " ", $keywords);
        $keywords = str_replace("\t", " ", $keywords);

        $keyword_list = array();
        foreach (explode(",", $keywords) AS $keyword) {
            $new_keyword = trim($keyword);

            if (!empty($new_keyword)) {
                $keyword_list[] = $new_keyword;
            }
        }

        return $keyword_list;
    }

    /**
     * Loads XML dump of matheon persons and creates Opus_Persons objects.
     * The result will be stored in $this->persons, which is an associative
     * array of (matheon_user_id => value:Opus_Person).
     *
     * @return void
     */
    public function load_preprint_persons() {
        $file = $this->dumps_dir . '/preprint_persons.xml';

        foreach ($this->load_xml_mysqldump($file) AS $person) {
            $opm = new Opus_Person();
            $opm->setAcademicTitle($person['title']);
            $opm->setFirstName($person['first_name']);
            $opm->setLastName($person['last_name']);
            $opm->setEmail($person['email_address']);
            // $opm->store();

            $idlocal = $opm->addIdentifierLocal();
            $idlocal->setValue("maths_id.user_id=" . $person['id']);

            if (array_key_exists($person['id'], $this->persons)) {
                throw new Exception("Person with ID {$person['id']} already exists in hash.");
            }

            $this->persons[$person['id']] = $opm;
        }

        return;
    }

    /**
     * Loads XML dump of matheon authors and creates/updates the Opus_Persons
     * objects from $this->persons.
     *
     * @return void
     */
    public function load_preprint_authors() {
        $file = $this->dumps_dir . '/preprint_authors.xml';

        $matheon_preprint_authors = array();
        foreach ($this->load_xml_mysqldump($file) AS $author) {

            $matheon_author_id = $author['author'];
            $matheon_preprint_id = $author['document'];
            $matheon_user = null;

            $matheon_user_id = $author['user_id'];
            if (false === empty($matheon_user_id)) {
                if (false === array_key_exists($matheon_user_id, $this->persons)) {
                    echo "-- Kein Benutzer gefunden fuer user_id $matheon_user_id (document $matheon_preprint_id)\n";
                    continue;
                }

                $matheon_user = $this->persons[$matheon_user_id];

                if ($author['givenname'] != '' && $matheon_user->getFirstName() !== $author['givenname']) {
                    echo "-- Widerspruechlicher Vorname: user_id $matheon_user_id (document $matheon_preprint_id): " . $matheon_user->getFirstName() . " !== {$author['givenname']}\n";
                }

                if ($author['familyname'] != '' && $matheon_user->getLastName() !== $author['familyname']) {
                    echo "-- Widerspruechlicher Nachname: user_id $matheon_user_id (document $matheon_preprint_id): " . $matheon_user->getLastName() . " !== {$author['familyname']}\n";
                }
            }
            else {
                $opm = new Opus_Person();
                $opm->setFirstName($author['givenname']);
                $opm->setLastName($author['familyname']);

                $idlocal = $opm->addIdentifierLocal();
                $idlocal->setValue("preprint_document_authors.author=" . $author['author']);

                $matheon_user = $opm;
            }

            $firstname = $matheon_user->getFirstName();
            $lastname = $matheon_user->getLastName();

            if (empty($firstname)) {
                // var_dump($author);
                echo "-- Leerer Vorname: $matheon_preprint_id // author $matheon_author_id // user_id $matheon_user_id -- empty FirstName!\n";
                $matheon_user->setFirstName("INVALID");
            }

            if (empty($lastname)) {
                // var_dump($author);
                echo "-- Leerer Nachname: document $matheon_preprint_id // author $matheon_author_id // user_id $matheon_user_id\n";
                $matheon_user->setLastName("INVALID");
            }

            if (!array_key_exists($matheon_preprint_id, $matheon_preprint_authors)) {
                $matheon_preprint_authors[$matheon_preprint_id] = array();
            }
            $matheon_preprint_authors[$matheon_preprint_id][] = $matheon_user;
        }

        $this->preprint_authors = $matheon_preprint_authors;
        return;
    }

    /**
     * Loads XML dump of matheon files and creates files-array by document_id.
     *
     * @return void
     */
    public function load_preprint_files() {
        $file = $this->dumps_dir . '/preprint_files.xml';
        $mysqldump = $this->load_xml_mysqldump($file);
        $this->preprint_files = $this->array2hash($mysqldump, 'table_id');
        return;
    }


    /**
     * Mark all collection roles as invisible.
     *
     * @return void
     */
    public function disable_all_collectionroles() {
        foreach (Opus_CollectionRole::fetchAll() AS $cr) {
            if ($cr->getName() == 'msc') {
                echo "Updating collection {$cr->getDisplayName()}.\n";
                $cr->setVisibleFrontdoor(0);
            }
            else {
                echo "Disabling collection {$cr->getDisplayName()}.\n";
                $cr->setVisible(0);
                $cr->setVisibleBrowsingStart(0);
                $cr->setVisibleFrontdoor(0);
                $cr->setVisibleOai(0);
            }

            $cr->store();
        }
    }
//(2, 21, "Zuse Institute Berlin"),
//(3, 15, "Technische Universität Berlin"),
//(4, 13, "Freie Universität Berlin"),
//(5, 20, "Weierstraß-Institut"),
//(6, 16, "Humboldt-Universität zu Berlin"),
//(7, 23, "DFG Research Center Matheon");

    /**
     * Loads XML dump of matheon projects and creates array by document_id.
     *
     * @return void
     */
    public function load_projects() {
// TODO: Add Unit tests.
//        $role = new Opus_CollectionRole();
//        $role->setName('projects-'.rand());
//        $role->setOaiName('projects-'.rand());
//
//        $root_node = $role->addRootNode();
//        $role_id = $role->store();
//
//        echo "role_id: $role_id\n";
//
//        $app_node = $root_node->addFirstChild();
//        // $app_node->setRoleId( $role_id );
//        $app_node->store();

        $role = Opus_CollectionRole::fetchByName('projects');
        $role->setVisible(1);
        $role->setDisplayBrowsing('Number, Name');
        $role->setVisibleBrowsingStart(1);
        $role->setDisplayFrontdoor('Number, Name');
        $role->setVisibleFrontdoor(1);
        $role->store();

        $root = $role->addRootCollection()->setVisible(1);

        // TODO: write unit test
        $role->store();

        $role->store();

        $collections = array();
        $app_area_collection = array();

        $file = $this->dumps_dir . '/projects.xml';
        foreach ($this->load_xml_mysqldump($file) AS $project) {
            $app_area = $project['app_area'];
            $app_area_name = $project['app_area_name'];
            $app_area_visible = $project['app_area_visible'];

            $project_id = $project['project_id'];
            $project_title = $project['project_title'];

            if (false === array_key_exists($app_area, $app_area_collection)) {
                $app_collection = $root->addLastChild()->setVisible($app_area_visible);
                $app_collection->setNumber($app_area);
                $app_collection->setName($app_area_name);
                $root->store();

                // TODO: Add Unit tests.
                // $app_node->store();

                $app_area_collection[$app_area] = $app_collection;
            }
            $app_collection = $app_area_collection[$app_area];

            if (false === array_key_exists($project_id, $collections)) {
                $project_collection = $app_collection->addLastChild()->setVisible(1);
                $project_collection->setNumber($project_id);
                $project_collection->setName($project_title);
                $project_collection->store();

                $collections[$project_id] = $project_collection;
            }
            else {
                throw new Exception("Collection $project already exists.");
            }
        }

        $file = $this->dumps_dir . '/preprint_projects.xml';
        foreach ($this->load_xml_mysqldump($file) AS $preprint_project) {
            $pid = $preprint_project['id'];
            $project = $preprint_project['project'];

            if (false === array_key_exists($pid, $this->preprint_projects)) {
                $this->preprint_projects[$pid] = array();
            }
            // echo "adding collection for project $project to preprint $pid\n";
            $this->preprint_projects[$pid][] = $collections[$project];
        }

        return;
    }


    /**
     * Loads XML dump of matheon projects and creates array by document_id.
     *
     * @return void
     */
    public function load_institutes() {
        $role = Opus_CollectionRole::fetchByName('institutes');
        $role->setVisible(1);
        $role->setDisplayBrowsing('Name');
        $role->setVisibleBrowsingStart(1);
        $role->setDisplayFrontdoor('Name');
        $role->setVisibleFrontdoor(1);
        $role->store();

        $root = $role->getRootCollection()->setVisible(1);

        // TODO: Write unit test.
        // $root->store();

        $role->store();

        $collections = array();

        $file = $this->dumps_dir . '/institutes.xml';
        foreach ($this->load_xml_mysqldump($file) AS $institute) {
            $institute_id = $institute['institute_id'];
            $institute_key = $institute['institute_key'];
            $institute_name = $institute['institute_name'];

            if (false === array_key_exists($institute_id, $collections)) {
                $institute_collection = $root->addLastChild()->setVisible(1);
                $institute_collection->setName($institute_name)
                                     ->setNumber($institute_key);
                $root->store();

                $collections[$institute_id] = $institute_collection;
            }
        }

        $file = $this->dumps_dir . '/preprint_institutes.xml';
        foreach ($this->load_xml_mysqldump($file) AS $institute_project) {
            $pid = $institute_project['preprint_id'];
            $institute_id = $institute_project['institute_id'];

            if (false === array_key_exists($pid, $this->preprint_projects)) {
                $this->preprint_institutes[$pid] = array();
            }
            // echo "adding collection for project $project to preprint $pid\n";
            $this->preprint_institutes[$pid][] = $collections[$institute_id];
        }

        return;
    }


    /**
     * Custom run method.
     *
     * @return <type>
     */
    public function run() {

        // Load mySQL dump for preprint persons.
        $this->load_preprint_persons();
        echo "found and created " . count($this->persons) . " persons\n";

        // Load mySQL dump for preprint authors.
        $this->load_preprint_authors();
        echo "found " . count($this->preprint_authors) . " authors\n";

        // Load mySQL dump for preprints.
        $this->load_preprint_files();
        echo "found " . count($this->preprint_files) . " files\n";

        // Disable all un-used collections
        $this->disable_all_collectionroles();

        // Load mySQL dump for preprint projects.
        $this->load_projects();
        echo "found and created " . count($this->preprint_projects) . " projects\n";

        // Load mySQL dump for preprint institutes.
        $this->load_institutes();
        echo "found and created " . count($this->preprint_institutes) . " institutes\n";

        // Load mySQL dump for preprints.
        $preprints = $this->load_xml_mysqldump($this->dumps_dir . '/preprints.xml');
        echo "found " . count($preprints) . " preprints\n";





        $counter = 0;
        $total = count($preprints);

        foreach ($preprints AS $pid => $preprint) {
            $pid = $preprint['id'];

            $doc = new Opus_Document();
            $doc->setType('preprint');
            $doc->setLanguage('eng');

            //    <field name="id">1</field>
            $oldid = $doc->addIdentifierOld();
            $oldid->setValue($pid);

            //    <field name="status">2</field>
            $doc->setServerState('published');

            //    <field name="serial">2</field>
            $serial = $doc->addIdentifierSerial();
            $serial->setValue($preprint['serial']);

            //    <field name="title">Skew-Hamiltonian and Hamiltonian eigenvalue problems: Theory, algorithms and applications</field>
            $field = $preprint['title'];
            if ($field != '') {
                $model = $doc->addTitleMain();
                $model->setLanguage('eng');
                $model->setValue($field);
            }

            //    <field name="filename_ps">bkm2final.ps</field>
            //    <field name="filename_pdf">bkm2final.pdf</field>
            if (array_key_exists($pid, $this->preprint_files)) {
                foreach ($this->preprint_files[$pid] AS $file) {
                    $model = $doc->addFile();
                    $model->setLanguage('eng');
                    $model->setTempFile($this->files_dir . DIRECTORY_SEPARATOR . $pid . DIRECTORY_SEPARATOR . $file['file_name']);
                    $model->setPathName($file['file_name']);

                    if (array_key_exists('original_file_name', $file)) {
                        $model->setLabel($file['original_file_name']);
                    }
                }
            }

            //    <field name="submitter">25</field>
            //    <field name="submit_date">2003-12-03 00:00:00</field>
            $doc->setServerDatePublished($preprint['submit_date']);

            //    <field name="referee">842</field>
            $field = $preprint['referee'];
            if (array_key_exists($field, $this->persons)) {
                $doc->addPersonReferee($this->persons[$field]);
            }
            else {
                throw new Exception("No referee for document $pid");
            }

            //    <field name="approve_date">2003-12-10 00:00:00</field>
            $doc->setCompletedDate($preprint['approve_date']);

            //    <field name="comment" xsi:nil="true" />
            $field = $preprint['comment'];
            if ($field != '') {
                $model = $doc->addNote();
                $model->setMessage($field);
                $model->setVisibility('private');
            }

            //    <field name="prevpub" xsi:nil="true" />
            //    <field name="owner_id">606</field>
            $field = $preprint['owner_id'];
            if (array_key_exists($field, $this->persons)) {
                $doc->addPersonSubmitter($this->persons[$field]);
            }
            else {
                // throw new Exception("No owner for document $pid");
            }

            //    <field name="abstract" xsi:nil="true" />
            $field = $preprint['abstract'];
            if ($field != '') {
                $model = $doc->addTitleAbstract();
                $model->setLanguage('eng');
                $model->setValue($field);
            }

            //    <field name="msc" xsi:nil="true" />
            $field = $preprint['msc'];
            if ($field != '') {
                $msc_hash = self::parse_msc($field);

                $msc_rest_string = $msc_hash['rest_string'];
                $mscs = $msc_hash['mscs'];
                $msc_string_clean = $msc_hash['msc_string_clean'];

                if (count($mscs) > 0) {
                    if ($msc_rest_string != '') {
                        echo "-- Unbekannte Zeichenkette in MSC-Werten gefunden:\n";
                        echo "\tZeichenkette: '$msc_string_clean'\n";
                        echo "\tGefundene MSCs: ('" . implode("','", $mscs) . "')\n";
                        echo "\tUnbekannter Anteil: '$msc_rest_string'\n";
                    }
                    else {
                        // echo "successfully parsed mscs: ('" . implode("','", $mscs) . "')\n";
                    }
                }

                $msc_role = Opus_CollectionRole::fetchByName('msc');

                foreach ($mscs AS $m) {
                    $model = $doc->addSubjectMSC();
                    $model->setValue("$m");

                    $msc_collections = Opus_Collection::fetchCollectionsByRoleNumber($msc_role->getId(), trim($m));

                    if (!is_array($msc_collections) or count($msc_collections) < 1) {
                        echo "-- Unbekannte MSC-Klassifikation gefunden: $m\n";
                    }
                    else if (count($msc_collections) > 1) {
                        echo "-- Doppelte MSC-Klassifikation gefunden: $m\n";
                    }

                    if (is_array($msc_collections) && count($msc_collections) >= 1) {
                        foreach ($msc_collections AS $msc_c) {
                            $doc->addCollection($msc_c);
                        }
                    }
                }
            }

            //    <field name="keywords" xsi:nil="true" />
            $field = $preprint['keywords'];
            if ($field != '') {
                foreach (self::parse_keywords($field) AS $k) {
                    $model = $doc->addSubjectUncontrolled();
                    $model->setValue($k);
                }
            }

            // check for authors key...
            if (!array_key_exists($pid, $this->preprint_authors)) {
                throw new Exception("No authors for document $pid");
            }

            // load authors...
            $unique_authors = array();

            foreach ($this->preprint_authors[$pid] AS $mda) {
                $mda_id = $mda->store();

                if (false === array_key_exists($mda_id, $unique_authors)) {
                    $unique_authors[$mda_id] = 0;
                    $doc->addPersonAuthor($mda);
                }
                else {
                    echo "-- Doppelter Autor in Dokument (serial: {$preprint['serial']}):\n";
                    echo "\t->firstName: " . $mda->getFirstName() . "\n";
                    echo "\t->lastName: " . $mda->getLastName() . "\n";
                }
                $unique_authors[$mda_id]++;
            }

            // load collections: projects
            if (array_key_exists($pid, $this->preprint_projects)) {
                foreach ($this->preprint_projects[$pid] AS $c) {
                    // echo "Adding collection {$c->getId()} to document $pid\n";
                    $doc->addCollection($c);
                }
            }

            // load collections: institutes
            if (array_key_exists($pid, $this->preprint_institutes)) {
                foreach ($this->preprint_institutes[$pid] AS $c) {
                    // echo "Adding collection {$c->getId()} to document $pid\n";
                    $doc->addCollection($c);
                }
            }

            $counter++;
            try {
                $docid = $doc->store();
                echo "created $counter/$total documents -- opus_id: $docid, serial: {$preprint['serial']}, pid: $pid\n";
            }
            catch (Opus_Model_Exception $e) {
                echo "failed creating document $counter/$total --serial: {$preprint['serial']}, pid: $pid\n";
                throw $e;
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
$migrate = new MatheonMigration_Preprints($options);
$migrate->run();
