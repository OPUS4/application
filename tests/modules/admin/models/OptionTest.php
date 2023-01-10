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

class Admin_Model_OptionTest extends ControllerTestCase
{
    /** @var Admin_Model_Option */
    private $model;

    public function setUp(): void
    {
        parent::setUp();

        $this->model = new Admin_Model_Option('test', [
            'key'     => 'supportedLanguages',
            'type'    => 'number',
            'section' => 'search',
            'options' => [
                'min' => 11,
                'max' => 19,
            ],
        ]);
    }

    public function testGetOptions()
    {
        $this->assertEquals(['min' => 11, 'max' => 19], $this->model->getOptions());
    }

    public function testGetEmptyOptions()
    {
        $model = new Admin_Model_Option('test', ['type' => 'number']);

        $this->assertEquals([], $model->getOptions());
    }

    public function testGetSection()
    {
        $this->assertEquals('search', $this->model->getSection());
    }

    public function testGetDefaultSection()
    {
        $model = new Admin_Model_Option('test', []);

        $this->assertEquals('general', $model->getSection());
    }

    public function testGetElementType()
    {
        $this->assertEquals('number', $this->model->getElementType());
    }

    public function testGetDefaultElementType()
    {
        $model = new Admin_Model_Option('test', []);
        $this->assertEquals('text', $model->getElementType());
    }

    public function testGetLabel()
    {
        $this->assertEquals(Admin_Form_Configuration::LABEL_TRANSLATION_PREFIX . 'test', $this->model->getLabel());
    }

    public function testGetDescription()
    {
        $this->assertEquals(
            Admin_Form_Configuration::LABEL_TRANSLATION_PREFIX . 'test_description',
            $this->model->getDescription()
        );
    }

    public function testGetName()
    {
        $this->assertEquals('test', $this->model->getName());
    }

    public function testGetKey()
    {
        $this->assertEquals('supportedLanguages', $this->model->getKey());
    }
}
