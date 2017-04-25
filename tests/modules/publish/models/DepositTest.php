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
 * @package     Module_Publish Unit Test
 * @author      Susanne Gottwald <gottwald@zib.de>
 * @copyright   Copyright (c) 2008-2017, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */
class Publish_Model_DepositTest extends ControllerTestCase {

    /**
     * @expectedException Publish_Model_FormDocumentNotFoundException
     */
    public function testInvalidDocumentState() {
        $document = $this->createTestDocument();
        $document->setServerState('published');
        $documentId = $document->store();

        $log = Zend_Registry::get('Zend_Log');
        $deposit = new Publish_Model_Deposit($log);
        $deposit->storeDocument($documentId);
    }

    public function testValidDocumentData() {
        $this->useEnglish();

        $document = $this->createTestDocument();
        $document->setServerState('temporary');
        $docId = $document->store();
        
        $enrichment = new Opus_EnrichmentKey();
        $enrichment->setName('Foo2Title');
        $enrichment->store();

        $data = array(
            'PersonSubmitterFirstName_1' => array('value' => 'Hans', 'datatype' => 'Person', 'subfield' => '0'),
            'PersonSubmitterLastName_1' => array('value' => 'Hansmann', 'datatype' => 'Person', 'subfield' => '1'),
            'PersonSubmitterEmail_1' => array('value' => 'test@mail.com', 'datatype' => 'Person', 'subfield' => '1'),
            'PersonSubmitterPlaceOfBirth_1' => array('value' => 'Stadt', 'datatype' => 'Person', 'subfield' => '1'),
            'PersonSubmitterDateOfBirth_1' => array('value' => '1970/02/01', 'datatype' => 'Person', 'subfield' => '1'),
            'PersonSubmitterAcademicTitle_1' => array('value' => 'Dr.', 'datatype' => 'Person', 'subfield' => '1'),
            'PersonSubmitterAllowEmailContact_1' => array('value' => '0', 'datatype' => 'Person', 'subfield' => '1'),
            'CompletedDate' => array('value' => '2012/2/1', 'datatype' => 'Date', 'subfield' => '0'),
            'PersonAuthorFirstName_1' => array('value' => 'vorname', 'datatype' => 'Person', 'subfield' => '1'),
            'PersonAuthorLastName_1' => array('value' => 'nachname', 'datatype' => 'Person', 'subfield' => '0'),            
            'PersonAuthorLastName_2' => array('value' => 'nurNachname', 'datatype' => 'Person', 'subfield' => '0'),
            'TitleMain_1' => array('value' => 'Entenhausen', 'datatype' => 'Title', 'subfield' => '0'),
            'TitleMainLanguage_1' => array('value' => 'deu', 'datatype' => 'Language', 'subfield' => '1'),
            'TitleMain_2' => array('value' => 'Irgendwas sonst', 'datatype' => 'Title', 'subfield' => '0'),
            'TitleMainLanguage_2' => array('value' => 'eng', 'datatype' => 'Language', 'subfield' => '1'),
            'Language' => array('value' => 'deu', 'datatype' => 'Language', 'subfield' => '0'),
            'Note' => array('value' => 'Dies ist ein Kommentar', 'datatype' => 'Note', 'subfield' => '0'),
            'Licence' => array('value' => '3', 'datatype' => 'Licence', 'subfield' => '0'),
            'ThesisGrantor' => array('value' => '1', 'datatype' => 'ThesisGrantor', 'subfield' => '0'),
            'ThesisPublisher' => array('value' => '2', 'datatype' => 'ThesisPublisher', 'subfield' => '0'),
            'ThesisYearAccepted' => array('value' => '2009', 'datatype' => 'Year', 'subfield' => '0'),
            'SubjectSwd_1' => array('value' => 'hallo098', 'datatype' => 'Subject', 'subfield' => '0'),
            'SubjectUncontrolled_1' => array('value' => 'Keyword', 'datatype' => 'Subject', 'subfield' => '0'),
            'SubjectUncontrolledLanguage_1' => array('value' => 'deu', 'datatype' => 'Language', 'subfield' => '1'),
            'SubjectMSC_1' => array('value' => '8030', 'datatype' => 'Collection', 'subfield' => '0'),
            'SubjectJEL_1' => array('value' => '6740', 'datatype' => 'Collection', 'subfield' => '0'),
            'SubjectPACS_1' => array('value' => '2878', 'datatype' => 'Collection', 'subfield' => '0'),
            'SubjectBKL_1' => array('value' => '13874', 'datatype' => 'Collection', 'subfield' => '0'),
            'IdentifierOld' => array('value' => 'Publish_Model_DepositTest_old', 'datatype' => 'Identifier', 'subfield' => '0'),
            'IdentifierSerial' => array('value' => 'Publish_Model_DepositTest_serial', 'datatype' => 'Identifier', 'subfield' => '0'),
            'IdentifierUuid' => array('value' => 'Publish_Model_DepositTest_uuid', 'datatype' => 'Identifier', 'subfield' => '0'),
            'IdentifierIsbn' => array('value' => 'Publish_Model_DepositTest_isbn', 'datatype' => 'Identifier', 'subfield' => '0'),
            'IdentifierDoi' => array('value' => 'Publish_Model_DepositTest_doi', 'datatype' => 'Identifier', 'subfield' => '0'),
            'IdentifierHandle' => array('value' => 'Publish_Model_DepositTest_handle', 'datatype' => 'Identifier', 'subfield' => '0'),
            'IdentifierUrn' => array('value' => 'Publish_Model_DepositTest_urn', 'datatype' => 'Identifier', 'subfield' => '0'),
            'IdentifierUrl' => array('value' => 'Publish_Model_DepositTest_url', 'datatype' => 'Identifier', 'subfield' => '0'),
            'IdentifierIssn' => array('value' => 'Publish_Model_DepositTest_issn', 'datatype' => 'Identifier', 'subfield' => '0'),
            'IdentifierStdDoi' => array('value' => 'Publish_Model_DepositTest_stddoi', 'datatype' => 'Identifier', 'subfield' => '0'),
            'IdentifierArxiv' => array('value' => 'Publish_Model_DepositTest_arxiv', 'datatype' => 'Identifier', 'subfield' => '0'),
            'IdentifierPubmed' => array('value' => 'Publish_Model_DepositTest_pubmed', 'datatype' => 'Identifier', 'subfield' => '0'),
            'IdentifierCrisLink' => array('value' => 'Publish_Model_DepositTest_crislink', 'datatype' => 'Identifier', 'subfield' => '0'),
            'IdentifierSplashUrl' => array('value' => 'Publish_Model_DepositTest_splashurl', 'datatype' => 'Identifier', 'subfield' => '0'),
            'IdentifierOpus3' => array('value' => 'Publish_Model_DepositTest_opus3', 'datatype' => 'Identifier', 'subfield' => '0'),
            'IdentifierOpac' => array('value' => 'Publish_Model_DepositTest_opac', 'datatype' => 'Identifier', 'subfield' => '0'),
            'ReferenceIsbn' => array('value' => 'Publish_Model_DepositTest_ref_isbn', 'datatype' => 'Reference', 'subfield' => '0'),
            'ReferenceUrn' => array('value' => 'Publish_Model_DepositTest_ref_urn', 'datatype' => 'Reference', 'subfield' => '0'),
            'ReferenceHandle' => array('value' => 'Publish_Model_DepositTest_ref_handle', 'datatype' => 'Reference', 'subfield' => '0'),
            'ReferenceDoi' => array('value' => 'Publish_Model_DepositTest_ref_doi', 'datatype' => 'Reference', 'subfield' => '0'),
            'ReferenceIssn' => array('value' => 'Publish_Model_DepositTest_ref_issn', 'datatype' => 'Reference', 'subfield' => '0'),
            'ReferenceUrl' => array('value' => 'Publish_Model_DepositTest_ref_url', 'datatype' => 'Reference', 'subfield' => '0'),
            'ReferenceCrisLink' => array('value' => 'Publish_Model_DepositTest_ref_crislink', 'datatype' => 'Reference', 'subfield' => '0'),
            'ReferenceStdDoi' => array('value' => 'Publish_Model_DepositTest_ref_stddoi', 'datatype' => 'Reference', 'subfield' => '0'),
            'ReferenceSplashUrl' => array('value' => 'Publish_Model_DepositTest_ref_splashurl', 'datatype' => 'Reference', 'subfield' => '0'),
            'SeriesNumber1' => array('value' => '5', 'datatype' => 'SeriesNumber', 'subfield' => '0'),
            'Series1' => array('value' => '4', 'datatype' => 'Series', 'subfield' => '1'),
            'Foo2Title' => array('value' => 'title as enrichment', 'datatype' => 'Enrichment', 'subfield' => '0'),
        );

        $log = Zend_Registry::get('Zend_Log');

        $dep = new Publish_Model_Deposit($log);
        $dep->storeDocument($docId, null, $data);

        $document = $dep->getDocument();
        $document->store();                               
        
        $personSubmitter = $document->getPersonSubmitter(0);        
        $this->assertEquals('Hans', $personSubmitter->getFirstName());
        $this->assertEquals('Hansmann', $personSubmitter->getLastName());
        $this->assertEquals('test@mail.com', $personSubmitter->getEmail());
        $this->assertEquals('Stadt', $personSubmitter->getPlaceOfBirth());

        $datesHelper = new Application_Controller_Action_Helper_Dates();

        $this->assertEquals($datesHelper->getOpusDate('1970/02/01'), $personSubmitter->getDateOfBirth());

        $this->assertEquals('Dr.', $personSubmitter->getAcademicTitle());
        $this->assertEquals('0', $personSubmitter->getAllowEmailContact());
        
        $this->assertEquals($datesHelper->getOpusDate('2012/2/1'), $document->getCompletedDate());

        $personAuthor1 = $document->getPersonAuthor(0);        
        $this->assertEquals('vorname', $personAuthor1->getFirstName());
        $this->assertEquals('nachname', $personAuthor1->getLastName());        
        $personAuthor2 = $document->getPersonAuthor(1);                
        $this->assertEquals('nurNachname', $personAuthor2->getLastName());
        
        $titleMains = $document->getTitleMain();
        $titleMain1 = $titleMains[0];
        $this->assertEquals('Entenhausen', $titleMain1->getValue());
        $this->assertEquals('deu', $titleMain1->getLanguage());
        $titleMain2 = $titleMains[1];
        $this->assertEquals('Irgendwas sonst', $titleMain2->getValue());
        $this->assertEquals('eng', $titleMain2->getLanguage());
        
        $this->assertEquals('deu', $document->getLanguage());
        
        $this->assertEquals('Dies ist ein Kommentar', $document->getNote(0)->getMessage());
                        
        $this->assertEquals(3, $document->getLicence(0)->getModel()->getId());
          
        $this->assertEquals(1, $document->getThesisGrantor(0)->getModel()->getId());
        $this->assertEquals(2, $document->getThesisPublisher(0)->getModel()->getId());
        
        $this->assertEquals('2009', $document->getThesisYearAccepted());
                
        $this->assertEquals('hallo098', $document->getSubject(0)->getValue());
        $this->assertEquals('Keyword', $document->getSubject(1)->getValue());
        $this->assertEquals('deu', $document->getSubject(1)->getLanguage());
        
        $this->assertEquals(8030, $document->getCollection(0)->getId());
        $this->assertEquals(6740, $document->getCollection(1)->getId());
        $this->assertEquals(2878, $document->getCollection(2)->getId());
        $this->assertEquals(13874, $document->getCollection(3)->getId());
        
        $this->assertEquals('Publish_Model_DepositTest_old', $document->getIdentifier(0)->getValue());
        $this->assertEquals('old', $document->getIdentifier(0)->getType());        
        $this->assertEquals('Publish_Model_DepositTest_serial', $document->getIdentifier(1)->getValue());
        $this->assertEquals('serial', $document->getIdentifier(1)->getType());        
        $this->assertEquals('Publish_Model_DepositTest_uuid', $document->getIdentifier(2)->getValue());
        $this->assertEquals('uuid', $document->getIdentifier(2)->getType());        
        $this->assertEquals('Publish_Model_DepositTest_isbn', $document->getIdentifier(3)->getValue());
        $this->assertEquals('isbn', $document->getIdentifier(3)->getType());        
        $this->assertEquals('Publish_Model_DepositTest_urn', $document->getIdentifier(4)->getValue());
        $this->assertEquals('urn', $document->getIdentifier(4)->getType());        
        $this->assertEquals('Publish_Model_DepositTest_doi', $document->getIdentifier(5)->getValue());
        $this->assertEquals('doi', $document->getIdentifier(5)->getType());        
        $this->assertEquals('Publish_Model_DepositTest_handle', $document->getIdentifier(6)->getValue());
        $this->assertEquals('handle', $document->getIdentifier(6)->getType());   
        $this->assertEquals('Publish_Model_DepositTest_url', $document->getIdentifier(7)->getValue());
        $this->assertEquals('url', $document->getIdentifier(7)->getType());        
        $this->assertEquals('Publish_Model_DepositTest_issn', $document->getIdentifier(8)->getValue());
        $this->assertEquals('issn', $document->getIdentifier(8)->getType());        
        $this->assertEquals('Publish_Model_DepositTest_stddoi', $document->getIdentifier(9)->getValue());
        $this->assertEquals('std-doi', $document->getIdentifier(9)->getType());
        $this->assertEquals('Publish_Model_DepositTest_crislink', $document->getIdentifier(10)->getValue());
        $this->assertEquals('cris-link', $document->getIdentifier(10)->getType());
        $this->assertEquals('Publish_Model_DepositTest_splashurl', $document->getIdentifier(11)->getValue());
        $this->assertEquals('splash-url', $document->getIdentifier(11)->getType());        
        $this->assertEquals('Publish_Model_DepositTest_opus3', $document->getIdentifier(12)->getValue());
        $this->assertEquals('opus3-id', $document->getIdentifier(12)->getType());        
        $this->assertEquals('Publish_Model_DepositTest_opac', $document->getIdentifier(13)->getValue());
        $this->assertEquals('opac-id', $document->getIdentifier(13)->getType());
        $this->assertEquals('Publish_Model_DepositTest_arxiv', $document->getIdentifier(14)->getValue());
        $this->assertEquals('arxiv', $document->getIdentifier(14)->getType());        
        $this->assertEquals('Publish_Model_DepositTest_pubmed', $document->getIdentifier(15)->getValue());
        $this->assertEquals('pmid', $document->getIdentifier(15)->getType());
                
        $this->assertEquals(5, $document->getSeries(0)->getNumber());
        $this->assertEquals(4, $document->getSeries(0)->getModel()->getId());
        
        $this->assertEquals('title as enrichment', $document->getEnrichment(0)->getValue());
         
        $document->deletePermanent();
        Opus_EnrichmentKey::fetchbyName('Foo2Title')->delete();
    }

    /**
     * OPUSVIER-3713
     */
    public function testCastStringToDate()
    {
        $this->useEnglish();

        $deposit = new Publish_Model_Deposit(Application_Configuration::getInstance()->getLogger());

        $date = $deposit->castStringToOpusDate('2017/03/12');

        $this->assertInstanceOf('Opus_Date', $date);

        $this->assertEquals('2017', $date->getYear());

        $this->assertNotEquals('12', $date->getMonth());
        $this->assertEquals('03', $date->getMonth());

        $this->assertNotEquals('03', $date->getDay());
        $this->assertEquals('12', $date->getDay());
    }

    public function testCastStringToDateGerman()
    {
        $this->useGerman();

        $deposit = new Publish_Model_Deposit(Application_Configuration::getInstance()->getLogger());

        $date = $deposit->castStringToOpusDate('12.03.2017');

        $this->assertInstanceOf('Opus_Date', $date);

        $this->assertEquals('2017', $date->getYear());

        $this->assertNotEquals('12', $date->getMonth());
        $this->assertEquals('03', $date->getMonth());

        $this->assertNotEquals('03', $date->getDay());
        $this->assertEquals('12', $date->getDay());
    }

}

