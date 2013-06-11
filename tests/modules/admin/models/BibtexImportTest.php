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
 * @category    Application Unit Tests
 * @author      Gunar Maiwald (maiwald@zib.de)
 * @copyright   Copyright (c) 2008-2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */


class Admin_Model_BibtexImportTest extends ControllerTestCase {

    private static $script;
    private static $bibdir;

    private $importer;

    private $doc;
    private $doc2;


    public static function setUpBeforeClass() {
        self::$script = dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/scripts/import/MetadataImporter.php';
        self::$bibdir = dirname(dirname(dirname(dirname(__FILE__)))) . '/import/bibtex/';
    }


    public function setUp() {
        parent::setUp();
        $this->doc = null;
        $this->doc2 = null;
    }
    

    public function tearDown() {

        if (!is_null($this->doc)) {
            $this->doc->deletePermanent();
        }

        if (!is_null($this->doc2)) {
            $this->doc2->deletePermanent();
        }

        if (!is_null($this->importer)) {
            if (file_exists($this->importer->getXmlFilename())) {
                unlink($this->importer->getXmlFilename());
            }
        }
        parent::tearDown();
    }


    private function __import($filename) {
        $config = Zend_Registry::get('Zend_Config');
        $tmpDirectory = $config->workspacePath . DIRECTORY_SEPARATOR . "tmp";

        $this->importer = new Admin_Model_BibtexImport(self::$bibdir . $filename, $tmpDirectory);
        $this->importer->convertBibtexToOpusxml();
        $numberOfOpusDocuments = $this->importer->convertBibtexToOpusxml();

        exec("php " . self::$script . " " . $this->importer->getXmlFilename());

        $ids = Opus_Document::getAllIds();

        if($numberOfOpusDocuments === 2) {
            $last_id = array_pop($ids);
            $this->doc2 = new Opus_Document($last_id);
        }

        $last_id = array_pop($ids);
        $this->doc = new Opus_Document($last_id);
 
        return $numberOfOpusDocuments;
    }

    public function testFileIsNotReadable() {
        $config = Zend_Registry::get('Zend_Config');
        $directory = $config->workspacePath . DIRECTORY_SEPARATOR . "tmp";
        $filename = 'non_existing.bib';
        $this->assertFalse(is_file( self::$bibdir . $filename ));

        $this->setExpectedException('Admin_Model_Exception', self::$bibdir . $filename . ' is not readable' );
        $import = new Admin_Model_BibtexImport(self::$bibdir . $filename, $directory);
    }


    public function testDirectoryIsNotWriteable() {
        $config = Zend_Registry::get('Zend_Config');
        $directory = $config->workspacePath . DIRECTORY_SEPARATOR . "tmp/non_existing_directory";
        $filename = "article.bib";
        $this->assertFalse(is_writable( $directory ));


        $this->setExpectedException('Admin_Model_Exception', $directory . ' is not writeable');
        $import = new Admin_Model_BibtexImport(self::$bibdir . $filename, $directory);
    }


    public function testNoValidMetadataFile() {
        $filename = "malformed.bib";

        $this->setExpectedException('Admin_Model_Exception', self::$bibdir . $filename . ' contains no valid metadata');
        $this->__import("malformed.bib");
    }


    public function testImportArticle() {
        $number = $this->__import("article.bib");
        $this->assertEquals('1', $number);

        $this->assertEquals('article', $this->doc->getType());
        $this->assertEquals('Peter', $this->doc->getPersonAuthor(0)->getFirstName());
        $this->assertEquals('Adams', $this->doc->getPersonAuthor(0)->getLastName());
        $this->assertEquals('The title of the article', $this->doc->getTitleMain(0)->getValue());
        $this->assertEquals('The name of the journal', $this->doc->getTitleParent(0)->getValue());
        $this->assertEquals('1993', $this->doc->getPublishedYear());
        $this->assertEquals('4', $this->doc->getVolume());
        $this->assertEquals('2', $this->doc->getIssue());
        $this->assertEquals('101', $this->doc->getPageFirst());
        $this->assertEquals('113', $this->doc->getPageLast());
        $this->assertEquals('An optional note', $this->doc->getNote(0)->getMessage());
        $this->assertEquals('public', $this->doc->getNote(0)->getVisibility());

        $this->assertEquals('BibtexRecord', $this->doc->getEnrichment(0)->getKeyName());
        $this->assertEquals(
"@article{article,
  author  = {Peter Adams},
  title   = {The title of the article},
  journal = {The name of the journal},
  year    = 1993,
  volume  = 4,
  number  = 2,
  pages   = {101-113},
  month   = 7,
  note    = {An optional note},
}", $this->doc->getEnrichment(0)->getValue());
    }


