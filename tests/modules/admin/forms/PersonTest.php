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
 * @copyright   Copyright (c) 2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Unit Test fuer Formularklasse zum Editieren einer Person.
 */
class Admin_Form_PersonTest extends ControllerTestCase {
    
    public function testCreateForm() {
        $form = new Admin_Form_Person();
        
        $this->assertNotNull($form->getElement('PersonId'));
        $this->assertNotNull($form->getElement('AcademicTitle'));
        $this->assertNotNull($form->getElement('LastName'));
        $this->assertNotNull($form->getElement('FirstName'));
        $this->assertNotNull($form->getElement('Email'));
        $this->assertNotNull($form->getElement('PlaceOfBirth'));
        $this->assertNotNull($form->getElement('DateOfBirth'));
    }

    public function testPopulateFromModel() {
        $this->setUpEnglish();
        
        $form = new Admin_Form_Person();
        
        $person = new Opus_Person();
        
        $person->setFirstName('John');
        $person->setLastName('Doe');
        $person->setAcademicTitle('PhD');
        $person->setPlaceOfBirth('Berlin');
        
        $datesHelper =$form->getDatesHelper();
        
        $person->setDateOfBirth($datesHelper->getOpusDate('1990/01/01'));
        $person->setEmail('john@example.org');
        
        $person->store();
        
        $form->populateFromModel($person);
        
        $personId = $person->getId();
        $person->delete();
        
        $this->assertEquals($personId, $form->getElement('PersonId')->getValue());
        $this->assertEquals($person->getLastName(), $form->getElement('LastName')->getValue());
        $this->assertEquals($person->getFirstName(), $form->getElement('FirstName')->getValue());
        $this->assertEquals($person->getAcademicTitle(), $form->getElement('AcademicTitle')->getValue());
        $this->assertEquals($person->getEmail(), $form->getElement('Email')->getValue());
        $this->assertEquals($person->getPlaceOfBirth(), $form->getElement('PlaceOfBirth')->getValue());
        $this->assertEquals('1990/01/01', $form->getElement('DateOfBirth')->getValue());
    }
    
    public function testUpdateModel() {
        $this->setUpEnglish();
        
        $form = new Admin_Form_Person();
        
        $form->getElement('AcademicTitle')->setValue('Prof. Dr.');
        $form->getElement('FirstName')->setValue('Jennifer');
        $form->getElement('LastName')->setValue('Block');
        $form->getElement('Email')->setValue('jenny@example.org');
        $form->getElement('PlaceOfBirth')->setValue('London');
        $form->getElement('DateOfBirth')->setValue('1990/02/01');
        
        $person = new Opus_Person();
                
        $form->updateModel($person);
        
        $this->assertEquals('Prof. Dr.', $person->getAcademicTitle());
        $this->assertEquals('Jennifer', $person->getFirstName());
        $this->assertEquals('Block', $person->getLastName());
        $this->assertEquals('jenny@example.org', $person->getEmail());
        $this->assertEquals('London', $person->getPlaceOfBirth());
        
        $datesHelper = $form->getDatesHelper();
        
        $this->assertEquals('1990/02/01', $datesHelper->getDateString($person->getDateOfBirth()));
    }
    
    public function testUpdateModelBadModel() {
        $form = new Admin_Form_Person();
        
        $logger = new MockLogger();
        
        $form->setLog($logger);
        
        $form->updateModel(new Opus_Document());
        
        $messages = $logger->getMessages();
        
        $this->assertEquals(1, count($messages));
        $this->assertContains('not instance of Opus_Person', $messages[0]);
    }
    
    public function testGetModel() {
        $this->setUpEnglish();
        
        $form = new Admin_Form_Person();
        
        $document = new Opus_Document(146);
        $persons = $document->getPerson();
        $person = $persons[0]->getModel();

        $form->getElement('PersonId')->setValue($person->getId());
        $form->getElement('AcademicTitle')->setValue('Prof. Dr.');
        $form->getElement('FirstName')->setValue('Jennifer');
        $form->getElement('LastName')->setValue('Block');
        $form->getElement('Email')->setValue('jenny@example.org');
        $form->getElement('PlaceOfBirth')->setValue('London');
        $form->getElement('DateOfBirth')->setValue('1990/02/01');
        
        $model = $form->getModel();
     
        $this->assertEquals($person->getId(), $model->getId());
        $this->assertEquals('Prof. Dr.', $model->getAcademicTitle());
        $this->assertEquals('Jennifer', $model->getFirstName());
        $this->assertEquals('Block', $model->getLastName());
        $this->assertEquals('jenny@example.org', $model->getEmail());
        $this->assertEquals('London', $model->getPlaceOfBirth());
        
        $datesHelper = $form->getDatesHelper();
        
        $this->assertEquals('1990/02/01', $datesHelper->getDateString($model->getDateOfBirth()));
    }
    
    public function testGetModelNew() {
        $this->setUpEnglish();
        
        $form = new Admin_Form_Person();
        
        $form->getElement('AcademicTitle')->setValue('Prof. Dr.');
        $form->getElement('FirstName')->setValue('Jennifer');
        $form->getElement('LastName')->setValue('Block');
        $form->getElement('Email')->setValue('jenny@example.org');
        $form->getElement('PlaceOfBirth')->setValue('London');
        $form->getElement('DateOfBirth')->setValue('1990/02/01');
        
        $person = $form->getModel();
                
        $this->assertNull($person->getId());
        $this->assertEquals('Prof. Dr.', $person->getAcademicTitle());
        $this->assertEquals('Jennifer', $person->getFirstName());
        $this->assertEquals('Block', $person->getLastName());
        $this->assertEquals('jenny@example.org', $person->getEmail());
        $this->assertEquals('London', $person->getPlaceOfBirth());
        
        $datesHelper = $form->getDatesHelper();
        
        $this->assertEquals('1990/02/01', $datesHelper->getDateString($person->getDateOfBirth()));
    }
    
    public function testValidation() {
        $this->setUpEnglish();
            
        $form = new Admin_Form_Person();
    
        $post = array(
            'LastName' => '', // Pflichtfeld
            'DateOfBirth' => 'Sonntag' // 
        );
        
        $this->assertFalse($form->isValid($post));
        $this->assertContains('isEmpty', $form->getErrors('LastName'));
        $this->assertContains('dateFalseFormat', $form->getErrors('DateOfBirth'));

        $post = array(
            'LastName' => 'Doe', // Pflichtfeld
            'DateOfBirth' => '1990/02/01'
        );
        
        $this->assertTrue($form->isValid($post));
        
    }
    
    public function testValidationGerman() {
        $this->setUpGerman();
        
        $form = new Admin_Form_Person();
        
        $post = array(
            'LastName' => 'Doe', // Pflichtfeld
            'DateOfBirth' => '01.02.1990'
        );
        
        $this->assertTrue($form->isValid($post));
    }
    
    public function testProcessPostSave() {
        $form = new Admin_Form_Person();
        
        $post = array(
            'Save' => 'Speichern'
        );
        
        $this->assertEquals('save', $form->processPost($post, null));
    }
    
    public function testProcessPostCancel() {
        $form = new Admin_Form_Person();
        
        $post = array(
            'Cancel' => 'Abbrechen'
        );
        
        $this->assertEquals('cancel', $form->processPost($post, null));
    }
    
    public function testProcessPostEmpty() {
        $form = new Admin_Form_Person();
        
        $this->assertNull($form->processPost(array(), null));
    }
    
}
