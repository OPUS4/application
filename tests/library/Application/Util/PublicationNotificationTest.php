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
 * @copyright   Copyright (c) 2018, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\Config;
use Opus\Common\Log;
use Opus\Common\Person;
use Opus\Common\Title;

class Application_Util_PublicationNotificationTest extends ControllerTestCase
{
    /** @var bool */
    protected $configModifiable = true;

    /** @var string */
    protected $additionalResources = 'database';

    /** @var Application_Util_PublicationNotification */
    protected $notification;

    /** @var Zend_Log */
    protected $logger;

    /** @var Zend_Config */
    protected $config;

    public function setUp(): void
    {
        parent::setUp();
        $this->notification = new Application_Util_PublicationNotification();
        $this->logger       = Log::get();
        // add required config keys
        $this->config                                              = Config::get();
        $this->config->notification->document->submitted->enabled  = self::CONFIG_VALUE_TRUE;
        $this->config->notification->document->published->enabled  = self::CONFIG_VALUE_TRUE;
        $this->config->notification->document->submitted->subject  = 'Dokument #%1$s eingestellt: %2$s : %3$s';
        $this->config->notification->document->published->subject  = 'Dokument #%1$s veröffentlicht: %2$s : %3$s';
        $this->config->notification->document->submitted->template = 'submitted.phtml';
        $this->config->notification->document->published->template = 'published.phtml';
        $this->config->notification->document->submitted->email    = "submitted@localhost";
        $this->config->notification->document->published->email    = "published@localhost";
    }

    public function testGetRecipientsForPublicationContextWithoutAuthorsAsRecipents()
    {
        $doc = $this->createTestDocument();
        $doc->store();
        $method     = $this->getMethod('getRecipients');
        $recipients = $method->invokeArgs($this->notification, [[], $doc]);
        $this->assertEquals(1, count($recipients));
        $this->assertEquals('published@localhost', $recipients[0]['name']);
        $this->assertEquals('published@localhost', $recipients[0]['address']);
    }

    public function testGetRecipientsForPublicationContextWithoutPublishedMailConfig()
    {
        $this->config->notification->document->published->email = "";
        $method                                                 = $this->getMethod('getRecipients');
        $recipients                                             = $method->invokeArgs($this->notification, []);
        $this->assertEquals(0, count($recipients));
    }

    public function testGetRecipientsForPublicationContextWithoutPublishedMailConfigButWithAuthors()
    {
        $this->config->notification->document->published->email = "";
        $doc = $this->createTestDocument();
        $doc->store();

        $authors = [
            ["name" => "Doe, John", "address" => "doe@localhost"],
            ["name" => "Doe, Jane", "address" => "jane.doe@localhost"],
        ];

        $method     = $this->getMethod('getRecipients');
        $recipients = $method->invokeArgs(
            $this->notification,
            [$authors, $doc]
        );

        $this->assertEquals(2, count($recipients));
        $this->assertEquals($recipients[0]['name'], "Doe, John");
        $this->assertEquals($recipients[0]['address'], "doe@localhost");
        $this->assertEquals($recipients[1]['name'], "Doe, Jane");
        $this->assertEquals($recipients[1]['address'], "jane.doe@localhost");
    }

    public function testGetRecipientsForPublicationContextAndMultipleAddressesWithoutAuthorsAsRecipents()
    {
        $this->config->notification->document->published->email = "published@localhost,publ@host.tld";
        $doc = $this->createTestDocument();
        $doc->store();
        $method     = $this->getMethod('getRecipients');
        $recipients = $method->invokeArgs(
            $this->notification,
            [[], $doc]
        );

        $this->assertEquals(2, count($recipients));
        $this->assertEquals($recipients[0]['name'], "published@localhost");
        $this->assertEquals($recipients[0]['address'], "published@localhost");
        $this->assertEquals($recipients[1]['name'], "publ@host.tld");
        $this->assertEquals($recipients[1]['address'], "publ@host.tld");
    }

