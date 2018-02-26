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
 * @package     Home
 * @author      Jens Schwidder <schwidder@zib.de>
 * @author      Sascha Szott <szott@zib.de>
 * @copyright   Copyright (c) 2008-2018, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Basic unit test for inded controller of home module.
 *
 * @covers Home_IndexController
 */
class Home_IndexControllerTest extends ControllerTestCase {

    /**
     * Tests routing to and successfull execution of 'index' action.
     */
    public function testIndexAction() {
        $this->dispatch('/home');
        $this->assertResponseCode(200);
        $this->assertModule('home');
        $this->assertController('index');
        $this->assertAction('index');
        $this->validateXHTML();
    }

    /**
     * Test help action.
     */
    public function testHelpActionEnglish() {
        $this->useEnglish();
        $this->dispatch('/home/index/help');
        $this->assertResponseCode(200);
        $this->assertModule('home');
        $this->assertController('index');
        $this->assertAction('help');
        $this->validateXHTML();
    }

    public function testHelpActionGerman() {
        $this->useGerman();
        $this->dispatch('/home/index/help');
        $this->assertResponseCode(200);
        $this->assertModule('home');
        $this->assertController('index');
        $this->assertAction('help');
        $this->validateXHTML();
    }

    public function testHelpActionSeparate() {
        $config = Zend_Registry::get('Zend_Config');
        $config->help->separate = true;
        $this->dispatch('/home/index/help');
        $this->assertResponseCode(200);
        $this->assertModule('home');
        $this->assertController('index');
        $this->assertAction('help');
        $this->validateXHTML();
    }

    /**
     * Test help action.
     */
    public function testContactAction() {
        $this->dispatch('/home/index/contact');
        $this->assertResponseCode(200);
        $this->assertModule('home');
        $this->assertController('index');
        $this->assertAction('contact');
        $this->validateXHTML();
    }

    /**
     * Test help action.
     */
    public function testImprintAction() {
        $this->dispatch('/home/index/imprint');
        $this->assertResponseCode(200);
        $this->assertModule('home');
        $this->assertController('index');
        $this->assertAction('imprint');
        $this->validateXHTML();
    }

    public function testFailureAction() {
        $this->dispatch('/home/index/failure');
        $this->assertRedirectTo('/home');
        $this->assertModule('home');
        $this->assertController('index');
        $this->assertAction('failure');
    }

    public function testNoticeAction() {
        $this->dispatch('/home/index/notice');
        $this->assertRedirectTo('/home');
        $this->assertModule('home');
        $this->assertController('index');
        $this->assertAction('notice');
    }

    private function getDocsInSearchIndex($checkConsistency = true) {
        $searcher = new Opus_SolrSearch_Searcher();
        $query = new Opus_SolrSearch_Query();
        $query->setCatchAll("*:*");
        $query->setRows(Opus_SolrSearch_Query::MAX_ROWS);
        $resultList = $searcher->search($query, $checkConsistency);
        return $resultList;
    }

