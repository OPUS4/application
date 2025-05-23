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

use Opus\Common\Date;
use Opus\Common\Document;
use Opus\Common\FileInterface;
use Opus\Common\Model\NotFoundException;
use Opus\Common\Person;
use Opus\Common\PersonInterface;
use Opus\Common\UserRole;

/**
 * TODO Where is the magic 23 coming from? Fix!
 */
class Review_Model_ClearDocumentsHelperTest extends ControllerTestCase
{
    /** @var string[] */
    protected $additionalResources = ['database'];

    /** @var int */
    private $documentId;

    /** @var PersonInterface */
    private $person;

    public function setUp(): void
    {
        parent::setUp();

        $document = $this->createTestDocument();
        $document->setServerState('unpublished');
        $document->setPersonReferee([]);
        $document->setEnrichment([]);
        $this->documentId = $document->store();

        $document = Document::get($this->documentId);
        $this->assertEquals(0, count($document->getPersonReferee()));
        $this->assertEquals(0, count($document->getEnrichment()));

        $person = Person::new();
        $person->setFirstName('John');
        $person->setLastName('Doe');
        $this->person = $person;
    }

    public function testClearDocument()
    {
        $helper = new Review_Model_ClearDocumentsHelper();
        $helper->clear([$this->documentId], 23, $this->person);

        $document = Document::get($this->documentId);
        $this->assertEquals('published', $document->getServerState());
        $this->assertEquals(1, count($document->getPersonReferee()));

        $enrichments = $document->getEnrichment();
        $this->assertEquals(1, count($enrichments));
        $this->assertEquals(23, $enrichments[0]->getValue());
    }

    public function testClearDocumentWithFile()
    {
        $path = '/tmp/opus4-test/' . uniqid() . "/src";
        mkdir($path, 0777, true);

        $filepath = $path . DIRECTORY_SEPARATOR . "foobar.pdf";
        touch($filepath);

        $document = Document::get($this->documentId);
        $document->addFile()
            ->setTempFile($filepath)
            ->setPathName('foobar.pdf')
            ->setLabel('Volltextdokument (PDF)');
        $document->store();

        $helper = new Review_Model_ClearDocumentsHelper();
        $helper->clear([$this->documentId], 23, $this->person);

        $document = Document::get($this->documentId);
        $this->assertEquals('published', $document->getServerState());
        $this->assertEquals(1, count($document->getPersonReferee()));

        $enrichments = $document->getEnrichment();
        $this->assertEquals(1, count($enrichments));
        $this->assertEquals(23, $enrichments[0]->getValue());

        // Check guest access for file
        $files = $document->getFile();
        $this->assertCount(1, $files);

        $file = $files[0];

        $guestRole = UserRole::fetchByName('guest');

        $this->assertContains($file->getId(), $guestRole->listAccessFiles());
    }

    public function testRejectDocument()
    {
        $helper = new Review_Model_ClearDocumentsHelper();
        $helper->reject([$this->documentId], 23, $this->person);

        $document = Document::get($this->documentId);
        $this->assertNotEquals('published', $document->getServerState());
        $this->assertEquals(1, count($document->getPersonReferee()));

        $enrichments = $document->getEnrichment();
        $this->assertEquals(1, count($enrichments));
        $this->assertEquals(23, $enrichments[0]->getValue());
    }

    public function testClearInvalidDocument()
    {
        $helper = new Review_Model_ClearDocumentsHelper();

        $this->expectException(NotFoundException::class);
        $helper->clear([$this->documentId + 100000], 23);
    }

    public function testRejectInvalidDocument()
    {
        $helper = new Review_Model_ClearDocumentsHelper();

        $this->expectException(NotFoundException::class);
        $helper->reject([$this->documentId + 100000], 23);
    }

    public function testClearDocumentWoPerson()
    {
        $helper = new Review_Model_ClearDocumentsHelper();
        $helper->clear([$this->documentId], 23);

        $document = Document::get($this->documentId);
        $this->assertEquals('published', $document->getServerState());
        $this->assertEquals(0, count($document->getPersonReferee()));

        $enrichments = $document->getEnrichment();
        $this->assertEquals(1, count($enrichments));
        $this->assertEquals(23, $enrichments[0]->getValue());
    }

