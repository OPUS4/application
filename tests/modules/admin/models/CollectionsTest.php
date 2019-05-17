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
 * @category    Tests
 * @package     Admin_Model
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2018, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */
class Admin_Model_CollectionsTest extends ControllerTestCase
{

    private $collectionRoleId;

    private $model;

    private $_docId;

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
        $root = $collectionRole->addRootCollection();

        $this->collectionRoleId = $collectionRole->store();

        $this->model = new Admin_Model_Collections();
        $this->model->setView(Zend_Registry::get('Opus_View'));

        $document = $this->createTestDocument();
        $document->addCollection($root);
        $this->_docId = $document->store();
    }

    public function tearDown()
    {
        $collectionRole = new Opus_CollectionRole($this->collectionRoleId);
        $collectionRole->delete();

        parent::tearDown();
    }

    /**
     * Checks that visible = 1 for visible collection role.
     */
    public function testGetCollectionRoleInfo()
    {
        $collections = $this->model->getCollectionRolesInfo();

        $this->assertNotNull($collections);
        $this->assertInternalType('array', $collections);

        foreach ($collections as $collection) {
            $this->assertInternalType('array', $collection);
            $this->assertCount(8, $collection);
            $this->assertArrayHasKey('id', $collection);
            $this->assertArrayHasKey('name', $collection);
            $this->assertArrayHasKey('hasChildren', $collection);
            $this->assertArrayHasKey('visible', $collection);
            if (strcmp($collection['name'], 'default_collection_role_TestCollectionRole-Name') == 0) {
                $this->assertEquals(1, $collection['visible']);
            }
            $this->assertArrayHasKey('isRoot', $collection);
            $this->assertArrayHasKey('role', $collection);
            $this->assertInstanceOf('Opus_CollectionRole', $collection['role']);
            $this->assertArrayHasKey('collection', $collection);
            $this->assertInstanceOf('Opus_Collection', $collection['collection']);
            $this->assertArrayHasKey('assigned', $collection);
            $this->assertFalse($collection['assigned']);
        }
    }

    /**
     * Checks that hidden collection role has visible = 0.
     */
    public function testRoleInvisible()
    {
        $collectionRole = Opus_CollectionRole::fetchByName('TestCollectionRole-Name');
        $collectionRole->setVisible(0);
        $collectionRole->store();

        $collections = $this->model->getCollectionRolesInfo();

        $this->assertNotNull($collections);
        $this->assertInternalType('array', $collections);

        foreach ($collections as $collection) {
            $this->assertInternalType('array', $collection);
            $this->assertCount(8, $collection);
            $this->assertArrayHasKey('id', $collection);
            $this->assertArrayHasKey('name', $collection);
            $this->assertArrayHasKey('hasChildren', $collection);
            $this->assertArrayHasKey('visible', $collection);
            if (strcmp($collection['name'], 'default_collection_role_TestCollectionRole-Name') === 0) {
                $this->assertEquals(0, $collection['visible']);
            }
            $this->assertArrayHasKey('isRoot', $collection);
            $this->assertArrayHasKey('role', $collection);
            $this->assertInstanceOf('Opus_CollectionRole', $collection['role']);
            $this->assertArrayHasKey('collection', $collection);
            $this->assertInstanceOf('Opus_Collection', $collection['collection']);
            $this->assertArrayHasKey('assigned', $collection);
            $this->assertFalse($collection['assigned']);
        }
    }

    public function testCollectionRoleWithoutRootNotIncluded()
    {
        $collections = $this->model->getCollectionRolesInfo();

        $this->assertNotNull($collections);
        $this->assertInternalType('array', $collections);

        foreach ($collections as $collection) {
            $this->assertInternalType('array', $collection);
            $this->assertCount(8, $collection);
            $this->assertArrayHasKey('id', $collection);
            $this->assertArrayHasKey('name', $collection);
            $this->assertArrayHasKey('hasChildren', $collection);
            $this->assertArrayHasKey('visible', $collection);
            if (strcmp($collection['name'], 'default_collection_role_no-root-test') == 0) {
                $this->fail('Collection role no-root-test should not be present in array.');
            }
            $this->assertArrayHasKey('isRoot', $collection);
            $this->assertArrayHasKey('role', $collection);
            $this->assertInstanceOf('Opus_CollectionRole', $collection['role']);
            $this->assertArrayHasKey('collection', $collection);
            $this->assertInstanceOf('Opus_Collection', $collection['collection']);
            $this->assertArrayHasKey('assigned', $collection);
            $this->assertFalse($collection['assigned']);
        }
    }

    public function testCollectionRolesInfoForAssigned()
    {
        $collections = $this->model->getCollectionRolesInfo($this->_docId);

        $this->assertNotNull($collections);
        $this->assertInternalType('array', $collections);

        foreach ($collections as $collection)
        {
            $this->assertArrayHasKey('assigned', $collection);

            if (strcmp($collection['name'], 'default_collection_role_TestCollectionRole-Name') === 0)
            {
                $this->assertTrue($collection['assigned']);
            }
            else {
                $this->assertFalse($collection['assigned']);
            }
        }
    }
}
