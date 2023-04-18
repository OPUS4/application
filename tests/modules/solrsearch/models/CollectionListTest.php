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

use Opus\Common\CollectionInterface;
use Opus\Common\CollectionRole;
use Opus\Common\CollectionRoleInterface;
use Opus\Common\Model\ModelException;
use Opus\Common\Model\NotFoundException;

class Solrsearch_Model_CollectionListTest extends ControllerTestCase
{
    /** @var string[] */
    protected $additionalResources = ['database'];

    public function testMissingCollectionId()
    {
        $this->expectException(Solrsearch_Model_Exception::class);
        new Solrsearch_Model_CollectionList(null);
    }

    public function testInvalidCollectionId()
    {
        $this->expectException(Solrsearch_Model_Exception::class);
        new Solrsearch_Model_CollectionList('');
    }

    public function testInvisbleCollection()
    {
        $collection = $this->getFirstNonRootCollection(1);

        $collection->setVisible(false);
        $collection->store();
        try {
            new Solrsearch_Model_CollectionList($collection->getId());
        } catch (Solrsearch_Model_Exception $e) {
            $collection->setVisible(true);
            $collection->store();
            return;
        }
        $this->fail('Expected exception Solrsearch_Model_Exception was not raised.');
    }

    public function testInvisibleCollectionRole()
    {
        $collectionRole = $this->getCollectionRole(1);
        $rootCollection = $this->getRootCollection($collectionRole->getId());

        $collectionRole->setVisible(false);
        $collectionRole->store();
        try {
            new Solrsearch_Model_CollectionList($rootCollection->getId());
        } catch (Solrsearch_Model_Exception $e) {
            $collectionRole->setVisible(true);
            $collectionRole->store();
            return;
        }
        $this->fail('Expected exception Solrsearch_Model_Exception was not raised.');
    }

    public function testInvisibleBrowsingCollectionRole()
    {
        $collectionRole = $this->getCollectionRole(1);
        $rootCollection = $this->getRootCollection($collectionRole->getId());

        $collectionRole->setVisibleBrowsingStart(false);
        $collectionRole->store();
        try {
            new Solrsearch_Model_CollectionList($rootCollection->getId());
        } catch (Solrsearch_Model_Exception $e) {
            $collectionRole->setVisibleBrowsingStart(true);
            $collectionRole->store();
            return;
        }
        $this->fail('Expected exception Solrsearch_Model_Exception was not raised.');
    }

    public function testIsRootCollection()
    {
        $rootCollection = $this->getRootCollection(1);

        $collectionList = new Solrsearch_Model_CollectionList($rootCollection->getId());
        $this->assertTrue($collectionList->isRootCollection());
    }

    public function testIsNotRootCollection()
    {
        $rootCollection = $this->getRootCollection(1);

        $this->assertGreaterThan(0, count($rootCollection->getChildren()));
        foreach ($rootCollection->getChildren() as $childCollection) {
            if ($childCollection->getVisible()) {
                $collectionList = new Solrsearch_Model_CollectionList($childCollection->getId());
                $this->assertFalse($collectionList->isRootCollection());
            }
        }
    }

    public function testGetParentsOfRootCollection()
    {
        $rootCollection = $this->getRootCollection(1);

        $collectionList = new Solrsearch_Model_CollectionList($rootCollection->getId());
        $this->assertEquals(0, count($collectionList->getParents()));
    }

    public function testGetParentsOfChildOfRootCollection()
    {
        $rootCollection   = $this->getRootCollection(1);
        $childCollections = $rootCollection->getChildren();

        foreach ($childCollections as $childCollection) {
            if ($childCollection->getVisible()) {
                $collectionList = new Solrsearch_Model_CollectionList($childCollection->getId());
                $parents        = $collectionList->getParents();
                $this->assertEquals(1, count($parents));
                $this->assertEquals($rootCollection->getId(), $parents[0]->getId());
                $this->assertEquals($rootCollection->getRole()->getId(), $parents[0]->getRole()->getId());
            }
        }
    }

