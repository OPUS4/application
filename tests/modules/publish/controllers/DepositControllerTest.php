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

use Opus\Common\Document;
use Opus\Common\Log;

/**
 * @covers Publish_DepositController
 */
class Publish_DepositControllerTest extends ControllerTestCase
{
    /** @var string */
    protected $additionalResources = 'all';

    /**
     * Method tests the deposit action with GET request which leads to a redirect (code 302)
     */
    public function testdepositActionWithoutPost()
    {
        $this->dispatch('/publish/deposit/deposit');
        $this->assertResponseCode(302);
        $this->assertController('deposit');
        $this->assertAction('deposit');
    }

    /**
     * Method tests the deposit action with invalid POST request
     * which leads to a Error Message and code 200
     */
    public function testDepositActionWithValidPostAndBackButton()
    {
        $session               = new Zend_Session_Namespace('Publish');
        $elemente              = [
            1 => ['name' => 'PersonSubmitterFirstName_1', 'value' => 'Hans'],
            2 => ['name' => 'PersonSubmitterLastName_1', 'value' => 'Hansmann'],
            3 => ['name' => 'PersonSubmitterEmail_1', 'value' => 'test@mail.com'],
            4 => ['name' => 'CompletedDate', 'value' => '2011/03/03'],
            5 => ['name' => 'TitleMain_1', 'value' => 'Irgendwas'],
            6 => ['name' => 'TitleMainLanguage_1', 'value' => 'deu'],
        ];
        $session->elements     = $elemente;
        $session->documentId   = '712';
        $session->documentType = 'preprint';

        $this->getRequest()
            ->setMethod('POST')
            ->setPost([
                'back' => '',
            ]);

        $this->dispatch('/publish/deposit/deposit');

        $this->assertResponseCode(200);
        $this->assertController('form');
        $this->assertAction('check');
    }

