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
 * @author      Edouard Simon (edouard.simon@zib.de)
 * @copyright   Copyright (c) 2008-2019, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

class Application_Security_AclProviderTest extends ControllerTestCase
{

    protected $additionalResources = 'database';

    private $roleId;
    private $userId;

    public function setUp()
    {
        parent::setUp();
        $testRole = new Opus_UserRole();
        $testRole->setName('_test');

        $testRole->appendAccessModule('documents');

        $this->roleId = $testRole->store();

        $userAccount = new Opus_Account();
        $userAccount->setLogin('role_tester')
            ->setPassword('role_tester');
        $userAccount->setRole($testRole);
        $this->userId = $userAccount->store();

        // fake authentication
        Zend_Auth::getInstance()->getStorage()->write('role_tester');
    }

    public function tearDown()
    {
        parent::tearDown();
        $testRole = new Opus_UserRole($this->roleId);
        $testRole->delete();
        $userAccount = new Opus_Account($this->userId);
        $userAccount->delete();
    }

    public function testGetAcls()
    {
        $aclProvider = new Application_Security_AclProvider();
        $acl = $aclProvider->getAcls();
        $this->assertTrue($acl instanceof Zend_Acl, 'Excpected instance of Zend_Acl');
        $this->assertTrue(
            $acl->isAllowed(Application_Security_AclProvider::ACTIVE_ROLE, 'documents'),
            "expectec user has access to resource 'documents'"
        );
        $this->assertFalse(
            $acl->isAllowed(Application_Security_AclProvider::ACTIVE_ROLE, 'accounts'),
            "expectec user has no access to resource 'accounts'"
        );
    }

    public function testRoleNameLikeUserName()
    {
        $userAccount = new Opus_Account();
        $userAccount->setLogin('_test')
            ->setPassword('role_tester');
        $userAccount->setRole(new Opus_UserRole($this->roleId));
        $userId = $userAccount->store();
        Zend_Auth::getInstance()->getStorage()->write('_test');

        $aclProvider = new Application_Security_AclProvider();
        $acl = $aclProvider->getAcls();
        $userAccount->delete();
        $this->assertTrue($acl instanceof Zend_Acl, 'Excpected instance of Zend_Acl');
        $this->assertTrue(
            $acl->isAllowed(Application_Security_AclProvider::ACTIVE_ROLE, 'documents'),
            "expected user has access to resource 'documents'"
        );
        $this->assertFalse(
            $acl->isAllowed(Application_Security_AclProvider::ACTIVE_ROLE, 'accounts'),
            "expected user has no access to resource 'account'"
        );
    }

    public function testGetAllResources()
    {
        $aclResources = Application_Security_AclProvider::$resourceNames;

        $allResources = [];

        foreach ($aclResources as $resources) {
            $allResources = array_merge($allResources, $resources);
        }
        $aclProvider = new Application_Security_AclProvider();
        $this->assertEquals($allResources, $aclProvider->getAllResources());
    }
}
