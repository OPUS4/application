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
 * @package     Tests
 * @author      Ralf Claußnitzer <ralf.claussnitzer@slub-dresden.de>
 * @author      Henning Gerhardt <henning.gerhardt@slub-dresden.de>
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Test cases for class Opus_Form_Builder.
 *
 * @category Applicaton
 * @package  Tests
 *
 * @group    FormBuilderTest
 */
class LibraryTests_Form_BuilderTest extends PHPUnit_Framework_TestCase {

    /**
     * Test fixture holding an instance of Opus_Form_BuilderTest_Model.
     *
     * @var Opus_Model_Abstract
     */
    protected $_model = null;

    /**
     * Test fixture holding an instance of the Opus_Form_BuilderTest_DbModel table gateway.
     *
     * @var Zend_Db_Table_Interface
     */
    protected $_table = null;

    /**
     * Instance of the class under test.
     *
     * @var Opus_Form_Builder
     */
    protected $_builder = null;

    /**
     * Set up test fixtures and tables.
     *
     * @return void
     */
    public function setUp() {
        $dba = Zend_Db_Table::getDefaultAdapter();
        if ($dba->isExistent('dbmodel') === true) {
            $dba->deleteTable('dbmodel');
        }
        $dba->createTable('dbmodel');
        $dba->addField('dbmodel', array('name' => 'simple_field', 'type' => 'varchar', 'length' => 50));

        $this->_model = new LibraryTests_Form_BuilderTest_Model(null, new LibraryTests_Form_BuilderTest_DbModel);
        $this->_builder = new Opus_Form_Builder();

        // Set up a mock language list.
        $list = array('de' => 'Test_Deutsch', 'en' => 'Test_Englisch');
        Zend_Registry::set('Available_Languages', $list);

        $testTranslation = array('test1Descr' => 'test 1');
        $translate = new Zend_Translate(
            Zend_Translate::AN_ARRAY,
            $testTranslation,
            'en'
        );
        Zend_Registry::set('Zend_Translate', $translate);
    }

    /**
     * Test of creating a Zend Form.
     *
     * @return void
     */
    public function testCreateFormFromDocument() {
        $form = $this->_builder->build($this->_model);
        $this->assertType('Zend_Form', $form);
        $elements = $form->getElements();
        $this->assertArrayHasKey('SimpleField', $elements, 'Field "SimpleField" is missing in form.');
    }

    /**
     * Test if the serialized model is correctly stored within the form.
     *
     * @return void
     */
    public function testModelIsSerializedCorrectly() {
        $form = $this->_builder->build($this->_model);
        $serializedModel = $this->_builder->compressModel($this->_model);
        $serializedModelFromForm = $form->getElement(Opus_Form_Builder::HIDDEN_MODEL_ELEMENT_NAME)->getValue();
        $this->assertEquals($serializedModel, $serializedModelFromForm, 'Model serialization has failures.');
    }

    /**
     * Test if the value of a field is set in the generated form.
     *
     * @return void
     */
    public function testFieldValueIsSetInForm() {
        $this->_model->setSimpleField('Testvalue!');
        $form = $this->_builder->build($this->_model);
        $value = $form->getElement('SimpleField')->getValue();
        $this->assertEquals('Testvalue!', $value, 'Field value has not been set correctly.');
    }

    /**
     * Test if an external field is mapped to a sub form.
     *
     * @return void
     */
    public function testReferenceModelMappedToSubForm() {
        $form = $this->_builder->build($this->_model);
        $subForms = $form->getSubForms();
        $this->assertArrayHasKey('ReferenceField', $subForms, 'Sub form for field "ReferenceField" is missing in form.');
    }

    /**
     * Test if a generated sub form contains the expected field from
     * the external field's referenced type.
     *
     * @return void
     */
    public function testReferenceModelSubFormHasCorrectField() {
        $form = $this->_builder->build($this->_model);
        $subForm = $form->getSubForm('ReferenceField');
        $element = $subForm->getElement('Field1');
        $this->assertNotNull($element, '"Field1" is missing in sub form.');
    }

    /**
     * Test if a field has a validator
     *
     * @return void
     */
    public function testFieldHasAValidator() {
        $field = $this->_model->getField('SimpleField');

        $field->setValidator(new Zend_Validate_Alnum());
        $form = $this->_builder->build($this->_model);
        $value = $form->getElement('SimpleField')->getValidator('Zend_Validate_Alnum');
        $this->assertType('Zend_Validate_Alnum', $value, 'Field does not have correct validator.');
    }

