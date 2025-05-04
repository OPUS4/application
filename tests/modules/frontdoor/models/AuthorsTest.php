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
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\Document;
use Opus\Common\Person;
use Opus\Common\Title;

class Frontdoor_Model_AuthorsTest extends ControllerTestCase
{
    /** @var string[] */
    protected $additionalResources = ['database'];

    /** @var int */
    private $documentId;

    /** @var int */
    private $author1Id;

    /** @var int */
    private $author2Id;

    /** @var int */
    private $author3Id;

    /** @var int */
    private $author4Id;

    /** @var int */
    private $unpublishedDocumentId;

    public function setUp(): void
    {
        parent::setUp();
        $document = $this->createTestDocument();
        $document->setServerState('published');
        $document->setType('testtype');
        $document->setLanguage('deu');

        $title = Title::new();
        $title->setValue('testtitle');
        $title->setLanguage('deu');
        $document->setTitleMain($title);

        $author1 = Person::new();
        $author1->setFirstName('John');
        $author1->setLastName('Doe');
        $author1->setEmail('doe@example.org');
        $this->author1Id = $author1->store();

        $linkPerson1 = $document->addPersonAuthor($author1);
        $linkPerson1->setAllowEmailContact('1');

        $author2 = Person::new();
        $author2->setFirstName('Jane');
        $author2->setLastName('Doe');
        $this->author2Id = $author2->store();

        $linkPerson2 = $document->addPersonAuthor($author2);
        $linkPerson2->setAllowEmailContact('0');

        $author3 = Person::new();
        $author3->setFirstName('Jimmy');
        $author3->setLastName('Doe');
        $this->author3Id = $author3->store();

        $linkPerson3 = $document->addPersonAuthor($author3);
        $linkPerson3->setAllowEmailContact('1');

        $author4 = Person::new();
        $author4->setFirstName('Foo');
        $author4->setLastName('Bar');
        $author4->setEmail('foo@bar.de');
        $this->author4Id = $author4->store();

        $linkPerson4 = $document->addPersonAuthor($author4);
        $linkPerson4->setAllowEmailContact('1');

        $this->documentId = $document->store();
        $this->assertNotNull($this->documentId);

        $this->assertNotNull($this->author1Id);
        $this->assertNotEquals('', $this->author1Id);

        $this->assertNotNull($this->author2Id);
        $this->assertNotEquals('', $this->author2Id);

        $this->assertNotNull($this->author3Id);
        $this->assertNotEquals('', $this->author3Id);

        $this->assertNotNull($this->author4Id);
        $this->assertNotEquals('', $this->author4Id);

        $document = $this->createTestDocument();
        $document->setServerState('unpublished');
        $this->unpublishedDocumentId = $document->store();
        $this->assertNotNull($this->unpublishedDocumentId);
    }

    public function testConstructor()
    {
        $model = new Frontdoor_Model_Authors($this->documentId);
        $this->assertNotNull($model);
    }

    public function testGetAuthors()
    {
        $model = new Frontdoor_Model_Authors($this->documentId);
        $this->assertNotNull($model);
        $authors = $model->getAuthors();
        $this->assertEquals(4, count($authors));
        $author = $authors[0];
        $this->assertEquals('Doe, John', $author['name']);
        $author = $authors[1];
        $this->assertEquals('Doe, Jane', $author['name']);
        $author = $authors[2];
        $this->assertEquals('Doe, Jimmy', $author['name']);
        $author = $authors[3];
        $this->assertEquals('Bar, Foo', $author['name']);
    }

    public function testGetContactableAuthors()
    {
        $model = new Frontdoor_Model_Authors($this->documentId);
        $this->assertNotNull($model);
        $authors = $model->getContactableAuthors();
        $this->assertEquals(2, count($authors));
        $author = $authors[0];
        $this->assertEquals('Doe, John', $author['name']);
        $author = $authors[1];
        $this->assertEquals('Bar, Foo', $author['name']);
    }

    public function testGetDocument()
    {
        $model = new Frontdoor_Model_Authors($this->documentId);
        $this->assertNotNull($model);
        $this->assertEquals($this->documentId, $model->getDocument()->getId());
    }

    public function testSendMail()
    {
        $model = new Frontdoor_Model_Authors($this->documentId);
        $this->assertNotNull($model);
        $mailProvider = new SendMailMock();
        $model->sendMail(
            $mailProvider,
            'opus@kobv.de',
            'opus',
            'example subject',
            'example text',
            [
                $this->author1Id => '1',
                $this->author2Id => '1',
                $this->author3Id => 1,
                $this->author4Id => 1,
            ]
        );
        $addresses = $mailProvider->getAddress();
        $this->assertTrue(is_array($addresses));
        foreach ($addresses as $address) {
            $this->assertTrue(is_array($address));
            $this->assertTrue(array_key_exists('name', $address));
            $this->assertTrue(array_key_exists('address', $address));
        }
        $this->assertEquals(2, count($addresses));
        $this->assertEquals('doe@example.org', $addresses[0]['address']);
        $this->assertEquals('Doe, John', $addresses[0]['name']);
        $this->assertEquals('foo@bar.de', $addresses[1]['address']);
        $this->assertEquals('Bar, Foo', $addresses[1]['name']);
    }

    public function testUnpublishedDocumentID()
    {
        $this->markTestIncomplete('TODO: ensure that this method is called with guest privileges');
        $this->expectException(Frontdoor_Model_Exception::class);
        $this->expectExceptionMessage('access to requested document is forbidden');
        new Frontdoor_Model_Authors($this->unpublishedDocumentId);
    }

    public function testUnknownDocumentID()
    {
        $this->expectException(Frontdoor_Model_Exception::class);
        $this->expectExceptionMessage('invalid value for parameter docId given');
        new Frontdoor_Model_Authors('foo');
    }

    public function testPublishedDocument()
    {
        $doc   = Document::get($this->documentId);
        $model = new Frontdoor_Model_Authors($doc);
        $this->assertNotNull($model);
    }

    public function testUnpublishedDocument()
    {
        $this->markTestIncomplete('TODO: ensure that this method is called with guest privileges');
        $doc = Document::get($this->unpublishedDocumentId);
        $this->expectException(Frontdoor_Model_Exception::class);
        $this->expectExceptionMessage('access to requested document is forbidden');
        new Frontdoor_Model_Authors($doc);
    }

    public function testGetContactableAuthorsNoAuthors()
    {
        $doc = $this->createTestDocument();

        $author = Person::new();
        $author->setFirstName('John');
        $author->setLastName('Doe');
        $author->setEmail('doe@example.org');
        $this->authorId = $author->store();

        $linkPerson = $doc->addPersonAuthor($author);
        $linkPerson->setAllowEmailContact(false);

        $doc->store();

        $model = new Frontdoor_Model_Authors($doc);

        $authors = $model->getContactableAuthors();

        $this->assertCount(0, $authors);
    }
}
