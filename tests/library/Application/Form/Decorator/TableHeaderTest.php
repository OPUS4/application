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

class Application_Form_Decorator_TableHeaderTest extends ControllerTestCase
{
    /** @var string[] */
    protected $additionalResources = ['view', 'translation'];

    /** @var string[][] */
    private $columns = [
        [
            'label' => 'column1',
            'class' => 'name',
        ],
        [
            'label' => 'column2',
            'class' => 'size',
        ],
    ];

    public function testConstruct()
    {
        $decorator = new Application_Form_Decorator_TableHeader(
            ['placement' => 'prepend', 'columns' => $this->columns]
        );

        $this->assertEquals(Zend_Form_Decorator_Abstract::PREPEND, $decorator->getPlacement());
        $this->assertEquals($this->columns, $decorator->getColumns());
    }

    public function testSetOptionColumns()
    {
        $decorator = new Application_Form_Decorator_TableHeader();

        $decorator->setOption('columns', $this->columns);

        $this->assertEquals($this->columns, $decorator->getColumns());
    }

    public function testSetGetColumns()
    {
        $decorator = new Application_Form_Decorator_TableHeader();

        $decorator->setColumns($this->columns);

        $this->assertEquals($this->columns, $decorator->getColumns());
    }

    public function testRender()
    {
        $decorator = new Application_Form_Decorator_TableHeader();

        $decorator->setColumns($this->columns);

        $form = new Zend_Form();
        $form->addSubForm(new Zend_Form_SubForm(), 'subform1');

        $decorator->setElement($form);

        $markup = $decorator->render('content');

        $this->assertEquals(
            '<thead><tr><th class="name">column1</th><th class="size">column2</th></tr></thead>content',
            $markup
        );
    }

    public function testRenderEscape()
    {
        $decorator = new Application_Form_Decorator_TableHeader();

        $decorator->setColumns([['label' => '<h1>HTML</h1>']]);

        $form = new Zend_Form();
        $form->addSubForm(new Zend_Form_SubForm(), 'subform1');

        $decorator->setElement($form);

        $markup = $decorator->render('content');

        $this->assertEquals(
            '<thead><tr><th class="">&lt;h1&gt;HTML&lt;/h1&gt;</th></tr></thead>content',
            $markup
        );
    }

    public function testRenderTranslate()
    {
        $this->useGerman();

        $decorator = new Application_Form_Decorator_TableHeader();

        $decorator->setColumns([['label' => 'Value']]);

        $form = new Zend_Form();
        $form->addSubForm(new Zend_Form_SubForm(), 'subform1');

        $decorator->setElement($form);

        $markup = $decorator->render('content');

        $this->assertEquals(
            '<thead><tr><th class="">Text</th></tr></thead>content',
            $markup
        );
    }
}
