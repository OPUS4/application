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

/**
 * Unit Test fuer FileManager Formular.
 */
class Admin_Form_FileManagerTest extends ControllerTestCase
{
    /** @var string[] */
    protected $additionalResources = ['database', 'view', 'translation'];

    public function testConstructForm()
    {
        $form = new Admin_Form_FileManager();

        $this->assertEquals(3, count($form->getElements()));
        $this->assertNotNull($form->getElement('Id'));
        $this->assertNotNull($form->getElement('Save'));
        $this->assertNotNull($form->getElement('Cancel'));

        $this->assertEquals(3, count($form->getSubForms()));
        $this->assertNotNull($form->getSubForm('Action'));
        $this->assertNotNull($form->getSubForm('Info'));
        $this->assertNotNull($form->getSubForm('Files'));

        $this->assertEquals(3, count($form->getDecorators()));
        $this->assertNotNull($form->getDecorator('FormElements'));
        $this->assertNotNull($form->getDecorator('HtmlTag'));
        $this->assertNotNull($form->getDecorator('Form'));

        $this->assertEquals('FileManager', $form->getName());
    }

    public function testPopulateFromModel()
    {
        $form = new Admin_Form_FileManager();

        $document = Document::get(92);

        $filesForm = $form->getSubForm(Admin_Form_FileManager::SUBFORM_FILES);

        $this->assertEquals(0, count($filesForm->getSubForms()));

        $form->populateFromModel($document);

        $this->assertEquals(2, count($filesForm->getSubForms()));

        $infoForm = $form->getSubForm(Admin_Form_FileManager::SUBFORM_INFO);

        $this->assertEquals($document, $infoForm->getDocument());

        $actionForm = $form->getSubForm(Admin_Form_FileManager::SUBFORM_ACTION);

        $this->assertEquals($document, $actionForm->getDocument());
    }

    /**
     * TODO Test sollte nur prüfen, ob Funktionen in Unterformular aufgerufen wird (verwende Mock-Objekt)
     */
    public function testUpdateModel()
    {
        $form = new Admin_Form_FileManager();

        $document = Document::get(92);

        $form->populateFromModel($document);

        $filesForm = $form->getSubForm(Admin_Form_FileManager::SUBFORM_FILES);

        $files = $document->getFile();

        $this->assertEquals(2, count($files));
        $this->assertNull($files[0]->getComment());

        $fileForm = $filesForm->getSubForm('File0');

        $fileForm->getElement('Comment')->setValue('Testkommentar');

        $form->updateModel($document);

        $files = $document->getFile();

        $this->assertEquals(2, count($files));
        $this->assertEquals('Testkommentar', $files[0]->getComment());
    }

    public function testProcessPost()
    {
        $form = new Admin_Form_FileManager();

        $this->assertNull($form->processPost([], null));

        $this->assertNull($form->processPost([
            'Files' => [
                'File0' => [
                    'Id' => 5555,
                ],
            ],
        ], null));

        $post = [
            'Files' => [
                'File0' => [
                    'Id'     => 5555,
                    'Remove' => 'Entfernen',
                ],
            ],
        ];

        $form->constructFromPost($post, null);

        $this->assertEquals([
            'result' => 'switch',
            'target' => [
                'module'     => 'admin',
                'controller' => 'filemanager',
                'action'     => 'delete',
                'fileId'     => '5555',
            ],
        ], $form->processPost($post, null));

        // alles weitere wird in den Unterformularen getestet
    }

    public function testConstructFromPostEmptyAndNoDocument()
    {
        $form = new Admin_Form_FileManager();

        $form->constructFromPost([], null);

        $this->assertEquals(0, count($form->getSubForm('Files')->getSubForms()));
    }

    public function testConstructFromPost()
    {
        $document = Document::get(146);

        $post = [
            'Files' => [
                'File0' => [
                    'Id' => 126,
                ],
                'File1' => [
                    'Id' => 116,
                ],
            ],
        ];

        $form = new Admin_Form_FileManager();

        $form->constructFromPost($post, $document);

        $this->assertInstanceOf('Admin_Form_FileManager', $form);
        $this->assertEquals(2, count($form->getSubForm('Files')->getSubForms()));

        $fileForm = $form->getSubForm('Files')->getSubForm('File0');

        $this->assertNotNull($fileForm);
        $this->assertNull($fileForm->getElementValue('Id')); // Formular noch nicht befüllt
    }

    public function testContinueEdit()
    {
        $this->markTestIncomplete('Use Mocking Framework to make sure subform function is called.');
    }

    public function testSetGetMessage()
    {
        $form = new Admin_Form_FileManager();

        $this->assertNull($form->getMessage());

        $form->setMessage('Test');

        $this->assertEquals('Test', $form->getMessage());
    }

    public function testGetInstanceFromPost()
    {
        $document = Document::get(146);

        $post = [
            'Files' => [
                'File0' => [
                    'Id' => 126,
                ],
            ],
        ];

        $form = Admin_Form_FileManager::getInstanceFromPost($post, $document);

        $this->assertInstanceOf('Admin_Form_FileManager', $form);
        $this->assertEquals(1, count($form->getSubForm('Files')->getSubForms()));

        $fileForm = $form->getSubForm('Files')->getSubForm('File0');

        $this->assertNotNull($fileForm);
        $this->assertNull($fileForm->getElementValue('Id')); // Formular noch nicht befüllt
    }
}
