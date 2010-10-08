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
 * @category    Application
 * @package     Tests
 * @author      Thoralf Klein <thoralf.klein@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
class Remotecontrol_AccountControllerTest extends ControllerTestCase {

    private $login;
    private $password;
    private $roles;

    private $requestData = array();

    public function setUp() {
        parent::setUp();
    }

    private function addTestAccountWithRoles() {

        // Make sure, the role exists.
        $role = Opus_Role::fetchByName("administrator");
        $this->assertNotNull( $role );

        // Prepare first request.
        $this->login    = 'foobar'.rand();
        $this->password = 'bla';
        $this->roles    = array("foobar","administrator");

        $this->requestData = array(
                    'login'      => $this->login,
                    'password'   => $this->password,
                    'user-roles' => implode(",", $this->roles),
        );

        /* Creating first collection to work with. */
        $this->request
                ->setMethod('POST')
                ->setPost($this->requestData);
        $this->dispatch('/remotecontrol/account/add');

        // Make sure, this request returned successfully.
        $this->assertResponseCode(200);
        $this->assertController('account');
        $this->assertAction('add');

        $body = $this->getResponse()->getBody();
        $this->checkForBadStringsInHtml($body);
        $this->assertContains('SUCCESS', $body);
    }

    /**
     * Simple test action to check "add" module.
     */
    public function testAddAction() {
        $this->addTestAccountWithRoles();

        // Test if created account really exists...
        $account = Opus_Account::fetchAccountByLogin($this->login);
        $this->assertTrue($account instanceof Opus_Account);
        $this->assertEquals($this->login, $account->getLogin());
        $this->assertTrue($account->isPasswordCorrect($this->password));

        // Test if created account has requested roles
        $roles = $account->getRole();
        $this->assertEquals(1, count($roles));
        $this->assertEquals("administrator", $roles[0]->getName());

    }

    /**
     * Test action to check "add" module, expect failure at second insert.
     */
    public function testAddDoubleInsertAction() {
        $this->addTestAccountWithRoles();

        // First request has been issued in setUp.
        // Second insert with same key should fail.
        $this->request
                ->setMethod('POST')
                ->setPost($this->requestData);
        $this->dispatch('/remotecontrol/account/add');
        $this->assertResponseCode(400);
    }

    /**
     * Simple test action to check "add" module.
     */
    public function testChangeEmptyPasswordFailsAction() {
        $this->addTestAccountWithRoles();

        // Test if changing password works...
        $requestData = array(
                    'login'    => $this->login,
                    'password' => '',
        );

        /* Creating first collection to work with. */
        $this->request
                ->setMethod('POST')
                ->setPost($requestData);
        $this->dispatch('/remotecontrol/account/change-password');

        // Make sure, this request returned successfully.
        $this->assertResponseCode(400);
        $this->assertController('account');
        $this->assertAction('change-password');

        $body = $this->getResponse()->getBody();
        $this->assertContains('ERROR', $body);

        // Test if account really has old password
        $account = Opus_Account::fetchAccountByLogin($this->login);
        $this->assertTrue($account instanceof Opus_Account);
        $this->assertEquals($this->login, $account->getLogin());
        $this->assertTrue($account->isPasswordCorrect($this->password));
        $this->assertFalse($account->isPasswordCorrect(''));
    }

    /**
     * Simple test action to check "add" module.
     */
    public function testChangePasswordAction() {
        $this->addTestAccountWithRoles();

        // Test if changing password works...
        $password = "bla-new-".rand();
        $requestData = array(
                    'login'    => $this->login,
                    'password' => $password,
        );

        /* Creating first collection to work with. */
        $this->request
                ->setMethod('POST')
                ->setPost($requestData);
        $this->dispatch('/remotecontrol/account/change-password');

        // Make sure, this request returned successfully.
        $this->assertResponseCode(200);
        $this->assertController('account');
        $this->assertAction('change-password');

        $body = $this->getResponse()->getBody();
        $this->checkForBadStringsInHtml($body);
        $this->assertContains('SUCCESS', $body);

        // Test if created account really exists...
        $account = Opus_Account::fetchAccountByLogin($this->login);
        $this->assertTrue($account instanceof Opus_Account);
        $this->assertEquals($this->login, $account->getLogin());
        $this->assertTrue($account->isPasswordCorrect($password));
        $this->assertFalse($account->isPasswordCorrect($this->password));

    }

}
