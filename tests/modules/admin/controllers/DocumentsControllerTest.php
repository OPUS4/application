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
 * @category    TODO
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Basic unit test for the documents controller in the admin module.
 */
class Admin_DocumentsControllerTest extends ControllerTestCase {

    /**
     * Test index action.
     */
    public function testIndexAction() {
        $this->dispatch('/admin/documents');
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('documents');
        $this->assertAction('index');
    }

    /**
     * Test edit action.
     */
    public function testEditAction() {
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
        $this->dispatch('/admin/documents/edit');
        $this->assertRedirectTo('/admin/documents');
    }

    /**
     * Test edit action with bad ID.
     */
    public function testEditActionWithBadId() {
        $this->dispatch('/admin/documents/edit/id/1k1');
        $this->assertRedirectTo('/admin/documents');
    }

    /**
     * Test edit action with unknown ID.
     *
     * TODO check for specific exception?
     */
    public function testEditActionWithUnknownId() {
        $this->dispatch('/admin/documents/edit/id/500');
        $this->assertModule('default');
        $this->assertController('error');
        $this->assertAction('error');
    }

    public function testShowAction() {
        $this->dispatch('/admin/documents/show/id/1');
        $this->assertModule('admin');
        $this->assertController('documents');
        $this->assertAction('show');
    }

    public function testShowActionDoc91() {
        $this->dispatch('/admin/documents/show/id/91');
        $this->assertModule('admin');
        $this->assertController('documents');
        $this->assertAction('show');
    }

    public function testDeleteAction() {
        $this->dispatch('/admin/documents/delete/docId/24');
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('documents');
        $this->assertAction('delete');
    }

    public function testDeleteActionConfirmNo() {
        $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'id' => '24',
                    'sureno' => 'sureno'
                ));
        $this->dispatch('/admin/documents/delete');
        $this->assertModule('admin');
        $this->assertController('documents');
        $this->assertAction('delete');
        $this->assertRedirect('/admin/documents/index');

        $doc = new Opus_Document(24);
        $this->assertNotEquals('deleted', $doc->getServerState());
    }

    /**
     *
     */
    public function testDeleteActionConfirmYes() {
        $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'id' => '25',
                    'sureyes' => 'sureyes'
                ));
        $this->dispatch('/admin/documents/delete');
        $this->assertModule('admin');
        $this->assertController('documents');
        $this->assertAction('delete');
        $this->assertRedirect('/admin/documents/index');

        $doc = new Opus_Document(25);
        $this->assertEquals('deleted', $doc->getServerState());
    }

    /**
     * @depends testDeleteActionConfirmYes
     */
    public function testPermanentDeleteAction() {
        $this->dispatch('/admin/documents/permanentdelete/docId/25');
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('documents');
        $this->assertAction('permanentdelete');
    }

    /**
     * @depends testPermanentDeleteAction
     */
    public function testPermanentDeleteActionConfirmNo() {
        $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'id' => '24',
                    'sureno' => 'sureno'
                ));
        $this->dispatch('/admin/documents/permanentdelete');
        $this->assertModule('admin');
        $this->assertController('documents');
        $this->assertAction('permanentdelete');
        $this->assertRedirect('/admin/documents/index');

        $doc = new Opus_Document(25);
        $this->assertEquals('deleted', $doc->getServerState());
    }

    public function testPublishAction() {
        $this->dispatch('/admin/documents/publish/docId/100');
        $this->assertModule('admin');
        $this->assertController('documents');
        $this->assertAction('publish');
        $this->assertRedirect('/admin/documents/index');

        $doc = new Opus_Document(100);
        $this->assertEquals('published', $doc->getServerState());
    }

    public function testUnpublishAction() {
        $this->dispatch('/admin/documents/unpublish/docId/100');
        $this->assertModule('admin');
        $this->assertController('documents');
        $this->assertAction('unpublish');
        $this->assertRedirect('/admin/documents/index');

        $doc = new Opus_Document(100);
        $this->assertEquals('unpublished', $doc->getServerState());
    }

}

?>
