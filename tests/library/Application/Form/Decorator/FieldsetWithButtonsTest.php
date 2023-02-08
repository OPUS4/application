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

class Application_Form_Decorator_FieldsetWithButtonsTest extends ControllerTestCase
{
    /** @var string */
    protected $additionalResources = 'view';

    /** @var Application_Form_Decorator_FieldsetWithButtons */
    private $decorator;

    /** @var Zend_Form_SubForm */
    private $form;

    public function setUp(): void
    {
        parent::setUp();

        $this->form = new Zend_Form_SubForm();
        $this->form->setView(new Zend_View());
        $this->form->setLegend('Test');

        $this->decorator = new Application_Form_Decorator_FieldsetWithButtons();
        $this->decorator->setElement($this->form);
    }

    public function testRender()
    {
        $this->assertEquals(
            '<fieldset><legend>Test</legend>' . PHP_EOL . 'content</fieldset>',
            $this->decorator->render('content')
        );
    }

    public function testRenderWithButton()
    {
        $this->form->addElement('submit', 'Add');

        $this->decorator->setLegendButtons('Add');

        $this->assertEquals(
            '<fieldset><legend>Test<span class="button-group">'
            . '<span class="data-wrapper Add-data">'
            . '<span class="field" id="Add-element">' . PHP_EOL
            . '<input type="submit" name="Add" id="Add" value="Add" /></span></span>'
            . '</span></legend>' . PHP_EOL . 'content</fieldset>',
            $this->decorator->render('content')
        );
    }

    public function testRenderWithTwoButtons()
    {
        $this->form->addElement('submit', 'Add');
        $this->form->addElement('submit', 'Import');

        $this->decorator->setLegendButtons(['Import', 'Add']);

        $this->assertEquals(
            '<fieldset><legend>Test<span class="button-group">'
            . '<span class="data-wrapper Import-data">'
            . '<span class="field" id="Import-element">' . PHP_EOL
            . '<input type="submit" name="Import" id="Import" value="Import" /></span></span>'
            . '<span class="data-wrapper Add-data">'
            . '<span class="field" id="Add-element">' . PHP_EOL
            . '<input type="submit" name="Add" id="Add" value="Add" /></span></span>'
            . '</span></legend>' . PHP_EOL . 'content</fieldset>',
            $this->decorator->render('content')
        );
    }
}
