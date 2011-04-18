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
 * @author      Susanne Gottwald <gottwald@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
class Publish_FormControllerTest extends ControllerTestCase {

     /**
     * Send Button was pressed but the post is invalid (missing first name)
     */
    public function testCheckActionWithValidPostAndSendButton() {
        $session = new Zend_Session_Namespace('Publish');
        $session->unsetAll();
        $session->documentType = 'preprint';
        $session->documentId = '750';
        $session->fulltext = '0';
        $session->additionalFields = array();

        $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'PersonSubmitterFirstName1' => 'John',
                    'PersonSubmitterLastName1' => 'Doe',
                    'PersonSubmitterEmail1' => 'doe@example.org',
                    'TitleMain1' => 'Entenhausen',
                    'TitleMainLanguage1' => 'eng',
                    'TitleAbstract1' => 'Testabsatz',
                    'TitleAbstractLanguage1' => 'deu',
                    'PersonAuthorFirstName1' => '',
                    'PersonAuthorLastName1' => '',
                    'PersonAuthorAcademicTitle1' => 'Dr.',
                    'PersonAuthorEmail1' => '',
                    'PersonAuthorAllowEmailContact1' => '0',
                    'PersonAuthorDateOfBirth1' => '',
                    'PersonAuthorPlaceOfBirth1' => '',
                    'CompletedDate' => '2011/02/22',
                    'PageNumber' => '',
                    'SubjectUncontrolled1' => '',
                    'Institute' => '',
                    'IdentifierUrn' => '',
                    'Note' => '',
                    'Language' => 'deu',
                    'Licence' => 'ID:4',
                    'send' => 'Next step'
                ));

        $this->dispatch('/publish/form/check');
        //$this->assertResponseCode(302);
        $this->assertController('form');
        $this->assertAction('check');
    }
    
    

    /**
     * Test GET on upload action
     */
    public function testUploadActionWithOutPost() {
        $this->dispatch('/publish/form/upload');
        $this->assertResponseCode(302);
        $this->assertController('form');
        $this->assertAction('upload');
    }    

    /**
     * Test upload action with empty POST array
     */
    public function testUploadActionWithEmptyPost() {
        $this->request
                ->setMethod('POST')
                ->setPost(array());

        $this->dispatch('/publish/form/upload');
        $this->assertResponseCode(302);
        $this->assertController('form');
        $this->assertAction('upload');
    }
 
    public function testUploadActionWithValidPost() {
        $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'documentType' => 'all',
                    'rights' => '1',
                    'send' => 'Next step'
                ));

        $this->dispatch('/publish/form/upload');
        $this->assertResponseCode(200);
        $this->assertController('form');
        $this->assertAction('upload');
    }

    /**
     * Test upload action with user-changed MAXFILESIZE in POST array
     */
    public function testUploadActionWithAttackedPost() {
        $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'MAX_FILE_SIZE' => 123
                ));

        $this->dispatch('/publish/form/upload');
        $this->assertResponseCode(302);
        $this->assertController('form');
        $this->assertAction('upload');
    }

    /**
     * Test upload action with invalid POST array
     */
    public function testUploadActionWithInvalidDummyPost() {
        $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'foo' => 'bar',
                ));

        $this->dispatch('/publish/form/upload');
        $this->assertResponseCode(200);
        $this->assertController('form');
        $this->assertAction('upload');
    }

    /**
     * Test check action with GET
     */
    public function testCheckActionWithoutPost() {
        $this->dispatch('/publish/form/check');
        $this->assertResponseCode(302);
        $this->assertController('form');
        $this->assertAction('check');
    }   

    /**
     * Add Button was pressed and the post is valid
     */
    public function testCheckActionWithValidPostAndAddButton() {
        $session = new Zend_Session_Namespace('Publish');
        $session->unsetAll();
        $session->documentType = 'preprint';
        $session->documentId = '900';
        $session->fulltext = '0';
        $session->additionalFields = array();

        $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'PersonSubmitterFirstName1' => 'John',
                    'PersonSubmitterLastName1' => 'Doe',
                    'PersonSubmitterEmail1' => 'doe@example.org',
                    'TitleMain1' => 'Entenhausen',
                    'TitleMainLanguage1' => 'eng',
                    'TitleAbstract1' => 'Testabsatz',
                    'TitleAbstractLanguage1' => 'deu',
                    'PersonAuthorFirstName1' => '',
                    'PersonAuthorLastName1' => '',
                    'PersonAuthorAcademicTitle1' => 'Dr.',
                    'PersonAuthorEmail1' => '',
                    'PersonAuthorAllowEmailContact1' => '0',
                    'PersonAuthorDateOfBirth1' => '',
                    'PersonAuthorPlaceOfBirth1' => '',
                    'CompletedDate' => '2011/02/22',
                    'PageNumber' => '',
                    'SubjectUncontrolled1' => '',
                    'Institute' => '',
                    'IdentifierUrn' => '',
                    'Note' => '',
                    'Language' => 'deu',
                    'Licence' => 'ID:4',
                    'addMoreTitleMain' => 'Einen weiteren Titel hinzufügen'
                ));

        $this->dispatch('/publish/form/check');
        $this->assertResponseCode(200);
        $this->assertController('form');
        $this->assertAction('check');
    }
    
    /**
     * Abort from check page
     */
    public function testCheckActionWithAbortInPost() {
        $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'abort' => '',
                ));

        $this->dispatch('/publish/form/check');
        $this->assertResponseCode(302);
        $this->assertController('form');
        $this->assertAction('check');
    }

    /**
     * Abort from Collection view
     */
    public function testCheckActionWithAbortCollectionInPost() {
        $session = new Zend_Session_Namespace('Publish');
        $elemente = array(
            1 => array('name' => 'PersonSubmitterFirstName1', 'value' => 'Hans'),
            2 => array('name' => 'PersonSubmitterLastName1', 'value' => 'Hansmann'),
            3 => array('name' => 'PersonSubmitterEmail1', 'value' => 'test@mail.com'),
            4 => array('name' => 'CompletedDate', 'value' => '2011/03/03'),
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
        $session->fulltext = '0';
        $session->additionalFields = array();

        $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'abortCollection' => '',
                ));

        $this->dispatch('/publish/form/check');
        $this->assertResponseCode(200);
        $this->assertController('form');
        $this->assertAction('check');
    }

}

?>
