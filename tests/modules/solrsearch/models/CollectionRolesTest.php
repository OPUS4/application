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

use Opus\Common\CollectionRole;

class Solrsearch_Model_CollectionRolesTest extends ControllerTestCase
{
    /** @var string[] */
    protected $additionalResources = ['database'];

    public function testGetAllVisible()
    {
        $collectionRoles = new Solrsearch_Model_CollectionRoles();
        $visibleRoles    = $collectionRoles->getAllVisible();
        $this->assertEquals(14, count($visibleRoles));
    }

    public function testHasVisibleChildrenForEmptyCollectionRole()
    {
        $collectionRoles = new Solrsearch_Model_CollectionRoles();

        $class  = new ReflectionClass('Solrsearch_Model_CollectionRoles');
        $method = $class->getMethod('hasVisibleChildren');
        $method->setAccessible(true);
        $hasChildren = $method->invokeArgs($collectionRoles, [CollectionRole::get(17)]);

        $this->assertFalse($hasChildren);
    }

    public function testHasVisibleChildrenForNonEmptyCollectionRole()
    {
        $collectionRoles = new Solrsearch_Model_CollectionRoles();

        $class  = new ReflectionClass('Solrsearch_Model_CollectionRoles');
        $method = $class->getMethod('hasVisibleChildren');
        $method->setAccessible(true);
        $hasChildren = $method->invokeArgs($collectionRoles, [CollectionRole::get(7)]);

        $this->assertTrue($hasChildren);
    }

    public function testHasPublishedDocsForEmptyCollectionRole()
    {
        $collectionRoles = new Solrsearch_Model_CollectionRoles();

        $class  = new ReflectionClass('Solrsearch_Model_CollectionRoles');
        $method = $class->getMethod('hasPublishedDocs');
        $method->setAccessible(true);
        $hasChildren = $method->invokeArgs($collectionRoles, [CollectionRole::get(17)]);

        $this->assertFalse($hasChildren);
    }

    public function testHasPublishedDocsForNonEmptyCollectionRoleWithoutPublishedDocs()
    {
        $collectionRoles = new Solrsearch_Model_CollectionRoles();

        $class  = new ReflectionClass('Solrsearch_Model_CollectionRoles');
        $method = $class->getMethod('hasPublishedDocs');
        $method->setAccessible(true);
        $hasChildren = $method->invokeArgs($collectionRoles, [CollectionRole::get(7)]);

        $this->assertFalse($hasChildren);
    }

    public function testHasPublishedDocsForNonEmptyCollectionRoleWithPublishedDocs()
    {
        $collectionRoles = new Solrsearch_Model_CollectionRoles();

        $class  = new ReflectionClass('Solrsearch_Model_CollectionRoles');
        $method = $class->getMethod('hasPublishedDocs');
        $method->setAccessible(true);
        $hasChildren = $method->invokeArgs($collectionRoles, [CollectionRole::get(5)]);

        $this->assertTrue($hasChildren);
    }
}
