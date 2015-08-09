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
 * @category    Application Unit Tests
 * @package     Application_Form_Model
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class Application_Form_Model_TableTest extends ControllerTestCase {

    public function testConstructForm() {
        $form = new Application_Form_Model_Table();

        $this->assertEquals(2, count($form->getDecorators()));
    }

    public function testGetColumnLabel() {
        $form = new Application_Form_Model_Table();

        $form->setColumns(array(array('label' => 'Opus_Licence')));

        $this->assertEquals('Opus_Licence', $form->getColumnLabel(0));
    }

    public function testgetColumnLabelUnknownIndex() {
        $form = new Application_Form_Model_Table();

        $form->setColumns(array(array('label' => 'Opus_Licence')));

        $this->assertNull($form->getColumnLabel(1));
    }

    public function testSetGetModels() {
        $form = new Application_Form_Model_Table();

        $models = Opus_Licence::getAll();

        $form->setModels($models);

        $this->assertEquals($models, $form->getModels());
    }

    /**
     * @expectedException Application_Exception
     * @expectedExceptionMessage Parameter must be array.
     */
    public function testSetModelNotArray() {
        $form = new Application_Form_Model_Table();

        $models = Opus_Licence::getAll();

        $form->setModels('notanarray');
    }

    public function testSetGetModelsNull() {
        $form = new Application_Form_Model_Table();

        $form->setModels(Opus_Licence::getAll());

        $this->assertNotNull($form->getModels());

        $form->setModels(null);

        $this->assertNull($form->getModels());
    }

    public function testSetGetColumns() {
        $form = new Application_Form_Model_Table();

        $columns = array(array('label' => 'col1'));

        $form->setColumns($columns);

        $this->assertEquals($columns, $form->getColumns());
    }

    public function testGetViewScript() {
        $form = new Application_Form_Model_Table();

        $this->assertEquals('modeltable.phtml', $form->getViewScript());
    }

    public function testSetViewScript() {
        $form = new Application_Form_Model_Table();

        $form->setViewScript('series/modeltable.phtml');

        $this->assertEquals('series/modeltable.phtml', $form->getViewScript());

        $form->setViewScript(null);

        $this->assertEquals('modeltable.phtml', $form->getViewScript());
    }

    public function testIsRenderShowActionLinkDefault() {
        $form = new Application_Form_Model_Table();

        $this->assertTrue($form->isRenderShowActionLink(null));
    }

    public function testIsModifiableDefault() {
        $form = new Application_Form_Model_Table();

        $this->assertTrue($form->isModifiable(null));
    }

}