    public function testImportArticleTwoDocuments() {
        $number = $this->__import("articleTwoDocuments.bib");
        $this->assertEquals('2', $number);

        $this->assertEquals('article', $this->doc->getType());
        $this->assertEquals('John', $this->doc->getPersonAuthor(0)->getFirstName());
        $this->assertEquals('Doe', $this->doc->getPersonAuthor(0)->getLastName());
        $this->assertEquals('The title of first article', $this->doc->getTitleMain(0)->getValue());
        $this->assertEquals('The name of first journal', $this->doc->getTitleParent(0)->getValue());
        $this->assertEquals('1993', $this->doc->getPublishedYear());
        $this->assertEquals('4', $this->doc->getVolume());
        $this->assertEquals('2', $this->doc->getIssue());
        $this->assertEquals('101', $this->doc->getPageFirst());
        $this->assertEquals('113', $this->doc->getPageLast());
        $this->assertEquals('An optional note', $this->doc->getNote(0)->getMessage());
        $this->assertEquals('public', $this->doc->getNote(0)->getVisibility());

        $this->assertEquals('BibtexRecord', $this->doc->getEnrichment(0)->getKeyName());
        $this->assertEquals(
"@article{article1,
  author  = {John Doe},
  title   = {The title of first article},
  journal = {The name of first journal},
  year    = 1993,
  volume  = 4,
  number  = 2,
  pages   = {101-113},
  month   = 7,
  note    = {An optional note},
}", $this->doc->getEnrichment(0)->getValue());

        $this->assertEquals('article', $this->doc2->getType());
        $this->assertEquals('Jane', $this->doc2->getPersonAuthor(0)->getFirstName());
        $this->assertEquals('Roe', $this->doc2->getPersonAuthor(0)->getLastName());
        $this->assertEquals('The title of second article', $this->doc2->getTitleMain(0)->getValue());
        $this->assertEquals('The name of second journal', $this->doc2->getTitleParent(0)->getValue());
        $this->assertEquals('2003', $this->doc2->getPublishedYear());
        $this->assertEquals('14', $this->doc2->getVolume());
        $this->assertEquals('12', $this->doc2->getIssue());
        $this->assertEquals('10', $this->doc2->getPageFirst());
        $this->assertEquals('13', $this->doc2->getPageLast());
        $this->assertEquals('An optional second note', $this->doc2->getNote(0)->getMessage());
        $this->assertEquals('public', $this->doc2->getNote(0)->getVisibility());

        $this->assertEquals('BibtexRecord', $this->doc2->getEnrichment(0)->getKeyName());
        $this->assertEquals(
"@article{article2,
  author  = {Jane Roe},
  title   = {The title of second article},
  journal = {The name of second journal},
  year    = 2003,
  volume  = 14,
  number  = 12,
  pages   = {10-13},
  month   = 10,
  note    = {An optional second note},
}", $this->doc2->getEnrichment(0)->getValue());

    }


    public function testImportArticleAuthorAbbrev() {
        $number = $this->__import("articleAuthorAbbrev.bib");
        $this->assertEquals('1', $number);

        $this->assertEquals('article', $this->doc->getType());
        $this->assertEquals('P', $this->doc->getPersonAuthor(0)->getFirstName());
        $this->assertEquals('Adams', $this->doc->getPersonAuthor(0)->getLastName());
        $this->assertEquals('J', $this->doc->getPersonAuthor(1)->getFirstName());
        $this->assertEquals('Doe', $this->doc->getPersonAuthor(1)->getLastName());
        $this->assertEquals('M', $this->doc->getPersonAuthor(2)->getFirstName());
        $this->assertEquals('Musterman', $this->doc->getPersonAuthor(2)->getLastName());
        $this->assertEquals('The title of the article', $this->doc->getTitleMain(0)->getValue());
        $this->assertEquals('The name of the journal', $this->doc->getTitleParent(0)->getValue());
        $this->assertEquals('1993', $this->doc->getPublishedYear());

        $this->assertEquals('BibtexRecord', $this->doc->getEnrichment(0)->getKeyName());
        $this->assertEquals(
"@article{article,
  author  = {P. Adams and J. Doe and M. Musterman },
  title   = {The title of the article},
  journal = {The name of the journal},
  year    = 1993,
}", $this->doc->getEnrichment(0)->getValue());
    }


