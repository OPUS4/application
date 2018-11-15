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
 * @package     Admin
 * @author      Jens Schwidder <schwidder@zib.de>
 * @author      Sascha Szott <szott@zib.de>
 * @copyright   Copyright (c) 2008-2018, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Class Admin_WorkflowControllerTest.
 *
 * @covers Admin_WorkflowController
 */
class Admin_WorkflowControllerTest extends ControllerTestCase
{

    private function enablePublishNotification()
    {
        $config = Zend_Registry::get('Zend_Config');
        $config->notification->document->published->enabled = 1;
        $config->notification->document->published->email = "published@localhost";
    }

    private function createDocWithSubmitterAndAuthor($submitterMail, $authorMail)
    {
        $doc = $this->createTestDocument();

        $author = new Opus_Person();
        $author->setFirstName("John");
        $author->setLastName("Doe");
        if ($author != '') {
            $author->setEmail($authorMail);
        }
        $doc->addPersonAuthor($author);

        $submitter = new Opus_Person();
        $submitter->setFirstName("John");
        $submitter->setLastName("Submitter");
        if ($submitterMail != '') {
            $submitter->setEmail($submitterMail);
        }
        $doc->addPersonSubmitter($submitter);

        $doc->store();
        return $doc;
    }

    /**
     * Tests deleting a document.
     */
    public function testDeleteAction()
    {
        $this->dispatch('/admin/workflow/changestate/docId/24/targetState/deleted');
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('workflow');
        $this->assertAction('changestate');
    }

