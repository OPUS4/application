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
 * @package     Frontdoor
 * @author      Sascha Szott <szott@zib.de>
 * @copyright   Copyright (c) 2008-2018, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Class Frontdoor_MailControllerTest.
 *
 * @covers Frontdoor_MailController
 */
class Frontdoor_MailControllerTest extends ControllerTestCase {

    private $documentId;
    
    private $authorDocumentId;
    private $authorId;

    public function setUp() {
        parent::setUp();
        $document = $this->createTestDocument();
        $document->setServerState('published');
        $document->setType('baz');

        $title = new Opus_Title();
        $title->setValue('foobartitle');
        $title->setLanguage('deu');
        $document->setTitleMain($title);       

        $this->documentId = $document->store();
        $this->assertNotNull($this->documentId);

        $document = $this->createTestDocument();
        $document->setServerState('published');
        $document->setType('baz');

        $title = new Opus_Title();
        $title->setValue('foobartitle');
        $title->setLanguage('deu');
        $document->setTitleMain($title);

        $author = new Opus_Person();
        $author->setFirstName('John');
        $author->setLastName('Doe');
        $author->setEmail('doe@example.org');
        $this->authorId = $author->store();
        $this->assertNotNull($this->authorId);

        $link_person = $document->addPersonAuthor($author);
        $link_person->setAllowEmailContact('1');

        $this->authorDocumentId = $document->store();
        $this->assertNotNull($this->authorDocumentId);
    }

    public function testIndexActionNotSupported() {
        $this->dispatch('/frontdoor/mail/index/');
        $this->assertResponseCode(500);
        $this->assertContains('currently not supported', $this->getResponse()->getBody());
    }

    public function testSendmailActionNotSupported() {
        $this->dispatch('/frontdoor/mail/sendmail/');
        $this->assertResponseCode(500);
        $this->assertContains('currently not supported', $this->getResponse()->getBody());
    }

    public function testToauthorActionWithMissingParam() {
        $this->dispatch('/frontdoor/mail/toauthor/');
        $this->assertResponseCode(500);
    }

    public function testToauthorActionWithInvalidParam() {
        $this->dispatch('/frontdoor/mail/toauthor/docId/invaliddocid');
        $this->assertResponseCode(500);
    }

    public function testToauthorActionWithoutContactableAuthor() {
        $this->dispatch('/frontdoor/mail/toauthor/docId/' . $this->documentId);
        $this->assertResponseCode(500);
    }

    public function testToauthorAction() {
        $this->dispatch('/frontdoor/mail/toauthor/docId/' . $this->authorDocumentId);
        $this->assertResponseCode(200);
    }

    public function testToauthorActionWithPost() {
        $this->getRequest()->setMethod('POST');
        $this->dispatch('/frontdoor/mail/toauthor/docId/' . $this->authorDocumentId);
        $this->assertResponseCode(200);
    }

    public function testToauthorActionWithInvalidPost() {
        $this->markTestIncomplete('TODO');
    }

    public function testToauthorActionWithValidPost() {
        $this->markTestIncomplete('TODO');
    }

}

