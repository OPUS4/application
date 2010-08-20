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

    private $files_dir = null;
    private $dumps_dir = null;

    private $persons = array();
    private $document_authors = array();

    /**
     * Constructur.
     *
     * @param array $options Array with input options.
     */
    function __construct($options) {
        $this->dumps_dir = $options['dumps-dir'];
        $this->files_dir = $options['files-dir'];
    }

    public function load_persons($file) {
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

        return $this->persons;

    }

    public function load_preprint_authors($file) {

        $matheon_document_authors = array();

        foreach ($this->load_xml_mysqldump($file) AS $author) {

            $matheon_author_id = $author['author'];
            $matheon_preprint_id = $author['document'];
            $matheon_user = null;

            $matheon_user_id = $author['user_id'];
            if (false === empty($matheon_user_id)) {
                if (false === array_key_exists($matheon_user_id, $this->persons)) {
                    echo "no maths_id user found for id $matheon_user_id (document $matheon_preprint_id)\n";
                    continue;
                }

                $matheon_user = $this->persons[$matheon_user_id];

                if ($author['givenname'] != '' && $matheon_user->getFirstName() !== $author['givenname']) {
                    echo "firstname mismatch found for user_id $matheon_user_id (document $matheon_preprint_id): " . $matheon_user->getFirstName() . " !== {$author['givenname']}\n";
                }

                if ($author['familyname'] != '' && $matheon_user->getLastName() !== $author['familyname']) {
                    echo "lastname mismatch found for user_id $matheon_user_id (document $matheon_preprint_id): " . $matheon_user->getLastName() . " !== {$author['familyname']}\n";
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
                echo "document $matheon_preprint_id // author $matheon_author_id // user_id $matheon_user_id -- empty FirstName!\n";
                $matheon_user->setFirstName("INVALID");
            }

            if (empty($lastname)) {
                // var_dump($author);
                echo "document $matheon_preprint_id // author $matheon_author_id // user_id $matheon_user_id -- empty LastName!\n";
                $matheon_user->setLastName("INVALID");
            }

            if (!array_key_exists($matheon_preprint_id, $matheon_document_authors)) {
                $matheon_document_authors[$matheon_preprint_id] = array();
            }
            $matheon_document_authors[$matheon_preprint_id][] = $matheon_user;
        }

        $this->document_authors = $matheon_document_authors;

    }

    /**
     * Custom run method.
     *
     * @return <type>
     */
    public function run() {

        // Load mySQL dump for preprints.
        $preprints = $this->load_xml_mysqldump($this->dumps_dir . '/preprints.xml');
        echo "found " . count($preprints) . " preprints\n";

        // Load mySQL dump for preprints.
        $preprint_files = self::array2hash($this->load_xml_mysqldump($this->dumps_dir . '/preprint_files.xml'), 'table_id');
        echo "found " . count($preprint_files) . " files\n";

        // Load mySQL dump for preprint persons.
        $this->load_persons($this->dumps_dir . '/preprint_persons.xml');
        echo "found and created " . count($this->persons) . " persons\n";

        // Load mySQL dump for preprint authors.
        $this->load_preprint_authors($this->dumps_dir . '/preprint_authors.xml');
        echo "found " . count($this->document_authors) . " authors\n";





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
            if (array_key_exists($pid, $preprint_files)) {
                foreach ($preprint_files[$pid] AS $file) {
                    $model = $doc->addFile();
                    $model->setLanguage('eng');
                    $model->setSourcePath($this->files_dir . DIRECTORY_SEPARATOR . $pid);
                    $model->setTempFile($file['file_name']);
                    $model->setDestinationPath('/home/tklein/opus4-zib/server/workspace/files');
                    $model->setPathName($file['file_name']);

                    if (array_key_exists('original_file_name', $file)) {
                        $model->setLabel($file['original_file_name']);
                    }
                }
            }

            //    <field name="submitter">25</field>
            //    <field name="submit_date">2003-12-03 00:00:00</field>
            $doc->setPublishedDate($preprint['submit_date']);

            //    <field name="referee">842</field>
            $field = $preprint['referee'];
            if (array_key_exists($field, $this->persons)) {
                $doc->addPersonReferee($this->persons[$field]);
            }
            else {
                throw new Exception("No referee for document $pid");
            }

            //    <field name="approve_date">2003-12-10 00:00:00</field>
            $doc->setDateAccepted($preprint['approve_date']);

            //    <field name="comment" xsi:nil="true" />
            $field = $preprint['comment'];
            if ($field != '') {
                $model = $doc->addNote();
                $model->setMessage($field);
                $model->setCreator('nobody'); // FIXME: Allow empty values.
                $model->setScope('private');
            }

            //    <field name="prevpub" xsi:nil="true" />
            //    <field name="owner_id">606</field>
            $field = $preprint['owner_id'];
            if (array_key_exists($field, $this->persons)) {
                $doc->addPersonOwner($this->persons[$field]);
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
                // $msc_array = explode($delimiter, $string);
                echo "msc: $field\n";

                $model = $doc->addSubjectMSC();
                $model->setValue($field);
            }

            //    <field name="keywords" xsi:nil="true" />
            $field = $preprint['keywords'];
            if ($field != '') {
                $model = $doc->addSubjectUncontrolled();
                $model->setValue($field);
            }

            // check for authors key...
            if (!array_key_exists($pid, $this->document_authors)) {
                throw new Exception("No authors for document $pid");
            }

            // load authors...
            $unique_authors = array();
            $unique_authors_array = array();
            $duplicate_authors_array = array();

            foreach ($this->document_authors[$pid] AS $mda) {
                $mda_id = $mda->store();

                if (false === array_key_exists($mda_id, $unique_authors)) {
                    $unique_authors[$mda_id] = 0;
                    $unique_authors_array[] = $mda;
                }
                else {
                    $duplicate_authors_array[] = $mda;
                }
                $unique_authors[$mda_id]++;
            }

            foreach ($duplicate_authors_array as $mda) {
                echo "found (and skipped) duplicate author in serial {$preprint['serial']}:\n";
                echo "mda->firstName: " . $mda->getFirstName() . "\n";
                echo "mda->lastName: " . $mda->getLastName() . "\n";
            }

            $doc->setPersonAuthor($unique_authors_array);


            $counter++;
            try {
                $docid = $doc->store();
                echo "created $counter/$total documents, current document id: $docid($pid)\n";
            }
            catch (Opus_Model_Exception $e) {
                echo "failed creating document $counter/$total, current document id: serial {$preprint['serial']} ($pid)\n";
            }
        }

    }

}

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
 * Bootstrap application.
 */
require_once 'Zend/Application.php';
$application = new Zend_Application(
    APPLICATION_ENV,
    array(
        "config"=>array(
            APPLICATION_PATH . '/application/configs/application.ini',
            APPLICATION_PATH . '/config/config.ini'
        )
    )
);
$application->bootstrap();

/**
 * Run import script.
 */
$migrate = new MatheonMigration_Preprints($options);
$migrate->run();
