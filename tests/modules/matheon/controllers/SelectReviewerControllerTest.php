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
 * @author      Thoralf Klein <thoralf.klein@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class Matheon_SelectReviewerControllerTest extends ControllerTestCase {

    public function testFormWithoutDocumentId() {
        $session = new Zend_Session_Namespace('Publish');
        $session->unsetAll();

        $this->dispatch('/matheon/select-reviewer/form');
        $this->assertResponseCode(500);
    }

    public function testFormWithInvalidDocumentId() {
        $session = new Zend_Session_Namespace('Publish');
        $session->depositConfirmDocumentId = 12341234;

        $this->dispatch('/matheon/select-reviewer/form');
        $this->assertResponseCode(500);
        
    }

    public function testFormWithValidDocumentId() {
        $this->loginUser('referee', 'refereereferee');
        $loggedUserModel = new Publish_Model_LoggedUser();
        $loggedUserId = $loggedUserModel->getUserId();

        $document = new Opus_Document();
        $document->setServerState('unpublished');
        $document->addEnrichment()
                ->setKeyName('submitter.user_id')
                ->setValue($loggedUserId);

        $docId = $document->store();

        $session = new Zend_Session_Namespace('Publish');
        $session->depositConfirmDocumentId = $docId;

        $this->dispatch('/matheon/select-reviewer/form');
        $this->assertResponseCode(200);
    }

    public function testFormWithValidDocumentIdSubmit() {
        $this->loginUser('referee', 'refereereferee');
        $loggedUserModel = new Publish_Model_LoggedUser();
        $loggedUserId = $loggedUserModel->getUserId();

        $document = new Opus_Document();
        $document->setServerState('unpublished');
        $document->addEnrichment()
                ->setKeyName('submitter.user_id')
                ->setValue($loggedUserId);

        $docId = $document->store();

        $session = new Zend_Session_Namespace('Publish');
        $session->depositConfirmDocumentId = $docId;

        $this->request->setMethod('POST')
                ->setPost(array(
                    'reviewerid' => $loggedUserId,
                    'submit'     => 'Send',
                ));

        $this->dispatch('/matheon/select-reviewer/form');
        $this->assertResponseCode(200);

        $response = $this->getResponse();
        echo $response->getBody();

        $this->assertQueryContentContains('div#content', 'has been notified');
    }

    public function testEmptyForm() {
        $session = new Zend_Session_Namespace('Publish');
        $session->unsetAll();

        $this->dispatch('/matheon/select-reviewer/form');
        $this->assertResponseCode(500);
    }
}