    /**
     * Method tests the deposit action with a valid POST request
     * which leads to a OK Message, code 302 and Saving of all document data
     */
    public function testDepositActionWithValidPostAndSendButton()
    {
        $session  = new Zend_Session_Namespace('Publish');
        $elemente = [
            1  => ['name' => 'PersonSubmitterFirstName_1', 'value' => 'Hans', 'datatype' => 'Person', 'subfield' => '0'],
            2  => ['name' => 'PersonSubmitterLastName_1', 'value' => 'Hansmann', 'datatype' => 'Person', 'subfield' => '1'],
            3  => ['name' => 'PersonSubmitterEmail_1', 'value' => 'test@mail.com', 'datatype' => 'Person', 'subfield' => '1'],
            4  => ['name' => 'PersonSubmitterPlaceOfBirth_1', 'value' => 'Stadt', 'datatype' => 'Person', 'subfield' => '1'],
            5  => ['name' => 'PersonSubmitterDateOfBirth_1', 'value' => '1970/01/01', 'datatype' => 'Person', 'subfield' => '1'],
            6  => ['name' => 'PersonSubmitterAcademicTitle_1', 'value' => 'Dr.', 'datatype' => 'Person', 'subfield' => '1'],
            7  => ['name' => 'PersonSubmitterAllowEmailContact_1', 'value' => '0', 'datatype' => 'Person', 'subfield' => '1'],
            8  => ['name' => 'CompletedDate', 'value' => '2012/1/1', 'datatype' => 'Date', 'subfield' => '0'],
            9  => ['name' => 'TitleMain_1', 'value' => 'Entenhausen', 'datatype' => 'Title', 'subfield' => '0'],
            10 => ['name' => 'TitleMainLanguage_1', 'value' => 'deu', 'datatype' => 'Language', 'subfield' => '1'],
            11 => ['name' => 'TitleMain_2', 'value' => 'Irgendwas sonst', 'datatype' => 'Title', 'subfield' => '0'],
            12 => ['name' => 'TitleMainLanguage_2', 'value' => 'eng', 'datatype' => 'Language', 'subfield' => '1'],
            13 => ['name' => 'Language', 'value' => 'deu', 'datatype' => 'Language', 'subfield' => '0'],
            14 => ['name' => 'Note', 'value' => 'Dies ist ein Kommentar', 'datatype' => 'Note', 'subfield' => '0'],
            15 => ['name' => 'Licence', 'value' => '3', 'datatype' => 'Licence', 'subfield' => '0'],
            16 => ['name' => 'ThesisGrantor', 'value' => '1', 'datatype' => 'ThesisGrantor', 'subfield' => '0'],
            17 => ['name' => 'ThesisPublisher', 'value' => '2', 'datatype' => 'ThesisPublisher', 'subfield' => '0'],
            18 => ['name' => 'SubjectSwd_1', 'value' => 'hallo098', 'datatype' => 'Subject', 'subfield' => '0'],
            19 => ['name' => 'SubjectUncontrolled_1', 'value' => 'Keyword', 'datatype' => 'Subject', 'subfield' => '0'],
            20 => ['name' => 'SubjectUncontrolledLanguage_1', 'value' => 'deu', 'datatype' => 'Language', 'subfield' => '1'],
            21 => ['name' => 'SubjectMSC_1', 'value' => '8030', 'datatype' => 'Collection', 'subfield' => '0'],
            22 => ['name' => 'SubjectPACS_1', 'value' => '2878', 'datatype' => 'Collection', 'subfield' => '0'],
            23 => ['name' => 'IdentifierUrn', 'value' => 'Publish_DepositControllerTest_testDepositActionWithValidPostAndSendButton', 'datatype' => 'Identifier', 'subfield' => '0'],
            24 => ['name' => 'IdentifierOld', 'value' => 'blablup987', 'datatype' => 'Identifier', 'subfield' => '0'],
            25 => ['name' => 'IdentifierSerial', 'value' => 'blablup987', 'datatype' => 'Identifier', 'subfield' => '0'],
            26 => ['name' => 'IdentifierUuid', 'value' => 'blablup987', 'datatype' => 'Identifier', 'subfield' => '0'],
            27 => ['name' => 'IdentifierIsbn', 'value' => 'blablup987', 'datatype' => 'Identifier', 'subfield' => '0'],
            28 => ['name' => 'IdentifierDoi', 'value' => 'blablup987', 'datatype' => 'Identifier', 'subfield' => '0'],
            29 => ['name' => 'IdentifierHandle', 'value' => 'blablup987', 'datatype' => 'Identifier', 'subfield' => '0'],
            30 => ['name' => 'IdentifierUrl', 'value' => 'blablup987', 'datatype' => 'Identifier', 'subfield' => '0'],
            31 => ['name' => 'IdentifierIssn', 'value' => 'blablup987', 'datatype' => 'Identifier', 'subfield' => '0'],
            32 => ['name' => 'IdentifierStdDoi', 'value' => 'blablup987', 'datatype' => 'Identifier', 'subfield' => '0'],
            33 => ['name' => 'IdentifierCrisLink', 'value' => 'blablup987', 'datatype' => 'Identifier', 'subfield' => '0'],
            34 => ['name' => 'IdentifierSplashUrl', 'value' => 'blablup987', 'datatype' => 'Identifier', 'subfield' => '0'],
            35 => ['name' => 'IdentifierOpus3', 'value' => 'blablup987', 'datatype' => 'Identifier', 'subfield' => '0'],
            36 => ['name' => 'IdentifierOpac', 'value' => 'blablup987', 'datatype' => 'Identifier', 'subfield' => '0'],
            37 => ['name' => 'ReferenceIsbn', 'value' => 'blablup987', 'datatype' => 'Reference', 'subfield' => '0'],
            38 => ['name' => 'ReferenceUrn', 'value' => 'blablup987', 'datatype' => 'Reference', 'subfield' => '0'],
            39 => ['name' => 'ReferenceHandle', 'value' => 'blablup987', 'datatype' => 'Reference', 'subfield' => '0'],
            40 => ['name' => 'ReferenceDoi', 'value' => 'blablup987', 'datatype' => 'Reference', 'subfield' => '0'],
            41 => ['name' => 'ReferenceIssn', 'value' => 'blablup987', 'datatype' => 'Reference', 'subfield' => '0'],
            42 => ['name' => 'ReferenceUrl', 'value' => 'blablup987', 'datatype' => 'Reference', 'subfield' => '0'],
            43 => ['name' => 'ReferenceCrisLink', 'value' => 'blablup987', 'datatype' => 'Reference', 'subfield' => '0'],
            44 => ['name' => 'ReferenceStdDoi', 'value' => 'blablup987', 'datatype' => 'Reference', 'subfield' => '0'],
            45 => ['name' => 'ReferenceSplashUrl', 'value' => 'blablup987', 'datatype' => 'Reference', 'subfield' => '0'],
        ];

        $session->elements     = $elemente;
        $session->documentType = 'preprint';
        $doc                   = $this->createTestDocument();
        $doc->setServerState('temporary');
        $doc->setType('preprint');
        $session->documentId = $doc->store();

        $this->getRequest()
            ->setMethod('POST')
            ->setPost([
                'send' => 'Save document',
            ]);

        $this->dispatch('/publish/deposit/deposit');

        $this->assertResponseCode(302);
        $this->assertController('deposit');
        $this->assertAction('deposit');

        $doc = Document::get($session->documentId);
        $this->assertEquals('unpublished', $doc->getServerState());
        $this->assertEquals('publish', $doc->getEnrichmentValue('opus.source'));
    }

