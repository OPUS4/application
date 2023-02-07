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

use Opus\Common\Language;

class Admin_Form_LanguageTest extends ControllerTestCase
{
    /** @var string[] */
    protected $additionalResources = ['database'];

    public function testConstructForm()
    {
        $form = new Admin_Form_Language();

        $this->assertEquals(11, count($form->getElements()));

        $this->assertNotNull($form->getElement('Active'));
        $this->assertNotNull($form->getElement('Part2B'));
        $this->assertNotNull($form->getElement('Part2T'));
        $this->assertNotNull($form->getElement('Part1'));
        $this->assertNotNull($form->getElement('RefName'));
        $this->assertNotNull($form->getElement('Comment'));
        $this->assertNotNull($form->getElement('Scope'));
        $this->assertNotNull($form->getElement('Type'));

        $this->assertNotNull($form->getElement('Save'));
        $this->assertNotNull($form->getElement('Cancel'));
        $this->assertNotNull($form->getElement('Id'));
    }

    public function testPopulateFromModel()
    {
        $form = new Admin_Form_Language();

        $language = Language::new();
        $language->setActive(true);
        $language->setPart2B('ger');
        $language->setPart2T('deu');
        $language->setRefName('German');
        $language->setPart1('de');
        $language->setScope('M');
        $language->setType('H');
        $language->setComment('test comment');

        $form->populateFromModel($language);

        $this->assertEquals(1, $form->getElement('Active')->getValue());
        $this->assertEquals('ger', $form->getElement('Part2B')->getValue());
        $this->assertEquals('deu', $form->getElement('Part2T')->getValue());
        $this->assertEquals('de', $form->getElement('Part1')->getValue());
        $this->assertEquals('German', $form->getElement('RefName')->getValue());
        $this->assertEquals('test comment', $form->getElement('Comment')->getValue());
        $this->assertEquals('M', $form->getElement('Scope')->getValue());
        $this->assertEquals('H', $form->getElement('Type')->getValue());
        $this->assertNull($form->getElement('Id')->getValue());
    }

    public function testPopulateFromModelWithId()
    {
        $form = new Admin_Form_Language();

        $language = Language::get(2);

        $form->populateFromModel($language);

        $this->assertEquals(2, $form->getElement('Id')->getValue());
    }

    public function testUpdateModel()
    {
        $form = new Admin_Form_Language();

        $form->getElement('Id')->setValue(99);
        $form->getElement('Active')->setChecked(true);
        $form->getElement('RefName')->setValue('German');
        $form->getElement('Part2B')->setValue('ger');
        $form->getElement('Part2T')->setValue('deu');
        $form->getElement('Part1')->setValue('de');
        $form->getElement('Comment')->setValue('Deutsch');
        $form->getElement('Scope')->setValue('I');
        $form->getElement('Type')->setValue('L');

        $language = Language::new();

        $form->updateModel($language);

        $this->assertNull($language->getId());
        $this->assertEquals(1, $language->getActive());
        $this->assertEquals('German', $language->getRefName());
        $this->assertEquals('ger', $language->getPart2B());
        $this->assertEquals('deu', $language->getPart2T());
        $this->assertEquals('de', $language->getPart1());
        $this->assertEquals('Deutsch', $language->getComment());
        $this->assertEquals('I', $language->getScope());
        $this->assertEquals('L', $language->getType());
    }

    public function testValidationEmptyPost()
    {
        $form = new Admin_Form_Language();

        $this->assertFalse($form->isValid([]));

        $this->assertContains('isEmpty', $form->getErrors('RefName'));
        $this->assertContains('isEmpty', $form->getErrors('Part2T'));
    }

    public function testValidationEmptyFields()
    {
        $form = new Admin_Form_Language();

        $this->assertFalse($form->isValid([
            'RefName' => '   ',
            'Part2T'  => ' ',
        ]));

        $this->assertContains('isEmpty', $form->getErrors('RefName'));
        $this->assertContains('isEmpty', $form->getErrors('Part2T'));
    }

    public function testValidationInvalidValues()
    {
        $form = new Admin_Form_Language();

        $this->assertFalse($form->isValid([
            'RefName' => 'German',
            'Part2T'  => 'deu',
            'Scope'   => 'X',
            'Type'    => 'Y',
        ]));

        $this->assertNotContains('isEmpty', $form->getErrors('RefName'));
        $this->assertNotContains('isEmpty', $form->getErrors('Part2T'));
        $this->assertContains('notInArray', $form->getErrors('Scope'));
        $this->assertContains('notInArray', $form->getErrors('Type'));
    }

    public function testValidationTrue()
    {
        $form = new Admin_Form_Language();

        $this->assertTrue($form->isValid([
            'RefName' => 'German',
            'Part2T'  => 'deu',
        ]));
    }
}
