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

    private $log;
    
    private $bibdir;

    private $doc;
    
    private $doc2;

    private $filename;

    private $numDocuments;


    public function setUp() {
        parent::setUp();
        $this->bibdir = dirname(dirname(dirname(dirname(__FILE__)))) . '/import/bibtex/';
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
        parent::tearDown();
    }


    private function __import() {
        $bibtexImporter = new Admin_Model_BibtexImport($this->bibdir . $this->filename);
        $bibtexImporter->import();
        $this->numDocuments = $bibtexImporter->getNumDocuments();

        $ids = Opus_Document::getAllIds();

        if($this->numDocuments  === 2) {
            $last_id = array_pop($ids);
            $this->doc2 = new Opus_Document($last_id);
        }

        $last_id = array_pop($ids);
        $this->doc = new Opus_Document($last_id);

    }


    /* Exception Tests: 3 Exceptions without message  */

    public function testFileNotReadableException() {
        $this->filename = 'non_existing.bib';
        $this->assertFalse(is_file( $this->bibdir . $this->filename ));
        $this->setExpectedException('Admin_Model_BibtexImportException', null, Admin_Model_BibtexImportException::FILE_NOT_READABLE);
        $this->__import();
    }

    public function testFileNotUtf8Exception() {
        $this->filename = 'misc.bib.iso';
        $this->setExpectedException('Admin_Model_BibtexImportException', null, Admin_Model_BibtexImportException::FILE_NOT_UTF8);
        $this->__import();
      }
    

    public function testFileNotBibtexEception() {
        $this->filename = 'miscNoOpeningSign.bib';
        $this->setExpectedException('Admin_Model_BibtexImportException', null, Admin_Model_BibtexImportException::FILE_NOT_BIBTEX);
        $this->__import();
    }

    /* Exception Tests: 3 Exceptions with message  */


    public function testRecordWithoutIdException() {
        $this->filename = 'bookNoID.bib';
        $record = trim(file_get_contents($this->bibdir . $this->filename));
        $this->setExpectedException('Admin_Model_BibtexImportException', $record, Admin_Model_BibtexImportException::RECORD_WITHOUT_ID);
        $this->__import();
    }


    public function testDuplicateIdException() {
        $this->filename = 'articleTwoDocumentsDuplicateID.bib';
        $id = 'articleID';
        $this->setExpectedException('Admin_Model_BibtexImportException', $id, Admin_Model_BibtexImportException::DUPLICATE_ID);
        $this->__import();
    }


    public function testInvalidXmlException() {
        $this->filename = 'articleWithoutTitle.bib';
        $id = 'article_ID';
        $this->setExpectedException('Admin_Model_BibtexImportException', $id, Admin_Model_BibtexImportException::INVALID_XML_ERROR);
        $this->__import();
    }

    public function testInvalidXmlExceptionTwoDocuments() {
        $this->filename = 'articlesWithTwoErrors.bib';
        $ids = 'article_ID_3, article_ID_5';
        $this->setExpectedException('Admin_Model_BibtexImportException', $ids, Admin_Model_BibtexImportException::INVALID_XML_ERROR);
        $this->__import();
    }


    /* Mapping Tests */

    public function testImportArticle() {
        $this->filename = 'article.bib';
        $this->__import();
        
        $this->assertEquals('1', $this->numDocuments);
        $this->assertEquals('unpublished', $this->doc->getServerState());
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

        $record = trim(file_get_contents($this->bibdir . $this->filename));
        $this->assertEquals('BibtexRecord', $this->doc->getEnrichment(0)->getKeyName());
        $this->assertEquals($record, $this->doc->getEnrichment(0)->getValue());
    }


    public function testImportArticleTwoDocuments() {
        $this->filename = 'articleTwoDocuments.bib';
        $this->__import();
        $this->assertEquals('2', $this->numDocuments);

        $this->assertEquals('article', $this->doc->getType());
        $this->assertEquals('unpublished', $this->doc->getServerState());
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
        $this->assertEquals('unpublished', $this->doc2->getServerState());
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
        $this->filename = 'articleAuthorAbbrev.bib';
        $this->__import();
        $this->assertEquals('1', $this->numDocuments);

        $this->assertEquals('article', $this->doc->getType());
        $this->assertEquals('unpublished', $this->doc->getServerState());
        $this->assertEquals('P', $this->doc->getPersonAuthor(0)->getFirstName());
        $this->assertEquals('Adams', $this->doc->getPersonAuthor(0)->getLastName());
        $this->assertEquals('J', $this->doc->getPersonAuthor(1)->getFirstName());
        $this->assertEquals('Doe', $this->doc->getPersonAuthor(1)->getLastName());
        $this->assertEquals('M', $this->doc->getPersonAuthor(2)->getFirstName());
        $this->assertEquals('Musterman', $this->doc->getPersonAuthor(2)->getLastName());
        $this->assertEquals('The title of the article', $this->doc->getTitleMain(0)->getValue());
        $this->assertEquals('The name of the journal', $this->doc->getTitleParent(0)->getValue());
        $this->assertEquals('1993', $this->doc->getPublishedYear());

        $record = trim(file_get_contents($this->bibdir . $this->filename));
        $this->assertEquals('BibtexRecord', $this->doc->getEnrichment(0)->getKeyName());
        $this->assertEquals($record, $this->doc->getEnrichment(0)->getValue());
    }


    public function testImportArticleAuthorCommaSeparated() {
        $this->filename = 'articleAuthorCommaSeparated.bib';
        $this->__import();
        $this->assertEquals('1', $this->numDocuments);

        $this->assertEquals('article', $this->doc->getType());
        $this->assertEquals('unpublished', $this->doc->getServerState());
        $this->assertEquals('Peter', $this->doc->getPersonAuthor(0)->getFirstName());
        $this->assertEquals('Adams', $this->doc->getPersonAuthor(0)->getLastName());
        $this->assertEquals('John', $this->doc->getPersonAuthor(1)->getFirstName());
        $this->assertEquals('Doe', $this->doc->getPersonAuthor(1)->getLastName());
        $this->assertEquals('Max', $this->doc->getPersonAuthor(2)->getFirstName());
        $this->assertEquals('Musterman', $this->doc->getPersonAuthor(2)->getLastName());
        $this->assertEquals('The title of the article', $this->doc->getTitleMain(0)->getValue());
        $this->assertEquals('The name of the journal', $this->doc->getTitleParent(0)->getValue());
        $this->assertEquals('1993', $this->doc->getPublishedYear());

        $record = trim(file_get_contents($this->bibdir . $this->filename));
        $this->assertEquals('BibtexRecord', $this->doc->getEnrichment(0)->getKeyName());
        $this->assertEquals($record, $this->doc->getEnrichment(0)->getValue());
    }

    public function testImportArticleManyAuthors() {
        $this->filename = 'articleManyAuthors.bib';
        $this->__import();
        $this->assertEquals('1', $this->numDocuments);

        $this->assertEquals('article', $this->doc->getType());
        $this->assertEquals('unpublished', $this->doc->getServerState());
        $this->assertEquals('Peter', $this->doc->getPersonAuthor(0)->getFirstName());
        $this->assertEquals('Adams', $this->doc->getPersonAuthor(0)->getLastName());
        $this->assertEquals('John', $this->doc->getPersonAuthor(1)->getFirstName());
        $this->assertEquals('Doe', $this->doc->getPersonAuthor(1)->getLastName());
        $this->assertEquals('Max', $this->doc->getPersonAuthor(2)->getFirstName());
        $this->assertEquals('Musterman', $this->doc->getPersonAuthor(2)->getLastName());
        $this->assertEquals('The title of the article', $this->doc->getTitleMain(0)->getValue());
        $this->assertEquals('The name of the journal', $this->doc->getTitleParent(0)->getValue());
        $this->assertEquals('1993', $this->doc->getPublishedYear());

        $record = trim(file_get_contents($this->bibdir . $this->filename));
        $this->assertEquals('BibtexRecord', $this->doc->getEnrichment(0)->getKeyName());
        $this->assertEquals($record, $this->doc->getEnrichment(0)->getValue());
    }


    public function testImportBook() {
        $this->filename = 'book.bib';
        $this->__import();
        $this->assertEquals('1', $this->numDocuments);

        $this->assertEquals('book', $this->doc->getType());
        $this->assertEquals('unpublished', $this->doc->getServerState());
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
        
        $record = trim(file_get_contents($this->bibdir . $this->filename));
        $this->assertEquals('BibtexRecord', $this->doc->getEnrichment(0)->getKeyName());
        $this->assertEquals($record, $this->doc->getEnrichment(0)->getValue());
    }


    public function testImportBooklet() {
        $this->filename = 'booklet.bib';
        $this->__import();
        $this->assertEquals('1', $this->numDocuments);

        $this->assertEquals('book', $this->doc->getType());
        $this->assertEquals('unpublished', $this->doc->getServerState());
        $this->assertEquals('The title of the booklet', $this->doc->getTitleMain(0)->getValue());
        $this->assertEquals('Peter', $this->doc->getPersonAuthor(0)->getFirstName());
        $this->assertEquals('Caxton', $this->doc->getPersonAuthor(0)->getLastName());
        $this->assertEquals('1995', $this->doc->getPublishedYear());
        $this->assertEquals('The address of the publisher', $this->doc->getPublisherPlace());
        $this->assertEquals('An optional note', $this->doc->getNote(0)->getMessage());

        $record = trim(file_get_contents($this->bibdir . $this->filename));
        $this->assertEquals('BibtexRecord', $this->doc->getEnrichment(0)->getKeyName());
        $this->assertEquals($record, $this->doc->getEnrichment(0)->getValue());
    }


    public function testImportInbook() {
        $this->filename = 'inbook.bib';
        $this->__import();
        $this->assertEquals('1', $this->numDocuments);

        $this->assertEquals('bookpart', $this->doc->getType());
        $this->assertEquals('unpublished', $this->doc->getServerState());
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

        $record = trim(file_get_contents($this->bibdir . $this->filename));
        $this->assertEquals('BibtexRecord', $this->doc->getEnrichment(0)->getKeyName());
        $this->assertEquals($record, $this->doc->getEnrichment(0)->getValue());
    }


    public function testImportIncollection() {
        $this->filename = 'incollection.bib';
        $this->__import();
        $this->assertEquals('1', $this->numDocuments);

        $this->assertEquals('bookpart', $this->doc->getType());
        $this->assertEquals('unpublished', $this->doc->getServerState());
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

        $record = trim(file_get_contents($this->bibdir . $this->filename));
        $this->assertEquals('BibtexRecord', $this->doc->getEnrichment(0)->getKeyName());
        $this->assertEquals($record, $this->doc->getEnrichment(0)->getValue());
    }


    public function testImportInproceedings() {
        $this->filename = 'inproceedings.bib';
        $this->__import();
        $this->assertEquals('1', $this->numDocuments);

        $this->assertEquals('conferenceobject', $this->doc->getType());
        $this->assertEquals('unpublished', $this->doc->getServerState());
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

        $record = trim(file_get_contents($this->bibdir . $this->filename));
        $this->assertEquals('BibtexRecord', $this->doc->getEnrichment(0)->getKeyName());
        $this->assertEquals($record, $this->doc->getEnrichment(0)->getValue());
    }

    public function testImportManual() {
        $this->filename = 'manual.bib';
        $this->__import();
        $this->assertEquals('1', $this->numDocuments);

        $this->assertEquals('other', $this->doc->getType());
        $this->assertEquals('unpublished', $this->doc->getServerState());
        $this->assertEquals('The address of the publisher', $this->doc->getPublisherPlace());
        $this->assertEquals('The title of the manual', $this->doc->getTitleMain(0)->getValue());
        $this->assertEquals('1999', $this->doc->getPublishedYear());
        $this->assertEquals('Peter', $this->doc->getPersonAuthor(0)->getFirstName());
        $this->assertEquals('Gainsford', $this->doc->getPersonAuthor(0)->getLastName());
        $this->assertEquals('The organization', $this->doc->getContributingCorporation());
        $this->assertEquals( '23', $this->doc->getEdition());
        $this->assertEquals('An optional note', $this->doc->getNote(0)->getMessage());
        $this->assertEquals('public', $this->doc->getNote(0)->getVisibility());

        $record = trim(file_get_contents($this->bibdir . $this->filename));
        $this->assertEquals('BibtexRecord', $this->doc->getEnrichment(0)->getKeyName());
        $this->assertEquals($record, $this->doc->getEnrichment(0)->getValue());
    }


    public function testImportMastersthesis() {
        $this->filename = 'mastersthesis.bib';
        $this->__import();
        $this->assertEquals('1', $this->numDocuments);

        $this->assertEquals('masterthesis', $this->doc->getType());
        $this->assertEquals('unpublished', $this->doc->getServerState());
        $this->assertEquals('Peter', $this->doc->getPersonAuthor(0)->getFirstName());
        $this->assertEquals('Harwood', $this->doc->getPersonAuthor(0)->getLastName());
        $this->assertEquals('The title of the mastersthesis', $this->doc->getTitleMain(0)->getValue());
        $this->assertEquals('The school where the thesis was written', $this->doc->getContributingCorporation());
        $this->assertEquals('2000', $this->doc->getPublishedYear());
        $this->assertEquals('The address of the publisher', $this->doc->getPublisherPlace());
        $this->assertEquals('An optional note', $this->doc->getNote(0)->getMessage());
        $this->assertEquals('public', $this->doc->getNote(0)->getVisibility());

        $record = trim(file_get_contents($this->bibdir . $this->filename));
        $this->assertEquals('BibtexRecord', $this->doc->getEnrichment(0)->getKeyName());
        $this->assertEquals($record, $this->doc->getEnrichment(0)->getValue());
    }


    public function testImportMisc() {
        $this->filename = 'misc.bib';
        $this->__import();
        $this->assertEquals('1', $this->numDocuments);

        $this->assertEquals('other', $this->doc->getType());
        $this->assertEquals('unpublished', $this->doc->getServerState());
        $this->assertEquals('Peter', $this->doc->getPersonAuthor(0)->getFirstName());
        $this->assertEquals('Isley', $this->doc->getPersonAuthor(0)->getLastName());
        $this->assertEquals('The title of the misc', $this->doc->getTitleMain(0)->getValue());
        $this->assertEquals('2001', $this->doc->getPublishedYear());
        $this->assertEquals('An optional note', $this->doc->getNote(0)->getMessage());
        $this->assertEquals('public', $this->doc->getNote(0)->getVisibility());

        $record = trim(file_get_contents($this->bibdir . $this->filename));
        $this->assertEquals('BibtexRecord', $this->doc->getEnrichment(0)->getKeyName());
        $this->assertEquals($record, $this->doc->getEnrichment(0)->getValue());
    }


    public function testImportPhdthesis() {
        $this->filename = 'phdthesis.bib';
        $this->__import();
        $this->assertEquals('1', $this->numDocuments);

        $this->assertEquals('doctoralthesis', $this->doc->getType());
        $this->assertEquals('unpublished', $this->doc->getServerState());
        $this->assertEquals('Peter', $this->doc->getPersonAuthor(0)->getFirstName());
        $this->assertEquals('Joslin', $this->doc->getPersonAuthor(0)->getLastName());
        $this->assertEquals('The title of the phdthesis', $this->doc->getTitleMain(0)->getValue());
        $this->assertEquals('The school where the thesis was written', $this->doc->getContributingCorporation());
        $this->assertEquals('2002', $this->doc->getPublishedYear());
        $this->assertEquals('The address of the publisher', $this->doc->getPublisherPlace());
        $this->assertEquals('An optional note', $this->doc->getNote(0)->getMessage());
        $this->assertEquals('public', $this->doc->getNote(0)->getVisibility());

        $record = trim(file_get_contents($this->bibdir . $this->filename));
        $this->assertEquals('BibtexRecord', $this->doc->getEnrichment(0)->getKeyName());
        $this->assertEquals($record, $this->doc->getEnrichment(0)->getValue());
    }


    public function testImportProceedings() {
        $this->filename = 'proceedings.bib';
        $this->__import();
        $this->assertEquals('1', $this->numDocuments);

        $this->assertEquals('conferenceobject', $this->doc->getType());
        $this->assertEquals('unpublished', $this->doc->getServerState());
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

        $record = trim(file_get_contents($this->bibdir . $this->filename));
        $this->assertEquals('BibtexRecord', $this->doc->getEnrichment(0)->getKeyName());
        $this->assertEquals($record, $this->doc->getEnrichment(0)->getValue());
    }


    public function testImportTechreport() {
        $this->filename = 'techreport.bib';
        $this->__import();
        $this->assertEquals('1', $this->numDocuments);

        $this->assertEquals('report', $this->doc->getType());
        $this->assertEquals('unpublished', $this->doc->getServerState());
        $this->assertEquals('Peter', $this->doc->getPersonAuthor(0)->getFirstName());
        $this->assertEquals('Lambert', $this->doc->getPersonAuthor(0)->getLastName());
        $this->assertEquals('The title of the techreport', $this->doc->getTitleMain(0)->getValue());
        $this->assertEquals('The institution that published', $this->doc->getPublisherName());
        $this->assertEquals('2004', $this->doc->getPublishedYear());
        $this->assertEquals('An optional note', $this->doc->getNote(0)->getMessage());
        $this->assertEquals('public', $this->doc->getNote(0)->getVisibility());
        $this->assertEquals('The address of the publisher', $this->doc->getPublisherPlace());

        $record = trim(file_get_contents($this->bibdir . $this->filename));
        $this->assertEquals('BibtexRecord', $this->doc->getEnrichment(0)->getKeyName());
        $this->assertEquals($record, $this->doc->getEnrichment(0)->getValue());
    }


    public function testImportUnpublished() {
        $this->filename = 'unpublished.bib';
        $this->__import();
        $this->assertEquals('1', $this->numDocuments);

        $this->assertEquals('other', $this->doc->getType());
        $this->assertEquals('unpublished', $this->doc->getServerState());
        $this->assertEquals('Peter', $this->doc->getPersonAuthor(0)->getFirstName());
        $this->assertEquals('Marcheford', $this->doc->getPersonAuthor(0)->getLastName());
        $this->assertEquals('The title of the unpublished work', $this->doc->getTitleMain(0)->getValue());
        $this->assertEquals('An optional note', $this->doc->getNote(0)->getMessage());
        $this->assertEquals('public', $this->doc->getNote(0)->getVisibility());
        $this->assertEquals('2005', $this->doc->getPublishedYear());

        $record = trim(file_get_contents($this->bibdir . $this->filename));
        $this->assertEquals('BibtexRecord', $this->doc->getEnrichment(0)->getKeyName());
        $this->assertEquals($record, $this->doc->getEnrichment(0)->getValue());
    }

}