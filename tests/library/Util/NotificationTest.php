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
 * @copyright   Copyright (c) 2008-2012, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class Util_NotificationTest extends ControllerTestCase {

    private $notification;

    private $logger;

    private $config;

    public function setUp() {
        parent::setUp();
        $this->notification = new Util_Notification();
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
    public function testPrepareMailForSubmissionContext() {       
        $doc = new Opus_Document();
        $doc->setLanguage("eng");

        $title = new Opus_Title();
        $title->setValue("Test Document");
        $title->setLanguage("eng");
        $doc->addTitleMain($title);

        $doc->store();
        $this->notification->prepareMail($doc, Util_Notification::SUBMISSION, 'http://localhost/foo/1');
        $doc->deletePermanent();        
    }

    /**
     * Diese Testmethode hat keine Assertions. Sie stellt lediglich sicher, dass alle Codeteile
     * der Funktion prepareMail durchlaufen werden.
     */
    public function testPrepareMailForPublicationContext() {        
        $doc = new Opus_Document();
        $doc->setLanguage("eng");

        $title = new Opus_Title();
        $title->setValue("Test Document");
        $title->setLanguage("eng");
        $doc->addTitleMain($title);

        $author = new Opus_Person();
        $author->setFirstName("John");
        $author->setLastName("Doe");
        $doc->addPersonAuthor($author);

        $author = new Opus_Person();
        $author->setFirstName("John With Address");
        $author->setLastName("Doe");
        $author->setEmail("doe@localhost.de");
        $doc->addPersonAuthor($author);

        $submitter = new Opus_Person();
        $submitter->setFirstName("John");
        $submitter->setLastName("Submitter");
        $submitter->setEmail("sub@localhost.de");
        $doc->addPersonSubmitter($submitter);

        $doc->store();
        $this->notification->prepareMail($doc, Util_Notification::PUBLICATION, 'http://localhost/foo/1');
        $doc->deletePermanent();
    }

    public function testValidateContextWithSubmissionContext() {
        $method = $this->getMethod('validateContext');
        $this->assertTrue($method->invokeArgs($this->notification, array(Util_Notification::SUBMISSION)));
    }

    public function testValidateContextWithSubmissionContextAndDisabledSubmissionNotification() {
        $this->config->notification->document->submitted->enabled = 0;
        $method = $this->getMethod('validateContext');
        $this->assertFalse($method->invokeArgs($this->notification, array(Util_Notification::SUBMISSION)));
    }

    public function testValidateContextWithPublicationContext() {
        $method = $this->getMethod('validateContext');
        $this->assertTrue($method->invokeArgs($this->notification, array(Util_Notification::PUBLICATION)));
    }

    public function testValidateContextWithPublishedContextAndDisabledPublicationNotification() {
        $this->config->notification->document->published->enabled = 0;
        $method = $this->getMethod('validateContext');
        $this->assertFalse($method->invokeArgs($this->notification, array(Util_Notification::PUBLICATION)));
    }

    public function testValidateContextWithUnknownContext() {
        $method = $this->getMethod('validateContext');
        $this->assertFalse($method->invokeArgs($this->notification, array('deleted')));
    }

    public function testValidateContextWithContextNull() {
        $method = $this->getMethod('validateContext');
        $this->assertFalse($method->invokeArgs($this->notification, array(null)));
    }

    public function testGetSubmissionMailSubjectWithEmptyAuthorsAndEmptyTitle() {
        $method = $this->getMethod('getMailSubject');
        $subject = $method->invokeArgs($this->notification, array(Util_Notification::SUBMISSION, "123", array(), ""));
        $this->assertEquals("Dokument #123 eingestellt: n/a : n/a", $subject);
    }

    public function testGetSubmissionMailSubjectWithOneAuthor() {
        $method = $this->getMethod('getMailSubject');
        $subject = $method->invokeArgs($this->notification, array(Util_Notification::SUBMISSION, "123", array("Doe, John"), "Test Document"));
        $this->assertEquals("Dokument #123 eingestellt: Doe, John : Test Document", $subject);
    }

    public function testGetSubmissionMailSubjectWithTwoAuthors() {
        $method = $this->getMethod('getMailSubject');
        $subject = $method->invokeArgs($this->notification, array(Util_Notification::SUBMISSION, "123", array("Doe, John", "Doe, Jane"), "Test Document"));
        $this->assertEquals("Dokument #123 eingestellt: Doe, John ; Doe, Jane : Test Document", $subject);
    }

    public function testGetPublicationMailSubjectWithEmptyAuthorsAndEmptyTitle() {
        $method = $this->getMethod('getMailSubject');
        $subject = $method->invokeArgs($this->notification, array(Util_Notification::PUBLICATION, "123", array(), ""));
        $this->assertEquals("Dokument #123 veröffentlicht: n/a : n/a", $subject);
    }

    public function testGetPublicationMailSubjectWithOneAuthor() {
        $method = $this->getMethod('getMailSubject');
        $subject = $method->invokeArgs($this->notification, array(Util_Notification::PUBLICATION, "123", array("Doe, John"), "Test Document"));
        $this->assertEquals("Dokument #123 veröffentlicht: Doe, John : Test Document", $subject);
    }

    public function testGetPublicationMailSubjectWithTwoAuthors() {
        $method = $this->getMethod('getMailSubject');
        $subject = $method->invokeArgs($this->notification, array(Util_Notification::PUBLICATION, "123", array("Doe, John", "Doe, Jane"), "Test Document"));
        $this->assertEquals("Dokument #123 veröffentlicht: Doe, John ; Doe, Jane : Test Document", $subject);
    }

    public function testGetMailSubjectWithInvalidContext() {
        $method = $this->getMethod('getMailSubject');
        $subject = $method->invokeArgs($this->notification, array("deleted", "123", array("Doe, John", "Doe, Jane"), "Test Document"));
        $this->assertNull($subject);
    }

    public function testGetSubmissionMailBodyWithEmptyAuthorsAndEmptyTitle() {
        $method = $this->getMethod('getMailBody');
        $body = $method->invokeArgs($this->notification, array(Util_Notification::SUBMISSION, "123", array(), "", "http://localhost/foo/1"));
        $this->assertContains("Autor(en): n/a", $body);
        $this->assertContains("Titel: n/a", $body);
        $this->assertContains("Dokument-ID: 123", $body);
        $this->assertContains("http://localhost/foo/1", $body);
        $this->assertContains("Ein neues Dokument wurde auf Ihrem OPUS4-Dokumentenserver hochgeladen", $body);
    }

    public function testGetSubmissionMailBodyWithTwoAuthorsAndNonEmptyTitle() {
        $method = $this->getMethod('getMailBody');
        $body = $method->invokeArgs($this->notification, array(Util_Notification::SUBMISSION, "123", array( "Doe, John", "Doe, Jane" ), "Test Title", "http://localhost/foo/1"));
        $this->assertContains("Autor(en):\nDoe, John\nDoe, Jane", $body);
        $this->assertContains("Titel: Test Title", $body);
        $this->assertContains("Dokument-ID: 123", $body);
        $this->assertContains("http://localhost/foo/1", $body);
        $this->assertContains("Ein neues Dokument wurde auf Ihrem OPUS4-Dokumentenserver hochgeladen", $body);
    }

    public function testGetPublicationMailBodyWithEmptyAuthorsAndEmptyTitle() {
        $method = $this->getMethod('getMailBody');
        $body = $method->invokeArgs($this->notification, array(Util_Notification::PUBLICATION, "123", array(), "", "http://localhost/foo/1"));
        $this->assertContains("Autor(en): n/a", $body);
        $this->assertContains("Titel: n/a", $body);
        $this->assertContains("Dokument-ID: 123", $body);
        $this->assertContains("http://localhost/foo/1", $body);
        $this->assertContains("Folgendes Dokument wurde auf dem OPUS4-Dokumentenserver freigegeben", $body);
    }

    public function testGetPublicationMailBodyWithTwoAuthorsAndNonEmptyTitle() {
        $method = $this->getMethod('getMailBody');
        $body = $method->invokeArgs($this->notification, array(Util_Notification::PUBLICATION, "123", array( "Doe, John", "Doe, Jane" ), "Test Title", "http://localhost/foo/1"));
        $this->assertContains("Autor(en):\nDoe, John\nDoe, Jane", $body);
        $this->assertContains("Titel: Test Title", $body);
        $this->assertContains("Dokument-ID: 123", $body);
        $this->assertContains("http://localhost/foo/1", $body);
        $this->assertContains("Folgendes Dokument wurde auf dem OPUS4-Dokumentenserver freigegeben", $body);
    }

    public function testGetMailBodyWithInvalidContext() {
        $method = $this->getMethod('getMailBody');
        $body = $method->invokeArgs($this->notification, array("deleted", "123", array(), "Test Document", "http://localhost/foo/1"));
        $this->assertNull($body);
    }

    public function testGetSubmissionMailBodyWithUnknownTemplateFile() {
        $this->config->notification->document->submitted->template = 'does-not-exist.phtml';
        $method = $this->getMethod('getMailBody');
        $body = $method->invokeArgs($this->notification, array(Util_Notification::SUBMISSION, "123", array(), "Test Document", "http://localhost/foo/1"));
        $this->assertNull($body);
    }

    public function testGetRecipientsWithInvalidContext() {        
        $method = $this->getMethod('getRecipients');
        $recipients = $method->invokeArgs($this->notification, array("deleted"));
        $this->assertNull($recipients);
    }

    public function testGetRecipientsForSubmissionContext() {        
        $method = $this->getMethod('getRecipients');
        $recipients = $method->invokeArgs($this->notification, array(Util_Notification::SUBMISSION));
        $this->assertEquals(1, count($recipients));
        $this->assertEquals($recipients[0]['name'], "submitted@localhost");
        $this->assertEquals($recipients[0]['address'], "submitted@localhost");
    }

    public function testGetRecipientsForSubmissionContextWithoutSubmittedMailConfig() {
        $this->config->notification->document->submitted->email = "";
        $method = $this->getMethod('getRecipients');
        $recipients = $method->invokeArgs($this->notification, array(Util_Notification::SUBMISSION));
        $this->assertEquals(0, count($recipients));
    }

    public function testGetRecipientsForSubmissionContextAndMultipleAddresses() {
        $this->config->notification->document->submitted->email = "submitted@localhost,sub@host.tld";
        $method = $this->getMethod('getRecipients');
        $recipients = $method->invokeArgs($this->notification, array(Util_Notification::SUBMISSION));
        $this->assertEquals(2, count($recipients));
        $this->assertEquals($recipients[0]['name'], "submitted@localhost");
        $this->assertEquals($recipients[0]['address'], "submitted@localhost");
        $this->assertEquals($recipients[1]['name'], "sub@host.tld");
        $this->assertEquals($recipients[1]['address'], "sub@host.tld");
    }

    public function testGetRecipientsForPublicationContextWithoutAuthorsAsRecipents() {
        $doc = new Opus_Document();
        $doc->store();
        $method = $this->getMethod('getRecipients');
        $recipients = $method->invokeArgs($this->notification, array(Util_Notification::PUBLICATION, array(), $doc));
        $doc->deletePermanent();
        $this->assertEquals(1, count($recipients));
        $this->assertEquals($recipients[0]['name'], "published@localhost");
        $this->assertEquals($recipients[0]['address'], "published@localhost");
    }

    public function testGetRecipientsForPublicationContextWithoutPublishedMailConfig() {
        $this->config->notification->document->published->email = "";
        $method = $this->getMethod('getRecipients');
        $recipients = $method->invokeArgs($this->notification, array(Util_Notification::PUBLICATION));
        $this->assertEquals(0, count($recipients));
    }

    public function testGetRecipientsForPublicationContextAndMultipleAddressesWithoutAuthorsAsRecipents() {
        $this->config->notification->document->published->email = "published@localhost,publ@host.tld";
        $doc = new Opus_Document();
        $doc->store();
        $method = $this->getMethod('getRecipients');
        $recipients = $method->invokeArgs($this->notification, array(Util_Notification::PUBLICATION, array(), $doc));
        $doc->deletePermanent();
        $this->assertEquals(2, count($recipients));
        $this->assertEquals($recipients[0]['name'], "published@localhost");
        $this->assertEquals($recipients[0]['address'], "published@localhost");
        $this->assertEquals($recipients[1]['name'], "publ@host.tld");
        $this->assertEquals($recipients[1]['address'], "publ@host.tld");
    }

    public function testGetRecipientsForPublicationContextWithAuthorsAsRecipents() {
        $this->config->notification->document->published->email = "published@localhost";
        $doc = new Opus_Document();
        $doc->store();

        $authors = array( 
            array ( "name" => "Doe, John", "address" => "doe@localhost" ),
            array ( "name" => "Doe, Jane", "address" => "jane.doe@localhost" )
        );

        $method = $this->getMethod('getRecipients');
        $recipients = $method->invokeArgs($this->notification, array(Util_Notification::PUBLICATION, $authors, $doc));
        $doc->deletePermanent();
        $this->assertEquals(3, count($recipients));
        $this->assertEquals($recipients[0]['name'], "published@localhost");
        $this->assertEquals($recipients[0]['address'], "published@localhost");
        $this->assertEquals($recipients[1]['name'], "Doe, John");
        $this->assertEquals($recipients[1]['address'], "doe@localhost");
        $this->assertEquals($recipients[2]['name'], "Doe, Jane");
        $this->assertEquals($recipients[2]['address'], "jane.doe@localhost");
    }

    public function testGetRecipientsForPublicationContextWithSubmitterAsRecipient() {
        $this->config->notification->document->published->email = "published@localhost";
        $doc = new Opus_Document();
        $submitter = new Opus_Person();
        $submitter->setFirstName('John');
        $submitter->setLastName('Submitter');
        $submitter->setEmail('john.submitter@localhost.de');
        $doc->addPersonSubmitter($submitter);
        $doc->store();

        $authors = array(
            array ( "name" => "Doe, John", "address" => "doe@localhost" ),
            array ( "name" => "Doe, Jane", "address" => "jane.doe@localhost" )
        );

        $method = $this->getMethod('getRecipients');
        $recipients = $method->invokeArgs($this->notification, array(Util_Notification::PUBLICATION, $authors, $doc));
        $doc->deletePermanent();
        
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

    public function testGetRecipientsForPublicationContextWithSubmitterWithoutMailAddressAsRecipient() {
        $this->config->notification->document->published->email = "published@localhost";
        $doc = new Opus_Document();
        $submitter = new Opus_Person();
        $submitter->setFirstName('John');
        $submitter->setLastName('Submitter');        
        $doc->addPersonSubmitter($submitter);
        $doc->store();

        $method = $this->getMethod('getRecipients');
        $recipients = $method->invokeArgs($this->notification, array(Util_Notification::PUBLICATION, array(), $doc));
        $doc->deletePermanent();

        $this->assertEquals(1, count($recipients));
        $this->assertEquals($recipients[0]['name'], "published@localhost");
        $this->assertEquals($recipients[0]['address'], "published@localhost");
    }

    /**
     * Diese Testmethode hat keine Assertions. Sie stellt lediglich sicher, dass alle Codeteile
     * der Funktion prepareMail durchlaufen werden.
     */
    public function testPrepareMailWithTwoOptionalArgs() {
        $doc = new Opus_Document();
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
        $this->notification->prepareMail($doc, Util_Notification::PUBLICATION, 'http://localhost/foo/1', false, array(false, true));
        $doc->deletePermanent();
    }

    public function testGetRecipientsForPublicationContextWithoutSubmitter() {
        $doc = new Opus_Document();
        $doc->setLanguage("eng");

        $title = new Opus_Title();
        $title->setValue("Test Document");
        $title->setLanguage("eng");
        $doc->addTitleMain($title);

        $submitter = new Opus_Person();
        $submitter->setFirstName("John");
        $submitter->setLastName("Submitter");
        $submitter->setEmail("sub@localhost.de");
        $doc->addPersonSubmitter($submitter);

        $doc->store();
        $method = $this->getMethod('getRecipients');
        $recipients = $method->invokeArgs($this->notification, array(Util_Notification::PUBLICATION, array(array("name" => "foo", "address" => "foo@localhost")), $doc, false));
        $this->assertEquals(2, count($recipients));
        $this->assertEquals("published@localhost", $recipients[0]["name"]);
        $this->assertEquals("published@localhost", $recipients[0]["address"]);
        $this->assertEquals("foo", $recipients[1]["name"]);
        $this->assertEquals("foo@localhost", $recipients[1]["address"]);
        $doc->deletePermanent();
        
    }
    
    public function testCreateWorkerJobIfAsyncEnabled() {
        if (isset($this->config->runjobs->asynchronous)) {
            $origAsyncFlag = $this->config->runjobs->asynchronous;
            $this->config->runjobs->asynchronous = true;
        } else {
            $origAsyncFlag = null;
            $this->config->merge(new Zend_Config(array('runjobs' => array('asynchronous' => true))));
        }

        $this->assertEquals(0, Opus_Job::getCount(), 'test data changed.');

        $doc = new Opus_Document();
        $doc->setLanguage("eng");

        $title = new Opus_Title();
        $title->setValue("Test Document");
        $title->setLanguage("eng");
        $doc->addTitleMain($title);

        $doc->store();
        $this->notification->prepareMail($doc, Util_Notification::SUBMISSION, 'http://localhost/foo/1');
        $doc->deletePermanent();        

        $mailJobs = Opus_Job::getByLabels(array(Opus_Job_Worker_MailNotification::LABEL));
        
        $this->assertEquals(1, count($mailJobs), 'Expected 1 mail job');
        
        $jobs = Opus_Job::getAll();
        
        if(!empty($jobs)) {
            foreach($jobs as $job) {
                $job->delete();
            }
        }
        if (is_null($origAsyncFlag)) {
            unset($this->config->runjobs->asynchronous);
        } else {
            $this->config->runjobs->asynchronous = $origAsyncFlag;
        }
        
    }

    private function getMethod($methodName) {
        $class = new ReflectionClass('Util_Notification');
        $method = $class->getMethod($methodName);
        $method->setAccessible(true);
        return $method;
    }
}