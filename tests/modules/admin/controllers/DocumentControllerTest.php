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
 * @author      Michael Lang <lang@zib.de>
 * @copyright   Copyright (c) 2008-2018, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Unit tests for Admin_DocumentController.
 *
 * @covers Admin_DocumentController
 */
class Admin_DocumentControllerTest extends ControllerTestCase {

    private $expectedNavigationLinks;

    public function setUp() {
        parent::setUp();

        // Die Links werden aus den Fieldset Legenden der Unterformulare generiert (nur 1. Ebene)
        $this->expectedNavigationLinks = array(
            '#fieldset-General' => 'Allgemeines',
            '#fieldset-Persons' => 'Personen',
            '#fieldset-Titles' => 'Titelinformationen',
            '#fieldset-Bibliographic' => 'Bibliographische Informationen',
            '#fieldset-Series' => 'Schriftenreihen',
            '#fieldset-Enrichments' => 'Benutzerdefinierte Felder (Enrichments)',
            '#fieldset-Collections' => 'Sammlungen, Klassifikationen',
            '#fieldset-Content' => 'Inhaltliche Erschließung',
            '#fieldset-IdentifiersAll' => 'Identifikatoren',
            '#fieldset-Licences' => 'Lizenzen',
            '#fieldset-Patents' => 'Patente',
            '#fieldset-Notes' => 'Bemerkungen',
            '#fieldset-Files' => 'Dateien',
        );
    }

    /**
     * Regression test for OPUSVIER-1757
     */
    public function testEditLinkForEmptySectionIsNotDisplayed() {
        $this->dispatch('/admin/document/index/id/92');
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('document');
        $this->assertAction('index');
        $response = $this->getResponse()->getBody();
        $this->assertTrue(substr_count($response, 'edit/id/92/section/patents') == 0);
    }

    /**
     * Regression test for OPUSVIER-1841.
     */
    public function testWarningDisplayingDateOfBirth() {
        $doc = $this->createTestDocument();

        $person = new Opus_Person();
        $person->setFirstName("Johnny");
        $person->setLastName("Test");
        $dateOfBirth = new Opus_Date(new Zend_Date('1.1.2010', 'dd/MM/yyyy'));
        $person->setDateOfBirth($dateOfBirth);

        $doc->addPersonAuthor($person);

        $doc->store();

        $docId = $doc->getId();

        $this->dispatch('/admin/document/index/id/' . $docId);

        $body = $this->getResponse()->getBody();
        $this->assertTrue(substr_count($body, 'exception \'PHPUnit_Framework_Error_Warning\' with message \'htmlspecialchars() expects parameter 1 to be string, array given\' in /home/jens/opus4dev/opus4/server/modules/admin/views/scripts/document/index.phtml:145') == 0);
        $this->assertTrue(substr_count($body, 'Warning: htmlspecialchars() expects parameter 1 to be string, array given in /home/jens/opus4dev/opus4/server/modules/admin/views/scripts/document/index.phtml on line 145') == 0);
    }