    public function testImportArticleAuthorCommaSeparated() {
        $number = $this->__import("articleAuthorCommaSeparated.bib");
        $this->assertEquals('1', $number);

        $this->assertEquals('article', $this->doc->getType());
        $this->assertEquals('Peter', $this->doc->getPersonAuthor(0)->getFirstName());
        $this->assertEquals('Adams', $this->doc->getPersonAuthor(0)->getLastName());
        $this->assertEquals('John', $this->doc->getPersonAuthor(1)->getFirstName());
        $this->assertEquals('Doe', $this->doc->getPersonAuthor(1)->getLastName());
        $this->assertEquals('Max', $this->doc->getPersonAuthor(2)->getFirstName());
        $this->assertEquals('Musterman', $this->doc->getPersonAuthor(2)->getLastName());
        $this->assertEquals('The title of the article', $this->doc->getTitleMain(0)->getValue());
        $this->assertEquals('The name of the journal', $this->doc->getTitleParent(0)->getValue());
        $this->assertEquals('1993', $this->doc->getPublishedYear());

        $this->assertEquals('BibtexRecord', $this->doc->getEnrichment(0)->getKeyName());
        $this->assertEquals(
"@article{article,
  author  = {Adams, Peter and Doe, John and Musterman, Max },
  title   = {The title of the article},
  journal = {The name of the journal},
  year    = 1993,
}", $this->doc->getEnrichment(0)->getValue());
    }

    public function testImportArticleManyAuthors() {
        $number = $this->__import("articleManyAuthors.bib");
        $this->assertEquals('1', $number);

        $this->assertEquals('article', $this->doc->getType());
        $this->assertEquals('Peter', $this->doc->getPersonAuthor(0)->getFirstName());
        $this->assertEquals('Adams', $this->doc->getPersonAuthor(0)->getLastName());
        $this->assertEquals('John', $this->doc->getPersonAuthor(1)->getFirstName());
        $this->assertEquals('Doe', $this->doc->getPersonAuthor(1)->getLastName());
        $this->assertEquals('Max', $this->doc->getPersonAuthor(2)->getFirstName());
        $this->assertEquals('Musterman', $this->doc->getPersonAuthor(2)->getLastName());
        $this->assertEquals('The title of the article', $this->doc->getTitleMain(0)->getValue());
        $this->assertEquals('The name of the journal', $this->doc->getTitleParent(0)->getValue());
        $this->assertEquals('1993', $this->doc->getPublishedYear());

        $this->assertEquals('BibtexRecord', $this->doc->getEnrichment(0)->getKeyName());
        $this->assertEquals(
"@article{article,
  author  = {Peter Adams and John Doe and Max Musterman },
  title   = {The title of the article},
  journal = {The name of the journal},
  year    = 1993,
}", $this->doc->getEnrichment(0)->getValue());
    }


