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

class Admin_Form_ConfigurationTest extends ControllerTestCase
{
    /** @var string[] */
    protected $additionalResources = ['translation'];

    public function testInit()
    {
        $form = new Admin_Form_Configuration();

        $this->assertTrue(count($form->getElements()) > 3);
        $this->assertNotNull($form->getElement('Save'));
        $this->assertNotNull($form->getElement('Cancel'));
    }

    public function testPopulateFromModel()
    {
        $form = new Admin_Form_Configuration();

        $form->populateFromModel(new Zend_Config([
            'searchengine' => ['solr' => ['parameterDefaults' => ['rows' => '20']]],
        ])); // searchengine.solr.parameterDefaults.rows

        $element = $form->getElement('maxSearchResults');

        $this->assertNotNull($element);
        $this->assertEquals(20, $element->getValue());
    }

    public function testUpdateModel()
    {
        $form = new Admin_Form_Configuration();

        $form->getElement('maxSearchResults')->setValue(15);

        $config = new Zend_Config([], true);

        $form->updateModel($config);

        $this->assertEquals(15, $config->searchengine->solr->parameterDefaults->rows);
    }

    public function testValidationSuccess()
    {
        $form = new Admin_Form_Configuration();

        $this->assertTrue($form->isValid([
            'supportedLanguages' => ['de'],
            'maxSearchResults'   => '10',
        ]));
    }

    public function testValidationFailure()
    {
        $form = new Admin_Form_Configuration();

        $this->assertFalse($form->isValid([
            'supportedLanguages' => ['ru'],
        ]));
    }
}
