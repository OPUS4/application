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

use Opus\Common\CollectionRole;

class Admin_Form_CollectionRoleTest extends ControllerTestCase
{
    /** @var string[] */
    protected $additionalResources = ['database', 'translation'];

    public function testConstructForm()
    {
        $form = new Admin_Form_CollectionRole();

        $this->assertCount(15, $form->getElements());

        $this->assertNotNull($form->getElement('Name'));
        $this->assertNotNull($form->getElement('DisplayName'));
        $this->assertNotNull($form->getElement('OaiName'));
        $this->assertNotNull($form->getElement('Position'));
        $this->assertNotNull($form->getElement('Visible'));
        $this->assertNotNull($form->getElement('VisibleBrowsingStart'));
        $this->assertNotNull($form->getElement('VisibleFrontdoor'));
        $this->assertNotNull($form->getElement('VisibleOai'));
        $this->assertNotNull($form->getElement('DisplayBrowsing'));
        $this->assertNotNull($form->getElement('DisplayFrontdoor'));
        $this->assertNotNull($form->getElement('AssignRoot'));
        $this->assertNotNull($form->getElement('AssignLeavesOnly'));
        $this->assertNotNull($form->getElement('HideEmptyCollections'));

        $this->assertNotNull($form->getElement('Save'));
        $this->assertNull($form->getElement('Cancel'));
        $this->assertNotNull($form->getElement('Id'));
    }

    public function testPopulateFromModel()
    {
        $form = new Admin_Form_CollectionRole();

        $model = CollectionRole::new();

        $model->setName('TestName');
        $model->setOaiName('TestOaiName');
        $model->setPosition(5);
        $model->setVisible(1);
        $model->setVisibleBrowsingStart(1);
        $model->setVisibleFrontdoor(1);
        $model->setVisibleOai(1);
        $model->setDisplayBrowsing('Name');
        $model->setDisplayFrontdoor('Number,Name');
        $model->setAssignRoot(1);
        $model->setAssignLeavesOnly(1);
        $model->setHideEmptyCollections(1);

        $form->populateFromModel($model);

        $this->assertEquals('TestName', $form->getElement('Name')->getValue());
        $this->assertEquals('TestOaiName', $form->getElement('OaiName')->getValue());
        $this->assertEquals(5, $form->getElement('Position')->getValue());
        $this->assertEquals(1, $form->getElement('Visible')->getValue());
        $this->assertEquals(1, $form->getElement('VisibleBrowsingStart')->getValue());
        $this->assertEquals(1, $form->getElement('VisibleFrontdoor')->getValue());
        $this->assertEquals(1, $form->getElement('VisibleOai')->getValue());
        $this->assertEquals('Name', $form->getElement('DisplayBrowsing')->getValue());
        $this->assertEquals('Number,Name', $form->getElement('DisplayFrontdoor')->getValue());
        $this->assertEquals(1, $form->getElement('AssignRoot')->getValue());
        $this->assertEquals(1, $form->getElement('AssignLeavesOnly')->getValue());
        $this->assertEquals(1, $form->getElement('HideEmptyCollections')->getValue());

        // no translations for unknown collection role
        $this->assertNull($form->getElement('DisplayName')->getValue());
    }

    public function testPopulateFromModelWithId()
    {
        $form = new Admin_Form_CollectionRole();

        $model = CollectionRole::get(2);

        $form->populateFromModel($model);

        $this->assertEquals(2, $form->getElement('Id')->getValue());
        $this->assertEquals('ddc', $form->getElement('Name')->getValue());

        // default translations for 'ddc' collection role
        $this->assertEquals([
            'en' => 'Dewey Decimal Classification',
            'de' => 'DDC-Klassifikation',
        ], $form->getElement('DisplayName')->getValue());
    }

