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
 * @category    Unit Tests
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Unit tests for Admin_DocumentController.
 */
class Admin_DocumentControllerTest extends ControllerTestCase {
    
    /**
     * Test edit action.
     */
    public function testEditAction() {
        $this->markTestSkipped("needs to be adapted"); 
        $this->dispatch('/admin/documents/edit/id/1');
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('documents');
        $this->assertAction('edit');
    }

    /**
     * Test edit action with missing ID.
     */
    public function testEditActionWithMissingId() {
        $this->markTestSkipped("needs to be adapted"); 
        $this->dispatch('/admin/documents/edit');
        $this->assertRedirectTo('/admin/documents');
    }

    /**
     * Test edit action with bad ID.
     */
    public function testEditActionWithBadId() {
        $this->markTestSkipped("needs to be adapted"); 
        $this->dispatch('/admin/documents/edit/id/1k1');
        $this->assertRedirectTo('/admin/documents');
    }

    /**
     * Test edit action with unknown ID.
     *
     * TODO check for specific exception?
     */
    public function testEditActionWithUnknownId() {
        $this->markTestSkipped("needs to be adapted"); 
        $this->dispatch('/admin/documents/edit/id/500');
        $this->assertModule('default');
        $this->assertController('error');
        $this->assertAction('error');
    }

    public function testShowAction() {
        $this->markTestSkipped("needs to be adapted"); 
        $this->dispatch('/admin/documents/show/id/1');
        $this->assertModule('admin');
        $this->assertController('documents');
        $this->assertAction('show');
    }

    public function testShowActionDoc91() {
        $this->markTestSkipped("needs to be adapted"); 
        $this->dispatch('/admin/documents/show/id/91');
        $this->assertModule('admin');
        $this->assertController('documents');
        $this->assertAction('show');
    }

    /**
     * Tests deleting a document.
     */
    public function testDeleteAction() {
        $this->dispatch('/admin/document/delete/docId/24');
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('document');
        $this->assertAction('delete');
    }
    
    /**
     * Tests user selecting no in delete confirmation form.
     */
    public function testDeleteActionConfirmNo() {
        $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'id' => '24',
                    'sureno' => 'sureno'
                ));
        $this->dispatch('/admin/document/delete');
        $this->assertModule('admin');
        $this->assertController('document');
        $this->assertAction('delete');
        $this->assertRedirect('/admin/document/index');

        $doc = new Opus_Document(24);
        $this->assertNotEquals('deleted', $doc->getServerState());
    }

    /**
     * Tests user selecting yes in delete confirmation form.
     */
    public function testDeleteActionConfirmYes() {
        $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'id' => '25',
                    'sureyes' => 'sureyes'
                ));
        $this->dispatch('/admin/document/delete');
        $this->assertModule('admin');
        $this->assertController('document');
        $this->assertAction('delete');
        $this->assertRedirect('/admin/document/index');

        $doc = new Opus_Document(25);
        $this->assertEquals('deleted', $doc->getServerState());
    }

    /**
     * Tests permanently deleting a document.
     * 
     * @depends testDeleteActionConfirmYes
     */
    public function testPermanentDeleteAction() {
        $this->dispatch('/admin/document/permanentdelete/docId/25');
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('document');
        $this->assertAction('permanentdelete');
    }

    /**
     * Tests user answering no in permanent delete confirmation form.
     * 
     * @depends testPermanentDeleteAction
     */
    public function testPermanentDeleteActionConfirmNo() {
        $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'id' => '24',
                    'sureno' => 'sureno'
                ));
        $this->dispatch('/admin/document/permanentdelete');
        $this->assertModule('admin');
        $this->assertController('document');
        $this->assertAction('permanentdelete');
        $this->assertRedirect('/admin/document/index');

        $doc = new Opus_Document(25);
        $this->assertEquals('deleted', $doc->getServerState());
    }

    /**
     * Tests user answering yes in publish confirmation form.
     */
    public function testPublishActionConfirmYes() {
        $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'id' => '100',
                    'sureyes' => 'sureyes'
                ));
        $this->dispatch('/admin/document/publish');
        $this->assertModule('admin');
        $this->assertController('document');
        $this->assertAction('publish');
        $this->assertRedirect('/admin/document/index');

        $doc = new Opus_Document(100);
        $this->assertEquals('published', $doc->getServerState());
    }

    /**
     * Tests unpublishing document.
     * 
     * @depends testPublishActionConfirmYes
     */
    public function testUnpublishAction() {
        $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'id' => '100',
                    'sureyes' => 'sureyes'
                ));
        $this->dispatch('/admin/document/unpublish');
        $this->assertModule('admin');
        $this->assertController('document');
        $this->assertAction('unpublish');
        $this->assertRedirect('/admin/document/index');

        $doc = new Opus_Document(100);
        $this->assertEquals('unpublished', $doc->getServerState());
    }
    
    public function testUnlinkCollectionAction() {
        $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'submit' => 'submit'
                ));
        $this->dispatch('/admin/document/unlinkcollection/id/1/role/2/collection/15');
        $this->assertModule('admin');
        $this->assertController('document');
        $this->assertAction('unlinkcollection');
        $this->assertRedirect('/admin/document/index');
    }

}

?>
