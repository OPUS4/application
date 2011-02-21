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
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
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
        $this->assertModule('admin');
        $this->assertController('collectionroles');
        $this->assertAction('delete');
    }

    public function testDeleteActionWithMissingParam() {
        $this->dispatch('/admin/collectionroles/delete');
        $this->assertRedirect();
        $this->assertModule('admin');
        $this->assertController('collectionroles');
        $this->assertAction('delete');
    }

    public function testMoveAction() {
        $this->dispatch('/admin/collectionroles/move/pos/1/roleid/' . $this->emptyCollectionRole->getId());
        $this->assertRedirect();
        $this->assertModule('admin');
        $this->assertController('collectionroles');
        $this->assertAction('move');
    }

    public function testMoveActionWithMissingParam() {
        $this->dispatch('/admin/collectionroles/move');
        $this->assertRedirect();
        $this->assertModule('admin');
        $this->assertController('collectionroles');
        $this->assertAction('move');
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
        $this->assertModule('admin');
        $this->assertController('collectionroles');
        $this->assertAction('hide');
    }

    public function testHideActionWithMissingParam() {
        $this->dispatch('/admin/collectionroles/hide');
        $this->assertRedirect();
        $this->assertModule('admin');
        $this->assertController('collectionroles');
        $this->assertAction('hide');
    }

    public function testUnhideAction() {
        $this->dispatch('/admin/collectionroles/unhide/roleid/' . $this->nonEmptyCollectionRole->getId());
        $this->assertRedirect();
        $this->assertModule('admin');
        $this->assertController('collectionroles');
        $this->assertAction('unhide');
    }

    public function testUnhideActionWithMissingParam() {
        $this->dispatch('/admin/collectionroles/unhide');
        $this->assertRedirect();
        $this->assertModule('admin');
        $this->assertController('collectionroles');
        $this->assertAction('unhide');
    }    
}
?>