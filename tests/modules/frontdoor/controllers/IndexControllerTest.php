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

use Opus\Common\Collection;
use Opus\Common\CollectionRole;
use Opus\Common\Config;
use Opus\Common\Date;
use Opus\Common\Document;
use Opus\Common\DocumentInterface;
use Opus\Common\Note;
use Opus\Common\Subject;
use Opus\Common\Title;

/**
 * @covers Frontdoor_IndexController
 */
class Frontdoor_IndexControllerTest extends ControllerTestCase
{
    /** @var string */
    protected $additionalResources = 'all';

    /** @var DocumentInterface  Document to count on :) */
    protected $document;

    /** @var DocumentInterface */
    protected $documentCol;

    /**
     * Provide clean documents and statistics table and remove temporary files.
     * Create document for counting.
     */
    public function setUp(): void
    {
        parent::setUpWithEnv('production');
        $this->assertSecurityConfigured();

        $path = Config::getInstance()->getTempPath() . '~localstat.xml';
        @unlink($path);

        $this->document = $this->createTestDocument();
        $this->document->setType("doctoral_thesis");

        $title = Title::new();
        $title->setLanguage('deu');
        $title->setValue('Titel');
        $this->document->addTitleMain($title);

        $title = Title::new();
        $title->setLanguage('eng');
        $title->setValue('Title');
        $this->document->addTitleMain($title);

        $this->document->store();

        //setting server globals
        $_SERVER['REMOTE_ADDR']     = '127.0.0.1';
        $_SERVER['HTTP_USER_AGENT'] = 'bla';
        $_SERVER['REDIRECT_STATUS'] = 200;

        // create collection test document
        $this->documentCol = $this->createTestDocument();
        $this->documentCol->addCollection(Collection::get(40)); // invisible collection
        $this->documentCol->addCollection(Collection::get(16214)); // visible collection with invisible collection role
        $this->documentCol->addCollection(Collection::get(1031)); // visible collection with visible collection role

        // collection role ID = 10 (sichbar)
        $this->documentCol->addCollection(Collection::get(16136)); // versteckte Collection (Role = 10)
        $this->documentCol->addCollection(Collection::get(15991)); // sichbare Collection (Role = 10);
        $this->documentCol->setServerState('published');
        $this->documentCol->store();
    }

    public function testIndexActionOnPublished()
    {
        $this->document->setServerState('published')->store();
        $docId = $this->document->getId();
        $this->dispatch('/frontdoor/index/index/docId/' . $docId);

        $this->assertResponseCode(200);
        $this->assertController('index');
        $this->assertAction('index');

        $response = $this->getResponse();
        $this->checkForBadStringsInHtml($response->getBody());
        $this->assertContains('<div class="frontdoor">', $response->getBody());
    }

    public function testIndexActionOnDeleted()
    {
        $this->document->setServerState('deleted')->store();
        $docId = $this->document->getId();
        $this->dispatch('/frontdoor/index/index/docId/' . $docId);

        $this->assertResponseCode(410);
        $this->assertController('index');
        $this->assertAction('index');

        $response = $this->getResponse();
        $this->assertContains('<div class="frontdoor-error">', $response->getBody());
    }

    public function testIndexActionOnUnpublished()
    {
        $this->document->setServerState('unpublished')->store();
        $docId = $this->document->getId();
        $this->dispatch('/frontdoor/index/index/docId/' . $docId);

        $this->assertResponseCode(403);
        $this->assertController('index');
        $this->assertAction('index');

        $response = $this->getResponse();
        $this->assertContains('<div class="frontdoor-error">', $response->getBody());
    }

    public function testIndexActionOnTemporary()
    {
        $this->document->setServerState('temporary')->store();
        $docId = $this->document->getId();
        $this->dispatch('/frontdoor/index/index/docId/' . $docId);

        $this->assertResponseCode(403);
        $this->assertController('index');
        $this->assertAction('index');

        $response = $this->getResponse();
        $this->assertContains('<div class="frontdoor-error">', $response->getBody());
    }

    public function testIndexActionOnNonExistent()
    {
        $docId = $this->document->getId();
        $this->dispatch('/frontdoor/index/index/docId/' . $docId . $docId . '100');

        $this->assertResponseCode(404);
        $this->assertController('index');
        $this->assertAction('index');

        $response = $this->getResponse();
        $this->assertContains('<div class="frontdoor-error">', $response->getBody());
    }

    /**
     * @deprecated since OPUS 3.0.2, the function under test is marked as deprecated
     * and will be removed in future releases
     */
    public function testMapopus3Action()
    {
        $opus3Id = 'foobar-' . rand();
        $this->document->addIdentifierOpus3()->setValue($opus3Id);
        $docId = $this->document->store();

        $this->dispatch('/frontdoor/index/mapopus3/oldId/' . $opus3Id);

        $this->assertResponseCode(302);
        $this->assertModule('frontdoor');
        $this->assertController('index');
        $this->assertAction('mapopus3');

        $response = $this->getResponse();
        $headers  = $response->getHeaders();

        $this->assertEquals('Location', $headers[0]['name']);
        $this->assertStringEndsWith('/rewrite/index/id/type/opus3-id/value/' . $opus3Id, $headers[0]['value']);

        $this->checkForBadStringsInHtml($response->getBody());
    }

    /**
     * test to document bug OPUSVIER-1695
     */
    public function testUncontrolledKeywordHeaderIsNotDisplayedIfUncontrolledKeywordsDoNotExist()
    {
        $this->dispatch('/frontdoor/index/index/docId/92');
        $this->assertResponseCode(200);
        $this->assertModule('frontdoor');
        $this->assertController('index');
        $this->assertAction('index');
        $body = $this->getResponse()->getBody();
        $this->assertContains('<table class="result-data frontdoordata">', $body);
        $this->assertNotContains('<td><em class="data-marker"/></td>', $body);
    }

    /**
     * Regression test for OPUSVIER-2165
     */
    public function testFrontdoorTitleRespectsDocumentLanguageDeu()
    {
        $docId = $this->document->getId();

        $doc = Document::get($docId);
        $doc->setLanguage('deu');
        $doc->setServerState('published');
        $doc->store();

        $this->dispatch("/frontdoor/index/index/docId/$docId");

        $this->assertContains('<title>OPUS 4 | Titel</title>', $this->getResponse()->getBody());
        $this->assertNotContains('<title>OPUS 4 | Title</title>', $this->getResponse()->getBody());
    }

    /**
     * Regression test for OPUSVIER-2165
     */
    public function testFrontdoorTitleRespectsDocumentLanguageEng()
    {
        $docId = $this->document->getId();

        $doc = Document::get($docId);
        $doc->setLanguage('eng');
        $doc->setServerState('published');
        $doc->store();

        $this->dispatch("/frontdoor/index/index/docId/$docId");

        $this->assertContains('<title>OPUS 4 | Title</title>', $this->getResponse()->getBody());
        $this->assertNotContains('<title>OPUS 4 | Titel</title>', $this->getResponse()->getBody());
    }

    /**
     * Regression test for OPUSVIER-2165
     *
     * if database does not contain a title in the document's language,
     * the first title is used as page title
     */
    public function testFrontdoorTitleRespectsDocumentLanguageWithoutCorrespondingTitle()
    {
        $docId = $this->document->getId();

        $doc = Document::get($docId);
        $doc->setLanguage('fra');
        $doc->setServerState('published');
        $doc->store();

        $this->dispatch("/frontdoor/index/index/docId/$docId");

        $this->assertNotContains('<title>OPUS 4 | Title</title>', $this->getResponse()->getBody());
        $this->assertContains('<title>OPUS 4 | Titel</title>', $this->getResponse()->getBody());
    }