    /**
     * Test, if a field could have more than one validator (validator chain!)
     *
     * @return void
     */
    public function testFieldHasCorrectValidators() {
        $field = $this->_model->getField('SimpleField');

        $val1 = new Zend_Validate_Alnum();
        $val2 = new Zend_Validate_Date();

        $chain = new Zend_Validate();
        $chain->addValidator($val1)->addValidator($val2);

        $field->setValidator($chain);
        $form = $this->_builder->build($this->_model);
        $value = $form->getElement('SimpleField')->getValidator('Zend_Validate');
        $this->assertEquals($chain, $value, 'Field does not have correct validators.');
    }

    /**
     * Recreation of a form should always create the same form.
     *
     * @return void
     */
    public function testRecreateFormFromPostDataRendersSameForm() {

        $this->_model->setMultiField(array(1,2,3));
        $this->_model->addMultiModel()->setField1('foo');
        $this->_model->addMultiModel()->setField1('bar');

        $form = $this->_builder->build($this->_model);
        $modelname = Opus_Form_Builder::HIDDEN_MODEL_ELEMENT_NAME;

        $post = array(
            'SimpleField' => $form->SimpleField->getValue(),
            'MultiField' => array(1,2,3),
            'MultiModel' => array(
                '0' => array( 'Field1' => 'foo' ),
                '1' => array( 'Field1' => 'bar')),
            'ReferenceField' => array(
                'Field1' => $form->ReferenceField->Field1->getValue(),
            ),
            $modelname => $form->$modelname->getValue(),
        );
        $new_form = $this->_builder->buildFromPost($post);

        $view = new Zend_View();
        $form->setView($view);
        $new_form->setView($view);

        $str_form = @$form->__toString();
        $str_new_form = @$new_form->__toString();

        $this->assertEquals($str_form, $str_new_form, 'Recreated form should match the original form.');
    }

    /**
     * Test if a multivalue field gets mapped to a sub form.
     *
     * @return void
     */
    public function testMultivaluedFieldsAreMappedToSubform() {
        $form = $this->_builder->build($this->_model);
        $subForm = $form->getSubForm('MultiField');
        $this->assertNotNull($subForm, 'Sub form for "MultiField" is missing.');
    }

    /**
     * Test if an initialized multi value field gets mapped to the correct number
     * of form elements with one element maping to one value respectivly.
     *
     * @return void
     */
    public function testMultivaluedFieldSubformHasRightCountOfFieldElements() {
        $this->_model->setMultiField(array('hana', 'dul', 'set'));

        $form = $this->_builder->build($this->_model);
        $subForm = $form->getSubForm('MultiField');

        /*
         * Multifield elements are:
         * - 3 remove buttons
         * - 1 add button
         * - 3 subforms
         * every subform should contain
         * - 1 element
         * - 0 subforms
         * summary:
         * 10 elements total
         */
        $total = count($subForm->getElements());
        $total += count($subForm->getSubForms());
        $total += count($subForm->getSubForm('1')->getElements());
        $total += count($subForm->getSubForm('2')->getElements());
        $total += count($subForm->getSubForm('3')->getElements());

        $this->assertEquals(10, $total, 'Wrong number of elements generated.');
    }

    /**
     * Test if an initialized multi value field gets mapped to the correct number
     * of form elements and all form elements have their correct value assigned.
     *
     * @return void
     */
    public function testMultivaluedFieldSubformFieldElementsInitializedCorrectly() {
        $expected = array('hana', 'dul', 'set');
        $this->_model->setMultiField($expected);

        $form = $this->_builder->build($this->_model);
        $subForm = $form->getSubForm('MultiField');
        $elements = $subForm->getElements();

        $result[] = $subForm->getSubForm('1')->getElement('MultiField')->getValue();
        $result[] = $subForm->getSubForm('2')->getElement('MultiField')->getValue();
        $result[] = $subForm->getSubForm('3')->getElement('MultiField')->getValue();
        $this->assertEquals($expected, $result, 'Multifield does not contain correct values.');
    }

