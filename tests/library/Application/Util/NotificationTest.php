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

use Opus\Common\Job;
use Opus\Common\Log;
use Opus\Common\Person;
use Opus\Common\Title;
use Opus\Job\MailNotification;

class Application_Util_NotificationTest extends ControllerTestCase
{
    /** @var bool */
    protected $configModifiable = true;

    /** @var string */
    protected $additionalResources = 'database';

    /** @var Application_Util_Notification */
    protected $notification;

    /** @var Zend_Log */
    protected $logger;

    /** @var Zend_Config */
    protected $config;

    public function setUp(): void
    {
        parent::setUp();
        $this->notification = new Application_Util_Notification();
        $this->logger       = Log::get();
        // add required config keys
        $this->config                                              = $this->getConfig();
        $this->config->notification->document->submitted->enabled  = self::CONFIG_VALUE_TRUE;
        $this->config->notification->document->published->enabled  = self::CONFIG_VALUE_TRUE;
        $this->config->notification->document->submitted->subject  = 'Dokument #%1$s eingestellt: %2$s : %3$s';
        $this->config->notification->document->published->subject  = 'Dokument #%1$s veröffentlicht: %2$s : %3$s';
        $this->config->notification->document->submitted->template = 'submitted.phtml';
        $this->config->notification->document->published->template = 'published.phtml';
        $this->config->notification->document->submitted->email    = "submitted@localhost";
        $this->config->notification->document->published->email    = "published@localhost";
    }

    /**
     * Diese Testmethode hat keine Assertions. Sie stellt lediglich sicher, dass alle Codeteile
     * der Funktion prepareMail durchlaufen werden.
     */
    public function testPrepareMailForSubmissionContext()
    {
        $doc = $this->createTestDocument();
        $doc->setLanguage("eng");

        $title = Title::new();
        $title->setValue("Test Document");
        $title->setLanguage("eng");
        $doc->addTitleMain($title);

        $doc->store();
        $this->notification->prepareMail($doc, 'http://localhost/foo/1');
    }

    public function testGetSubmissionMailSubjectWithEmptyAuthorsAndEmptyTitle()
    {
        $document = $this->createTestDocument();
        $docId    = $document->store();

        $method  = $this->getMethod('getMailSubject');
        $subject = $method->invokeArgs($this->notification, [$document, []]);
        $this->assertEquals("Dokument #$docId eingestellt: n/a : n/a", $subject);
    }

