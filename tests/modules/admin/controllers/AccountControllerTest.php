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

use Opus\Common\Account;
use Opus\Common\Security\SecurityException;

/**
 * Basic unit tests for the Admin_AccountController class.
 *
 * @covers Admin_AccountController
 */
class Admin_AccountControllerTest extends ControllerTestCase
{
    /** @var string */
    protected $additionalResources = 'all';

    public static function tearDownAfterClass(): void
    {
        // even if something fails, the created test account won't be left in database
        $accounts = ['wally', 'wally2'];

        foreach ($accounts as $login) {
            try {
                $account = Account::fetchAccountByLogin($login);
                $account->delete();
            } catch (SecurityException $ex) {
            }
        }
    }

    /**
     * Tests routing to and successfull execution of 'index' action.
     */
    public function testIndexAction()
    {
        $this->dispatch('/admin/account');
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('account');
        $this->assertAction('index');

        // check information shown (Stichproben)
        $this->assertQueryContentContains('td.accountname', 'admin');
        $this->assertQueryContentContains('td.accountname', 'security4');
        $this->assertQueryContentContains('td.lastname', 'Zugriff auf Review und Admin Modul');
        $this->assertQueryContentContains('td.firstname', 'security4');
        $this->assertQueryContentContains('td.email', 'security4@example.org');
        $this->assertQueryContentContains('td.roles', 'reviewer');
        $this->assertQueryContentContains('td.roles', 'fulladmin');
    }

    /**
     * Tests showing an account.
     */
    public function testShowAction()
    {
        $this->dispatch('/admin/account/show/id/1');
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('account');
        $this->assertAction('show');
    }

    public function testShowActionWithoutId()
    {
        $this->dispatch('/admin/account/show');
        $this->assertModule('admin');
        $this->assertController('account');
        $this->assertAction('show');
        $this->assertRedirect('/admin/account/index');
    }

    /**
     * Tests showing form for new account.
     */
    public function testNewAction()
    {
        $this->dispatch('/admin/account/new');
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('account');
        $this->assertAction('new');
    }

    /**
     * Tests showing account for editing.
     */
    public function testEditAction()
    {
        $this->dispatch('/admin/account/edit/id/1');
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('account');
        $this->assertAction('edit');
    }

