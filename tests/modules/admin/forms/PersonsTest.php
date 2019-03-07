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
 * @copyright   Copyright (c) 2017-2019, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

class Admin_Form_PersonsTest extends ControllerTestCase
{

    private $_elementNames = [
        'LastName', 'FirstName', 'IdentifierGnd', 'IdentifierOrcid', 'IdentifierMisc',
        'Email', 'PlaceOfBirth', 'DateOfBirth', 'AcademicTitle', 'Save', 'Cancel', 'FormId'
    ];

    public function testCreateForm()
    {
        $form = new Admin_Form_Persons();

        $elements = $form->getElements();

        $this->assertCount(12, $elements);

        foreach ($this->_elementNames as $name) {
            $this->assertArrayHasKey($name, $elements);
        }
    }

    public function testAddUpdateFieldDecorators()
    {
        $form = new Admin_Form_Persons();

        $elementNames = $this->_elementNames;

        array_pop($elementNames); // do not check 'FormId'
        array_pop($elementNames); // do not check 'Cancel'
        array_pop($elementNames); // do not check 'Save'

        foreach ($elementNames as $name) {
            $element = $form->getElement($name);

            $decorator = $element->getDecorator('UpdateField');

            $this->assertNotFalse($decorator, "Field '$name' should have UpdateField decorator.");
            $this->assertNotNull($decorator, "Field '$name' should have UpdateField decorator.");
        }
    }

    public function testPopulateFromModelEmptyValues()
    {
        $values = [
            'last_name' => 'Smith',
            'first_name' => null,
            'identifier_orcid' => null,
            'identifier_gnd' => null,
            'identifier_misc' => null,
            'place_of_birth' => null,
            'date_of_birth' => null,
            'email' => null,
            'academic_title' => null
        ];

        $form = new Admin_Form_Persons();

        $form->populateFromModel($values);

        $this->assertEmpty($form->getElement('Email')->getMultiOptions());
        $this->assertEmpty($form->getElement('PlaceOfBirth')->getMultiOptions());
        $this->assertEmpty($form->getElement('DateOfBirth')->getMultiOptions());
        $this->assertEmpty($form->getElement('AcademicTitle')->getMultiOptions());
    }

