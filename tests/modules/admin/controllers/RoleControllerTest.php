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
 * @category    Tests
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2018, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Basic unit tests for Admin_RoleController class.
 *
 * @covers Admin_RoleController
 */
class Admin_RoleControllerTest extends ControllerTestCase {

    /**
     * Test showing index page.
     */
    public function testIndexAction() {
        $this->dispatch('/admin/role');
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('role');
        $this->assertAction('index');
    }

    /**
     * Test show role information.
     */
    public function testShowAction() {
        $this->dispatch('/admin/role/show/id/1');
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('role');
        $this->assertAction('show');
    }

    public function testShowActionWithoutId() {
        $this->dispatch('/admin/role/show');
        $this->assertRedirect('/admin/role/index');
        $this->assertModule('admin');
        $this->assertController('role');
        $this->assertAction('show');
    }

    /**
     * Test showing form for new role.
     */
    public function testNewAction() {
        $this->dispatch('/admin/role/new');
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('role');
        $this->assertAction('new');
    }

    /**
     * Test showing form for editing role.
     */
    public function testEditAction() {
        $this->dispatch('/admin/role/edit/id/1');
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('role');
        $this->assertAction('edit');
    }

    public function testEditActionWithoutId() {
        $this->dispatch('/admin/role/edit');
        $this->assertRedirect('/admin/role/index');
        $this->assertModule('admin');
        $this->assertController('role');
        $this->assertAction('edit');
    }

    public function testCreateAction() {
        $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'name' => 'testrole',
                    'privilegeadministrate' => '1',
                    'metadatadeleted' => '1',
                    'submit' => 'submit'
                ));

        $this->dispatch('/admin/role/create');
        $this->assertModule('admin');
        $this->assertController('role');
        $this->assertAction('create');
        $this->assertRedirect('/admin/role/index');
        $this->assertNotNull(Opus_UserRole::fetchByName('testrole'));
    }

    public function testCreateActionCancel() {
         $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'name' => 'testrole',
                    'privilegeadministrate' => '1',
                    'metadatadeleted' => '1',
                    'cancel' => 'cancel'
                ));

        $this->dispatch('/admin/role/create');
        $this->assertModule('admin');
        $this->assertController('role');
        $this->assertAction('create');
        $this->assertRedirect('/admin/role/index');
    }

    public function testCreateActionMissingInput() {
         $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'privilegeadministrate' => '1',
                    'metadatadeleted' => '1',
                    'submit' => 'submit'
                ));

        $this->dispatch('/admin/role/create');
        $this->assertModule('admin');
        $this->assertController('role');
        $this->assertAction('create');
        $this->assertResponseCode(200);
    }

    /**
     * @depends testCreateAction
     */
    public function testUpdateAction() {
        $role = Opus_UserRole::fetchByName('testrole');

         $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'name' => 'testrole2',
                    'privilegeclearance' => '1',
                    'metadatapublished' => '1',
                    'metadatadeleted' => '1',
                    'submit' => 'submit'
                ));

        $this->dispatch('/admin/role/update/id/' . $role->getId());
        $this->assertModule('admin');
        $this->assertController('role');
        $this->assertAction('update');
        $this->assertRedirect();
        $role = Opus_UserRole::fetchByName('testrole2');
        $this->assertNotNull($role);
        $this->assertNotNull($role->getId());
        $this->assertEquals('testrole2', $role->getDisplayName());
    }

    /**
     * @depends testUpdateAction
     */
    public function testUpdateActionInvalidInput() {
        $role = Opus_UserRole::fetchByName('testrole2');

         $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'name' => '',
                    'privilegeclearance' => '1',
                    'metadatapublished' => '1',
                    'metadatadeleted' => '1',
                    'submit' => 'submit'
                ));

        $this->dispatch('/admin/role/update/id/' . $role->getId());
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('role');
        $this->assertAction('update');
    }

    /**
     * @depends testUpdateActionInvalidInput
     */
    public function testDeleteAction() {
        $role = Opus_UserRole::fetchByName('testrole2');
        $this->assertNotNull($role);
        $this->dispatch('/admin/role/delete/id/' . $role->getId());
        $this->assertModule('admin');
        $this->assertController('role');
        $this->assertAction('delete');
        $this->assertRedirect();
    }

}

