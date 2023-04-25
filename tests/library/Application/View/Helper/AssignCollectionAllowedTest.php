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

use Opus\Common\CollectionRole;
use Opus\Common\CollectionRoleInterface;

class Application_View_Helper_AssignCollectionAllowedTest extends ControllerTestCase
{
    /** @var string */
    protected $additionalResources = 'database';

    /** @var Application_View_Helper_AssignCollectionAllowed */
    private $helper;

    /** @var CollectionRoleInterface */
    private $role;

    public function setUp(): void
    {
        parent::setUp();

        $this->helper = new Application_View_Helper_AssignCollectionAllowed();

        $role = CollectionRole::new();
        $role->setName('TestCollectionRole');
        $role->setOaiName('test');
        $role->setDisplayBrowsing('Number');
        $role->setDisplayFrontdoor('Name');
        $role->store();

        $this->role = $role;
    }

    public function tearDown(): void
    {
        $this->role->delete();

        parent::tearDown();
    }

    public function testRootCollectionNotAllowed()
    {
        $this->assertFalse($this->helper->assignCollectionAllowed([
            'isRoot' => true,
            'role'   => $this->role,
        ]));
    }

    public function testRootCollectionAllowed()
    {
        $this->role->setAssignRoot(1);
        $this->role->setAssignLeavesOnly(0);
        $this->role->store();

        $this->assertTrue($this->helper->assignCollectionAllowed([
            'isRoot' => true,
            'role'   => $this->role,
        ]));
    }

    public function testNonLeafCollectionNotAllowed()
    {
        $this->role->setAssignLeavesOnly(1);
        $this->role->store();

        $this->assertFalse($this->helper->assignCollectionAllowed([
            'isLeaf' => false,
            'role'   => $this->role,
        ]));
    }

    public function testNonLeafCollectionAllowed()
    {
        $this->role->setAssignLeavesOnly(0);
        $this->role->store();

        $this->assertTrue($this->helper->assignCollectionAllowed([
            'isLeaf' => false,
            'role'   => $this->role,
        ]));
    }

    public function testLeafCollectionAllowed()
    {
        $this->role->setAssignLeavesOnly(1);
        $this->role->store();

        $this->assertTrue($this->helper->assignCollectionAllowed([
            'isLeaf' => true,
            'role'   => $this->role,
        ]));

        $this->role->setAssignLeavesOnly(0);
        $this->role->store();

        $this->assertTrue($this->helper->assignCollectionAllowed([
            'isLeaf' => true,
            'role'   => $this->role,
        ]));
    }

    public function testRootNotAllowedIfNotLeafAndLeavesOnly()
    {
        $this->role->setAssignRoot(1);
        $this->role->setAssignLeavesOnly(1);
        $this->role->store();

        $this->assertFalse($this->helper->assignCollectionAllowed([
            'isRoot' => true,
            'isLeaf' => false,
            'role'   => $this->role,
        ]));
    }

    public function testRootAllowedIfLeaf()
    {
        $this->role->setAssignRoot(1);
        $this->role->setAssignLeavesOnly(1);
        $this->role->store();

        $this->assertTrue($this->helper->assignCollectionAllowed([
            'isRoot' => true,
            'isLeaf' => true,
            'role'   => $this->role,
        ]));
    }

    /**
     * TODO this method should be eliminated - one should be sufficient
     */
    public function testNotAllowedIfAlreadyAssigned()
    {
        $this->role->setAssignRoot(1);
        $this->role->setAssignLeavesOnly(0);
        $root = $this->role->addRootCollection();
        $this->role->store();

        $document = $this->createTestDocument();
        $docId    = $document->store();

        $this->assertTrue($this->helper->assignCollectionAllowed([
            'role'       => $this->role,
            'collection' => $root,
        ], $docId));

        $document->addCollection($root);
        $docId = $document->store();

        $this->assertFalse($this->helper->assignCollectionAllowed([
            'role'       => $this->role,
            'collection' => $root,
        ], $docId));
    }

    public function testNotAllowedIfAlreadyAssignedUseAssignedOption()
    {
        $this->role->setAssignRoot(1);
        $this->role->setAssignLeavesOnly(0);
        $root = $this->role->addRootCollection();
        $this->role->store();

        $document = $this->createTestDocument();
        $docId    = $document->store();

        $this->assertTrue($this->helper->assignCollectionAllowed([
            'assigned' => false,
        ], $docId));

        $document->addCollection($root);
        $docId = $document->store();

        $this->assertFalse($this->helper->assignCollectionAllowed([
            'assigned' => true,
        ], $docId));
    }
}