    /**
     * Test if more post data is skipped which are not values for Model_Document
     *
     * @return void
     */
    public function testSkippingOfPostData() {
        $form = $this->_builder->build($this->_model);
        $modelname = Opus_Form_Builder::HIDDEN_MODEL_ELEMENT_NAME;

        $post = array(
            'AttackerCode' => 'Bad Value',
            'SimpleField' => $form->SimpleField->getValue(),
            'ReferenceField' => array(
                'Field1' => $form->ReferenceField->Field1->getValue(),
            ),
            $modelname => $form->$modelname->getValue(),
            'MoreBadCode' => array(
                'try_to' => 'exploid this form',
            ),
        );
        $new_form = $this->_builder->buildFromPost($post);

        $this->assertEquals($form, $new_form, 'Post data should be skipped if not values of Model_Document.');
    }

    /**
     * Test if adding of a field works. Addtional field should not contain any value.
     *
     * @return void
     */
    public function testAddingAdditionalFieldToForm() {
        $start_values = array('hana', 'dul', 'set');
        $this->_model->setMultiField($start_values);
        $form = $this->_builder->build($this->_model);
        $modelname = Opus_Form_Builder::HIDDEN_MODEL_ELEMENT_NAME;

        $post = array(
            'SimpleField' => $form->SimpleField->getValue(),
            $modelname => $form->$modelname->getValue(),
            'ReferenceField' => array(
                'Field1' => $form->ReferenceField->Field1->getValue(),
            ),
            'MultiField' => array(
                'hana',
                'dul',
                'set',
                'add_MultiField' => '+',
            ),
        );

        $new_form = $this->_builder->buildFromPost($post);
        $subForm = $new_form->getSubForm('MultiField');

        /*
         * Form with a added field should contain
         * - 4 remove buttons
         * - 1 add button
         * - 4 sub forms
         * every sub form should contain
         * - 1 element
         * summary:
         * 13 elements
         */
        $total = count($subForm->getElements());
        $total += count($subForm->getSubForms());
        $total += count($subForm->getSubForm('1')->getElements());
        $total += count($subForm->getSubForm('2')->getElements());
        $total += count($subForm->getSubForm('3')->getElements());
        $total += count($subForm->getSubForm('4')->getElements());
        $this->assertEquals(13, $total, 'Multifield should contain 13 elements.');

        $expected = $start_values;
        $result[] = $subForm->getSubForm('1')->getElement('MultiField')->getValue();
        $result[] = $subForm->getSubForm('2')->getElement('MultiField')->getValue();
        $result[] = $subForm->getSubForm('3')->getElement('MultiField')->getValue();
        $result[] = $subForm->getSubForm('4')->getElement('MultiField')->getValue();
        // new value should be empty
        $expected[] = '';
        $this->assertEquals($expected, $result, 'Multifield does not contain correct values');
    }

    /**
     * Test if removing of a field works.
     *
     * @return void
     */
    public function testRemovingAFieldFromForm() {
        $start_values = array('hana', 'dul', 'set');
        $this->_model->setMultiField($start_values);
        $form = $this->_builder->build($this->_model);
        $modelname = Opus_Form_Builder::HIDDEN_MODEL_ELEMENT_NAME;

        $post = array(
            'SimpleField' => $form->SimpleField->getValue(),
            $modelname => $form->$modelname->getValue(),
            'ReferenceField' => array(
                'Field1' => $form->ReferenceField->Field1->getValue(),
            ),
            'MultiField' => array(
                'hana',
                'dul',
                'set',
                'remove_MultiField_0' => '-',
            ),
        );

        $new_form = $this->_builder->buildFromPost($post);
        $subForm = $new_form->getSubForm('MultiField');

        /*
         * Form with a removed field should contain
         * - 2 remove buttons
         * - 1 add button
         * - 2 sub forms
         * every sub form should contain
         * - 1 element
         * summary:
         * 7 elements
         */
        $total = count($subForm->getElements());
        $total += count($subForm->getSubForms());
        $total += count($subForm->getSubForm('1')->getElements());
        $total += count($subForm->getSubForm('2')->getElements());
        $this->assertEquals(7, $total, 'Multifield should contain 7 elements.');

        $expected = $start_values;
        $result[] = $subForm->getSubForm('1')->getElement('MultiField')->getValue();
        $result[] = $subForm->getSubForm('2')->getElement('MultiField')->getValue();
        array_shift($expected);
        $this->assertEquals($expected, $result, 'Multifield does not contain correct values');

    }

