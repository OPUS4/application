<?php
/*
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
 * @category    Application Unit Tests
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2018, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */
class Admin_Model_CollectionRoleTest extends ControllerTestCase
{

    private $collectionRoleId;

    private $moveTestColId = null;

    public function setUp()
    {
        parent::setUp();

        $collectionRole = new Opus_CollectionRole();
        $collectionRole->setName('TestCollectionRole-Name');
        $collectionRole->setOaiName('TestCollectionRole-OaiName');
        $collectionRole->setVisible(1);
        $collectionRole->setVisibleBrowsingStart(1);
        $collectionRole->setVisibleFrontdoor(1);
        $collectionRole->setVisibleOai(1);
        $collectionRole->setDisplayBrowsing('Number');
        $collectionRole->setDisplayFrontdoor('Name');
        $collectionRole->setPosition(99);

        $this->collectionRoleId = $collectionRole->store();
    }

    public function tearDown()
    {
        $collectionRole = new Opus_CollectionRole($this->collectionRoleId);
        $collectionRole->delete();

        if (!is_null($this->moveTestColId)) {
            $collectionRole = new Opus_CollectionRole($this->moveTestColId);
            $collectionRole->delete();
        }

        parent::tearDown();
    }

    public function testConstructModel()
    {
        $model = new Admin_Model_CollectionRole($this->collectionRoleId);

        $collectionRole = $model->getObject();

        $this->assertEquals($this->collectionRoleId, $collectionRole->getId());
    }

    public function testConstructModelWithNull()
    {
        $model = new Admin_Model_CollectionRole();
        $collectionRole = $model->getObject();
        $this->assertEquals(1, $collectionRole->getVisible());
        $this->assertEquals(1, $collectionRole->getVisibleBrowsingStart());
        $this->assertEquals(1, $collectionRole->getVisibleFrontdoor());
        $this->assertEquals(1, $collectionRole->getVisibleOai());
    }

    /**
     * @expectedException Admin_Model_Exception
     * @expectedExceptionMessage missing parameter roleid
     */
    public function testConstructModelWithEmptyParameter()
    {
        $model = new Admin_Model_CollectionRole('');
    }

    /**
     * @expectedException Admin_Model_Exception
     * @expectedExceptionMessage roleid parameter value unknown
     */
    public function testConstructModelWithUnknownId()
    {
        $model = new Admin_Model_CollectionRole(2222);
    }

    /**
     * @expectedException Admin_Model_Exception
     * @expectedExceptionMessage roleid parameter value unknown
     */
    public function testContructModelWithBadParameter()
    {
        $model = new Admin_Model_CollectionRole('noId');
    }

    public function testGetObject()
    {
        $model = new Admin_Model_CollectionRole($this->collectionRoleId);

        $collectionRole = $model->getObject();

        $this->assertEquals($this->collectionRoleId, $collectionRole->getId());
    }

    public function testSetVisibilityTrue()
    {
        $model = new Admin_Model_CollectionRole($this->collectionRoleId);

        $collectionRole = $model->getObject();

        $collectionRole->setVisible(0);
        $collectionRole->store();

        $model->setVisibility(true);

        $collectionRole = new Opus_CollectionRole($this->collectionRoleId);

        $this->assertEquals(1, $collectionRole->getVisible());
    }

    public function testSetVisibilityFalse()
    {
        $model = new Admin_Model_CollectionRole($this->collectionRoleId);

        $collectionRole = $model->getObject();

        $this->assertEquals(1, $collectionRole->getVisible());

        $model->setVisibility(false);

        $collectionRole = new Opus_CollectionRole($this->collectionRoleId);

        $this->assertEquals(0, $collectionRole->getVisible());
    }

    public function testMove()
    {
        $colRole = new Opus_CollectionRole();
        $colRole->setName('MoveTestColRole-Name');
        $colRole->setOaiName('MoveTestColRole-OaiName');
        $colRole->setDisplayFrontdoor('Number');
        $colRole->setDisplayBrowsing('Name');
        $colRole->setPosition(100);
        $this->moveTestColId = $colRole->store();

        $colRoles = Opus_CollectionRole::fetchAll();

        $colRolesCount = count($colRoles);

        $this->assertEquals($this->moveTestColId, $colRoles[$colRolesCount - 1]->getId());
        $this->assertEquals($this->collectionRoleId, $colRoles[$colRolesCount - 2]->getId());

        $model = new Admin_Model_CollectionRole($this->collectionRoleId);

        $model->move(100);

        $colRoles = Opus_CollectionRole::fetchAll();

        $colRolesCount = count($colRoles);

        // Reihenfolge ist jetzt vertauscht
        $this->assertEquals($this->collectionRoleId, $colRoles[$colRolesCount - 1]->getId());
        $this->assertEquals($this->moveTestColId, $colRoles[$colRolesCount - 2]->getId());
    }
}
