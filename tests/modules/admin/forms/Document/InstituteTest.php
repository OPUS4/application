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
use Opus\Model\Dependent\Link\DocumentDnbInstitute;

/**
 * Unit Tests fuer Unterformular fuer Verknuepfung mit einem Institut im Metadaten-Formular.
 */
class Admin_Form_Document_InstituteTest extends ControllerTestCase
{
    /** @var string[] */
    protected $additionalResources = ['database'];

    public function testCreateForm()
    {
        $form = new Admin_Form_Document_Institute(Admin_Form_Document_Institute::ROLE_PUBLISHER);

        $this->assertEquals(2, count($form->getElements()));
        $this->assertNotNull($form->getElement('Id'));
        $this->assertNotNull($form->getElement('Institute'));
    }

    public function testCreateFormBadRole()
    {
        $this->expectException(Application_Exception::class);
        $this->expectExceptionMessage('Unknown role \'unknown_role\'.');
        $form = new Admin_Form_Document_Institute('unknown_role');
    }

    public function testPopulateFromModel()
    {
        $form = new Admin_Form_Document_Institute(Admin_Form_Document_Institute::ROLE_PUBLISHER);

        $doc        = Document::get(146);
        $publishers = $doc->getThesisPublisher();
        $publisher  = $publishers[0];

        $form->populateFromModel($publisher);

        $this->assertEquals($doc->getId(), $form->getElement('Id')->getValue());
        $this->assertEquals($publisher->getModel()->getId(), $form->getElement('Institute')->getValue());
    }

    public function testUpdateModel()
    {
        $form = new Admin_Form_Document_Institute(Admin_Form_Document_Institute::ROLE_PUBLISHER);

        $form->getElement('Institute')->setValue(3);

        $model = new DocumentDnbInstitute();

        $form->updateModel($model);

        $this->assertEquals(3, $model->getModel()->getId());
    }

    public function testGetModel()
    {
        $form = new Admin_Form_Document_Institute(Admin_Form_Document_Institute::ROLE_PUBLISHER);

        $doc         = Document::get(146);
        $publishers  = $doc->getThesisPublisher();
        $publisher   = $publishers[0];
        $publisherId = $publisher->getModel()->getId();

        $form->getElement('Id')->setValue($doc->getId());
        $form->getElement('Institute')->setValue($publisherId);

        $model   = $form->getModel();
        $modelId = $model->getId();

        $this->assertNotNull($model);
        $this->assertNotNull($modelId);
        $this->assertEquals($doc->getId(), $modelId[0]);
        $this->assertEquals($publisherId, $modelId[1]);
        $this->assertEquals('publisher', $modelId[2]);
        $this->assertEquals($publisherId, $model->getModel()->getId());
    }

    public function testGetNewModel()
    {
        $form = new Admin_Form_Document_Institute(Admin_Form_Document_Institute::ROLE_PUBLISHER);

        $form->getElement('Institute')->setValue(3);

        $model = $form->getModel();

        $this->assertNotNull($model);
        $this->assertNull($model->getId());
        $this->assertEquals(3, $model->getModel()->getId());
    }

    public function testValidation()
    {
        $form = new Admin_Form_Document_Institute(Admin_Form_Document_Institute::ROLE_PUBLISHER);

        $post = [];

        $this->assertFalse($form->isValid($post));
        $this->assertContains('isEmpty', $form->getErrors('Institute'));

        $post = [
            'Institute' => 'a',
        ];

        $this->assertFalse($form->isValid($post));
        $this->assertContains('notInt', $form->getErrors('Institute'));
    }
}