    /**
     * Regression test for OPUSVIER-2165
     *
     * if database contains more than one title in the document's language,
     * the first title is used as page title
     */
    public function testFrontdoorTitleRespectsDocumentLanguageMultipleCandidates()
    {
        $docId = $this->document->getId();

        $doc = Document::get($docId);
        $doc->setLanguage('deu');
        $doc->setServerState('published');
        $doc->addTitleMain()->setValue('Titel2')->setLanguage('deu');
        $doc->store();

        $this->dispatch("/frontdoor/index/index/docId/$docId");

        $this->assertNotContains('<title>OPUS 4 | Title</title>', $this->getResponse()->getBody());
        $this->assertNotContains('<title>OPUS 4 | Titel2</title>', $this->getResponse()->getBody());
        $this->assertContains('<title>OPUS 4 | Titel</title>', $this->getResponse()->getBody());
    }

    /**
     * Regression test for OPUSVIER-1924
     */
    public function testIdentifierUrlIsHandledProperlyInFrontdoorForNonProtocolURL()
    {
        $d           = Document::get('91');
        $identifiers = $d->getIdentifierUrl();
        $identifier  = $identifiers[0];
        $this->assertEquals('www.myexampledomain.de/myexamplepath', $identifier->getValue());
        $this->dispatch('/frontdoor/index/index/docId/91');
        $this->assertEquals(2, substr_count($this->getResponse()->getBody(), 'http://www.myexampledomain.de/myexamplepath'));
    }

    /**
     * Regression test for OPUSVIER-1924
     */
    public function testIdentifierUrlIsHandledProperlyInFrontdoorForProtocolURL()
    {
        $d           = Document::get('92');
        $identifiers = $d->getIdentifierUrl();
        $identifier  = $identifiers[0];
        $this->assertEquals('http://www.myexampledomain.de/myexamplepath', $identifier->getValue());
        $this->dispatch('/frontdoor/index/index/docId/92');
        $this->assertEquals(2, substr_count($this->getResponse()->getBody(), 'http://www.myexampledomain.de/myexamplepath'));
    }

    /**
     * Regression test for OPUSVIER-1647
     */
    public function testUrlEscapedFileNameDoc1()
    {
        $d             = Document::get(1);
        $filePathnames = [];
        foreach ($d->getFile() as $file) {
            $filePathnames[] = $file->getPathName();
        }

        $filenameNormal = 'asis-hap.pdf';
        $filenameWeird  = 'asis-hap_\'.pdf';
        $this->assertContains($filenameNormal, $filePathnames, "testdata changed!");
        $this->assertContains($filenameWeird, $filePathnames, "testdata changed!");

        $this->dispatch('/frontdoor/index/index/docId/1');

        $responseBody = $this->getResponse()->getBody();
        $this->assertRegExp('/<a class="application_pdf" href="[^"]+\/1\/asis-hap.pdf"/', $responseBody);
        $this->assertRegExp('/<a class="application_pdf" href="[^"]+\/1\/asis-hap_%27.pdf"/', $responseBody);
        $this->assertNotRegExp('/<a class="application_pdf" href="[^"]+\/1\/asis-hap_\'.pdf"/', $responseBody);
    }

    /**
     * Regression test for OPUSVIER-1647
     */
    public function testUrlEscapedFileNameDoc147()
    {
        $d             = Document::get(147);
        $filePathnames = [];
        foreach ($d->getFile() as $file) {
            $filePathnames[] = $file->getPathName();
        }

        $filenameNormal = 'special-chars-%-"-#-&.pdf';
        $filenameWeird  = "'many'  -  spaces  and  quotes.pdf";
        $this->assertContains($filenameNormal, $filePathnames, "testdata changed!");
        $this->assertContains($filenameWeird, $filePathnames, "testdata changed!");

        $this->dispatch('/frontdoor/index/index/docId/147');

        $responseBody = $this->getResponse()->getBody();
        $this->assertRegExp('/<a class="application_pdf" href="[^"]+\/\d+\/special-chars-%25-%22-%23-%26.pdf">/', $responseBody);
        $this->assertRegExp('/<a class="application_pdf" href="[^"]+\/\d+\/%27many%27\+\+-\+\+spaces\+\+and\+\+quotes.pdf">/', $responseBody);
    }

    /**
     * Regression test for OPUSVIER-2129
     */
    public function testSeries146()
    {
        $this->dispatch('/frontdoor/index/index/docId/146');
        $this->assertContains('/solrsearch/index/search/searchtype/series/id/1" ', $this->getResponse()->getBody());
    }

    public function testSubjectSortOrder()
    {
        $this->dispatch('/frontdoor/index/index/docId/1');
        $this->assertContains('Informationssystem; Geschichte; Ostwald, Wilhelm', $this->getResponse()->getBody());
    }

    public function testSubjectSortOrderAlphabetical()
    {
        $this->adjustConfiguration([
            'frontdoor' => ['subjects' => ['alphabeticalSorting' => self::CONFIG_VALUE_TRUE]],
        ]);

        // frontdoor.subjects.alphabeticalSorting

        $this->dispatch('/frontdoor/index/index/docId/1');
        $this->assertContains(
            'Geschichte; Informations- und Dokumentationswissenschaft; Informationssystem',
            $this->getResponse()->getBody()
        );
    }

    /**
     * Regression test for OPUSVIER-2232
     */
    public function testSeries149InVisible()
    {
        $d             = Document::get(149);
        $seriesIds     = [];
        $seriesNumbers = [];
        foreach ($d->getSeries() as $series) {
            $seriesIds[]     = $series->getModel()->getId();
            $seriesNumbers[] = $series->getNumber();
        }

        $this->assertContains('3', $seriesIds);
        $this->assertContains('4', $seriesIds);
        $this->assertContains('id-3-is-invisible', $seriesNumbers);
        $this->assertContains('id-4-is-visible', $seriesNumbers);

        $this->dispatch('/frontdoor/index/index/docId/149');
        $this->assertResponseCode(200);
        $responseBody = $this->getResponse()->getBody();

        // series 3 is NOT visible
        $this->assertNotContains('id-3-is-invisible', $responseBody);
        $this->assertNotRegExp('/href="\/solrsearch\/index\/search\/searchtype\/series\/id\/3"/', $responseBody);

        // series 4 is visible
        $this->assertContains('id-4-is-visible', $responseBody);
        $this->assertRegExp('/href="\/solrsearch\/index\/search\/searchtype\/series\/id\/4"/', $responseBody);
    }

    /**
     * Regression test for OPUSVIER-2435
     */
    public function testUrlEncodedAuthorNamesDoc150()
    {
        $d          = Document::get(150);
        $firstNames = [];
        $lastNames  = [];

        foreach ($d->getPersonAuthor() as $author) {
            $firstNames[] = $author->getFirstName();
            $lastNames[]  = $author->getLastName();
        }

        $this->assertContains('J\"ohn', $firstNames, "testdata changed!");
        $this->assertContains('J\"ane', $firstNames, "testdata changed!");
        $this->assertContains('D\"oe', $lastNames, "testdata changed!");
        $this->dispatch('/frontdoor/index/index/docId/150');

        $responseBody = $this->getResponse()->getBody();
        $this->assertRegExp('/<a href="[^"]+\/author\/J%5C%22ohn\+Doe"/', $responseBody);
        $this->assertRegExp('/<a href="[^"]+\/author\/J%5C%22ane\+D%5C%22oe"/', $responseBody);
        $this->assertNotRegExp('/<a href="[^"]+\/author\/\&quot;J\\\&quot;ohn Doe\&quot;"/', $responseBody);
        $this->assertNotRegExp('/<a href="[^"]+\/author\/\&quot;J\\\&quot;ane D\\\&quot;oe\&quot;"/', $responseBody);
    }

