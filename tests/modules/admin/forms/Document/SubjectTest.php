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
 * @copyright   Copyright (c) 2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\Document;
use Opus\Common\Subject;

/**
 * Unit Tests fuer Unterformular fuer ein Subject im Metadaten-Formular.
 */
class Admin_Form_Document_SubjectTest extends ControllerTestCase
{
    /** @var string[] */
    protected $additionalResources = ['view', 'translation'];

    public function testCreateForm()
    {
        $form = new Admin_Form_Document_Subject('psyndex');

        $this->assertCount(4, $form->getElements());

        $this->assertNotNull($form->getElement('Id'));
        $this->assertNotNull($form->getElement('Value'));
        $this->assertNotNull($form->getElement('ExternalKey'));
        $this->assertNotNull($form->getElement('Language'));

        $this->assertEquals('psyndex', $form->getSubjectType());
        $this->assertNull($form->getLanguage());
    }

    public function testCreateFormWithLanguage()
    {
        $form = new Admin_Form_Document_Subject('swd', 'deu');

        $this->assertCount(4, $form->getElements());

        $this->assertNotNull($form->getElement('Id'));
        $this->assertNotNull($form->getElement('Value'));
        $this->assertNotNull($form->getElement('ExternalKey'));

        $language = $form->getElement('Language');
        $this->assertNotNull($language);
        $this->assertInstanceOf('Zend_Form_Element_Hidden', $language);
        $this->assertEquals('deu', $language->getValue());

        $this->assertEquals('swd', $form->getSubjectType());
        $this->assertEquals('deu', $form->getLanguage());
    }

    public function testPopulateFromModel()
    {
        $form = new Admin_Form_Document_Subject('swd', 'deu');

        $document   = Document::get(146);
        $subjects   = $document->getSubject();
        $subjectSwd = $subjects[0];

        $this->assertEquals('swd', $subjectSwd->getType());

        $form->populateFromModel($subjectSwd);

        $this->assertEquals($subjectSwd->getId(), $form->getElement('Id')->getValue());
        $this->assertEquals($subjectSwd->getLanguage(), $form->getElement('Language')->getValue());
        $this->assertEquals($subjectSwd->getValue(), $form->getElement('Value')->getValue());
        $this->assertEquals($subjectSwd->getExternalKey(), $form->getElement('ExternalKey')->getValue());
    }

    public function testUpdateModel()
    {
        $form = new Admin_Form_Document_Subject('psyndex');

        $form->getElement('Language')->setValue('eng');
        $form->getElement('Value')->setValue('Test Schlagwort');
        $form->getElement('ExternalKey')->setValue('Test Schluessel');

        $subject = Subject::new();

        $form->updateModel($subject);

        $this->assertEquals('eng', $subject->getLanguage());
        $this->assertEquals('Test Schlagwort', $subject->getValue());
        $this->assertEquals('Test Schluessel', $subject->getExternalKey());
        $this->assertEquals('psyndex', $subject->getType());
    }

    public function testGetModel()
    {
        $form = new Admin_Form_Document_Subject('uncontrolled');

        $document = Document::get(146);
        $subjects = $document->getSubject();
        $subject  = $subjects[1];

        $this->assertEquals('uncontrolled', $subject->getType());

        $form->getElement('Id')->setValue($subject->getId());
        $form->getElement('Language')->setValue('rus');
        $form->getElement('Value')->setValue('Test Schlagwort');
        $form->getElement('ExternalKey')->setValue('Test Key');

        $model = $form->getModel();

        $this->assertEquals($subject->getId(), $model->getId());
        $this->assertEquals('rus', $model->getLanguage());
        $this->assertEquals('Test Schlagwort', $model->getValue());
        $this->assertEquals('Test Key', $model->getExternalKey());
        $this->assertEquals('uncontrolled', $model->getType());
    }

    public function testGetNewModel()
    {
        $form = new Admin_Form_Document_Subject('swd', 'deu');

        $form->getElement('Value')->setValue('Test Schlagwort');
        $form->getElement('ExternalKey')->setValue('Test Key');

        $model = $form->getModel();

        $this->assertNull($model->getId());
        $this->assertEquals('deu', $model->getLanguage());
        $this->assertEquals('Test Schlagwort', $model->getValue());
        $this->assertEquals('Test Key', $model->getExternalKey());
        $this->assertEquals('swd', $model->getType());
    }

    public function testGetModelUnknownId()
    {
        $form = new Admin_Form_Document_Subject('uncontrolled');

        $form->getElement('Id')->setValue('7777');
        $form->getElement('Language')->setValue('rus');
        $form->getElement('Value')->setValue('Test Schlagwort');
        $form->getElement('ExternalKey')->setValue('Test Key');

        $logger = new MockLogger();
        $form->setLogger($logger);

        $model = $form->getModel();

        $this->assertNull($model->getId());
        $this->assertEquals('rus', $model->getLanguage());
        $this->assertEquals('Test Schlagwort', $model->getValue());
        $this->assertEquals('Test Key', $model->getExternalKey());
        $this->assertEquals('uncontrolled', $model->getType());

        $messages = $logger->getMessages();

        $this->assertCount(1, $messages);
        $this->assertContains('Unknown subject ID = \'7777\'.', $messages[0]);
    }

    public function testGetModelBadId()
    {
        $form = new Admin_Form_Document_Subject('uncontrolled');

        $form->getElement('Id')->setValue('bad');
        $form->getElement('Language')->setValue('rus');
        $form->getElement('Value')->setValue('Test Schlagwort');
        $form->getElement('ExternalKey')->setValue('Test Key');

        $model = $form->getModel();

        $this->assertNull($model->getId());
        $this->assertEquals('rus', $model->getLanguage());
        $this->assertEquals('Test Schlagwort', $model->getValue());
        $this->assertEquals('Test Key', $model->getExternalKey());
        $this->assertEquals('uncontrolled', $model->getType());
    }

    public function testValidation()
    {
        $form = new Admin_Form_Document_Subject('swd', 'deu');

        $post = [
            'Value' => ' ', // darf nicht leer sein
        ];

        $this->assertFalse($form->isValid($post));

        $this->assertContains('isEmpty', $form->getErrors('Value'));
    }

    public function testPrepareRenderingAsView()
    {
        $form = new Admin_Form_Document_Subject('swd', 'deu');

        $form->prepareRenderingAsView();

        $this->assertNotNull($form->getElement('Id'));
        $this->assertNotNull($form->getElement('Language'));
        $this->assertNotNull($form->getElement('Value'));
        $this->assertNotNull($form->getElement('ExternalKey'));
        $this->assertNull($form->getElement('Remove'));
    }
}
