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
 * @category    Application
 * @package     Tests
 * @author      Sascha Szott <szott@zib.de>
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Basic unit tests for the collections controller in admin module.
 */
class Admin_CollectionControllerTest extends ControllerTestCase {

    private $emptyCollectionRole = null;
    private $nonEmptyCollectionRole = null;
    private $collection = null;

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

        $this->collection = new Opus_Collection();        
        $rootCollection = $this->nonEmptyCollectionRole->addRootCollection();
        $rootCollection->store();
        $rootCollection->addFirstChild($this->collection);
        $this->collection->store();
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
        $this->dispatch('/admin/collection');
        $this->assertRedirect();
        $this->assertModule('admin');
        $this->assertController('collection');
        $this->assertAction('index');
    }

    /**
     * Test show first level of collection.
     */
    public function testShowAction() {
        $this->dispatch('/admin/collection/show/id/' . $this->collection->getId());
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('collection');
        $this->assertAction('show');
    }

    public function testShowActionWithEmptyRole() {
        $this->dispatch('/admin/collection/show/role/' . $this->emptyCollectionRole->getId());
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('collection');
        $this->assertAction('show');
    }

    public function testShowActionWithNonEmptyRole() {
        $this->dispatch('/admin/collection/show/role/' . $this->nonEmptyCollectionRole->getId());
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('collection');
        $this->assertAction('show');
    }

    public function testShowActionMissingArg() {
        $this->dispatch('/admin/collection/show');
        $this->assertRedirect();
        $this->assertModule('admin');
        $this->assertController('collection');
        $this->assertAction('show');
    }

    /**
     * Test opening collection for editing.
     */
    public function testEditAction() {
        $this->dispatch('/admin/collection/edit/id/' . $this->collection->getId());
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('collection');
        $this->assertAction('edit');
    }

    public function testDeleteAction() {
        $this->dispatch('/admin/collection/delete/id/' . $this->collection->getId());
        $this->assertRedirect();
        $this->assertModule('admin');
        $this->assertController('collection');
        $this->assertAction('delete');
    }

    public function testDeleteActionWithMissingParam() {
        $this->dispatch('/admin/collection/delete');
        $this->assertRedirect();
        $this->assertModule('admin');
        $this->assertController('collection');
        $this->assertAction('delete');
    }

    public function testNewAction() {
        $this->dispatch('/admin/collection/new/type/child/id/' . $this->collection->getId());
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('collection');
        $this->assertAction('new');
    }
    
    public function testNewActionWithMissingParams() {
        $this->dispatch('/admin/collection/new');
        $this->assertRedirect();
        $this->assertModule('admin');
        $this->assertController('collection');
        $this->assertAction('new');
    }

    public function testNewActionWithMissingParam() {
        $this->dispatch('/admin/collection/new/id/' . $this->collection->getId());
        $this->assertRedirect();
        $this->assertModule('admin');
        $this->assertController('collection');
        $this->assertAction('new');
    }

    public function testHideAction() {
        $this->dispatch('/admin/collection/hide/id/' . $this->collection->getId());
        $this->assertRedirect();
        $this->assertModule('admin');
        $this->assertController('collection');
        $this->assertAction('hide');
    }

    public function testHideActionWithMissingParam() {
        $this->dispatch('/admin/collection/hide');
        $this->assertRedirect();
        $this->assertModule('admin');
        $this->assertController('collection');
        $this->assertAction('hide');
    }

    public function testUnhideAction() {
        $this->dispatch('/admin/collection/unhide/id/' . $this->collection->getId());
        $this->assertRedirect();
        $this->assertModule('admin');
        $this->assertController('collection');
        $this->assertAction('unhide');
    }

    public function testUnhideActionWithMissingParam() {
        $this->dispatch('/admin/collection/unhide');
        $this->assertRedirect();
        $this->assertModule('admin');
        $this->assertController('collection');
        $this->assertAction('unhide');
    }

    public function testAssignActionWithMissingParam() {
        $this->dispatch('/admin/collection/assign');
        $this->assertRedirect();
        $this->assertModule('admin');
        $this->assertController('collection');
        $this->assertAction('assign');
    }
}
?>