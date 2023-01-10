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

use Opus\Common\Collection;

class Admin_Form_CollectionTest extends ControllerTestCase
{
    /** @var string[] */
    protected $additionalResources = ['database'];

    public function testConstructForm()
    {
        $form = new Admin_Form_Collection();

        $this->assertCount(7, $form->getElements());
        $this->assertNotNull($form->getElement('Name'));
        $this->assertNotNull($form->getElement('Number'));
        $this->assertNotNull($form->getElement('Visible'));
        $this->assertNotNull($form->getElement('VisiblePublish'));
        $this->assertNotNull($form->getElement('OaiSubset'));
        //$this->assertNotNull($form->getElement('Theme'));
        $this->assertNotNull($form->getElement('Save'));
        $this->assertNull($form->getElement('Cancel'));
        $this->assertNotNull($form->getElement('Id'));
    }

    public function testPopulateFromModel()
    {
        $form = new Admin_Form_Collection();

        $model = Collection::new();

        $model->setName('TestName');
        $model->setNumber('50');
        $model->setVisible(1);
        $model->setVisiblePublish(1);
        $model->setOaiSubset('TestSubset');
        // $model->setTheme('plain');
        $form->populateFromModel($model);
        $this->assertEquals('TestName', $form->getElement('Name')->getValue());
        $this->assertEquals(50, $form->getElement('Number')->getValue());
        $this->assertEquals(1, $form->getElement('Visible')->getValue());
        $this->assertEquals(1, $form->getElement('VisiblePublish')->getValue());
        $this->assertEquals('TestSubset', $form->getElement('OaiSubset')->getValue());
        // $this->assertEquals('plain', $form->getElement('Theme')->getValue());
    }

    public function testHandlingOfNullValue()
    {
        $form = new Admin_Form_Collection();

        $model = Collection::new();

        $model->setName('TestName');
        $model->setNumber(null);
        $model->setOaiSubset(null);

        $form->populateFromModel($model);

        $this->assertEquals('TestName', $form->getElement('Name')->getValue());
        $this->assertNull($form->getElement('Number')->getValue());
        $this->assertNull($form->getElement('OaiSubset')->getValue());
    }

    public function testPopulateFromModelWithId()
    {
        $form = new Admin_Form_Collection();

        $model = Collection::get(3);

        $form->populateFromModel($model);

        $this->assertEquals(3, $form->getElement('Id')->getValue());
    }

    public function testUpdateModel()
    {
        $form = new Admin_Form_Collection();

        $form->getElement('Id')->setValue(99);
        $form->getElement('Name')->setValue('TestName');
        $form->getElement('Number')->setValue('50');
        $form->getElement('Visible')->setValue('1');
        $form->getElement('VisiblePublish')->setValue('1');
        $form->getElement('OaiSubset')->setValue('TestSubset');
        // $form->getElement('Theme')->setValue('plain');

        $model = Collection::new();

        $form->updateModel($model);

        $this->assertNull($model->getId());
        $this->assertEquals('TestName', $model->getName());
        $this->assertEquals('50', $model->getNumber());
        $this->assertEquals('1', $model->getVisible());
        $this->assertEquals('1', $model->getVisiblePublish());
        $this->assertEquals('TestSubset', $model->getOaiSubset());
        //$this->assertEquals('plain', $model->getTheme());
    }

    public function testValidationSuccess()
    {
        $form = new Admin_Form_Collection();

        $this->assertTrue($form->isValid([
            'Name' => 'ColName',
        ]));

        $this->assertTrue($form->isValid([
            'Number' => 'ColNumber',
        ]));

        $this->assertTrue($form->isValid([
            'Name'   => 'ColName',
            'Number' => 'ColNumber',
        ]));
    }

    public function testValidationFailure()
    {
        $form = new Admin_Form_Collection();

        $this->assertFalse($form->isValid([]));

        $errors = $form->getErrors('Name');
        $this->assertNotNull($errors);
        $this->assertContains('allElementsEmpty', $errors);

        $errors = $form->getErrors('Number');
        $this->assertNotNull($errors);
        $this->assertContains('allElementsEmpty', $errors);
    }
}
