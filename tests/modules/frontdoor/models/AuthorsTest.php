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
 * @copyright   Copyright (c) 2008-2011, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class Frontdoor_Model_AuthorsTest extends ControllerTestCase {

    private $documentId;
    private $author1Id;
    private $author2Id;
    private $author3Id;
    private $author4Id;

    public function setUp() {
        parent::setUp();
        $document = new Opus_Document();
        $document->setServerState('published');
        $document->setType('testtype');

        $title = new Opus_Title();
        $title->setValue('testtitle');
        $document->setTitleMain($title);

        $author1 = new Opus_Person();
        $author1->setFirstName('John');
        $author1->setLastName('Doe');
        $author1->setEmail('doe@example.org');
        $this->author1Id = $author1->store();

        $link_person1 = $document->addPersonAuthor($author1);
        $link_person1->setAllowEmailContact('1');

        $author2 = new Opus_Person();
        $author2->setFirstName('Jane');
        $author2->setLastName('Doe');
        $this->author2Id = $author2->store();

        $link_person2 = $document->addPersonAuthor($author2);
        $link_person2->setAllowEmailContact('0');   

        $author3 = new Opus_Person();
        $author3->setFirstName('Jimmy');
        $author3->setLastName('Doe');
        $this->author3Id = $author3->store();

        $link_person3 = $document->addPersonAuthor($author3);
        $link_person3->setAllowEmailContact('1');

        $author4 = new Opus_Person();
        $author4->setFirstName('Foo');
        $author4->setLastName('Bar');
        $author4->setEmail('foo@bar.de');
        $this->author4Id = $author4->store();
        
        $link_person4 = $document->addPersonAuthor($author4);
        $link_person4->setAllowEmailContact('1');

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
    }

    public function tearDown() {
        $document = new Opus_Document($this->documentId);
        $document->deletePermanent();

        $person = new Opus_Person($this->author1Id);
        $person->delete();

        $person = new Opus_Person($this->author2Id);
        $person->delete();

        $person = new Opus_Person($this->author3Id);
        $person->delete();

        $person = new Opus_Person($this->author4Id);
        $person->delete();
        
        parent::tearDown();
    }

    public function testConstructor() {
        $model = new Frontdoor_Model_Authors($this->documentId);
        $this->assertNotNull($model);
    }

    public function testGetAuthors() {
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

    public function testGetContactableAuthors() {
        $model = new Frontdoor_Model_Authors($this->documentId);
        $this->assertNotNull($model);
        $authors = $model->getContactableAuthors();
        $this->assertEquals(2, count($authors));
        $author = $authors[0];
        $this->assertEquals('Doe, John', $author['name']);
        $author = $authors[1];
        $this->assertEquals('Bar, Foo', $author['name']);
    }

    public function testGetDocument() {
        $model = new Frontdoor_Model_Authors($this->documentId);
        $this->assertNotNull($model);
        $this->assertEquals($this->documentId, $model->getDocument()->getId());
    }

    public function testSendMail() {
        $model = new Frontdoor_Model_Authors($this->documentId);
        $this->assertNotNull($model);
        $mailProvider = new SendMailMock();
        $model->sendMail(
                $mailProvider,
                'opus@kobv.de',
                'opus',
                'example subject',
                'example text',
                array(
                    $this->author1Id => '1',
                    $this->author2Id => '1',
                    $this->author3Id => '1',
                    $this->author4Id => '1'));
        $addresses = $mailProvider->getAddress();
        $this->assertEquals(2, count($addresses));
        $this->assertEquals('doe@example.org', $addresses[0]['address']);
        $this->assertEquals('Doe, John', $addresses[0]['name']);
        $this->assertEquals('foo@bar.de', $addresses[1]['address']);
        $this->assertEquals('Bar, Foo', $addresses[1]['name']);
    }
}
?>