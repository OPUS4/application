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
 * @category    Application Unit Test
 * @package     Form_Element
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class Form_Element_TextTest extends ControllerTestCase {

    public function testConstruct() {
        $element = new Form_Element_Text('text');

        $this->assertEquals(8, count($element->getDecorators()));
        $this->assertNotNull($element->getDecorator('ViewHelper'));

        $paths = $element->getPluginLoader(Zend_Form::DECORATOR)->getPaths();
        $this->assertArrayHasKey('Form_Decorator_', $paths);
        $this->assertContains('Form/Decorator/', $paths['Form_Decorator_']);
    }

    public function testSetGetHint() {
        $element = new Form_Element_Text('text');

        $this->assertNull($element->getHint());

        $element->setHint('Hinweis');

        $this->assertEquals('Hinweis', $element->getHint());
    }

    public function testLoadDefaultDecorators() {
        $element = new Form_Element_Text('text');

        $element->setDecorators(array());

        $this->assertEmpty($element->getDecorators());

        $element->loadDefaultDecorators();

        $this->assertEquals(8, count($element->getDecorators()));
        $this->assertNotNull($element->getDecorator('ViewHelper'));
    }

    public function testLoadDefaultDecoratorsDisabled() {
        $element = new Form_Element_Text('text', array('disableLoadDefaultDecorators' => true));

        $this->assertEmpty($element->getDecorators());
    }

    public function testLoadDefaultDecoratorsCustomDecorators() {
        $element = new Form_Element_Text('text', array('decorators' => array('ViewHelper')));

        $this->assertEquals(1, count($element->getDecorators()));
        $this->assertNotNull($element->getDecorator('ViewHelper'));
    }

    public function testPrepareRenderingAsView() {
        $element = new Form_Element_Text('text');

        $element->prepareRenderingAsView();

        $this->assertEquals(1, count($element->getDecorators()));
        $this->assertNotNull($element->getDecorator('StaticView'));
    }

}