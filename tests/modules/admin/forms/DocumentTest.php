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
 * @category    Application Unit Test
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Unit Tests für Metadaten-Formular Klasse.
 */
class Admin_Form_DocumentTest extends ControllerTestCase {

    public function testConstructForm() {
        $form = new Admin_Form_Document();

        $this->assertEquals(0, count($form->getElements()));

        $subformNames = array(
            'ActionBox',
            'InfoBox',
            'General',
            'Persons',
            'Titles',
            'Bibliographic',
            'Series',
            'Enrichments',
            'Collections',
            'Content',
            'IdentifiersAll',
            'Licences',
            'Patents',
            'Notes',
            'Actions'
        );

        $this->verifySubForms($form, $subformNames);
    }

    /**
     * Prüft ob populateFromModel an Unterformulare weitergereicht wird.
     */
    public function testPopulateFromModel() {
        $form = new Admin_Form_Document();

        $document = new Opus_Document(146);

        $form->populateFromModel($document);

        $this->assertEquals(1, count($form->getSubForm('Persons')->getSubForm('author')->getSubForms()));
        $this->assertEquals(3, count($form->getSubForm('IdentifiersAll')->getSubForms()));
        $this->assertEquals(1, count($form->getSubForm('IdentifiersAll')->getSubForm('IdentifiersDOI')->getSubForms()));
        $this->assertEquals(1, count($form->getSubForm('IdentifiersAll')->getSubForm('IdentifiersURN')->getSubForms()));
        $this->assertEquals(14, count($form->getSubForm('IdentifiersAll')->getSubForm('Identifiers')->getSubForms()));
        $this->assertEquals(8, count($form->getSubForm('Collections')->getSubForms()));
    }

    public function testGetInstanceFromPost() {
        $document = new Opus_Document(146);

        $post = array();

        $form = Admin_Form_Document::getInstanceFromPost($post, $document);

        $this->assertNotNull($form);
        $this->assertInstanceOf('Admin_Form_Document', $form);
    }

    public function testProcessPostEmpty() {
        $form = new Admin_Form_Document();

        $this->assertNull($form->processPost(array(), array()));
    }

    public function testProcessPostSave() {
        $form = new Admin_Form_Document();

        $post = array(
            'ActionBox' => array(
                'Save' => 'Speichern'
            )
        );

        $this->assertEquals(Admin_Form_Document::RESULT_SAVE, $form->processPost($post, $post));
    }

    public function testContinueEdit() {
        $form = new Admin_Form_Document();

        $request = $this->getRequest();
        $request->setParams(array(
            'continue' => 'addperson',
            'person' => '310',
            'role' => 'editor',
            'order' => '2',
            'contact' => '0'
        ));

        $session = new Admin_Model_DocumentEditSession(100);

        $this->assertEquals(0, count($form->getSubForm('Persons')->getSubForm('editor')->getSubForms()));

        $form->continueEdit($request, $session);

        $this->assertEquals(1, count($form->getSubForm('Persons')->getSubForm('editor')->getSubForms()));

        $subform = $form->getSubForm('Persons')->getSubForm('editor')->getSubForm('PersonEditor0');

        $this->assertNotNull($subform);
        $this->assertEquals(310, $subform->getElementValue('PersonId'));
        $this->assertEquals(1, $subform->getElementValue('SortOrder')); // nur ein Editor
        $this->assertEquals(0, $subform->getElementValue('AllowContact'));
    }

    public function testIsValidTrue() {
        $form = new Admin_Form_Document();

        $document = $this->createTestDocument();
        $document->addTitleMain(new Opus_Title());

        $form->populateFromModel($document);

        $post = array(
            'General' => array(
                'Language' => 'deu',
                'Type' => 'all'
            ),
            'Titles' => array(
                'Main' => array(
                    'TitleMain0' => array(
                        'Language' => 'deu',
                        'Value' => 'Deutscher Titel'
                    )
                )
            ),
            'Actions' => array(
                'OpusHash' => $this->getHash($form)
            )
        );

        $result = $form->isValid($post, $post);

        $this->assertTrue($result);
    }

    /**
     * Die Validierung schlägt fehl, weil der Titel einen leeren Wert hat.
     */
    public function testIsValidFalse() {
        $form = new Admin_Form_Document();

        $document = $this->createTestDocument();
        $document->addTitleMain(new Opus_Title());

        $form->populateFromModel($document);

        $post = array(
            'General' => array(
                'Language' => 'deu',
                'Type' => 'all'
            ),
            'Titles' => array(
                'Main' => array(
                    'TitleMain0' => array(
                        'Language' => 'deu',
                        'Value' => ''
                    )
                )
            ),
            'Actions' => array(
                'OpusHash' => $this->getHash($form)
            )
        );

        $result = $form->isValid($post, $post);

        $this->assertFalse($result);

        $errors = $form->getErrors('Titles');
        $this->assertContains('admin_validate_error_notempty', $errors['Main']['TitleMain0']['Value']);
    }

