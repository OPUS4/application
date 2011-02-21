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
 * @category    TODO
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Basic unit tests for IP range controller in admin module.
 */
class Admin_IprangeControllerTest extends ControllerTestCase {

    /**
     * Tests routing to and successfull execution of 'index' action.
     */
    public function testIndexAction() {
        $this->dispatch('/admin/iprange');
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('iprange');
        $this->assertAction('index');
    }

    /**
     * Test showing form for adding a new IP range.
     */
    public function testNewAction() {
        $this->dispatch('/admin/iprange/new');
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('iprange');
        $this->assertAction('new');
    }

    /**
     * Tests creating a new IP range.
     */
    public function testCreateAction() {
        $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'name' => 'Localhost',
                    'startingip' => '127.0.0.1',
                    'endingip' => '127.0.0.1',
                    'roleguest' => '1',
                    'roleadministrator' => '0',
                    'submit' => 'submit'
                ));

        $this->dispatch('/admin/iprange/create');
        $this->assertController('iprange');
        $this->assertAction('create');
        $this->assertRedirect();
    }

    /**
     * Tests showing the information about an IP range.
     *
     * @depends testCreateAction
     */
    public function testShowAction() {
        $ipRanges = Opus_Iprange::getAll();
        $id = $ipRanges[0]->getId();

        $this->dispatch('/admin/iprange/show/id/' . $id);
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('iprange');
        $this->assertAction('show');
    }

    /**
     * Tests showing an edit form for an IP range.
     *
     * @depends testCreateAction
     */
    public function testEditAction() {
        $ipRanges = Opus_Iprange::getAll();
        $id = $ipRanges[0]->getId();

        $this->dispatch('/admin/iprange/edit/id/' . $id);
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('iprange');
        $this->assertAction('edit');
    }

    /**
     * Tests storing updated information for an IP range.
     *
     * @depends testEditAction
     */
    public function testUpdateAction() {
        $ipRanges = Opus_Iprange::getAll();
        $id = $ipRanges[0]->getId();

        $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'id' => $id,
                    'name' => 'MyComputer',
                    'startingip' => '127.0.0.1',
                    'endingip' => '127.0.0.1',
                    'roleguest' => '1',
                    'roleadministrator' => '0',
                    'submit' => 'submit'
                ));

        $this->dispatch('/admin/iprange/update');
        $this->assertController('iprange');
        $this->assertAction('update');
        $this->assertRedirect();
    }

    /**
     * Tests deleting an IP range.
     *
     * @depends testUpdateAction
     */
    public function testDeleteAction() {
        $ipRanges = Opus_Iprange::getAll();
        $id = $ipRanges[0]->getId();

        $this->dispatch('/admin/iprange/delete/id/' . $id);
        $this->assertRedirectTo('/admin/iprange');
    }

}

?>
