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
 * @copyright   Copyright (c) 2017, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

class Application_Security_HttpAuthResolverTest extends ControllerTestCase
{
    /** @var string */
    protected $additionalResources = 'database';

    public function setUp(): void
    {
        parent::setUp();
        $this->makeConfigurationModifiable();
    }

    public function testResolve()
    {
        $this->enableSecurity();

        $resolver = new Application_Security_HttpAuthResolver();

        $this->assertEquals(sha1('sworduserpwd'), $resolver->resolve('sworduser', 'opus-sword'));
    }

    public function testResolveAdmin()
    {
        $this->enableSecurity();

        $resolver = new Application_Security_HttpAuthResolver();

        $this->assertFalse($resolver->resolve('admin', 'opus-sword'));
    }

    public function testResolveUnknownUser()
    {
        $this->enableSecurity();

        $resolver = new Application_Security_HttpAuthResolver();

        $this->assertFalse($resolver->resolve('unknown', 'opus-sword'));
        $this->assertInternalType('bool', $resolver->resolve('unknown', 'opus-sword'));
    }

    public function testResolveUserWithoutAccessToModule()
    {
        $this->enableSecurity();

        $resolver = new Application_Security_HttpAuthResolver();

        $this->assertFalse($resolver->resolve('security8', 'opus-sword'));
    }
}
