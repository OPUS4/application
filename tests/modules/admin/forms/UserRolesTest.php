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
 * @copyright   Copyright (c) 2019, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\Account;
use Opus\Common\UserRole;

class Admin_Form_UserRolesTest extends ControllerTestCase
{
    /** @var string[] */
    protected $additionalResources = ['database'];

    public function testConstruct()
    {
        $form = new Admin_Form_UserRoles();

        $roles = UserRole::getAll();

        $elements = $form->getElements();

        $this->assertSameSize($roles, $elements);
    }

    public function testPopulateFromModel()
    {
        $account = Account::fetchAccountByLogin('sworduser');

        $form = new Admin_Form_UserRoles();

        $form->populateFromModel($account);

        $elements = $form->getElements();

        foreach ($elements as $element) {
            if (in_array($element->getName(), ['sworduser', 'guest'])) {
                $this->assertEquals(1, $element->getValue());
            } else {
                $this->assertEquals(0, $element->getValue(), $element->getName());
            }
        }
    }

    public function testClearAll()
    {
        $form = new Admin_Form_UserRoles();

        foreach ($form->getElements() as $element) {
            $element->setValue(1);
        }

        foreach ($form->getElements() as $element) {
            $this->assertEquals(1, $element->getValue());
        }

        $form->clearAll();

        foreach ($form->getElements() as $element) {
            if (in_array($element->getName(), ['guest'])) {
                $this->assertEquals(1, $element->getValue());
            } else {
                $this->assertEquals(0, $element->getValue(), $element->getName());
            }
        }
    }

    public function testUpdateModel()
    {
        $form = new Admin_Form_UserRoles();

        $form->clearAll();
        $form->getElement('administrator')->setValue(1);
        $form->getElement('sworduser')->setValue(1);

        $account = Account::new();

        $form->updateModel($account);

        $roles = $account->getRole();

        $this->assertInternalType('array', $roles);
        $this->assertCount(3, $roles);

        foreach ($roles as $role) {
            $this->assertContains($role->getName(), ['sworduser', 'administrator', 'guest']);
        }
    }

    public function testGetSelectedRoles()
    {
        $form = new Admin_Form_UserRoles();

        $form->clearAll();
        $form->getElement('administrator')->setValue(1);
        $form->getElement('sworduser')->setValue(1);
        $form->getElement('docsadmin')->setValue(1);

        $selected = $form->getSelectedRoles();

        $this->assertCount(4, $selected);
        $this->assertContains('administrator', $selected);
        $this->assertContains('sworduser', $selected);
        $this->assertContains('docsadmin', $selected);
        $this->assertContains('guest', $selected);
    }

    public function testPopulate()
    {
        $form = new Admin_Form_UserRoles();

        $form->clearAll();

        $form->populate([
            'administrator' => 1,
            'sworduser'     => 1,
            'docsadmin'     => 0,
        ]);

        $selected = $form->getSelectedRoles();

        $this->assertCount(3, $selected);
        $this->assertContains('administrator', $selected);
        $this->assertContains('sworduser', $selected);
        $this->assertContains('guest', $selected);
    }
}
