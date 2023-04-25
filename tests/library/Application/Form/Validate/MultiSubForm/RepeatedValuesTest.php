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
class Application_Form_Validate_MultiSubForm_RepeatedValuesTest extends ControllerTestCase
{
    /** @var string */
    protected $additionalResources = 'translation';

    public function testConstruct()
    {
        $instance = new Application_Form_Validate_MultiSubForm_RepeatedValues('Language', 'testmessage');

        $this->assertEquals('Language', $instance->getElementName());
        $this->assertEquals('testmessage', $instance->getMessage());
        $this->assertNull($instance->getOtherElements());
    }

    public function testConstructWithOtherElement()
    {
        $instance = new Application_Form_Validate_MultiSubForm_RepeatedValues('Value', 'testmessage', 'Language');

        $this->assertEquals('Value', $instance->getElementName());
        $this->assertEquals('testmessage', $instance->getMessage());

        $elements = $instance->getOtherElements();

        $this->assertNotNull($elements);
        $this->assertInternalType('array', $elements);
        $this->assertEquals(1, count($elements));
        $this->assertEquals('Language', $elements[0]);
    }

    public function testConstructWithOtherElements()
    {
        $instance = new Application_Form_Validate_MultiSubForm_RepeatedValues(
            'Value',
            'testmessage',
            ['Language', 'Active']
        );

        $this->assertEquals('Value', $instance->getElementName());
        $this->assertEquals('testmessage', $instance->getMessage());

        $elements = $instance->getOtherElements();

        $this->assertNotNull($elements);
        $this->assertInternalType('array', $elements);
        $this->assertEquals(2, count($elements));
        $this->assertEquals('Language', $elements[0]);
        $this->assertEquals('Active', $elements[1]);
    }

    public function testConstructBadFirstArgument()
    {
        $this->expectException(Application_Exception::class);
        $this->expectExceptionMessage('#1 argument must not be null or empty.');
        $instance = new Application_Form_Validate_MultiSubForm_RepeatedValues(null, 'testmessage');
    }

    public function testConstructBadSecondArgument()
    {
        $this->expectException(Application_Exception::class);
        $this->expectExceptionMessage('#2 argument must not be null or empty.');
        $instance = new Application_Form_Validate_MultiSubForm_RepeatedValues('Language', null);
    }

    public function testImplementsInterface()
    {
        $instance = new Application_Form_Validate_MultiSubForm_RepeatedValues('Institute', 'message');

        $this->assertTrue($instance instanceof Application_Form_Validate_MultiSubFormInterface);
    }

    public function testIsValidReturnsTrue()
    {
        $instance = new Application_Form_Validate_MultiSubForm_RepeatedValues('Institute', 'message');

        $this->assertTrue($instance->isValid(null));
    }

    public function testGetValues()
    {
        $validator = new Application_Form_Validate_MultiSubForm_RepeatedValues('Language', 'message');

        $post = [
            'subform1' => [
                'Language' => 'deu',
            ],
            'subform2' => [
                'Language' => 'eng',
            ],
        ];

        $values = $validator->getValues('Language', $post);

        $this->assertEquals(2, count($values));
        $this->assertEquals(['deu', 'eng'], $values);
    }

    public function testGetValuesWithOtherElement()
    {
        $validator = new Application_Form_Validate_MultiSubForm_RepeatedValues('Value', 'message', 'Language');

        $post = [
            'subform1' => [
                'Language' => 'deu',
                'Value'    => 'Schlagwort 1',
            ],
            'subform2' => [
                'Language' => 'eng',
                'Value'    => 'Schlagwort 2',
            ],
        ];

        $values = $validator->getValues('Value', $post);

        $this->assertEquals(2, count($values));
        $this->assertEquals([
            ['deu', 'Schlagwort 1'],
            ['eng', 'Schlagwort 2'],
        ], $values);
    }

    public function testGetValuesWithOtherElements()
    {
        $validator = new Application_Form_Validate_MultiSubForm_RepeatedValues(
            'Value',
            'message',
            ['Language', 'Active']
        );

        $post = [
            'subform1' => [
                'Language' => 'deu',
                'Value'    => 'Schlagwort 1',
                'Active'   => '1',
            ],
            'subform2' => [
                'Language' => 'eng',
                'Value'    => 'Schlagwort 2',
                'Active'   => 0,
            ],
        ];

        $values = $validator->getValues('Value', $post);

        $this->assertEquals(2, count($values));
        $this->assertEquals([
            ['deu', '1', 'Schlagwort 1'],
            ['eng', '0', 'Schlagwort 2'],
        ], $values);
    }

    public function testPrepareValidation()
    {
        $validator = new Application_Form_Validate_MultiSubForm_RepeatedValues('Language', 'testmessage');

        $form = new Zend_Form();

        $subform = new Zend_Form_SubForm();
        $subform->addElement('text', 'Language');
        $form->addSubForm($subform, 'subform1');

        $subform = new Zend_Form_SubForm();
        $subform->addElement('text', 'Language');
        $form->addSubForm($subform, 'subform2');

        $post = [
            'subform1' => [
                'Language' => 'deu',
            ],
            'subform2' => [
                'Language' => 'eng',
            ],
        ];

        $validator->prepareValidation($form, $post, null);

        $position = 0;

        foreach ($form->getSubForms() as $subform) {
            $element = $subform->getElement('Language');
            $this->assertTrue($element->getValidator('Application_Form_Validate_DuplicateValue') !== false);
            $validator = $element->getValidator('Application_Form_Validate_DuplicateValue');
            $this->assertEquals(['deu', 'eng'], $validator->getValues());
            $this->assertEquals($position++, $validator->getPosition());
            $messageTemplates = $validator->getMessageTemplates();
            $this->assertEquals('testmessage', $messageTemplates['notValid']);
        }
    }

    public function testPrepareValidationWithOtherElements()
    {
        $validator = new Application_Form_Validate_MultiSubForm_RepeatedValues('Value', 'testmessage', 'Language');

        $form = new Zend_Form();

        $subform = new Zend_Form_SubForm();
        $subform->addElement('text', 'Value');
        $form->addSubForm($subform, 'subform1');

        $subform = new Zend_Form_SubForm();
        $subform->addElement('text', 'Value');
        $form->addSubForm($subform, 'subform2');

        $post = [
            'subform1' => [
                'Language' => 'deu',
                'Value'    => 'Schlagwort 1',
            ],
            'subform2' => [
                'Language' => 'eng',
                'Value'    => 'Schlagwort 2',
            ],
        ];

        $validator->prepareValidation($form, $post, null);

        $position = 0;

        foreach ($form->getSubForms() as $subform) {
            $element = $subform->getElement('Value');
            $this->assertTrue($element->getValidator('Application_Form_Validate_DuplicateMultiValue') !== false);
            $validator = $element->getValidator('Application_Form_Validate_DuplicateMultiValue');
            $this->assertEquals(
                [['deu', 'Schlagwort 1'], ['eng', 'Schlagwort 2']],
                $validator->getValues()
            );
            $this->assertEquals($position++, $validator->getPosition());
            $messageTemplates = $validator->getMessageTemplates();
            $this->assertEquals('testmessage', $messageTemplates['notValid']);
        }
    }
}
