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
 * @package     Publish
 * @author      Susanne Gottwald <gottwald@zib.de>
 * @copyright   Copyright (c) 2008-2018, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Class Publish_DepositControllerTest.
 *
 * @covers Publish_DepositController
 */
class Publish_DepositControllerTest extends ControllerTestCase {

    /**
     * Method tests the deposit action with GET request which leads to a redirect (code 302)
     */
    public function testdepositActionWithoutPost() {
        $this->dispatch('/publish/deposit/deposit');
        $this->assertResponseCode(302);
        $this->assertController('deposit');
        $this->assertAction('deposit');
    }

    /**
     * Method tests the deposit action with invalid POST request
     * which leads to a Error Message and code 200
     */
    public function testDepositActionWithValidPostAndBackButton() {
        $session = new Zend_Session_Namespace('Publish');
        $elemente = array(
            1 => array('name' => 'PersonSubmitterFirstName_1','value' => 'Hans'),
            2 => array('name' => 'PersonSubmitterLastName_1','value' => 'Hansmann'),
            3 => array('name' => 'PersonSubmitterEmail_1','value' => 'test@mail.com'),
            4 => array('name' => 'CompletedDate', 'value' => '2011/03/03'),
            5 => array('name' => 'TitleMain_1','value' => 'Irgendwas'),
            6 => array('name' => 'TitleMainLanguage_1','value' => 'deu')
        );
        $session->elements = $elemente;
        $session->documentId = '712';
        $session->documentType = 'preprint';

        $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'back' => ''
                ));

        $this->dispatch('/publish/deposit/deposit');

        $this->assertResponseCode(200);
        $this->assertController('form');
        $this->assertAction('check');
    }

    /**
     * Method tests the deposit action with a valid POST request
     * which leads to a OK Message, code 302 and Saving of all document data
     */
    public function testDepositActionWithValidPostAndSendButton() {
        $session = new Zend_Session_Namespace('Publish');
        $elemente = array(
            1 => array('name' => 'PersonSubmitterFirstName_1', 'value' => 'Hans', 'datatype'=>'Person', 'subfield'=>'0'),
            2 => array('name' => 'PersonSubmitterLastName_1', 'value' => 'Hansmann', 'datatype'=>'Person', 'subfield'=>'1'),
            3 => array('name' => 'PersonSubmitterEmail_1', 'value' => 'test@mail.com', 'datatype'=>'Person', 'subfield'=>'1'),
            4 => array('name' => 'PersonSubmitterPlaceOfBirth_1', 'value' => 'Stadt', 'datatype'=>'Person', 'subfield'=>'1'),
            5 => array('name' => 'PersonSubmitterDateOfBirth_1', 'value' => '1970/01/01', 'datatype'=>'Person', 'subfield'=>'1'),
            6 => array('name' => 'PersonSubmitterAcademicTitle_1', 'value' => 'Dr.', 'datatype'=>'Person', 'subfield'=>'1'),
            7 => array('name' => 'PersonSubmitterAllowEmailContact_1', 'value' => '0', 'datatype'=>'Person', 'subfield'=>'1'),
            8 => array('name' => 'CompletedDate', 'value' => '2012/1/1', 'datatype'=>'Date', 'subfield'=>'0'),
            9 => array('name' => 'TitleMain_1', 'value' => 'Entenhausen', 'datatype'=>'Title', 'subfield'=>'0'),
            10 => array('name' => 'TitleMainLanguage_1', 'value' => 'deu', 'datatype'=>'Language', 'subfield'=>'1'),
            11 => array('name' => 'TitleMain_2','value' => 'Irgendwas sonst', 'datatype'=>'Title', 'subfield'=>'0'),
            12 => array('name' => 'TitleMainLanguage_2','value' => 'eng', 'datatype'=>'Language', 'subfield'=>'1'),
            13 => array('name' => 'Language','value' => 'deu', 'datatype'=>'Language', 'subfield'=>'0'),
            14 => array('name' => 'Note','value' => 'Dies ist ein Kommentar', 'datatype'=>'Note', 'subfield'=>'0'),
            15 => array('name' => 'Licence','value' => '3', 'datatype'=>'Licence', 'subfield'=>'0'),
            16 => array('name' => 'ThesisGrantor','value' => '1', 'datatype'=>'ThesisGrantor', 'subfield'=>'0'),
            17 => array('name' => 'ThesisPublisher' ,'value' => '2', 'datatype'=>'ThesisPublisher', 'subfield'=>'0'),
            18 => array('name' => 'SubjectSwd_1','value' => 'hallo098', 'datatype'=>'Subject', 'subfield'=>'0'),
            19 => array('name' => 'SubjectUncontrolled_1','value' => 'Keyword', 'datatype'=>'Subject', 'subfield'=>'0'),
            20 => array('name' => 'SubjectUncontrolledLanguage_1','value' => 'deu', 'datatype'=>'Language', 'subfield'=>'1'),
            21 => array('name' => 'SubjectMSC_1' ,'value' => '8030', 'datatype'=>'Collection', 'subfield'=>'0'),
            22 => array('name' => 'SubjectPACS_1','value' => '2878', 'datatype'=>'Collection', 'subfield'=>'0'),
            23 => array('name' => 'IdentifierUrn' ,'value' => 'Publish_DepositControllerTest_testDepositActionWithValidPostAndSendButton', 'datatype'=>'Identifier', 'subfield'=>'0'),
            24 => array('name' => 'IdentifierOld' ,'value' => 'blablup987', 'datatype'=>'Identifier', 'subfield'=>'0'),
            25 => array('name' => 'IdentifierSerial','value' => 'blablup987', 'datatype'=>'Identifier', 'subfield'=>'0'),
            26 => array('name' => 'IdentifierUuid','value' => 'blablup987', 'datatype'=>'Identifier', 'subfield'=>'0'),
            27 => array('name' => 'IdentifierIsbn','value' => 'blablup987', 'datatype'=>'Identifier', 'subfield'=>'0'),
            28 => array('name' => 'IdentifierDoi','value' => 'blablup987', 'datatype'=>'Identifier', 'subfield'=>'0'),
            29 => array('name' => 'IdentifierHandle' ,'value' => 'blablup987', 'datatype'=>'Identifier', 'subfield'=>'0'),
            30 => array('name' => 'IdentifierUrl','value' => 'blablup987', 'datatype'=>'Identifier', 'subfield'=>'0'),
            31 => array('name' => 'IdentifierIssn','value' => 'blablup987', 'datatype'=>'Identifier', 'subfield'=>'0'),
            32 => array('name' => 'IdentifierStdDoi','value' => 'blablup987', 'datatype'=>'Identifier', 'subfield'=>'0'),
            33 => array('name' => 'IdentifierCrisLink','value' => 'blablup987', 'datatype'=>'Identifier', 'subfield'=>'0'),
            34 => array('name' => 'IdentifierSplashUrl','value' => 'blablup987', 'datatype'=>'Identifier', 'subfield'=>'0'),
            35 => array('name' => 'IdentifierOpus3','value' => 'blablup987', 'datatype'=>'Identifier', 'subfield'=>'0'),
            36 => array('name' => 'IdentifierOpac','value' => 'blablup987', 'datatype'=>'Identifier', 'subfield'=>'0'),
            37 => array('name' => 'ReferenceIsbn','value' => 'blablup987', 'datatype'=>'Reference', 'subfield'=>'0'),
            38 => array('name' => 'ReferenceUrn','value' => 'blablup987', 'datatype'=>'Reference', 'subfield'=>'0'),
            39 => array('name' => 'ReferenceHandle' ,'value' => 'blablup987', 'datatype'=>'Reference', 'subfield'=>'0'),
            40 => array('name' => 'ReferenceDoi','value' => 'blablup987', 'datatype'=>'Reference', 'subfield'=>'0'),
            41 => array('name' => 'ReferenceIssn','value' => 'blablup987', 'datatype'=>'Reference', 'subfield'=>'0'),
            42 => array('name' => 'ReferenceUrl','value' => 'blablup987', 'datatype'=>'Reference', 'subfield'=>'0'),
            43 => array('name' => 'ReferenceCrisLink','value' => 'blablup987', 'datatype'=>'Reference', 'subfield'=>'0'),
            44 => array('name' => 'ReferenceStdDoi','value' => 'blablup987', 'datatype'=>'Reference', 'subfield'=>'0'),
            45 => array('name' => 'ReferenceSplashUrl','value' => 'blablup987', 'datatype'=>'Reference', 'subfield'=>'0'));

        $session->elements = $elemente;
        $session->documentType = 'preprint';
        $doc = $this->createTestDocument();
        $doc->setServerState('temporary');
        $doc->setType('preprint');
        $session->documentId = $doc->store();

        $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'send' => 'Save document'
                ));

        $this->dispatch('/publish/deposit/deposit');                
        
        $this->assertResponseCode(302);
        $this->assertController('deposit');
        $this->assertAction('deposit');        
    }

    public function testConfirmAction() {
        $session = new Zend_Session_Namespace('Publish');
        $session->depositConfirmDocumentId = '712';
        $this->dispatch('/publish/deposit/confirm');
        $this->assertController('deposit');
        $this->assertAction('confirm');
    }

    /**
     * Test that GET request on confirm action will result in
     * redirecting to index action (OPUSVIER-1680)
     */
    public function testGetConfirmActionResultsInRedirect() {
	$this->dispatch('/publish/deposit/confirm');
	$this->assertResponseCode(302);
        $this->assertResponseLocationHeader($this->getResponse(), '/publish');
    }

    /**
     * Method tests the deposit action with invalid POST request
     * which leads to a Error Message and code 200
     */
    public function testDepositActionWithAbortInPost() {
        $session = new Zend_Session_Namespace('Publish');
        $elemente = array(
            1 => array('name' => 'PersonSubmitterFirstName_1', 'value' => 'Hans'),
            2 => array('name' => 'PersonSubmitterLastName_1', 'value' => 'Hansmann'),
            3 => array('name' => 'PersonSubmitterEmail_1', 'value' => 'test@mail.com'),
            4 => array('name' => 'CompletedDate', 'value' => '2011/03/03'),
            5 => array('name' => 'TitleMain_1', 'value' => 'Irgendwas'),
            6 => array('name' => 'TitleMainLanguage_1', 'value' => 'deu')
        );

        $session->elements = $elemente;
        $session->documentType = 'preprint';
        $doc = $this->createTestDocument();
        $doc->setServerState('temporary');
        $doc->setType('preprint');
        $session->documentId = $doc->store();

        $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'abort' => ''
                ));

        $this->dispatch('/publish/deposit/deposit');       
        
        $this->assertResponseCode(302);
        $this->assertController('deposit');
        $this->assertAction('deposit');
    }

    /**
     * @expectedException Publish_Model_FormDocumentNotFoundException
     */
    public function testStoreExistingDocument()
    {
        $doc = $this->createTestDocument();
        $doc->setServerState('published');
        $doc->setType('preprint');

        $log = Zend_Registry::get('Zend_Log');
        $deposit = new Publish_Model_Deposit($log);
        $deposit->storeDocument($doc->store());
    }

}

