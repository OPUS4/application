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
 * Unit Tests fuer Unterformular, das Dateien in FileManager auflistet.
 */
class Admin_Form_FilesTest extends ControllerTestCase
{
    /** @var string[] */
    protected $additionalResources = ['view', 'translation'];

    public function testConstructForm()
    {
        $this->disableTranslation();
        $form = new Admin_Form_Files();

        $this->assertEquals('admin_document_section_files', $form->getLegend());
        $this->assertNotNull($form->getDecorator('FieldsetWithButtons'));

        $this->assertEquals(2, count($form->getElements()));
        $this->assertNotNull($form->getElement('Add'));
        $this->assertNotNull($form->getElement('Import'));

        $this->assertEmpty($form->getElement('Import')->getDecorators());

        $decorator = $form->getDecorator('FieldsetWithButtons');

        $this->assertEquals(['Import', 'Add'], $decorator->getLegendButtons());
    }

    public function testProcessPostAdd()
    {
        $form = new Admin_Form_Files();

        $post = [
            'Add' => 'Upload',
        ];

        $result = $form->processPost($post, null);

        $this->assertEquals([
            'result' => 'switch',
            'target' => [
                'module'     => 'admin',
                'controller' => 'filemanager',
                'action'     => 'upload',
            ],
        ], $result);
    }

    public function testProcessPostRemove()
    {
        $form = new Admin_Form_Files();

        $form->appendSubForm();

        $post = [
            'File0' => [
                'Id'     => '5555',
                'Remove' => 'Entfernen',
            ],
        ];

        $result = $form->processPost($post, null);

        $this->assertEquals([
            'result' => 'switch',
            'target' => [
                'module'     => 'admin',
                'controller' => 'filemanager',
                'action'     => 'delete',
                'fileId'     => '5555',
            ],
        ], $result);
    }

    public function testProcessPostImport()
    {
        $form = new Admin_Form_Files();

        $post = [
            'Import' => 'Import',
        ];

        $result = $form->processPost($post, null);

        $this->assertEquals([
            'result' => 'switch',
            'target' => [
                'module'     => 'admin',
                'controller' => 'filebrowser',
                'action'     => 'index',
            ],
        ], $result);
    }

    public function testContinueEdit()
    {
        $form = new Admin_Form_Files();

        $document = Document::get(91);

        $form->populateFromModel($document);

        $this->assertEquals(4, count($form->getSubForms()));

        $form->continueEdit($this->getRequest(), null);
    }

    public function testContinueEditRemoveSubForm()
    {
        $form = new Admin_Form_Files();

        $document = Document::get(91);

        $form->populateFromModel($document);

        $this->assertEquals(4, count($form->getSubForms()));

        $request = $this->getRequest();
        $request->setParam('fileId', 116);

        $post = null;

        $form->continueEdit($request, $post);

        $this->assertEquals(3, count($form->getSubForms()));
    }

    public function testContinueEditRemoveSubFormAndUpdate()
    {
        $form = new Admin_Form_Files();

        $document = Document::get(91);

        $form->populateFromModel($document);

        $this->assertEquals(4, count($form->getSubForms()));

        $request = $this->getRequest();
        $request->setParam('fileId', 116);

        $this->assertEmpty($form->getSubForm('File1')->getElementValue('Comment'));

        $post = [
            'File0' => [
                'Id' => '116',
            ],
            'File1' => [
                'Id'      => '127',
                'Comment' => 'Testkommentar',
            ],
        ];

        $form->continueEdit($request, $post);

        $this->assertEquals(3, count($form->getSubForms()));

        $this->assertEquals($form->getSubForm('File0')->getElementValue('Id'), '127');
        $this->assertEquals($form->getSubForm('File0')->getElementValue('Comment'), 'Testkommentar');
    }

    public function testGetSubFormForId()
    {
        $form = new Admin_Form_Files();

        $document = Document::get(91);

        $form->populateFromModel($document);

        $this->assertEquals(4, count($form->getSubForms()));

        $subform = $form->getSubFormForId(116);

        $this->assertEquals('File0', $subform->getName());
        $this->assertEquals(116, $subform->getElementValue('Id'));

        $this->assertNull($form->getSubFormForId(5555));
    }

    public function testFilesAppearInOrder()
    {
        $form = new Admin_Form_Files();

        $document = Document::get(155);

        $form->populateFromModel($document);

        $files = $document->getFile();

        $this->assertEquals(count($files), count($form->getSubForms()));

        $index = 0;

        foreach ($form->getSubForms() as $name => $subform) {
            $this->assertEquals(
                $files[$index]->getId(),
                $subform->getElement('Id')->getValue(),
                "Subform '$name' should have been at position $index."
            );
            $index++;
        }
    }

    public function testGetFieldValues()
    {
        $form = new Admin_Form_Files();

        $document = Document::get(155);

        $files = $document->getFile();

        $values = $form->getFieldValues($document);

        $this->assertEquals(count($files), count($values));

        foreach ($files as $index => $file) {
            $this->assertEquals($file->getId(), $values[$index]->getId(), 'Files are not in expected order.');
        }
    }
}