    /**
     * Regression test for OPUSHOSTING-52
     */
    public function testShowLinkForPrintOnDemandIfLicenceAppropriate()
    {
        $this->adjustConfiguration([
            'printOnDemand' => [
                'url'    => 'http://localhost/',
                'button' => '',
            ],
        ]);

        $this->dispatch('/frontdoor/index/index/docId/1');
        $this->assertQuery('div#print-on-demand');
    }

    /**
     * Regression test for OPUSHOSTING-52
     */
    public function testHideLinkForPrintOnDemandIfLicenceNotAppropriate()
    {
        $this->adjustConfiguration([
            'printOnDemand' => [
                'url'    => 'http://localhost/',
                'button' => '',
            ],
        ]);

        $this->dispatch('/frontdoor/index/index/docId/91');
        $this->assertNotQuery('div#print-on-demand');
    }

    /**
     * Regression test for OPUSVIER-2492
     */
    public function testDisplayAllUserDefinedCollectionRoles()
    {
        $this->dispatch('/frontdoor/index/index/docId/151');
        $this->assertQueryContentContains('table.result-data.frontdoordata th.name', 'frontdoor-test-1:');
        $this->assertQueryContentContains('table.result-data.frontdoordata th.name', 'frontdoor-test-2:');
        $this->assertQueryContentRegex('table.result-data.frontdoordata tr', '/frontdoor-test-1.*Test Frontdoor 1.1/');
        $this->assertQueryContentContains('table.result-data.frontdoordata td', 'Test Frontdoor 1.2');
        $this->assertNotQueryContentRegex('table.result-data.frontdoordata tr', '/frontdoor-test-1.*Test Frontdoor 1.2/');
    }

    /**
     * Regression test for OPUSVIER-1951
     *
     * TODO ausbauen und aktivieren
     */
    public function testDisplayAllDocumentFields()
    {
        $this->dispatch('/frontdoor/index/index/docId/146');
        $translate = Application_Translate::getInstance();

        $path = 'table.result-data.frontdoordata th.name';

        $this->assertQuery('h2.titlemain');
        $this->assertQueryContentContains($path, $translate->_('PersonAuthor'));
        $this->assertQuery('div#abstract');
        $this->assertQueryContentContains($path, $translate->_('IdentifierUrn'));
        $this->assertQueryContentContains($path, $translate->_('IdentifierUrl'));
        $this->assertQueryContentContains($path, $translate->_('IdentifierHandle'));
        $this->assertQueryContentContains($path, $translate->_('IdentifierDoi'));
        $this->assertQueryContentContains($path, $translate->_('IdentifierIsbn'));
        $this->assertQueryContentContains($path, $translate->_('IdentifierIssn'));
        $this->assertQueryContentContains($path, $translate->_('IdentifierArxiv'));
        $this->assertQueryContentContains($path, $translate->_('IdentifierPubmed'));
        $this->assertQueryContentContains($path, $translate->_('TitleParent'));
        $this->assertQueryContentContains($path, $translate->_('TitleSub'));
        $this->assertQueryContentContains($path, $translate->_('TitleAdditional'));
        $this->assertQueryContentContains($path, $translate->_('Series'));
        $this->assertQueryContentContains($path, $translate->_('PublisherName'));
        $this->assertQueryContentContains($path, $translate->_('PublisherPlace'));
        $this->assertQueryContentContains($path, $translate->_('PersonEditor'));
        $this->assertQueryContentContains($path, $translate->_('PersonTranslator'));
        $this->assertQueryContentContains($path, $translate->_('PersonContributor'));
        $this->assertQueryContentContains($path, $translate->_('PersonReferee'));
        $this->assertQueryContentContains($path, $translate->_('PersonAdvisor'));
        $this->assertQueryContentContains($path, $translate->_('Type'));
        $this->assertQueryContentContains($path, $translate->_('Language'));
        $this->assertQueryContentContains($path, $translate->_('CompletedDate'));
        $this->assertQueryContentContains($path, $translate->_('PublishedDate'));
        $this->assertQueryContentContains($path, $translate->_('ThesisPublisher'));
        $this->assertQueryContentContains($path, $translate->_('ThesisGrantor'));
        $this->assertQueryContentContains($path, $translate->_('ThesisDateAccepted'));
        $this->assertQueryContentContains($path, $translate->_('CreatingCorporation'));
        $this->assertQueryContentContains($path, $translate->_('ContributingCorporation'));
        $this->assertQueryContentContains($path, $translate->_('ServerDatePublished'));
        $this->assertQueryContentContains($path, $translate->_('subject_frontdoor_swd'));
        $this->assertQueryContentContains($path, $translate->_('subject_frontdoor_uncontrolled'));
        $this->assertQueryContentContains($path, $translate->_('Volume'));
        $this->assertQueryContentContains($path, $translate->_('Issue'));
        $this->assertQueryContentContains($path, $translate->_('ArticleNumber'));
        $this->assertQueryContentContains($path, $translate->_('Edition'));
        $this->assertQueryContentContains($path, $translate->_('PageNumber'));
        $this->assertQueryContentContains($path, $translate->_('PageFirst'));
        $this->assertQueryContentContains($path, $translate->_('PageLast'));
        $this->assertQueryContentContains($path, $translate->_('Note'));
        // Enrichments
        $this->assertQueryContentContains($path, $translate->_('EnrichmentEvent'));
        $this->assertQueryContentContains($path, $translate->_('EnrichmentCity'));
        $this->assertQueryContentContains($path, $translate->_('EnrichmentCountry'));
        // Opus3 Enrichments
        $this->assertQueryContentContains($path, $translate->_('EnrichmentSourceTitle'));
        $this->assertQueryContentContains($path, $translate->_('EnrichmentSourceSwb'));
        $this->assertQueryContentContains($path, $translate->_('EnrichmentClassRvk'));
        $this->assertQueryContentContains($path, $translate->_('EnrichmentContributorsName'));

        $this->assertQueryContentContains($path, $translate->_('default_collection_role_institutes'));
        $this->assertQueryContentContains($path, $translate->_('default_collection_role_ccs'));
        $this->assertQueryContentContains($path, $translate->_('default_collection_role_ddc'));
        $this->assertQueryContentContains($path, $translate->_('default_collection_role_msc'));
        $this->assertQueryContentContains($path, $translate->_('default_collection_role_pacs'));
        $this->assertQueryContentContains($path, $translate->_('default_collection_role_bk'));
        $this->assertQueryContentContains($path, $translate->_('default_collection_role_jel'));

        $this->assertQueryContentContains($path, $translate->_('IdentifierSerial'));

        $this->assertQueryContentContains($path, $translate->_('Licence'));
    }

