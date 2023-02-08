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

class Application_Form_Validate_CollectionRoleNameUniqueTest extends ControllerTestCase
{
    /** @var string */
    protected $additionalResources = 'database';

    /** @var Application_Form_Validate_CollectionRoleNameUnique */
    private $validator;

    public function setUp(): void
    {
        parent::setUp();

        $this->validator = new Application_Form_Validate_CollectionRoleNameUnique();
    }

    public function testIsValidTrue()
    {
        $this->assertTrue($this->validator->isValid('newTestColRole'));
        $this->assertTrue($this->validator->isValid('newTestColRole', []));
        $this->assertTrue($this->validator->isValid('newTestColRole', ['Id' => 1]));
    }

    public function testIsValidTrueForUpdate()
    {
        $this->assertTrue($this->validator->isValid('ddc', ['Id' => 2]));
    }

    public function testIsValidFalse()
    {
        $this->assertFalse($this->validator->isValid('ddc'));
        $this->assertFalse($this->validator->isValid('ddc', []));
        $this->assertFalse($this->validator->isValid('ddc', ['Id' => 1]));
    }
}
