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
 * @category    Application Unit Test
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class Admin_Form_AccountTest extends ControllerTestCase {

    private $_account;


    public function setUp()
    {
        parent::setUp();

        $user = new Opus_Account();
        $user->setLogin('user');
        $user->setPassword('userpwd');
        $user->store();

        $this->_account = $user;
    }

    public function tearDown()
    {
        if (!is_null($this->_account))
        {
            $this->_account->delete();
        }

        parent::tearDown();
    }

    public function testCreateForm() {
        $form = new Admin_Form_Account();
        $this->assertNotNull($form);
    }

    public function testCreateFormForUser() {
        $user = new Opus_Account(null, null, 'user');
        $form = new Admin_Form_Account($user->getId());
        $this->assertNotNUll($form);
        $this->assertEquals('user', $form->getElement('username')->getValue());
    }

    /**
     * Test that the checkbox for the role 'administrator' is disabled if the
     * current user is editing him or herself.
     */
    public function testCreateFormForCurrentUser() {
        $this->loginUser('admin', 'adminadmin');
        $user = new Opus_Account(null, null, 'admin');
        $form = new Admin_Form_Account($user->getId());
        $this->assertNotNull($form);
        $element = $form->getElement('roleadministrator');
        $this->assertEquals(true, $element->getAttrib('disabled'));
    }

    /**
     * Test creating an account form.
     */
    public function testDoNotLowerCaseUsername() {
        $user = new Opus_Account(null, null, 'user');

        $form = new Admin_Form_Account($user->getId());

        $this->assertNotNull($form);

        $username = $form->getElement("username");

        $username->setValue('DummYuser');

        $this->assertTrue($username->getValue() === 'DummYuser', $username->getValue());
    }

    public function testChangedLoginNameValidationExistingLoginNameAccount() {
        $user = new Opus_Account(null, null, 'user');

        $form = new Admin_Form_Account($user->getId());

        $this->assertNotNull($form);

        $postData = array(
            'username' => 'admin',
            'roleguest' => '1',
            'password' => 'notchanged',
            'confirmPassword' => 'notchanged'
            );

        $this->assertFalse($form->isValid($postData));
    }

    public function testChangedLoginNameValidationNewLoginName() {
        $user = new Opus_Account(null, null, 'user');

        $form = new Admin_Form_Account($user->getId());

        $this->assertNotNull($form);

        $postData = array(
            'username' => 'newuser',
            'roleguest' => '1',
            'password' => 'notchanged',
            'confirmPassword' => 'notchanged'
            );

        $this->assertTrue($form->isValid($postData));
    }

    public function testEditValidationSameAccount() {
        $user = new Opus_Account(null, null, 'user');

        $form = new Admin_Form_Account($user->getId());

        // check that form was populated
        $this->assertEquals('user', $form->getElement('username')->getValue());

        $postData = array(
            'username' => 'user',
            'oldLogin' => 'user', // added by AccountController based on ID
            'roleguest' => '1',
            'password' => 'notchanged',
            'confirmPassword' => 'notchanged'
            );

        $this->assertTrue($form->isValid($postData));
    }

    public function testValidationMissmatchedPasswords() {
        $form = new Admin_Form_Account();

        $postData = array(
            'username' => 'newaccount',
            'roleguest' => '1',
            'password' => 'password',
            'confirmPassword' => 'different'
        );

        $this->assertFalse($form->isValid($postData));

        $this->assertContains('notMatch', $form->getErrors('confirmPassword'));
    }

    public function testValidationBadEmail() {
        $form = new Admin_Form_Account();

        $postData = array(
            'username' => 'newaccount',
            'roleguest' => '1',
            'email' => 'notAnEmail',
            'password' => 'password',
            'confirmPassword' => 'password'
        );

        $this->assertFalse($form->isValid($postData));

        $this->assertContains('emailAddressInvalidFormat', $form->getErrors('email'));
    }

}

