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

class Application_Form_Validate_LoginAvailableTest extends ControllerTestCase
{
    /** @var string */
    protected $additionalResources = 'database';

    /** @var Application_Form_Validate_LoginAvailable */
    private $validator;

    /** @var AccountInterface */
    private $account;

    public function setUp(): void
    {
        parent::setUp();
        $this->validator = new Application_Form_Validate_LoginAvailable();

        $user = Account::new();
        $user->setLogin('user');
        $user->setPassword('userpwd');
        $user->store();

        $this->account = $user;
    }

    public function tearDown(): void
    {
        if ($this->account !== null) {
            $this->account->delete();
        }

        parent::tearDown();
    }

    /**
     * Test that validation of existing user account is not case sensitive.
     * This test will break if that behaviour of the database/framework will
     * change.
     */
    public function testValidationNotCaseSensitive()
    {
        $this->assertFalse($this->validator->isValid('User'));
        $this->assertFalse($this->validator->isValid('user'));
        $this->assertFalse($this->validator->isValid('uSer'));
        $this->assertFalse($this->validator->isValid('USER'));
    }

    public function testAccountAvailable()
    {
        $this->assertTrue($this->validator->isValid('newuser'));
    }

    public function testAccountNotAvailable()
    {
        $this->assertFalse($this->validator->isValid('admin'));
    }

    public function testAccountAvailableInEditMode()
    {
        $validator = new Application_Form_Validate_LoginAvailable(['ignoreCase' => true]);
        $this->assertTrue($validator->isValid('newuser'));
    }

    public function testAccountNotAvailableInEditMode()
    {
        $validator = new Application_Form_Validate_LoginAvailable(['ignoreCase' => true]);
        $this->assertFalse($validator->isValid('admin'));
    }

    /**
     * Im Edit Mode (ignoreCase) ist die Validierung auch erfolgreich, wenn der
     * Account bereits existiert, aber sich der neue Name nur im Case von Zeichen
     * vom alten Namen unterscheidet.
     */
    public function testIgnoreCaseChangesForEditMode()
    {
        $validator = new Application_Form_Validate_LoginAvailable(['ignoreCase' => true]);

        $context = ['oldLogin' => 'admin'];

        $this->assertTrue($validator->isValid('ADMIN', $context));
        $this->assertTrue($validator->isValid('aDmin', $context));
    }

    public function testNotAvailableForEditMode()
    {
        $validator = new Application_Form_Validate_LoginAvailable(['ignoreCase' => true]);

        $context = ['oldLogin' => 'admin'];

        $this->assertFalse($validator->isValid('user', $context));
    }
}
