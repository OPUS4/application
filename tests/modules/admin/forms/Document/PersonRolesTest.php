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
 * @copyright   Copyright (c) 2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

class Admin_Form_Document_PersonRolesTest extends ControllerTestCase
{
    /** @var string[] */
    private $roles;

    public function setUp(): void
    {
        parent::setUp();

        $this->roles = [
            'author'      => 'author',
            'editor'      => 'editor',
            'translator'  => 'translator',
            'contributor' => 'contributor',
            'other'       => 'other',
            'advisor'     => 'advisor',
            'referee'     => 'referee',
            'submitter'   => 'submitter',
        ];
    }

    public function testConstructForm()
    {
        $form = new Admin_Form_Document_PersonRoles();

        $this->assertEquals(8, count($form->getElements()));

        foreach ($this->roles as $role) {
            $elementName = 'Role' . ucfirst($role);
            $this->assertNotNull(
                $form->getElement($elementName),
                "Element '$elementName' wurde nicht generiert."
            );
        }
    }

    public function testConstructFormForRoles()
    {
        foreach ($this->roles as $role) {
            $activeRoles = $this->roles;
            unset($activeRoles[$role]);

            $form = new Admin_Form_Document_PersonRoles($role);

            $this->assertEquals(7, count($form->getElements()));

            $this->assertNull($form->getElement('Role' . ucfirst($role)));

            foreach ($activeRoles as $activeRole) {
                $elementName = 'Role' . ucfirst($activeRole);
                $this->assertNotNull(
                    $form->getElement($elementName),
                    "Für Rolle '$role' wurde Element '$elementName' nicht generiert."
                );
            }
        }
    }

    public function testProcessPost()
    {
        $form = new Admin_Form_Document_PersonRoles();

        $post = [
            'RoleContributor' => 'Beitragende Person',
        ];

        $result = $form->processPost($post, null);

        $this->assertNotNull($result);
        $this->assertArrayHasKey('result', $result);
        $this->assertEquals(Admin_Form_Document_PersonRoles::RESULT_CHANGE_ROLE, $result['result']);
        $this->assertArrayHasKey('role', $result);
        $this->assertEquals('contributor', $result['role']);
    }

    public function testProcessPostEmpty()
    {
        $form = new Admin_Form_Document_PersonRoles();

        $this->assertNull($form->processPost([], null));
    }

    public function testGetRoleElementName()
    {
        $form = new Admin_Form_Document_PersonRoles();

        $this->assertEquals('RoleAuthor', $form->getRoleElementName('author'));
        $this->assertEquals('RoleEditor', $form->getRoleElementName('Editor'));
        $this->assertEquals('Role', $form->getRoleElementName(null)); // nutzlos, aber keine Exception
    }
}
