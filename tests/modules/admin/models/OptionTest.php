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
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2016, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */
class Admin_Model_OptionTest extends ControllerTestCase {

    private $_model;

    public function setUp() {
        parent::setUp();

        $this->_model = new Admin_Model_Option('test', array(
            'key' => 'supportedLanguages',
            'type' => 'number',
            'section' => 'search',
            'options' => array(
                'min' => 11,
                'max' =>19
            )
        ));
    }

    public function testGetOptions() {
        $this->assertEquals(array('min' => 11, 'max' => 19), $this->_model->getOptions());
    }

    public function testGetEmptyOptions() {
        $model = new Admin_Model_Option('test', array('type' => 'number'));

        $this->assertEquals(array(), $model->getOptions());
    }

    public function testGetSection() {
        $this->assertEquals('search', $this->_model->getSection());
    }

    public function testGetDefaultSection() {
        $model = new Admin_Model_Option('test', array());

        $this->assertEquals('general', $model->getSection());
    }

    public function testGetElementType() {
        $this->assertEquals('number', $this->_model->getElementType());
    }

    public function testGetDefaultElementType() {
        $model = new Admin_Model_Option('test', array());
        $this->assertEquals('text', $model->getElementType());
    }

    public function testGetLabel() {
        $this->assertEquals(Admin_Form_Configuration::LABEL_TRANSLATION_PREFIX . 'test', $this->_model->getLabel());
    }

    public function testGetDescription() {
        $this->assertEquals(
            Admin_Form_Configuration::LABEL_TRANSLATION_PREFIX . 'test_description',
            $this->_model->getDescription()
        );
    }

    public function testGetName() {
        $this->assertEquals('test', $this->_model->getName());
    }

    public function testGetKey() {
        $this->assertEquals('supportedLanguages', $this->_model->getKey());
    }

}
