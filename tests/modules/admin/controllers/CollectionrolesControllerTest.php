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
 * @category    Tests
 * @package     Admin
 * @author      Sascha Szott <szott@zib.de>
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2018, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 *
 * @covers Admin_CollectionrolesController
 */
class Admin_CollectionrolesControllerTest extends ControllerTestCase {

    private $emptyCollectionRole = null;

    private $nonEmptyCollectionRole = null;

    public function setUp() {
        parent::setUp();

        $this->emptyCollectionRole = new Opus_CollectionRole();
        $this->emptyCollectionRole->setName("test1role");
        $this->emptyCollectionRole->setOaiName("test1role");
        $this->emptyCollectionRole->setDisplayBrowsing("Name");
        $this->emptyCollectionRole->setDisplayFrontdoor("Name");
        $this->emptyCollectionRole->setDisplayOai("Name");
        $this->emptyCollectionRole->setPosition(100);
        $this->emptyCollectionRole->store();

        $this->nonEmptyCollectionRole = new Opus_CollectionRole();
        $this->nonEmptyCollectionRole->setName("test2role");
        $this->nonEmptyCollectionRole->setOaiName("test2role");
        $this->nonEmptyCollectionRole->setDisplayBrowsing("Name");
        $this->nonEmptyCollectionRole->setDisplayFrontdoor("Name");
        $this->nonEmptyCollectionRole->setDisplayOai("Name");
        $this->nonEmptyCollectionRole->setPosition(101);
        $this->nonEmptyCollectionRole->store();

        $rootCollection = $this->nonEmptyCollectionRole->addRootCollection();
        $rootCollection->store();
    }

    public function tearDown() {
        if (!is_null($this->nonEmptyCollectionRole) && !is_null($this->nonEmptyCollectionRole->getId())) {
            $this->nonEmptyCollectionRole->delete();
        }
        if (!is_null($this->emptyCollectionRole) && !is_null($this->emptyCollectionRole->getId())) {
            $this->emptyCollectionRole->delete();
        }
        parent::tearDown();
    }

    public function testIndexAction() {
        $this->dispatch('/admin/collectionroles');
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('collectionroles');
        $this->assertAction('index');
    }

    public function testIndexActionInvisibleCssClass() {
        $this->useEnglish();

        $this->dispatch('/admin/collectionroles');
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('collectionroles');
        $this->assertAction('index');

        $this->assertXpathCount('//td[@class="edit"]', 22); // 19 in Testdaten, +2 in setUp
        $this->assertXpathContentContains('//td[@class="hide invisible"]/a', 'Unhide');
        $this->assertXpathCount('//td[@class="hide invisible"]/a', 3); // 3 in Testdaten, +2 in setUp
        $this->assertXpathCount('//th[@class="invisible"]/a', 3); // 3 in Testdaten, +2 in setUp
    }

    public function testEditAction() {
        $this->dispatch('/admin/collectionroles/edit/roleid/' . $this->nonEmptyCollectionRole->getId());
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('collectionroles');
        $this->assertAction('edit');
    }

    public function testDeleteAction() {
        $this->dispatch('/admin/collectionroles/delete/roleid/' . $this->nonEmptyCollectionRole->getId());
        $this->assertRedirect();
        $this->assertResponseLocationHeader($this->getResponse(), '/admin/collectionroles');
    }

    public function testDeleteActionWithMissingParam() {
        $this->dispatch('/admin/collectionroles/delete');
        $this->assertRedirect();
        $this->assertResponseLocationHeader($this->getResponse(), '/admin/collectionroles');
    }

    public function testMoveAction() {
        $this->dispatch('/admin/collectionroles/move/pos/1/roleid/' . $this->emptyCollectionRole->getId());
        $this->assertRedirect();
        $this->assertResponseLocationHeader($this->getResponse(), '/admin/collectionroles');
    }

    public function testMoveActionWithMissingParam() {
        $this->dispatch('/admin/collectionroles/move');
        $this->assertRedirect();
        $this->assertResponseLocationHeader($this->getResponse(), '/admin/collectionroles');
    }

    public function testNewAction() {
        $this->dispatch('/admin/collectionroles/new');
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('collectionroles');
        $this->assertAction('new');
    }