    public function testGetSubmissionMailSubjectWithOneAuthor()
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
        $this->assertEquals("Dokument #$docId eingestellt: Doe, John : Test Document", $subject);
    }

    public function testGetSubmissionMailSubjectWithTwoAuthors()
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
        $this->assertEquals("Dokument #$docId eingestellt: Doe, John ; Doe, Jane : Test Document", $subject);
    }

    public function testGetSubmissionMailBodyWithEmptyAuthorsAndEmptyTitle()
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
        $this->assertContains("Ein neues Dokument wurde auf Ihrem OPUS4-Dokumentenserver hochgeladen", $body);
    }

    public function testGetSubmissionMailBodyWithTwoAuthorsAndNonEmptyTitle()
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
        $this->assertContains("Ein neues Dokument wurde auf Ihrem OPUS4-Dokumentenserver hochgeladen", $body);
    }

    public function testGetSubmissionMailBodyWithUnknownTemplateFile()
    {
        $this->config->notification->document->submitted->template = 'does-not-exist.phtml';
        $method = $this->getMethod('getMailBody');
        $body   = $method->invokeArgs(
            $this->notification,
            ["123", [], "Test Document", "http://localhost/foo/1"]
        );
        $this->assertNull($body);
    }

    public function testGetRecipientsForSubmissionContext()
    {
        $method     = $this->getMethod('getRecipients');
        $recipients = $method->invoke($this->notification);
        $this->assertEquals(1, count($recipients));
        $this->assertEquals($recipients[0]['name'], "submitted@localhost");
        $this->assertEquals($recipients[0]['address'], "submitted@localhost");
    }

    public function testGetRecipientsForSubmissionContextWithoutSubmittedMailConfig()
    {
        $this->config->notification->document->submitted->email = "";
        $method                                                 = $this->getMethod('getRecipients');
        $recipients                                             = $method->invoke($this->notification);
        $this->assertEquals(0, count($recipients));
    }

    public function testGetRecipientsForSubmissionContextAndMultipleAddresses()
    {
        $this->config->notification->document->submitted->email = "submitted@localhost,sub@host.tld";
        $method                                                 = $this->getMethod('getRecipients');
        $recipients                                             = $method->invoke($this->notification);
        $this->assertEquals(2, count($recipients));
        $this->assertEquals($recipients[0]['name'], "submitted@localhost");
        $this->assertEquals($recipients[0]['address'], "submitted@localhost");
        $this->assertEquals($recipients[1]['name'], "sub@host.tld");
        $this->assertEquals($recipients[1]['address'], "sub@host.tld");
    }

    /**
     * Diese Testmethode hat keine Assertions. Sie stellt lediglich sicher, dass alle Codeteile
     * der Funktion prepareMail durchlaufen werden.
     */
    public function testPrepareMailWithTwoOptionalArgs()
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
        $author->setEmail("john@localhost.de");
        $doc->addPersonAuthor($author);

        $author = Person::new();
        $author->setFirstName("Jane");
        $author->setLastName("Doe");
        $author->setEmail("jane@localhost.de");
        $doc->addPersonAuthor($author);

        $submitter = Person::new();
        $submitter->setFirstName("John");
        $submitter->setLastName("Submitter");
        $submitter->setEmail("sub@localhost.de");
        $doc->addPersonSubmitter($submitter);

        $doc->store();
        $this->notification->prepareMail(
            $doc,
            'http://localhost/foo/1',
            false,
            [false, true]
        );
    }

    public function testCreateWorkerJobIfAsyncEnabled()
    {
        // TODO use Job::deleteAll() - requires opus4admin permissions !
        $jobs = Job::getAll();

        if (! empty($jobs)) {
            foreach ($jobs as $job) {
                $job->delete();
            }
        }

        $this->config->merge(new Zend_Config(['runjobs' => ['asynchronous' => self::CONFIG_VALUE_TRUE]]));
        $this->assertEquals(0, Job::getCount(), 'test data changed.');

        $doc = $this->createTestDocument();
        $doc->setLanguage("eng");

        $title = Title::new();
        $title->setValue("Test Document");
        $title->setLanguage("eng");
        $doc->addTitleMain($title);

        $doc->store();
        $this->notification->prepareMail($doc, 'http://localhost/foo/1');

        $mailJobs = Job::getByLabels([MailNotification::LABEL]);

        $this->assertEquals(1, count($mailJobs), 'Expected 1 mail job');

        $jobs = Job::getAll();

        if (! empty($jobs)) {
            foreach ($jobs as $job) {
                $job->delete();
            }
        }
    }

    /**
     * @param string $methodName
     * @return ReflectionMethod
     * @throws ReflectionException
     */
    protected function getMethod($methodName)
    {
        $class  = new ReflectionClass('Application_Util_Notification');
        $method = $class->getMethod($methodName);
        $method->setAccessible(true);
        return $method;
    }

    public function testGetSubjectTemplate()
    {
        $template = $this->notification->getSubjectTemplate();
        $this->assertNotNull($template);
        $this->assertNotEmpty($template);
    }

    public function testGetMailSubject()
    {
        $document = $this->createTestDocument();
        $document->setLanguage('deu');

        $title = $document->addTitleMain();
        $title->setLanguage('deu');
        $title->setValue('Testdokument');

        $docId = $document->store();

        $subject = $this->notification->getMailSubject($document, []);

        $this->assertNotContains($title->__toString(), $subject);
        $this->assertContains('Testdokument', $subject);
        $this->assertContains($docId, $subject);
    }
}