    public function testAbstractPreserveSpace()
    {
        $doc = $this->createTestDocument();
        $doc->setLanguage("eng");
        $doc->setServerState("published");

        $abstract = Title::new();
        $abstract->setLanguage("eng");
        $abstract->setValue("foo\nbar\n\nbaz");
        $doc->addTitleAbstract($abstract);

        $doc->store();

        $this->dispatch('/frontdoor/index/index/docId/' . $doc->getId());

        $this->assertXpathContentContains(
            '//li[contains(@class = "abstract preserve-spaces", @lang="en")]',
            "foo\nbar\n\nbaz",
            $this->getResponse()->getBody()
        );
    }

    public function testNotePerserveSpace()
    {
        $doc = $this->createTestDocument();
        $doc->setLanguage("eng");
        $doc->setServerState("published");

        $note = Note::new();
        $note->setMessage("foo\nbar\n\nbaz");
        $note->setVisibility("public");
        $doc->addNote($note);

        $doc->store();

        $this->dispatch('/frontdoor/index/index/docId/' . $doc->getId());

        $this->assertContains(
            '<pre class="preserve-spaces">' . "foo\nbar\n\nbaz</pre>",
            $this->getResponse()->getBody()
        );
    }

    /**
     * Regression Test for OPUSVIER-2651
     */
    public function testOPUSVIER2651NameNumber()
    {
        $role             = CollectionRole::get(7);
        $displayFrontdoor = $role->getDisplayFrontdoor();
        $role->setDisplayFrontdoor('Name,Number');
        $role->store();

        $this->dispatch('/frontdoor/index/index/docId/89');

        // undo changes
        $role->setDisplayFrontdoor($displayFrontdoor);
        $role->store();

        $this->assertQueryContentContains(
            'td',
            'Maschinenbau, Energietechnik, Fertigungstechnik: Allgemeines 52.00',
            $this->getResponse()->getBody()
        );
    }

    /**
     * Regression Test for OPUSVIER-2651
     */
    public function testOPUSVIER2651NumberName()
    {
        $role             = CollectionRole::get(7);
        $displayFrontdoor = $role->getDisplayFrontdoor();
        $role->setDisplayFrontdoor('Number,Name');
        $role->store();

        $this->dispatch('/frontdoor/index/index/docId/89');

        // undo changes
        $role->setDisplayFrontdoor($displayFrontdoor);
        $role->store();

        $this->assertQueryContentContains(
            'td',
            '52.00 Maschinenbau, Energietechnik, Fertigungstechnik: Allgemeines',
            $this->getResponse()->getBody()
        );
    }

    /**
     * Regression Test for OPUSVIER-2651
     */
    public function testOPUSVIER2651Name()
    {
        $role             = CollectionRole::get(7);
        $displayFrontdoor = $role->getDisplayFrontdoor();
        $role->setDisplayFrontdoor('Name');
        $role->store();

        $this->dispatch('/frontdoor/index/index/docId/89');

        // undo changes
        $role->setDisplayFrontdoor($displayFrontdoor);
        $role->store();

        $this->assertNotQueryContentContains('td', '52.00', $this->getResponse()->getBody());
        $this->assertQueryContentContains(
            'td',
            'Maschinenbau, Energietechnik, Fertigungstechnik: Allgemeines',
            $this->getResponse()->getBody()
        );
    }

    /**
     * Regression Test for OPUSVIER-2651
     */
    public function testOPUSVIER2651Number()
    {
        $role             = CollectionRole::get(7);
        $displayFrontdoor = $role->getDisplayFrontdoor();
        $role->setDisplayFrontdoor('Number');
        $role->store();

        $this->dispatch('/frontdoor/index/index/docId/89');

        // undo changes
        $role->setDisplayFrontdoor($displayFrontdoor);
        $role->store();

        $this->assertQueryContentContains('td', '52.00', $this->getResponse()->getBody());
        $this->assertNotQueryContentContains(
            'td',
            'Maschinenbau, Energietechnik, Fertigungstechnik: Allgemeines',
            $this->getResponse()->getBody()
        );
    }

    public function testCollectionDisplayed()
    {
        $this->useEnglish();

        $this->dispatch('/frontdoor/index/index/docId/' . $this->documentCol->getId());

        // Sichtbare Collection mit sichtbarer CollectionRole wird angezeigt (Visible = 1, RoleVisibleFrontdoor = true)
        $this->assertQueryContentContains('table.result-data.frontdoordata th.name', 'CCS-Classification:');
        $this->assertQueryContentContains('td', 'B. Hardware');
    }

    public function testRegression3148InvisibleCollectionRoleNotDisplayed()
    {
        $this->useEnglish();

        $this->dispatch('/frontdoor/index/index/docId/' . $this->documentCol->getId());

        // Unsichtbare CollectionRole (Visible = 0, RoleVisibleFrontdoor = false)
        $this->assertNotQueryContentContains('table.result-data.frontdoordata th.name', 'invisible-collection:');
    }

    public function testRegression3148InvisibleCollectionNotDisplayed()
    {
        $this->useEnglish();

        $this->dispatch('/frontdoor/index/index/docId/' . $this->documentCol->getId());

        // Unsichtbare Collection
        $this->assertNotQueryContentContains('td', '28 Christliche Konfessionen');

        // CollectionRole wird nicht angezeigt, da keine sichtbare Collection vorhanden ist
        $this->assertNotQueryContentContains(
            'table.result-data.frontdoordata th.name',
            'Dewey Decimal Classification:'
        );
    }

    public function testRegression3148DisplayCollectionRoleWithVisibleAndInvisibleCollections()
    {
        $this->useEnglish();

        $this->dispatch('/frontdoor/index/index/docId/' . $this->documentCol->getId());

        // CollectionRole wird angezeigt
        $this->assertQueryContentContains('table.result-data.frontdoordata th.name', 'series:');

        $this->assertQueryContentContains('td', 'Schriftenreihe Schiffbau');
        $this->assertNotQueryContentContains('td', 'Band 363');
    }

    public function testServerDatePublishedOnFrontdoor()
    {
        $this->useGerman();
        $this->dispatch('/frontdoor/index/index/docId/146');
        $this->assertContains('<td>03.01.2012</td>', $this->getResponse()->getBody());
    }

    /**
     * Regression Test for OPUSVIER-3159
     */
    public function testGrantorDepartmentVisibleInFrontdoor()
    {
        $this->useGerman();
        $this->dispatch('/frontdoor/index/index/docId/146');
        $this->assertContains(
            '<tr><th class="name">Titel verleihende Institution:</th>'
            . '<td>Foobar Universität, Testwissenschaftliche Fakultät</td></tr>',
            $this->getResponse()->getBody()
        );
    }

    public function testValidateXHTML()
    {
        $this->dispatch('/frontdoor/index/index/docId/146');
        $this->assertResponseCode(200);
        $this->validateXHTML();
    }

    public function testValidateXHTMLWithShortendAbstracts()
    {
        // Aktiviere Kürzung von Abstrakten
        $this->adjustConfiguration(
            ['frontdoor' => ['numOfShortAbstractChars' => '200']]
        );

        $this->dispatch('/frontdoor/index/index/docId/92');
        $this->assertResponseCode(200);
        $this->validateXHTML();
    }

    public function testDisplayFullCollectionName()
    {
        $this->useGerman();
        $this->dispatch('/frontdoor/index/index/docId/146');
        $this->assertQueryContentContains(
            'td',
            'Technische Universität Hamburg-Harburg / Bauwesen / Abwasserwirtschaft und Gewässerschutz B-2'
        );
    }

