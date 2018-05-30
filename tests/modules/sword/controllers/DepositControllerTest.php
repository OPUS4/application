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
 * @category    Tests
 * @package     Sword
 * @author      Sascha Szott
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2016-2018
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 *
 * @covers Sword_DepositController
 */
class Sword_DepositControllerTest extends ControllerTestCase {
        
    private $testHelper;
    
    public function setUp() {
        parent::setUp();
        $this->testHelper = new DepositTestHelper();
        $this->testHelper->setupTmpDir();
    }
    
    public function testZipArchiveMinimalDocumentDeposit() {
        $this->depositSuccessful('minimal-record.zip', DepositTestHelper::CONTENT_TYPE_ZIP);
    }
    
    public function testTarArchiveMinimalDocumentDeposit() {
        $this->depositSuccessful('minimal-record.tar', DepositTestHelper::CONTENT_TYPE_TAR);
    }
    
    public function testZipArchiveAllFieldDocumentDeposit() {
        $doc = $this->depositSuccessful('allfields-document.zip', DepositTestHelper::CONTENT_TYPE_ZIP, true, false, false, 6, 3, 'published');
        $this->checkAllFieldsImport($doc);
        $doc->deletePermanent();
        $this->testHelper->removeImportCollection();
    }

    public function testTarArchiveAllFieldDocumentDeposit() {
        $doc = $this->depositSuccessful('allfields-document.tar', DepositTestHelper::CONTENT_TYPE_TAR, true, false, false, 6, 3, 'published');
        $this->checkAllFieldsImport($doc);
        $doc->deletePermanent();
        $this->testHelper->removeImportCollection();
    }
    
    public function testZipArchiveDanglingIds() {
        $doc = $this->depositSuccessful('dangling-ids.zip', DepositTestHelper::CONTENT_TYPE_ZIP, false, false);
        $this->checkMinimalDoc($doc);
        $doc->deletePermanent();
        $this->testHelper->removeImportCollection();
    }
    
    public function testTarArchiveDanglingIds() {
        $doc = $this->depositSuccessful('dangling-ids.tar', DepositTestHelper::CONTENT_TYPE_TAR, false, false);
        $this->checkMinimalDoc($doc);
        $doc->deletePermanent();
        $this->testHelper->removeImportCollection();
    }

    public function testZipArchiveWithUrnCollision() {
        $doc = $this->depositSuccessful('urn-collision.zip', DepositTestHelper::CONTENT_TYPE_ZIP, false, false);
        $this->checkOnlyOneDocIsImported($doc);
        $doc->deletePermanent();
        $this->testHelper->removeImportCollection();
    }
    
    public function testTarArchiveWithUrnCollision() {
        $doc = $this->depositSuccessful('urn-collision.tar', DepositTestHelper::CONTENT_TYPE_TAR, false, false);
        $this->checkOnlyOneDocIsImported($doc);
        $doc->deletePermanent();
        $this->testHelper->removeImportCollection();        
    }
    
    public function testZipArchiveWithEmptyElementsDocumentDeposit() {
        $doc = $this->depositSuccessful('empty-elements.zip', DepositTestHelper::CONTENT_TYPE_ZIP, false, false);
        $this->checkMinimalDoc($doc, 'eng', 'book', 'titlemain');
        $doc->deletePermanent();
        $this->testHelper->removeImportCollection();
    }

    public function testTarArchiveWithEmptyElementsDocumentDeposit() {
        $doc = $this->depositSuccessful('empty-elements.tar', DepositTestHelper::CONTENT_TYPE_TAR, false, false);
        $this->checkMinimalDoc($doc, 'eng', 'book', 'titlemain');
        $doc->deletePermanent();
        $this->testHelper->removeImportCollection();
    }

    public function testZipArchiveWithEmptyElementsDocumentDepositAlternative() {
        $doc = $this->depositSuccessful('empty-elements-alternative.zip', DepositTestHelper::CONTENT_TYPE_ZIP, false, false);
        $this->checkMinimalDoc($doc, 'eng', 'book', 'titlemain');
        $doc->deletePermanent();
        $this->testHelper->removeImportCollection();
    }

    public function testTarArchiveWithEmptyElementsDocumentDepositAlternative() {
        $doc = $this->depositSuccessful('empty-elements-alternative.tar', DepositTestHelper::CONTENT_TYPE_TAR, false, false);
        $this->checkMinimalDoc($doc, 'eng', 'book', 'titlemain');
        $doc->deletePermanent();
        $this->testHelper->removeImportCollection();
    }
    
