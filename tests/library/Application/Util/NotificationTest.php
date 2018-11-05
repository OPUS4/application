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
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2018, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

class Application_Util_NotificationTest extends ControllerTestCase
{

    protected $notification;

    protected $logger;

    protected $config;

    public function setUp()
    {
        parent::setUp();
        $this->notification = new Application_Util_Notification();
        $this->logger = Zend_Registry::get('Zend_Log');
        // add required config keys
        $this->config = Zend_Registry::get('Zend_Config');
        $this->config->notification->document->submitted->enabled = 1;
        $this->config->notification->document->published->enabled = 1;
        $this->config->notification->document->submitted->subject = 'Dokument #%1$s eingestellt: %2$s : %3$s';
        $this->config->notification->document->published->subject = 'Dokument #%1$s veröffentlicht: %2$s : %3$s';
        $this->config->notification->document->submitted->template = 'submitted.phtml';
        $this->config->notification->document->published->template = 'published.phtml';
        $this->config->notification->document->submitted->email = "submitted@localhost";
        $this->config->notification->document->published->email = "published@localhost";
    }

    /**
     * Diese Testmethode hat keine Assertions. Sie stellt lediglich sicher, dass alle Codeteile
     * der Funktion prepareMail durchlaufen werden.
     */
    public function testPrepareMailForSubmissionContext()
    {
        $doc = $this->createTestDocument();
        $doc->setLanguage("eng");

        $title = new Opus_Title();
        $title->setValue("Test Document");
        $title->setLanguage("eng");
        $doc->addTitleMain($title);

        $doc->store();
        $this->notification->prepareMail($doc, 'http://localhost/foo/1');
    }

    public function testGetSubmissionMailSubjectWithEmptyAuthorsAndEmptyTitle()
    {
        $method = $this->getMethod('getMailSubject');
        $subject = $method->invokeArgs($this->notification, ["123", [], ""]);
        $this->assertEquals("Dokument #123 eingestellt: n/a : n/a", $subject);
    }

    public function testGetSubmissionMailSubjectWithOneAuthor()
    {
        $method = $this->getMethod('getMailSubject');
        $subject = $method->invokeArgs(
            $this->notification,
            ["123", ["Doe, John"], "Test Document"]
        );
        $this->assertEquals("Dokument #123 eingestellt: Doe, John : Test Document", $subject);
    }

    public function testGetSubmissionMailSubjectWithTwoAuthors()
    {
        $method = $this->getMethod('getMailSubject');
        $subject = $method->invokeArgs(
            $this->notification,
            ["123", ["Doe, John", "Doe, Jane"], "Test Document"]
        );
        $this->assertEquals("Dokument #123 eingestellt: Doe, John ; Doe, Jane : Test Document", $subject);
    }

    public function testGetSubmissionMailBodyWithEmptyAuthorsAndEmptyTitle()
    {
        $method = $this->getMethod('getMailBody');
        $body = $method->invokeArgs(
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
        $body = $method->invokeArgs(
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
        $body = $method->invokeArgs(
            $this->notification,
            ["123", [], "Test Document", "http://localhost/foo/1"]
        );
        $this->assertNull($body);
    }

    public function testGetRecipientsForSubmissionContext()
    {
        $method = $this->getMethod('getRecipients');
        $recipients = $method->invoke($this->notification);
        $this->assertEquals(1, count($recipients));
        $this->assertEquals($recipients[0]['name'], "submitted@localhost");
        $this->assertEquals($recipients[0]['address'], "submitted@localhost");
    }

    public function testGetRecipientsForSubmissionContextWithoutSubmittedMailConfig()
    {
        $this->config->notification->document->submitted->email = "";
        $method = $this->getMethod('getRecipients');
        $recipients = $method->invoke($this->notification);
        $this->assertEquals(0, count($recipients));
    }

    public function testGetRecipientsForSubmissionContextAndMultipleAddresses()
    {
        $this->config->notification->document->submitted->email = "submitted@localhost,sub@host.tld";
        $method = $this->getMethod('getRecipients');
        $recipients = $method->invoke($this->notification);
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

        $title = new Opus_Title();
        $title->setValue("Test Document");
        $title->setLanguage("eng");
        $doc->addTitleMain($title);

        $author = new Opus_Person();
        $author->setFirstName("John");
        $author->setLastName("Doe");
        $author->setEmail("john@localhost.de");
        $doc->addPersonAuthor($author);

        $author = new Opus_Person();
        $author->setFirstName("Jane");
        $author->setLastName("Doe");
        $author->setEmail("jane@localhost.de");
        $doc->addPersonAuthor($author);

        $submitter = new Opus_Person();
        $submitter->setFirstName("John");
        $submitter->setLastName("Submitter");
        $submitter->setEmail("sub@localhost.de");
        $doc->addPersonSubmitter($submitter);

        $doc->store();
        $this->notification->prepareMail(
            $doc, 'http://localhost/foo/1', false, [false, true]
        );
    }

    public function testCreateWorkerJobIfAsyncEnabled()
    {
        // TODO use Opus_Job::deleteAll() - requires opus4admin permissions !
        $jobs = Opus_Job::getAll();

        if(!empty($jobs)) {
            foreach($jobs as $job) {
                $job->delete();
            }
        }

        $this->config->merge(new Zend_Config(['runjobs' => ['asynchronous' => 1]]));
        $this->assertEquals(0, Opus_Job::getCount(), 'test data changed.');

        $doc = $this->createTestDocument();
        $doc->setLanguage("eng");

        $title = new Opus_Title();
        $title->setValue("Test Document");
        $title->setLanguage("eng");
        $doc->addTitleMain($title);

        $doc->store();
        $this->notification->prepareMail($doc, 'http://localhost/foo/1');

        $mailJobs = Opus_Job::getByLabels([Opus_Job_Worker_MailNotification::LABEL]);

        $this->assertEquals(1, count($mailJobs), 'Expected 1 mail job');

        $jobs = Opus_Job::getAll();

        if(!empty($jobs)) {
            foreach($jobs as $job) {
                $job->delete();
            }
        }
    }

    protected function getMethod($methodName)
    {
        $class = new ReflectionClass('Application_Util_Notification');
        $method = $class->getMethod($methodName);
        $method->setAccessible(true);
        return $method;
    }
}