    public function testDisplayCollectionLink()
    {
        $this->useGerman();
        $this->dispatch('/frontdoor/index/index/docId/146');
        $this->assertContains(
            '<a href="/solrsearch/index/search/searchtype/collection/id/16007" title="Sammlung anzeigen">'
            . 'Technische Universität Hamburg-Harburg / Bauwesen / Abwasserwirtschaft und Gewässerschutz B-2</a>',
            $this->getResponse()->getBody()
        );
    }

    /**
     * Regression Test for OPUSVIER-2414
     */
    public function testIncludeMetaDateCitationDateIfPublishedYearSet()
    {
        $this->dispatch('/frontdoor/index/index/docId/145');
        $this->assertContains(
            '<meta name="citation_date" content="2011" />',
            $this->getResponse()->getBody()
        );
    }

    public function testPatentInformationGerman()
    {
        $this->useGerman();
        $this->dispatch("/frontdoor/index/index/docId/146");
        $this->assertQueryContentContains('//th', 'Patentnummer:');
        $this->assertQueryContentContains('//tr', '1234');
        $this->assertQueryContentContains('//th', 'Land der Patentanmeldung:');
        $this->assertQueryContentContains('//tr', 'DDR');
        $this->assertQueryContentContains('//th', 'Jahr der Patentanmeldung:');
        $this->assertQueryContentContains('//tr', '1970');
        $this->assertQueryContentContains('//th', 'Patentanmeldung:');
        $this->assertQueryContentContains('//tr', 'The foo machine.');
        $this->assertQueryContentContains('//th', 'Datum der Patenterteilung:');
        $this->assertQueryContentContains('//tr', '01.01.1970');
    }

    public function testPatentInformationEnglish()
    {
        $this->useEnglish();
        $this->dispatch("/frontdoor/index/index/docId/146");
        $this->assertQueryContentContains('//th', 'Patent Number:');
        $this->assertQueryContentContains('//tr', '1234');
        $this->assertQueryContentContains('//th', 'Country of Patent Application:');
        $this->assertQueryContentContains('//tr', 'DDR');
        $this->assertQueryContentContains('//th', 'Patent Application Year:');
        $this->assertQueryContentContains('//tr', '1970');
        $this->assertQueryContentContains('//th', 'Patent Application:');
        $this->assertQueryContentContains('//tr', 'The foo machine.');
        $this->assertQueryContentContains('//th', 'Patent Grant Date:');
        $this->assertQueryContentContains('//tr', '1970/01/01');
    }

    public function testPatentInformationMultiple()
    {
        $this->markTestSkipped("Document 200 ist für Löschtests, daher fehlt das zweite Patent unter Umständen.");

        $this->useEnglish();
        $this->dispatch("/frontdoor/index/index/docId/200");
        $this->assertQueryContentContains('//th', 'Patent Number:');
        $this->assertQueryContentContains('//tr', '1234');
        $this->assertQueryContentContains('//tr', '4321');
        $this->assertQueryContentContains('//th', 'Country of Patent Application:');
        $this->assertQueryContentContains('//tr', 'DDR');
        $this->assertQueryContentContains('//tr', 'BRD');
        $this->assertQueryContentContains('//th', 'Patent Application Year:');
        $this->assertQueryContentContains('//tr', '1970');
        $this->assertQueryContentContains('//tr', '1972');
        $this->assertQueryContentContains('//th', 'Patent Application:');
        $this->assertQueryContentContains('//tr', 'The foo machine.');
        $this->assertQueryContentContains('//tr', 'The bar machine.');
        $this->assertQueryContentContains('//th', 'Patent Grant Date:');
        $this->assertQueryContentContains('//tr', '1970/01/01');
        $this->assertQueryContentContains('//tr', '1972/01/01');
    }

    public function testRegression3118()
    {
        $this->useEnglish();
        $this->enableSecurity();
        $this->loginUser('admin', 'adminadmin');
        $this->dispatch('/frontdoor/index/index/docId/146');
        $this->assertNotQueryContentContains(
            '//dl[@id="Document-ServerState"]//li[@class="active"]',
            'Publish document'
        );
        $this->assertQueryContentContains(
            '//dl[@id="Document-ServerState"]//li[@class="active"]',
            'Published'
        );
    }

    /**
     * Regression Tests for OPUSVIER-2813
     */
    public function testDateFormatGerman()
    {
        $this->useGerman();
        $this->dispatch("/frontdoor/index/index/docId/91");
        $this->assertQueryContentContains('//th', 'Datum der Abschlussprüfung');
        $this->assertQueryContentContains('//tr', '26.02.2010');
        $this->assertQueryContentContains('//th', 'Datum der Freischaltung');
        $this->assertQueryContentContains('//tr', '05.03.2010');
    }

    public function testDateFormatEnglish()
    {
        $this->useEnglish();
        $this->dispatch("/frontdoor/index/index/docId/91");
        $this->assertQueryContentContains('//th', 'Date of final exam');
        $this->assertQueryContentContains('//tr', '2010/02/26');
        $this->assertQueryContentContains('//th', 'Release Date');
        $this->assertQueryContentContains('//tr', '2010/03/05');
    }

    /**
     * Asserts that document files are displayed up in the custom order according to the sort order field, if specified
     * in the config.
     */
    public function testFilesInCustomSortOrder()
    {
        $config                                  = $this->getConfig();
        $config->frontdoor->files->customSorting = '1';

        $this->dispatch('/frontdoor/index/index/docId/155');

        $body          = $this->getResponse()->getBody();
        $positionFile1 = strpos($body, 'oai_invisible.txt');
        $positionFile2 = strpos($body, 'test.txt');
        $positionFile3 = strpos($body, 'test.pdf');
        $this->assertTrue($positionFile1 < $positionFile2);
        $this->assertTrue($positionFile1 < $positionFile3);
        $this->assertTrue($positionFile2 < $positionFile3);
    }

    /**
     * Asserts that document files are displayed up in alphabetic order, if specified in the config.
     */
    public function testFilesInAlphabeticSortOrder()
    {
        $config                                  = $this->getConfig();
        $config->frontdoor->files->customSorting = self::CONFIG_VALUE_FALSE;

        $this->dispatch('/frontdoor/index/index/docId/155');

        $body          = $this->getResponse()->getBody();
        $positionFile1 = strpos($body, 'oai_invisible.txt');
        $positionFile2 = strpos($body, 'test.pdf');
        $positionFile3 = strpos($body, 'test.txt');
        $this->assertTrue($positionFile1 < $positionFile2);
        $this->assertTrue($positionFile1 < $positionFile3);
        $this->assertTrue($positionFile2 < $positionFile3);
    }

