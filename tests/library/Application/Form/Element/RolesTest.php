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

/**
 * Unit Tests von Formularelement fuer die Auswahl von Rollen.
 */
class Application_Form_Element_RolesTest extends FormElementTestCase
{
    /** @var string */
    protected $additionalResources = 'database';

    public function setUp(): void
    {
        $this->formElementClass       = 'Application_Form_Element_Roles';
        $this->expectedDecoratorCount = 4;
        $this->expectedDecorators     = ['ViewHelper', 'ElementHtmlTag', 'LabelNotEmpty', 'dataWrapper'];
        $this->staticViewHelper       = 'viewFormMultiCheckbox';
        parent::setUp();
    }

    public function testGetRolesMultiOptions()
    {
        $element = new Application_Form_Element_Roles('Roles');

        $options = $element->getRolesMultiOptions();

        $this->assertEquals(count(UserRole::getAll()), count($options));

        foreach ($options as $value => $label) {
            $this->assertEquals($value, $label);
        }
    }

    public function testSetValue()
    {
        $element = new Application_Form_Element_Roles('Roles');

        $element->setValue(['guest', 'reviewer', 'sworduser']);

        $this->assertEquals(['guest', 'reviewer', 'sworduser'], $element->getValue());
    }

    public function testSetValueWithRoles()
    {
        $element = new Application_Form_Element_Roles('Roles');

        $element->setValue([
            UserRole::fetchByName('docsadmin'),
            UserRole::fetchByName('reviewer'),
        ]);

        $this->assertEquals(['docsadmin', 'reviewer'], $element->getValue());
    }

    public function testGetRoles()
    {
        $element = new Application_Form_Element_Roles('Roles');

        $element->setValue(['reviewer', 'docsadmin', 'sworduser']);

        $this->assertEquals(['reviewer', 'docsadmin', 'sworduser'], $element->getValue());

        $roles = $element->getRoles();

        $expectedRoles = ['reviewer', 'docsadmin', 'sworduser'];

        $this->assertCount(count($expectedRoles), $roles);

        foreach ($roles as $role) {
            $this->assertInstanceOf(Opus\UserRole::class, $role);
            $this->assertContains($role->getName(), $expectedRoles);

            // removed already checked roles from expectation
            $expectedRoles = array_diff($expectedRoles, [$role->getName()]);
        }

        $this->assertCount(0, $expectedRoles);
    }

    public function testGetRolesForNull()
    {
        $element = new Application_Form_Element_Roles('Roles');

        $roles = $element->getRoles();

        $this->assertNotNull($roles);
        $this->assertInternalType('array', $roles);
        $this->assertEmpty($roles);
    }

    public function testIsValid()
    {
        $element = new Application_Form_Element_Roles('Roles');

        $this->assertTrue($element->isValid(null));
        $this->assertTrue($element->isValid([]));
        $this->assertTrue($element->isValid(['reviewer', 'docsadmin']));

        $this->assertFalse($element->isValid(['unknown', 'docsadmin']));
    }

    public function testGetValueNull()
    {
        $element = new Application_Form_Element_Roles('Roles');

        $element->setValue(null);

        $this->assertNull($element->getValue());
    }
}
