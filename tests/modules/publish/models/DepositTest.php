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
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\Date;
use Opus\Common\EnrichmentKey;
use Opus\Common\EnrichmentKeyInterface;
use Opus\Common\Log;

class Publish_Model_DepositTest extends ControllerTestCase
{
    /** @var string[] */
    protected $additionalResources = ['database', 'translation'];

    /** @var EnrichmentKeyInterface */
    private $enrichmentKey;

    public function tearDown(): void
    {
        parent::tearDown();

        // das Entfernen des in den Tests neu angelegten EnrichmentKeys darf erst
        // erfolgen, nachdem alle Dokumente, die den EnrichmentKey verwenden, aus
        // der Datenbank entfernt wurden (ansonsten MySQL-Fehler, weil FK-Constraint
        // fk_document_enrichment_enrichmentkeys in Tabelle document_enrichments
        // verletzt wird)
        if ($this->enrichmentKey !== null) {
            $this->enrichmentKey->delete();
        }
    }

    public function testInvalidDocumentState()
    {
        $document = $this->createTestDocument();
        $document->setServerState('published');
        $documentId = $document->store();

        $log     = Log::get();
        $deposit = new Publish_Model_Deposit($log);

        $this->expectException(Publish_Model_FormDocumentNotFoundException::class);
        $deposit->storeDocument($documentId);
    }

