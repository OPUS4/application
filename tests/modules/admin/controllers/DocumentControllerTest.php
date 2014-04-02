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
 * @category    Unit Tests
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Unit tests for Admin_DocumentController.
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
            '#fieldset-Identifiers' => 'Identifier',
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
        
        $doc->deletePermanent();

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
        
        $doc->deletePermanent();
        
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
        
        $doc->deletePermanent();
        
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
        $this->assertQueryContentContains('div.breadcrumbsContainer', 'KOBV (146)');
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

            'input#Document-Identifiers-Add',
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

}
