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
 * Basic unit tests for account module.
 */
class Account_IndexControllerTest extends ControllerTestCase {

    private $user;

    public function setUp() {
        parent::setUp();
        $this->user = new Opus_Account();
        $this->user->setLogin('john');
        $this->user->setPassword('testpwd');
        $this->user->store();
    }

    public function tearDown() {
        parent::tearDown();
        $this->user->delete();
    }

    /**
     * Test showing account information.
     */
    public function testIndexAction() {
        $this->loginUser('admin', 'adminadmin');
        $this->dispatch('/account');
        $this->assertResponseCode(200);
        $this->assertModule('account');
        $this->assertController('index');
        $this->assertAction('index');
    }

    public function testIndexActionWithoutLogin() {
        $this->dispatch('/account');
        $this->assertModule('account');
        $this->assertController('index');
        $this->assertAction('index');
        $this->assertRedirect('/default/auth/index');
    }

    public function testSaveActionMissingInput() {
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
    }

    /**
     * Test modifying account information.
     */
    public function testChangePassword() {
        $this->loginUser('john', 'testpwd');
        $this->request
                ->setMethod('POST')
                ->setPost(array(
                   'username' => 'john',
                   'firstname' => '',
                   'lastname' => '',
                   'email' => '',
                   'password' => 'newpassword',
                   'confirmPassword' => 'newpassword'
                ));
        $this->dispatch('/account/index/save');
        $this->assertRedirect();

        $this->loginUser('john', 'newpassword');
    }

    /**
     * Test changing login.
     */
    public function testChangeLogin() {
        $this->loginUser('john', 'testpwd');
        $this->request
                ->setMethod('POST')
                ->setPost(array(
                   'username' => 'john2',
                   'firstname' => '',
                   'lastname' => '',
                   'email' => ''
                ));
        $this->dispatch('/account/index/save');
        $this->assertRedirect();

        $this->loginUser('john2', 'testpwd');

        $user = new Opus_Account(null, null, 'john2');
    }

}

?>
