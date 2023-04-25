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

use Opus\Common\UserRole;

class Admin_Form_RoleTest extends ControllerTestCase
{
    /** @var string[] */
    protected $additionalResources = ['database'];

    public function testCreateForm()
    {
        $form = new Admin_Form_Role();
        $this->assertNotNull($form);
    }

    public function testContructWithRole()
    {
        $role = UserRole::fetchByName('guest');
        $form = new Admin_Form_Role($role->getId());
        $this->assertEquals('guest', $form->getElementValue(Admin_Form_Role::ELEMENT_NAME));
    }

    public function testPopulateFromModel()
    {
        $role = UserRole::fetchByName('administrator');
        $form = new Admin_Form_Role();

        $form->populateFromModel($role);
        $this->assertEquals('administrator', $form->getElementValue(Admin_Form_Role::ELEMENT_NAME));
    }

    /**
     * @return array[]
     */
    public function validRoleNameDataProvider()
    {
        return [
            ['abc'],
            ['abcd'],
            ['t17'],
            ['ABc'],
        ];
    }

    /**
     * @param string $validName Role name
     * @dataProvider validRoleNameDataProvider
     */
    public function testValidRoleName($validName)
    {
        $form = new Admin_Form_Role();
        $this->assertTrue($form->isValid([Admin_Form_Role::ELEMENT_NAME => $validName]), $validName);
    }

    /**
     * @return array[]
     */
    public function invalidRoleNameDataProvider()
    {
        return [
            [''],
            ['123'],
            ['ab'],
            ['12'],
            ['1234'],
            ['guest'], // already exists
            ['1ab'],
            ['a-b'],
        ];
    }

    /**
     * @param string $invalidName Role name
     * @dataProvider invalidRoleNameDataProvider
     */
    public function testInvalidRoleName($invalidName)
    {
        $form = new Admin_Form_Role();
        $this->assertFalse($form->isValid([Admin_Form_Role::ELEMENT_NAME => $invalidName]), $invalidName);
    }

    public function testValidationTranslated()
    {
        $this->application->bootstrap('translation');

        $form = new Admin_Form_Role();

        $name = $form->getElement(Admin_Form_Role::ELEMENT_NAME);

        $this->useEnglish();
        $name->isValid('');
        $messages = $name->getMessages();

        $this->assertArrayHasKey('regexNotMatch', $messages);
        $this->assertContains('letters and numbers', $messages['regexNotMatch']);

        $this->assertArrayHasKey('stringLengthTooShort', $messages);
        $this->assertContains('less than 3 characters', $messages['stringLengthTooShort']);

        $this->useGerman();
        $name->isValid('');
        $messages = $name->getMessages();

        $this->assertArrayHasKey('regexNotMatch', $messages);
        $this->assertContains('Buchstaben und Zahlen', $messages['regexNotMatch']);

        $this->assertArrayHasKey('stringLengthTooShort', $messages);
        $this->assertContains('weniger als 3 Zeichen', $messages['stringLengthTooShort']);
    }
}
