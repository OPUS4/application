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

/**
 * Unit Tests fuer abstrakte Basisklasse für Formulare, die als View angezeigt werden können.
 */
class Application_Form_AbstractViewableTest extends TestCase
{
    /** @var Application_Form_AbstractViewable */
    private $form;

    public function setUp(): void
    {
        parent::setUp();

        $this->form = $this->getForm();
    }

    /**
     * @return Application_Form_AbstractViewable
     */
    private function getForm()
    {
        return $this->getMockForAbstractClass(Application_Form_AbstractViewable::class);
    }

    public function testPrepareRenderingAsView()
    {
        $form = $this->form;

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
        $element  = new Zend_Form_Element_Text('subformtextfull');
        $element->setValue('Im SubForm mit Text');
        $subform2->addElement($element);

        $form->addSubForm($subform2, 'subformnotempty');

        $form->prepareRenderingAsView();

        $this->assertTrue($form->isViewModeEnabled());

        $this->assertEquals(3, count($form->getElements())); // Leere Elemente wurden entfernt
        $this->assertArrayHasKey('textfull', $form->getElements());
        $this->assertArrayHasKey('checkboxtrue', $form->getElements());
        $this->assertArrayHasKey('select', $form->getElements());

        $this->assertEquals(1, count($form->getSubForms())); // Leeres Unterformular wurde entfernt
        $this->assertArrayHasKey('subformnotempty', $form->getSubForms());

        // Decorators ueberpruefen
        $decorators = $form->getElement('textfull')->getDecorators();

        $this->assertEquals(5, count($decorators));
        $this->assertArrayHasKey('Application_Form_Decorator_ViewHelper', $decorators);
        $this->assertTrue($form->getElement('textfull')->getDecorator('ViewHelper')->isViewOnlyEnabled());

        $decorators = $form->getElement('checkboxtrue')->getDecorators();

        $this->assertEquals(5, count($decorators));
        $this->assertArrayHasKey('Application_Form_Decorator_ViewHelper', $decorators);
        $this->assertTrue($form->getElement('checkboxtrue')->getDecorator('ViewHelper')->isViewOnlyEnabled());

        $decorators = $form->getElement('select')->getDecorators();

        $this->assertEquals(5, count($decorators));
        $this->assertArrayHasKey('Application_Form_Decorator_ViewHelper', $decorators);
        $this->assertTrue($form->getElement('select')->getDecorator('ViewHelper')->isViewOnlyEnabled());
    }

    public function testIsEmptyTrue()
    {
        $form = $this->getForm();

        $this->assertTrue($form->isEmpty());
    }

    public function testIsEmptyFalse()
    {
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

    public function testRemoveEmptyElementsYes()
    {
        $form = $this->getForm();

        $form->addElement('text', 'test');

        $this->assertNotNull($form->getElement('test'));

        $form->prepareRenderingAsView();

        $this->assertNull($form->getElement('test'));
    }

    public function testRemoveEmptyElementsNo()
    {
        $form = $this->getForm();

        $form->setRemoveEmptyElements(false);

        $form->addElement('text', 'test');

        $this->assertNotNull($form->getElement('test'));

        $form->prepareRenderingAsView();

        $this->assertNotNull($form->getElement('test'));
    }

    public function testIsRemoveEmptyElements()
    {
        $form = $this->getForm();

        $form->setRemoveEmptyElements(true);
        $this->assertTrue($form->isRemoveEmptyElements());

        $form->setRemoveEmptyElements(false);
        $this->assertFalse($form->isRemoveEmptyElements());
    }

    public function testRemoveEmptyCheckboxYes()
    {
        $form = $this->getForm();

        $form->addElement('checkbox', 'test');

        $this->assertNotNull($form->getElement('test'));

        $form->prepareRenderingAsView();

        $this->assertNull($form->getElement('test'));
    }

    public function testRemoveEmptyCheckboxNo()
    {
        $form = $this->getForm();

        $form->setRemoveEmptyCheckbox(false);

        $form->addElement('checkbox', 'test');

        $this->assertNotNull($form->getElement('test'));

        $form->prepareRenderingAsView();

        $this->assertNotNull($form->getElement('test'));
    }

    public function testIsRemoveEmptyCheckbox()
    {
        $form = $this->getForm();

        $form->setRemoveEmptyCheckbox(true);
        $this->assertTrue($form->isRemoveEmptyCheckbox());

        $form->setRemoveEmptyCheckbox(false);
        $this->assertFalse($form->isRemoveEmptyCheckbox());
    }
}