    public function testUpdateModel()
    {
        $form = new Admin_Form_CollectionRole();

        $form->getElement('Id')->setValue(99);
        $form->getElement('Name')->setValue('TestName');
        $form->getElement('OaiName')->setValue('TestOaiName');
        $form->getElement('Position')->setValue(7);
        $form->getElement('Visible')->setValue(1);
        $form->getElement('VisibleBrowsingStart')->setValue(1);
        $form->getElement('VisibleFrontdoor')->setValue(1);
        $form->getElement('VisibleOai')->setValue(1);
        $form->getElement('DisplayBrowsing')->setValue('Number,Name');
        $form->getElement('DisplayFrontdoor')->setValue('Name,Number');
        $form->getElement('AssignRoot')->setValue(1);
        $form->getElement('AssignLeavesOnly')->setValue(1);
        $form->getElement('HideEmptyCollections')->setValue(1);

        $model = CollectionRole::new();

        $form->updateModel($model);

        $this->assertNull($model->getId());
        $this->assertEquals('TestName', $model->getName());
        $this->assertEquals('TestOaiName', $model->getOaiName());
        $this->assertEquals(7, $model->getPosition());
        $this->assertEquals(1, $model->getVisible());
        $this->assertEquals(1, $model->getVisibleBrowsingStart());
        $this->assertEquals(1, $model->getVisibleFrontdoor());
        $this->assertEquals(1, $model->getVisibleOai());
        $this->assertEquals('Number,Name', $model->getDisplayBrowsing());
        $this->assertEquals('Name,Number', $model->getDisplayFrontdoor());
        $this->assertEquals(1, $model->getAssignRoot());
        $this->assertEquals(1, $model->getAssignLeavesOnly());
        $this->assertEquals(1, $model->getHideEmptyCollections());
    }

    public function testValidationEmptyPost()
    {
        $form = new Admin_Form_CollectionRole();

        $this->assertFalse($form->isValid([]));

        $this->assertContains('isEmpty', $form->getErrors('Name'));
        $this->assertContains('isEmpty', $form->getErrors('OaiName'));
        $this->assertContains('isEmpty', $form->getErrors('DisplayBrowsing'));
        $this->assertContains('isEmpty', $form->getErrors('DisplayFrontdoor'));
    }

    public function testValidationSuccess()
    {
        $form = new Admin_Form_CollectionRole();

        $this->assertTrue($form->isValid([
            'Name'             => 'TestName',
            'OaiName'          => 'TestOaiName',
            'DisplayBrowsing'  => 'Name',
            'DisplayFrontdoor' => 'Name,Number',
        ]));
    }

    public function testValidationFailureBecauseOfConflict()
    {
        $form = new Admin_Form_CollectionRole();

        $this->assertFalse($form->isValid([
            'Name'             => 'institutes',
            'OaiName'          => 'institutes',
            'DisplayBrowsing'  => 'Name',
            'DisplayFrontdoor' => 'Name,Number',
        ]));

        $this->assertContains('notUnique', $form->getErrors('Name'));
        $this->assertContains('notUnique', $form->getErrors('OaiName'));
    }

    public function testValidationTrueForEditing()
    {
        $form = new Admin_Form_CollectionRole();

        $this->assertTrue($form->isValid([
            'Id'               => '1', // ID for 'institutes' CollectionRole
            'Name'             => 'institutes',
            'OaiName'          => 'institutes',
            'DisplayBrowsing'  => 'Name',
            'DisplayFrontdoor' => 'Name,Number',
        ]));
    }

    public function testValidationWithoutInvalidCharInCollectionRoleName()
    {
        $form = new Admin_Form_CollectionRole();

        $this->assertTrue($form->isValid([
            'Name'             => 'foobar',
            'OaiName'          => 'foobar',
            'DisplayBrowsing'  => 'Name',
            'DisplayFrontdoor' => 'Name,Number',
        ]));

        $this->assertNotContains('containsInvalidChar', $form->getErrors('Name'));
        $this->assertNotContains('containsInvalidChar', $form->getErrors('OaiName'));
    }

    public function testValidationWithInvalidCharInCollectionRoleName()
    {
        $form = new Admin_Form_CollectionRole();

        $this->assertFalse($form->isValid([
            'Name'             => 'foo bar',
            'OaiName'          => 'foo bar',
            'DisplayBrowsing'  => 'Name',
            'DisplayFrontdoor' => 'Name,Number',
        ]));

        $this->assertContains('containsInvalidChar', $form->getErrors('Name'));
        $this->assertNotContains('containsInvalidChar', $form->getErrors('OaiName'));
    }

    public function testPopulateFromPost()
    {
        $form = new Admin_Form_CollectionRole();

        $form->populate([
            'Name'        => 'testName',
            'DisplayName' => [
                'en' => 'English',
                'de' => 'Deutsch',
            ],
            'OaiName'     => 'testOaiName',
        ]);

        $this->assertEquals('testName', $form->getElementValue(Admin_Form_CollectionRole::ELEMENT_NAME));
        $this->assertEquals('testOaiName', $form->getElementValue(Admin_Form_CollectionRole::ELEMENT_OAI_NAME));
        $this->assertEquals([
            'en' => 'English',
            'de' => 'Deutsch',
        ], $form->getElementValue(Admin_Form_CollectionRole::ELEMENT_DISPLAYNAME));
    }
}