    /**
     * Regression test for OPUSVIER-1843.
     */
    public function testRegression1843() {
        $this->markTestSkipped('Replace - War für altes Metadaten-Formular.');

        $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'Opus_Document[CompletedDate]' => '2000/01/01',
                    'Opus_Document[CompletedYear]' => '2000',
                    'Opus_Document[ThesisDateAccepted]' => '2000/01/01',
                    'Opus_Document[PublishedDate]' => '2000/01/01',
                    'Opus_Document[PublishedYear]' => '2000',
                    'Opus_Document[ServerDateModified]' => '2000/01/01',
                    'Opus_Document[ServerDatePublished]' => '2000/01/01',
                    'save' => 'Speichern'
                ));
        $this->dispatch('/admin/document/update/id/96/section/dates');

        $body = $this->getResponse()->getBody();
        $this->assertTrue(substr_count($body, '1999/01/01') !== 0, $body);
    }

    public function testRegression2353ExceptionForAbstractsEditForm() {
        $this->dispatch('admin/document/edit/id/92/section/abstracts');
        $body = $this->getResponse()->getBody();
        $this->assertTrue(substr_count($body, 'Call to a member function setAttrib') == 0);
        $this->checkForBadStringsInHtml($body);
    }

    public function testPreserveNewlinesForAbstract() {
        $this->markTestIncomplete("Muss fuer OPUS 4.4 angepasst werden."); // TODO OPUSVIER-2794
        $doc = $this->createTestDocument();
        $doc->setLanguage("eng");

        $abstract = new Opus_Title();
        $abstract->setLanguage("eng");
        $abstract->setValue("foo\nbar\n\nbaz");
        $doc->addTitleAbstract($abstract);

        $doc->store();

        $this->dispatch('/admin/document/index/id/' . $doc->getId());

        $this->assertContains('<pre class="abstractTextContainer preserve-spaces">' . "foo\nbar\n\nbaz" . '</pre>', $this->getResponse()->getBody());
    }

    public function testPreserveNewlinesForNote() {
        $this->markTestIncomplete("Muss fuer OPUS 4.4 angepasst werden."); // TODO OPUSVIER-2794
        $doc = $this->createTestDocument();
        $doc->setLanguage("eng");
        $doc->setServerState("published");

        $note = new Opus_Note();
        $note->setMessage("foo\nbar\n\nbaz");
        $note->setVisibility("public");
        $doc->addNote($note);

        $doc->store();

        $this->dispatch('/admin/document/index/id/' . $doc->getId());

        $this->assertContains('<pre class="preserve-spaces noteTextContainer">' . "foo\nbar\n\nbaz" . '</pre>', $this->getResponse()->getBody());
    }

    public function testDisplayCollectionNumberAndNameOnOverviewPageForDDCCollection() {
        $this->markTestIncomplete("Muss fuer OPUS 4.4 angepasst werden."); // TODO OPUSVIER-2794
        $role = new Opus_CollectionRole(2);
        $displayBrowsing = $role->getDisplayBrowsing();
        $role->setDisplayBrowsing('Name');
        $role->store();

        $this->dispatch('/admin/document/index/id/89');

        // undo changes
        $role->setDisplayBrowsing($displayBrowsing);
        $role->store();

        $this->assertContains('62 Ingenieurwissenschaften', $this->getResponse()->getBody());
        $this->assertNotContains('Ingenieurwissenschaften 62', $this->getResponse()->getBody());
    }

    public function testDisplayCollectionNumberAndNameOnAssignmentPageForDDCCollection() {
        $this->markTestIncomplete("Muss fuer OPUS 4.4 angepasst werden."); // TODO OPUSVIER-2794
        $role = new Opus_CollectionRole(2);
        $displayBrowsing = $role->getDisplayBrowsing();
        $role->setDisplayBrowsing('Name');
        $role->store();

        $this->dispatch('/admin/document/edit/id/89/section/collections');

        // undo changes
        $role->setDisplayBrowsing($displayBrowsing);
        $role->store();

        $this->assertContains('62 Ingenieurwissenschaften', $this->getResponse()->getBody());
        $this->assertNotContains('Ingenieurwissenschaften 62', $this->getResponse()->getBody());
    }

    public function testShowDocInfoOnIndex() {
        $this->dispatch('/admin/document/index/id/146');
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('document');
        $this->assertAction('index');
        $this->assertQueryContentContains('div#docinfo', 'KOBV');
        $this->assertQueryContentContains('div#docinfo', '146');
        $this->assertQueryContentContains('div#docinfo', 'Doe, John');
    }

    public function testIndexActionValidXHTML() {
        $this->dispatch('/admin/document/index/id/146');
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('document');
        $this->assertAction('index');

        // Prüfen, ob XHTML valid ist
        $this->validateXHTML($this->getResponse()->getBody());
        $this->assertQueryContentContains('div.breadcrumbsContainer', 'KOBV');
    }

    public function testIndexActionCollectionRolesTranslated() {
        $this->useEnglish();

        $this->dispatch('/admin/document/index/id/146');
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('document');
        $this->assertAction('index');

        $this->assertQueryContentContains('//fieldset#fieldset-ddc/legend', 'Dewey Decimal Classification');
    }

    public function testIndexActionNavigationLinksPresent() {
        $this->useGerman();

        $this->dispatch('/admin/document/index/id/146');
        $this->assertResponseCode(200);

        $this->verifyNavigationLinks($this->expectedNavigationLinks);
    }

    public function testEditActionNavigationLinksPresent() {
        $this->useGerman();

        $this->dispatch('/admin/document/edit/id/146');
        $this->assertResponseCode(200);

        // Dateien werden nicht im Metadaten-Formular editiert
        unset($this->expectedNavigationLinks['#fieldset-Files']);

        $this->verifyNavigationLinks($this->expectedNavigationLinks);
    }

    protected function verifyNavigationLinks($expectedLinks) {
        $this->assertQuery('//dl#Document-Goto');
        $this->assertQueryCount('//dl#Document-Goto//li/a', count($expectedLinks));

        foreach ($expectedLinks as $link => $label) {
            $this->assertXpathContentContains("//dl[@id=\"Document-Goto\"]//li/a[@href=\"$link\"]", $label,
                "Link '$link' mit Label '$label' is missing from navigation.");
        }
    }

    public function testEditActionValidXHTML() {
        $this->dispatch('/admin/document/edit/id/146');
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('document');
        $this->assertAction('edit');

        // Prüfen, ob XHTML valid ist
        $this->validateXHTML($this->getResponse()->getBody());
        $this->verifyBreadcrumbDefined();

        // Check Add-Buttons
        $addButtons = array(
            'input#Document-Persons-author-Add',
            'input#Document-Persons-editor-Add',
            'input#Document-Persons-translator-Add',
            'input#Document-Persons-contributor-Add',
            'input#Document-Persons-other-Add',
            'input#Document-Persons-advisor-Add',
            'input#Document-Persons-referee-Add',
            'input#Document-Persons-submitter-Add',

            'input#Document-Titles-Main-Add',
            'input#Document-Titles-Additional-Add',
            'input#Document-Titles-Parent-Add',
            'input#Document-Titles-Sub-Add',

            'input#Document-Bibliographic-Publishers-Add',
            'input#Document-Bibliographic-Grantors-Add',

            'input#Document-Series-Add',
            'input#Document-Enrichments-Add',
            'input#Document-Collections-Add',

            'input#Document-Content-Abstracts-Add',
            'input#Document-Content-Subjects-Swd-Add',
            'input#Document-Content-Subjects-Psyndex-Add',
            'input#Document-Content-Subjects-Uncontrolled-Add',

            'input#Document-IdentifiersAll-Identifiers-Add',
            'input#Document-Patents-Add',
            'input#Document-Notes-Add',
        );

        $this->assertQueryCount('input[@value="Add"]', count($addButtons), 'Not enough add buttons.');

        foreach ($addButtons as $button) {
            $this->assertQuery($button);
        }
    }

    public function testRemoveButtonsTranslated() {
        $this->useGerman();

        $this->dispatch('/admin/document/edit/id/146');
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('document');
        $this->assertAction('edit');

        $document = new DOMDocument();
        $document->loadHTML($this->getResponse()->getBody());
        $elements = $document->getElementsByTagName('input');

        foreach ($elements as $element) {
            if ($element->getAttribute('type') === 'submit') {
                $elementId = $element->getAttribute('id');
                if (strpos($elementId, 'Remove') !== false) {
                    $this->assertEquals('Entfernen', $element->getAttribute('value'));
                }
            }
        }
    }

    /**
     * Test for OPUSVIER-1841.
     */
    public function testRegression1841() {
        $this->useEnglish();
        $this->loginUser('admin', 'adminadmin');

        // Display metadata overview for test document (fully populated)
        $this->dispatch('/admin/document/index/id/146');

        // Checks
        $this->assertNotQueryContentContains('//div', 'Warning:');
        $this->assertNotQueryContentContains('//div', 'htmlspecialchars');
        $this->assertQueryContentContains('//div', '1970/01/01');
    }

      // document/overviewTests
    public function testIndexActionGerman() {
        $this->useGerman();

        $this->dispatch('/admin/document/index/id/146');

        // Information in Actionbox
        $this->assertQueryContentContains('//*[@id="Document-ServerState"]/dd/ul/li[1]', 'Freigegeben');
        $this->assertQueryContentContains('//dd[@id="Document-ServerDatePublished-value"]', '03.01.2012');
        $this->assertQuery('//dd[@id="Document-ServerDateModified-value"]');
        $this->assertQueryContentContains('//dd[@id="Document-ServerDateModified-value"]', '03.01.2012');

        // General
        $this->assertQueryContentContains('//*[@id="Document-General-Language"]', 'Deutsch');
        $this->assertQueryContentContains('//*[@id="Document-General-Type"]', 'Masterarbeit');
        $this->assertQueryContentContains('//*[@id="Document-General-PublishedDate"]', '30.04.2007');
        $this->assertQueryContentContains('//*[@id="Document-General-PublishedYear"]', '2008');
        $this->assertQueryContentContains('//*[@id="Document-General-CompletedDate"]', '01.12.2011');
        $this->assertQueryContentContains('//*[@id="Document-General-CompletedYear"]', '2009');
        $this->assertQueryContentContains('//*[@id="Document-General-EmbargoDate"]', '05.06.1984');

        // Persons
        $this->assertQueryContentContains('//*[@id="Document-Persons-author-PersonAuthor0-FirstName"]', 'John');
        $this->assertQueryContentContains('//*[@id="Document-Persons-author-PersonAuthor0-LastName"]', 'Doe');
        $this->assertQueryContentContains('//*[@id="Document-Persons-author-PersonAuthor0-Email"]', 'doe@example.org');
        $this->assertQuery('//*[@id="Document-Persons-author-PersonAuthor0-AllowContact"]');

        $this->assertQueryContentContains('//*[@id="Document-Persons-editor-PersonEditor0-FirstName"]', 'Jane');
        $this->assertQueryContentContains('//*[@id="Document-Persons-editor-PersonEditor0-LastName"]', 'Doe');
        $this->assertQueryContentContains('//*[@id="Document-Persons-editor-PersonEditor0-AcademicTitle"]', 'PhD');
        $this->assertQueryContentContains('//*[@id="Document-Persons-editor-PersonEditor0-PlaceOfBirth"]', 'London');

        $this->assertQueryContentContains('//*[@id="Document-Persons-translator-PersonTranslator0-FirstName"]', 'Jane');
        $this->assertQueryContentContains('//*[@id="Document-Persons-translator-PersonTranslator0-LastName"]', 'Doe');
        $this->assertQueryContentContains('//*[@id="Document-Persons-translator-PersonTranslator0-AcademicTitle"]', 'PhD');
        $this->assertQueryContentContains('//*[@id="Document-Persons-translator-PersonTranslator0-DateOfBirth"]', '01.01.1970');
        $this->assertQueryContentContains('//*[@id="Document-Persons-translator-PersonTranslator0-PlaceOfBirth"]', 'New York');

        $this->assertQueryContentContains('//*[@id="Document-Persons-contributor-PersonContributor0-FirstName"]', 'Jane');
        $this->assertQueryContentContains('//*[@id="Document-Persons-contributor-PersonContributor0-LastName"]', 'Doe');
        $this->assertQueryContentContains('//*[@id="Document-Persons-contributor-PersonContributor0-AcademicTitle"]', 'PhD');
        $this->assertQueryContentContains('//*[@id="Document-Persons-contributor-PersonContributor0-DateOfBirth"]', '02.01.1970');

        $this->assertQueryContentContains('//*[@id="Document-Persons-other-PersonOther0-FirstName"]', 'Jane');
        $this->assertQueryContentContains('//*[@id="Document-Persons-other-PersonOther0-LastName"]', 'Doe');
        $this->assertQueryContentContains('//*[@id="Document-Persons-other-PersonOther0-AcademicTitle"]', 'PhD');

        $this->assertQueryContentContains('//*[@id="Document-Persons-advisor-PersonAdvisor0-FirstName"]', 'Jane');
        $this->assertQueryContentContains('//*[@id="Document-Persons-advisor-PersonAdvisor0-LastName"]', 'Doe');
        $this->assertQueryContentContains('//*[@id="Document-Persons-advisor-PersonAdvisor0-AcademicTitle"]', 'PhD');

        $this->assertQueryContentContains('//*[@id="Document-Persons-referee-PersonReferee0-FirstName"]', 'Jane');
        $this->assertQueryContentContains('//*[@id="Document-Persons-referee-PersonReferee0-LastName"]', 'Doe');
        $this->assertQueryContentContains('//*[@id="Document-Persons-referee-PersonReferee0-AcademicTitle"]', 'PhD');

        $this->assertQueryContentContains('//*[@id="Document-Persons-submitter-PersonSubmitter0-FirstName"]', 'Jane');
        $this->assertQueryContentContains('//*[@id="Document-Persons-submitter-PersonSubmitter0-LastName"]', 'Doe');
        $this->assertQueryContentContains('//*[@id="Document-Persons-submitter-PersonSubmitter0-AcademicTitle"]', 'PhD');

        // Titles
        $this->assertQueryContentContains('//*[@id="Document-Titles-Main-TitleMain0-Language"]', 'Deutsch');
        $this->assertQueryContentContains('//*[@id="Document-Titles-Main-TitleMain0-Value"]', 'KOBV');
        $this->assertQueryContentContains('//*[@id="Document-Titles-Main-TitleMain1-Language"]', 'Englisch');
        $this->assertQueryContentContains('//*[@id="Document-Titles-Main-TitleMain1-Value"]', 'COLN');

        $this->assertQueryContentContains('//*[@id="Document-Titles-Additional-TitleAdditional0-Language"]', 'Deutsch');
        $this->assertQueryContentContains('//*[@id="Document-Titles-Additional-TitleAdditional0-Value"]',
                'Kooperativer Biblioheksverbund Berlin-Brandenburg');

        $this->assertQueryContentContains('//*[@id="Document-Titles-Parent-TitleParent0-Language"]', 'Deutsch');
        $this->assertQueryContentContains('//*[@id="Document-Titles-Parent-TitleParent0-Value"]', 'Parent Title');

        $this->assertQueryContentContains('//*[@id="Document-Titles-Sub-TitleSub0-Language"]', 'Deutsch');
        $this->assertQueryContentContains('//*[@id="Document-Titles-Sub-TitleSub0-Value"]', 'Service-Zentrale');
        $this->assertQueryContentContains('//*[@id="Document-Titles-Sub-TitleSub1-Language"]', 'Englisch');
        $this->assertQueryContentContains('//*[@id="Document-Titles-Sub-TitleSub1-Value"]', 'Service Center');

        // Bibliographische Informationen
        $this->assertQueryContentContains('//*[@id="Document-Bibliographic-Edition"]', '1');
        $this->assertQueryContentContains('//*[@id="Document-Bibliographic-Volume"]', '2');
        $this->assertQueryContentContains('//*[@id="Document-Bibliographic-PublisherName"]', 'Foo Publishing');
        $this->assertQueryContentContains('//*[@id="Document-Bibliographic-PublisherPlace"]', 'Timbuktu');
        $this->assertQueryContentContains('//*[@id="Document-Bibliographic-PageCount"]', '4');
        $this->assertQueryContentContains('//*[@id="Document-Bibliographic-PageFirst"]', '1');
        $this->assertQueryContentContains('//*[@id="Document-Bibliographic-PageLast"]', '4');
        $this->assertQueryContentContains('//*[@id="Document-Bibliographic-Issue"]', '3');
        $this->assertQueryContentContains('//*[@id="Document-Bibliographic-ContributingCorporation"]', 'Baz University');
        $this->assertQueryContentContains('//*[@id="Document-Bibliographic-CreatingCorporation"]', 'Bar University');
        $this->assertQueryContentContains('//*[@id="Document-Bibliographic-ThesisDateAccepted"]', '02.11.2010');
        $this->assertQueryContentContains('//*[@id="Document-Bibliographic-ThesisYearAccepted"]', '1999');
        $this->assertQueryContentContains('//*[@id="Document-Bibliographic-BelongsToBibliography"]', 'Ja');

        $this->assertQueryContentContains('//*[@id="Document-Bibliographic-Publishers-ThesisPublisher0-Institute"]', 'Foobar Universitätsbibliothek');
        $this->assertQueryContentContains('//*[@id="Document-Bibliographic-Publishers-ThesisPublisher1-Institute"]', 'Institute with DNB contact ID');
        $this->assertQueryContentContains('//*[@id="Document-Bibliographic-Grantors-ThesisGrantor0-Institute"]', 'Foobar Universität, Testwissenschaftliche Fakultät');
        $this->assertQueryContentContains('//*[@id="Document-Bibliographic-Grantors-ThesisGrantor1-Institute"]', 'School of Life');

        // Series
        $this->assertQueryContentContains('//*[@id="Document-Series-Series0-SeriesId"]', 'MySeries');
        $this->assertQueryContentContains('//*[@id="Document-Series-Series0-Number"]', '5/5');
        $this->assertQueryContentContains('//*[@id="Document-Series-Series0-SortOrder"]', '6');

        // Enrichments
        $this->assertQueryContentContains('//*[@id="Document-Enrichments-Enrichment0-KeyName"]', 'validtestkey');
        $this->assertQueryContentContains('//*[@id="Document-Enrichments-Enrichment0-Value"]', 'Köln');

        $this->assertQueryContentContains('//*[@id="Document-Enrichments-Enrichment1-KeyName"]', 'Zur Bestellung der Druckausgabe');
        $this->assertQueryContentContains('//*[@id="Document-Enrichments-Enrichment1-Value"]', 'http://www.test.de');

        $this->assertQueryContentContains('//*[@id="Document-Enrichments-Enrichment2-KeyName"]', 'Quelle');
        $this->assertQueryContentContains('//*[@id="Document-Enrichments-Enrichment2-Value"]', 'Dieses Dokument ist auch erschienen als ...');

        $this->assertQueryContentContains('//*[@id="Document-Enrichments-Enrichment3-KeyName"]', 'RVK - Regensburger Verbundklassifikation');
        $this->assertQueryContentContains('//*[@id="Document-Enrichments-Enrichment3-Value"]', 'LI 99660');

        $this->assertQueryContentContains('//*[@id="Document-Enrichments-Enrichment4-KeyName"]', 'Sonstige beteiligte Person');
        $this->assertQueryContentContains('//*[@id="Document-Enrichments-Enrichment4-Value"]', 'John Doe (Foreword) and Jane Doe (Illustration)');

        $this->assertQueryContentContains('//*[@id="Document-Enrichments-Enrichment5-KeyName"]', 'Name der Veranstaltung');
        $this->assertQueryContentContains('//*[@id="Document-Enrichments-Enrichment5-Value"]', 'Opus4 OAI-Event');

        $this->assertQueryContentContains('//*[@id="Document-Enrichments-Enrichment6-KeyName"]', 'Stadt der Veranstaltung');
        $this->assertQueryContentContains('//*[@id="Document-Enrichments-Enrichment6-Value"]', 'Opus4 OAI-City');

        $this->assertQueryContentContains('//*[@id="Document-Enrichments-Enrichment7-KeyName"]', 'Land der Veranstaltung');
        $this->assertQueryContentContains('//*[@id="Document-Enrichments-Enrichment7-Value"]', 'Opus4 OAI-Country');

        // Collections
        $this->assertQueryContentContains('//*[@id="Document-Collections-ddc-collection0-Name"]', 'DDC-Klassifikation'); // Verknüpfung mit Root-Collection
        $this->assertQueryContentContains('//*[@id="Document-Collections-ddc-collection1-Name"]', '28 Christliche Konfessionen');
        $this->assertQueryContentContains('//*[@id="Document-Collections-ddc-collection2-Name"]', '51 Mathematik');
        $this->assertQueryContentContains('//*[@id="Document-Collections-ddc-collection3-Name"]', '433 Deutsche Wörterbücher');

        $this->assertQueryContentContains('//*[@id="Document-Collections-ccs-collection0-Name"]', 'CCS-Klassifikation'); // Verknüpfung mit Root-Collection

        $this->assertQueryContentContains('//*[@id="Document-Collections-pacs-collection0-Name"]', '12.15.Hh Determination of Kobayashi-Maskawa matrix elements');

        $this->assertQueryContentContains('//*[@id="Document-Collections-jel-collection0-Name"]', 'JEL-Klassifikation'); // Verknüpfung mit Root-Collection

        $this->assertQueryContentContains('//*[@id="Document-Collections-msc-collection0-Name"]', '05-XX COMBINATORICS (For finite fields, see 11Txx)');

        $this->assertQueryContentContains('//*[@id="Document-Collections-bk-collection0-Name"]', '08.20 Geschichte der westlichen Philosophie: Allgemeines');

        $this->assertQueryContentContains('//*[@id="Document-Collections-institutes-collection0-Name"]', 'Abwasserwirtschaft und Gewässerschutz B-2');


        // Abstracts
        $this->assertQueryContentContains('//*[@id="Document-Content-Abstracts-TitleAbstract0-Language"]', 'Deutsch');
        $this->assertQueryContentContains('//*[@id="Document-Content-Abstracts-TitleAbstract0-Value"]',
                'Die KOBV-Zentrale in Berlin-Dahlem.');

        $this->assertQueryContentContains('//*[@id="Document-Content-Abstracts-TitleAbstract1-Language"]', 'Englisch');
        $this->assertQueryContentContains('//*[@id="Document-Content-Abstracts-TitleAbstract1-Value"]',
                'Lorem impsum.');

        // Subjects
        $this->assertQueryContentContains('//*[@id="Document-Content-Subjects-Swd-Subject0-Value"]', 'Berlin');

        $this->assertQueryContentContains('//*[@id="Document-Content-Subjects-Uncontrolled-Subject0-Language"]', 'Deutsch');
        $this->assertQueryContentContains('//*[@id="Document-Content-Subjects-Uncontrolled-Subject0-Value"]', 'Palmöl');

        // Identifier
        $this->assertQueryContentContains('//*[@id="Document-IdentifiersAll-IdentifiersDOI-IdentifierDOI0-Value"]', '123');

        $this->assertQueryContentContains('//*[@id="Document-IdentifiersAll-IdentifiersURN-IdentifierURN0-Value"]', 'urn:nbn:op:123');

        $this->assertQueryContentContains('//*[@id="Document-IdentifiersAll-Identifiers-Identifier0-Type"]', 'alter Identifier');
        $this->assertQueryContentContains('//*[@id="Document-IdentifiersAll-Identifiers-Identifier0-Value"]', '123');

        $this->assertQueryContentContains('//*[@id="Document-IdentifiersAll-Identifiers-Identifier1-Type"]', 'Sequenznummer');
        $this->assertQueryContentContains('//*[@id="Document-IdentifiersAll-Identifiers-Identifier1-Value"]', '123');

        $this->assertQueryContentContains('//*[@id="Document-IdentifiersAll-Identifiers-Identifier2-Type"]', 'Uuid');
        $this->assertQueryContentContains('//*[@id="Document-IdentifiersAll-Identifiers-Identifier2-Value"]', '123');

        $this->assertQueryContentContains('//*[@id="Document-IdentifiersAll-Identifiers-Identifier3-Type"]', 'ISBN');
        $this->assertQueryContentContains('//*[@id="Document-IdentifiersAll-Identifiers-Identifier3-Value"]', '123');

        $this->assertQueryContentContains('//*[@id="Document-IdentifiersAll-Identifiers-Identifier4-Type"]', 'Handle');
        $this->assertQueryContentContains('//*[@id="Document-IdentifiersAll-Identifiers-Identifier4-Value"]', '123');

        $this->assertQueryContentContains('//*[@id="Document-IdentifiersAll-Identifiers-Identifier5-Type"]', 'URL');
        $this->assertQueryContentContains('//*[@id="Document-IdentifiersAll-Identifiers-Identifier5-Value"]', '123');

        $this->assertQueryContentContains('//*[@id="Document-IdentifiersAll-Identifiers-Identifier6-Type"]', 'ISSN');
        $this->assertQueryContentContains('//*[@id="Document-IdentifiersAll-Identifiers-Identifier6-Value"]', '123');

        $this->assertQueryContentContains('//*[@id="Document-IdentifiersAll-Identifiers-Identifier7-Type"]', 'STD-DOI');
        $this->assertQueryContentContains('//*[@id="Document-IdentifiersAll-Identifiers-Identifier7-Value"]', '123');

        $this->assertQueryContentContains('//*[@id="Document-IdentifiersAll-Identifiers-Identifier8-Type"]', 'CRIS-Link');
        $this->assertQueryContentContains('//*[@id="Document-IdentifiersAll-Identifiers-Identifier8-Value"]', '123');

        $this->assertQueryContentContains('//*[@id="Document-IdentifiersAll-Identifiers-Identifier9-Type"]', 'SplashURL');
        $this->assertQueryContentContains('//*[@id="Document-IdentifiersAll-Identifiers-Identifier9-Value"]', '123');

        $this->assertQueryContentContains('//*[@id="Document-IdentifiersAll-Identifiers-Identifier10-Type"]', 'OPUS 3 Id');
        $this->assertQueryContentContains('//*[@id="Document-IdentifiersAll-Identifiers-Identifier10-Value"]', '123');

        $this->assertQueryContentContains('//*[@id="Document-IdentifiersAll-Identifiers-Identifier11-Type"]', 'Opac Id');
        $this->assertQueryContentContains('//*[@id="Document-IdentifiersAll-Identifiers-Identifier11-Value"]', '123');

        $this->assertQueryContentContains('//*[@id="Document-IdentifiersAll-Identifiers-Identifier12-Type"]', 'Pubmed-Id');
        $this->assertQueryContentContains('//*[@id="Document-IdentifiersAll-Identifiers-Identifier12-Value"]', '123');

        $this->assertQueryContentContains('//*[@id="Document-IdentifiersAll-Identifiers-Identifier13-Type"]', 'ArXiv-Id');
        $this->assertQueryContentContains('//*[@id="Document-IdentifiersAll-Identifiers-Identifier13-Value"]', '123');

        // Lizenzen
        $this->assertQueryContentContains('//fieldset[@id="fieldset-Licences"]/legend', 'Lizenzen');
        $this->assertQueryContentContains('//*[@id="Document-Licences-licence4-label"]', 'Creative Commons - CC BY-ND - Namensnennung');

        // Patents
        $this->assertQueryContentContains('//*[@id="Document-Patents-Patent0-Number"]', '1234');
        $this->assertQueryContentContains('//*[@id="Document-Patents-Patent0-Countries"]', 'DDR');
        $this->assertQueryContentContains('//*[@id="Document-Patents-Patent0-YearApplied"]', '1970');
        $this->assertQueryContentContains('//*[@id="Document-Patents-Patent0-Application"]', 'The foo machine.');
        $this->assertQueryContentContains('//*[@id="Document-Patents-Patent0-DateGranted"]', '01.01.1970');

        // Notes
        $this->assertQueryContentContains('//*[@id="Document-Notes-Note0-Visibility"]', 'Öffentlich');
        $this->assertQueryContentContains('//*[@id="Document-Notes-Note0-Message"]', 'Für die Öffentlichkeit');
        $this->assertQueryContentContains('//*[@id="Document-Notes-Note1-Visibility"]', 'Intern');
        $this->assertQueryContentContains('//*[@id="Document-Notes-Note1-Message"]', 'Für den Admin');

        // Files
        $this->assertQueryContentContains('//*[@id="Document-Files-File0-Label"]/a', 'foo-pdf');
        $this->assertQueryContentContains('//*[@id="Document-Files-File0-FileSize"]', '8.61 KB');
        $this->assertQueryContentContains('//*[@id="Document-Files-File0-MimeType"]', 'application/pdf');
        $this->assertQueryContentContains('//*[@id="Document-Files-File0-Language"]', 'Deutsch');
        $this->assertQueryContentContains('//*[@id="Document-Files-File0-VisibleInFrontdoor"]', 'Ja');
        $this->assertQueryContentContains('//*[@id="Document-Files-File0-VisibleInOai"]', 'Ja');
    }

    public function testIndexActionEnglish() {
        $this->useEnglish();

        $this->dispatch('/admin/document/index/id/146');

        // Information in Actionbox
        $this->assertQueryContentContains('//*[@id="Document-ServerState"]/dd/ul/li[1]', 'Published');
        $this->assertQueryContentContains('//dd[@id="Document-ServerDatePublished-value"]', '2012/01/03');
        $this->assertQuery('//dd[@id="Document-ServerDateModified-value"]');
        $this->assertQueryContentContains('//dd[@id="Document-ServerDateModified-value"]', '2012/01/03');

        // General
        $this->assertQueryContentContains('//*[@id="Document-General-Language"]', 'German');
        $this->assertQueryContentContains('//*[@id="Document-General-Type"]', 'Master\'s Thesis');
        $this->assertQueryContentContains('//*[@id="Document-General-PublishedDate"]', '2007/04/30');
        $this->assertQueryContentContains('//*[@id="Document-General-PublishedYear"]', '2008');
        $this->assertQueryContentContains('//*[@id="Document-General-CompletedDate"]', '2011/12/01');
        $this->assertQueryContentContains('//*[@id="Document-General-CompletedYear"]', '2009');
        $this->assertQueryContentContains('//*[@id="Document-General-EmbargoDate"]', '1984/06/05');

        // Persons
        $this->assertQueryContentContains('//*[@id="Document-Persons-author-PersonAuthor0-FirstName"]', 'John');
        $this->assertQueryContentContains('//*[@id="Document-Persons-author-PersonAuthor0-LastName"]', 'Doe');
        $this->assertQueryContentContains('//*[@id="Document-Persons-author-PersonAuthor0-Email"]', 'doe@example.org');
        $this->assertNotQuery('//*[@id="Document-Persons-author-PersonAuthor0-DateOfBirth"]');
        $this->assertNotQuery('//*[@id="Document-Persons-author-PersonAuthor0-PlaceOfBirth"]');
        $this->assertQuery('//*[@id="Document-Persons-author-PersonAuthor0-AllowContact"]');

        $this->assertQueryContentContains('//*[@id="Document-Persons-editor-PersonEditor0-FirstName"]', 'Jane');
        $this->assertQueryContentContains('//*[@id="Document-Persons-editor-PersonEditor0-LastName"]', 'Doe');
        $this->assertQueryContentContains('//*[@id="Document-Persons-editor-PersonEditor0-AcademicTitle"]', 'PhD');
        $this->assertQueryContentContains('//*[@id="Document-Persons-editor-PersonEditor0-PlaceOfBirth"]', 'London');

        $this->assertQueryContentContains('//*[@id="Document-Persons-translator-PersonTranslator0-FirstName"]', 'Jane');
        $this->assertQueryContentContains('//*[@id="Document-Persons-translator-PersonTranslator0-LastName"]', 'Doe');
        $this->assertQueryContentContains('//*[@id="Document-Persons-translator-PersonTranslator0-AcademicTitle"]', 'PhD');
        $this->assertQueryContentContains('//*[@id="Document-Persons-translator-PersonTranslator0-DateOfBirth"]', '1970/01/01');
        $this->assertQueryContentContains('//*[@id="Document-Persons-translator-PersonTranslator0-PlaceOfBirth"]', 'New York');

        $this->assertQueryContentContains('//*[@id="Document-Persons-contributor-PersonContributor0-FirstName"]', 'Jane');
        $this->assertQueryContentContains('//*[@id="Document-Persons-contributor-PersonContributor0-LastName"]', 'Doe');
        $this->assertQueryContentContains('//*[@id="Document-Persons-contributor-PersonContributor0-AcademicTitle"]', 'PhD');
        $this->assertQueryContentContains('//*[@id="Document-Persons-contributor-PersonContributor0-DateOfBirth"]', '1970/01/02');

        $this->assertQueryContentContains('//*[@id="Document-Persons-other-PersonOther0-FirstName"]', 'Jane');
        $this->assertQueryContentContains('//*[@id="Document-Persons-other-PersonOther0-LastName"]', 'Doe');
        $this->assertQueryContentContains('//*[@id="Document-Persons-other-PersonOther0-AcademicTitle"]', 'PhD');

        $this->assertQueryContentContains('//*[@id="Document-Persons-advisor-PersonAdvisor0-FirstName"]', 'Jane');
        $this->assertQueryContentContains('//*[@id="Document-Persons-advisor-PersonAdvisor0-LastName"]', 'Doe');
        $this->assertQueryContentContains('//*[@id="Document-Persons-advisor-PersonAdvisor0-AcademicTitle"]', 'PhD');

        $this->assertQueryContentContains('//*[@id="Document-Persons-referee-PersonReferee0-FirstName"]', 'Jane');
        $this->assertQueryContentContains('//*[@id="Document-Persons-referee-PersonReferee0-LastName"]', 'Doe');
        $this->assertQueryContentContains('//*[@id="Document-Persons-referee-PersonReferee0-AcademicTitle"]', 'PhD');

        $this->assertQueryContentContains('//*[@id="Document-Persons-submitter-PersonSubmitter0-FirstName"]', 'Jane');
        $this->assertQueryContentContains('//*[@id="Document-Persons-submitter-PersonSubmitter0-LastName"]', 'Doe');
        $this->assertQueryContentContains('//*[@id="Document-Persons-submitter-PersonSubmitter0-AcademicTitle"]', 'PhD');

        // Titles
        $this->assertQueryContentContains('//*[@id="Document-Titles-Main-TitleMain0-Language"]', 'German');
        $this->assertQueryContentContains('//*[@id="Document-Titles-Main-TitleMain0-Value"]', 'KOBV');
        $this->assertQueryContentContains('//*[@id="Document-Titles-Main-TitleMain1-Language"]', 'English');
        $this->assertQueryContentContains('//*[@id="Document-Titles-Main-TitleMain1-Value"]', 'COLN');

        $this->assertQueryContentContains('//*[@id="Document-Titles-Additional-TitleAdditional0-Language"]', 'German');
        $this->assertQueryContentContains('//*[@id="Document-Titles-Additional-TitleAdditional0-Value"]',
                'Kooperativer Biblioheksverbund Berlin-Brandenburg');

        $this->assertQueryContentContains('//*[@id="Document-Titles-Parent-TitleParent0-Language"]', 'German');
        $this->assertQueryContentContains('//*[@id="Document-Titles-Parent-TitleParent0-Value"]', 'Parent Title');

        $this->assertQueryContentContains('//*[@id="Document-Titles-Sub-TitleSub0-Language"]', 'German');
        $this->assertQueryContentContains('//*[@id="Document-Titles-Sub-TitleSub0-Value"]', 'Service-Zentrale');
        $this->assertQueryContentContains('//*[@id="Document-Titles-Sub-TitleSub1-Language"]', 'English');
        $this->assertQueryContentContains('//*[@id="Document-Titles-Sub-TitleSub1-Value"]', 'Service Center');

        // Bibliographische Informationen
        $this->assertQueryContentContains('//*[@id="Document-Bibliographic-Edition"]', '1');
        $this->assertQueryContentContains('//*[@id="Document-Bibliographic-Volume"]', '2');
        $this->assertQueryContentContains('//*[@id="Document-Bibliographic-PublisherName"]', 'Foo Publishing');
        $this->assertQueryContentContains('//*[@id="Document-Bibliographic-PublisherPlace"]', 'Timbuktu');
        $this->assertQueryContentContains('//*[@id="Document-Bibliographic-PageCount"]', '4');
        $this->assertQueryContentContains('//*[@id="Document-Bibliographic-PageFirst"]', '1');
        $this->assertQueryContentContains('//*[@id="Document-Bibliographic-PageLast"]', '4');
        $this->assertQueryContentContains('//*[@id="Document-Bibliographic-Issue"]', '3');
        $this->assertQueryContentContains('//*[@id="Document-Bibliographic-ContributingCorporation"]', 'Baz University');
        $this->assertQueryContentContains('//*[@id="Document-Bibliographic-CreatingCorporation"]', 'Bar University');
        $this->assertQueryContentContains('//*[@id="Document-Bibliographic-ThesisDateAccepted"]', '2010/11/02');
        $this->assertQueryContentContains('//*[@id="Document-Bibliographic-ThesisYearAccepted"]', '1999');
        $this->assertQueryContentContains('//*[@id="Document-Bibliographic-BelongsToBibliography"]', 'Yes');

        $this->assertQueryContentContains('//*[@id="Document-Bibliographic-Publishers-ThesisPublisher0-Institute"]', 'Foobar Universitätsbibliothek');
        $this->assertQueryContentContains('//*[@id="Document-Bibliographic-Publishers-ThesisPublisher1-Institute"]', 'Institute with DNB contact ID');
        $this->assertQueryContentContains('//*[@id="Document-Bibliographic-Grantors-ThesisGrantor0-Institute"]', 'Foobar Universität');
        $this->assertQueryContentContains('//*[@id="Document-Bibliographic-Grantors-ThesisGrantor1-Institute"]', 'School of Life');

        // Series
        $this->assertQueryContentContains('//*[@id="Document-Series-Series0-SeriesId"]', 'MySeries');
        $this->assertQueryContentContains('//*[@id="Document-Series-Series0-Number"]', '5/5');
        $this->assertQueryContentContains('//*[@id="Document-Series-Series0-SortOrder"]', '6');

        // Enrichments
        $this->assertQueryContentContains('//*[@id="Document-Enrichments-Enrichment0-KeyName"]', 'validtestkey');
        $this->assertQueryContentContains('//*[@id="Document-Enrichments-Enrichment0-Value"]', 'Köln');

        $this->assertQueryContentContains('//*[@id="Document-Enrichments-Enrichment1-KeyName"]', 'To order the print edition');
        $this->assertQueryContentContains('//*[@id="Document-Enrichments-Enrichment1-Value"]', 'http://www.test.de');

        $this->assertQueryContentContains('//*[@id="Document-Enrichments-Enrichment2-KeyName"]', 'Source');
        $this->assertQueryContentContains('//*[@id="Document-Enrichments-Enrichment2-Value"]', 'Dieses Dokument ist auch erschienen als ...');

        $this->assertQueryContentContains('//*[@id="Document-Enrichments-Enrichment3-KeyName"]', 'RVK - Regensburg Classification');
        $this->assertQueryContentContains('//*[@id="Document-Enrichments-Enrichment3-Value"]', 'LI 99660');

        $this->assertQueryContentContains('//*[@id="Document-Enrichments-Enrichment4-KeyName"]', 'Contributor');
        $this->assertQueryContentContains('//*[@id="Document-Enrichments-Enrichment4-Value"]', 'John Doe (Foreword) and Jane Doe (Illustration)');

        $this->assertQueryContentContains('//*[@id="Document-Enrichments-Enrichment5-KeyName"]', 'Name of Event');
        $this->assertQueryContentContains('//*[@id="Document-Enrichments-Enrichment5-Value"]', 'Opus4 OAI-Event');

        $this->assertQueryContentContains('//*[@id="Document-Enrichments-Enrichment6-KeyName"]', 'City of event');
        $this->assertQueryContentContains('//*[@id="Document-Enrichments-Enrichment6-Value"]', 'Opus4 OAI-City');

        $this->assertQueryContentContains('//*[@id="Document-Enrichments-Enrichment7-KeyName"]', 'Country of event');
        $this->assertQueryContentContains('//*[@id="Document-Enrichments-Enrichment7-Value"]', 'Opus4 OAI-Country');

        // Collections
        $this->assertQueryContentContains('//*[@id="Document-Collections-ddc-collection0-Name"]', 'Dewey Decimal Classification'); // Verknüpfung mit Root-Collection
        $this->assertQueryContentContains('//*[@id="Document-Collections-ddc-collection1-Name"]', '28 Christliche Konfessionen');
        $this->assertQueryContentContains('//*[@id="Document-Collections-ddc-collection2-Name"]', '51 Mathematik');
        $this->assertQueryContentContains('//*[@id="Document-Collections-ddc-collection3-Name"]', '433 Deutsche Wörterbücher');

        $this->assertQueryContentContains('//*[@id="Document-Collections-ccs-collection0-Name"]', 'CCS-Classification'); // Verknüpfung mit Root-Collection

        $this->assertQueryContentContains('//*[@id="Document-Collections-pacs-collection0-Name"]', '12.15.Hh Determination of Kobayashi-Maskawa matrix elements');

        $this->assertQueryContentContains('//*[@id="Document-Collections-jel-collection0-Name"]', 'JEL-Classification'); // Verknüpfung mit Root-Collection

        $this->assertQueryContentContains('//*[@id="Document-Collections-msc-collection0-Name"]', '05-XX COMBINATORICS (For finite fields, see 11Txx)');

        $this->assertQueryContentContains('//*[@id="Document-Collections-bk-collection0-Name"]', '08.20 Geschichte der westlichen Philosophie: Allgemeines');

        $this->assertQueryContentContains('//*[@id="Document-Collections-institutes-collection0-Name"]', 'Abwasserwirtschaft und Gewässerschutz B-2');


        // Abstracts
        $this->assertQueryContentContains('//*[@id="Document-Content-Abstracts-TitleAbstract0-Language"]', 'German');
        $this->assertQueryContentContains('//*[@id="Document-Content-Abstracts-TitleAbstract0-Value"]',
                'Die KOBV-Zentrale in Berlin-Dahlem.');

        $this->assertQueryContentContains('//*[@id="Document-Content-Abstracts-TitleAbstract1-Language"]', 'English');
        $this->assertQueryContentContains('//*[@id="Document-Content-Abstracts-TitleAbstract1-Value"]',
                'Lorem impsum.');

        // Subjects
        $this->assertQueryContentContains('//*[@id="Document-Content-Subjects-Swd-Subject0-Value"]', 'Berlin');

        $this->assertQueryContentContains('//*[@id="Document-Content-Subjects-Uncontrolled-Subject0-Language"]', 'German');
        $this->assertQueryContentContains('//*[@id="Document-Content-Subjects-Uncontrolled-Subject0-Value"]', 'Palmöl');

        // Identifier
        $this->assertQueryContentContains('//*[@id="Document-IdentifiersAll-IdentifiersDOI-IdentifierDOI0-Value"]', '123');

        $this->assertQueryContentContains('//*[@id="Document-IdentifiersAll-IdentifiersURN-IdentifierURN0-Value"]', 'urn:nbn:op:123');

        $this->assertQueryContentContains('//*[@id="Document-IdentifiersAll-Identifiers-Identifier0-Type"]', 'old Identifier');
        $this->assertQueryContentContains('//*[@id="Document-IdentifiersAll-Identifiers-Identifier0-Value"]', '123');

        $this->assertQueryContentContains('//*[@id="Document-IdentifiersAll-Identifiers-Identifier1-Type"]', 'Serial Number');
        $this->assertQueryContentContains('//*[@id="Document-IdentifiersAll-Identifiers-Identifier1-Value"]', '123');

        $this->assertQueryContentContains('//*[@id="Document-IdentifiersAll-Identifiers-Identifier2-Type"]', 'Uuid');
        $this->assertQueryContentContains('//*[@id="Document-IdentifiersAll-Identifiers-Identifier2-Value"]', '123');

        $this->assertQueryContentContains('//*[@id="Document-IdentifiersAll-Identifiers-Identifier3-Type"]', 'ISBN');
        $this->assertQueryContentContains('//*[@id="Document-IdentifiersAll-Identifiers-Identifier3-Value"]', '123');

        $this->assertQueryContentContains('//*[@id="Document-IdentifiersAll-Identifiers-Identifier4-Type"]', 'Handle');
        $this->assertQueryContentContains('//*[@id="Document-IdentifiersAll-Identifiers-Identifier4-Value"]', '123');

        $this->assertQueryContentContains('//*[@id="Document-IdentifiersAll-Identifiers-Identifier5-Type"]', 'URL');
        $this->assertQueryContentContains('//*[@id="Document-IdentifiersAll-Identifiers-Identifier5-Value"]', '123');

        $this->assertQueryContentContains('//*[@id="Document-IdentifiersAll-Identifiers-Identifier6-Type"]', 'ISSN');
        $this->assertQueryContentContains('//*[@id="Document-IdentifiersAll-Identifiers-Identifier6-Value"]', '123');

        $this->assertQueryContentContains('//*[@id="Document-IdentifiersAll-Identifiers-Identifier7-Type"]', 'STD-DO');
        $this->assertQueryContentContains('//*[@id="Document-IdentifiersAll-Identifiers-Identifier7-Value"]', '123');

        $this->assertQueryContentContains('//*[@id="Document-IdentifiersAll-Identifiers-Identifier8-Type"]', 'CRIS-Link');
        $this->assertQueryContentContains('//*[@id="Document-IdentifiersAll-Identifiers-Identifier8-Value"]', '123');

        $this->assertQueryContentContains('//*[@id="Document-IdentifiersAll-Identifiers-Identifier9-Type"]', 'SplashURL');
        $this->assertQueryContentContains('//*[@id="Document-IdentifiersAll-Identifiers-Identifier9-Value"]', '123');

        $this->assertQueryContentContains('//*[@id="Document-IdentifiersAll-Identifiers-Identifier10-Type"]', 'Opus3 Id');
        $this->assertQueryContentContains('//*[@id="Document-IdentifiersAll-Identifiers-Identifier10-Value"]', '123');

        $this->assertQueryContentContains('//*[@id="Document-IdentifiersAll-Identifiers-Identifier11-Type"]', 'Opac Id');
        $this->assertQueryContentContains('//*[@id="Document-IdentifiersAll-Identifiers-Identifier11-Value"]', '123');

        $this->assertQueryContentContains('//*[@id="Document-IdentifiersAll-Identifiers-Identifier12-Type"]', 'Pubmed Id');
        $this->assertQueryContentContains('//*[@id="Document-IdentifiersAll-Identifiers-Identifier12-Value"]', '123');

        $this->assertQueryContentContains('//*[@id="Document-IdentifiersAll-Identifiers-Identifier13-Type"]', 'ArXiv Id');
        $this->assertQueryContentContains('//*[@id="Document-IdentifiersAll-Identifiers-Identifier13-Value"]', '123');

        // Lizenzen (Name der Lizenz nicht übersetzt)
        $this->assertQueryContentContains('//fieldset[@id="fieldset-Licences"]/legend', 'Licences');
        $this->assertQueryContentContains('//*[@id="Document-Licences-licence4-label"]', 'Creative Commons - CC BY-ND - Namensnennung');

        // Patents
        $this->assertQueryContentContains('//*[@id="Document-Patents-Patent0-Number"]', '1234');
        $this->assertQueryContentContains('//*[@id="Document-Patents-Patent0-Countries"]', 'DDR');
        $this->assertQueryContentContains('//*[@id="Document-Patents-Patent0-YearApplied"]', '1970');
        $this->assertQueryContentContains('//*[@id="Document-Patents-Patent0-Application"]', 'The foo machine.');
        $this->assertQueryContentContains('//*[@id="Document-Patents-Patent0-DateGranted"]', '1970/01/01');

        // Notes
        $this->assertQueryContentContains('//*[@id="Document-Notes-Note0-Visibility"]', 'Public');
        $this->assertQueryContentContains('//*[@id="Document-Notes-Note0-Message"]', 'Für die Öffentlichkeit');
        $this->assertQueryContentContains('//*[@id="Document-Notes-Note1-Visibility"]', 'Private');
        $this->assertQueryContentContains('//*[@id="Document-Notes-Note1-Message"]', 'Für den Admin');

        // Files
        $this->assertQueryContentContains('//*[@id="Document-Files-File0-Label"]/a', 'foo-pdf');
        $this->assertQueryContentContains('//*[@id="Document-Files-File0-FileSize"]', '8.61 KB');
        $this->assertQueryContentContains('//*[@id="Document-Files-File0-MimeType"]', 'application/pdf');
        $this->assertQueryContentContains('//*[@id="Document-Files-File0-Language"]', 'German');
        $this->assertQueryContentContains('//*[@id="Document-Files-File0-VisibleInFrontdoor"]', 'Yes');
        $this->assertQueryContentContains('//*[@id="Document-Files-File0-VisibleInOai"]', 'Yes');
    }

    /**
     * Asserts that document files are displayed up in the correct order, if the sort order field is set.
     */
    public function testFilesWithSortOrder() {
        $this->dispatch('/admin/document/index/id/155');
        $body = $this->_response->getBody();
        $positionFile1 = strpos($body, 'oai_invisible.txt');
        $positionFile2 = strpos($body, 'test.txt');
        $positionFile3 = strpos($body, 'test.pdf');
        $positionFile4 = strpos($body, 'frontdoor_invisible.txt');
        $this->assertTrue($positionFile1 < $positionFile2);
        $this->assertTrue($positionFile2 < $positionFile3);
        $this->assertTrue($positionFile3 < $positionFile4);
    }

    /**
     * Asserts that document files are displayed up in the correct order, if the sort order field is NOT set.
     */
    public function testDocumentFilesWithoutSortOrder() {
        $this->dispatch('/admin/document/index/id/92');
        $body = $this->_response->getBody();
        $positionFile1 = strpos($body, 'test.xhtml');
        $positionFile2 = strpos($body, 'datei mit unüblichem Namen.xhtml');
        $this->assertTrue($positionFile1 < $positionFile2);
    }

    public function testFrontdoorLinkWithoutIdParameter() {
        $this->dispatch('/admin/document/index/id/146');
        $this->assertXpath('//ul[@class = "form-action"]/li[@class = "frontdoor"]/a[contains(@href, "docId/146")]');
        $this->assertXpathCountMax(
            '//ul[@class = "form-action"]/li[@class = "frontdoor"]/a[contains(@href, "id/146")]', 0,
            'Parameter \'id\' should not appear in link to frontdoor.');
    }

    /**
     * Run in separate process so fatal error won't stop build completely.
     * TODO OPUSVIER-3399 @ runInSeparateProcess
     */
    public function testShowDocumentWithFilesWithLanguageNull() {
        $doc = $this->createTestDocument();
        $file = $this->createTestFile('nolang.pdf');

        $file->setLanguage(null);

        $doc->addFile($file);
        $docId = $doc->store();

        $this->dispatch("/admin/document/index/id/$docId");

        $body = $this->getResponse()->getBody();

        $this->checkForCustomBadStringsInHtml($body, array(
            'Catchable fatal error',
            'Object of class Zend_View_Helper_Translate could not be converted to string',
            'Application/View/Parial/filerow.phtml'
        ));
    }

    public function testUnableToTranslateForMetadataView() {
        $logger = new MockLogger();
        Zend_Registry::set('Zend_Log', $logger);

        $adapter = Zend_Registry::get('Zend_Translate')->getAdapter();
        $options = $adapter->getOptions();
        $options['log'] = $logger;
        $adapter->setOptions($options);

        $this->dispatch('/admin/document/index/id/146');

        $failedTranslations = array();

        foreach ($logger->getMessages() as $line) {
            if (strpos($line, 'Unable to translate') !== false) {
                $failedTranslations[] = $line;
            }
        }

        $output = Zend_Debug::dump($failedTranslations, null, false);

        // until all messages can be prevented less than 20 is good enough
        $this->assertLessThanOrEqual(20, count($failedTranslations), $output);
    }

    public function testUnableToTranslateForEditForm() {
        $logger = new MockLogger();
        Zend_Registry::set('Zend_Log', $logger);

        $adapter = Zend_Registry::get('Zend_Translate')->getAdapter();
        $options = $adapter->getOptions();
        $options['log'] = $logger;
        $adapter->setOptions($options);

        $this->dispatch('/admin/document/edit/id/146');

        $failedTranslations = array();

        foreach ($logger->getMessages() as $line) {
            if (strpos($line, 'Unable to translate') !== false) {
                $failedTranslations[] = $line;
            }
        }

        $output = Zend_Debug::dump($failedTranslations, null, false);

        // until all messages can be prevented less than 20 is good enough
        $this->assertLessThanOrEqual(20, count($failedTranslations), $output);
    }

    public function testRedirectToLogin()
    {
        $this->enableSecurity();

        $this->dispatch('/admin/document/index/id/1');

        $this->assertRedirectTo('/auth/index/rmodule/admin/rcontroller/document/raction/index/id/1');
    }

}