    public function testEditActionWithoutId()
    {
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
    public function testCreateAction()
    {
         $this->getRequest()
                ->setMethod('POST')
                ->setPost([
                    'username'        => 'wally',
                    'firstname'       => 'wally',
                    'lastname'        => 'walross',
                    'email'           => 'wally@example.org',
                    'password'        => 'dummypassword',
                    'confirmPassword' => 'dummypassword',
                    'roles'           => [
                        'guest'         => '1',
                        'administrator' => '0',
                    ],
                    'Save'            => 'Save',
                ]);

        $this->dispatch('/admin/account/new');
        $this->assertModule('admin');
        $this->assertController('account');
        $this->assertAction('new');
        $this->assertRedirect();
        $this->assertNotNull(Account::fetchAccountByLogin('wally'));
    }

    public function testCreateActionCancel()
    {
         $this->getRequest()
                ->setMethod('POST')
                ->setPost([
                    'Cancel' => 'Cancel',
                ]);

        $this->dispatch('/admin/account/new');
        $this->assertModule('admin');
        $this->assertController('account');
        $this->assertAction('new');
        $this->assertRedirect('/admin/account/index');
    }

    public function testCreateActionMissingInput()
    {
         $this->getRequest()
                ->setMethod('POST')
                ->setPost([
                    'password'        => 'dummypassword',
                    'confirmPassword' => 'dummypassword',
                    'roles'           => [
                        'guest'         => '1',
                        'administrator' => '0',
                    ],
                    'Save'            => 'Save',
                ]);
        $this->dispatch('/admin/account/new');
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('account');
        $this->assertAction('new');
    }

    /**
     * Tests updating an account.
     *
     * @depends testCreateAction
     */
    public function testUpdateAction()
    {
        $account = Account::fetchAccountByLogin('wally');
        $id      = $account->getId();
        $this->getRequest()
                ->setMethod('POST')
                ->setPost([
                    'Id'        => $id,
                    'username'  => 'wally2',
                    'firstname' => 'wally',
                    'lastname'  => 'walross',
                    'email'     => 'wally@example.org',
                    'roles'     => [
                        'guest'         => '1',
                        'administrator' => '0',
                    ],
                    'Save'      => 'Save',
                ]);

        $this->dispatch('/admin/account/edit');
        $this->assertController('account');
        $this->assertAction('edit');
        $this->assertRedirect();
        $this->assertNotNull(Account::fetchAccountByLogin('wally2'));
    }

    public function testUpdateActionCancel()
    {
        $this->getRequest()
                ->setMethod('POST')
                ->setPost([
                    'Cancel' => 'Cancel',
                ]);
        $this->dispatch('/admin/account/edit');
        $this->assertModule('admin');
        $this->assertController('account');
        $this->assertAction('edit');
        $this->assertRedirect('/admin/account/index');
    }

    /**
     * @depends testUpdateAction
     */
    public function testUpdateActionMissingInput()
    {
        $account = Account::fetchAccountByLogin('wally2');
        $id      = $account->getId();
        $this->getRequest()
                ->setMethod('POST')
                ->setPost([
                    'Id'    => $id,
                    'roles' => [
                        'roleguest'         => '1',
                        'roleadministrator' => '0',
                    ],
                    'Save'  => 'Save',
                ]);

        $this->dispatch('/admin/account/edit');
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('account');
        $this->assertAction('edit');
    }

    /**
     * @depends testUpdateActionMissingInput
     */
    public function testUpdateActionChangePassword()
    {
        $account = Account::fetchAccountByLogin('wally2');
        $id      = $account->getId();
        $this->getRequest()
                ->setMethod('POST')
                ->setPost([
                    'Id'              => $id,
                    'username'        => 'wally2',
                    'firstname'       => 'wally',
                    'lastname'        => 'walross',
                    'email'           => 'wally@example.org',
                    'password'        => 'newpassword',
                    'confirmPassword' => 'newpassword',
                    'roles'           => [
                        'roleguest'         => '1',
                        'roleadministrator' => '0',
                    ],
                    'submit'          => 'submit',
                ]);

        $this->dispatch('/admin/account/edit');
        $this->assertController('account');
        $this->assertAction('edit');
        $this->assertRedirect();
        $this->assertNotNull(Account::fetchAccountByLogin('wally2'));
    }

    /**
     * Tests deleting an account.
     *
     * @depends testUpdateActionChangePassword
     */
    public function testDeleteAction()
    {
        $account = Account::fetchAccountByLogin('wally2');
        $id      = $account->getId();
        $this->getRequest()
            ->setMethod('POST')
            ->setPost([
                'Id'         => $id,
                'ConfirmYes' => 'Yes',
            ]);

        $this->dispatch('/admin/account/delete');
        $this->assertController('account');
        $this->assertAction('delete');
        $this->assertRedirect('/admin/account/index');

        $this->expectException(SecurityException::class);
        $this->assertNull(Account::fetchAccountByLogin('wally2'));
    }

    public function testDeleteActionDeleteSelf()
    {
        $user = Account::new();
        $user->setLogin('john');
        $user->setPassword('testpwd');
        $user->store();

        $this->loginUser('john', 'testpwd');

        $this->dispatch('/admin/account/delete/id/' . $user->getId());
        $this->assertController('account');
        $this->assertAction('delete');
        $this->assertRedirect('/admin/account/index');

        $user = Account::fetchAccountByLogin('john');
        $this->assertNotNull($user);
        $user->delete();
    }

    public function testDeleteActionDeleteAdmin()
    {
        $user = Account::fetchAccountByLogin('admin');
        $this->dispatch('/admin/account/delete/id/' . $user->getId());
        $this->assertController('account');
        $this->assertAction('delete');
        $this->assertRedirect('/admin/account/index');
        $user = Account::fetchAccountByLogin('admin');
        $this->assertNotNull($user);
    }

    public function testHideDeleteLinkForAdmin()
    {
        $user = Account::fetchAccountByLogin('admin');
        $this->dispatch('/admin/account');
        $this->assertResponseCode(200);

        $this->assertQueryCount("a[@href='" . $this->getRequest()->getBaseUrl()
            . "/admin/account/delete/id/" . $user->getId() . "']", 0, "There should be no delete link for 'admin'.");
    }

    public function testHideDeleteLinkForCurrentUser()
    {
        $this->enableSecurity();
        $this->loginUser('security4', 'security4pwd');

        $this->dispatch('/admin/account');
        $this->assertResponseCode(200, $this->getResponse()->getBody());
        $this->logoutUser();

        $user = Account::fetchAccountByLogin('security4');

        $this->assertQueryCount(
            "a[@href='" . $this->getRequest()->getBaseUrl()
            . "/admin/account/delete/id/" . $user->getId() . "']",
            0,
            "There should be no delete link for current user'."
        );
    }
}
