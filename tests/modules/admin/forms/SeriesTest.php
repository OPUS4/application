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

use Opus\Common\Series;

class Admin_Form_SeriesTest extends ControllerTestCase
{
    /** @var string[] */
    protected $additionalResources = ['database'];

    public function testConstructForm()
    {
        $form = new Admin_Form_Series();

        $this->assertEquals(7, count($form->getElements()));

        $this->assertNotNull($form->getElement('Title'));
        $this->assertNotNull($form->getElement('Infobox'));
        $this->assertNotNull($form->getElement('Visible'));
        $this->assertNotNull($form->getElement('SortOrder'));

        $this->assertNotNull($form->getElement('Save'));
        $this->assertNotNull($form->getElement('Cancel'));
        $this->assertNotNull($form->getElement('Id'));
    }

    public function testPopulateFromModel()
    {
        $form = new Admin_Form_Series();

        $series = Series::new();
        $series->setTitle('TestTitle');
        $series->setInfobox('TestInfo');
        $series->setVisible(1);
        $series->setSortOrder(20);

        $form->populateFromModel($series);

        $this->assertEquals('TestTitle', $form->getElement('Title')->getValue());
        $this->assertEquals('TestInfo', $form->getElement('Infobox')->getValue());
        $this->assertEquals(1, $form->getElement('Visible')->getValue());
        $this->assertEquals(20, $form->getElement('SortOrder')->getValue());
    }

    public function testPopulateFromModelWithId()
    {
        $form = new Admin_Form_Series();

        $series = Series::get(2);

        $form->populateFromModel($series);

        $this->assertEquals(2, $form->getElement('Id')->getValue());
    }

    public function testUpdateModel()
    {
        $form = new Admin_Form_Series();

        $form->getElement('Title')->setValue('TestTitle');
        $form->getElement('Infobox')->setValue('TestInfo');
        $form->getElement('Visible')->setValue(1);
        $form->getElement('SortOrder')->setValue(22);

        $series = Series::new();

        $form->updateModel($series);

        $this->assertEquals('TestTitle', $series->getTitle());
        $this->assertEquals('TestInfo', $series->getInfobox());
        $this->assertEquals(1, $series->getVisible());
        $this->assertEquals(22, $series->getSortOrder());
    }

    public function testValidationEmptyPost()
    {
        $form = new Admin_Form_Series();

        $this->assertFalse($form->isValid([]));

        $this->assertContains('isEmpty', $form->getErrors('Title'));
    }

    public function testValidationEmptyFields()
    {
        $form = new Admin_Form_Series();

        $this->assertFalse($form->isValid(['Title' => '  ']));

        $this->assertContains('isEmpty', $form->getErrors('Title'));
    }

    public function testValidationTrue()
    {
        $form = new Admin_Form_Series();

        $this->assertTrue($form->isValid(['Title' => 'TestTitle', 'SortOrder' => '50']));
    }
}