    public function testZipSingleDocWithMultipleFilesImplicit() {
        $doc = $this->depositSuccessful('single-doc-files-implicit.zip', DepositTestHelper::CONTENT_TYPE_ZIP, false, false);
        $this->checkMinimalDoc($doc, 'eng', 'book', 'titlemain', 3);
        $files = $doc->getFile();
        $language = $doc->getLanguage();
        $this->checkFile($files[0], 'doc1.pdf', $language, null, 1, 1);
        $this->checkFile($files[1], 'doc1.txt', $language, null, 1, 1);
        $this->checkFile($files[2], 'foo.txt', $language, null, 1, 1);
        $doc->deletePermanent();
        $this->testHelper->removeImportCollection();        
    }
    
    public function testTarSingleDocWithMultipleFilesImplicit() {
        $doc = $this->depositSuccessful('single-doc-files-implicit.tar', DepositTestHelper::CONTENT_TYPE_TAR, false, false);
        $this->checkMinimalDoc($doc, 'eng', 'book', 'titlemain', 3);
        $files = $doc->getFile();
        $language = $doc->getLanguage();
        $this->checkFile($files[0], 'doc1.pdf', $language, null, 1, 1);
        $this->checkFile($files[1], 'doc1.txt', $language, null, 1, 1);
        $this->checkFile($files[2], 'foo.txt', $language, null, 1, 1);
        $doc->deletePermanent();
        $this->testHelper->removeImportCollection();        
    }

    private function checkOnlyOneDocIsImported($doc) {
        $this->assertEquals('eng', $doc->getLanguage());
        $this->assertEquals('article', $doc->getType());        
        $this->testHelper->assertTitleValues($doc->getTitleMain(0), 'The Title Main 1', 'eng');
        $this->assertEquals('colliding-urn', $doc->getIdentifierUrn(0)->getValue());
    }

    private function checkMinimalDoc($doc, $language = 'deu', $docType = 'book', $titleMainValue = 'Title Main deu', $fileCount = 0) {
        $this->assertEquals($language, $doc->getLanguage());
        $this->assertEquals($docType, $doc->getType());
        $this->testHelper->assertTitleValues($doc->getTitleMain(0), $titleMainValue, $language);
        $this->assertEquals($fileCount, count($doc->getFile()));
    }

