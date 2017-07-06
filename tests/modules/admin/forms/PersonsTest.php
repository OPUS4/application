<?php
/*
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
 * @category    Tests
 * @package     Admin_Form
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2017, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

class Admin_Form_PersonsTest extends ControllerTestCase
{

    private $_elementNames = array(
        'LastName', 'FirstName', 'IdentifierGnd', 'IdentifierOrcid', 'IdentifierMisc',
        'Email', 'PlaceOfBirth', 'DateOfBirth', 'AcademicTitle', 'Save', 'Cancel'
    );

    public function testCreateForm()
    {
        $form = new Admin_Form_Persons();

        $elements = $form->getElements();

        $this->assertCount(11, $elements);

        foreach ($this->_elementNames as $name)
        {
            $this->assertArrayHasKey($name, $elements);
        }
    }

    public function testAddUpdateFieldDecorators()
    {
        $form = new Admin_Form_Persons();

        $elementNames = $this->_elementNames;

        array_pop($elementNames); // do not check 'Cancel'
        array_pop($elementNames); // do not check 'Save'

        foreach ($elementNames as $name)
        {
            $element = $form->getElement($name);

            $decorator = $element->getDecorator('UpdateField');

            $this->assertNotFalse($decorator, "Field '$name' should have UpdateField decorator.");
            $this->assertNotNull($decorator, "Field '$name' should have UpdateField decorator.");
        }
    }

    public function testPopulateFromModel()
    {
        $this->useEnglish();

        $values = array(
            'last_name' => 'Smith',
            'first_name' => 'John',
            'identifier_orcid' => '0000-0000-1234-5678',
            'identifier_gnd' => '123456789',
            'identifier_misc' => 'id1234',
            'place_of_birth' => array('Berlin', 'Hamburg'),
            'date_of_birth' => array('2017-06-14', '2017-03-27', '2017-11-09'),
            'email' => 'test@example.org',
            'academic_title' => array('PhD', 'Dr.')
        );

        $form = new Admin_Form_Persons();

        $form->populateFromModel($values);

        $this->assertEquals('Smith', $form->getElementValue('LastName'));
        $this->assertEquals('John', $form->getElementValue('FirstName'));
        $this->assertEquals('0000-0000-1234-5678', $form->getElementValue('IdentifierOrcid'));
        $this->assertEquals('123456789', $form->getElementValue('IdentifierGnd'));
        $this->assertEquals('id1234', $form->getElementValue('IdentifierMisc'));

        // the combobox fields do not get populated with a specific value
        $this->assertNull($form->getElementValue('Email'));
        $this->assertNull($form->getElementValue('PlaceOfBirth'));
        $this->assertNull($form->getElementValue('DateOfBirth'));
        $this->assertNull($form->getElementValue('AcademicTitle'));

        $options = $form->getElement('Email')->getMultiOptions();
        $this->assertCount(1, $options);
        $this->assertContains('test@example.org', $options);
        $this->assertArrayHasKey('test@example.org', $options);

        $options = $form->getElement('PlaceOfBirth')->getMultiOptions();
        $this->assertCount(2, $options);
        $this->assertContains('Berlin', $options);
        $this->assertArrayHasKey('Berlin', $options);
        $this->assertContains('Hamburg', $options);
        $this->assertArrayHasKey('Hamburg', $options);

        $options = $form->getElement('DateOfBirth')->getMultiOptions();
        $this->assertCount(3, $options);
        $this->assertContains('2017/06/14', $options);
        $this->assertContains('2017/03/27', $options);
        $this->assertContains('2017/11/09', $options);

        $options = $form->getElement('AcademicTitle')->getMultiOptions();
        $this->assertCount(2, $options);
        $this->assertContains('PhD',$options);
        $this->assertContains('Dr.', $options);
    }

    public function testPopulateDatesGerman()
    {
        $this->useGerman();

        $form = new Admin_Form_Persons();

        $values = array(
            'last_name' => 'Smith',
            'first_name' => 'John',
            'identifier_orcid' => '0000-0000-1234-5678',
            'identifier_gnd' => '123456789',
            'identifier_misc' => 'id1234',
            'place_of_birth' => array('Berlin', 'Hamburg'),
            'date_of_birth' => array('2017-06-14', '2017-03-27', '2017-11-09'),
            'email' => 'test@example.org',
            'academic_title' => array('PhD', 'Dr.')
        );

        $form->populateFromModel($values);

        $element = $form->getElement('DateOfBirth');
        $this->assertNotNull($element);

        $options = $element->getMultiOptions();
        $this->assertCount(3, $options);
        $this->assertContains('14.06.2017', $options);
        $this->assertContains('27.03.2017', $options);
        $this->assertContains('09.11.2017', $options);
    }

    public function testPopulateFromPost()
    {

    }

    public function testValidationTrue()
    {

    }

    public function testValidationFalse()
    {

    }

    public function testGetChanges()
    {
        $form = new Admin_Form_Persons();

        $form->getElement('Email')->setValue('test@example.org')->setAttrib('active', true);

        $changes = $form->getChanges();

        $this->assertCount(1, $changes);
        $this->assertEquals(array('Email' => 'test@example.org'), $changes);
    }

    /**
     * If validation fails and the form is displayed again, manually enter values of comboboxes
     * should be kept.
     */
    public function testKeepPostValues() {

    }

}