    /**
     * Tests user selecting no in delete confirmation form.
     */
    public function testDeleteActionConfirmNo()
    {
        $this->request
                ->setMethod('POST')
                ->setPost([
                    'sureno' => 'sureno'
                ]);
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
    public function testDeleteActionConfirmYes()
    {
        $this->request
                ->setMethod('POST')
                ->setPost([
                    'sureyes' => 'sureyes'
                ]);
        $this->dispatch('/admin/workflow/changestate/docId/102/targetState/deleted');
        $this->assertModule('admin');
        $this->assertController('workflow');
        $this->assertAction('changestate');
        $this->assertRedirect('/admin/document/index');

        $doc = new Opus_Document(102);
        $this->assertEquals('deleted', $doc->getServerState());
        $doc->setServerState('unpublished');
        $doc->store();
    }

    /**
     * Tests showing confirmation for permanently deleting a document.
     */
    public function testPermanentDeleteAction()
    {
        $document = $this->createTestDocument();
        $document->setServerState('deleted');
        $documentId = $document->store();

        $this->dispatch('/admin/workflow/changestate/docId/' . $documentId . '/targetState/removed');

        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('workflow');
        $this->assertAction('changestate');
    }

    /**
     * Tests user answering no in permanent delete confirmation form.
     */
    public function testPermanentDeleteActionConfirmNo()
    {
        $document = $this->createTestDocument();
        $document->setServerState('deleted');
        $documentId = $document->store();

        $this->request
                ->setMethod('POST')
                ->setPost([
                    'sureno' => 'sureno'
                ]);
        $this->dispatch('/admin/workflow/changestate/docId/' . $documentId . '/targetState/removed');
        $this->assertModule('admin');
        $this->assertController('workflow');
        $this->assertAction('changestate');
        $this->assertRedirect('/admin/document/index');

        $doc = new Opus_Document($documentId);
        $this->assertEquals('deleted', $doc->getServerState());
    }

    /**
     * Tests user answering yes in publish confirmation form.
     */
    public function testPublishActionConfirmYes()
    {
        $this->request
                ->setMethod('POST')
                ->setPost([
                    'sureyes' => 'sureyes'
                ]);
        $this->dispatch('/admin/workflow/changestate/docId/100/targetState/published');
        $this->assertModule('admin');
        $this->assertController('workflow');
        $this->assertAction('changestate');
        $this->assertRedirect('/admin/document/index');

        $doc = new Opus_Document(100);
        $this->assertEquals('published', $doc->getServerState());
        $doc->setServerState('unpublished');
        $doc->store();
    }

    /**
     * Tests unpublishing document.
     *
     * Moving a document to state 'unpublished' is not permitted.
     *
     * @depends testPublishActionConfirmYes
     */
    public function testUnpublishAction()
    {
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
    public function testXssUsingIdForDeletingDocuments()
    {
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
    public function testNoRedirectForAlreadyDeletedDocuments()
    {
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
    public function testNoRedirectForInvalidIdForDeletingDocuments()
    {
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
    public function testNoRedirectForNonNumericIdForDeletingDocuments()
    {
        $this->dispatch('/admin/workflow/changestate/docId/foo/targetState/deleted');
        $this->assertEquals(302, $this->getResponse()->getHttpResponseCode());
        $this->assertResponseLocationHeader($this->getResponse(), '/admin/documents');

        $this->assertFalse($this->getResponse()->getHttpResponseCode() == 200,
                "Request was not redirected.");
        $this->assertTrue($this->getResponse()->getHttpResponseCode() != 500,
                "Request produced internal error. " . $this->getResponse()->getBody());
    }

    public function testNotificationIsNotSupported()
    {
        $doc = $this->createDocWithSubmitterAndAuthor(
            'submitter@localhost.de', 'author@localhost.de'
        );
        $this->dispatch('/admin/workflow/changestate/docId/' . $doc->getId() . '/targetState/published');

        $body = $this->getResponse()->getBody();

        $this->assertNotContains('submitter@localhost.de', $body);
        $this->assertNotContains('author@localhost.de', $body);
        $this->assertNotContains(
            '<input type="checkbox" name="submitter" id="submitter"', $body
        );
        $this->assertNotContains(
            '<input type="checkbox" name="author_1" id="author_1"', $body
        );
    }

    public function testSubmitterNotificationIsAvailable()
    {
        $this->enablePublishNotification();
        $doc = $this->createDocWithSubmitterAndAuthor(
            'submitter@localhost.de', 'author@localhost.de'
        );
        $this->dispatch('/admin/workflow/changestate/docId/' . $doc->getId() . '/targetState/published');

        $body = $this->getResponse()->getBody();

        $this->assertContains('submitter@localhost.de', $body);
        $this->assertContains('author@localhost.de', $body);
        $this->assertXpath('//input[@type="checkbox" and @id="submitter" and @name="submitter" and @value="1" and @checked="checked"]');
    }

    public function testAuthorNotificationIsAvailable()
    {
        $this->enablePublishNotification();
        $doc = $this->createDocWithSubmitterAndAuthor(
            'submitter@localhost.de', 'author@localhost.de'
        );
        $this->dispatch('/admin/workflow/changestate/docId/' . $doc->getId() . '/targetState/published');

        $body = $this->getResponse()->getBody();

        $this->assertContains('submitter@localhost.de', $body);
        $this->assertContains('author@localhost.de', $body);
        $this->assertXpath('//input[@type="checkbox" and @id="author_0" and @name="author_0" and @value="1" and @checked="checked"]');
    }

    public function testSubmitterNotificationIsNotAvailable()
    {
        $this->enablePublishNotification();
        $doc = $this->createDocWithSubmitterAndAuthor('', 'author@localhost.de');

        $this->dispatch('/admin/workflow/changestate/docId/' . $doc->getId() . '/targetState/published');

        $body = $this->getResponse()->getBody();

        $this->assertNotContains('submitter@localhost.de', $body);
        $this->assertContains('author@localhost.de', $body);
        $this->assertXpath('//input[@type="checkbox" and @id="submitter" and @name="submitter" and @value="1" and @disabled="disabled"]');
        $this->assertXpath('//input[@type="checkbox" and @id="author_0" and @name="author_0" and @value="1" and @checked="checked"]');
    }

    public function testAuthorNotificationIsNotAvailable()
    {
        $this->enablePublishNotification();
        $doc = $this->createDocWithSubmitterAndAuthor('submitter@localhost.de', '');

        $this->dispatch('/admin/workflow/changestate/docId/' . $doc->getId() . '/targetState/published');

        $body = $this->getResponse()->getBody();

        $this->assertContains('submitter@localhost.de', $body);
        $this->assertNotContains('author@localhost.de', $body);
        $this->assertXpath('//input[@type="checkbox" and @id="submitter" and @name="submitter" and @value="1" and @checked="checked"]');
        $this->assertXpath('//input[@type="checkbox" and @id="author_0" and @name="author_0" and @value="1" and @disabled="disabled"]');
    }

    public function testAuthorNotificationForMultipleAuthors()
    {
        $this->enablePublishNotification();
        $doc = $this->createDocWithSubmitterAndAuthor(
            'submitter@localhost.de', 'author@localhost.de'
        );

        $author = new Opus_Person();
        $author->setFirstName("AFN");
        $author->setLastName("ALN");
        $author->setEmail("A@localhost.de");
        $doc->addPersonAuthor($author);

        $author = new Opus_Person();
        $author->setFirstName("BFN");
        $author->setLastName("BLN");
        $doc->addPersonAuthor($author);

        $author = new Opus_Person();
        $author->setFirstName("CFN");
        $author->setLastName("CLN");
        $author->setEmail("C@localhost.de");
        $doc->addPersonAuthor($author);

        $doc->store();

        $this->dispatch('/admin/workflow/changestate/docId/' . $doc->getId() . '/targetState/published');

        $body = $this->getResponse()->getBody();

        $this->assertContains('submitter@localhost.de', $body);
        $this->assertContains('author@localhost.de', $body);
        $this->assertContains('A@localhost.de', $body);
        $this->assertContains('C@localhost.de', $body);

        $this->assertXpath('//input[@type="checkbox" and @id="submitter" and @name="submitter" and @value="1" and @checked="checked"]');
        $this->assertXpath('//input[@type="checkbox" and @id="author_0" and @name="author_0" and @value="1" and @checked="checked"]');
        $this->assertXpath('//input[@type="checkbox" and @id="author_1" and @name="author_1" and @value="1" and @checked="checked"]');
        $this->assertXpath('//input[@type="checkbox" and @id="author_2" and @name="author_2" and @value="1" and @disabled="disabled"]');
        $this->assertXpath('//input[@type="checkbox" and @id="author_3" and @name="author_3" and @value="1" and @checked="checked"]');
    }

    public function testShowDocInfoOnConfirmationPage()
    {
        $this->dispatch('/admin/workflow/changestate/docId/146/targetState/deleted');
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('workflow');
        $this->assertAction('changestate');

        $this->assertQueryContentContains('div#docinfo', 'KOBV');
        $this->assertQueryContentContains('div#docinfo', '146');
        $this->assertQueryContentContains('div#docinfo', 'Doe, John');
    }

    public function testConfirmationDisabled()
    {
        Zend_Registry::get('Zend_Config')->merge(new Zend_Config([
            'confirmation' => ['document' => ['statechange' => ['enabled' => '0']]]
        ]));

        $this->dispatch('/admin/workflow/changestate/docId/102/targetState/deleted');
        $this->assertRedirectTo('/admin/document/index/id/102'); // Änderung wird sofort durchgefuehrt

        $doc = new Opus_Document(102);
        $this->assertEquals('deleted', $doc->getServerState());
        $doc->setServerState('unpublished');
        $doc->store();
    }
}
