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
use Opus\Common\Title;

/**
 * Unit Test fuer Unterformular fuer einen Titel.
 */
class Admin_Form_Document_TitleTest extends ControllerTestCase
{
    /** @var string[] */
    protected $additionalResources = ['view', 'translation'];

    public function testCreateForm()
    {
        $form = new Admin_Form_Document_Title();

        $this->assertNotNull($form->getElement('Id'));
        $this->assertNotNull($form->getElement('Type'));
        $this->assertNotNull($form->getElement('Language'));
        $this->assertNotNull($form->getElement('Value'));
    }

    public function testPopulateFromModel()
    {
        $form = new Admin_Form_Document_Title();

        $doc = Document::get(146);

        $titles = $doc->getTitleMain();

        $title = $titles[0];

        $form->populateFromModel($title);

        $this->assertEquals($title->getId(), $form->getElement('Id')->getValue());
        $this->assertEquals($title->getType(), $form->getElement('Type')->getValue());
        $this->assertEquals($title->getLanguage(), $form->getElement('Language')->getValue());
        $this->assertEquals($title->getValue(), $form->getElement('Value')->getValue());
    }

    public function testUpdateModel()
    {
        $form = new Admin_Form_Document_Title();

        $form->getElement('Type')->setValue('main');
        $form->getElement('Language')->setValue('rus');
        $form->getElement('Value')->setValue('Test Title');

        $title = Title::new();

        $form->updateModel($title);

        $this->assertEquals('main', $title->getType());
        $this->assertEquals('rus', $title->getLanguage());
        $this->assertEquals('Test Title', $title->getValue());
    }

    public function testGetModel()
    {
        $form = new Admin_Form_Document_Title();

        $doc = Document::get(146);

        $titles = $doc->getTitleMain();

        $title = $titles[0];

        $form->getElement('Id')->setValue($title->getId());
        $form->getElement('Type')->setValue('parent');
        $form->getElement('Language')->setValue('rus');
        $form->getElement('Value')->setValue('Test Title');

        $model = $form->getModel();

        $this->assertEquals($title->getId(), $model->getId());
        $this->assertEquals('parent', $model->getType());
        $this->assertEquals('rus', $model->getLanguage());
        $this->assertEquals('Test Title', $model->getValue());
    }

    public function testGetNewModel()
    {
        $form = new Admin_Form_Document_Title();

        $form->getElement('Type')->setValue('parent');
        $form->getElement('Language')->setValue('rus');
        $form->getElement('Value')->setValue('Test Title');

        $model = $form->getModel();

        $this->assertNull($model->getId());
        $this->assertEquals('parent', $model->getType());
        $this->assertEquals('rus', $model->getLanguage());
        $this->assertEquals('Test Title', $model->getValue());
    }

    /**
     * TODO Validierung ausbauen (Type, Language)
     */
    public function testValidation()
    {
        $this->disableTranslation();

        $form = new Admin_Form_Document_Title();

        $post = [
            'Type'     => 'parent',
            'Language' => 'rus',
            'Value'    => '',
        ];

        $this->assertFalse($form->isValid($post));

        $this->assertContains('admin_validate_error_notempty', $form->getErrors('Value'));
    }
}