    public function testValidDocumentData()
    {
        $this->useEnglish();

        $document = $this->createTestDocument();
        $document->setServerState('temporary');
        $docId = $document->store();

        $this->enrichmentKey = EnrichmentKey::new();
        $this->enrichmentKey->setName('Foo2Title');
        $this->enrichmentKey->store();

        $data = [
            'PersonSubmitterFirstName_1'         => ['value' => 'Hans', 'datatype' => 'Person', 'subfield' => '0'],
            'PersonSubmitterLastName_1'          => ['value' => 'Hansmann', 'datatype' => 'Person', 'subfield' => '1'],
            'PersonSubmitterEmail_1'             => ['value' => 'test@mail.com', 'datatype' => 'Person', 'subfield' => '1'],
            'PersonSubmitterPlaceOfBirth_1'      => ['value' => 'Stadt', 'datatype' => 'Person', 'subfield' => '1'],
            'PersonSubmitterDateOfBirth_1'       => ['value' => '1970/02/01', 'datatype' => 'Person', 'subfield' => '1'],
            'PersonSubmitterAcademicTitle_1'     => ['value' => 'Dr.', 'datatype' => 'Person', 'subfield' => '1'],
            'PersonSubmitterAllowEmailContact_1' => ['value' => '0', 'datatype' => 'Person', 'subfield' => '1'],
            'CompletedDate'                      => ['value' => '2012/2/1', 'datatype' => 'Date', 'subfield' => '0'],
            'PersonAuthorFirstName_1'            => ['value' => 'vorname', 'datatype' => 'Person', 'subfield' => '1'],
            'PersonAuthorLastName_1'             => ['value' => 'nachname', 'datatype' => 'Person', 'subfield' => '0'],
            'PersonAuthorLastName_2'             => ['value' => 'nurNachname', 'datatype' => 'Person', 'subfield' => '0'],
            'TitleMain_1'                        => ['value' => 'Entenhausen', 'datatype' => 'Title', 'subfield' => '0'],
            'TitleMainLanguage_1'                => ['value' => 'deu', 'datatype' => 'Language', 'subfield' => '1'],
            'TitleMain_2'                        => ['value' => 'Irgendwas sonst', 'datatype' => 'Title', 'subfield' => '0'],
            'TitleMainLanguage_2'                => ['value' => 'eng', 'datatype' => 'Language', 'subfield' => '1'],
            'Language'                           => ['value' => 'deu', 'datatype' => 'Language', 'subfield' => '0'],
            'Note'                               => ['value' => 'Dies ist ein Kommentar', 'datatype' => 'Note', 'subfield' => '0'],
            'Licence'                            => ['value' => '3', 'datatype' => 'Licence', 'subfield' => '0'],
            'ThesisGrantor'                      => ['value' => '1', 'datatype' => 'ThesisGrantor', 'subfield' => '0'],
            'ThesisPublisher'                    => ['value' => '2', 'datatype' => 'ThesisPublisher', 'subfield' => '0'],
            'ThesisYearAccepted'                 => ['value' => '2009', 'datatype' => 'Year', 'subfield' => '0'],
            'SubjectSwd_1'                       => ['value' => 'hallo098', 'datatype' => 'Subject', 'subfield' => '0'],
            'SubjectUncontrolled_1'              => ['value' => 'Keyword', 'datatype' => 'Subject', 'subfield' => '0'],
            'SubjectUncontrolledLanguage_1'      => ['value' => 'deu', 'datatype' => 'Language', 'subfield' => '1'],
            'SubjectMSC_1'                       => ['value' => '8030', 'datatype' => 'Collection', 'subfield' => '0'],
            'SubjectJEL_1'                       => ['value' => '6740', 'datatype' => 'Collection', 'subfield' => '0'],
            'SubjectPACS_1'                      => ['value' => '2878', 'datatype' => 'Collection', 'subfield' => '0'],
            'SubjectBKL_1'                       => ['value' => '13874', 'datatype' => 'Collection', 'subfield' => '0'],
            'IdentifierOld'                      => ['value' => 'Publish_Model_DepositTest_old', 'datatype' => 'Identifier', 'subfield' => '0'],
            'IdentifierSerial'                   => ['value' => 'Publish_Model_DepositTest_serial', 'datatype' => 'Identifier', 'subfield' => '0'],
            'IdentifierUuid'                     => ['value' => 'Publish_Model_DepositTest_uuid', 'datatype' => 'Identifier', 'subfield' => '0'],
            'IdentifierIsbn'                     => ['value' => 'Publish_Model_DepositTest_isbn', 'datatype' => 'Identifier', 'subfield' => '0'],
            'IdentifierDoi'                      => ['value' => 'Publish_Model_DepositTest_doi', 'datatype' => 'Identifier', 'subfield' => '0'],
            'IdentifierHandle'                   => ['value' => 'Publish_Model_DepositTest_handle', 'datatype' => 'Identifier', 'subfield' => '0'],
            'IdentifierUrn'                      => ['value' => 'Publish_Model_DepositTest_urn', 'datatype' => 'Identifier', 'subfield' => '0'],
            'IdentifierUrl'                      => ['value' => 'Publish_Model_DepositTest_url', 'datatype' => 'Identifier', 'subfield' => '0'],
            'IdentifierIssn'                     => ['value' => 'Publish_Model_DepositTest_issn', 'datatype' => 'Identifier', 'subfield' => '0'],
            'IdentifierStdDoi'                   => ['value' => 'Publish_Model_DepositTest_std-doi', 'datatype' => 'Identifier', 'subfield' => '0'],
            'IdentifierArxiv'                    => ['value' => 'Publish_Model_DepositTest_arxiv', 'datatype' => 'Identifier', 'subfield' => '0'],
            'IdentifierPubmed'                   => ['value' => 'Publish_Model_DepositTest_pmid', 'datatype' => 'Identifier', 'subfield' => '0'],
            'IdentifierCrisLink'                 => ['value' => 'Publish_Model_DepositTest_cris-link', 'datatype' => 'Identifier', 'subfield' => '0'],
            'IdentifierSplashUrl'                => ['value' => 'Publish_Model_DepositTest_splash-url', 'datatype' => 'Identifier', 'subfield' => '0'],
            'IdentifierOpus3'                    => ['value' => 'Publish_Model_DepositTest_opus3-id', 'datatype' => 'Identifier', 'subfield' => '0'],
            'IdentifierOpac'                     => ['value' => 'Publish_Model_DepositTest_opac-id', 'datatype' => 'Identifier', 'subfield' => '0'],
            'ReferenceIsbn'                      => ['value' => 'Publish_Model_DepositTest_ref_isbn', 'datatype' => 'Reference', 'subfield' => '0'],
            'ReferenceUrn'                       => ['value' => 'Publish_Model_DepositTest_ref_urn', 'datatype' => 'Reference', 'subfield' => '0'],
            'ReferenceHandle'                    => ['value' => 'Publish_Model_DepositTest_ref_handle', 'datatype' => 'Reference', 'subfield' => '0'],
            'ReferenceDoi'                       => ['value' => 'Publish_Model_DepositTest_ref_doi', 'datatype' => 'Reference', 'subfield' => '0'],
            'ReferenceIssn'                      => ['value' => 'Publish_Model_DepositTest_ref_issn', 'datatype' => 'Reference', 'subfield' => '0'],
            'ReferenceUrl'                       => ['value' => 'Publish_Model_DepositTest_ref_url', 'datatype' => 'Reference', 'subfield' => '0'],
            'ReferenceCrisLink'                  => ['value' => 'Publish_Model_DepositTest_ref_crislink', 'datatype' => 'Reference', 'subfield' => '0'],
            'ReferenceStdDoi'                    => ['value' => 'Publish_Model_DepositTest_ref_stddoi', 'datatype' => 'Reference', 'subfield' => '0'],
            'ReferenceSplashUrl'                 => ['value' => 'Publish_Model_DepositTest_ref_splashurl', 'datatype' => 'Reference', 'subfield' => '0'],
            'SeriesNumber1'                      => ['value' => '5', 'datatype' => 'SeriesNumber', 'subfield' => '0'],
            'Series1'                            => ['value' => '4', 'datatype' => 'Series', 'subfield' => '1'],
            'Foo2Title'                          => ['value' => 'title as enrichment', 'datatype' => 'Enrichment', 'subfield' => '0'],
        ];

        $log = Log::get();

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

        // the order here matches the order in of identifiers in $data
        $identifierTypes = [
            'old',
            'serial',
            'uuid',
            'isbn',
            'doi',
            'handle',
            'urn',
            'url',
            'issn',
            'std-doi',
            'arxiv',
            'pmid',
            'cris-link',
            'splash-url',
            'opus3-id',
            'opac-id',
        ];

        $identifiers = $document->getIdentifier();
        $this->assertCount(16, $identifiers);

        foreach ($identifiers as $index => $identifier) {
            $type = $identifierTypes[$index];
            $this->assertEquals("Publish_Model_DepositTest_$type", $document->getIdentifier($index)->getValue());
            $this->assertEquals($type, $document->getIdentifier($index)->getType());
        }

        $this->assertEquals(5, $document->getSeries(0)->getNumber());
        $this->assertEquals(4, $document->getSeries(0)->getModel()->getId());

        $this->assertEquals('title as enrichment', $document->getEnrichment(0)->getValue());
    }

    /**
     * OPUSVIER-3713
     */
    public function testCastStringToDate()
    {
        $this->useEnglish();

        $deposit = new Publish_Model_Deposit(Application_Configuration::getInstance()->getLogger());

        $date = $deposit->castStringToOpusDate('2017/03/12');

        $this->assertInstanceOf(Date::class, $date);

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

        $this->assertInstanceOf(Date::class, $date);

        $this->assertEquals('2017', $date->getYear());

        $this->assertNotEquals('12', $date->getMonth());
        $this->assertEquals('03', $date->getMonth());

        $this->assertNotEquals('03', $date->getDay());
        $this->assertEquals('12', $date->getDay());
    }
}
