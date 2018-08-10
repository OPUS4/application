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
 * @package     Admin_Form
 * @author      Jens Schwidder <schwidder@zib.de>
 * @author      Maximilian Salomon <salomon@zib.de>
 * @copyright   Copyright (c) 2008-2018, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */
class Admin_Form_FileTest extends ControllerTestCase {

    public function testConstructForm() {
        $form = new Admin_Form_File();

        $this->assertEquals(10, count($form->getElements()));

        $elements = array('Id', 'FileLink', 'FileSize', 'Language', 'Label', 'Comment', 'VisibleIn', 'Roles', 'ServerDateSubmitted', 'SortOrder');

        foreach ($elements as $element) {
            $this->assertNotNull($form->getElement($element), "Element '$element' is missing.");
        }

        $this->assertEquals(1, count($form->getSubForms()));
        $this->assertNotNull($form->getSubForm('Hashes'));
    }

    public function testPopulateFromModel() {
        $this->useEnglish();
        $form = new Admin_Form_File();

        $file = new Opus_File(126); // hängt an Testdokument 146

        $form->populateFromModel($file);

        $this->assertEquals(126, $form->getElementValue('Id'));
        $this->assertEquals($file, $form->getElementValue('FileLink'));
        $this->assertEmpty($form->getElement('FileLink')->getErrorMessages()); // Datei existiert
        $this->assertEquals(8817, $form->getElement('FileSize')->getValue());
        $this->assertEquals('deu', $form->getElement('Language')->getValue());
        $this->assertEquals('foo-pdf', $form->getElement('Label')->getValue());
        $this->assertEquals('foo-pdf file', $form->getElement('Comment')->getValue());
        $this->assertEquals('2013/12/10', $form->getElement('ServerDateSubmitted')->getValue());
        $this->assertEquals('1', $form->getElement('SortOrder')->getValue());

        $this->assertEquals(array('frontdoor', 'oai'), $form->getElement('VisibleIn')->getValue());

        $this->assertEquals(array('administrator', 'guest', 'reviewer'), $form->getElement('Roles')->getValue());

        $hashes = $form->getSubForm('Hashes');
        // TODO hashes
    }

    public function testPopulateFromModelFileDoesNotExist() {
        $form = new Admin_Form_File();

        $file = new Opus_File(123); // von Dokument 122

        $form->populateFromModel($file);

        $this->assertFalse($file->exists(), 'Datei mit ID = 123 sollte in den Testdaten nicht existieren.');
        $this->assertEquals(123, $form->getElementValue('Id'));

        $errorMessages = $form->getElement('FileLink')->getErrorMessages();

        $this->assertEquals(1, count($errorMessages));
        $this->assertEquals('admin_filemanager_file_does_not_exist', $errorMessages[0]);
    }

    public function testUpdateModel() {
        $form = new Admin_Form_File();

        $form->getElement('Language')->setValue('fra');
        $form->getElement('Label')->setValue('Testlabel');
        $form->getElement('Comment')->setValue('Testkommentar');
        $form->getElement('VisibleIn')->setValue(array('frontdoor', 'oai'));
        $form->getElement('Roles')->setValue(array('reviewer', 'docsadmin'));

        $document = $this->createTestDocument();

        $file = $document->addFile();
        $file->setPathName('test.pdf');

        $document->store();

        $form->updateModel($file);

        $this->assertEquals('fra', $file->getLanguage());
        $this->assertEquals('Testlabel', $file->getLabel());
        $this->assertEquals('Testkommentar', $file->getComment());
        $this->assertEquals(1, $file->getVisibleInFrontdoor());
        $this->assertEquals(1, $file->getVisibleInOai());

        $roles = $form->getRolesForFile($file->getId());

        $this->assertEquals(2, count($roles));
        $this->assertContains('reviewer', $roles);
        $this->assertContains('docsadmin', $roles);

        $form->getElement('VisibleIn')->setValue(array('oai'));
        $form->getElement('Roles')->setValue(array('reviewer', 'guest'));

        $form->updateModel($file);

        $this->assertEquals(0, $file->getVisibleInFrontdoor());
        $this->assertEquals(1, $file->getVisibleInOai());

        $roles = $form->getRolesForFile($file->getId());

        $this->assertEquals(2, count($roles));
        $this->assertContains('reviewer', $roles);
        $this->assertContains('guest', $roles);
    }

    public function testUpdateModelSingleValues() {
        $form = new Admin_Form_File();

        $form->getElement('VisibleIn')->setValue('frontdoor');
        $form->getElement('Roles')->setValue('reviewer');

        $document = $this->createTestDocument();

        $file = $document->addFile();
        $file->setPathName('test.pdf');

        $document->store();

        $form->updateModel($file);

        $this->assertEquals(1, $file->getVisibleInFrontdoor());
        $this->assertEquals(0, $file->getVisibleInOai());

        $roles = $form->getRolesForFile($file->getId());

        $this->assertInternalType('array', $roles);
        $this->assertEquals(1, count($roles));
        $this->assertContains('reviewer', $roles);
    }

    public function testGetModel() {
        $form = new Admin_Form_File();

        $form->getElement('Id')->setValue(126); // Datei 'test.pdf' von Dokument 146

        $file = new Opus_File(126);

        $form->populateFromModel($file);

        $form->getElement('Comment')->setValue('Testkommentar');

        $model = $form->getModel();

        $this->assertInstanceOf('Opus_File', $model);
        $this->assertEquals(126, $model->getId());
        $this->assertEquals('Testkommentar', $model->getComment());

        $roles = $form->getRolesForFile($model->getId());

        $this->assertEquals(3, count($roles));
        $this->assertContains('administrator', $roles);
        $this->assertContains('guest', $roles);
        $this->assertContains('reviewer', $roles);
    }

