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
 * @category    Application
 * @package     Module_Admin
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class Admin_Form_Person extends Admin_Form_AbstractDocumentSubForm {

    const ELEMENT_PERSON_ID = 'PersonId';
        
    const ELEMENT_ACADEMIC_TITLE = 'AcademicTitle';
    
    const ELEMENT_LAST_NAME = 'LastName';
    
    const ELEMENT_FIRST_NAME = 'FirstName';
    
    const ELEMENT_EMAIL = 'Email';
    
    const ELEMENT_PLACE_OF_BIRTH = 'PlaceOfBirth';
    
    const ELEMENT_DATE_OF_BIRTH = 'DateOfBirth';
        
    const ELEMENT_SAVE = 'Save';
    
    const ELEMENT_CANCEL = 'Cancel';
    
    const RESULT_SAVE = 'save';
    
    const RESULT_CANCEL = 'cancel';
    
    public function init() {
        parent::init();
        
        $elementFactory = new Admin_Model_FormElementFactory();
                
        // Person-ID
        $element = new Zend_Form_Element_Hidden(self::ELEMENT_PERSON_ID);
        $this->addElement($element);
                                
        $element = new Zend_Form_Element_Text(self::ELEMENT_ACADEMIC_TITLE);
        $element->setLabel('AcademicTitle');
        $this->addElement($element);
        
        $element = new Zend_Form_Element_Text(self::ELEMENT_LAST_NAME);
        $element->setLabel('LastName');
        $element->setRequired(true);
        $this->addElement($element);
        
        $element = new Zend_Form_Element_Text(self::ELEMENT_FIRST_NAME);
        $element->setLabel('FirstName');
        $this->addElement($element);
        
        $element = new Zend_Form_Element_Text(self::ELEMENT_EMAIL);
        $element->setLabel('Email');
        // TODO email validation
        $this->addElement($element);
                
        $element = new Zend_Form_Element_Text(self::ELEMENT_PLACE_OF_BIRTH);
        $element->setLabel('PlaceOfBirth');
        $this->addElement($element);
        
        $element = $elementFactory->createDateElement(self::ELEMENT_DATE_OF_BIRTH);
        $this->addElement($element);
        
        
        // Move to parent form SinglePerson
        $element = new Zend_Form_Element_Submit(self::ELEMENT_SAVE);
        $this->addElement($element);
        
        $element = new Zend_Form_Element_Submit(self::ELEMENT_CANCEL);
        $this->addElement($element);
    }
    
    /**
     * 
     * @param Opus_Model_Dependent_Link_DocumentPerson $model
     * 
     * TODO ELEMENT_ROLE
     */
    public function populateFromModel($personLink) {
        $this->populateFromPerson($personLink->getModel());
    }
    
    public function populateFromPerson($person) {
        $this->getElement(self::ELEMENT_PERSON_ID)->setValue($person->getId());
        $this->getElement(self::ELEMENT_ACADEMIC_TITLE)->setValue($person->getAcademicTitle());
        $this->getElement(self::ELEMENT_FIRST_NAME)->setValue($person->getFirstName());
        $this->getElement(self::ELEMENT_LAST_NAME)->setValue($person->getLastName());
        $this->getElement(self::ELEMENT_PLACE_OF_BIRTH)->setValue($person->getPlaceOfBirth());
        $date = $person->getDateOfBirth(); // TODO format date
        $this->getElement(self::ELEMENT_DATE_OF_BIRTH)->setValue($date);
        $this->getElement(self::ELEMENT_EMAIL)->setValue($person->getEmail());
    }
    
    public function populateFromPost($post) {
        $personId = $post[self::ELEMENT_PERSON_ID];
        $person = new Opus_Person($personId);
        $this->populateFromPerson($person);
    }
    
    public function processPost($post, $context) {
        if (array_key_exists(self::ELEMENT_SAVE, $post)) {
            return self::RESULT_SAVE;
        }
        else if (array_key_exists(self::ELEMENT_CANCEL, $post)) {
            return self::RESULT_CANCEL;
        }
    }

    public function updateModel($model) {
        if ($model instanceof Opus_Person) {
            $model->setAcademicTitle($this->getElementValue(self::ELEMENT_ACADEMIC_TITLE));
            $model->setLastName($this->getElementValue(self::ELEMENT_LAST_NAME));
            $model->setFirstName($this->getElementValue(self::ELEMENT_FIRST_NAME));
            $model->setEmail($this->getElementValue(self::ELEMENT_EMAIL));
            $model->setPlaceOfBirth($this->getElementValue(self::ELEMENT_PLACE_OF_BIRTH));
            // TODO DateOfBirth
        }
        else if ($model instanceof Opus_Model_Dependent_Link_DocumentPerson) {
            
        }
    }
    
    /**
     * Liefert Instanz von Opus_Person zurueck.
     * 
     * @return /Opus_Person
     */
    public function getModel() {
       $personId = $this->getElement(self::ELEMENT_PERSON_ID)->getValue();
       
       if (is_numeric($personId)) {
           $person = new Opus_Person($personId);
       }
       else {
           $person = new Opus_Person();
       }
       
       $this->updateModel($person);
       
       Zend_Debug::dump($person);
       
       return $person;
    }
    
}