    public function testPopulateFromModel()
    {
        $this->useEnglish();

        $values = [
            'last_name' => 'Smith',
            'first_name' => 'John',
            'identifier_orcid' => '0000-0000-1234-5678',
            'identifier_gnd' => '123456789',
            'identifier_misc' => 'id1234',
            'place_of_birth' => ['Berlin', 'Hamburg'],
            'date_of_birth' => ['2017-06-14', '2017-03-27', '2017-11-09'],
            'email' => 'test@example.org',
            'academic_title' => ['PhD', 'Dr.']
        ];

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

    public function testPopulateFromModelDatesGerman()
    {
        $this->useGerman();

        $form = new Admin_Form_Persons();

        $values = [
            'last_name' => 'Smith',
            'first_name' => 'John',
            'identifier_orcid' => '0000-0000-1234-5678',
            'identifier_gnd' => '123456789',
            'identifier_misc' => 'id1234',
            'place_of_birth' => ['Berlin', 'Hamburg'],
            'date_of_birth' => ['2017-06-14', '2017-03-27', '2017-11-09'],
            'email' => 'test@example.org',
            'academic_title' => ['PhD', 'Dr.']
        ];

        $form->populateFromModel($values);

        $element = $form->getElement('DateOfBirth');
        $this->assertNotNull($element);

        $options = $element->getMultiOptions();
        $this->assertCount(3, $options);
        $this->assertContains('14.06.2017', $options);
        $this->assertContains('27.03.2017', $options);
        $this->assertContains('09.11.2017', $options);
    }

    public function testPopulateFromModelSingleDateFormatting()
    {
        $this->useGerman();

        $form = new Admin_Form_Persons();

        $values = [
            'last_name' => 'Smith',
            'first_name' => 'John',
            'identifier_orcid' => '0000-0000-1234-5678',
            'identifier_gnd' => '123456789',
            'identifier_misc' => 'id1234',
            'place_of_birth' => ['Berlin', 'Hamburg'],
            'date_of_birth' => '2017-06-14',
            'email' => 'test@example.org',
            'academic_title' => ['PhD', 'Dr.']
        ];

        $form->populateFromModel($values);

        $element = $form->getElement('DateOfBirth');
        $this->assertNotNull($element);

        $options = $element->getMultiOptions();
        $this->assertCount(1, $options);
        $this->assertNotContains('2017-06-14', $options);
        $this->assertContains('14.06.2017', $options);
    }

    public function testPopulateFromModelSpacesMessageForIdentityFields()
    {
        $this->useEnglish();

        $form = new Admin_Form_Persons();

        $values = [
            'last_name' => ['Smith', ' Smith ', 'Smith  '],
            'first_name' => ['John', '  John', ' John '],
            'identifier_orcid' => ['0000-0000-1234-5678', '  0000-0000-1234-5678  '],
            'identifier_gnd' => ['123456789', ' 123456789 '],
            'identifier_misc' => ['id1234', 'id1234  ']
        ];

        $form->populateFromModel($values);

        $checkElements = ['LastName', 'FirstName', 'IdentifierOrcid', 'IdentifierGnd', 'IdentifierMisc'];

        foreach ($checkElements as $name)
        {
            $element = $form->getElement($name);
            $this->assertNotNull($element);

            $hint = $element->getHint();
            $this->assertNotNull($hint);
            $this->assertContains('Please select update to trim values when saving.', $hint);
        }
    }

    public function testPopulateFromModelUseSearchValuesForIdentityFields()
    {
        $this->useEnglish();

        $form = new Admin_Form_Persons();

        $values = [
            'last_name' => ['smith', ' Smith ', 'Smith  '],
            'first_name' => ['john', '  John', ' John ']
        ];

        $form->populateFromModel($values);

        $lastName = $form->getElement('LastName')->getValue();
        $firstName = $form->getElementValue('FirstName');

        $this->assertEquals('smith', $lastName);
        $this->assertEquals('john', $firstName);

        $form->setPerson(['last_name' => 'Smith', 'first_name' => 'John']);
        $form->populateFromModel($values);

        $lastName = $form->getElementValue('LastName');
        $firstName = $form->getElement('FirstName')->getValue();

        $this->assertEquals('Smith', $lastName);
        $this->assertEquals('John', $firstName);
    }

    public function testPopulateFromPost()
    {
        $post = [
            'LastName' => 'Smith',
            'FirstName' => 'John',
            'FirstNameUpdateEnabled' => 'on',
            'Email' => 'test@example.org',
            'PlaceOfBirth' => 'Berlin',
            'DateOfBirth' => '20.03.2003',
            'IdentifierOrcid' => '0000-0000-1234-5678',
            'IdentifierOrcidUpdateEnabled' => 'on',
            'IdentifierGnd' => '123456789',
            'IdentifierMisc' => 'id1234',
            'AcademicTitle' => 'PhD',
        ];

        $form = new Admin_Form_Persons();

        $form->populate($post);

        $this->assertEquals('Smith', $form->getElement('LastName')->getValue());
        $this->assertEquals('John', $form->getElement('FirstName')->getValue());
        $this->assertEquals('test@example.org', $form->getElement('Email')->getValue());
        $this->assertEquals('Berlin', $form->getElement('PlaceOfBirth')->getValue());
        $this->assertEquals('20.03.2003', $form->getElement('DateOfBirth')->getValue());
        $this->assertEquals('0000-0000-1234-5678', $form->getElement('IdentifierOrcid')->getValue());
        $this->assertEquals('123456789', $form->getElement('IdentifierGnd')->getValue());
        $this->assertEquals('id1234', $form->getElement('IdentifierMisc')->getValue());
        $this->assertEquals('PhD', $form->getElement('AcademicTitle')->getValue());

        $this->assertNull($form->getElement('LastName')->getAttrib('active'));
        $this->assertTrue($form->getElement('FirstName')->getAttrib('active'));
        $this->assertTrue($form->getElement('IdentifierOrcid')->getAttrib('active'));
        $this->assertNull($form->getElement('IdentifierGnd')->getAttrib('active'));
        $this->assertNull($form->getElement('IdentifierMisc')->getAttrib('active'));
        $this->assertNull($form->getElement('Email')->getAttrib('active'));
        $this->assertNull($form->getElement('PlaceOfBirth')->getAttrib('active'));
        $this->assertNull($form->getElement('DateOfBirth')->getAttrib('active'));
        $this->assertNull($form->getElement('AcademicTitle')->getAttrib('active'));
    }

    public function testValidationRequired()
    {
        $form = new Admin_Form_Persons();

        $this->assertTrue($form->isValid(['LastName' => 'Smith', 'LastNameUpdateEnabled' => 'on']));

        $this->assertFalse($form->isValid([]));
        $this->assertContains('isEmpty', $form->getErrors('LastName'));

        $this->assertFalse($form->isValid(['LastName' => '   ']));
        $this->assertContains('isEmpty', $form->getErrors('LastName'));
    }

    public function testValidateDates()
    {
        $this->useEnglish();

        $form = new Admin_Form_Persons();

        $this->assertTrue($form->isValid([
            'LastName' => 'Smith',
            'DateOfBirth' => '2017/07/23',
            'LastNameUpdateEnabled' => 'on'
        ]));

        $this->assertFalse($form->isValid([
            'LastName' => 'Smith',
            'DateOfBirth' => '2017',
            'LastNameUpdateEnabled' => 'on'
        ]));

        $this->assertFalse($form->isValid([
            'LastName' => 'Smith',
            'DateOfBirth' => '23.07.2017',
            'LastNameUpdateEnabled' => 'on'
        ]));

        $this->assertFalse($form->isValid([
            'LastName' => 'Smith',
            'DateOfBirth' => '2017/02/29',
            'LastNameUpdateEnabled' => 'on'
        ]));

        $this->assertTrue($form->isValid([
            'LastName' => 'Smith',
            'DateOfBirth' => '2016/02/29',
            'LastNameUpdateEnabled' => 'on'
        ]));
    }

    public function testValidateDatesGerman()
    {
        $this->useGerman();

        $form = new Admin_Form_Persons();

        $this->assertTrue($form->isValid([
            'LastName' => 'Schmidt',
            'DateOfBirth' => '23.07.2017',
            'LastNameUpdateEnabled' => 'on'
        ]));

        $this->assertFalse($form->isValid([
            'LastName' => 'Schmidt',
            'DateOfBirth' => '2017',
            'LastNameUpdateEnabled' => 'on'
        ]));

        $this->assertFalse($form->isValid([
            'LastName' => 'Schmidt',
            'DateOfBirth' => '2017/07/23',
            'LastNameUpdateEnabled' => 'on'
        ]));
    }


    public function testValidateEmail()
    {
        $form = new Admin_Form_Persons();

        $this->assertTrue($form->isValid([
            'LastName' => 'Smith',
            'Email' => 'test@example.org',
            'LastNameUpdateEnabled' => 'on'
        ]));

        $this->assertFalse($form->isValid([
            'LastName' => 'Smith',
            'Email' => 'test(at)example.org',
            'LastNameUpdateEnabled' => 'on'
        ]));

        $this->assertFalse($form->isValid([
            'LastName' => 'Smith',
            'Email' => 'test@',
            'LastNameUpdateEnabled' => 'on'
        ]));

        $this->assertFalse($form->isValid([
            'LastName' => 'Smith',
            'Email' => 'example.org',
            'LastNameUpdateEnabled' => 'on'
        ]));
    }

    public function testValidateIdentifierOrcid()
    {
        $form = new Admin_Form_Persons();

        $this->assertTrue($form->isValid([
            'LastName' => 'Smith',
            'IdentifierOrcid' => '0000-0002-1825-0097',
            'LastNameUpdateEnabled' => 'on'
        ]));

        $this->assertFalse($form->isValid([
            'LastName' => 'Smith',
            'IdentifierOrcid' => '0000000218250097',
            'LastNameUpdateEnabled' => 'on'
        ]));
    }

    public function testValidateIdentifierGnd()
    {
        $form = new Admin_Form_Persons();

        $this->assertTrue($form->isValid([
            'LastName' => 'Smith',
            'IdentifierGnd' => '118768581',
            'LastNameUpdateEnabled' => 'on'
        ]));

        $this->assertFalse($form->isValid([
            'LastName' => 'Smith',
            'IdentifierGnd' => '0118768581',
            'LastNameUpdateEnabled' => 'on'
        ]));
    }

    public function testGetChanges()
    {
        $form = new Admin_Form_Persons();

        $form->getElement('Email')->setValue('test@example.org')->setAttrib('active', true);
        $form->getElement('IdentifierMisc')->setValue('id1234');
        $form->getElement('PlaceOfBirth')->setAttrib('active', true);

        $changes = $form->getChanges();

        $this->assertNotNull($changes);
        $this->assertInternalType('array', $changes);
        $this->assertCount(2, $changes);

        $this->assertArrayHasKey('Email', $changes);
        $this->assertEquals('test@example.org', $changes['Email']);

        $this->assertArrayHasKey('PlaceOfBirth', $changes);
        $this->assertNull($changes['PlaceOfBirth']);
    }

    public function testGetChangesForDateOfBirth()
    {
        $this->useEnglish();

        $form = new Admin_Form_Persons();

        $form->getElement('DateOfBirth')->setValue('1968/10/23')->setAttrib('active', true);

        $changes = $form->getChanges();

        $this->assertNotNull($changes);
        $this->assertInternalType('array', $changes);
        $this->assertCount(1, $changes);

        $this->assertArrayHasKey('DateOfBirth', $changes);
        $this->assertEquals('1968-10-23', $changes['DateOfBirth']);
    }

    /**
     * If validation fails and the form is displayed again, manually entered values of comboboxes
     * should be kept.
     */
    public function testKeepPostValues()
    {
        $form = new Admin_Form_Persons();

        $form->populateFromModel([
            'first_name' => 'John',
            'last_name' => 'Smith',
            'identifier_orcid' => '',
            'identifier_gnd' => '',
            'identifier_misc' => '',
            'email' => '',
            'place_of_birth' => ['Berlin', 'München'],
            'date_of_birth' => null,
            'academic_title' => ''
        ]);

        $form->getElement('PlaceOfBirth')->setValue('Köln');

        $output = $form->render(Zend_Registry::get('Opus_View'));

        $this->assertContains('<option value="Köln">Köln</option>', $output);
        $this->assertContains('<option value="Berlin">Berlin</option>', $output);
        $this->assertContains('<option value="München">München</option>', $output);
    }

    public function testValidateOneFieldMustBeSelectedForUpdate()
    {
        $form = new Admin_Form_Persons();

        $this->assertFalse($form->isValid(['LastName' => 'Test']));

        $messages = $form->getErrorMessages();

        $this->assertNotNull($messages);
        $this->assertInternalType('array', $messages);
        $this->assertCount(1, $messages);
        $this->assertContains('admin_person_error_no_update', $messages);
    }

    public function testValidWithOneFieldSelectedForUpdate()
    {
        $form = new Admin_Form_Persons();

        $this->assertTrue($form->isValid(['LastName' => 'Test', 'LastNameUpdateEnabled' => 'on']));

        $messages = $form->getErrorMessages();

        $this->assertEmpty($messages);
    }
}
