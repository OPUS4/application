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

class Admin_Model_FormElementFactoryTest extends ControllerTestCase {

    private $factory;

    public function setUp() {
        parent::setUp();

        $this->__factory = new Admin_Model_FormElementFactory();
    }

    public function testConstructor() {
        $factory = new Admin_Model_FormElementFactory();
    }

    public function testCreateElementForCheckboxField() {
        $doc = new Opus_Document();

        $field = $doc->getField('BelongsToBibliography');

        $element = $this->__factory->getElementForField($doc, $field);

        $this->assertNotNull($element);
        $this->assertTrue($element instanceOf Zend_Form_Element_Checkbox);
        $this->assertEquals('BelongsToBibliography', $element->getName());
    }

    public function testCreateElementForTextField() {
        $model = new Opus_Document();

        $field = $model->getField('PageFirst');

        $element = $this->__factory->getElementForField($model, $field);

        $this->assertNotNull($element);
        $this->assertTrue($element instanceOf Zend_Form_Element_Text);
        $this->assertEquals('PageFirst', $element->getName());
    }

    public function testCreateElementForTextareaField() {
        $model = new Opus_TitleAbstract();

        $field = $model->getField('Value');

        $element = $this->__factory->getElementForField($model, $field);

        $this->assertNotNull($element);
        $this->assertTrue($element instanceOf Zend_Form_Element_TextArea);
        $this->assertEquals('Value', $element->getName());
    }

    public function testCreateElementForSelectField() {
        $model = new Opus_Document();

        $field = $model->getField('PublicationState');

        $element = $this->__factory->getElementForField($model, $field);

        $this->assertNotNull($element);
        $this->assertTrue($element instanceOf Zend_Form_Element_Select);
        $this->assertEquals('PublicationState', $element->getName());

        $defaults = $field->getDefault();
        $options = $element->getMultiOptions();

        $this->assertEquals(count($defaults), count($options));
    }

}

