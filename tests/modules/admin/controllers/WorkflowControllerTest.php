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


class Admin_WorkflowControllerTest extends ControllerTestCase {

    /**
     * Tests deleting a document.
     */
    public function testDeleteAction() {
        $this->dispatch('/admin/workflow/changestate/docId/24/targetState/deleted');
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('workflow');
        $this->assertAction('changestate');
    }

    /**
     * Tests user selecting no in delete confirmation form.
     */
    public function testDeleteActionConfirmNo() {
        $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'sureno' => 'sureno'
                ));
        $this->dispatch('/admin/workflow/changestate/docId/24/targetState/deleted');
        $this->assertModule('admin');
        $this->assertController('workflow');
        $this->assertAction('changestate');
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
                    'sureyes' => 'sureyes'
                ));
        $this->dispatch('/admin/workflow/changestate/docId/25/targetState/deleted');
        $this->assertModule('admin');
        $this->assertController('workflow');
        $this->assertAction('changestate');
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
        $this->dispatch('/admin/workflow/changestate/docId/25/targetState/removed');
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('workflow');
        $this->assertAction('changestate');
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
                    'sureno' => 'sureno'
                ));
        $this->dispatch('/admin/workflow/changestate/docId/24/targetState/removed');
        $this->assertModule('admin');
        $this->assertController('workflow');
        $this->assertAction('changestate');
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
                    'sureyes' => 'sureyes'
                ));
        $this->dispatch('/admin/workflow/changestate/docId/100/targetState/published');
        $this->assertModule('admin');
        $this->assertController('workflow');
        $this->assertAction('changestate');
        $this->assertRedirect('/admin/document/index');

        $doc = new Opus_Document(100);
        $this->assertEquals('published', $doc->getServerState());
    }

    /**
     * Tests unpublishing document.
     *
     * Moving a document to state 'unpublished' is not permitted.
     *
     * @depends testPublishActionConfirmYes
     */
    public function testUnpublishAction() {
        $this->dispatch('/admin/workflow/changestate/docId/100/targetState/unpublished');

        $this->assertEquals(302, $this->getResponse()->getHttpResponseCode());
        $this->assertResponseLocationHeader($this->getResponse(), '/admin/document/index/id/100');

        $this->assertFalse($this->getResponse()->getHttpResponseCode() == 200,
                "Request was not redirected.");
        $this->assertTrue($this->getResponse()->getHttpResponseCode() != 500,
                "Request produced internal error. " . $this->getResponse()->getBody());
    }

    /**
     * Regression test for OPUSVIER-1744.
     *
     * Test for XSS using docId.
     */
    public function testXssUsingIdForDeletingDocuments() {
        $this->dispatch('/admin/workflow/changestate/docId/<span>123/targetState/deleted');
        $this->assertEquals(302, $this->getResponse()->getHttpResponseCode());
        $this->assertResponseLocationHeader($this->getResponse(), '/admin/documents');

        $this->assertTrue(substr_count($this->getResponse()->getBody(), '<span>123') == 0);
    }

    /**
     * Regression test for OPUSVIER-1744.
     *
     * Test for failure to redirect for already deleted documents.
     */
    public function testNoRedirectForAlreadyDeletedDocuments() {
        $this->dispatch('/admin/workflow/changestate/docId/123/targetState/deleted');
        $this->assertEquals(302, $this->getResponse()->getHttpResponseCode());
        $this->assertResponseLocationHeader($this->getResponse(), '/admin/document/index/id/123');

        $this->assertFalse($this->getResponse()->getHttpResponseCode() == 200,
                "Request was not redirected.");
        $this->assertTrue($this->getResponse()->getHttpResponseCode() != 500,
                "Request produced internal error.");
    }

    /**
     * Regression test for OPUSVIER-1744.
     *
     * If the document ID is invalid a redirect should happen.
     */
    public function testNoRedirectForInvalidIdForDeletingDocuments() {
        $this->dispatch('/admin/workflow/changestate/docId/123456789/targetState/deleted');
        $this->assertEquals(302, $this->getResponse()->getHttpResponseCode());
        $this->assertResponseLocationHeader($this->getResponse(), '/admin/documents');

        $this->assertFalse($this->getResponse()->getHttpResponseCode() == 200,
                "Request was not redirected.");
        $this->assertTrue($this->getResponse()->getHttpResponseCode() != 500,
                "Request produced internal error. " . $this->getResponse()->getBody());
    }

    /**
     * Regression test for OPUSVIER-1744.
     *
     * If the document ID is non-numeric a redirect should happen.
     */
    public function testNoRedirectForNonNumericIdForDeletingDocuments() {
        $this->dispatch('/admin/workflow/changestate/docId/foo/targetState/deleted');
        $this->assertEquals(302, $this->getResponse()->getHttpResponseCode());
        $this->assertResponseLocationHeader($this->getResponse(), '/admin/documents');

        $this->assertFalse($this->getResponse()->getHttpResponseCode() == 200,
                "Request was not redirected.");
        $this->assertTrue($this->getResponse()->getHttpResponseCode() != 500,
                "Request produced internal error. " . $this->getResponse()->getBody());
    }

}