    public function testImportBook() {
        $number = $this->__import("book.bib");
        $this->assertEquals('1', $number);

        $this->assertEquals('book', $this->doc->getType());
        $this->assertEquals('Peter', $this->doc->getPersonAuthor(0)->getFirstName());
        $this->assertEquals('Babington', $this->doc->getPersonAuthor(0)->getLastName());
        $this->assertEquals('The title of the @-book', $this->doc->getTitleMain(0)->getValue());
        $this->assertEquals('The name of the publisher', $this->doc->getPublisherName());
        $this->assertEquals('1994', $this->doc->getPublishedYear());
        $this->assertEquals( '4', $this->doc->getVolume());
        $this->assertEquals('10', $this->doc->getTitleParent(0)->getValue());
        $this->assertEquals('The address', $this->doc->getPublisherPlace());
        $this->assertEquals('3', $this->doc->getEdition());
        $this->assertEquals('An optional note', $this->doc->getNote(0)->getMessage());
        $this->assertEquals('public', $this->doc->getNote(0)->getVisibility());
        $this->assertEquals('3257227892', $this->doc->getIdentifierIsbn(0)->getValue());
        
        $this->assertEquals('BibtexRecord', $this->doc->getEnrichment(0)->getKeyName());
        $this->assertEquals(
"@book{book,
  author    = {Peter Babington},
  title     = {The title of the @-book},
  publisher = {The name of the publisher},
  year      = 1994,
  volume    = 4,
  series    = 10,
  address   = {The address},
  edition   = 3,
  month     = 7,
  note      = {An optional note},
  isbn      = {3257227892}
}", $this->doc->getEnrichment(0)->getValue());
    }


    public function testImportBooklet() {
        $number = $this->__import("booklet.bib");
        $this->assertEquals('1', $number);

        $this->assertEquals('book', $this->doc->getType());
        $this->assertEquals('The title of the booklet', $this->doc->getTitleMain(0)->getValue());
        $this->assertEquals('Peter', $this->doc->getPersonAuthor(0)->getFirstName());
        $this->assertEquals('Caxton', $this->doc->getPersonAuthor(0)->getLastName());
        $this->assertEquals('1995', $this->doc->getPublishedYear());
        $this->assertEquals('The address of the publisher', $this->doc->getPublisherPlace());
        $this->assertEquals('An optional note', $this->doc->getNote(0)->getMessage());
        $this->assertEquals('BibtexRecord', $this->doc->getEnrichment(0)->getKeyName());
        $this->assertEquals(
"@booklet{booklet,
  title        = {The title of the booklet},
  author       = {Peter Caxton},
  howpublished = {How it was published},
  address      = {The address of the publisher},
  month        = 7,
  year         = 1995,
  note         = {An optional note}
}", $this->doc->getEnrichment(0)->getValue());
    }


    public function testImportInbook() {
        $number = $this->__import("inbook.bib");
        $this->assertEquals('1', $number);

        $this->assertEquals('bookpart', $this->doc->getType());
        $this->assertEquals('Peter', $this->doc->getPersonAuthor(0)->getFirstName());
        $this->assertEquals('The title of the book', $this->doc->getTitleParent(0)->getValue());
        $this->assertEquals('8', $this->doc->getTitleMain(0)->getValue());
        $this->assertEquals('201', $this->doc->getPageFirst());
        $this->assertEquals('213', $this->doc->getPageLast());
        $this->assertEquals('The name of the publisher', $this->doc->getPublisherName());
        $this->assertEquals('1996', $this->doc->getPublishedYear());
        $this->assertEquals( '4', $this->doc->getVolume());
        $this->assertEquals('The address of the publisher', $this->doc->getPublisherPlace());
        $this->assertEquals('12', $this->doc->getEdition());
        $this->assertEquals('An optional note', $this->doc->getNote(0)->getMessage());
        $this->assertEquals('public', $this->doc->getNote(0)->getVisibility());
        $this->assertEquals('BibtexRecord', $this->doc->getEnrichment(0)->getKeyName());
        $this->assertEquals(
"@inbook{inbook,
  author       = {Peter Eston},
  title        = {The title of the book},
  chapter      = 8,
  pages        = {201-213},
  publisher    = {The name of the publisher},
  year         = 1996,
  volume       = 4,
  series       = 5,
  type         = {type of inbook},
  address      = {The address of the publisher},
  edition      = 12,
  month        = 7,
  note         = {An optional note}
}", $this->doc->getEnrichment(0)->getValue());
    }