    /**
     * Test if a corret model is returned from a given form.
     *
     * @return void
     */
    public function testGettingCorrectModelFromForm() {
        $form = $this->_builder->build($this->_model);
        $form_model = $this->_builder->getModelFromForm($form);
        $this->assertEquals($this->_model, $form_model, 'Returned model should be the same as original model.');
    }

    /**
     * Test if a form without a hidden model field return null.
     *
     * @return void
     */
    public function testGettingNullIfNoModelIsInForm() {
        $form = $this->_builder->build($this->_model);
        $modelelementname = Opus_Form_Builder::HIDDEN_MODEL_ELEMENT_NAME;
        unset($form->$modelelementname);
        $form_model = $this->_builder->getModelFromForm($form);
        $this->assertNull($form_model, 'A form without a hidden model field should not contain model informations.');
    }

    /**
     * Test that it not should be possible to emptying multi field values
     *
     * @return void
     */
    public function testDoNotEmptyingMultiFields() {
        $expected = array('hana');
        $this->_model->setMultiField($expected);
        $form = $this->_builder->build($this->_model);
        $modelname = Opus_Form_Builder::HIDDEN_MODEL_ELEMENT_NAME;

        $post = array(
            'SimpleField' => $form->SimpleField->getValue(),
            $modelname => $form->$modelname->getValue(),
            'ReferenceField' => array(
                'Field1' => $form->ReferenceField->Field1->getValue(),
            ),
            'MultiField' => array(
                'hana',
                'remove_MultiField_0' => '-',
            ),
        );

        $new_form = $this->_builder->buildFromPost($post);
        $subForm = $new_form->getSubForm('MultiField');

        /*
         * Form with a non-removed field should contain
         * - 0 remove buttons
         * - 1 add button
         * - 1 sub forms
         * every sub form should contain
         * - 1 element
         * summary:
         * 3 elements
         */
        $total = count($subForm->getElements());
        $total += count($subForm->getSubForms());
        $total += count($subForm->getSubForm('1')->getElements());
        $this->assertEquals(3, $total, 'Multifield should contain 3 elements.');

        $result[] = $subForm->getSubForm('1')->getElement('MultiField')->getValue();
        $this->assertEquals($expected, $result, 'Multifield does not contain correct values');
    }

    /**
     * Test that a wrong remove index does not work.
     *
     * @return void
     */
    public function testDoNotRemovingElementWithNonWrongIndex() {
        $expected = array('hana', 'dul');
        $this->_model->setMultiField($expected);
        $form = $this->_builder->build($this->_model);
        $modelname = Opus_Form_Builder::HIDDEN_MODEL_ELEMENT_NAME;

        $post = array(
            'SimpleField' => $form->SimpleField->getValue(),
            $modelname => $form->$modelname->getValue(),
            'ReferenceField' => array(
                'Field1' => $form->ReferenceField->Field1->getValue(),
            ),
            'MultiField' => array(
                'hana',
                'dul',
                'remove_MultiField_5' => '-',
            ),
        );

        $new_form = $this->_builder->buildFromPost($post);
        $subForm = $new_form->getSubForm('MultiField');

        /*
         * Form with a non-removed field should contain
         * - 2 remove buttons
         * - 1 add button
         * - 2 sub forms
         * every sub form should contain
         * - 1 element
         * summary:
         * 7 elements
         */
        $total = count($subForm->getElements());
        $total += count($subForm->getSubForms());
        $total += count($subForm->getSubForm('1')->getElements());
        $total += count($subForm->getSubForm('2')->getElements());
        $this->assertEquals(7, $total, 'Multifield should contain 7 elements.');

        $result[] = $subForm->getSubForm('1')->getElement('MultiField')->getValue();
        $result[] = $subForm->getSubForm('2')->getElement('MultiField')->getValue();
        $this->assertEquals($expected, $result, 'Multifield does not contain correct values');
    }

    /**
     * Test if adding of a given filter works.
     *
     * @return void
     */
    public function testSetFilterForElement() {
        $field = $this->_model->getField('SimpleField');

        $field->setFilter(new Zend_Filter_StringTrim());
        $form = $this->_builder->build($this->_model);
        $value = $form->getElement('SimpleField')->getFilter('Zend_Filter_StringTrim');
        $this->assertType('Zend_Filter_StringTrim', $value, 'Field does not have correct filter.');

    }

