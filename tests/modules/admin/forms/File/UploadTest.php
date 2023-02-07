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

class Admin_Form_File_UploadTest extends ControllerTestCase
{
    /** @var string[] */
    protected $additionalResources = ['view', 'translation'];

    /** @var int */
    private $documentId;

    public function tearDown(): void
    {
        $this->removeDocument($this->documentId);

        parent::tearDown();
    }

    public function testCreateForm()
    {
        $form = new Admin_Form_File_Upload();

        $elements = ['Id', 'File', 'Label', 'Comment', 'Language', 'Save', 'Cancel', 'OpusHash', 'SortOrder'];

        $this->assertSameSize($elements, $form->getElements());

        foreach ($elements as $element) {
            $this->assertNotNull($form->getElement($element), "Element '$element' is missing.'");
        }

        $this->assertCount(1, $form->getSubForms());
        $this->assertNotNull($form->getSubForm('Info'));

        $this->assertEquals('admin_filemanager_upload', $form->getLegend());
    }

    public function testPopulateFromModel()
    {
        $document = Document::get(146);

        $form = new Admin_Form_File_Upload();

        $form->populateFromModel($document);

        $this->assertEquals(146, $form->getElementValue('Id'));

        $infoForm = $form->getSubForm('Info');

        $this->assertEquals($document, $infoForm->getDocument());
    }

    public function testValidation()
    {
        $form = new Admin_Form_File_Upload();

        $post = [];

        $result = $form->isValid($post);

        $this->assertFalse($result);

        $this->assertContains('isEmpty', $form->getErrors('Id'));
        $this->assertContains('isEmpty', $form->getErrors('Language'));
        $this->assertContains('notInArray', $form->getErrors('Language'));
        $this->assertContains('missingToken', $form->getErrors('OpusHash'));
    }

    public function testUpdateModel()
    {
        $form = new Admin_Form_File_Upload();

        $form->getElement('Label')->setValue('Testlabel');
        $form->getElement('Comment')->setValue('Testkommentar');
        $form->getElement('Language')->setValue('rus');

        $document = $this->createTestDocument();

        $fileInfo = [
            [
                'name'     => 'test%202.txt',
                'type'     => 'text/plain',
                'tmp_name' => 'test',
            ],
        ];

        $form->setFileInfo($fileInfo);
        $form->updateModel($document);

        $files = $document->getFile();

        $this->assertCount(1, $files);

        $file = $files[0];

        $this->assertEquals('Testlabel', $file->getLabel());
        $this->assertEquals('Testkommentar', $file->getComment());
        $this->assertEquals('rus', $file->getLanguage());
        $this->assertEquals('test 2.txt', $file->getPathName()); // urldecode
        $this->assertEquals('text/plain', $file->getMimeType());
        $this->assertEquals('test', $file->getTempFile());
    }

    public function testGetFileInfo()
    {
        $form = new Admin_Form_File_Upload();

        $fileInfo = $form->getFileInfo();

        $this->assertInternalType('array', $fileInfo);
        $this->assertCount(0, $fileInfo);
    }

    public function testSetGetFileInfo()
    {
        $form = new Admin_Form_File_Upload();

        // entspricht nicht der richtige Struktur, reicht aber für Test
        $fileInfo = [
            ['file'],
        ];

        $form->setFileInfo($fileInfo);

        $this->assertEquals($fileInfo, $form->getFileInfo());
    }
}