    public function testConfirmAction()
    {
        $session                           = new Zend_Session_Namespace('Publish');
        $session->depositConfirmDocumentId = '712';
        $this->dispatch('/publish/deposit/confirm');
        $this->assertController('deposit');
        $this->assertAction('confirm');
    }

    /**
     * Test that GET request on confirm action will result in
     * redirecting to index action (OPUSVIER-1680)
     */
    public function testGetConfirmActionResultsInRedirect()
    {
        $this->dispatch('/publish/deposit/confirm');
        $this->assertResponseCode(302);
        $this->assertResponseLocationHeader($this->getResponse(), '/publish');
    }

    /**
     * Method tests the deposit action with invalid POST request
     * which leads to a Error Message and code 200
     */
    public function testDepositActionWithAbortInPost()
    {
        $session  = new Zend_Session_Namespace('Publish');
        $elemente = [
            1 => ['name' => 'PersonSubmitterFirstName_1', 'value' => 'Hans'],
            2 => ['name' => 'PersonSubmitterLastName_1', 'value' => 'Hansmann'],
            3 => ['name' => 'PersonSubmitterEmail_1', 'value' => 'test@mail.com'],
            4 => ['name' => 'CompletedDate', 'value' => '2011/03/03'],
            5 => ['name' => 'TitleMain_1', 'value' => 'Irgendwas'],
            6 => ['name' => 'TitleMainLanguage_1', 'value' => 'deu'],
        ];

        $session->elements     = $elemente;
        $session->documentType = 'preprint';
        $doc                   = $this->createTestDocument(); // Cleanup des Dokuments erfolgt im Publish-Modul
        $doc->setServerState('temporary');
        $doc->setType('preprint');
        $session->documentId = $doc->store();

        $this->getRequest()
            ->setMethod('POST')
            ->setPost([
                'abort' => '',
            ]);

        $this->dispatch('/publish/deposit/deposit');

        $this->assertResponseCode(302);
        $this->assertController('deposit');
        $this->assertAction('deposit');
    }

    public function testStoreExistingDocument()
    {
        $doc = $this->createTestDocument();
        $doc->setServerState('published');
        $doc->setType('preprint');

        $log     = Log::get();
        $deposit = new Publish_Model_Deposit($log);

        $this->expectException(Publish_Model_FormDocumentNotFoundException::class);
        $deposit->storeDocument($doc->store());
    }
}
