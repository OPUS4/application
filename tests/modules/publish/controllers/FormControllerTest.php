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
 * @copyright   Copyright (c) 2008-2011, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class Publish_FormControllerTest extends ControllerTestCase {

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
 
    /**
     * Test upload action with correct form entries.
     * Test always fails because filupload validation fails.
     * => replaced by a selnium test
     */
//    public function testUploadActionWithValidPost() {
//        $config = Zend_Registry::get('Zend_Config');        
//        $config->form->first->require_upload = 0;
//        $config->publish->maxfilesize = 1024;
//        $config->documentTypes->include = 'all,preprint,article,demo,workingpaper';
//        
//        $this->request
//                ->setMethod('POST')
//                ->setPost(array(
//                    'documentType' => 'all',
//                    'MAX_FILE_SIZE' => '10240000',
//                    'rights' => '0',
//                    'rights' => '1',                    
//                    'uploadComment' => '',
//                    'send' => 'Weiter zum nächsten Schritt'
//                ));
//
//        $this->dispatch('/publish/form/upload');        
//        $this->assertResponseCode(200);
//        $this->assertController('form');
//        $this->assertAction('check');
//    }

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
        $session->documentType = 'preprint';
        $session->documentId = '950';
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
                    'CompletedDate' => '2011/04/20',
                    'PageNumber' => '',
                    'SubjectUncontrolled1' => '',
                    'Institute' => '',
                    'IdentifierUrn' => '',
                    'Note' => '',
                    'Language' => 'deu',
                    'Licence' => 'ID:1',
                    'Series1' => '',
                    'SeriesNumber1' => '',
                    'addMoreTitleMain' => 'Add one more title main'
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
     * Send Button was pressed but the post is invalid (missing first name)
     */
    public function testCheckActionWithValidPostAndSendButton() {
        $session = new Zend_Session_Namespace('Publish');
        //$session->unsetAll();
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
                    'send' => 'Weiter zum nächsten Schritt'
                ));

        $this->dispatch('/publish/form/check');
        $this->assertResponseCode(200);
        $this->assertController('form');
        $this->assertAction('check');
    }      
}

?>