    /**
     * Test if a selection is build properly.
     *
     * @return void
     */
    public function testBuildingOfSelectionElement() {
        $values = array('hana', 'dul');
        $selection = new Opus_Model_Field('SelectionField');
        $selection->setSelection(true);
        $selection->setDefault($values);
        $this->_model->addField($selection);
        $form = $this->_builder->build($this->_model);
        $element = $form->getElement('SelectionField');

        $this->assertEquals('Zend_Form_Element_Select', $element->getType(), 'Builded element is not a selection.');
        $this->assertEquals($values, $element->getMultiOptions(), 'Selection does not contain correct values.');
    }

    /**
     * Test if a text area is build properly.
     *
     * @return void
     */
    public function testBuildingOfTextAreaElement() {
        $value = 'Hello World';
        $textarea = new Opus_Model_Field('TextAreaField');
        $textarea->setTextarea(true);
        $textarea->setValue($value);
        $this->_model->addField($textarea);
        $form = $this->_builder->build($this->_model);
        $element = $form->getElement('TextAreaField');

        $this->assertEquals('Zend_Form_Element_Textarea', $element->getType(), 'Builded element is not a text area.');
        $this->assertEquals($value, $element->getValue(), 'TextArea does not contain correct value.');
    }

    /**
     * Test if a selection element contains proper descriptions and model ids
     * when building from linked Opus_Model classes.
     *
     * @return void
     */
    public function testBuildingSelectionFromLinkedModels() {
        // Set up test licences in the database.
        // It's crucial to call store() to provide an id to each licence model.
        $lica = new Opus_Licence();
        $lica->setNameLong('Long Licence 1');
        $lica->setLinkLicence('http://long.org/licence/1');
        $lica->store();
        $licb = new Opus_Licence();
        $licb->setNameLong('Short Licence 2');
        $licb->setLinkLicence('http://short.org/licence/2');
        $licb->store();

        // Create a selection field holding licences.
        $field = new Opus_Model_Field('Licence');
        $field->setValueModelClass('Opus_Licence');
        $field->setSelection(true);
        $field->setDefault(array($lica, $licb));

        // At the created field to the test fixture model an build a form from it
        $this->_model->addField($field);
        $form = $this->_builder->build($this->_model);

        // Test the created form element.
        $element = $form->getElement('Licence');

        $this->assertEquals('Zend_Form_Element_Select', $element->getType(), 'Builded element is not a selection.');

        $values = array(
            $lica->getDisplayName(),
            $licb->getDisplayName());

        $this->assertEquals($values, $element->getMultiOptions(), 'Selection does not contain correct values.');
    }

    /**
     * Test if a checkbox is build properly.
     *
     * @return void
     */
    public function testBuildingOfCheckboxElement() {
        $value = 1;
        $checkbox = new Opus_Model_Field('CheckboxField');
        $checkbox->setCheckbox(true);
        $checkbox->setValue($value);
        $this->_model->addField($checkbox);
        $form = $this->_builder->build($this->_model);
        $element = $form->getElement('CheckboxField');

        $this->assertEquals('Zend_Form_Element_Checkbox', $element->getType(), 'Builded element is not a checkbox.');
        $this->assertEquals($value, $element->getValue(), 'Checkbox does not contain correct value.');
    }

    /**
     * Test if a translated description is added to a element.
     *
     * @return void
     */
    public function testDescriptionIsAddedToAElement() {
        // translation of description is set up in setUp()
        $testfield1 = new Opus_Model_Field('test1');
        $this->_model->addField($testfield1);
        $form = $this->_builder->build($this->_model);
        $element = $form->getElement('test1');
        $this->assertEquals('test 1' , $element->getDescription(), 'Description is not added to field.');
    }

    /**
     * Test if a description is not added if no translation is available.
     *
     * @return void
     */
    public function testDescriptionIsNotAddedToAElement() {
        // translation of description is set up in setUp()
        $testfield2 = new Opus_Model_Field('test2');
        $this->_model->addField($testfield2);
        $form = $this->_builder->build($this->_model);
        $element = $form->getElement('test2');
        $this->assertNull($element->getDescription(), 'Description of a field contains a unexpected value.');
    }
}