    public function testGetRecipientsForPublicationContextWithAuthorsAsRecipents()
    {
        $this->config->notification->document->published->email = "published@localhost";
        $doc = $this->createTestDocument();
        $doc->store();

        $authors = [
            ["name" => "Doe, John", "address" => "doe@localhost"],
            ["name" => "Doe, Jane", "address" => "jane.doe@localhost"],
        ];

        $method     = $this->getMethod('getRecipients');
        $recipients = $method->invokeArgs(
            $this->notification,
            [$authors, $doc]
        );

        $this->assertEquals(3, count($recipients));
        $this->assertEquals($recipients[0]['name'], "published@localhost");
        $this->assertEquals($recipients[0]['address'], "published@localhost");
        $this->assertEquals($recipients[1]['name'], "Doe, John");
        $this->assertEquals($recipients[1]['address'], "doe@localhost");
        $this->assertEquals($recipients[2]['name'], "Doe, Jane");
        $this->assertEquals($recipients[2]['address'], "jane.doe@localhost");
    }

    public function testGetRecipientsForPublicationContextWithSubmitterAsRecipient()
    {
        $this->config->notification->document->published->email = "published@localhost";
        $doc       = $this->createTestDocument();
        $submitter = Person::new();
        $submitter->setFirstName('John');
        $submitter->setLastName('Submitter');
        $submitter->setEmail('john.submitter@localhost.de');
        $doc->addPersonSubmitter($submitter);
        $doc->store();

        $authors = [
            ["name" => "Doe, John", "address" => "doe@localhost"],
            ["name" => "Doe, Jane", "address" => "jane.doe@localhost"],
        ];

        $method     = $this->getMethod('getRecipients');
        $recipients = $method->invokeArgs(
            $this->notification,
            [$authors, $doc]
        );

        $this->assertEquals(4, count($recipients));
        $this->assertEquals($recipients[0]['name'], "published@localhost");
        $this->assertEquals($recipients[0]['address'], "published@localhost");
        $this->assertEquals($recipients[1]['name'], "Doe, John");
        $this->assertEquals($recipients[1]['address'], "doe@localhost");
        $this->assertEquals($recipients[2]['name'], "Doe, Jane");
        $this->assertEquals($recipients[2]['address'], "jane.doe@localhost");
        $this->assertEquals($recipients[3]['name'], "Submitter, John");
        $this->assertEquals($recipients[3]['address'], "john.submitter@localhost.de");
    }

    public function testGetRecipientsForPublicationContextWithSubmitterWithoutMailAddressAsRecipient()
    {
        $this->config->notification->document->published->email = "published@localhost";
        $doc       = $this->createTestDocument();
        $submitter = Person::new();
        $submitter->setFirstName('John');
        $submitter->setLastName('Submitter');
        $doc->addPersonSubmitter($submitter);
        $doc->store();

        $method     = $this->getMethod('getRecipients');
        $recipients = $method->invokeArgs(
            $this->notification,
            [[], $doc]
        );

        $this->assertEquals(1, count($recipients));
        $this->assertEquals($recipients[0]['name'], "published@localhost");
        $this->assertEquals($recipients[0]['address'], "published@localhost");
    }

    public function testGetRecipientsForPublicationContextWithoutSubmitter()
    {
        $doc = $this->createTestDocument();
        $doc->setLanguage("eng");

        $title = Title::new();
        $title->setValue("Test Document");
        $title->setLanguage("eng");
        $doc->addTitleMain($title);

        $submitter = Person::new();
        $submitter->setFirstName("John");
        $submitter->setLastName("Submitter");
        $submitter->setEmail("sub@localhost.de");
        $doc->addPersonSubmitter($submitter);

        $doc->store();
        $method     = $this->getMethod('getRecipients');
        $recipients = $method->invokeArgs(
            $this->notification,
            [[["name" => "foo", "address" => "foo@localhost"]], $doc, false]
        );
        $this->assertEquals(2, count($recipients));
        $this->assertEquals("published@localhost", $recipients[0]["name"]);
        $this->assertEquals("published@localhost", $recipients[0]["address"]);
        $this->assertEquals("foo", $recipients[1]["name"]);
        $this->assertEquals("foo@localhost", $recipients[1]["address"]);
    }

