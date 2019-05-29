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
 * @category    Tests
 * @package     Account
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2018, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Basic unit tests for account module.
 *
 * @covers Account_IndexController
 */
class Account_IndexControllerTest extends ControllerTestCase {

    private $user;

    public function setUp() {
        parent::setUp();

        $this->deleteUser('john');
        $this->user = new Opus_Account();
        $this->user->setLogin('john');
        $this->user->setPassword('testpwd');
        $this->user->store();
    }

    public function tearDown() {
        $this->user->delete();
        parent::tearDown();
    }

    private function deleteUser($username) {
        $account = Opus_Account::fetchAccountByLogin($username);
        if ($account instanceof Opus_Account) {
            $account->delete();
        }
    }

    /**
     * Test showing account information (if editOwnAccount allowed!)
     */
    public function testIndexSuccessAction() {
        $config = Zend_Registry::get('Zend_Config');
        $config->account->editOwnAccount = 1;

        $this->loginUser('admin', 'adminadmin');
        $this->dispatch('/account');
        $this->assertResponseCode(200);
        $this->assertModule('account');
        $this->assertController('index');
        $this->assertAction('index');
    }

    /**
     * Test showing account information (if editOwnAccount disabled!)
     */
    public function testIndexDeniedIfEditAccountDisabledAction() {
        $config = Zend_Registry::get('Zend_Config');
        $config->account->editOwnAccount = 0;

        $this->loginUser('admin', 'adminadmin');
        $this->dispatch('/account');
        $this->assertRedirectTo('/auth/index/rmodule/account/rcontroller/index/raction/index');
    }

    /**
     * Test showing account information (not allowed for user!)
     */
    public function testIndexWithoutLoginAction() {
        $this->dispatch('/account');
        $this->assertNotResponseCode(200);
        $this->assertRedirectTo('/auth/index/rmodule/account/rcontroller/index/raction/index');
    }

    public function testChangePasswordFailsOnMissingInputAction() {
        $config = Zend_Registry::get('Zend_Config');
        $config->account->editOwnAccount = 1;

        $this->loginUser('john', 'testpwd');
        $this->request
                ->setMethod('POST')
                ->setPost(array(
                   'password' => 'newpassword'
                ));
        $this->dispatch('/account/index/save');
        $this->assertResponseCode(200);
        $this->assertModule('account');
        $this->assertController('index');
        $this->assertAction('save');

        // Check if change failed...
        $account = new Opus_Account(null, null, 'john');
        $this->assertTrue( $account->isPasswordCorrect('testpwd') );
        $this->assertFalse( $account->isPasswordCorrect('newpassword') );

        $this->assertContains('<ul class="errors">', $this->getResponse()->getBody());
    }

    public function testChangePasswordFailsOnNoMatch() {
        $config = Zend_Registry::get('Zend_Config');
        $config->account->editOwnAccount = 1;

        $this->loginUser('john', 'testpwd');
        $this->request
                ->setMethod('POST')
                ->setPost(array(
                   'password' => 'newpassword',
                   'confirmPassword' => 'anotherpassword'
                ));
        $this->dispatch('/account/index/save');
        $this->assertResponseCode(200);
        $this->assertModule('account');
        $this->assertController('index');
        $this->assertAction('save');

        // Check if change failed...
        $account = new Opus_Account(null, null, 'john');
        $this->assertTrue( $account->isPasswordCorrect('testpwd') );
        $this->assertFalse( $account->isPasswordCorrect('newpassword') );

        $this->assertContains('<ul class="errors">', $this->getResponse()->getBody());
    }

    /**
     * Test modifying account information.
     */
    public function testChangePasswordSuccess() {
        $config = Zend_Registry::get('Zend_Config');
        $config->account->editOwnAccount = 1;

        $this->loginUser('john', 'testpwd');
        $this->request
                ->setMethod('POST')
                ->setPost(array(
                   'username' => 'john',
                   'firstname' => '',
                   'lastname' => '',
                   'email' => '',
                   'password' => 'newpassword',
                   'confirm' => 'newpassword'
                ));
        $this->dispatch('/account/index/save');
        $this->assertRedirect();

        // Check if change succeeded...
        $account = new Opus_Account(null, null, 'john');
        $this->assertTrue( $account->isPasswordCorrect('newpassword') );

        $this->assertNotContains('<ul class="errors">', $this->getResponse()->getBody());
    }

    /**
     * Test modifying account information.
     */
    public function testChangePasswordSuccessWithSpecialChars() {
        $config = Zend_Registry::get('Zend_Config');
        $config->account->editOwnAccount = 1;

        $this->loginUser('john', 'testpwd');
        $this->request
                ->setMethod('POST')
                ->setPost(array(
                   'username' => 'john',
                   'firstname' => '',
                   'lastname' => '',
                   'email' => '',
                   'password' => 'new@pwd$%',
                   'confirm' => 'new@pwd$%'
                ));
        $this->dispatch('/account/index/save');
        $this->assertRedirect();

        // Check if change succeeded...
        $account = new Opus_Account(null, null, 'john');
        $this->assertTrue( $account->isPasswordCorrect('new@pwd$%') );

        $this->assertNotContains('<ul class="errors">', $this->getResponse()->getBody());
    }

    /**
     * Test changing login.
     */
    public function testChangeLoginSuccess() {
        $config = Zend_Registry::get('Zend_Config');
        $config->account->editOwnAccount = 1;

        $this->deleteUser('john2');

        $this->loginUser('john', 'testpwd');
        $this->getRequest()
                ->setMethod('POST')
                ->setPost(array(
                   'username' => 'john2',
                   'firstname' => '',
                   'lastname' => '',
                   'email' => ''
                ));
        $this->dispatch('/account/index/save');

        $this->assertRedirect();

        // Check if new user exists (with proper password) and old does not...
        $account = Opus_Account::fetchAccountByLogin('john2');
        $this->assertNotNull( $account );
        $this->assertTrue( $account->isPasswordCorrect('testpwd') );

        $account = Opus_Account::fetchAccountByLogin('john');
        $this->assertNull( $account );

        // Delete user 'john2' if we're done...
        $this->deleteUser('john2');
    }

    public function testAccessAccountModule() {
        $this->useEnglish();
        $this->enableSecurity();
        $this->loginUser("security7", "security7pwd");
        $this->dispatch('/account');
        $this->assertQueryContentContains('//html/head/title', 'Account');
        $this->assertQueryContentContains("//div", 'security7');
    }

    public function testNoAccessAccountModule() {
        $this->enableSecurity();
        $this->loginUser("security1", "security1pwd");
        $this->dispatch('/account');
        $this->assertRedirectTo('/auth/index/rmodule/account/rcontroller/index/raction/index');
    }
}