    private function checkAllFieldsImport($doc) {
        $this->assertEquals('deu', $doc->getLanguage());
        $this->assertEquals('book', $doc->getType());
        $this->assertEquals('10', $doc->getPageFirst());
        $this->assertEquals('20', $doc->getPageLast());
        $this->assertEquals('11', $doc->getPageNumber());
        $this->assertEquals('99', $doc->getEdition());
        $this->assertEquals('11', $doc->getVolume());
        $this->assertEquals('543', $doc->getIssue());
        $this->assertEquals('Foo Publications', $doc->getPublisherName());
        $this->assertEquals('Earth', $doc->getPublisherPlace());
        $this->assertEquals('Bar University', $doc->getCreatingCorporation());
        $this->assertEquals('Baz Institute', $doc->getContributingCorporation());
        $this->assertEquals(1, $doc->getBelongsToBibliography());
        $this->assertEquals('published', $doc->getServerState());
        
        $this->checkTitleFields($doc->getTitleMain(), 'Title Main');
        $this->checkTitleFields($doc->getTitleAbstract(), 'Abstract');
        $this->checkTitleFields($doc->getTitleParent(), 'Title Parent');
        $this->checkTitleFields($doc->getTitleSub(), 'Title Sub');
        $this->checkTitleFields($doc->getTitleAdditional(), 'Title Additional');       
        
        $persons = $doc->getPerson();
        $this->assertEquals(8, count($persons));
        $roles = array(
            'advisor' => 1, 
            'author' => 2, 
            'contributor' => 3, 
            'editor' => 4, 
            'referee' => 5, 
            'translator' => 6, 
            'submitter' => 7, 
            'other' => 8);
        for ($i = 0; $i < count($persons); $i++) {
            $person = $persons[$i];
            $role = $person->getRole();
            if (!array_key_exists($role, $roles)) {
                throw new Exception('unexpected person role ' . $role);
            }
            $this->checkPersonFields($person, $roles[$role]);
            unset($roles[$role]);
        }
        $this->assertTrue(empty($roles));
        
        $subjects = $doc->getSubject();
        $this->assertEquals(4, count($subjects));
        $this->checkSubject($subjects[0], 'kw1deu', 'swd');
        $this->checkSubject($subjects[1], 'kw1eng', 'swd');
        $this->checkSubject($subjects[2], 'kw2deu', 'uncontrolled');
        $this->checkSubject($subjects[3], 'kw2eng', 'uncontrolled');
        
        $publisher = $doc->getThesisPublisher();
        $this->assertEquals(1, count($publisher));
        $publisherId = $publisher[0]->getModel()->getId();
        $this->assertEquals(2, $publisherId);
        
        $grantor = $doc->getThesisGrantor();
        $this->assertEquals(1, count($grantor));
        $grantorId = $grantor[0]->getModel()->getId();
        $this->assertEquals(4, $grantorId);
        
        $this->assertEquals('2010-10-01', $doc->getCompletedDate()->__toString());
        $this->assertEquals('2011-11-01', $doc->getPublishedDate()->__toString());
        $this->assertEquals('2012-12-02', $doc->getThesisDateAccepted()->__toString());
        
        $this->checkIdentifier($doc, 'old');
        $this->checkIdentifier($doc, 'serial');
        $this->checkIdentifier($doc, 'uuid');
        $this->checkIdentifier($doc, 'isbn');
        $this->checkIdentifier($doc, 'urn');
        $this->checkIdentifier($doc, 'doi');
        $this->checkIdentifier($doc, 'handle');
        $this->checkIdentifier($doc, 'url');
        $this->checkIdentifier($doc, 'issn');
        $this->checkIdentifier($doc, 'stdDoi', 'std-doi');
        $this->checkIdentifier($doc, 'crisLink', 'cris-link');
        $this->checkIdentifier($doc, 'splashUrl', 'splash-url');
        $this->checkIdentifier($doc, 'opus3', 'opus3-id');
        $this->checkIdentifier($doc, 'opac', 'opac-id');
        $this->checkIdentifier($doc, 'pubmed', 'pmid');
        $this->checkIdentifier($doc, 'arxiv');
        
        $notes = $doc->getNote();
        $this->assertEquals(2, count($notes));
        $this->checkNote($notes[0], 'private');
        $this->checkNote($notes[1], 'public');
        
        $collections = $doc->getCollection();
        $this->assertEquals(3, count($collections));
        $this->checkCollections($collections);
        
        $series = $doc->getSeries();
        $this->assertEquals(2, count($series));
        $this->checkSeries($series[0], 1, 10);
        $this->checkSeries($series[1], 4, 11);
        
        $enrichments = $doc->getEnrichment();
        $this->assertEquals(6, count($enrichments));
        $this->checkNonImportEnrichments($enrichments);
        
        $licences = $doc->getLicence();
        $this->assertEquals(2, count($licences));
        $this->checkLicence($licences[0], 3);
        $this->checkLicence($licences[1], 4);
        
        $files = $doc->getFile();
        $this->assertEquals(4, count($files));
        $this->checkFile($files[0], 'doc2.pdf', 'eng', null, 1, 0, null, 'comment3');
        $this->checkFile($files[1], 'doc.pdf', 'eng', null, 1, 1, null, 'comment4');
        $this->checkFile($files[2], 'doc.txt', 'deu', null, 0, 1, 1, 'comment2');
        $this->checkFile($files[3], 'doc1.pdf', 'deu', 'doc1', 1, 1, 2, 'comment1');        
    }
    
    private function checkFile($file, $name, $language, $displayName, $visibleInOai, $visibleInFrontdoor, $sortOrder = null, $comment = null) {
        $this->assertEquals($name, $file->getPathName());
        $this->assertEquals($language, $file->getLanguage());
        if (!is_null($displayName)) {
            $this->assertEquals($displayName, $file->getLabel());
        }
        $this->assertEquals($visibleInOai, $file->getVisibleInOai());
        $this->assertEquals($visibleInFrontdoor, $file->getVisibleInFrontdoor());
        if (!is_null($sortOrder)) {
            $this->assertEquals($sortOrder, $file->getSortOrder());
        }
        if (!is_null($comment)) {
            $this->assertEquals($comment, $file->getComment());
        }        
    }
    
    private function checkLicence($licence, $id) {
        $this->assertEquals($id, $licence->getModel()->getId());
    }
    
    private function checkNonImportEnrichments($enrichments) {
        foreach ($enrichments as $enrichment) {
            if (strpos($enrichment->getKeyName(), 'opus.import.') !== 0) {
                // überprüfe hier nur die Enrichments, die nicht automatisch beim Import eines Dokuments angelegt werden
                $keyname = $enrichment->getKeyName();
                $value = $enrichment->getValue();
                $this->assertTrue($keyname == 'SourceSwb' && $value == 'enrichment1' || $keyname == 'SourceTitle' && $value == 'enrichment2');
            }
        }
    }    
    
    private function checkSeries($series, $id, $number) {
        $this->assertEquals($id, $series->getModel()->getId());
        $this->assertEquals($number, $series->getNumber());
    }
    
    private function checkCollections($collections) {
        $idsFound = array();
        foreach ($collections as $collection) {
            $collId = $collection->getId();
            if ($collId == $this->testHelper->getCollectionId() || $collId == 15997 || $collId == 7871) {
                $idsFound[$collId] = true;
            }
        }
        $this->assertEquals(3, count($idsFound));
    }
        