    public function testImportIncollection() {
        $number = $this->__import("incollection.bib");
        $this->assertEquals('1', $number);

        $this->assertEquals('bookpart', $this->doc->getType());
        $this->assertEquals('Peter', $this->doc->getPersonAuthor(0)->getFirstName());
        $this->assertEquals('Farindon', $this->doc->getPersonAuthor(0)->getLastName());
        $this->assertEquals('The title of the bookpart', $this->doc->getTitleMain(0)->getValue());
        $this->assertEquals('The title of the book', $this->doc->getTitleParent(0)->getValue());
        $this->assertEquals('The name of the publisher', $this->doc->getPublisherName());
        $this->assertEquals('1997', $this->doc->getPublishedYear());
        $this->assertEquals( '4', $this->doc->getVolume());
        $this->assertEquals('301', $this->doc->getPageFirst());
        $this->assertEquals('313', $this->doc->getPageLast());
        $this->assertEquals('The address of the publisher', $this->doc->getPublisherPlace());
        $this->assertEquals('13', $this->doc->getEdition());
        $this->assertEquals('An optional note', $this->doc->getNote(0)->getMessage());
        $this->assertEquals('public', $this->doc->getNote(0)->getVisibility());
        $this->assertEquals('BibtexRecord', $this->doc->getEnrichment(0)->getKeyName());
        $this->assertEquals(
"@incollection{incollection,
  author       = {Peter Farindon},
  title        = {The title of the bookpart},
  booktitle    = {The title of the book},
  publisher    = {The name of the publisher},
  year         = 1997,
  editor       = {The editor},
  volume       = 4,
  series       = 5,
  type         = {Type of incollection},
  chapter      = 8,
  pages        = {301-313},
  address      = {The address of the publisher},
  edition      = 13,
  month        = 7,
  note         = {An optional note}
}", $this->doc->getEnrichment(0)->getValue());
    }


    public function testImportInproceedings() {
        $number = $this->__import("inproceedings.bib");
        $this->assertEquals('1', $number);

        $this->assertEquals('conferenceobject', $this->doc->getType());
        $this->assertEquals('Peter', $this->doc->getPersonAuthor(0)->getFirstName());
        $this->assertEquals('Draper', $this->doc->getPersonAuthor(0)->getLastName());
        $this->assertEquals('The title of the inproceedings', $this->doc->getTitleMain(0)->getValue());
        $this->assertEquals('The title of the conference', $this->doc->getTitleParent(0)->getValue());
        $this->assertEquals('1998', $this->doc->getPublishedYear());
        $this->assertEquals('The', $this->doc->getPersonEditor(0)->getFirstName());
        $this->assertEquals('editor', $this->doc->getPersonEditor(0)->getLastName());
        $this->assertEquals( '41', $this->doc->getVolume());
        $this->assertEquals('413', $this->doc->getPageFirst());
        $this->assertNull($this->doc->getPageLast());
        $this->assertEquals('The address of publisher', $this->doc->getPublisherPlace());
        $this->assertEquals('The organization', $this->doc->getContributingCorporation());
        $this->assertEquals('The publisher', $this->doc->getPublisherName());
        $this->assertEquals('An optional note', $this->doc->getNote(0)->getMessage());
        $this->assertEquals('public', $this->doc->getNote(0)->getVisibility());
        $this->assertEquals('BibtexRecord', $this->doc->getEnrichment(0)->getKeyName());
        $this->assertEquals(
"@inproceedings{inproceedings,
  author       = {Peter Draper},
  title        = {The title of the inproceedings},
  booktitle    = {The title of the conference},
  year         = 1998,
  editor       = {The editor},
  volume       = 41,
  series       = 5,
  pages        = 413,
  address      = {The address of publisher},
  month        = 7,
  organization = {The organization},
  publisher    = {The publisher},
  note         = {An optional note}
}", $this->doc->getEnrichment(0)->getValue());
    }

    public function testImportManual() {
        $number = $this->__import("manual.bib");
        $this->assertEquals('1', $number);

        $this->assertEquals('other', $this->doc->getType());
        $this->assertEquals('The address of the publisher', $this->doc->getPublisherPlace());
        $this->assertEquals('The title of the manual', $this->doc->getTitleMain(0)->getValue());
        $this->assertEquals('1999', $this->doc->getPublishedYear());
        $this->assertEquals('Peter', $this->doc->getPersonAuthor(0)->getFirstName());
        $this->assertEquals('Gainsford', $this->doc->getPersonAuthor(0)->getLastName());
        $this->assertEquals('The organization', $this->doc->getContributingCorporation());
        $this->assertEquals( '23', $this->doc->getEdition());
        $this->assertEquals('An optional note', $this->doc->getNote(0)->getMessage());
        $this->assertEquals('public', $this->doc->getNote(0)->getVisibility());
        $this->assertEquals('BibtexRecord', $this->doc->getEnrichment(0)->getKeyName());
        $this->assertEquals(
"@manual{manual,
  address      = {The address of the publisher},
  title        = {The title of the manual},
  year         = 1999,
  author       = {Peter Gainsford},
  organization = {The organization},
  edition      = 23,
  month        = 7,
  note         = {An optional note}
}", $this->doc->getEnrichment(0)->getValue());
    }


