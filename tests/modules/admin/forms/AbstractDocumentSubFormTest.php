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
 **/

/**
 * Unit Tests fuer abstrakte Parent-Klasse fuer Metadaten Unterformulare.
 */
class Admin_Form_AbstractDocumentSubFormTest extends ControllerTestCase {
    
    public function getForm() {
        return $this->getMockForAbstractClass('Admin_Form_AbstractDocumentSubForm');
    }
    
    public function getFormElementFactory() {
        $form = $this->getForm();
        
        $this->assertNotNull($form->getFormElementFactory());
        $this->assertInstanceOf('Admin_Model_FormElementFactory', $form->getFormElementFactory());
    }
    
    public function testGetDatesHelper() {
        $form = $this->getForm();
        
        $this->assertNotNull($form->getDatesHelper());
        $this->assertInstanceOf('Controller_Helper_Dates', $form->getDatesHelper());
    }
    
    public function testSetLog() {
        $form = $this->getForm();
        
        $logger = new MockLogger();
        
        $form->setLog($logger);
        
        $this->assertEquals($logger, $form->getLog());
    }
    
    public function testGetLog() {
        $form = $this->getForm();
        
        $this->assertNotNull($form->getLog());
        $this->assertInstanceOf('Zend_Log', $form->getLog());
    }
    
    public function testIsEmptyTrue() {
        $form = $this->getForm();
        
        $this->assertTrue($form->isEmpty());
    }
    
    public function testIsEmptyFalse() {
        $form = $this->getForm();
        
        $form->addElement(new Zend_Form_Element_Text('text'));
        
        $this->assertFalse($form->isEmpty()); // Element
        
        $form->addSubForm(new Zend_Form_SubForm(), 'subform');
        
        $this->assertFalse($form->isEmpty()); // Element und Unterformular
        
        $form->clearElements();
        
        $this->assertFalse($form->isEmpty()); // Nur Unterformular
        
        $form->clearSubForms();
        
        $this->assertTrue($form->isEmpty()); // Bonusassert
    }
    
    public function testGetElementValue() {
        $form = $this->getForm();
        
        $elementText = new Zend_Form_Element_Text('text');
        $form->addElement($elementText);
        
        $elementText->setValue('Test Test');
        $this->assertEquals('Test Test', $form->getElementValue('text'));
        
        $elementText->setValue('  ');
        $this->assertNull($form->getElementValue('text'));
        
        $elementText->setValue('0');
        $this->assertEquals('0', $form->getElementValue('text'));
        
        $elementCheckbox = new Zend_Form_Element_Checkbox('checkbox');
        $form->addElement($elementCheckbox);
        
        $elementCheckbox->setChecked(true);
        $this->assertEquals('1', $form->getElementValue('checkbox'));
        
        $elementCheckbox->setChecked(false);
        $this->assertEquals('0', $form->getElementValue('checkbox'));
    }
    
    public function testGetElementValueUnknownElement() {
        $form = $this->getForm();
        
        $this->assertNull($form->getElementValue('unknownelement'));
    }
    
    public function testPrepareRenderingAsView() {
        $form = $this->getForm();
        
        // Elemente hinzufügen, ein leeres, ein nicht leeres
        $form->addElement(new Zend_Form_Element_Text('textempty'));
        $element = new Zend_Form_Element_Textarea('textareaempty');
        $element->setValue('     '); // leerer String
        $form->addElement($element);
        
        $element = new Zend_Form_Element_Text('textfull');
        $element->setValue('Mit Text');
        $form->addElement($element);
        
        $form->addElement(new Zend_Form_Element_Checkbox('checkboxfalse')); // wird entfernt
        $element = new Zend_Form_Element_Checkbox('checkboxtrue'); // wird nicht entfernt
        $element->setChecked(true);
        $form->addElement($element);
        
        $form->addElement(new Zend_Form_Element_Submit('save')); // wird entfernt
        $form->addElement(new Zend_Form_Element_Button('cancel')); // wird entfernt
        
        $element = new Zend_Form_Element_Select('select');
        $element->addMultiOption('option1');
        $element->setValue('option1');
        $form->addElement($element); // wird nicht entfernt
        
        // Unterformulare hinzufügen, ein leeres, ein nicht leeres
        $subform = $this->getForm(); // Leeres Unterformular
        $form->addSubForm($subform, 'subformempty');
            
        $subform2 = $this->getForm(); // Nicht leeres Unterformular    
        $element = new Zend_Form_Element_Text('subformtextfull');
        $element->setValue('Im SubForm mit Text');
        $subform2->addElement($element);
        
        $form->addSubForm($subform2, 'subformnotempty');
                
        $form->prepareRenderingAsView();
        
        $this->assertEquals(3, count($form->getElements())); // Leere Elemente wurden entfernt
        $this->assertArrayHasKey('textfull', $form->getElements());
        $this->assertArrayHasKey('checkboxtrue', $form->getElements());
        $this->assertArrayHasKey('select', $form->getElements());
        
        $this->assertEquals(1, count($form->getSubForms())); // Leeres Unterformular wurde entfernt
        $this->assertArrayHasKey('subformnotempty', $form->getSubForms());
        
        // Decorators ueberpruefen
        $decorators = $form->getElement('textfull')->getDecorators();
        
        $this->assertEquals(1, count($decorators));
        $this->assertArrayHasKey('Form_Decorator_StaticView', $decorators);

        $decorators = $form->getElement('checkboxtrue')->getDecorators();
        
        $this->assertEquals(1, count($decorators));
        $this->assertArrayHasKey('Form_Decorator_StaticViewCheckbox', $decorators);

        $decorators = $form->getElement('select')->getDecorators();
        
        $this->assertEquals(1, count($decorators));
        $this->assertArrayHasKey('Form_Decorator_StaticViewSelect', $decorators);
    }
    
}
