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
 * @package     Tests
 * @author      Thoralf Klein <thoralf.klein@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
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
            1 => array('name' => 'PersonSubmitterFirstName1', 'value' => 'Hans'),
            2 => array('name' => 'PersonSubmitterLastName1', 'value' => 'Hansmann'),
            3 => array('name' => 'PersonSubmitterEmail1', 'value' => 'test@mail.com'),
            4 => array('name' => 'CompletedDate', 'value' => '2011/03/03'),
            5 => array('name' => 'CompletedDate', 'value' => '2011/03/03'),
            5 => array('name' => 'EnrichmentLegalNotices', 'value' => '1'),
            6 => array('name' => 'TitleMain1', 'value' => 'Irgendwas'),
            7 => array('name' => 'TitleMainLanguage1', 'value' => 'deu')
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
     * which leads to a OK Message, code 200 and Saving of all document data
     */
    public function testDepositActionWithValidPostAndCollectionButton() {
        $session = new Zend_Session_Namespace('Publish');
        $elemente = array(
            1 => array('name' => 'PersonSubmitterFirstName1', 'value' => 'Hans'),
            2 => array('name' => 'PersonSubmitterLastName1', 'value' => 'Hansmann'),
            3 => array('name' => 'PersonSubmitterEmail1', 'value' => 'test@mail.com'),
            4 => array('name' => 'CompletedDate', 'value' => '2011/03/03'),
            5 => array('name' => 'CompletedDate', 'value' => '2011/03/03'),
            5 => array('name' => 'EnrichmentLegalNotices', 'value' => '1'),
            6 => array('name' => 'TitleMain1', 'value' => 'Irgendwas'),
            7 => array('name' => 'TitleMainLanguage1', 'value' => 'deu')
        );
        $session->elements = $elemente;
        
        $doc = new Opus_Document();
        $doc->setType('preprint');
        $doc->setServerState('temporary');
        $docId = $doc->store();
        $session->documentType = 'preprint';
        $session->documentId = $docId;

        $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'collection' => ''
                ));

        $this->dispatch('/publish/deposit/deposit');
        $this->assertResponseCode(200);
        $this->assertController('collection');
        $this->assertAction('top');
    }

    /**
     * Method tests the deposit action with a valid POST request
     * which leads to a OK Message, code 200 and Saving of all document data
     */
    public function testDepositActionWithValidPostAndSendButton() {
        $session = new Zend_Session_Namespace('Publish');
        $elemente = array(
            1 => array('name' => 'PersonSubmitterFirstName1', 'value' => 'Hans'),
            2 => array('name' => 'PersonSubmitterLastName1', 'value' => 'Hansmann'),
            3 => array('name' => 'PersonSubmitterEmail1', 'value' => 'test@mail.com'),
            4 => array('name' => 'PersonSubmitterPlaceOfBirth1', 'value' => 'Stadt'),
            5 => array('name' => 'PersonSubmitterDateOfBirth1', 'value' => '1970/01/01'),
            6 => array('name' => 'PersonSubmitterAcademicTitle1', 'value' => 'Dr.'),
            7 => array('name' => 'PersonSubmitterAllowEmailContact1', 'value' => '0'),
            8 => array('name' => 'CompletedDate', 'value' => '2011/03/03'),
            9 => array('name' => 'CompletedDate', 'value' => '2011/03/03'),
            10 => array('name' => 'EnrichmentLegalNotices', 'value' => '1'),
            11 => array('name' => 'TitleMain1', 'value' => 'Irgendwas'),
            12 => array('name' => 'TitleMainLanguage1', 'value' => 'deu'),
            13 => array('name' => 'TitleMain2', 'value' => 'Irgendwas sonst'),
            14 => array('name' => 'TitleMainLanguage2', 'value' => 'eng'),
            15 => array('name' => 'Language', 'value' => 'deu'),
            16 => array('name' => 'SubjectUncontrolled1', 'value' => 'Keyword'),
            17 => array('name' => 'Note', 'value' => 'Dies ist ein Kommentar'),
            18 => array('name' => 'Licence', 'value' => 'ID_1'),
            19 => array('name' => 'IdentifierUrn', 'value' => 'blablup987'),
            20 => array('name' => 'Institute', 'value' => 'Freie Universität Berlin'),           
            21 => array('name' => 'ThesisGrantor', 'value' => 'ID:1'),
            22 => array('name' => 'SubjectMSC1', 'value' => '00A09'),
            23 => array('name' => 'SubjectSwd1', 'value' => 'hallo098'),
            24 => array('name' => 'ThesisPublisher', 'value' => 'ID:1'),
            25 => array('name' => 'SubjectPACS1', 'value' => '11.15.Bt'),
            26 => array('name' => 'IdentifierOld', 'value' => 'blablup987'),
            27 => array('name' => 'IdentifierSerial', 'value' => 'blablup987'),
            28 => array('name' => 'IdentifierUuid', 'value' => 'blablup987'),
            29 => array('name' => 'IdentifierIsbn', 'value' => 'blablup987'),
            30 => array('name' => 'IdentifierDoi', 'value' => 'blablup987'),
            31 => array('name' => 'IdentifierHandle', 'value' => 'blablup987'),
            32 => array('name' => 'IdentifierUrl', 'value' => 'blablup987'),
            33 => array('name' => 'IdentifierIssn', 'value' => 'blablup987'),
            34 => array('name' => 'IdentifierStdDoi', 'value' => 'blablup987'),
            35 => array('name' => 'IdentifierCrisLink', 'value' => 'blablup987'),
            36 => array('name' => 'IdentifierSplashUrl', 'value' => 'blablup987'),
            37 => array('name' => 'IdentifierOpus3', 'value' => 'blablup987'),
            38 => array('name' => 'IdentifierOpac', 'value' => 'blablup987'),
            39 => array('name' => 'ReferenceIsbn', 'value' => 'blablup987'),
            40 => array('name' => 'ReferenceUrn', 'value' => 'blablup987'),
            41 => array('name' => 'ReferenceHandle', 'value' => 'blablup987'),
            42 => array('name' => 'ReferenceDoi', 'value' => 'blablup987'),
            43 => array('name' => 'ReferenceIssn', 'value' => 'blablup987'),
            44 => array('name' => 'ReferenceUrl', 'value' => 'blablup987'),
            45 => array('name' => 'ReferenceCrisLink', 'value' => 'blablup987'),
            46 => array('name' => 'ReferenceStdDoi', 'value' => 'blablup987'),
            47 => array('name' => 'ReferenceSplashUrl', 'value' => 'blablup987')
        );
        $session->elements = $elemente;
        $session->documentType = 'preprint';
        $doc = new Opus_Document();
        $doc->setServerState('temporary');
        $doc->setType('preprint');
        $session->documentId = $doc->store();

        $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'send' => 'Abspeichern'
                ));

        $this->dispatch('/publish/deposit/deposit');        
//        $this->assertResponseCode(302);
//        $this->assertController('deposit');
//        $this->assertAction('deposit');
    }

    public function testConfirmAction() {
        $session = new Zend_Session_Namespace('Publish');
        $session->depositConfirmDocumentId = '712';
        $this->dispatch('/publish/deposit/confirm');
        $this->assertController('deposit');
        $this->assertAction('confirm');
    }
}

?>
