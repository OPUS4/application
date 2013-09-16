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
 * @category    TODO
 * @package     TODO
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */


class Form_Validate_MultiSubForm_RepeatedValuesTest extends ControllerTestCase {

    public function testConstruct() {
        $instance = new Form_Validate_MultiSubForm_RepeatedValues('Language', 'testmessage');

        $this->assertEquals('Language', $instance->getElementName());
        $this->assertEquals('testmessage', $instance->getMessage());
    }

    /**
     * @expectedException Application_Exception
     * @expectedExceptionMessage #1 argument must not be null or empty.
     */
    public function testConstructBadFirstArgument() {
        $instance = new Form_Validate_MultiSubForm_RepeatedValues(null, 'testmessage');
    }

    /**
     * @expectedException Application_Exception
     * @expectedExceptionMessage #2 argument must not be null or empty.
     */
    public function testConstructBadSecondArgument() {
        $instance = new Form_Validate_MultiSubForm_RepeatedValues('Language', null);
    }

    public function testImplementsInterface() {
        $instance = new Form_Validate_MultiSubForm_RepeatedValues('Institute', 'message');

        $this->assertTrue($instance instanceof Form_Validate_IMultiSubForm);
    }

    public function testIsValidReturnsTrue() {
        $instance = new Form_Validate_MultiSubForm_RepeatedValues('Institute', 'message');

        $this->assertTrue($instance->isValid(null));
    }

    public function testGetValues() {
        $validator = new Form_Validate_MultiSubForm_RepeatedValues('Language', 'message');

        $post = array(
            'subform1' => array(
                'Language' => 'deu'
            ),
            'subform2' => array(
                'Language' => 'eng'
            )
        );

        $values = $validator->getValues('Language', $post);

        $this->assertEquals(2, count($values));
        $this->assertEquals(array('deu', 'eng'), $values);
    }

    public function testPrepareValidation() {
        $validator = new Form_Validate_MultiSubForm_RepeatedValues('Language', 'testmessage');

        $form = new Zend_Form();

        $subform = new Zend_Form_SubForm();
        $subform->addElement('text', 'Language');
        $form->addSubForm($subform, 'subform1');

        $subform = new Zend_Form_SubForm();
        $subform->addElement('text', 'Language');
        $form->addSubForm($subform, 'subform2');

        $post = array(
            'subform1' => array(
                'Language' => 'deu'
            ),
            'subform2' => array(
                'Language' => 'eng'
            )
        );

        $validator->prepareValidation($form, $post, null);

        $position = 0;

        foreach ($form->getSubForms() as $subform) {
            $element = $subform->getElement('Language');
            $this->assertTrue($element->getValidator('Form_Validate_DuplicateValue') !== false);
            $validator = $element->getValidator('Form_Validate_DuplicateValue');
            $this->assertEquals(array('deu', 'eng'), $validator->getValues());
            $this->assertEquals($position++, $validator->getPosition());
            $messageTemplates = $validator->getMessageTemplates();
            $this->assertEquals('testmessage', $messageTemplates['notValid']);
        }
    }



}