    /**
     * @param string $methodName
     * @return ReflectionMethod
     * @throws ReflectionException
     */
    protected function getMethod($methodName)
    {
        $class  = new ReflectionClass('Application_Util_PublicationNotification');
        $method = $class->getMethod($methodName);
        $method->setAccessible(true);
        return $method;
    }

    public function testGetPublicationMailBodyWithEmptyAuthorsAndEmptyTitle()
    {
        $method = $this->getMethod('getMailBody');
        $body   = $method->invokeArgs(
            $this->notification,
            ["123", [], "", "http://localhost/foo/1"]
        );
        $this->assertContains("Autor(en): n/a", $body);
        $this->assertContains("Titel: n/a", $body);
        $this->assertContains("Dokument-ID: 123", $body);
        $this->assertContains("http://localhost/foo/1", $body);
        $this->assertContains("Folgendes Dokument wurde auf dem OPUS4-Dokumentenserver freigegeben", $body);
    }

    public function testGetPublicationMailBodyWithTwoAuthorsAndNonEmptyTitle()
    {
        $method = $this->getMethod('getMailBody');
        $body   = $method->invokeArgs(
            $this->notification,
            ["123", ["Doe, John", "Doe, Jane"], "Test Title", "http://localhost/foo/1"]
        );
        $this->assertContains("Autor(en):\nDoe, John\nDoe, Jane", $body);
        $this->assertContains("Titel: Test Title", $body);
        $this->assertContains("Dokument-ID: 123", $body);
        $this->assertContains("http://localhost/foo/1", $body);
        $this->assertContains("Folgendes Dokument wurde auf dem OPUS4-Dokumentenserver freigegeben", $body);
    }

    /**
     * Diese Testmethode hat keine Assertions. Sie stellt lediglich sicher, dass alle Codeteile
     * der Funktion prepareMail durchlaufen werden.
     */
    public function testPrepareMailForPublicationContext()
    {
        $doc = $this->createTestDocument();
        $doc->setLanguage("eng");

        $title = Title::new();
        $title->setValue("Test Document");
        $title->setLanguage("eng");
        $doc->addTitleMain($title);

        $author = Person::new();
        $author->setFirstName("John");
        $author->setLastName("Doe");
        $doc->addPersonAuthor($author);

        $author = Person::new();
        $author->setFirstName("John With Address");
        $author->setLastName("Doe");
        $author->setEmail("doe@localhost.de");
        $doc->addPersonAuthor($author);

        $submitter = Person::new();
        $submitter->setFirstName("John");
        $submitter->setLastName("Submitter");
        $submitter->setEmail("sub@localhost.de");
        $doc->addPersonSubmitter($submitter);

        $doc->store();
        $this->notification->prepareMail($doc, 'http://localhost/foo/1');
    }

    public function testGetPublicationMailSubjectWithEmptyAuthorsAndEmptyTitle()
    {
        $document = $this->createTestDocument();
        $docId    = $document->store();

        $method  = $this->getMethod('getMailSubject');
        $subject = $method->invokeArgs(
            $this->notification,
            [$document, []]
        );
        $this->assertEquals("Dokument #$docId veröffentlicht: n/a : n/a", $subject);
    }

    public function testGetPublicationMailSubjectWithOneAuthor()
    {
        $document = $this->createTestDocument();
        $title    = $document->addTitleMain();
        $title->setLanguage('deu');
        $title->setValue('Test Document');
        $docId = $document->store();

        $method  = $this->getMethod('getMailSubject');
        $subject = $method->invokeArgs(
            $this->notification,
            [$document, ["Doe, John"]]
        );
        $this->assertEquals("Dokument #$docId veröffentlicht: Doe, John : Test Document", $subject);
    }

    public function testGetPublicationMailSubjectWithTwoAuthors()
    {
        $document = $this->createTestDocument();
        $title    = $document->addTitleMain();
        $title->setLanguage('deu');
        $title->setValue('Test Document');
        $docId = $document->store();

        $method  = $this->getMethod('getMailSubject');
        $subject = $method->invokeArgs(
            $this->notification,
            [$document, ["Doe, John", "Doe, Jane"]]
        );
        $this->assertEquals("Dokument #$docId veröffentlicht: Doe, John ; Doe, Jane : Test Document", $subject);
    }
}
