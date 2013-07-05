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
 * @category    Application
 * @package     Tests
 * @author      Jens Schwidder <schwidder@zib.de>
 * @author      Sascha Szott <szott@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Basic unit test for inded controller of home module.
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
    }

    /**
     * Tests setting language for session.
     */
    public function testLanguageAction() {
        $this->markTestIncomplete('How can this be tested?');
        $this->dispatch('/home/index/language/language/de');
        $this->assertRedirect();
    }

    /**
     * Test help action.
     */
    public function testHelpAction() {
        $this->dispatch('/home/index/help');
        $this->assertResponseCode(200);
        $this->assertModule('home');
        $this->assertController('index');
        $this->assertAction('help');
    }

    public function testHelpActionSeparate() {
        $this->markTestSkipped('Is *help.separate* parameter still supported?');
        $config = Zend_Registry::get('Zend_Config');
        $config->help->separate = true;
        $this->dispatch('/home/index/help');
        $this->assertResponseCode(200);
        $this->assertModule('home');
        $this->assertController('index');
        $this->assertAction('help');
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
    }

    public function testFailureAction() {
        $this->dispatch('/home/index/failure');
        $this->assertRedirect('/home/index/index');
        $this->assertModule('home');
        $this->assertController('index');
        $this->assertAction('failure');
    }

    public function testNoticeAction() {
        $this->dispatch('/home/index/notice');
        $this->assertRedirect('/home/index/index');
        $this->assertModule('home');
        $this->assertController('index');
        $this->assertAction('notice');
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

        $this->getResponse()->clearBody();

        $this->dispatch('/home');
        
        $document = new DOMDocument();
        $document->loadHTML($this->getResponse()->getBody());
        $element = $document->getElementById('solrsearch-totalnumofdocs');
        $numOfDocs = $element->firstChild->textContent;

        // Sollte nicht passieren, aber wenn doch zeige die Dokument-IDs an die nicht in Index und Datenbank sind
        if ($numOfDocs !== $numOfHits) {
            // get IDs from Index
            $searcher = new Opus_SolrSearch_Searcher();
            $query = new Opus_SolrSearch_Query();
            $query->setCatchAll("*:*");
            $resultList = $searcher->search($query);
            $this->assertEquals($numOfHits, $resultList->getNumberOfHits());
            $results = $resultList->getResults();
            
            $indexIds = array();

            foreach ($results as $result) {
                $indexIds[] = $result->getId();             
            }
            
            // get IDs from Database
            $documentFinder = new Opus_DocumentFinder();
            $documentFinder->setServerState('published');
            
            $dbIds = $documentFinder->ids();
            $this->assertEquals($numOfDocs, count($dbIds));
            
            $diffIds = array_diff($indexIds, $dbIds);
            
            $output = Zend_Debug::dump($diffIds, "Doc-Ids not in Index and Database", false);
            
            $this->assertEquals(0, count($diffIds), $output);
        }
        
        // Prüfen, das Link existiert
        $this->assertQueryContentContains('a#link-solrsearch-all-documents', "$numOfHits", $numOfHits);        
    }
}

