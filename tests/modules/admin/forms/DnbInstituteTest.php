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

use Opus\Common\DnbInstitute;

class Admin_Form_DnbInstituteTest extends ControllerTestCase
{
    /** @var string[] */
    protected $additionalResources = ['database', 'translation'];

    public function testConstructForm()
    {
        $form = new Admin_Form_DnbInstitute();

        $this->assertCount(11, $form->getElements());

        $this->assertNotNull($form->getElement('Name'));
        $this->assertNotNull($form->getElement('Department'));
        $this->assertNotNull($form->getElement('Address'));
        $this->assertNotNull($form->getElement('City'));
        $this->assertNotNull($form->getElement('Phone'));
        $this->assertNotNull($form->getElement('DnbContactId'));
        $this->assertNotNull($form->getElement('IsGrantor'));
        $this->assertNotNull($form->getElement('IsPublisher'));

        $this->assertNotNull($form->getElement('Save'));
        $this->assertNotNull($form->getElement('Cancel'));
        $this->assertNotNull($form->getElement('Id'));
    }

    public function testPopulateFromModel()
    {
        $form = new Admin_Form_DnbInstitute();

        $model = DnbInstitute::new();
        $model->setName('TestName');
        $model->setDepartment('TestDepartment');
        $model->setAddress('TestAddress');
        $model->setCity('TestCity');
        $model->setPhone('TestPhone');
        $model->setDnbContactId('TestDnbContactId');
        $model->setIsGrantor(true);
        $model->setIsPublisher(false);

        $form->populateFromModel($model);

        $this->assertEquals('TestName', $form->getElement('Name')->getValue());
        $this->assertEquals('TestDepartment', $form->getElement('Department')->getValue());
        $this->assertEquals('TestAddress', $form->getElement('Address')->getValue());
        $this->assertEquals('TestCity', $form->getElement('City')->getValue());
        $this->assertEquals('TestPhone', $form->getElement('Phone')->getValue());
        $this->assertEquals('TestDnbContactId', $form->getElement('DnbContactId')->getValue());
        $this->assertEquals(1, $form->getElement('IsGrantor')->getValue());
        $this->assertEquals('0', $form->getElement('IsPublisher')->getValue());

        $this->assertNull($form->getElement('Id')->getValue());
    }

    public function testPopulateFromModelWithId()
    {
        $form = new Admin_Form_DnbInstitute();

        $model = DnbInstitute::get(2);

        $form->populateFromModel($model);

        $this->assertEquals(2, $form->getElement('Id')->getValue());
    }

    public function testUpdateModel()
    {
        $form = new Admin_Form_DnbInstitute();

        $form->getElement('Id')->setValue(99);
        $form->getElement('Name')->setValue('TestName');
        $form->getElement('Department')->setValue('TestDepartment');
        $form->getElement('Address')->setValue('TestAddress');
        $form->getElement('City')->setValue('TestCity');
        $form->getElement('Phone')->setValue('TestPhone');
        $form->getElement('IsGrantor')->setChecked(true);
        $form->getElement('IsPublisher')->setChecked(false);

        $model = DnbInstitute::new();

        $form->updateModel($model);

        $this->assertNull($model->getId());
        $this->assertEquals('TestName', $model->getName());
        $this->assertEquals('TestDepartment', $model->getDepartment());
        $this->assertEquals('TestAddress', $model->getAddress());
        $this->assertEquals('TestCity', $model->getCity());
        $this->assertEquals('TestPhone', $model->getPhone());
        $this->assertEquals('1', $model->getIsGrantor());
        $this->assertEquals('0', $model->getIsPublisher());
    }

    public function testValidationEmptyPost()
    {
        $form = new Admin_Form_DnbInstitute();

        $this->assertFalse($form->isValid([]));

        $this->assertContains('isEmpty', $form->getErrors('Name'));
        $this->assertContains('isEmpty', $form->getErrors('City'));
    }

    public function testValidationEmptyFields()
    {
        $form = new Admin_Form_DnbInstitute();

        $this->assertFalse($form->isValid([
            'Name' => '   ',
            'City' => ' ',
        ]));

        $this->assertContains('isEmpty', $form->getErrors('Name'));
        $this->assertContains('isEmpty', $form->getErrors('City'));
    }

    public function testValidationTrue()
    {
        $form = new Admin_Form_DnbInstitute();

        $this->assertTrue($form->isValid([
            'Name' => 'OPUS 4 University',
            'City' => 'Berlin',
        ]));
    }

    public function testTranslationKeysForElements()
    {
        $form = new Admin_Form_DnbInstitute();

        foreach ($form->getElements() as $name => $element) {
            $label = $element->getLabel();
            if ($label !== null) {
                $this->assertFalse(
                    strpos($label, 'Opus_DnbInstitute_'),
                    "Element '$name' is not translated."
                );
            }
        }
    }
}
