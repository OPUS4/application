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

use Opus\Common\UserRole;

/**
 * Basic unit tests for Admin_RoleController class.
 *
 * @covers Admin_RoleController
 */
class Admin_RoleControllerTest extends ControllerTestCase
{
    /** @var string */
    protected $additionalResources = 'all';

    /**
     * Test showing index page.
     */
    public function testIndexAction()
    {
        $this->dispatch('/admin/role');
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('role');
        $this->assertAction('index');
    }

    /**
     * Test show role information.
     */
    public function testShowAction()
    {
        $this->dispatch('/admin/role/show/id/1');
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('role');
        $this->assertAction('show');
    }

    public function testShowActionWithoutId()
    {
        $this->dispatch('/admin/role/show');
        $this->assertRedirect('/admin/role/index');
        $this->assertModule('admin');
        $this->assertController('role');
        $this->assertAction('show');
    }

    /**
     * Test showing form for new role.
     */
    public function testNewAction()
    {
        $this->dispatch('/admin/role/new');
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('role');
        $this->assertAction('new');
    }

    /**
     * Test showing form for editing role.
     */
    public function testEditAction()
    {
        $this->dispatch('/admin/role/edit/id/10');
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('role');
        $this->assertAction('edit');
    }

    public function testEditActionWithoutId()
    {
        $this->dispatch('/admin/role/edit');
        $this->assertRedirect('/admin/role/index');
        $this->assertModule('admin');
        $this->assertController('role');
        $this->assertAction('edit');
    }

    public function testCreateAction()
    {
        $this->getRequest()
                ->setMethod('POST')
                ->setPost([
                    'Name'                  => 'testrole',
                    'privilegeadministrate' => '1',
                    'metadatadeleted'       => '1',
                    'Save'                  => 'Save',
                ]);

        $this->dispatch('/admin/role/new');
        $this->assertModule('admin');
        $this->assertController('role');
        $this->assertAction('new');
        $this->assertRedirect('/admin/role/index');
        $this->assertNotNull(UserRole::fetchByName('testrole'));
    }

    public function testCreateActionCancel()
    {
         $this->getRequest()
                ->setMethod('POST')
                ->setPost([
                    'name'                  => 'testrole',
                    'privilegeadministrate' => '1',
                    'metadatadeleted'       => '1',
                    'Cancel'                => 'Cancel',
                ]);

        $this->dispatch('/admin/role/new');
        $this->assertModule('admin');
        $this->assertController('role');
        $this->assertAction('new');
        $this->assertRedirect('/admin/role/index');
    }

    public function testCreateActionMissingInput()
    {
         $this->getRequest()
                ->setMethod('POST')
                ->setPost([
                    'privilegeadministrate' => '1',
                    'metadatadeleted'       => '1',
                    'Save'                  => 'Save',
                ]);

        $this->dispatch('/admin/role/new');
        $this->assertModule('admin');
        $this->assertController('role');
        $this->assertAction('new');
        $this->assertResponseCode(200);
    }

    /**
     * @depends testCreateAction
     */
    public function testUpdateAction()
    {
        $role = UserRole::fetchByName('testrole');

         $this->getRequest()
                ->setMethod('POST')
                ->setPost([
                    'Name'               => 'testrole2',
                    'privilegeclearance' => '1',
                    'metadatapublished'  => '1',
                    'metadatadeleted'    => '1',
                    'Save'               => 'Save',
                ]);

        $this->dispatch('/admin/role/edit/id/' . $role->getId());
        $this->assertModule('admin');
        $this->assertController('role');
        $this->assertAction('edit');
        $this->assertRedirect();
        $role = UserRole::fetchByName('testrole2');
        $this->assertNotNull($role);
        $this->assertNotNull($role->getId());
        $this->assertEquals('testrole2', $role->getDisplayName());
    }

    /**
     * @depends testUpdateAction
     */
    public function testUpdateActionInvalidInput()
    {
        $role = UserRole::fetchByName('testrole2');

         $this->getRequest()
                ->setMethod('POST')
                ->setPost([
                    'Name'               => '',
                    'privilegeclearance' => '1',
                    'metadatapublished'  => '1',
                    'metadatadeleted'    => '1',
                    'Save'               => 'Save',
                ]);

        $this->dispatch('/admin/role/edit/id/' . $role->getId());
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('role');
        $this->assertAction('edit');
    }

    /**
     * @depends testUpdateActionInvalidInput
     */
    public function testDeleteAction()
    {
        $role = UserRole::fetchByName('testrole2');
        $this->assertNotNull($role);
        $this->getRequest()->setMethod('POST')
            ->setPost([
                'Id'         => $role->getId(),
                'ConfirmYes' => 'Yes',
            ]);

        $this->dispatch('/admin/role/delete/id/' . $role->getId());
        $this->assertModule('admin');
        $this->assertController('role');
        $this->assertAction('delete');
        $this->assertRedirect();
    }
}