    public function testHideAction() {
        $this->dispatch('/admin/collectionroles/hide/roleid/' . $this->nonEmptyCollectionRole->getId());
        $this->assertRedirect();
        $this->assertResponseLocationHeader($this->getResponse(), '/admin/collectionroles');
    }

    public function testHideActionWithMissingParam() {
        $this->dispatch('/admin/collectionroles/hide');
        $this->assertRedirect();
        $this->assertResponseLocationHeader($this->getResponse(), '/admin/collectionroles');
    }

    public function testUnhideAction() {
        $this->dispatch('/admin/collectionroles/unhide/roleid/' . $this->nonEmptyCollectionRole->getId());
        $this->assertRedirect();
        $this->assertResponseLocationHeader($this->getResponse(), '/admin/collectionroles');
    }

    public function testUnhideActionWithMissingParam() {
        $this->dispatch('/admin/collectionroles/unhide');
        $this->assertRedirect();
        $this->assertResponseLocationHeader($this->getResponse(), '/admin/collectionroles');
    }

    /**
     * Regression Test for OPUSVIER-2638
     */
    public function testOPUSVIER2638() {
        $this->dispatch('/admin/collectionroles/edit/roleid/' . $this->nonEmptyCollectionRole->getId());
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('collectionroles');
        $this->assertAction('edit');

        $containsGermanTitle = strpos($this->getResponse()->getBody(), '<title>OPUS 4 | Sammlungseinstellungen</title>');
        $containsEnglishTitle = strpos($this->getResponse()->getBody(), '<title>OPUS 4 | Collection Properties</title>');
        $this->assertTrue($containsGermanTitle || $containsEnglishTitle);
    }

    public function testRegression3109CollectionRoleAddBreadcrumb() {
        $this->useGerman();

        $this->dispatch('/admin/collectionroles/new');
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('collectionroles');
        $this->assertAction('new');

        $this->assertNotQueryContentContains('//div.breadcrumbsContainer', 'admin_collection_index');
        $this->assertQueryCount('//div.breadcrumbsContainer//a', 2); // nur 2 Breadcrumbs mit Links
        $this->assertQueryContentContains('//div.breadcrumbsContainer//a', 'Administration');
        $this->assertQueryContentContains('//div.breadcrumbsContainer//a', 'Sammlungen');
        $this->assertQueryContentContains('//div.breadcrumbsContainer', 'Eine neue Sammlung anlegen');
    }

    public function testRegression3109CollectionRoleEditBreadcrumb() {
        $this->useGerman();

        $this->dispatch('/admin/collectionroles/edit/roleid/2');
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('collectionroles');
        $this->assertAction('edit');

        $this->assertNotQueryContentContains('//div.breadcrumbsContainer', 'admin_collection_index');
        $this->assertQueryCount('//div.breadcrumbsContainer//a', 2); // nur 2 Breadcrumbs mit Links
        $this->assertQueryContentContains('//div.breadcrumbsContainer//a', 'Administration');
        $this->assertQueryContentContains('//div.breadcrumbsContainer//a', 'Sammlungen');
        $this->assertQueryContentContains('//div.breadcrumbsContainer', 'Sammlungseinstellungen');
    }

    /**
     * Regression Test for OPUSVIER-3051
     */
    public function testDocumentServerDateModifiedNotUpdatedWhenCollectionSortOrderChanged() {

        // check for expected test data

        $collectionRole1 = new Opus_CollectionRole(1);
        $this->assertEquals(1, $collectionRole1->getPosition(), 'Test setup changed');
        $collectionRole2 = new Opus_CollectionRole(2);
        $this->assertEquals(2, $collectionRole2->getPosition(), 'Test setup changed');

        $docfinder = new Opus_DocumentFinder();
        $docfinder->setCollectionRoleId(2);
        $collectionRoleDocs = $docfinder->ids();

        $this->assertTrue(in_array(146, $collectionRoleDocs), 'Test setup changed');

        // test if server_date_modified is altered

        $docBefore = new Opus_Document(146);
        $this->dispatch('/admin/collectionroles/move/roleid/1/pos/2');
        $docAfter = new Opus_Document(146);

        // revert change in test data
        $this->resetRequest();
        $this->resetResponse();
        $this->dispatch('/admin/collectionroles/move/roleid/1/pos/1');

        $this->assertEquals((string) $docBefore->getServerDateModified(), (string) $docAfter->getServerDateModified());
    }

