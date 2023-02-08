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

/**
 * Unit Tests für Controller Helper für Zugriffsprüfungen.
 */
class Application_Controller_Action_Helper_AccessControlTest extends ControllerTestCase
{
    /** @var string[] */
    protected $additionalResources = ['database'];

    /** @var Application_Controller_Action_Helper_AccessControl */
    private $accessControl;

    public function setUp(): void
    {
        parent::setUpWithEnv('production');
        $this->assertSecurityConfigured();
        $acl = Application_Security_AclProvider::getAcl();
        $acl->allow('guest', 'accounts');
        $this->accessControl = new Application_Controller_Action_Helper_AccessControl();
    }

    public function tearDown(): void
    {
        $acl = Application_Security_AclProvider::getAcl();
        $acl->deny('guest', 'accounts');
        parent::tearDown();
    }

    public function testAccessAllowed()
    {
        $user = Zend_Auth::getInstance()->getIdentity();
        $this->assertEquals('', $user, "expected no user to be set (should use default 'guest' as default)");

        $allowedDocuments = $this->accessControl->accessAllowed('documents');
        $this->assertFalse($allowedDocuments, "expected access denied to resource 'documents'");

        $allowedAccount = $this->accessControl->accessAllowed('accounts');
        $this->assertTrue($allowedAccount, "expected access allowed to module 'account'");
    }

    public function testAccessAllowedForEmptyResource()
    {
        $this->expectException(Application_Exception::class);
        $this->expectExceptionMessage('#1 argument must not be empty|null');
        $this->accessControl->accessAllowed('  ');
    }

    public function testAccessAllowedForNullResource()
    {
        $this->expectException(Application_Exception::class);
        $this->expectExceptionMessage('#1 argument must not be empty|null');
        $this->accessControl->accessAllowed(null);
    }

    public function testAccessAllowedForUnknownResource()
    {
        $this->expectException(Zend_Acl_Exception::class);
        $this->expectExceptionMessage('Resource \'unknown\' not found');
        $this->assertFalse($this->accessControl->accessAllowed('unknown'));
    }
}