    public function testRejectDocumentWoPerson()
    {
        $helper = new Review_Model_ClearDocumentsHelper();
        $helper->reject([$this->documentId], 23);

        $document = Document::get($this->documentId);
        $this->assertNotEquals('published', $document->getServerState());
        $this->assertEquals(0, count($document->getPersonReferee()));

        $enrichments = $document->getEnrichment();
        $this->assertEquals(1, count($enrichments));
        $this->assertEquals(23, $enrichments[0]->getValue());
    }

    public function testPublishedDateIsSetIfEmpty()
    {
        $document = Document::get($this->documentId);
        $this->assertNull($document->getPublishedDate());

        $helper = new Review_Model_ClearDocumentsHelper();
        $helper->clear([$this->documentId], 23, $this->person);

        $document = Document::get($this->documentId);
        $this->assertEquals('published', $document->getServerState());
        $this->assertEquals(1, count($document->getPersonReferee()));

        $publishedDate = $document->getPublishedDate();

        $this->assertNotNull($publishedDate);

        $today             = new DateTime('today');
        $publishedDateTime = $publishedDate->getDateTime($today->getTimezone()->getName());
        $publishedDateTime->setTime(0, 0, 0);

        $this->assertEquals(0, (int) $today->diff($publishedDateTime)->format('%R%a'));
    }

    public function testPublishedDateIsNotOverwritten()
    {
        // set PublishedDate to yesterday
        $document     = Document::get($this->documentId);
        $yesterday    = new DateTime('yesterday');
        $expectedDate = new Date($yesterday);
        $document->setPublishedDate($expectedDate);
        $document->store();

        $helper = new Review_Model_ClearDocumentsHelper();
        $helper->clear([$this->documentId], 23, $this->person);

        $document = Document::get($this->documentId);
        $this->assertEquals('published', $document->getServerState());
        $this->assertEquals(1, count($document->getPersonReferee()));

        $publishedDate = $document->getPublishedDate();
        $publishedDate->setHour(0);
        $publishedDate->setMinute(0);
        $publishedDate->setSecond(0);

        $this->assertNotNull($publishedDate);
        $this->assertEquals(0, $expectedDate->compare($publishedDate)); // still yesterday
    }

    public function testIsAddGuestAccessEnabled()
    {
        $helper = new Review_Model_ClearDocumentsHelper();

        $this->assertTrue($helper->isAddGuestAccessEnabled());
    }

    public function testIsAddGuestAccessEnabledNotConfigured()
    {
        $config = $this->getConfig();

        unset($config->workflow->stateChange->published);

        $this->assertFalse(isset($config->workflow->stateChange->published->addGuestAccess));

        $helper = new Review_Model_ClearDocumentsHelper();
        $helper->setConfig($config);

        $this->assertTrue($helper->isAddGuestAccessEnabled());
    }

    public function testIsAddGuestAccessEnabledFalse()
    {
        $this->adjustConfiguration([
            'workflow' => ['stateChange' => ['published' => ['addGuestAccess' => 0]]],
        ]);

        $helper = new Review_Model_ClearDocumentsHelper();

        $this->assertFalse($helper->isAddGuestAccessEnabled());
    }

    public function testNoExceptionIfAddGuestAccessIsDisabled()
    {
        $guestRole = UserRole::fetchByName('guest');

        $this->adjustConfiguration([
            'workflow' => ['stateChange' => ['published' => ['addGuestAccess' => 0]]],
        ]);

        $path = '/tmp/opus4-test/' . uniqid() . "/src";
        mkdir($path, 0777, true);

        $filepath = $path . DIRECTORY_SEPARATOR . "foobar.pdf";
        touch($filepath);

        $document = Document::get($this->documentId);
        $document->addFile()
            ->setTempFile($filepath)
            ->setPathName('foobar.pdf')
            ->setLabel('Volltextdokument (PDF)');
        $document->store();

        $files = $document->getFile();
        $this->assertCount(1, $files);

        $file = $files[0];
        $this->assertInstanceOf(FileInterface::class, $file);

        // Access to file for guest is automatically set because of **defaultAccessRole**
        $guestRole->removeAccessFile($file->getId());
        $guestRole->store();

        $helper = new Review_Model_ClearDocumentsHelper();
        $helper->clear([$this->documentId], 23, $this->person);

        $document = Document::get($this->documentId);
        $this->assertEquals('published', $document->getServerState());
        $this->assertEquals(1, count($document->getPersonReferee()));

        // Check no guest access for file
        $this->assertNotContains($file->getId(), $guestRole->listAccessFiles());
    }
}