    public function testImportMastersthesis() {
        $number = $this->__import("mastersthesis.bib");
        $this->assertEquals('1', $number);

        $this->assertEquals('masterthesis', $this->doc->getType());
        $this->assertEquals('Peter', $this->doc->getPersonAuthor(0)->getFirstName());
        $this->assertEquals('Harwood', $this->doc->getPersonAuthor(0)->getLastName());
        $this->assertEquals('The title of the mastersthesis', $this->doc->getTitleMain(0)->getValue());
        $this->assertEquals('The school where the thesis was written', $this->doc->getContributingCorporation());
        $this->assertEquals('2000', $this->doc->getPublishedYear());
        $this->assertEquals('The address of the publisher', $this->doc->getPublisherPlace());
        $this->assertEquals('An optional note', $this->doc->getNote(0)->getMessage());
        $this->assertEquals('public', $this->doc->getNote(0)->getVisibility());
        $this->assertEquals('BibtexRecord', $this->doc->getEnrichment(0)->getKeyName());
        $this->assertEquals(
"@mastersthesis{mastersthesis,
  author       = {Peter Harwood},
  title        = {The title of the mastersthesis},
  school       = {The school where the thesis was written},
  year         = 2000,
  type         = {Type of thesis},
  address      = {The address of the publisher},
  month        = 7,
  note         = {An optional note}
}", $this->doc->getEnrichment(0)->getValue());
    }


    public function testImportMisc() {
        $number = $this->__import("misc.bib");
        $this->assertEquals('1', $number);


        $this->assertEquals('other', $this->doc->getType());
        $this->assertEquals('Peter', $this->doc->getPersonAuthor(0)->getFirstName());
        $this->assertEquals('Isley', $this->doc->getPersonAuthor(0)->getLastName());
        $this->assertEquals('The title of the misc', $this->doc->getTitleMain(0)->getValue());
        $this->assertEquals('2001', $this->doc->getPublishedYear());
        $this->assertEquals('An optional note', $this->doc->getNote(0)->getMessage());
        $this->assertEquals('public', $this->doc->getNote(0)->getVisibility());
        $this->assertEquals('BibtexRecord', $this->doc->getEnrichment(0)->getKeyName());
        $this->assertEquals(
"@misc{misc,
  author       = {Peter Isley},
  title        = {The title of the misc},
  howpublished = {How it was published},
  month        = 7,
  year         = 2001,
  note         = {An optional note}
}", $this->doc->getEnrichment(0)->getValue());
    }


    public function testImportPhdthesis() {
        $number = $this->__import("phdthesis.bib");
        $this->assertEquals('1', $number);

        $this->assertEquals('doctoralthesis', $this->doc->getType());
        $this->assertEquals('Peter', $this->doc->getPersonAuthor(0)->getFirstName());
        $this->assertEquals('Joslin', $this->doc->getPersonAuthor(0)->getLastName());
        $this->assertEquals('The title of the phdthesis', $this->doc->getTitleMain(0)->getValue());
        $this->assertEquals('The school where the thesis was written', $this->doc->getContributingCorporation());
        $this->assertEquals('2002', $this->doc->getPublishedYear());
        $this->assertEquals('The address of the publisher', $this->doc->getPublisherPlace());
        $this->assertEquals('An optional note', $this->doc->getNote(0)->getMessage());
        $this->assertEquals('public', $this->doc->getNote(0)->getVisibility());
        $this->assertEquals('BibtexRecord', $this->doc->getEnrichment(0)->getKeyName());
        $this->assertEquals(
"@phdthesis{phdthesis,
  author       = {Peter Joslin},
  title        = {The title of the phdthesis},
  school       = {The school where the thesis was written},
  year         = 2002,
  type         = {The type of thesis},
  address      = {The address of the publisher},
  month        = 7,
  note         = {An optional note}
}", $this->doc->getEnrichment(0)->getValue());
    }


