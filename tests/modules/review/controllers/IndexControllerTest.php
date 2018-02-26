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
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2018, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Basic unit tests for class Review_IndexController.
 *
 * @covers Review_IndexController
 */
class Review_IndexControllerTest extends ControllerTestCase {

    private $documentId = null;

    public function setUp() {
        parent::setUp();

        $document = $this->createTestDocument();
        $document->setServerState('unpublished');
        $document->setPersonReferee(array());
        $document->setEnrichment(array());
        $this->documentId = $document->store();

        $document = new Opus_Document($this->documentId);
        $this->assertEquals(0, count($document->getPersonReferee()));
        $this->assertEquals(0, count($document->getEnrichment()));
    }

    /**
     * Basic tests dispatching and executing 'index' action.
     */
    public function testCallWithoutActionShouldPullFromIndexAction() {
        $this->dispatch('/review');

        $this->assertResponseCode(200);
        $this->assertController('index');
        $this->assertAction('index');

        $this->assertQueryCount('//table.documents//a[@class="new-window"]', 10);
    }

    public function testClearActionWithoutPost() {
        $this->dispatch('/review/index/clear');

        $this->assertResponseCode(200);
        $this->assertController('index');
        $this->assertAction('clear');
    }

    public function testRejectActionWithoutPost() {
        $this->dispatch('/review/index/reject');
        $this->assertResponseCode(200);
        $this->assertController('index');
        $this->assertAction('reject');
    }

    public function testIndexActionClearButtonWithOneDocumentGoesToClear() {
        $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'selected' => array('1', $this->documentId),
                    'buttonSubmit' => 'buttonSubmit',
                ));
        $this->dispatch('/review/index/index');

        $this->assertResponseCode(200);
        $this->assertModule('review');
        $this->assertController('index');
        $this->assertAction('clear');

        $response = $this->getResponse();
        $this->assertContains('sureyes', $response->getBody());
        $this->assertContains('sureno', $response->getBody());

        $document = new Opus_Document($this->documentId);
        $this->assertEquals('unpublished', $document->getServerState());
    }

    public function testClearActionWithOneDocumentUnconfirmed() {
        $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'selected' => $this->documentId,
                ));
        $this->dispatch('/review/index/clear');

        $this->assertResponseCode(200);
        $this->assertModule('review');
        $this->assertController('index');
        $this->assertAction('clear');

        $response = $this->getResponse();
        $this->assertContains('sureyes', $response->getBody());
        $this->assertContains('sureno', $response->getBody());

        $document = new Opus_Document($this->documentId);
        $this->assertEquals('unpublished', $document->getServerState());
    }

    public function testClearActionWithOneDocumentCanceled() {
        $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'selected' => $this->documentId,
                    'sureno' => 'no',
                ));
        $this->dispatch('/review/index/clear');

        $this->assertResponseCode(200);
        $this->assertModule('review');
        $this->assertController('index');
        $this->assertAction('index');

        $response = $this->getResponse();
        $this->assertNotContains('sureyes', $response->getBody());
        $this->assertNotContains('sureno', $response->getBody());

        $document = new Opus_Document($this->documentId);
        $this->assertEquals('unpublished', $document->getServerState());
    }

    public function testClearActionWithOneDocumentConfirmed() {
        $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'selected' => $this->documentId,
                    'sureyes' => 'yes',
                ));
        $this->dispatch('/review/index/clear');

        $this->assertResponseCode(200);
        $this->assertModule('review');
        $this->assertController('index');
        $this->assertAction('clear');

        $response = $this->getResponse();
        $this->assertNotContains('sureyes', $response->getBody());
        $this->assertNotContains('sureno', $response->getBody());

        $document = new Opus_Document($this->documentId);
        $this->assertEquals('published', $document->getServerState());
    }

    public function testRejectActionWithOneDocumentUnconfirmed() {
        $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'selected' => $this->documentId,
                ));
        $this->dispatch('/review/index/reject');

        $this->assertResponseCode(200);
        $this->assertModule('review');
        $this->assertController('index');
        $this->assertAction('reject');

        $response = $this->getResponse();
        $this->assertContains('sureyes', $response->getBody());
        $this->assertContains('sureno', $response->getBody());

        $document = new Opus_Document($this->documentId);
        $this->assertEquals('unpublished', $document->getServerState());
    }

    public function testRejectActionWithOneDocumentCanceled() {
        $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'selected' => $this->documentId,
                    'sureno' => 'no',
                ));
        $this->dispatch('/review/index/reject');

        $this->assertResponseCode(200);
        $this->assertModule('review');
        $this->assertController('index');
        $this->assertAction('index');

        $response = $this->getResponse();
        $this->assertNotContains('sureyes', $response->getBody());
        $this->assertNotContains('sureno', $response->getBody());

        $document = new Opus_Document($this->documentId);
        $this->assertEquals('unpublished', $document->getServerState());
    }

    public function testRejectActionWithOneDocumentConfirmed() {
        $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'selected' => $this->documentId,
                    'sureyes' => 'yes',
                ));
        $this->dispatch('/review/index/reject');

        $this->assertResponseCode(200);
        $this->assertModule('review');
        $this->assertController('index');
        $this->assertAction('reject');

        $response = $this->getResponse();
        $this->assertNotContains('sureyes', $response->getBody());
        $this->assertNotContains('sureno', $response->getBody());

        $document = new Opus_Document($this->documentId);
        $this->assertEquals('deleted', $document->getServerState());
    }

}