    /**
     * Checks, whether the document language title is printed before other titles.
     * OPUSVIER-1752
     * OPUSVIER-3316
     */
    public function testTitleSortOrderGermanFirst()
    {
        $functions = ['addTitleMain', 'addTitleParent', 'addTitleSub', 'addTitleAdditional', 'addTitleAbstract'];
        foreach ($functions as $function) {
            $doc   = $this->createTestDocument();
            $title = Title::new();
            $title->setLanguage('deu');
            $title->setValue('deutscher Titel');
            $doc->$function($title);

            $title = Title::new();
            $title->setLanguage('eng');
            $title->setValue('englischer Titel');
            $doc->$function($title);

            $doc->setLanguage('deu');
            $doc->setServerState('published');
            $docId = $doc->store();

            $this->dispatch('/frontdoor/index/index/docId/' . $docId);

            // Absicherung gegen HTML Aenderungen;  in Meta-Tags steht Text in Attribut
            $this->assertEquals(
                1,
                substr_count($this->getResponse()->getBody(), '>deutscher Titel<'),
                'Teststring is found more than once; test is not reliable anymore'
            );
            $this->assertEquals(
                1,
                substr_count($this->getResponse()->getBody(), '>englischer Titel<'),
                'Teststring is found more than once; test is not reliable anymore'
            );

            $title1 = strpos($this->getResponse()->getBody(), '>deutscher Titel<');
            $title2 = strpos($this->getResponse()->getBody(), '>englischer Titel<');
            $this->assertTrue($title1 < $title2);
            $this->getResponse()->clearBody();
        }
    }

    /**
     * Checks, whether the document language title is printed before other titles.
     * OPUSVIER-1752
     * OPUSVIER-3316
     */
    public function testTitleSortOrderEnglishFirst()
    {
        $functions = ['addTitleMain', 'addTitleParent', 'addTitleSub', 'addTitleAdditional', 'addTitleAbstract'];
        foreach ($functions as $function) {
            $doc   = $this->createTestDocument();
            $title = Title::new();
            $title->setLanguage('deu');
            $title->setValue('deutscher Titel');
            $doc->$function($title);

            $title = Title::new();
            $title->setLanguage('eng');
            $title->setValue('englischer Titel');
            $doc->$function($title);

            $doc->setLanguage('eng');
            $doc->setServerState('published');
            $docId = $doc->store();

            $this->dispatch('/frontdoor/index/index/docId/' . $docId);
            // Absicherung gegen HTML Aenderungen;  in Meta-Tags steht Text in Attribut
            $this->assertEquals(
                1,
                substr_count($this->getResponse()->getBody(), '>deutscher Titel<'),
                'Teststring is found more than once; test is not reliable anymore'
            );
            $this->assertEquals(
                1,
                substr_count($this->getResponse()->getBody(), '>englischer Titel<'),
                'Teststring is found more than once; test is not reliable anymore'
            );

            $title1 = strpos($this->getResponse()->getBody(), '>englischer Titel<');
            $title2 = strpos($this->getResponse()->getBody(), '>deutscher Titel<');
            $this->assertTrue($title1 < $title2);
            $this->getResponse()->clearBody();
        }
    }

    public function testSortingOfFiles()
    {
        $doc = $this->createTestDocument();
        $doc->setServerState('published');

        $file = $this->createOpusTestFile('file1.txt');
        $file->setSortOrder(1);
        $doc->addFile($file);

        $file = $this->createOpusTestFile('file2.txt');
        $file->setSortOrder(2);
        $doc->addFile($file);

        $file = $this->createOpusTestFile('file10.txt');
        $file->setSortOrder(10);
        $doc->addFile($file);

        $docId = $doc->store();

        $this->dispatch("/frontdoor/index/index/docId/$docId");

        $body = $this->getResponse()->getBody();

        $this->assertTrue(
            strpos($body, '>file1.txt') < strpos($body, '>file2.txt'),
            'Order of files is wrong.'
        );
        $this->assertTrue(
            strpos($body, '>file2.txt') < strpos($body, '>file10.txt'),
            'Order of files is wrong.'
        );
    }

    /**
     * Tests, whether the current language of a document's file is shown behind the link as flag.
     */
    public function testFileFlagOfDocument()
    {
        $doc = $this->createTestDocument();
        $doc->setServerState('published');
        $file = $this->createOpusTestFile('eng');
        $file->setLanguage('eng');
        $doc->addFile($file);
        $file = $this->createOpusTestFile('deu');
        $file->setLanguage('deu');
        $doc->addFile($file);
        $file = $this->createOpusTestFile('spa');
        $file->setLanguage('spa');
        $doc->addFile($file);
        $file = $this->createOpusTestFile('fra');
        $file->setLanguage('fra');
        $doc->addFile($file);
        $file = $this->createOpusTestFile('rus');
        $file->setLanguage('rus');
        $doc->addFile($file);
        $docId = $doc->store();

        $this->dispatch('/frontdoor/index/index/docId/' . $docId);
        $body = $this->getResponse()->getBody();
        $this->assertContains(
            '<img width="16" height="11" src="/img/lang/eng.png" class="file-language eng" alt="eng"/>',
            $body
        );
        $this->assertContains(
            '<img width="16" height="11" src="/img/lang/deu.png" class="file-language deu" alt="deu"/>',
            $body
        );
        $this->assertContains(
            '<img width="16" height="11" src="/img/lang/spa.png" class="file-language spa" alt="spa"/>',
            $body
        );
        $this->assertContains(
            '<img width="16" height="11" src="/img/lang/fra.png" class="file-language fra" alt="fra"/>',
            $body
        );
        $this->assertContains(
            '<img width="16" height="11" src="/img/lang/rus.png" class="file-language rus" alt="rus"/>',
            $body
        );
    }

    /**
     * Asserts, that there are no language-flags, if the corresponding flag file is not present.
     *
     * TODO find better solution than renaming the folder (during development confusing for git)
     */
    public function testFlagsWithoutFile()
    {
        $this->markTestSkipped('Refactor test for more direct testing.');
        $doc = $this->createTestDocument();
        $doc->setServerState('published');
        $file = $this->createOpusTestFile('eng');
        $file->setLanguage('eng');
        $doc->addFile($file);
        $file = $this->createOpusTestFile('deu');
        $file->setLanguage('deu');
        $doc->addFile($file);
        $file = $this->createOpusTestFile('spa');
        $file->setLanguage('spa');
        $doc->addFile($file);
        $file = $this->createOpusTestFile('fra');
        $file->setLanguage('fra');
        $doc->addFile($file);
        $file = $this->createOpusTestFile('rus');
        $file->setLanguage('rus');
        $doc->addFile($file);
        $docId = $doc->store();

        $oldPath = APPLICATION_PATH . '/public/img/lang/';
        $bupPath = APPLICATION_PATH . '/public/img/lang_bup/';
        rename($oldPath, $bupPath);
        $this->dispatch('/frontdoor/index/index/docId/' . $docId);
        rename($bupPath, $oldPath);
        $body = $this->getResponse()->getBody();

        $this->assertNotContains('<img width="16" height="11" src="/img/lang/eng.png" alt="eng"/>', $body);
        $this->assertNotContains('<img width="16" height="11" src="/img/lang/deu.png" alt="deu"/>', $body);
        $this->assertNotContains('<img width="16" height="11" src="/img/lang/spa.png" alt="spa"/>', $body);
        $this->assertNotContains('<img width="16" height="11" src="/img/lang/fra.png" alt="fra"/>', $body);
        $this->assertNotContains('<img width="16" height="11" src="/img/lang/rus.png" alt="rus"/>', $body);

        $this->assertQueryContentContains('//span.file-language', '(spa)');
        $this->assertQueryContentContains('//span.file-language', '(eng)');
        $this->assertQueryContentContains('//span.file-language', '(deu)');
        $this->assertQueryContentContains('//span.file-language', '(rus)');
        $this->assertQueryContentContains('//span.file-language', '(fra)');
    }

