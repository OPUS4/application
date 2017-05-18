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
 * Unit Tests für Formular das Opus_Model_Dependent_Link_DocumentPerson Objekte anzeigt.
 */
class Admin_Form_PersonLinkTest extends ControllerTestCase {
    
    public function testConstructForm() {
        $form = new Admin_Form_PersonLink();
        
        $this->assertEquals(4, count($form->getElements()));
        
        $this->assertNotNull($form->getElement('PersonId'));
        $this->assertTrue($form->getElement('PersonId')->isRequired());
        $this->assertNotNull($form->getElement('Role'));
        $this->assertTrue($form->getElement('Role')->isRequired());
        $this->assertNotNull($form->getElement('SortOrder'));
        $this->assertNotNull($form->getElement('AllowContact'));
    }
    
    public function testPopulateFromModel() {
        $form = new Admin_Form_PersonLink();
        
        $model = new Opus_Model_Dependent_Link_DocumentPerson();
        
        $person = new Opus_Person(310); // von Testdokument 250 (Personensortierung)
        
        $model->setModel($person);
        $model->setSortOrder(5);
        $model->setAllowEmailContact(true);
        $model->setRole('advisor');
        
        $form->populateFromModel($model);
        
        $this->assertEquals(310, $form->getElementValue('PersonId'));
        $this->assertEquals(5, $form->getElementValue('SortOrder'));
        $this->assertEquals(1, $form->getElementValue('AllowContact'));
        $this->assertEquals('advisor', $form->getElementValue('Role'));
    }
    
    public function testPopulateFromModelBadModel() {
        $form = new Admin_Form_PersonLink();
        
        $logger = new MockLogger();
        
        $form->setLog($logger);
        
        $form->populateFromModel($this->createTestDocument());
        
        $messages = $logger->getMessages();
        
        $this->assertEquals(1, count($messages));
        $this->assertContains('not instance of', $messages[0]);
    }
    
    public function testGetModel() {
        $form = new Admin_Form_PersonLink();
        
        $this->assertNull($form->getModel());
        
        $document = new Opus_Document(250);
        $authors = $document->getPersonAuthor();
        
        $this->assertEquals(3, count($authors));
        $form->populateFromModel($authors[0]);
        
        $this->assertEquals($authors[0], $form->getModel());
    }
    
    public function testValidationFalseRequired() {
        $form = new Admin_Form_PersonLink();
        
        $post = array(
            'PersonId' => '', // Personen ID muss vorhanden sein
            'Role' => '' // Rolle muss vorhanden sein
        );
        
        $this->assertFalse($form->isValid($post));
        
        $this->assertEmpty($form->getErrors('SortOrder'));
        $this->assertEmpty($form->getErrors('AllowContact'));
        $this->assertContains('isEmpty', $form->getErrors('PersonId'));
        $this->assertContains('isEmpty', $form->getErrors('Role'));
    }
    
    public function testValidationFalseBadValues() {
        $form = new Admin_Form_PersonLink();
        
        $post = array(
            'PersonId' => 'tom', // keine ID
            'Role' => 'unknown' // das ist keine erlaubte Rolle
        );
        
        $this->assertFalse($form->isValid($post));
        
        $this->assertContains('notInt', $form->getErrors('PersonId'));
        $this->assertContains('notInArray', $form->getErrors('Role'));
    }
    
    /**
     * Es wird an dieser Stelle noch nicht geprüft, ob die Person wirklich existiert.
     */
    public function testValidationTrue() {
        $form = new Admin_Form_PersonLink();
        
        $post = array(
            'PersonId' => '310', // Personen ID muss vorhanden sein
            'Role' => 'author' // Rolle muss vorhanden sein
        );
        
        $this->assertTrue($form->isValid($post));
    }
    
    public function testUpdateModel() {
        $form = new Admin_Form_PersonLink();
        
        $form->getElement('Role')->setValue('referee');
        $form->getElement('SortOrder')->setValue(6);
        $form->getElement('AllowContact')->setChecked(true);
        
        $model = new Opus_Model_Dependent_Link_DocumentPerson();
        
        $form->updateModel($model);
        
        $this->assertEquals('referee', $model->getRole());
        $this->assertEquals(6, $model->getSortOrder());
        $this->assertEquals(1, $model->getAllowEmailContact());
    }
    
    public function testUpdateModelBadModel() {
        $form = new Admin_Form_PersonLink();

        $logger = new MockLogger();
        
        $form->setLog($logger);
        
        $form->updateModel($this->createTestDocument());
        
        $messages = $logger->getMessages();
        
        $this->assertEquals(1, count($messages));
        $this->assertContains('not instance of', $messages[0]);
    }
    
}
