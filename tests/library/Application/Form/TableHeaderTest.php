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

class Application_Form_TableHeaderTest extends TestCase
{
    /** @var Application_Form_TableHeader */
    private $form;

    /** @var array */
    private $columns = [
        ['label' => null, 'class' => 'file'],
        ['label' => 'files_column_size', 'class' => 'size'],
        ['label' => 'files_column_language', 'class' => 'language'],
        ['label' => 'files_column_frontdoor', 'class' => 'visiblefrontdoor'],
        ['label' => 'files_column_oai', 'class' => 'visibleoai'],
    ];

    public function setUp(): void
    {
        parent::setUp();

        $this->form = new Application_Form_TableHeader($this->columns);
    }

    public function testConstructForm()
    {
        $this->assertEquals($this->columns, $this->form->getColumns());
    }

    public function testInit()
    {
        $this->assertEquals(1, count($this->form->getDecorators()));
        $this->assertNotNull($this->form->getDecorator('ViewScript'));
    }

    public function testConstructFormNull()
    {
        $this->expectException(Application_Exception::class);
        $this->expectExceptionMessage('Parameter \'columns\' must be array.');
        new Application_Form_TableHeader(null);
    }

    public function testConstructFormNotArray()
    {
        $this->expectException(Application_Exception::class);
        $this->expectExceptionMessage('Parameter \'columns\' must be array.');
        new Application_Form_TableHeader('notAnArray');
    }

    public function testGetColumnCount()
    {
        $this->assertEquals(5, $this->form->getColumnCount());
    }

    public function testGetColumnLabel()
    {
        $this->assertNull($this->form->getColumnLabel(0));
        $this->assertEquals('files_column_size', $this->form->getColumnLabel(1));
        $this->assertEquals('files_column_language', $this->form->getColumnLabel(2));
        $this->assertEquals('files_column_frontdoor', $this->form->getColumnLabel(3));
        $this->assertEquals('files_column_oai', $this->form->getColumnLabel(4));
    }

    public function testGetColumnClass()
    {
        $this->assertEquals('file', $this->form->getColumnClass(0));
        $this->assertEquals('size', $this->form->getColumnClass(1));
        $this->assertEquals('language', $this->form->getColumnClass(2));
        $this->assertEquals('visiblefrontdoor', $this->form->getColumnClass(3));
        $this->assertEquals('visibleoai', $this->form->getColumnClass(4));
    }
}
