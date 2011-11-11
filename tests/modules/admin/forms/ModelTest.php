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
 * @category    Application Unit Test
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class Admin_Form_ModelTest extends ControllerTestCase {

    public function testCreatePersonForm() {
        $person = new Opus_Person();

        $form = new Admin_Form_Model($person);

        $this->assertEquals(6, count($form->getElements()));
        $this->assertNotNull($form->getElement('AcademicTitle'));
        $this->assertNotNull($form->getElement('FirstName'));
        $this->assertNotNull($form->getElement('LastName'));
        $this->assertNotNull($form->getElement('DateOfBirth'));
        $this->assertNotNull($form->getElement('PlaceOfBirth'));
        $this->assertNotNull($form->getElement('Email'));
    }

    public function testCreateDocumentPersonForm() {
        $doc = new Opus_Document();

        $person = new Opus_Person();

        $doc->addPerson($person);

        $persons = $doc->getPerson();

        $person = $persons[0];

        $form = new Admin_Form_Model($person);

        $this->assertEquals(9, count($form->getElements()));
        $this->assertNotNull($form->getElement('Role'));
        $this->assertNotNull($form->getElement('AcademicTitle'));
        $this->assertNotNull($form->getElement('FirstName'));
        $this->assertNotNull($form->getElement('LastName'));
        $this->assertNotNull($form->getElement('DateOfBirth'));
        $this->assertNotNull($form->getElement('PlaceOfBirth'));
        $this->assertNotNull($form->getElement('Email'));
        $this->assertNotNull($form->getElement('SortOrder'));
        $this->assertNotNull($form->getElement('AllowEmailContact'));
    }

    public function testIsFieldDisabled() {
        $doc = new Opus_Document();

        $form = new Admin_Form_Model($doc);

        $this->assertTrue($form->isFieldDisabled('ServerDatePublished'));
        $this->assertTrue($form->isFieldDisabled('ServerDateModified'));
    }

    public function testIsFieldHidden() {
        $model = new Opus_TitleAbstract();

        $form = new Admin_Form_Model($model);

        $this->assertTrue($form->isFieldHidden('Type'));
    }

    public function testGetVisibleFieldsForTitleAbstract() {
        $model = new Opus_TitleAbstract();

        $form = new Admin_Form_Model($model);

        $fields = $form->getVisibleFields($model);

        $this->assertNotEmpty($fields);
        $this->assertEquals(2, count($fields));
        $this->assertContains('Value', $fields);
        $this->assertContains('Language', $fields);
    }

    public function testGetVisibleFieldsForOpusIdentifier() {
        $model = new Opus_Identifier();

        $form = new Admin_Form_Model($model);

        $fields = $form->getVisibleFields($model);

        $this->assertNotEmpty($fields);
        $this->assertEquals(2, count($fields));
        $this->assertContains('Type', $fields);
        $this->assertContains('Value', $fields);

        // Verify order
        $this->assertEquals('Type', $fields[0], 'Field Type should be first.');
        $this->assertEquals('Value', $fields[1], 'Field Value should be second.');
    }

    public function testGetVisibleFieldsForOpusPerson() {
        $model = new Opus_Person();

        $form = new Admin_Form_Model($model);

        $fields = $form->getVisibleFields($model);

        $this->assertNotEmpty($fields);
        $this->assertEquals(6, count($fields));
    }

    public function testGetVisibleFieldsForDocumentPersonField() {
        $doc = new Opus_Document();

        $field = $doc->getField('Person');

        $form = new Admin_Form_Model($field);

        $fields = $form->getVisibleFields($field);

        $this->assertNotEmpty($fields);
        $this->assertEquals(9, count($fields));
        $this->assertEquals('Role', $fields[0]);
        $this->assertEquals('AcademicTitle', $fields[1]);
        $this->assertEquals('FirstName', $fields[2]);
        $this->assertEquals('LastName', $fields[3]);
        $this->assertEquals('Email', $fields[4]);
        $this->assertEquals('AllowEmailContact', $fields[5]);
        $this->assertEquals('PlaceOfBirth', $fields[6]);
        $this->assertEquals('DateOfBirth', $fields[7]);
        $this->assertEquals('SortOrder', $fields[8]);
    }

}
?>