    private function checkNote($note, $type) {
        $this->assertEquals('note-' . $type, $note->getMessage());
        $this->assertEquals($type, $note->getVisibility());
    }
    
    private function checkIdentifier($doc, $type, $value = null) {
        $methodName = 'getIdentifier' . ucfirst($type);
        $identifier = $doc->$methodName(0);
        if (is_null($value)) {
            $this->assertEquals($type, $identifier->getValue());
        }
        else {
            $this->assertEquals($value, $identifier->getValue());
        }
    }

    private function checkSubject($subject, $value, $type) {
        $this->assertEquals($value, $subject->getValue());
        $this->assertEquals($type, $subject->getType());
    }
    
    private function checkPersonFields($person, $index) {        
        $this->assertEquals('fn' . $index, $person->getFirstName());
        $this->assertEquals('ln' . $index, $person->getLastName());
        $this->assertEquals('ac' . $index, $person->getAcademicTitle());
        $this->assertEquals('foo' . $index . '@example.com', $person->getEmail());
        $this->assertEquals('1', $person->getAllowEmailContact());
        $this->assertEquals('pob' . $index, $person->getPlaceOfBirth());
        $this->assertEquals('198' . $index . '-01-02', $person->getDateOfBirth()->__toString());
        
        if ($person->getRole() == 'advisor') {
            $this->assertEquals('orcid', $person->getIdentifierOrcid());
            $this->assertEquals('gnd', $person->getIdentifierGnd());
            $this->assertEquals('intern', $person->getIdentifierMisc());
        }
    }

    private function checkTitleFields($titles, $titleType) {
        $this->assertEquals(2, count($titles));
        $this->testHelper->assertTitleValues($titles[0], "$titleType deu", 'deu');
        $this->testHelper->assertTitleValues($titles[1], "$titleType eng", 'eng');        
    }
    
    /**
     * 
     * @param type $fileName
     * @param type $contentType
     * @param type $abstractExist
     * @param type $deleteDoc
     * @param type $deleteCollection
     * @param type $numOfEnrichments
     * @param type $numOfCollections
     * @param type $serverState
     * @return Opus_Document
     */
    private function depositSuccessful(
        $fileName, $contentType, $abstractExist = true, $deleteDoc = true, $deleteCollection = true,
        $numOfEnrichments = 4, $numOfCollections = 1, $serverState = 'unpublished'
    )
    {
        $this->testHelper->assertEmptyTmpDir();
        $this->testHelper->disableExceptionConversion();
        $this->getRequest()->setMethod('POST');
        $this->getRequest()->setHeader('Content-Type', $contentType);
        $this->testHelper->setValidAuthorizationHeader($this->getRequest(), DepositTestHelper::USER_AGENT);
        $checksum = $this->testHelper->uploadFile($this->getRequest(), $fileName);
        $this->getRequest()->setHeader('Content-Disposition', $fileName);
        $this->testHelper->addImportCollection();

        $this->dispatch('/sword/deposit');                        
        $this->testHelper->assertEmptyTmpDir();
        
        $doc = $this->checkAtomEntryDocument($checksum, $fileName, $abstractExist, $numOfEnrichments, $numOfCollections, $serverState, $deleteDoc);
        if ($deleteCollection) {
            $this->testHelper->removeImportCollection();
        }
        
        return $doc;
    }
    
    private function checkAtomEntryDocument($checksum, $fileName, $abstractExist, $numOfEnrichments, $numOfCollections, $serverState, $deleteDoc) {
        $this->assertEquals(201, $this->getResponse()->getHttpResponseCode());
        
        $doc = new DOMDocument();
        $doc->loadXML($this->getResponse()->getBody());                
        
        $roots = $doc->childNodes;
        $this->assertEquals(1, $roots->length);
        $root = $roots->item(0);
        
        $doc = $this->testHelper->checkAtomEntryDocument($root, $fileName, $checksum, $abstractExist, $numOfEnrichments, $numOfCollections);        
        $this->assertEquals($serverState, $doc->getServerState());        
        $this->checkHttpResponseHeaders($this->testHelper->getFrontdoorUrl());
        
        if (!$deleteDoc) {
            return $doc;
        }
        
        $doc->deletePermanent();
    }
    
    private function checkHttpResponseHeaders($frontdoorUrl) {
        $headers = $this->getResponse()->getHeaders();
        foreach ($headers as $header) {
            $name = $header['name'];
            $value = $header['value'];
            switch ($name) {
                case 'Location':
                    $this->assertEquals($frontdoorUrl, $value);
                    break;
                case 'Content-Type':
                    $this->assertEquals('application/atom+xml; charset=UTF-8', $value);
                    break;
                default:
                    throw new Exception('Unexpected HTTP response header ' . $name);
            }
        }        
    }

}

