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
 * @category    TODO
 * @package     TODO
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
class PersonControllerTest extends ControllerTestCase {

    private $documentId;

    public function tearDown() {
        $this->removeDocument($this->documentId);

        parent::tearDown();
    }

    public function testAssignAction() {
        $this->dispatch('/admin/person/assign/document/146');

        $this->validateXHTML();
        $this->verifyBreadcrumbDefined();

        $this->assertXpath('//option[@value="author" and @selected="selected"]'); // default
    }

    public function testAssignActionBadDocumentId() {
        $this->dispatch('/admin/person/assign/document/bad');
        $this->assertRedirectTo('/admin/documents');
        $this->verifyFlashMessage('admin_document_error_novalidid');
    }

    public function testAssignActionUnknownDocumentId() {
        $this->dispatch('/admin/person/assign/document/5555');
        $this->assertRedirectTo('/admin/documents');
        $this->verifyFlashMessage('admin_document_error_novalidid');
    }

    public function testAssignActionNoDocumentId() {
        $this->dispatch('/admin/person/assign');
        $this->assertRedirectTo('/admin/documents');
        $this->verifyFlashMessage('admin_document_error_novalidid');
    }

    public function testAssignActionWithRoleParam() {
        $this->dispatch('/admin/person/assign/document/146/role/translator');

        $this->assertNotXpath('//option[@value="author" and @selected="selected"]');
        $this->assertXpath('//option[@value="translator" and @selected="selected"]');
    }

    public function testAssignActionCancel() {
        $this->getRequest()->setMethod('POST')->setPost(array(
            'Cancel' => 'Abbrechen'
        ));

        $this->dispatch('/admin/person/assign/document/146/role/advisor');
        $this->assertRedirectTo('/admin/document/edit/id/146/continue/addperson');
    }

    public function testAssignActionAddPerson() {
        $document = $this->createTestDocument();

        $this->documentId = $document->store();

        $this->getRequest()->setMethod('POST')->setPost(array(
            'LastName' => 'Testy-AssignAction',
            'Document' => array(
                'Role' => 'translator'
            ),
            'Save' => 'Speichern'
        ));

        $this->dispatch('/admin/person/assign/document/' . $this->documentId . '/role/translator');

        $location = $this->getLocation();

        $matches = array();
        preg_match('/person\/(\d+)\//', $location, $matches);
        $personId = $matches[1];

        $person = new Opus_Person($personId);

        $lastName = $person->getLastName();

        $person->delete();

        $this->assertTrue(strpos($location, '/admin/document/edit/id/'
                . $this->documentId . '/continue/addperson') === 0);

        $this->assertEquals('Testy-AssignAction', $lastName);
    }

    public function testEditlinkedAction() {
        $this->dispatch('/admin/person/editlinked/document/146/personId/259');

        $this->validateXHTML();
        $this->verifyBreadcrumbDefined();

        $this->assertXpath('//input[@id="LastName" and @value="Doe"]');
        $this->assertXpath('//input[@id="FirstName" and @value="John"]');
    }

    public function testEditlinkedActionBadDocumentId() {
        $this->dispatch('/admin/person/editlinked/document/bad');
        $this->assertRedirectTo('/admin/documents');
        $this->verifyFlashMessage('admin_document_error_novalidid');
    }

    public function testEditlinkedActionUnknownDocumentId() {
        $this->dispatch('/admin/person/editlinked/document/5555');
        $this->assertRedirectTo('/admin/documents');
        $this->verifyFlashMessage('admin_document_error_novalidid');
    }

    public function testEditlinkedActionNoDocumentId() {
        $this->dispatch('/admin/person/editlinked');
        $this->assertRedirectTo('/admin/documents');
        $this->verifyFlashMessage('admin_document_error_novalidid');
    }

    public function testEditlinkedActionNoPersonId() {
        $this->dispatch('/admin/person/editlinked/document/146');
        $this->assertRedirectTo('/admin/document/edit/id/146/continue/true');
    }

    public function testEditlinkedActionUnknownPersonId() {
        $this->dispatch('/admin/person/editlinked/document/146/personId/7777');
        $this->assertRedirectTo('/admin/document/edit/id/146/continue/true');
    }

    public function testEditlinkedActionBadPersonId() {
        $this->dispatch('/admin/person/editlinked/document/146/personId/bad');
        $this->assertRedirectTo('/admin/document/edit/id/146/continue/true');
    }

    public function testEditlinkedActionPersonNotLinkedToDocument() {
        $this->markTestIncomplete('Klären wofür die Action verwendet werden soll.');
        $this->dispatch('/admin/person/editlinked/document/146/personId/253');
        $this->assertRedirectTo('/admin/document/edit/id/146/continue/true');
    }

    public function testEditlinkedActionCancel() {
        $this->getRequest()->setMethod('POST')->setPost(array(
            'Cancel' => 'Abbrechen'
        ));

        $this->dispatch('/admin/person/editlinked/document/146/personId/259');
        $this->assertRedirectTo('/admin/document/edit/id/146/continue/true');
    }

    public function testEditlinkedActionSave() {
        $document = $this->createTestDocument();

        $person = new Opus_Person();
        $person->setLastName('Testy-EditlinkedAction');

        $person = $document->addPersonTranslator($person);

        $this->documentId = $document->store();

        $personId = $person->getModel()->getId();

        $this->getRequest()->setMethod('POST')->setPost(array(
            'PersonId' => $personId,
            'LastName' => 'Testy',
            'FirstName' => 'Simone',
            'Document' => array(
                'Role' => 'translator'
            ),
            'Save' => 'Speichern'
        ));

        $this->dispatch('/admin/person/editlinked/personId/' . $personId . '/role/translator/document/'
            . $this->documentId);

        $this->assertRedirectTo('/admin/document/edit/id/' . $this->documentId
            . '/continue/updateperson/person/' . $personId);

        $person = new Opus_Person($personId);

        $this->assertEquals('Testy', $person->getLastName());
        $this->assertEquals('Simone', $person->getFirstName());
    }


}