    /**
     * Die Validierung schlägt fehl, weil die Dokumentensprache 'deu' ist und kein deutscher Titel vorliegt. Diese
     * Prüfung wird intern über die Funktion isDependenciesValid durchgeführt.
     */
    public function testIsValidFalseDependency() {
        $form = new Admin_Form_Document();

        $document = $this->createTestDocument();
        $document->addTitleMain(new Opus_Title());

        $form->populateFromModel($document);

        $post = array(
            'General' => array(
                'Language' => 'deu',
                'Type' => 'all'
            ),
            'Titles' => array(
                'Main' => array(
                    'TitleMain0' => array(
                        'Language' => 'eng',
                        'Value' => 'English Title'
                    )
                )
            ),
            'Actions' => array(
                'OpusHash' => $this->getHash($form)
            )
        );

        $result = $form->isValid($post, $post);

        $this->assertFalse($result);

        $subform = $form->getSubForm('Titles')->getSubForm('Main');
        $this->assertEquals(1, count($subform->getErrorMessages()));
        $this->assertContains('admin_document_error_NoTitleInDocumentLanguage', $subform->getErrorMessages());
    }

    /**
     * Prüft ob Dependency Validierung ausgeführt wird, wenn normale Validierung fehlschlägt.
     */
    public function testIsValidFalseDependency2() {
        $form = new Admin_Form_Document();

        $document = $this->createTestDocument();
        $document->addTitleMain(new Opus_Title());
        $document->addTitleMain(new Opus_Title());

        $form->populateFromModel($document);

        $post = array(
            'General' => array(
                'Language' => 'deu',
                'Type' => 'all'
            ),
            'Titles' => array(
                'Main' => array(
                    'TitleMain0' => array(
                        'Language' => 'eng',
                        'Value' => 'English Title'
                    ),
                    'TitleMain1' => array(
                        'Language' => 'rus',
                        'Value' => ''
                    )
                )
            ),
            'Actions' => array(
                'OpusHash' => $this->getHash($form)
            )
        );

        $result = $form->isValid($post, $post);

        $this->assertFalse($result);

        $errors = $form->getErrors('Titles');
        $this->assertContains('admin_validate_error_notempty', $errors['Main']['TitleMain1']['Value']);

        $subform = $form->getSubForm('Titles')->getSubForm('Main');
        $this->assertEquals(1, count($subform->getErrorMessages()), 'Dependency-Validierung wurde nicht ausgeführt.');
        $this->assertContains('admin_document_error_NoTitleInDocumentLanguage', $subform->getErrorMessages());
    }

    public function testSetGetMessage() {
        $form = new Admin_Form_Document();

        $this->assertNull($form->getMessage());

        $form->setMessage('Test Nachricht');

        $this->assertEquals('Test Nachricht', $form->getMessage());
    }

    public function testPrepareRenderingAsViewFullDocument() {
        $form = new Admin_Form_Document();

        $document = new Opus_Document(146);

        $form->populateFromModel($document);
        $form->prepareRenderingAsView();

        $this->verifySubForms($form, array(
            'ActionBox',
            'InfoBox',
            'General',
            'Persons',
            'Titles',
            'Bibliographic',
            'Series',
            'Enrichments',
            'Collections',
            'Content',
            'IdentifiersAll',
            'Licences',
            'Patents',
            'Notes',
            'Files'
        ));
    }

    public function testPrepareRenderingAsViewDocumentWithoutFiles() {
        $form = new Admin_Form_Document();

        $document = new Opus_Document(200);

        $form->populateFromModel($document);
        $form->prepareRenderingAsView();

        $this->assertNull($form->getSubForm('Files'));
    }

    /**
     * Für ein leeres Dokument werden fast alle Unterformulare entfernt.
     * Weiterhin angezeigt werden die ActionBox, und die InfoBox,
     */
    public function testPrepareRenderingAsViewForEmptyDocument() {
        $form = new Admin_Form_Document();

        $document = $this->createTestDocument();

        $form->populateFromModel($document);
        $form->prepareRenderingAsView();

        $this->verifySubForms($form, array('ActionBox', 'InfoBox', 'Bibliographic', 'IdentifiersAll'));
    }

    protected function verifySubForms($form, $names) {
        $this->assertEquals(count($names), count($form->getSubForms()));

        foreach ($names as $name) {
            $this->assertNotNull($form->getSubForm($name), "Unterformular '$name' fehlt.");
        }
    }

    protected function getHash($form) {
        $session = new Zend_Session_Namespace('testing');

        $hashElement = $form->getSubForm('Actions')->getElement('OpusHash');
        $hashElement->setSession($session);
        $hashElement->initCsrfToken();
        $hashElement->initCsrfValidator();

        return $hashElement->getHash();
    }

}