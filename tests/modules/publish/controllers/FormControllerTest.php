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
 * @author      Sascha Szott <szott@zib.de>
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2018, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Class Publish_FormControllerTest.
 *
 * @covers Publish_FormController
 */
class Publish_FormControllerTest extends ControllerTestCase {

    public function setUp() {
        parent::setUp();
        $this->useGerman();
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
        
        $body = $this->getResponse()->getBody();

        $this->assertContains('Es sind Fehler aufgetreten. Bitte beachten Sie die Fehlermeldungen an den Formularfeldern.', $body);
        $this->assertContains('Bitte wählen Sie einen Dokumenttyp aus der Liste aus.', $body);
        $this->assertContains("<div class='form-errors'>", $body);
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
     * "Add Title" Button was pressed and the post is valid
     */
    public function testCheckActionWithValidPostAndAddButton() {
        $doc = $this->createTemporaryDoc();

        $session = new Zend_Session_Namespace('Publish');
        $session->documentType = 'preprint';
        $session->documentId = $doc->getId();
        $session->fulltext = '0';
        $session->additionalFields = array();

        $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'PersonSubmitterLastName_1' => 'Doe',
                    'PersonSubmitterEmail_1' => 'doe@example.org',
                    'TitleMain_1' => 'Entenhausen',
                    'TitleMainLanguage_1' => 'deu',
                    'PersonAuthorLastName_1' => 'AuthorLastName',
                    'CompletedDate' => '22.01.2011',
                    'Language' => 'deu',
                    'Licence' => '4',
                    'addMoreTitleMain' => 'Add one more title main'
                ));

        $this->dispatch('/publish/form/check');

        $this->assertResponseCode(200);
        $this->assertController('form');
        $this->assertAction('check');

        $body = $this->getResponse()->getBody();
        
        $this->assertContains('TitleMain_1', $body);
        $this->assertContains('TitleMainLanguage_1', $body);
        $this->assertContains('TitleMain_2', $body);
        $this->assertContains('TitleMainLanguage_2', $body);