    /**
     * @expectedException Application_Exception
     * @expectedExceptionMessage Bad file ID = 'bla'.
     */
    public function testGetModelBadId() {
        $form = new Admin_Form_File();

        $form->getElement('Id')->setValue('bla');

        $form->getModel();
    }

    /**
     * @expectedException Application_Exception
     * @expectedExceptionMessage Unknown file ID = '8888'.
     */
    public function testGetModelUnknownID() {
        $form = new Admin_Form_File();

        $form->getElement('Id')->setValue('8888');

        $form->getModel();
    }

    public function testSetDefaults() {
        $form = new Admin_Form_File();
        $form->setName('File0');

        $post = array(
            'File0' => array(
                'Id' => 116
            )
        );

        $form->setDefaults($post);

        $this->assertEquals(116, $form->getElementValue('Id'));
        $this->assertEquals('6970', $form->getElementValue('FileSize'));

        $hashes = $form->getSubForm('Hashes');

        $this->assertEquals(2, count($hashes->getElements()));
    }

    public function testValidation() {
        $form = new Admin_Form_File();

        $post = array(
            'FileLink' => 123,
            'Language' => 'deu'
        );

        $result = $form->isValid($post);

        $this->assertTrue($result);
    }

    /**
     * @expectedException Application_Exception
     * @expectedExceptionMessage File with ID = 5555 not found.
     */
    public function testValidationUnknownFileLink() {
        $form = new Admin_Form_File();

        $post = array(
            'FileLink' => '5555',
            'Language' => 'eng'
        );

        $result = $form->isValid($post);
    }

    public function testUpdateFileRoles() {
        $form = new Admin_Form_File();

        $logger = new MockLogger();

        $form->setLogger($logger);

        $document = $this->createTestDocument();

        $file = $document->addFile();
        $file->setPathName('test.pdf');

        $document->store(); // setzt automatisch 'guest' Zugriff für Datei

        $form->updateFileRoles($file, array('administrator', 'reviewer'));

        $fileId = $file->getId();

        $messages = $logger->getMessages();

        $this->assertEquals(3, count($messages));
        $this->assertContains("File ID = $fileId access for role 'guest' removed.", $messages[0]);
        $this->assertContains("File ID = $fileId access for role 'administrator' added.", $messages[1]);
        $this->assertContains("File ID = $fileId access for role 'reviewer' added.", $messages[2]);

        $roles = $form->getRolesForFile($fileId);

        $this->assertEquals(2, count($roles));
        $this->assertContains('administrator', $roles);
        $this->assertContains('reviewer', $roles);

        $logger->clear();

        $form->updateFileRoles($file, array('guest', 'reviewer'));

        $messages = $logger->getMessages();

        $this->assertEquals(3, count($messages));
        $this->assertContains("File ID = $fileId access for role 'administrator' removed.", $messages[0]);
        $this->assertContains("File ID = $fileId access for role 'guest' added.", $messages[1]);
        $this->assertContains("File ID = $fileId access for role 'reviewer' already permitted.", $messages[2]);

        $roles = $form->getRolesForFile($fileId);

        $this->assertEquals(2, count($roles));
        $this->assertContains('guest', $roles);
        $this->assertContains('reviewer', $roles);

        $logger->clear();

        $form->updateFileRoles($file, 'docsadmin');

        $messages = $logger->getMessages();

        $this->assertEquals(3, count($messages));
        $this->assertContains("File ID = $fileId access for role 'guest' removed.", $messages[0]);
        $this->assertContains("File ID = $fileId access for role 'reviewer' removed.", $messages[1]);
        $this->assertContains("File ID = $fileId access for role 'docsadmin' added.", $messages[2]);

        $roles = $form->getRolesForFile($fileId);

        $this->assertEquals(1, count($roles));
        $this->assertContains('docsadmin', $roles);

        $logger->clear();

        $form->updateFileRoles($file, null);

        $messages = $logger->getMessages();

        $this->assertEquals(1, count($messages));
        $this->assertContains("File ID = $fileId access for role 'docsadmin' removed.", $messages[0]);

        $roles = $form->getRolesForFile($fileId);

        $this->assertEquals(0, count($roles));

        $logger->clear();

        $form->updateFileRoles($file, 'unknownrole');

        $messages = $logger->getMessages();

        $this->assertEquals(1, count($messages));
        $this->assertContains("Unknown role 'unknownrole'.", $messages[0]);

        $roles = $form->getRolesForFile($fileId);

        $this->assertEquals(0, count($roles));
    }

    public function testUpdateModelSortOrderNull() {
        $form = new Admin_Form_File();

        $form->getElement('Language')->setValue('fra');
        $form->getElement('SortOrder')->setValue(1);

        $document = $this->createTestDocument();

        $file = $document->addFile();
        $file->setPathName('test.pdf');

        $document->store();

        $form->updateModel($file);

        $this->assertEquals('fra', $file->getLanguage());
        $this->assertEquals(1, $file->getSortOrder());

        $form->getElement('SortOrder')->setValue(null);

        $form->updateModel($file);

        $this->assertNotNull($file->getSortOrder());

        $document->store(); // triggered exception before fix
    }

}