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
use Opus\Common\AccountInterface;
use Opus\Common\Security\SecurityException;

/**
 * Basic unit tests for account form for users.
 */
class Account_Form_AccountTest extends ControllerTestCase
{
    /** @var string[] */
    protected $additionalResources = ['database', 'translation'];

    /** @var AccountInterface  */
    private $account;

    public function setUp(): void
    {
        parent::setUp();

        try {
            $account = Account::fetchAccountByLogin('user');
        } catch (SecurityException $ex) {
            $account = Account::new();
            $account->setLogin('user');
            $account->setPassword('userpwd');
            $account->store();
        }

        $this->account = $account;
    }

    public function tearDown(): void
    {
        if ($this->account !== null) {
            $this->account->delete();
        }

        parent::tearDown();
    }

    /**
     * Test creating an account form.
     */
    public function testCreateForm()
    {
        $form = new Account_Form_Account('user');

        $this->assertNotNull($form);
    }

    /**
     * OPUSVIER-2413 Regression Test.
     */
    public function testDoNotLowerCaseUsername()
    {
        $form = new Account_Form_Account('user');

        $username = $form->getElement("username");

        $username->setValue('DummYuser');

        $this->assertTrue($username->getValue() === 'DummYuser');
    }

    public function testChangedLoginNameValidationExistingLoginNameAccount()
    {
        $form = new Account_Form_Account('user');

        $this->assertNotNull($form);

        $postData = [
            'username'        => 'admin',
            'roleguest'       => '1',
            'password'        => 'notchanged',
            'confirmPassword' => 'notchanged',
        ];

        $this->assertFalse($form->isValid($postData));
    }

    public function testChangedLoginNameValidationNewLoginName()
    {
        $form    = new Account_Form_Account();
        $account = Account::fetchAccountByLogin('user');
        $form->populateFromModel($account);

        $this->assertNotNull($form);

        $postData = [
            'username'  => 'newuser',
            'roleguest' => '1',
            'password'  => 'notchanged',
            'confirm'   => 'notchanged',
        ];

        $this->assertTrue($form->isValid($postData));
    }

    public function testEditValidationSameAccount()
    {
        $form    = new Account_Form_Account();
        $account = Account::fetchAccountByLogin('user');
        $form->populateFromModel($account);

        // check that form was populated
        $this->assertEquals('user', $form->getElement('username')->getValue());

        $postData = [
            'username'  => 'user',
            'oldLogin'  => 'user', // added by AccountController based on ID
            'roleguest' => '1',
            'password'  => 'notchanged',
            'confirm'   => 'notchanged',
        ];

        $this->assertTrue($form->isValid($postData));
    }

    public function testValidationMissmatchedPasswords()
    {
        $form    = new Account_Form_Account();
        $account = Account::fetchAccountByLogin('user');
        $form->populateFromModel($account);

        $postData = [
            'username'  => 'user',
            'roleguest' => '1',
            'password'  => 'password',
            'confirm'   => 'different',
        ];

        $this->assertFalse($form->isValid($postData));

        $errors = $form->getErrors(null, true);

        $this->assertTrue(isset($errors['confirm']));
        $this->assertTrue(in_array('notMatch', $errors['confirm']));
    }

    public function testValidationBadEmail()
    {
        $form    = new Account_Form_Account();
        $account = Account::fetchAccountByLogin('user');
        $form->populateFromModel($account);

        $postData = [
            'username'  => 'user',
            'roleguest' => '1',
            'email'     => 'notAnEmail',
            'password'  => 'password',
            'confirm'   => 'password',
        ];

        $this->assertFalse($form->isValid($postData));

        $errors = $form->getErrors(null, true);

        $this->assertTrue(isset($errors['email']));
        $this->assertTrue(in_array('emailAddressInvalidFormat', $errors['email']));
    }
}