        $this->assertNotContains("<div class='form-errors'>", $body);
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
     * Send Button was pressed but the post is invalid (missing last name for author)
     */
    public function testCheckActionWithValidPostAndSendButton() {
        $doc = $this->createTemporaryDoc();

        $session = new Zend_Session_Namespace('Publish');
        $session->documentType = 'preprint';
        $session->documentId = $doc->getId();
        $session->fulltext = '0';
        $session->additionalFields = array();

        $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'PersonSubmitterLastName_1' => 'Doe',
                    'PersonSubmitterEmail_1' => 'doe@example.org',
                    'TitleMain_1' => 'Entenhausen',
                    'TitleMainLanguage_1' => 'deu',
                    'CompletedDate' => '22.01.2011',
                    'Language' => 'deu',
                    'Licence' => '4',
                    'send' => 'Weiter zum nächsten Schritt'
                ));

        $this->dispatch('/publish/form/check');

        $this->assertResponseCode(200);
        $this->assertController('form');
        $this->assertAction('check');
        $this->assertContains('Es sind Fehler aufgetreten. Bitte beachten Sie die Fehlermeldungen an den Formularfeldern.', $this->getResponse()->getBody());
        $this->assertContains("<div class='form-errors'>", $this->getResponse()->getBody());
    }

    public function testCheckActionWithValidPostAndSendButtonAndAllRequiredFields() {
        $doc = $this->createTemporaryDoc();

        $session = new Zend_Session_Namespace('Publish');
        $session->documentType = 'preprint';
        $session->documentId = $doc->getId();
        $session->fulltext = '0';
        $session->additionalFields = array();

        $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'PersonSubmitterLastName_1' => 'Doe',
                    'PersonSubmitterEmail_1' => 'doe@example.org',
                    'TitleMain_1' => 'Entenhausen',
                    'TitleMainLanguage_1' => 'deu',
                    'PersonAuthorLastName_1' => 'AuthorLastName',
                    'CompletedDate' => '22.01.2011',
                    'Language' => 'deu',
                    'Licence' => '4',
                    'ThesisPublisher_1' => '2',
                    'send' => 'Weiter zum nächsten Schritt'
                ));

        $this->dispatch('/publish/form/check');

        $this->assertResponseCode(200);
        $this->assertController('form');
        $this->assertAction('check');

        $this->assertNotContains('Es sind Fehler aufgetreten. Bitte beachten Sie die Fehlermeldungen an den Formularfeldern.', $this->getResponse()->getBody());
        $this->assertNotContains("<div class='form-errors'>", $this->getResponse()->getBody());

        $this->assertContains('Bitte überprüfen Sie Ihre Eingaben.', $this->getResponse()->getBody());
        $this->assertContains('<b>Kontaktdaten des Einstellers</b>', $this->getResponse()->getBody());
        $this->assertContains('<td>Doe</td>', $this->getResponse()->getBody());
        $this->assertContains('<td>doe@example.org</td>', $this->getResponse()->getBody());

        $this->assertContains('<b>Haupttitel</b>', $this->getResponse()->getBody());
        $this->assertContains('<td>Entenhausen</td>', $this->getResponse()->getBody());
        $this->assertQueryContentRegex('td', '/German|Deutsch/');

        $this->assertContains('<b>Autor(en)</b>', $this->getResponse()->getBody());
        $this->assertContains('<td>AuthorLastName</td>', $this->getResponse()->getBody());
        $this->assertContains('<td>Nein</td>', $this->getResponse()->getBody());

        $this->assertContains('<b>Weitere Formulardaten:</b>', $this->getResponse()->getBody());
        $this->assertContains('<td>22.01.2011</td>', $this->getResponse()->getBody());
        $this->assertContains('<td>Creative Commons - CC BY-ND - Namensnennung - Keine Bearbeitungen 4.0 International</td>', $this->getResponse()->getBody());
        $this->assertContains('<b>Es wurden keine Dateien hochgeladen. </b>', $this->getResponse()->getBody());
    }

    /**
     * Regression Test for OPUSVIER-1886
     */
    public function testOPUSVIER1886WithBibliography() {
        $config = Zend_Registry::get('Zend_Config');
        $oldval = null;
        if (isset($config->form->first->bibliographie)) {
            $oldval = $config->form->first->bibliographie;
        }
        $config->form->first->bibliographie = 1;

        $doc = $this->createTemporaryDoc();

        $session = new Zend_Session_Namespace('Publish');
        $session->documentType = 'demo';
        $session->documentId = $doc->getId();
        $session->fulltext = '0';
        $session->additionalFields = array();

        $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'PersonSubmitterFirstName_1' => 'John',
                    'PersonSubmitterLastName_1' => 'Doe',
                    'send' => 'Weiter zum nächsten Schritt'
                ));

        $this->dispatch('/publish/form/check');

        // undo config changes
        if (is_null($oldval)) {
            unset($config->form->first->bibliographie);
        } else {
            $config->form->first->bibliographie = $oldval;
        }

        $this->assertResponseCode(200);
        $this->assertContains('Bitte überprüfen Sie Ihre Eingaben.', $this->getResponse()->getBody());
        $this->assertContains('<legend>Bibliographie</legend>', $this->getResponse()->getBody());
        $this->assertContains('Dokument wird <b>nicht</b> zur Bibliographie hinzugefügt.', $this->getResponse()->getBody());
    }

    public function testOPUSVIER1886WithBibliographyUnselected() {
        $config = Zend_Registry::get('Zend_Config');
        $oldval = null;
        if (isset($config->form->first->bibliographie)) {
            $oldval = $config->form->first->bibliographie;
        }
        $config->form->first->bibliographie = 1;

        $doc = $this->createTemporaryDoc();
        $doc->setBelongsToBibliography(0);
        $doc->store();

        $session = new Zend_Session_Namespace('Publish');
        $session->documentType = 'demo';
        $session->documentId = $doc->getId();
        $session->fulltext = '0';
        $session->additionalFields = array();

        $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'PersonSubmitterFirstName_1' => 'John',
                    'PersonSubmitterLastName_1' => 'Doe',
                    'send' => 'Weiter zum nächsten Schritt'
                ));

        $this->dispatch('/publish/form/check');

        // undo config changes
        if (is_null($oldval)) {
            unset($config->form->first->bibliographie);
        } else {
            $config->form->first->bibliographie = $oldval;
        }

        $this->assertResponseCode(200);
        $this->assertContains('Bitte überprüfen Sie Ihre Eingaben.', $this->getResponse()->getBody());
        $this->assertContains('<legend>Bibliographie</legend>', $this->getResponse()->getBody());
        $this->assertContains('Dokument wird <b>nicht</b> zur Bibliographie hinzugefügt.', $this->getResponse()->getBody());
    }

    public function testOPUSVIER1886WithBibliographySelected() {
        $config = Zend_Registry::get('Zend_Config');
        $oldval = null;
        if (isset($config->form->first->bibliographie)) {
            $oldval = $config->form->first->bibliographie;
        }
        $config->form->first->bibliographie = 1;

        $doc = $this->createTemporaryDoc();
        $doc->setBelongsToBibliography(1);
        $doc->store();

        $session = new Zend_Session_Namespace('Publish');
        $session->documentType = 'demo';
        $session->documentId = $doc->getId();
        $session->fulltext = '0';
        $session->additionalFields = array();

        $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'PersonSubmitterFirstName_1' => 'John',
                    'PersonSubmitterLastName_1' => 'Doe',
                    'send' => 'Weiter zum nächsten Schritt'
                ));

        $this->dispatch('/publish/form/check');

        // undo config changes
        if (is_null($oldval)) {
            unset($config->form->first->bibliographie);
        } else {
            $config->form->first->bibliographie = $oldval;
        }

        $this->assertResponseCode(200);
        $this->assertContains('Bitte überprüfen Sie Ihre Eingaben.', $this->getResponse()->getBody());
        $this->assertContains('<legend>Bibliographie</legend>', $this->getResponse()->getBody());
        $this->assertContains('Dokument wird zur Bibliographie <b>hinzugefügt</b>.', $this->getResponse()->getBody());
    }

    /**
     * Regression Test for OPUSVIER-1886
     */
    public function testOPUSVIER1886WithoutBibliography() {
        $config = Zend_Registry::get('Zend_Config');
        $oldval = null;
        if (isset($config->form->first->bibliographie)) {
            $oldval = $config->form->first->bibliographie;
        }
        $config->form->first->bibliographie = 0;

        $doc = $this->createTemporaryDoc();

        $session = new Zend_Session_Namespace('Publish');
        $session->documentType = 'demo';
        $session->documentId = $doc->getId();
        $session->fulltext = '0';
        $session->additionalFields = array();

        $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'PersonSubmitterFirstName_1' => 'John',
                    'PersonSubmitterLastName_1' => 'Doe',
                    'send' => 'Weiter zum nächsten Schritt'
                ));

        $this->dispatch('/publish/form/check');

        // undo config changes
        if (is_null($oldval)) {
            unset($config->form->first->bibliographie);
        } else {
            $config->form->first->bibliographie = $oldval;
        }

        $this->assertResponseCode(200);
        $this->assertContains('Bitte überprüfen Sie Ihre Eingaben.', $this->getResponse()->getBody());
        $this->assertNotContains('<legend>Bibliographie</legend>', $this->getResponse()->getBody());
        $this->assertNotContains('Dokument wird <b>nicht</b> zur Bibliographie hinzugefügt.', $this->getResponse()->getBody());
    }

    /**
     * Regression Test for OPUSVIER-2646
     */
    public function testFormManipulationForBibliography() {
        $this->markTestIncomplete('testing multipart formdata not yet solved');
        $config = Zend_Registry::get('Zend_Config');
        $oldval = null;
        if (isset($config->form->first->bibliographie)) {
            $oldval = $config->form->first->bibliographie;
        }
        $config->form->first->bibliographie = 0;

        $this->request
                ->setMethod('POST')
                  ->setPost(array(
                    'documentType' => 'demo',
                    'MAX_FILE_SIZE' => '10240000',
                    'fileupload' => '',
                    'uploadComment' => '',
                    'bibliographie' => '1',
                    'rights' => '1',
                    'send' => 'Weiter zum nächsten Schritt',
                ));
        $this->dispatch('/publish/form/upload');
        $session = new Zend_Session_Namespace('Publish');

        // undo config changes
        if (is_null($oldval)) {
            unset($config->form->first->bibliographie);
        } else {
            $config->form->first->bibliographie = $oldval;
        }
        
        $doc = new Opus_Document($session->documentId);
        $belongsToBibliography = $doc->getBelongsToBibliography();
        $doc->deletePermanent();

        $this->assertResponseCode(200);
        $this->assertNotContains("Es sind Fehler aufgetreten.", $this->response->getBody());
        $this->assertFalse((boolean) $belongsToBibliography, 'Expected that document does not belong to bibliography');        
    }

    /**
     * @return Opus_Document
     */
    private function createTemporaryDoc() {
        $doc = $this->createTestDocument();
        $doc->setServerState('temporary');
        $doc->store();
        return $doc;
    }

    public function testDoNotShowFileNoticeOnSecondFormPageIfFileUploadIsDisabled() {
        $this->fileNoticeOnSecondFormPage(0);

        $this->assertContains('<h3 class="document-type" title="Dokumenttyp">Alle Felder (Testdokumenttyp)</h3>', $this->getResponse()->getBody());
        $this->assertNotContains('<legend>Sie haben folgende Datei(en) hochgeladen: </legend>', $this->getResponse()->getBody());
        $this->assertNotContains('<b>Es wurden keine Dateien hochgeladen. </b>', $this->getResponse()->getBody());
    }

    public function testDoNotShowFileNoticeOnThirdFormPageIfFileUploadIsDisabled() {
        $this->fileNoticeOnThirdFormPage(0);

        $this->assertResponseCode(200);
        $this->assertContains('Bitte überprüfen Sie Ihre Eingaben', $this->getResponse()->getBody());
        $this->assertNotContains('<legend>Sie haben folgende Datei(en) hochgeladen: </legend>', $this->getResponse()->getBody());
        $this->assertNotContains('<b>Es wurden keine Dateien hochgeladen. </b>', $this->getResponse()->getBody());
    }

    public function testShowFileNoticeOnSecondFormPageIfFileUploadIsEnabled() {
        $this->fileNoticeOnSecondFormPage(1);

        $this->assertContains('<h3 class="document-type" title="Dokumenttyp">Alle Felder (Testdokumenttyp)</h3>', $this->getResponse()->getBody());
        $this->assertContains('<legend>Sie haben folgende Datei(en) hochgeladen: </legend>', $this->getResponse()->getBody());
        $this->assertContains('<b>Es wurden keine Dateien hochgeladen. </b>', $this->getResponse()->getBody());
    }

    public function testShowFileNoticeOnThirdFormPageIfFileUploadIsEnabled() {
        $this->fileNoticeOnThirdFormPage(1);

        $this->assertResponseCode(200);
        $this->assertContains('Bitte überprüfen Sie Ihre Eingaben', $this->getResponse()->getBody());
        $this->assertContains('<legend>Sie haben folgende Datei(en) hochgeladen: </legend>', $this->getResponse()->getBody());
        $this->assertContains('<b>Es wurden keine Dateien hochgeladen. </b>', $this->getResponse()->getBody());
    }

    private function fileNoticeOnThirdFormPage($value) {
        $config = Zend_Registry::get('Zend_Config');
        $oldval = null;
        if (isset($config->form->first->enable_upload)) {
            $oldval = $config->form->first->enable_upload;
        }
        $config->form->first->enable_upload = $value;

        $doc = $this->createTemporaryDoc();

        $session = new Zend_Session_Namespace('Publish');
        $session->documentType = 'demo';
        $session->documentId = $doc->getId();
        $session->fulltext = '0';
        $session->additionalFields = array();

        $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'PersonSubmitterFirstName_1' => 'John',
                    'PersonSubmitterLastName_1' => 'Doe',
                    'send' => 'Weiter zum nächsten Schritt'
                ));

        $this->dispatch('/publish/form/check');

        // undo config changes
        if (is_null($oldval)) {
            unset($config->form->first->enable_upload);
        } else {
            $config->form->first->enable_upload = $oldval;
        }
    }

    private function fileNoticeOnSecondFormPage($value) {
        $config = Zend_Registry::get('Zend_Config');
        $oldval = null;
        if (isset($config->form->first->enable_upload)) {
            $oldval = $config->form->first->enable_upload;
        }
        $config->form->first->enable_upload = $value;

        $doc = $this->createTemporaryDoc();

        $session = new Zend_Session_Namespace('Publish');
        $session->documentType = 'all';
        $session->documentId = $doc->getId();
        $session->fulltext = '0';
        $session->additionalFields = array();

        $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'addMoreTitleMain' => 'Add one more title main'
                ));

        $this->dispatch('/publish/form/check');

        // undo config changes
        if (is_null($oldval)) {
            unset($config->form->first->enable_upload);
        } else {
            $config->form->first->enable_upload = $oldval;
        }
    }

    private function addTestDocument($session, $documentType) {
        $doc = $this->createTemporaryDoc();

        $session->documentType = $documentType;
        $session->documentId = $doc->getId();
        $session->additionalFields = array();
    }

    /**
     * Button pressed: Add one more Title Main
     */
    public function testCheckActionWithAddButton() {
        $session = new Zend_Session_Namespace('Publish');
        $this->addTestDocument($session, 'preprint');
        $data = array(
            'PersonSubmitterFirstName_1' => '',
            'PersonSubmitterLastName_1' => '',
            'PersonSubmitterEmail_1' => '',
            'TitleMain_1' => '',
            'TitleMainLanguage_1' => '',
            'TitleAbstract_1' => '',
            'TitleAbstractLanguage_1' => '',
            'PersonAuthorFirstName_1' => '',
            'PersonAuthorLastName_1' => '',
            'PersonAuthorAcademicTitle_1' => '',
            'PersonAuthorEmail_1' => '',
            'PersonAuthorAllowEmailContact_1' => '0',
            'PersonAuthorDateOfBirth_1' => '',
            'PersonAuthorPlaceOfBirth_1' => '',
            'CompletedYear' => '',
            'CompletedDate' => '07.09.2011',
            'PageNumber' => '',
            'SubjectUncontrolled_1' => '',
            'SubjectUncontrolledLanguage_1' => '',
            'Institute_1' => '',
            'IdentifierUrn' => '',
            'Note' => '',
            'Language' => 'deu',
            'Licence' => '',
            'SeriesNumber_1' => '',
            'Series_1' => '',

            // Add Button wurde gedrückt
            'addMoreTitleMain' => 'Einen+weiteren+Titel+hinzufügen',
        );

        $this->request
            ->setMethod('POST')
            ->setPost($data);
        $this->dispatch('/publish/form/check');
        $this->assertResponseCode(200);

        $this->assertEquals(5, count($session->additionalFields));
        $this->assertEquals('2', $session->additionalFields['TitleMain']);
        $this->assertEquals(1, $session->additionalFields['stepInstitute_1']);
        $this->assertEquals('1', $session->additionalFields['collId0Institute_1']);
    }

    /**
     * Button pressed: Delete the last Title Main
     */
    public function testCheckActionWithDeleteButton() {
        $session = new Zend_Session_Namespace('Publish');
        $this->addTestDocument($session, 'preprint');
        $session->additionalFields['TitleMain'] = '2';

        $data = array(
            'PersonSubmitterFirstName_1' => '',
            'PersonSubmitterLastName_1' => '',
            'PersonSubmitterEmail_1' => '',
            'TitleMain_1' => '',
            'TitleMainLanguage_1' => '',
            'TitleMain_2' => '',
            'TitleMainLanguage_2' => '',
            'TitleAbstract_1' => '',
            'TitleAbstractLanguage_1' => '',
            'PersonAuthorFirstName_1' => '',
            'PersonAuthorLastName_1' => '',
            'PersonAuthorAcademicTitle_1' => '',
            'PersonAuthorEmail_1' => '',
            'PersonAuthorAllowEmailContact_1' => '0',
            'PersonAuthorDateOfBirth_1' => '',
            'PersonAuthorPlaceOfBirth_1' => '',
            'CompletedYear' => '',
            'CompletedDate' => '07.09.2011',
            'PageNumber' => '',
            'SubjectUncontrolled_1' => '',
            'SubjectUncontrolledLanguage_1' => '',
            'Institute_1' => '',
            'IdentifierUrn' => '',
            'Note' => '',
            'Language' => 'deu',
            'Licence' => '',
            'SeriesNumber_1' => '',
            'Series_1' => '',

            // Delete Button wurde gedrückt
            'deleteMoreTitleMain' => 'Den+letzten+Titel+löschen',
        );

        $this->request
            ->setMethod('POST')
            ->setPost($data);
        $this->dispatch('/publish/form/check');
        $this->assertEquals('200', $this->getResponse()->getHttpResponseCode());

        $this->assertEquals(5, count($session->additionalFields));
        $this->assertEquals(1, $session->additionalFields['TitleMain']);
        $this->assertEquals(1, $session->additionalFields['stepInstitute_1']);
        $this->assertEquals('1', $session->additionalFields['collId0Institute_1']);
    }

    /**
     * Button pressed: Browse down Institute
     */
    public function testCheckActionWithBrowseDownButton() {
        $session = new Zend_Session_Namespace('Publish');
        $this->addTestDocument($session, 'preprint');
        $session->additionalFields['Institute'] = '1';
        $session->additionalFields['collId0Institute_1'] = '1';
        $session->additionalFields['stepInstitute_1'] = '1';

        $data = array(
            'PersonSubmitterFirstName_1' => '',
            'PersonSubmitterLastName_1' => '',
            'PersonSubmitterEmail_1' => '',
            'TitleMain_1' => '',
            'TitleMainLanguage_1' => '',
            'TitleAbstract_1' => '',
            'TitleAbstractLanguage_1' => '',
            'PersonAuthorFirstName_1' => '',
            'PersonAuthorLastName_1' => '',
            'PersonAuthorAcademicTitle_1' => '',
            'PersonAuthorEmail_1' => '',
            'PersonAuthorAllowEmailContact_1' => '0',
            'PersonAuthorDateOfBirth_1' => '',
            'PersonAuthorPlaceOfBirth_1' => '',
            'CompletedYear' => '',
            'CompletedDate' => '07.09.2011',
            'PageNumber' => '',
            'SubjectUncontrolled_1' => '',
            'SubjectUncontrolledLanguage_1' => '',
            'Institute_1' => '15994',
            'IdentifierUrn' => '',
            'Note' => '',
            'Language' => 'deu',
            'Licence' => '',
            'SeriesNumber_1' => '',
            'Series_1' => '',

            // Browse Down Button wurde gedrückt
            'browseDownInstitute' => 'runter',
        );

        $this->request
            ->setMethod('POST')
            ->setPost($data);
        $this->dispatch('/publish/form/check');
        $this->assertEquals('200', $this->getResponse()->getHttpResponseCode());

        $this->assertEquals(6, count($session->additionalFields));
        $this->assertEquals('15994', $session->additionalFields['collId1Institute_1']);
        $this->assertEquals(2, $session->additionalFields['stepInstitute_1']);
        $this->assertEquals('1', $session->additionalFields['Institute']);
        $this->assertEquals('1', $session->additionalFields['collId0Institute_1']);
    }

    /**
     * Button pressed: Browse up Institute
     */
    public function testCheckActionWithBrowseUpButton() {
        $session = new Zend_Session_Namespace('Publish');
        $this->addTestDocument($session, 'preprint');
        $session->additionalFields['Institute'] = '1';
        $session->additionalFields['collId0Institute_1'] = '1';
        $session->additionalFields['collId1Institute_1'] = '15994';
        $session->additionalFields['stepInstitute_1'] = '2';

        $data = array(
            'PersonSubmitterFirstName_1' => '',
            'PersonSubmitterLastName_1' => '',
            'PersonSubmitterEmail_1' => '',
            'TitleMain_1' => '',
            'TitleMainLanguage_1' => '',
            'TitleAbstract_1' => '',
            'TitleAbstractLanguage_1' => '',
            'PersonAuthorFirstName_1' => '',
            'PersonAuthorLastName_1' => '',
            'PersonAuthorAcademicTitle_1' => '',
            'PersonAuthorEmail_1' => '',
            'PersonAuthorAllowEmailContact_1' => '0',
            'PersonAuthorDateOfBirth_1' => '',
            'PersonAuthorPlaceOfBirth_1' => '',
            'CompletedYear' => '',
            'CompletedDate' => '07.09.2011',
            'PageNumber' => '',
            'SubjectUncontrolled_1' => '',
            'SubjectUncontrolledLanguage_1' => '',
            'collId2Institute_1' => '15995',
            'IdentifierUrn' => '',
            'Note' => '',
            'Language' => 'deu',
            'Licence' => '',
            'SeriesNumber_1' => '',
            'Series_1' => '',

            // Browse Up Button wurde gedrückt
            'browseUpInstitute' => 'hoch',
        );

        $this->request
            ->setMethod('POST')
            ->setPost($data);
        $this->dispatch('/publish/form/check');
        $this->assertEquals('200', $this->getResponse()->getHttpResponseCode());

        $this->assertEquals(6, count($session->additionalFields));
        $this->assertEquals(1, $session->additionalFields['stepInstitute_1']);
        $this->assertEquals('1', $session->additionalFields['Institute']);
        $this->assertEquals('15994', $session->additionalFields['collId1Institute_1']);
        $this->assertEquals('1', $session->additionalFields['collId0Institute_1']);
    }

    /**
     * Button pressed: no button pressed
     */
    public function testCheckActionWithMissingButton() {
        $session = new Zend_Session_Namespace('Publish');
        $this->addTestDocument($session, 'preprint');
        $session->additionalFields['PersonSubmitter'] = '1';
        $session->additionalFields['TitleMain'] = '1';
        $session->additionalFields['TitleAbstract'] = '1';
        $session->additionalFields['PersonAuthor'] = '1';
        $session->additionalFields['SubjectUncontrolled'] = '1';
        $session->additionalFields['stepInstitute_1'] = '1';
        $session->additionalFields['collId0Institute_1'] = '1';
        $session->additionalFields['Institute'] = '1';
        $session->additionalFields['Series'] = '1';

        $data = array(
            'PersonSubmitterFirstName_1' => '',
            'PersonSubmitterLastName_1' => '',
            'PersonSubmitterEmail_1' => '',
            'TitleMain_1' => '',
            'TitleMainLanguage_1' => '',
            'TitleAbstract_1' => '',
            'TitleAbstractLanguage_1' => '',
            'PersonAuthorFirstName_1' => '',
            'PersonAuthorLastName_1' => '',
            'PersonAuthorAcademicTitle_1' => '',
            'PersonAuthorEmail_1' => '',
            'PersonAuthorAllowEmailContact_1' => '0',
            'PersonAuthorDateOfBirth_1' => '',
            'PersonAuthorPlaceOfBirth_1' => '',
            'CompletedYear' => '',
            'CompletedDate' => '07.09.2011',
            'PageNumber' => '',
            'SubjectUncontrolled_1' => '',
            'SubjectUncontrolledLanguage_1' => '',
            'Institute_1' => '',
            'IdentifierUrn' => '',
            'Note' => '',
            'Language' => 'deu',
            'Licence' => '',
            'SeriesNumber_1' => '',
            'Series_1' => ''
            // kein Button wurde gedrückt
        );

        $this->request
            ->setMethod('POST')
            ->setPost($data);
        $this->dispatch('/publish/form/check');

        $response = $this->getResponse();
        $this->assertEquals('500', $response->getHttpResponseCode());
        $this->assertContains('Application_Exception', $response->getBody());

        //no button pressed, additionalFields still in intial state
        $this->assertEquals(9, count($session->additionalFields));
        $this->assertEquals('1', $session->additionalFields['PersonSubmitter']);
        $this->assertEquals('1', $session->additionalFields['TitleMain']);
        $this->assertEquals('1', $session->additionalFields['TitleAbstract']);
        $this->assertEquals('1', $session->additionalFields['PersonAuthor']);
        $this->assertEquals('1', $session->additionalFields['SubjectUncontrolled']);
        $this->assertEquals('1', $session->additionalFields['stepInstitute_1']);
        $this->assertEquals('1', $session->additionalFields['collId0Institute_1']);
        $this->assertEquals('1', $session->additionalFields['Institute']);
        $this->assertEquals('1', $session->additionalFields['Series']);
    }

    public function testManipulatePostMissingTitleMainLanguage() {
        $doc = $this->createTemporaryDoc();

        $session = new Zend_Session_Namespace('Publish');
        $session->documentType = 'preprint';
        $session->documentId = $doc->getId();
        $session->fulltext = '0';
        $session->additionalFields = array();

        $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'PersonSubmitterLastName_1' => 'Doe',
                    'PersonSubmitterEmail_1' => 'doe@example.org',
                    'TitleMain_1' => 'Entenhausen',                    
                    'PersonAuthorLastName_1' => 'AuthorLastName',
                    'CompletedDate' => '22.01.2011',
                    'Language' => 'deu',
                    'Licence' => '4',
                    'send' => 'Weiter zum nächsten Schritt'
                ));

        $this->dispatch('/publish/form/check');

        $this->assertResponseCode(200);
        $this->assertController('form');
        $this->assertAction('check');

        $this->assertNotContains('Undefined index: TitleMainLanguage_1', $this->getResponse()->getBody());
        $this->assertContains("<div class='form-errors'>", $this->getResponse()->getBody());
    }

    public function testManipulatePostMissingTitleAbstractLanguage() {
        $doc = $this->createTemporaryDoc();

        $session = new Zend_Session_Namespace('Publish');
        $session->documentType = 'preprint';
        $session->documentId = $doc->getId();
        $session->fulltext = '0';
        $session->additionalFields = array();

        $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'PersonSubmitterLastName_1' => 'Doe',
                    'PersonSubmitterEmail_1' => 'doe@example.org',
                    'TitleMain_1' => 'Entenhausen',
                    'TitleMainLanguage_1' => 'deu',
                    'TitleAbstract_1' => 'Foo',
                    'PersonAuthorLastName_1' => 'AuthorLastName',
                    'CompletedDate' => '22.01.2011',
                    'Language' => 'deu',
                    'Licence' => '4',
                    'send' => 'Weiter zum nächsten Schritt'
                ));

        $this->dispatch('/publish/form/check');

        $this->assertResponseCode(200);
        $this->assertController('form');
        $this->assertAction('check');

        $this->assertNotContains('Undefined index: TitleAbstractLanguage_1', $this->getResponse()->getBody());        
    }

    public function testManipulatePostMissingTitleParentLanguage() {
        $doc = $this->createTemporaryDoc();

        $session = new Zend_Session_Namespace('Publish');
        $session->documentType = 'all';
        $session->documentId = $doc->getId();
        $session->fulltext = '0';
        $session->additionalFields = array();

        $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'PersonSubmitterLastName_1' => 'Doe',
                    'PersonSubmitterEmail_1' => 'doe@example.org',
                    'TitleMain_1' => 'Entenhausen',
                    'TitleMainLanguage_1' => 'deu',
                    'TitleParent_1' => 'Foo',
                    'PersonAuthorLastName_1' => 'AuthorLastName',
                    'CompletedDate' => '22.01.2011',
                    'Language' => 'deu',
                    'Licence' => '4',
                    'send' => 'Weiter zum nächsten Schritt'
                ));

        $this->dispatch('/publish/form/check');

        $this->assertNotContains('Undefined index: TitleParentLanguage_1', $this->getResponse()->getBody());
    }

    public function testManipulatePostMissingTitleSubLanguage() {
        $doc = $this->createTemporaryDoc();

        $session = new Zend_Session_Namespace('Publish');
        $session->documentType = 'all';
        $session->documentId = $doc->getId();
        $session->fulltext = '0';
        $session->additionalFields = array();

        $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'PersonSubmitterLastName_1' => 'Doe',
                    'PersonSubmitterEmail_1' => 'doe@example.org',
                    'TitleMain_1' => 'Entenhausen',
                    'TitleMainLanguage_1' => 'deu',
                    'TitleSub_1' => 'Foo',
                    'PersonAuthorLastName_1' => 'AuthorLastName',
                    'CompletedDate' => '22.01.2011',
                    'Language' => 'deu',
                    'Licence' => '4',
                    'send' => 'Weiter zum nächsten Schritt'
                ));

        $this->dispatch('/publish/form/check');

        $this->assertNotContains('Undefined index: TitleSubLanguage_1', $this->getResponse()->getBody());
    }

    public function testManipulatePostMissingTitleAdditionalLanguage() {
        $doc = $this->createTemporaryDoc();

        $session = new Zend_Session_Namespace('Publish');
        $session->documentType = 'all';
        $session->documentId = $doc->getId();
        $session->fulltext = '0';
        $session->additionalFields = array();

        $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'PersonSubmitterLastName_1' => 'Doe',
                    'PersonSubmitterEmail_1' => 'doe@example.org',
                    'TitleMain_1' => 'Entenhausen',
                    'TitleMainLanguage_1' => 'deu',
                    'TitleAdditional_1' => 'Foo',
                    'PersonAuthorLastName_1' => 'AuthorLastName',
                    'CompletedDate' => '22.01.2011',
                    'Language' => 'deu',
                    'Licence' => '4',
                    'send' => 'Weiter zum nächsten Schritt'
                ));

        $this->dispatch('/publish/form/check');

        $this->assertNotContains('Undefined index: TitleAdditionalLanguage_1', $this->getResponse()->getBody());
    }

    public function testBarfooTemplateIsRenderedForDoctypeFoobar() {
        $session = new Zend_Session_Namespace('Publish');
        $session->documentType = 'foobar';
        $doc = $this->createTemporaryDoc();
        $session->documentId = $doc->getId();
        $session->fulltext = '0';
        $session->additionalFields = array();

        $this->request->setMethod('POST');
        $this->request->setPost(array('browseUpInstitute' => 'ignore'));

        $this->dispatch('/publish/form/check');

        $respBody = $this->getResponse()->getBody();        
        $this->assertContains("<label for='Language'>", $respBody);
        $this->assertContains('>foobar</h3>', $respBody);
    }

    public function testApplicationErrorForDoctypeBarbaz() {
        $session = new Zend_Session_Namespace('Publish');
        $session->documentType = 'barbaz';
        $doc = $this->createTemporaryDoc();
        $session->documentId = $doc->getId();
        $session->fulltext = '0';        
        $session->additionalFields = array('browseUpInstitute' => 'hoch',);

        $this->request->setMethod('POST');
        
        $this->dispatch('/publish/form/check');
        
        $this->assertResponseCode(500);
        $this->assertContains('Application_Exception', $this->getResponse()->getBody());
        $this->assertContains('invalid configuration: template file barbaz.phtml is not readable or does not exist', $this->getResponse()->getBody());        
    }

    public function testApplicationErrorForDoctypeBazbar() {
        $session = new Zend_Session_Namespace('Publish');
        $session->documentType = 'bazbar';
        $doc = $this->createTemporaryDoc();
        $session->documentId = $doc->getId();
        $session->fulltext = '0';        
        $session->additionalFields = array('browseUpInstitute' => 'hoch',);

        $this->request->setMethod('POST');
        
        $this->dispatch('/publish/form/check');
        
        $this->assertResponseCode(500);
        $this->assertContains('Application_Exception', $this->getResponse()->getBody());
        $this->assertContains('invalid configuration: template file barbaz.phtml is not readable or does not exist', $this->getResponse()->getBody());
    }

}

