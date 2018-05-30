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
 * Unit tests for authentication controller.
 *
 * TODO complete tests
 *
 * @covers AuthController
 */
class AuthControllerTest extends ControllerTestCase {

    public function testIndexAction() {
        $this->dispatch('/auth');
        $this->assertResponseCode(200);
    }

    public function testIndexActionLoggedIn() {
        $this->loginUser('admin', 'adminadmin');
        $this->dispatch('/auth');
        $this->assertResponseCode(200);
    }

    /**
     * <input type="hidden" name="hash" value="641c4c9e211577ecdd0bd3fcfc1375b8" id="hash" />
     */
    public function testGetLoginPage() {
        $this->dispatch('/auth/index/rmodule/home/rcontroller/index/raction/index');
        $this->assertResponseCode(200);
        $response = $this->getResponse();
        $treffer = preg_match('/<input.*name="hash".*value="(.*?)".*\/>/', $response->getBody(), $matches);
        $this->assertEquals(1, $treffer);
    }

    public function testLoginActionRedirectToHomeIfAlreadyLoggedIn() {
        $this->loginUser('security1', 'security1pwd');
        $this->dispatch('/auth/login');
        $this->assertRedirectTo('/home');
    }

    /**
     * @depends testGetLoginPage
     */
    public function testLoginAction() {
        $this->dispatch('/auth/index/rmodule/home/rcontroller/index/raction/index');
        $this->assertResponseCode(200);
        $response = $this->getResponse();
        $treffer = preg_match('/<input.*name="hash".*value="(.*?)".*\/>/', $response->getBody(), $matches);
        $this->assertEquals(1, $treffer);
        $hash = $matches[1];
        $this->resetRequest();
        $this->request
                ->setMethod('POST')
                ->setPost(array(
                   'hash' => $hash,
                   'login' => 'admin',
                   'password' => 'adminadmin'
                ));
        $this->dispatch('/auth/login/rmodule/home/rcontroller/index/raction/index');
        $this->assertRedirect('/home/index/index');
        $this->assertModule('default');
        $this->assertController('auth');
        $this->assertAction('login');
    }

    public function testLogoutActionAsAdmin() {
        $this->loginUser('admin', 'adminadmin');
        $this->dispatch('/auth/logout/rmodule/home/rcontroller/index/raction/index');
        $this->assertResponseLocationHeader($this->response, '/home');
        $this->assertResponseCode('302');
        $this->assertNull(Zend_Auth::getInstance()->getIdentity());
    }

    public function testLogoutActionAsAnonymous() {
        $this->dispatch('/auth/logout/rmodule/home/rcontroller/index/raction/index');
        $this->assertResponseLocationHeader($this->response, '/home');
        $this->assertResponseCode('302');
        $this->assertNull(Zend_Auth::getInstance()->getIdentity());
    }

    public function testLogoutActionFromAdministrationModule() {
        $this->loginUser('admin', 'adminadmin');
        $this->dispatch('/auth/logout/rmodule/admin/rcontroller/index/raction/index');
        $this->assertNotContains('Argument 4 passed to Zend_Controller_Action_Helper_Redirector::direct() must be an array, null given', $this->response->outputBody());
        $this->assertResponseLocationHeader($this->response, '/home');
        $this->assertResponseCode('302');
        $this->assertNull(Zend_Auth::getInstance()->getIdentity());
    }

    public function testLogoutActionFromAnyModule() {
        $this->loginUser('admin', 'adminadmin');
        $this->dispatch('/auth/logout/rmodule/any/rcontroller/index/raction/index');
        $this->assertResponseLocationHeader($this->response, '/home');
        $this->assertResponseCode('302');
        $this->assertNull(Zend_Auth::getInstance()->getIdentity());
    }
}

