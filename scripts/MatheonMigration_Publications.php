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
class MatheonMigration_Publications extends MatheonMigration_Base {

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
    }

    public static function assert_empty($hash, $key) {
        $value = is_null($hash[$key]) ? '' : trim($hash[$key]);

        if (!empty($hash[$key])) {
            throw new Exception("Assertion failed: Field '$key' should be empty, but has value '$value'.");
        }
    }

    public static function assert_int_equal($hash, $key, $expected) {
        if ((int)$hash[$key] !== $expected) {
            throw new Exception("Assertion failed: Field '$key' should have '$expected', but has '{$hash[$key]}'.");
        }
    }

    /**
     * Custom run method.
     *
     * @return <type>
     */
    public function run() {

        // Load mySQL dump for preprints.
        $publications = $this->load_xml_mysqldump($this->dumps_dir . '/publications.xml');
        echo "found " . count($publications) . " publications\n";


        $counter = 0;
        $total = count($publications);

        foreach ($publications AS $publication) {
            $pid = $publication['id'];

            // (IGNORED) <field name="publika_art_name">Article</field>
            $doc = new Opus_Document();
            $doc->setType($publication['publika_art_name']);
            $doc->setLanguage('eng');

            $oldid = $doc->addIdentifierOld();
            $oldid->setValue($pid);

            $doc->setServerState('published');

            // (IGNORED) <field name="owner_id">-1</field>
            // (IGNORED) <field name="ispublic">1</field>
            self::assert_int_equal($publication, 'ispublic', 1);

            // (IGNORED) <field name="publika_art">1</field>
            // (IGNORED) <field name="creation_date">2005-04-17 17:31:44</field>
            $doc->setServerDatePublished($publication['creation_date']);

            // (IGNORED) <field name="edit_date">2005-05-03 11:56:00</field>
            $doc->setServerDateModified($publication['edit_date']);

            // (IGNORED) <field name="author">E. Casas and F. Tr (IGNORED)  (IGNORED)  (IGNORED) ltzsch</field>
            // (IGNORED) <field name="author_search">Tr (IGNORED)  (IGNORED)  (IGNORED) ltzsch</field>

            // (IGNORED) <field name="title">Error estimates for the finite-element approximation of a semilinear elliptic control problem</field>
            $field = $publication['title'];
            if ($field != '') {
                $model = $doc->addTitleMain();
                $model->setLanguage('eng');
                $model->setValue($field);
            }

            // (IGNORED) <field name="publika_year">2002</field>
            // (IGNORED) <field name="booktitle" xsi:nil="true" />
            // (IGNORED) <field name="journal">concy</field>
            // (IGNORED) <field name="chapter" xsi:nil="true" />
            // (IGNORED) <field name="institution" xsi:nil="true" />
            // (IGNORED) <field name="note" xsi:nil="true" />
            // (IGNORED) <field name="publisher" xsi:nil="true" />
            // (IGNORED) <field name="school" xsi:nil="true" />
            // (IGNORED) <field name="ALTauthor" xsi:nil="true" />

            // (IGNORED) <field name="ALTeditor" xsi:nil="true" />
            self::assert_empty($publication, 'ALTeditor');

            // (IGNORED) <field name="OPTaddress"></field>
            // (IGNORED) <field name="OPTannote"></field>
            // (IGNORED) <field name="OPTauthor" xsi:nil="true" />
            self::assert_empty($publication, 'ALTauthor');

            // (IGNORED) <field name="OPTbooktitle" xsi:nil="true" />
            // (IGNORED) <field name="OPTchapter" xsi:nil="true" />
            // (IGNORED) <field name="OPTcrossref" xsi:nil="true" />
            // (IGNORED) <field name="OPTedition" xsi:nil="true" />
            // (IGNORED) <field name="OPTeditor" xsi:nil="true" />
            // (IGNORED) <field name="OPTkey"></field>
            // (IGNORED) <field name="OPTmonth"></field>
            // (IGNORED) <field name="OPTnote"></field>
            // (IGNORED) <field name="OPTnumber"></field>
            // (IGNORED) <field name="OPTorganization" xsi:nil="true" />
            // (IGNORED) <field name="OPTpages">695--712</field>
            // (IGNORED) <field name="OPTpublisher" xsi:nil="true" />
            // (IGNORED) <field name="OPTseries" xsi:nil="true" />
            // (IGNORED) <field name="OPTtype" xsi:nil="true" />
            // (IGNORED) <field name="OPTvolume">31</field>
            // (IGNORED) <field name="OPTISBN_ISSN" xsi:nil="true" />
            // (IGNORED) <field name="OPTLanguage"></field>
            // (IGNORED) <field name="OPThowpublished" xsi:nil="true" />
            // (IGNORED) <field name="OPTURL"></field>
            // (IGNORED) <field name="OPTclassmath"></field>
            // (IGNORED) <field name="OPTKeyWords"></field>
            // (IGNORED) <field name="internalkey">a1-CasT02</field>
            // (IGNORED) <field name="Projektrelevanz">0</field>
            // (IGNORED) <field name="msrepl_tran_version">7643237E-3578-4118-BCA5-C7D270E97888</field>











if (false) {
            
            //    <field name="submitter">25</field>
            //    <field name="submit_date">2003-12-03 00:00:00</field>

            //    <field name="referee">842</field>
            $field = $publication['referee'];
            if (array_key_exists($field, $this->persons)) {
                $doc->addPersonReferee($this->persons[$field]);
            }
            else {
                throw new Exception("No referee for document $pid");
            }

            //    <field name="approve_date">2003-12-10 00:00:00</field>
            $doc->setDateAccepted($publication['approve_date']);

            //    <field name="comment" xsi:nil="true" />
            $field = $publication['comment'];
            if ($field != '') {
                $model = $doc->addNote();
                $model->setMessage($field);
                $model->setCreator('nobody'); // FIXME: Allow empty values.
                $model->setScope('private');
            }

            //    <field name="prevpub" xsi:nil="true" />
            //    <field name="owner_id">606</field>
            $field = $publication['owner_id'];
            if (array_key_exists($field, $this->persons)) {
                $doc->addPersonOwner($this->persons[$field]);
            }
            else {
                // throw new Exception("No owner for document $pid");
            }

            //    <field name="abstract" xsi:nil="true" />
            $field = $publication['abstract'];
            if ($field != '') {
                $model = $doc->addTitleAbstract();
                $model->setLanguage('eng');
                $model->setValue($field);
            }

            //    <field name="msc" xsi:nil="true" />
            $field = $publication['msc'];
            if ($field != '') {
                $model = $doc->addSubjectMSC();
                $model->setValue($field);
            }

            //    <field name="keywords" xsi:nil="true" />
            $field = $publication['keywords'];
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
                echo "found (and skipped) duplicate author in serial {$publication['serial']}:\n";
                echo "mda->firstName: " . $mda->getFirstName() . "\n";
                echo "mda->lastName: " . $mda->getLastName() . "\n";
            }

            $doc->setPersonAuthor($unique_authors_array);
}

            $counter++;
            try {
                $docid = $doc->store();
                echo "created $counter/$total documents, current document id: $docid($pid)\n";
            }
            catch (Opus_Model_Exception $e) {
                echo "failed creating document $counter/$total, current document id: serial $pid\n";
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
);
$options = getopt("", $long_options);

if (empty($options)) {
    echo "required options: --dumps-dir=./dumps/ \n";
}

/**
 * Bootstrap application.
 */
require_once 'Zend/Application.php';
$application = new Zend_Application(APPLICATION_ENV,
        APPLICATION_PATH . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.ini');
$application->bootstrap();

/**
 * Run import script.
 */
$migrate = new MatheonMigration_Publications($options);
$migrate->run();