    public function testCreateActionGetRequest() {
        $this->dispatch('/admin/collectionroles/create');
        $this->assertRedirectTo('/admin/collectionroles');
    }

    public function testCreateAction() {
        $this->useEnglish();

        $roles = Opus_CollectionRole::fetchAll();

        $this->assertEquals(22, count($roles));

        $roleIds = array();

        foreach ($roles as $role) {
            $roleIds[] = $role->getId();
        }

        $post = array(
            'Name' => 'CreateTestColName',
            'OaiName' => 'CreateTestColOaiName',
            'DisplayBrowsing' => 'Name',
            'DisplayFrontdoor' => 'Number',
            'Visible' => '1',
            'VisibleBrowsingStart' => '1',
            'VisibleFrontdoor' => '0',
            'VisibleOai' => '0',
            'Position' => '20',
            'Save' => 'Speichern'
        );

        $this->getRequest()->setMethod('POST')->setPost($post);

        $this->dispatch('/admin/collectionroles/create');

        $roles = Opus_CollectionRole::fetchAll();

        $this->assertEquals(count($roleIds) + 1, count($roles)); // neue Collection wurde erzeugt

        $newColFound = false;

        foreach ($roles as $role) {
            if (!in_array($role->getId(), $roleIds)) {
                $role->delete();

                $this->assertEquals('CreateTestColName', $role->getName());
                $this->assertEquals('CreateTestColOaiName', $role->getOaiName());
                $this->assertEquals('Name', $role->getDisplayBrowsing());
                $this->assertEquals('Number', $role->getDisplayFrontdoor());
                $this->assertEquals(1, $role->getVisible());
                $this->assertEquals(1, $role->getVisibleBrowsingStart());
                $this->assertEquals(0, $role->getVisibleFrontdoor());
                $this->assertEquals(0, $role->getVisibleOai());
                $this->assertEquals(20, $role->getPosition());

                $newColFound = true;
            }
        }

        $this->assertTrue($newColFound, 'No new CollectionRole was created.');
        $this->assertRedirectTo('/admin/collectionroles');
        $this->verifyFlashMessage('Collection role \'CreateTestColName\' was created successfully.',
            self::MESSAGE_LEVEL_NOTICE);
    }

    public function testCreateActionForEdit() {
        $this->useEnglish();

        $roles = Opus_CollectionRole::fetchAll();

        $role = new Opus_CollectionRole();
        $role->setName('EditTestName');
        $role->setOaiName('EditTestOaiName');
        $role->setDisplayBrowsing('Name');
        $role->setDisplayFrontdoor('Number');
        $role->setVisible(1);
        $role->setVisibleBrowsingStart(1);
        $role->setVisibleFrontdoor(0);
        $role->setVisibleOai(0);
        $role->setPosition(20);

        $roleId = $role->store();

        $post = array(
            'oid' => $roleId,
            'Name' => 'ModifiedName',
            'OaiName' => 'ModifiedOaiName',
            'DisplayBrowsing' => 'Number,Name',
            'DisplayFrontdoor' => 'Name,Number',
            'Visible' => '0',
            'VisibleBrowsingStart' => '0',
            'VisibleFrontdoor' => '1',
            'VisibleOai' => '1',
            'Position' => '19',
            'Save' => 'Speichern'
        );

        $this->getRequest()->setMethod('POST')->setPost($post);

        $this->dispatch('/admin/collectionroles/create');

        $this->assertEquals(count($roles) + 1, count(Opus_CollectionRole::fetchAll())); // keine neue Collection

        $role = new Opus_CollectionRole($roleId);

        $role->delete();

        $this->assertEquals('ModifiedName', $role->getName());
        $this->assertEquals('ModifiedOaiName', $role->getOaiName());
        $this->assertEquals('Number,Name', $role->getDisplayBrowsing());
        $this->assertEquals('Name,Number', $role->getDisplayFrontdoor());
        $this->assertEquals(0, $role->getVisible());
        $this->assertEquals(0, $role->getVisibleBrowsingStart());
        $this->assertEquals(1, $role->getVisibleFrontdoor());
        $this->assertEquals(1, $role->getVisibleOai());
        $this->assertEquals(19, $role->getPosition());

        $this->assertRedirectTo('/admin/collectionroles');
        $this->verifyFlashMessage('Collection role \'ModifiedName\' was edited successfully.',
            self::MESSAGE_LEVEL_NOTICE);
    }

}