    public function testGetParentsOfGrandchildOfRootCollection()
    {
        $rootCollection   = $this->getRootCollection(1);
        $childCollections = $rootCollection->getChildren();

        foreach ($childCollections as $childCollection) {
            if ($childCollection->getVisible()) {
                $grandchildCollections = $childCollection->getChildren();
                foreach ($grandchildCollections as $grandchildCollection) {
                    if ($grandchildCollection->getVisible()) {
                        $collectionList = new Solrsearch_Model_CollectionList($grandchildCollection->getId());
                        $parents        = $collectionList->getParents();
                        $this->assertEquals(2, count($parents));
                        $this->assertEquals($rootCollection->getId(), $parents[0]->getId());
                        $this->assertEquals($rootCollection->getRole()->getId(), $parents[0]->getRole()->getId());
                        $this->assertEquals($childCollection->getId(), $parents[1]->getId());
                        $this->assertEquals($childCollection->getRole()->getId(), $parents[1]->getRole()->getId());
                    }
                }
            }
        }
    }

    public function testGetChildren()
    {
        $rootCollection = $this->getRootCollection(1);

        $collectionList = new Solrsearch_Model_CollectionList($rootCollection->getId());
        $children       = $collectionList->getChildren();

        $childrenPointer = 0;
        foreach ($rootCollection->getChildren() as $childCollection) {
            if ($childCollection->getVisible()) {
                $this->assertEquals($children[$childrenPointer], $childCollection);
                $childrenPointer++;
            }
        }
    }

    public function testTitleRootCollection()
    {
        $rootCollection = $this->getRootCollection(1);
        $collectionList = new Solrsearch_Model_CollectionList($rootCollection->getId());
        $collectionList->getTitle();
    }

    public function testTitleNonRootCollection()
    {
        $rootCollection = $this->getRootCollection(1);
        foreach ($rootCollection->getChildren() as $childCollection) {
            if ($childCollection->getVisible()) {
                $collectionList = new Solrsearch_Model_CollectionList($childCollection->getId());
                $collectionList->getTitle();
                return;
            }
        }
    }

    public function testTheme()
    {
        $rootCollection = $this->getRootCollection(1);
        $collectionList = new Solrsearch_Model_CollectionList($rootCollection->getId());
        $collectionList->getTheme();
    }

    public function testCollectionId()
    {
        $rootCollection = $this->getRootCollection(1);
        $collectionList = new Solrsearch_Model_CollectionList($rootCollection->getId());
        $collectionList->getCollectionId();
    }

    public function testCollectionRoleTitle()
    {
        $rootCollection = $this->getRootCollection(1);
        $collectionList = new Solrsearch_Model_CollectionList($rootCollection->getId());
        $collectionList->getCollectionRoleTitle();
    }

    /**
     * Teste das Ausblenden von leeren Sammlungen am Beispiel der MSC.
     *
     * @throws ModelException
     * @throws Solrsearch_Model_Exception
     */
    public function testGetChildrenWithoutEmptyCollections()
    {
        $collRole             = CollectionRole::fetchByName('msc');
        $hideEmptyCollections = $collRole->getHideEmptyCollections();
        $collRole->setHideEmptyCollections(1);
        $collRole->store();

        $collList = new Solrsearch_Model_CollectionList($collRole->getRootCollection()->getId());
        $this->assertCount(2, $collList->getChildren());

        $collRole->setHideEmptyCollections(0);
        $collRole->store();

        $collList = new Solrsearch_Model_CollectionList($collRole->getRootCollection()->getId());
        $this->assertTrue(count($collList->getChildren()) > 2);

        // ursprünglichen Wert wiederherstellen
        $collRole->setHideEmptyCollections($hideEmptyCollections);
        $collRole->store();
    }

    /**
     * @param int $collectionRoleId
     * @return CollectionRoleInterface
     * @throws NotFoundException
     */
    private function getCollectionRole($collectionRoleId)
    {
        $collectionRole = CollectionRole::get($collectionRoleId);
        $this->assertNotNull($collectionRole);
        $this->assertEquals('1', $collectionRole->getVisible());
        $this->assertEquals('1', $collectionRole->getVisibleBrowsingStart());
        return $collectionRole;
    }

    /**
     * @param int $collectionRoleId
     * @return CollectionInterface
     */
    private function getRootCollection($collectionRoleId)
    {
        $rootCollection = $this->getCollectionRole($collectionRoleId)->getRootCollection();
        $this->assertNotNull($rootCollection);
        $this->assertEquals('1', $rootCollection->getVisible());
        return $rootCollection;
    }

    /**
     * @param int $collectionRoleId
     * @return null|CollectionInterface
     */
    private function getFirstNonRootCollection($collectionRoleId)
    {
        $rootCollection = $this->getRootCollection($collectionRoleId);
        $children       = $rootCollection->getChildren();
        if (count($children) === 0) {
            return null;
        }
        return $children[0];
    }
}
