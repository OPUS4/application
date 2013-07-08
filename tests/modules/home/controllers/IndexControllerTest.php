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
            echo "\n";
            echo "num of index documents: $numOfIndexDocs\n";
            echo "num of database documents: $numOfDbDocs\n";
            
            // ermittle die Doc-IDs, die im Index, aber nicht in der DB existieren 
            // bzw. die in der DB, aber nicht im Index existieren
            $idsIndex = array();
            $results = $docsInIndex->getResults();
            foreach ($results as $result) {
                array_push($idsIndex, $result->getId());
            }
            
            $idsDb = $docFinder->ids();
            
            $idsIndexOnly = array_diff($idsIndex, $idsDb);
            $this->assertEquals(0, count($idsIndexOnly), 'Document IDs in search index, but not in database: ' . var_dump($idsIndexOnly));
            
            $idsDbOnly = array_diff($idsDb, $idsIndex);
            $this->assertEquals(0, count($idsDbOnly), 'Document IDs in database, but not in search index: ' . var_dump($idsDbOnly));
        }
        
        $this->assertEquals($numOfDbDocs, $numOfIndexDocs);        
        $this->assertEquals($numOfDocs, $numOfHits);
        
    }
    
}