    /**
     * Test für OPUSVIER-3275.
     */
    public function testEmbargoDatePassed()
    {
        $this->useEnglish();
        $file = $this->createOpusTestFile('foo.pdf');

        $doc = $this->createTestDocument();
        $doc->setServerState('published');
        $doc->addFile($file);

        $date = new Date();
        $date->setYear('2000')->setMonth('00')->setDay('01');
        $doc->setEmbargoDate($date);

        $docId = $doc->store();

        $this->dispatch('frontdoor/index/index/docId/' . $docId);
        $this->assertQueryContentContains('//*', '/files/' . $docId . '/foo.pdf');
        $this->assertNotQueryContentContains('//*', 'This document is embargoed until:');
    }

    /**
     * Test für OPUSVIER-3275.
     */
    public function testEmbargoDateHasNotPassed()
    {
        $this->useEnglish();
        $file = $this->createOpusTestFile('foo.pdf');

        $doc = $this->createTestDocument();
        $doc->setServerState('published');
        $doc->addFile($file);

        $date = new Date();
        $date->setYear('2100')->setMonth('00')->setDay('01');
        $doc->setEmbargoDate($date);

        $docId = $doc->store();

        $this->dispatch('frontdoor/index/index/docId/' . $docId);
        $this->assertNotQueryContentContains('//*', '/files/' . $docId . '/foo.pdf');
        $this->assertQueryContentContains('//*', 'This document is embargoed until:');
    }

    /**
     * EmbargoDate should be shown in metadata table, no matter if it has passed or not.
     * OPUSVIER-3270.
     */
    public function testEmbargoDateLabelWithEmbargoDatePassed()
    {
        $this->useEnglish();
        $doc = $this->createTestDocument();
        $doc->setEmbargoDate('2012-02-01');
        $doc->setServerState('published');
        $docId = $doc->store();

        $this->dispatch('frontdoor/index/index/docId/' . $docId);
        $this->assertQueryContentContains('//th', 'Embargo Date');
        $this->assertQueryContentContains('//td', '2012/02/01');
    }

    public function testMetaTagsForFileAccess()
    {
        $this->markTestIncomplete('test not implemented');
    }

    public function testMetaTagsForFiles()
    {
        $file = $this->createOpusTestFile('foo.pdf');

        $doc = $this->createTestDocument();
        $doc->setServerState('published');
        $doc->addFile($file);
        $docId = $doc->store();

        $this->dispatch('frontdoor/index/index/docId/' . $docId);

        $this->assertQueryContentContains('//meta/@content', "/files/$docId/foo.pdf");
    }

    public function testMetaTagsforEmbargoedDocument()
    {
        $file = $this->createOpusTestFile('foo.pdf');

        $doc = $this->createTestDocument();
        $doc->setEmbargoDate('2112-02-01');
        $doc->setServerState('published');
        $doc->addFile($file);
        $docId = $doc->store();

        $this->dispatch('frontdoor/index/index/docId/' . $docId);

        $this->assertNotQueryContentContains('//meta/@content', "/files/$docId/foo.pdf");
    }

    /**
     * EmbargoDate should be shown in metadata table, no matter if it has passed or not.
     * OPUSVIER-3270.
     */
    public function testEmbargoDateLabelWithEmbargoDateNotPassed()
    {
        $this->useEnglish();
        $doc = $this->createTestDocument();
        $doc->setEmbargoDate('2112-02-01');
        $doc->setServerState('published');
        $docId = $doc->store();

        $this->dispatch('frontdoor/index/index/docId/' . $docId);
        $this->assertQueryContentContains('//th', 'Embargo Date');
        $this->assertQueryContentContains('//td', '2112/02/01');
    }

    /**
     * If not specified in config, there should be no link to export a document to xml.
     */
    public function testXmlExportButtonNotPresent()
    {
        $this->enableSecurity();
        $this->loginUser('admin', 'adminadmin');
        $this->dispatch('/frontdoor/index/index/docId/305');
        $this->assertNotQuery('//a[@href="/frontdoor/index/index/docId/305/export/xml/stylesheet/example"]');
    }

    /**
     * The export functionality should be available for admins.
     */
    public function testXmlExportButtonPresentForAdmin()
    {
        $this->enableSecurity();
        $this->loginUser('admin', 'adminadmin');
        $this->adjustConfiguration(['export' => ['stylesheet' => ['frontdoor' => 'example']]]);
        $this->dispatch('/frontdoor/index/index/docId/305');
        $this->assertQuery(
            '//a[@href="/export/index/index/docId/305/export/xml/searchtype/id/stylesheet/example"]'
        );
    }

    /**
     * The export functionality should not be present for guests.
     */
    public function testXmlExportNotButtonPresentForGuest()
    {
        $this->enableSecurity();
        $this->adjustConfiguration(['export' => ['stylesheet' => ['frontdoor' => 'example']]]);
        $this->dispatch('/frontdoor/index/index/docId/305');
        $this->assertNotQuery('//a[@href="/frontdoor/index/index/docId/305/export/xml/stylesheet/example"]');
    }

    public function testGoogleScholarLink()
    {
        $this->useGerman();
        $this->dispatch('/frontdoor/index/index/docId/146');
        $this->assertResponseCode(200);
        $body = $this->getResponse()->getBody();
        $this->assertContains(
            'http://scholar.google.de/scholar?hl=de&amp;q=&quot;KOBV&quot;&amp;as_sauthors=John+Doe'
            . '&amp;as_ylo=2007&amp;as_yhi=2007',
            $body
        );
    }

    public function testGoogleScholarLinkEnglish()
    {
        $this->useEnglish();
        $this->dispatch('/frontdoor/index/index/docId/146');
        $this->assertResponseCode(200);
        $body = $this->getResponse()->getBody();
        $this->assertContains(
            'http://scholar.google.de/scholar?hl=en&amp;q=&quot;KOBV&quot;&amp;as_sauthors=John+Doe'
            . '&amp;as_ylo=2007&amp;as_yhi=2007',
            $body
        );
    }

    public function testGoogleScholarOpenInNewWindowEnabled()
    {
        $this->adjustConfiguration([
            'googleScholar' => ['openInNewWindow' => self::CONFIG_VALUE_TRUE],
        ]);
        $this->dispatch('/frontdoor/index/index/docId/146');
        $this->assertResponseCode(200);
        $this->assertXpathCount('//a[contains(@href, "scholar.google.de") and @target = "_blank"]', 1);
        $this->assertXpathCount('//a[contains(@href, "scholar.google.de") and not(@target)]', 0);
    }

    public function testGoogleScholarOpenInNewWindowDisabled()
    {
        $this->adjustConfiguration([
            'googleScholar' => ['openInNewWindow' => self::CONFIG_VALUE_FALSE],
        ]);
        $this->dispatch('/frontdoor/index/index/docId/146');
        $this->assertResponseCode(200);
        $this->assertXpathCount('//a[contains(@href, "scholar.google.de") and @target = "_blank"]', 0);
        $this->assertXpathCount('//a[contains(@href, "scholar.google.de") and not(@target)]', 1);
    }

    public function testShowDocumentWithFileWithoutLanguage()
    {
        $this->markTestIncomplete('OPUSVIER-3401');
        $doc  = $this->createTestDocument();
        $file = $this->createOpusTestFile('nolang.pdf');
        $doc->addFile($file);
        $docId = $doc->store();

        $this->dispatch("/frontdoor/index/index/docId/$docId");
    }

