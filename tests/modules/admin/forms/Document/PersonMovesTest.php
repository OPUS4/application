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
 * @copyright   Copyright (c) 2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

class Admin_Form_Document_PersonMovesTest extends ControllerTestCase
{
    public function testConstructForm()
    {
        $form = new Admin_Form_Document_PersonMoves();

        $this->assertCount(4, $form->getElements());

        $this->assertNotNull($form->getElement('First'));
        $this->assertNotNull($form->getElement('Up'));
        $this->assertNotNull($form->getElement('Down'));
        $this->assertNotNull($form->getElement('Last'));
    }

    public function testConstructFormPositionFirst()
    {
        $form = new Admin_Form_Document_PersonMoves(Admin_Form_Document_PersonMoves::POSITION_FIRST);

        $this->assertCount(2, $form->getElements());

        $this->assertNotNull($form->getElement('Down'));
        $this->assertNotNull($form->getElement('Last'));
    }

    public function testConstructFormPositionLast()
    {
        $form = new Admin_Form_Document_PersonMoves(Admin_Form_Document_PersonMoves::POSITION_LAST);

        $this->assertCount(2, $form->getElements());

        $this->assertNotNull($form->getElement('First'));
        $this->assertNotNull($form->getElement('Up'));
    }

    public function testProcessPost()
    {
        $form = new Admin_Form_Document_PersonMoves();

        $post = [
            'First' => 'Erster',
        ];

        $result = $form->processPost($post, null);

        $this->assertNotNull($result);
        $this->assertArrayHasKey('result', $result);
        $this->assertEquals(Admin_Form_Document_PersonMoves::RESULT_MOVE, $result['result']);
        $this->assertArrayHasKey('move', $result);
        $this->assertEquals('First', $result['move']);
    }

    public function testChangePosition()
    {
        $form = new Admin_Form_Document_PersonMoves();

        $this->assertCount(4, $form->getElements());

        $form->changePosition(Admin_Form_Document_PersonMoves::POSITION_FIRST);

        $this->assertCount(2, $form->getElements());
        $this->assertNotNull($form->getElement('Down'));
        $this->assertNotNull($form->getElement('Last'));

        $form->changePosition(Admin_Form_Document_PersonMoves::POSITION_LAST);

        $this->assertCount(2, $form->getElements());
        $this->assertNotNull($form->getElement('First'));
        $this->assertNotNull($form->getElement('Up'));

        $form->changePosition(Admin_Form_Document_PersonMoves::POSITION_DEFAULT);

        $this->assertCount(4, $form->getElements());
    }

    public function testProcessPostEmpty()
    {
        $form = new Admin_Form_Document_PersonMoves();

        $this->assertNull($form->processPost([], null));
    }
}