    /**
     * Regression test for OPUSVIER-849
     */
    public function testStartPageContainsTotalNumOfDocs() {
        // get total number of documents from all doc search
        $this->dispatch('/solrsearch/index/search/searchtype/all');

        $document = new DOMDocument();
        $document->loadHTML($this->getResponse()->getBody());
        $element = $document->getElementById('search-result-numofhits');
        $numOfHits = $element->firstChild->textContent;

        $docsInIndex = $this->getDocsInSearchIndex();
        $numOfIndexDocs = $docsInIndex->getNumberOfHits();
        $this->assertEquals($numOfIndexDocs, $numOfHits);

        $this->getResponse()->clearBody();

        $this->dispatch('/home');

        $document = new DOMDocument();
        $document->loadHTML($this->getResponse()->getBody());
        $element = $document->getElementById('solrsearch-totalnumofdocs');
        $numOfDocs = $element->firstChild->textContent;

        $docFinder = new Opus_DocumentFinder();
        $docFinder->setServerState('published');

        $numOfDbDocs = $docFinder->count();
        $this->assertEquals($numOfDbDocs, $numOfDocs);

        // kurze Erklärung des Vorhabens: die Dokumentanzahl bei der Catch-All-Suche
        // wird auf Basis einer Indexsuche ermittelt; die Anzahl der Dokument, die
        // auf der Startseite erscheint, wird dagegen über den DocumentFinder
        // ermittelt: im Idealfall sollten diese beiden Zahlen nicht voneinander
        // abweichen
        // wenn sie abweichen, dann aufgrund einer Inkonsistenz zwischen Datenbank
        // und Suchindex (das sollte im Rahmen der Tests eigentlich nicht auftreten)

        if ($numOfDbDocs != $numOfIndexDocs) {

            // ermittle die Doc-IDs, die im Index, aber nicht in der DB existieren
            // bzw. die in der DB, aber nicht im Index existieren
            $idsIndex = array();
            $results = $docsInIndex->getResults();
            foreach ($results as $result) {
                array_push($idsIndex, $result->getId());
            }

            $idsDb = $docFinder->ids();

            $idsIndexOnly = array_diff($idsIndex, $idsDb);
            $this->assertEquals(0, count($idsIndexOnly), 'Document IDs in search index, but not in database: '
                . var_export($idsIndexOnly, true));

            $idsDbOnly = array_diff($idsDb, $idsIndex);
            $this->assertEquals(0, count($idsDbOnly), 'Document IDs in database, but not in search index: '
                . var_export($idsDbOnly, true));

            $this->assertEquals($numOfDbDocs, $numOfIndexDocs,
                "number of docs in database ($numOfDbDocs) and search index ($numOfIndexDocs) differ from each other");
        }

        $this->assertEquals($numOfDocs, $numOfHits);
    }

    public function testFlashMessengerDivNotDisplayedWithoutMessages() {
        $this->dispatch('/home');
        $this->assertResponseCode(200);
        $this->assertNotQuery("div#content/div.messages");
    }

    /**
     * Prüft, ob nur die erlaubten Einträge im Admin Menu angezeigt werden.
     */
    public function testShowAccountLinkForUsersWithModuleAccess() {
        $this->useEnglish();
        $this->enableSecurity();
        $this->loginUser("security7", "security7pwd");
        $this->dispatch('/home');
        $this->assertQueryContentContains("//div[@id='login-bar']", 'Account');
    }

    public function testHideAccountLinkForUsersWithoutModuleAccess() {
        $this->useEnglish();
        $this->enableSecurity();
        $this->loginUser("security1", "security1pwd");
        $this->dispatch('/home');
        $this->assertNotQueryContentContains("//div[@id='login-bar']", 'Account');
    }

    public function testShowLanguageSelector() {
        $this->dispatch("/home");
        $this->assertQuery('//ul#lang-switch');
    }

    public function testHideLanguageSelector() {
        Zend_Registry::get('Zend_Config')->supportedLanguages = 'de';
        $this->dispatch("/home");
        $this->assertNotQuery('//ul#lang-switch');
    }

    public function testPageLanguageAttributeEnglish() {
        $this->useEnglish();

        $this->dispatch('/home');

        $this->assertQuery('//html[@lang="en"]');
        // TODO $this->assertXPath('//html[@xml:lang="en"]');

        $this->assertXPath('//meta[@http-equiv="Content-Language" and @content="en"]');
    }

    public function testPageLanguageAttributeGerman() {
        $this->useGerman();

        $this->dispatch('/home');

        $this->assertQuery('//html[@lang="de"]');
        // TODO $this->assertQuery('//html[@xml:lang="de"]');

        $this->assertXPath('//meta[@http-equiv="Content-Language" and @content="de"]');
    }

    /**
     * Assumes that test are not run in 'production' environment.
     */
    public function testSignalNonProductionEnvironment() {
        $this->useEnglish();

        $this->dispatch('/home');

        $this->assertQueryContentContains('//div#top-header', 'NON PRODUCTION ENVIRONMENT');
    }

    /**
     * No way to change APPLICATION_ENV once it is set. It makes testing 'production' impossible,
     * but that is a good thing because environment cannot be hidden by some other code.
     */
    public function testNoSignalingOfEnvironment() {
        $this->useEnglish();

        $this->dispatch('/home');

        if (APPLICATION_ENV !== 'production') {
            $this->assertQueryContentContains(
                '//div#top-header', 'NON PRODUCTION ENVIRONMENT (' . APPLICATION_ENV . ')'
            );
        }
        else {
            $this->assertNotQueryContentContains('//div#top-header', 'NON PRODUCTION ENVIRONMENT');
        }
    }

}
