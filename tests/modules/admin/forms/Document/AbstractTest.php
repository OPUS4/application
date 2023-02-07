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
use Opus\Common\TitleAbstract;

/**
 * Unit Tests fuer Unterformular fuer Zusammenfassungen.
 */
class Admin_Form_Document_AbstractTest extends ControllerTestCase
{
    /** @var string[] */
    protected $additionalResources = ['view', 'translation'];

    public function testCreateForm()
    {
        $form = new Admin_Form_Document_Abstract();

        $this->assertEquals(3, count($form->getElements()));
        $this->assertNotNull($form->getElement('Id'));
        $this->assertNotNull($form->getElement('Language'));
        $this->assertNotNull($form->getElement('Value'));
    }

    public function testPopulateFromModel()
    {
        $form = new Admin_Form_Document_Abstract();

        $doc = Document::get(146);

        $abstracts = $doc->getTitleAbstract();

        $abstract = $abstracts[0];

        $form->populateFromModel($abstract);

        $this->assertEquals($abstract->getId(), $form->getElement('Id')->getValue());
        $this->assertEquals($abstract->getLanguage(), $form->getElement('Language')->getValue());
        $this->assertEquals($abstract->getValue(), $form->getElement('Value')->getValue());

        $this->assertFalse($form->getDecorator('Fieldset'));
    }

    public function testUpdateModel()
    {
        $form = new Admin_Form_Document_Abstract();

        $form->getElement('Language')->setValue('eng');
        $form->getElement('Value')->setValue('Test Zusammenfassung!');

        $abstract = TitleAbstract::new();

        $form->updateModel($abstract);

        $this->assertEquals('eng', $abstract->getLanguage());
        $this->assertEquals('Test Zusammenfassung!', $abstract->getValue());
    }

    public function testGetModel()
    {
        $form = new Admin_Form_Document_Abstract();

        $doc = Document::get(146);

        $abstracts = $doc->getTitleAbstract();

        $abstract = $abstracts[0];

        $form->getElement('Id')->setValue($abstract->getId());
        $form->getElement('Language')->setValue('eng');
        $form->getElement('Value')->setValue('Test Zusammenfassung!');

        $model = $form->getModel();

        $this->assertEquals($abstract->getId(), $model->getId());
        $this->assertEquals('eng', $model->getLanguage());
        $this->assertEquals('Test Zusammenfassung!', $model->getValue());
    }

    public function testGetNewModel()
    {
        $form = new Admin_Form_Document_Abstract();
        $form->getElement('Language')->setValue('eng');
        $form->getElement('Value')->setValue('Test Zusammenfassung!');

        $model = $form->getModel();

        $this->assertNull($model->getId());
        $this->assertEquals('eng', $model->getLanguage());
        $this->assertEquals('Test Zusammenfassung!', $model->getValue());
    }

    public function testGetModelBadId()
    {
        $form = new Admin_Form_Document_Abstract();
        $form->getElement('Id')->setValue('bad');
        $form->getElement('Language')->setValue('eng');
        $form->getElement('Value')->setValue('Test Zusammenfassung!');

        $model = $form->getModel();

        $this->assertNull($model->getId());
        $this->assertEquals('eng', $model->getLanguage());
        $this->assertEquals('Test Zusammenfassung!', $model->getValue());
    }

    public function testGetModelUnknownId()
    {
        $form = new Admin_Form_Document_Abstract();

        $logger = new MockLogger();

        $form->setLogger($logger);
        $form->getElement('Id')->setValue(9999);
        $form->getElement('Language')->setValue('eng');
        $form->getElement('Value')->setValue('Test Zusammenfassung!');

        $model = $form->getModel();

        $this->assertNull($model->getId());
        $this->assertEquals('eng', $model->getLanguage());
        $this->assertEquals('Test Zusammenfassung!', $model->getValue());

        $messages = $logger->getMessages();

        $this->assertEquals(1, count($messages));
        $this->assertContains('Unknown ID = \'9999\'', $messages[0]);
    }

    public function testValidation()
    {
        $form = new Admin_Form_Document_Abstract();

        $post = [
            'Language' => 'rus',
            'Value'    => ' ',
        ];

        $this->assertFalse($form->isValid($post));

        $this->assertContains('isEmpty', $form->getErrors('Value'));
    }
}
