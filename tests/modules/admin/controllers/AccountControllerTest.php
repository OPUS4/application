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
 * Basic unit tests for the Admin_AccountController class.
 */
class Admin_AccountControllerTest extends ControllerTestCase {

    /**
     * Tests routing to and successfull execution of 'index' action.
     */
    public function testIndexAction() {
        $this->dispatch('/admin/account');
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('account');
        $this->assertAction('index');
    }

    /**
     * Tests showing an account.
     */
    public function testShowAction() {
        $this->dispatch('/admin/account/show/id/1');
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('account');
        $this->assertAction('show');
    }

    public function testShowActionWithoutId() {
        $this->dispatch('/admin/account/show');
        $this->assertModule('admin');
        $this->assertController('account');
        $this->assertAction('show');
        $this->assertRedirect('/admin/account/index');
    }

    /**
     * Tests showing form for new account.
     */
    public function testNewAction() {
        $this->dispatch('/admin/account/new');
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('account');
        $this->assertAction('new');
    }

    /**
     * Tests showing account for editing.
     */
    public function testEditAction() {
        $this->dispatch('/admin/account/edit/id/1');
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('account');
        $this->assertAction('edit');
    }

    public function testEditActionWithoutId() {
        $this->dispatch('/admin/account/edit');
        $this->assertModule('admin');
        $this->assertController('account');
        $this->assertAction('edit');
        $this->assertRedirect('/admin/account/index');
    }

    /**
     * Tests creating a new account.
     *
     * FIXME cancel form for now, since creating account results in database lock timeout
     */
    public function testCreateAction() {
         $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'username' => 'wally',
                    'password' => 'dummypassword',
                    'confirmPassword' => 'dummypassword',
                    'roleguest' => '1',
                    'roleadministrator' => '0',
                    'submit' => 'submit'
                ));

        $this->dispatch('/admin/account/create');
        $this->assertModule('admin');
        $this->assertController('account');
        $this->assertAction('create');
        $this->assertRedirect();
        $this->assertNotNull(new Opus_Account(null, null, 'wally'));
    }

    public function testCreateActionCancel() {
         $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'cancel' => 'cancel'
                ));

        $this->dispatch('/admin/account/create');
        $this->assertModule('admin');
        $this->assertController('account');
        $this->assertAction('create');
        $this->assertRedirect('/admin/account/index');
    }

    public function testCreateActionMissingInput() {
         $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'password' => 'dummypassword',
                    'confirmPassword' => 'dummypassword',
                    'roleguest' => '1',
                    'roleadministrator' => '0',
                    'submit' => 'submit'
                ));
        $this->dispatch('/admin/account/create');
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('account');
        $this->assertAction('create');
    }

    /**
     * Tests updating an account.
     *
     * @depends testCreateAction
     */
    public function testUpdateAction() {
        $account = new Opus_Account(null, null, 'wally');
        $id = $account->getId();
        $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'id' => $id,
                    'username' => 'wally2',
                    'roleguest' => '1',
                    'roleadministrator' => '0',
                    'submit' => 'submit'
                ));

        $this->dispatch('/admin/account/update');
        $this->assertController('account');
        $this->assertAction('update');
        $this->assertRedirect();
        $this->assertNotNull(new Opus_Account(null, null, 'wally2'));
    }

    public function testUpdateActionCancel() {
        $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'cancel' => 'cancel'
                ));
        $this->dispatch('/admin/account/update');
        $this->assertModule('admin');
        $this->assertController('account');
        $this->assertAction('update');
        $this->assertRedirect('/admin/account/index');
    }

    /**
     * @depends testUpdateAction
     */
    public function testUpdateActionMissingInput() {
        $account = new Opus_Account(null, null, 'wally2');
        $id = $account->getId();
        $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'id' => $id,
                    'roleguest' => '1',
                    'roleadministrator' => '0',
                    'submit' => 'submit'
                ));

        $this->dispatch('/admin/account/update');
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('account');
        $this->assertAction('update');
    }

    /**
     * @depends testUpdateActionMissingInput
     */
    public function testUpdateActionChangePassword() {
        $account = new Opus_Account(null, null, 'wally2');
        $id = $account->getId();
        $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'id' => $id,
                    'username' => 'wally2',
                    'password' => 'newpassword',
                    'confirmPassword' => 'newpassword',
                    'roleguest' => '1',
                    'roleadministrator' => '0',
                    'submit' => 'submit'
                ));

        $this->dispatch('/admin/account/update');
        $this->assertController('account');
        $this->assertAction('update');
        $this->assertRedirect();
        $this->assertNotNull(new Opus_Account(null, null, 'wally2'));
    }

    /**
     * Tests deleting an account.
     *
     * @depends testUpdateActionChangePassword
     */
    public function testDeleteAction() {
        $account = new Opus_Account(null, null, 'wally2');
        $id = $account->getId();
        $this->dispatch('/admin/account/delete/id/' . $id);
        $this->assertController('account');
        $this->assertAction('delete');
        $this->assertRedirect('/admin/account/index');
    }

}

?>