    public function testImportProceedings() {
        $number = $this->__import("proceedings.bib");
        $this->assertEquals('1', $number);

        $this->assertEquals('conferenceobject', $this->doc->getType());
        $this->assertEquals('The title of the conference', $this->doc->getTitleMain(0)->getValue());
        $this->assertEquals('2003', $this->doc->getPublishedYear());
        $this->assertEquals('Peter', $this->doc->getPersonEditor(0)->getFirstName());
        $this->assertEquals('Kidwelly', $this->doc->getPersonEditor(0)->getLastName());
        $this->assertEquals('44', $this->doc->getVolume());
        $this->assertEquals('The address of the publisher', $this->doc->getPublisherPlace());
        $this->assertEquals('The organization', $this->doc->getContributingCorporation());
        $this->assertEquals('The name of the publisher', $this->doc->getPublisherName());
        $this->assertEquals('An optional note', $this->doc->getNote(0)->getMessage());
        $this->assertEquals('public', $this->doc->getNote(0)->getVisibility());
        $this->assertEquals('BibtexRecord', $this->doc->getEnrichment(0)->getKeyName());
        $this->assertEquals(
"@proceedings{proceedings,
  title        = {The title of the conference},
  year         = 2003,
  editor       = {Peter Kidwelly},
  volume       = 44,
  series       = 5,
  address      = {The address of the publisher},
  month        = 7,
  organization = {The organization},
  publisher    = {The name of the publisher},
  note         = {An optional note}
}", $this->doc->getEnrichment(0)->getValue());
    }


    public function testImportTechreport() {
        $number = $this->__import("techreport.bib");
        $this->assertEquals('1', $number);

        $this->assertEquals('report', $this->doc->getType());
        $this->assertEquals('Peter', $this->doc->getPersonAuthor(0)->getFirstName());
        $this->assertEquals('Lambert', $this->doc->getPersonAuthor(0)->getLastName());
        $this->assertEquals('The title of the techreport', $this->doc->getTitleMain(0)->getValue());
        $this->assertEquals('The institution that published', $this->doc->getPublisherName());
        $this->assertEquals('2004', $this->doc->getPublishedYear());
        $this->assertEquals('An optional note', $this->doc->getNote(0)->getMessage());
        $this->assertEquals('public', $this->doc->getNote(0)->getVisibility());
        $this->assertEquals('The address of the publisher', $this->doc->getPublisherPlace());
        $this->assertEquals('BibtexRecord', $this->doc->getEnrichment(0)->getKeyName());
        $this->assertEquals(
"@techreport{techreport,
  author       = {Peter Lambert},
  title        = {The title of the techreport},
  institution  = {The institution that published},
  year         = 2004,
  number       = 2,
  address      = {The address of the publisher},
  month        = 7,
  note         = {An optional note}
}", $this->doc->getEnrichment(0)->getValue());
    }


    public function testImportUnpublished() {
        $number = $this->__import("unpublished.bib");
        $this->assertEquals('1', $number);
        
        $this->assertEquals('other', $this->doc->getType());
        $this->assertEquals('Peter', $this->doc->getPersonAuthor(0)->getFirstName());
        $this->assertEquals('Marcheford', $this->doc->getPersonAuthor(0)->getLastName());
        $this->assertEquals('The title of the unpublished work', $this->doc->getTitleMain(0)->getValue());
        $this->assertEquals('An optional note', $this->doc->getNote(0)->getMessage());
        $this->assertEquals('public', $this->doc->getNote(0)->getVisibility());
        $this->assertEquals('2005', $this->doc->getPublishedYear());
        $this->assertEquals('BibtexRecord', $this->doc->getEnrichment(0)->getKeyName());
        $this->assertEquals(
"@unpublished{unpublished,
  author       = {Peter Marcheford},
  title        = {The title of the unpublished work},
  note         = {An optional note},
  month        = 7,
  year         = 2005
}", $this->doc->getEnrichment(0)->getValue());
    }

}