    public function testTwitterOpenInNewWindowEnabled()
    {
        $this->adjustConfiguration([
            'twitter' => ['openInNewWindow' => self::CONFIG_VALUE_TRUE],
        ]);
        $this->dispatch('/frontdoor/index/index/docId/146');
        $this->assertResponseCode(200);
        $this->assertXpathCount('//a[contains(@href, "twitter.com") and @target = "_blank"]', 1);
        $this->assertXpathCount('//a[contains(@href, "twitter.com") and not(@target)]', 0);
    }

    public function testTwitterOpenInNewWindowDisabled()
    {
        $this->adjustConfiguration([
            'twitter' => ['openInNewWindow' => self::CONFIG_VALUE_FALSE],
        ]);
        $this->dispatch('/frontdoor/index/index/docId/146');
        $this->assertResponseCode(200);
        $this->assertXpathCount('//a[contains(@href, "twitter.com") and @target = "_blank"]', 0);
        $this->assertXpathCount('//a[contains(@href, "twitter.com") and not(@target)]', 1);
    }

    public function testUnableToTranslate()
    {
        $filter = new LogFilter();

        $logger = $this->getLogger();
        $logger->addFilter($filter);

        // TODO LAMINAS assert fails on some systems (order of tests?) - make more robust
        $this->assertEquals(7, $logger->getLevel(), 'Log level should be 7 for test.');

        $this->dispatch('/frontdoor/index/index/docId/146');

        $failedTranslations = [];

        foreach ($filter->getMessages() as $line) {
            if (strpos($line, 'Unable to translate') !== false) {
                $failedTranslations[] = $line;
            }
        }

        $output = Zend_Debug::dump($failedTranslations, null, false);

        // until all messages can be prevented less than 20 is good enough
        $this->assertLessThanOrEqual(1, count($failedTranslations), $output);
    }

    public function testMetaTagsForUrns()
    {
        $this->dispatch('/frontdoor/index/index/docId/146');

        $this->assertResponseCode(200);

        $urnResolverUrl = $this->getConfig()->urn->resolverUrl;

        $this->assertXpath('//meta[@name="DC.identifier" and @content="urn:nbn:op:123"]');
        $this->assertXpath('//meta[@name="DC.identifier" and @content="' . $urnResolverUrl . 'urn:nbn:op:123"]');
    }

    public function testBelongsToBibliographyTurnedOn()
    {
        $this->useEnglish();
        $this->adjustConfiguration([
            'frontdoor' => ['metadata' => ['BelongsToBibliography' => self::CONFIG_VALUE_TRUE]],
        ]);

        $this->dispatch('/frontdoor/index/index/docId/146');

        $this->assertXpath('//td[contains(@class, "BelongsToBibliography")]');
        $this->assertXpathContentContains('//td[contains(@class, "BelongsToBibliography")]', 'Yes');
    }

    public function testBelongsToBibliographyTurnedOff()
    {
        $this->adjustConfiguration([
            'frontdoor' => ['metadata' => ['BelongsToBibliography' => self::CONFIG_VALUE_FALSE]],
        ]);

        $this->dispatch('/frontdoor/index/index/docId/146');

        $this->assertNotXpath('//td[contains(@class, "BelongsToBibliography")]');
    }

    /**
     * Tests, if the XSLT has the correct language-attribute for main-title and abstract for the browser
     */
    public function testExistsCorrectLangAttribute()
    {
        $this->dispatch('/frontdoor/index/index/docId/146');
        $this->assertXpath('//li[contains(@class = "abstract preserve-spaces", @lang = "de")]');
        $this->assertXpath('//li[contains(@class = "abstract preserve-spaces", @lang = "en")]');
        $this->assertXpath('//h2[contains(@class = "titlemain", @lang = "de")]');
        $this->assertXpath('//h3[contains(@class = "titlemain", @lang = "en")]');
    }

    /**
     * Tests, if the sbstract and main-title with marked language has the correct content-language
     */
    public function testCorrectContentLanguage()
    {
        $this->dispatch('/frontdoor/index/index/docId/146');
        $this->assertXpathContentContains(
            '//li[contains(@class = "abstract preserve-spaces", @lang = "en")]',
            'Lorem'
        );
        $this->assertXpathContentContains(
            '//li[contains(@class = "abstract preserve-spaces", @lang = "de")]',
            'Berlin-Dahlem'
        );
        $this->assertXpathContentContains(
            '//h3[contains(@class = "titlemain", @lang = "en")]',
            'COLN'
        );
        $this->assertXpathContentContains(
            '//h2[contains(@class = "titlemain", @lang = "de")]',
            'KOBV'
        );
    }

    /**
     * Tests, if the XSLT has the correct language-attribute for title in the metadata-table for the browser
     */
    public function testMetaCorrectTitleLangAttribute()
    {
        $this->dispatch('/frontdoor/index/index/docId/146');
        $this->assertXpath('//td[contains(@class = "titleparent", @lang = "de")]');
        $this->assertXpath('//td[contains(@class = "titlesub", @lang = "en")]');
    }

    /**
     * Tests, if the several titles in the metadata-table with marked language has the correct content-language
     */
    public function testMetaCorrectTitleContentLang()
    {
        $this->dispatch('/frontdoor/index/index/docId/146');
        $this->assertXpathContentContains(
            '//td[contains(@class = "titlesub", @lang = "en")]',
            "Service Center"
        );
        $this->assertXpathContentContains(
            '//td[contains(@class = "titlesub", @lang = "de")]',
            "Service-Zentrale"
        );
    }

    public function testDuplicateDocIdParameter()
    {
        $this->dispatch('/frontdoor/index/index/docId/147/docId/146');

        $this->assertXpathContentContains('//h2[@class="titlemain"]', 'KOBV');
        $this->assertNotXpathContentContains('//h2[@class="titlemain"]', 'Sonderzeichen');
    }

    public function testPubmedRendering()
    {
        $this->dispatch('/frontdoor/index/index/docId/146');

        $path = 'table.result-data.frontdoordata th.name';

        $config = $this->getConfig();

        $pubmedUrl = $config->pubmed->baseUrl . '123';

        $this->assertQueryContentContains('table.result-data.frontdoordata td', $pubmedUrl);
        $this->assertXpath("//table/tr/td/a[@href=\"$pubmedUrl\"]");
    }

    public function testRenderingGndSubjectLink()
    {
        $doc = $this->createTestDocument();

        $subject = Subject::new();
        $subject->setType('swd');
        $subject->setValue('Bauhaus');
        $subject->setExternalKey('4130303-9');
        $doc->addSubject($subject);

        $subject = Subject::new();
        $subject->setLanguage('deu');
        $subject->setType('uncontrolled');
        $subject->setValue('mytag');
        $subject->setExternalKey('mykey');
        $doc->addSubject($subject);

        $doc->setServerState(Document::STATE_PUBLISHED);
        $docId = $doc->store();

        $config     = $this->getConfig();
        $gndLinkUrl = $config->gnd->baseUrl . '4130303-9';

        $this->dispatch("/frontdoor/index/index/docId/${docId}");

        $this->assertXpath("//a[@href=\"${gndLinkUrl}\"]");
        $this->assertXpathContentContains('//em[contains(@class,"subject")]', 'Bauhaus');
        $this->assertXpathContentContains("//a[@href=\"${gndLinkUrl}\"]", 'GND');

        $this->assertXpathContentContains('//em[contains(@class, "subject")]', 'mytag');
        $this->assertNotXpathContentContains('//a', 'mytag'); // no link for 'mytag'
    }
}
