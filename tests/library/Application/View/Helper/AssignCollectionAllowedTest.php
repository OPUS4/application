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
 * @category    Application Tests
 * @package     View_Helper
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2017, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

class Application_View_Helper_AssignCollectionAllowedTest extends ControllerTestCase
{

    private $_helper;

    private $_role;

    public function setUp()
    {
        parent::setUp();

        $this->_helper = new Application_View_Helper_AssignCollectionAllowed();

        $role = new Opus_CollectionRole();
        $role->setName('Test CollectionRole');
        $role->setOaiName('test');
        $role->setDisplayBrowsing('Number');
        $role->setDisplayFrontdoor('Name');
        $role->store();

        $this->_role = $role;
    }

    public function tearDown()
    {
        $this->_role->delete();

        parent::tearDown();
    }

    public function testRootCollectionNotAllowed()
    {
        $this->assertFalse($this->_helper->assignCollectionAllowed(array(
            'isRoot' => true,
            'role' => $this->_role
        )));
    }

    public function testRootCollectionAllowed()
    {
        $this->_role->setAssignRoot(1);
        $this->_role->setAssignLeavesOnly(0);
        $this->_role->store();

        $this->assertTrue($this->_helper->assignCollectionAllowed(array(
            'isRoot' => true,
            'role' => $this->_role
        )));
    }

    public function testNonLeafCollectionNotAllowed()
    {
        $this->_role->setAssignLeavesOnly(1);
        $this->_role->store();

        $this->assertFalse($this->_helper->assignCollectionAllowed(array(
            'isLeaf' => false,
            'role' => $this->_role
        )));
    }

    public function testNonLeafCollectionAllowed()
    {
        $this->_role->setAssignLeavesOnly(0);
        $this->_role->store();

        $this->assertTrue($this->_helper->assignCollectionAllowed(array(
            'isLeaf' => false,
            'role' => $this->_role
        )));
    }

    public function testLeafCollectionAllowed()
    {
        $this->_role->setAssignLeavesOnly(1);
        $this->_role->store();

        $this->assertTrue($this->_helper->assignCollectionAllowed(array(
            'isLeaf' => true,
            'role' => $this->_role
        )));

        $this->_role->setAssignLeavesOnly(0);
        $this->_role->store();

        $this->assertTrue($this->_helper->assignCollectionAllowed(array(
            'isLeaf' => true,
            'role' => $this->_role
        )));
    }

    public function testRootNotAllowedIfNotLeafAndLeavesOnly()
    {
        $this->_role->setAssignRoot(1);
        $this->_role->setAssignLeavesOnly(1);
        $this->_role->store();

        $this->assertFalse($this->_helper->assignCollectionAllowed(array(
            'isRoot' => true,
            'isLeaf' => false,
            'role' => $this->_role
        )));
    }

    public function testRootAllowedIfLeaf()
    {
        $this->_role->setAssignRoot(1);
        $this->_role->setAssignLeavesOnly(1);
        $this->_role->store();

        $this->assertTrue($this->_helper->assignCollectionAllowed(array(
            'isRoot' => true,
            'isLeaf' => true,
            'role' => $this->_role
        )));
    }

    /**
     * TODO this method should be eliminated - one should be sufficient
     */
    public function testNotAllowedIfAlreadyAssigned()
    {
        $this->_role->setAssignRoot(1);
        $this->_role->setAssignLeavesOnly(0);
        $root = $this->_role->addRootCollection();
        $this->_role->store();

        $document = $this->createTestDocument();
        $docId = $document->store();

        $this->assertTrue($this->_helper->assignCollectionAllowed(array(
            'role' => $this->_role,
            'collection' => $root
        ), $docId));

        $document->addCollection($root);
        $docId = $document->store();

        $this->assertFalse($this->_helper->assignCollectionAllowed(array(
            'role' => $this->_role,
            'collection' => $root
        ), $docId));
    }

    public function testNotAllowedIfAlreadyAssignedUseAssignedOption()
    {
        $this->_role->setAssignRoot(1);
        $this->_role->setAssignLeavesOnly(0);
        $root = $this->_role->addRootCollection();
        $this->_role->store();

        $document = $this->createTestDocument();
        $docId = $document->store();

        $this->assertTrue($this->_helper->assignCollectionAllowed(array(
            'assigned' => false
        ), $docId));

        $document->addCollection($root);
        $docId = $document->store();

        $this->assertFalse($this->_helper->assignCollectionAllowed(array(
            'assigned' => true
        ), $docId));
    }

}
