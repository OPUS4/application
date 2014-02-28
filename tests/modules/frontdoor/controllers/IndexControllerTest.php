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
 * @author      Julian Heise <heise@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
class Frontdoor_IndexControllerTest extends ControllerTestCase {

   /**
    * Document to count on :)
    *
    * @var Opus_Document
    */
   protected $_document = null;
   protected $_document_col = null;
   protected $_security_backup = null;

   /**
    * Provide clean documents and statistics table and remove temporary files.
    * Create document for counting.
    *
    * @return void
    */
   public function setUp() {
      parent::setUpWithEnv('production');
      $this->assertEquals(1, Zend_Registry::get('Zend_Config')->security);
      $this->assertTrue(Zend_Registry::isRegistered('Opus_Acl'), 'Expected registry key Opus_Acl to be set');
      $acl = Zend_Registry::get('Opus_Acl');
      $this->assertTrue($acl instanceof Zend_Acl, 'Expected instance of Zend_Acl');

      $path = Zend_Registry::get('temp_dir') . '~localstat.xml';
      @unlink($path);

      $this->_document = new Opus_Document();
      $this->_document->setType("doctoral_thesis");
      $this->_document->store();

      //setting server globals
      $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
      $_SERVER['HTTP_USER_AGENT'] = 'bla';
      $_SERVER['REDIRECT_STATUS'] = 200;

      // enable security
      $config = Zend_Registry::get('Zend_Config');
      $this->_security_backup = $config->security;
      $config->security = '1';

      // create collection test document
      $this->_document_col = new Opus_Document();
      $this->_document_col->addCollection(new Opus_Collection(40)); // invisible collection
      $this->_document_col->addCollection(new Opus_Collection(16214)); // visible collection with invisible collection role
      $this->_document_col->addCollection(new Opus_Collection(1031)); // visible collection with visible collection role

      // collection role ID = 10 (sichbar)
      $this->_document_col->addCollection(new Opus_Collection(16136)); // versteckte Collection (Role = 10)
      $this->_document_col->addCollection(new Opus_Collection(15991)); // sichbare Collection (Role = 10);
      $this->_document_col->setServerState('published');
      $this->_document_col->store();
   }

   protected function tearDown() {
      // restore old security config
      $config = Zend_Registry::get('Zend_Config');
      $config->security = $this->_security_backup;
      Zend_Registry::set('Zend_Config', $config);

      $this->removeDocument($this->_document);
      $this->removeDocument($this->_document_col);
      parent::tearDown();
   }

   public function testIndexActionOnPublished() {
      $this->_document->setServerState('published')->store();
      $doc_id = $this->_document->getId();
      $this->dispatch('/frontdoor/index/index/docId/' . $doc_id);

      $this->assertResponseCode(200);
      $this->assertController('index');
      $this->assertAction('index');

      $response = $this->getResponse();
      $this->checkForBadStringsInHtml($response->getBody());
      $this->assertContains('<div class="frontdoor">', $response->getBody());
   }

   public function testIndexActionOnDeleted() {
      $this->_document->setServerState('deleted')->store();
      $doc_id = $this->_document->getId();
      $this->dispatch('/frontdoor/index/index/docId/' . $doc_id);

      $this->assertResponseCode(410);
      $this->assertController('index');
      $this->assertAction('index');

      $response = $this->getResponse();
      $this->assertContains('<div class="frontdoor-error">', $response->getBody());
   }

   public function testIndexActionOnUnpublished() {
      $this->_document->setServerState('unpublished')->store();
      $doc_id = $this->_document->getId();
      $this->dispatch('/frontdoor/index/index/docId/' . $doc_id);

      $this->assertResponseCode(403);
      $this->assertController('index');
      $this->assertAction('index');

      $response = $this->getResponse();
      $this->assertContains('<div class="frontdoor-error">', $response->getBody());
   }

   public function testIndexActionOnTemporary() {
      $this->_document->setServerState('temporary')->store();
      $doc_id = $this->_document->getId();
      $this->dispatch('/frontdoor/index/index/docId/' . $doc_id);

      $this->assertResponseCode(403);
      $this->assertController('index');
      $this->assertAction('index');

      $response = $this->getResponse();
      $this->assertContains('<div class="frontdoor-error">', $response->getBody());
   }

   public function testIndexActionOnNonExistent() {
      $doc_id = $this->_document->getId();
      $this->dispatch('/frontdoor/index/index/docId/' . $doc_id . $doc_id . '100');

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
   public function testMapopus3Action() {
      $opus3_id = 'foobar-' . rand();
      $this->_document->addIdentifierOpus3()->setValue($opus3_id);
      $doc_id = $this->_document->store();

      $this->dispatch('/frontdoor/index/mapopus3/oldId/' . $opus3_id);

      $this->assertResponseCode(302);
      $this->assertModule('frontdoor');
      $this->assertController('index');
      $this->assertAction('mapopus3');

      $response = $this->getResponse();
      $headers = $response->getHeaders();

      $this->assertEquals('Location', $headers[0]['name']);
      $this->assertStringEndsWith('/rewrite/index/id/type/opus3-id/value/' . $opus3_id, $headers[0]['value']);

      $this->checkForBadStringsInHtml($response->getBody());
   }

   /**
    * test to document bug OPUSVIER-1695
    */
   public function testUncontrolledKeywordHeaderIsNotDisplayedIfUncontrolledKeywordsDoNotExist() {
      $this->dispatch('/frontdoor/index/index/docId/92');
      $this->assertResponseCode(200);
      $this->assertModule('frontdoor');
      $this->assertController('index');
      $this->assertAction('index');
      $body = $this->getResponse()->getBody();
      $this->assertContains('<table class="result-data frontdoordata">', $body);
      $this->assertNotContains('<td><em class="data-marker"/></td>', $body);
   }

   /*
    * Regression test for OPUSVIER-2165
    */

   public function testFrontdoorTitleRespectsDocumentLanguageDeu() {
      $this->dispatch('/frontdoor/index/index/docId/146');
      $this->assertNotContains('<title>OPUS 4 | COLN</title>', $this->getResponse()->getBody());
      $this->assertContains('<title>OPUS 4 | KOBV</title>', $this->getResponse()->getBody());
   }

   /**
    * Regression test for OPUSVIER-2165
    */
   public function testFrontdoorTitleRespectsDocumentLanguageEng() {
      $d = new Opus_Document(146);
      $lang = $d->getLanguage();
      $d->setLanguage('eng');
      $d->store();

      $this->dispatch('/frontdoor/index/index/docId/146');

      // restore language
      $d = new Opus_Document(146);
      $d->setLanguage($lang);
      $d->store();
      
      $this->assertContains('<title>OPUS 4 | COLN</title>', $this->getResponse()->getBody());
      $this->assertNotContains('<title>OPUS 4 | KOBV</title>', $this->getResponse()->getBody());

   }

   /**
    * Regression test for OPUSVIER-2165
    *
    * if database does not contain a title in the document's language,
    * the first title is used as page title
    * 
    */
   public function testFrontdoorTitleRespectsDocumentLanguageWithoutCorrespondingTitle() {
      $d = new Opus_Document(146);
      $lang = $d->getLanguage();
      $d->setLanguage('fra');
      $d->store();

      $this->dispatch('/frontdoor/index/index/docId/146');

      // restore language
      $d = new Opus_Document(146);
      $d->setLanguage($lang);
      $d->store();
      
      $this->assertNotContains('<title>OPUS 4 | COLN</title>', $this->getResponse()->getBody());
      $this->assertContains('<title>OPUS 4 | KOBV</title>', $this->getResponse()->getBody());      
   }

   /**
    * Regression test for OPUSVIER-2165
    *
    * if database contains more than one title in the document's language,
    * the first title is used as page title
    */
   public function testFrontdoorTitleRespectsDocumentLanguageMultipleCandidates() {
      $d = new Opus_Document(146);
      $titles = $d->getTitleMain();
      $d->addTitleMain()->setValue('VBOK')->setLanguage('deu');
      $d->store();

      $this->dispatch('/frontdoor/index/index/docId/146');

      // restore titles
      $d = new Opus_Document(146);
      $d->setTitleMain($titles);
      $d->store();
      
      $this->assertNotContains('<title>OPUS 4 | COLN</title>', $this->getResponse()->getBody());
      $this->assertNotContains('<title>OPUS 4 | VBKO</title>', $this->getResponse()->getBody());
      $this->assertContains('<title>OPUS 4 | KOBV</title>', $this->getResponse()->getBody());      
   }

   /**
    * Regression test for OPUSVIER-1924
    */
   public function testIdentifierUrlIsHandledProperlyInFrontdoorForNonProtocolURL() {
      $d = new Opus_Document('91');
      $identifiers = $d->getIdentifierUrl();
      $identifier = $identifiers[0];
      $this->assertEquals('www.myexampledomain.de/myexamplepath', $identifier->getValue());
      $this->dispatch('/frontdoor/index/index/docId/91');
      $this->assertEquals(2, substr_count($this->getResponse()->getBody(), 'http://www.myexampledomain.de/myexamplepath'));
   }

   /**
    * Regression test for OPUSVIER-1924
    */
   public function testIdentifierUrlIsHandledProperlyInFrontdoorForProtocolURL() {
      $d = new Opus_Document('92');
      $identifiers = $d->getIdentifierUrl();
      $identifier = $identifiers[0];
      $this->assertEquals('http://www.myexampledomain.de/myexamplepath', $identifier->getValue());
      $this->dispatch('/frontdoor/index/index/docId/92');
      $this->assertEquals(2, substr_count($this->getResponse()->getBody(), 'http://www.myexampledomain.de/myexamplepath'));
   }

   /**
    * Regression test for OPUSVIER-1647
    */
   public function testUrlEscapedFileNameDoc1() {
      $d = new Opus_Document(1);
      $filePathnames = array();
      foreach ($d->getFile() AS $file) {
         $filePathnames[] = $file->getPathName();
      }

      $filenameNormal = 'asis-hap.pdf';
      $filenameWeird = 'asis-hap_\'.pdf';
      $this->assertContains($filenameNormal, $filePathnames, "testdata changed!");
      $this->assertContains($filenameWeird, $filePathnames, "testdata changed!");

      $this->dispatch('/frontdoor/index/index/docId/1');

      $responseBody = $this->getResponse()->getBody();
      $this->assertRegExp('/<a href="[^"]+\/1\/asis-hap.pdf"/', $responseBody);
      $this->assertRegExp('/<a href="[^"]+\/1\/asis-hap_%27.pdf"/', $responseBody);
      $this->assertNotRegExp('/<a href="[^"]+\/1\/asis-hap_\'.pdf"/', $responseBody);
   }

   /**
    * Regression test for OPUSVIER-1647
    */
   public function testUrlEscapedFileNameDoc147() {
      $d = new Opus_Document(147);
      $filePathnames = array();
      foreach ($d->getFile() AS $file) {
         $filePathnames[] = $file->getPathName();
      }

      $filenameNormal = 'special-chars-%-"-#-&.pdf';
      $filenameWeird = "'many'  -  spaces  and  quotes.pdf";
      $this->assertContains($filenameNormal, $filePathnames, "testdata changed!");
      $this->assertContains($filenameWeird, $filePathnames, "testdata changed!");

      $this->dispatch('/frontdoor/index/index/docId/147');

      $responseBody = $this->getResponse()->getBody();
      $this->assertRegExp('/<a href="[^"]+\/\d+\/special-chars-%25-%22-%23-%26.pdf">/', $responseBody);
      $this->assertRegExp('/<a href="[^"]+\/\d+\/%27many%27\+\+-\+\+spaces\+\+and\+\+quotes.pdf">/', $responseBody);
   }

   /**
    * Regression test for OPUSVIER-2129
    */
   public function testSeries146() {
      $this->dispatch('/frontdoor/index/index/docId/146');
      $this->assertContains('/solrsearch/index/search/searchtype/series/id/1" ', $this->getResponse()->getBody());
   }

   /**
    * Regression test for OPUSVIER-2232
    */
   public function testSeries149InVisible() {
      $d = new Opus_Document(149);
      $seriesIds = array();
      $seriesNumbers = array();
      foreach ($d->getSeries() AS $series) {
         $seriesIds[] = $series->getModel()->getId();
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
   public function testUrlEncodedAuthorNamesDoc150() {
      $d = new Opus_Document(150);
      $firstNames = array();
      $lastNames = array();

      foreach ($d->getPersonAuthor() AS $author) {
         $firstNames[] = $author->getFirstName();
         $lastNames[] = $author->getLastName();
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
   public function testShowLinkForPrintOnDemandIfLicenceAppropriate() {
      $podConfArray = array('printOnDemand' => array(
              'url' => 'http://localhost/',
              'button' => ''
              ));
      $podConfig = new Zend_Config($podConfArray);
      Zend_Registry::getInstance()->get('Zend_Config')->merge($podConfig);

      $this->dispatch('/frontdoor/index/index/docId/1');
      $this->assertQuery('div#print-on-demand');
   }

   /**
    * Regression test for OPUSHOSTING-52
    */
   public function testHideLinkForPrintOnDemandIfLicenceNotAppropriate() {
      $podConfArray = array('printOnDemand' => array(
              'url' => 'http://localhost/',
              'button' => ''
              ));
      $podConfig = new Zend_Config($podConfArray);
      Zend_Registry::getInstance()->get('Zend_Config')->merge($podConfig);

      $this->dispatch('/frontdoor/index/index/docId/91');
      $this->assertNotQuery('div#print-on-demand');
   }

   /**
    * Regression test for OPUSVIER-2492
    */
   public function testDisplayAllUserDefinedCollectionRoles() {
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
   public function testDisplayAllDocumentFields() {

       $this->markTestSkipped('Postponed due to encoding problem.');

      $this->dispatch('/frontdoor/index/index/docId/146');
      $translate = Zend_Registry::getInstance()->get('Zend_Translate');
      
      $this->assertQuery('h2.titlemain');
      $this->assertQueryContentContains('table.result-data.frontdoordata th.name', $translate->_('PersonAuthor'));
      $this->assertQuery('div#abstract');
      $this->assertQueryContentContains('table.result-data.frontdoordata th.name', $translate->_('IdentifierUrn'));
      $this->assertQueryContentContains('table.result-data.frontdoordata th.name', $translate->_('IdentifierUrl'));
      $this->assertQueryContentContains('table.result-data.frontdoordata th.name', $translate->_('IdentifierHandle'));
      $this->assertQueryContentContains('table.result-data.frontdoordata th.name', $translate->_('IdentifierDoi'));
      $this->assertQueryContentContains('table.result-data.frontdoordata th.name', $translate->_('IdentifierIsbn'));
      $this->assertQueryContentContains('table.result-data.frontdoordata th.name', $translate->_('IdentifierIssn'));
      $this->assertQueryContentContains('table.result-data.frontdoordata th.name', $translate->_('IdentifierArxiv'));
      $this->assertQueryContentContains('table.result-data.frontdoordata th.name', $translate->_('IdentifierPubmed'));
      $this->assertQueryContentContains('table.result-data.frontdoordata th.name', $translate->_('TitleParent'));
      $this->assertQueryContentContains('table.result-data.frontdoordata th.name', $translate->_('TitleSub'));
      $this->assertQueryContentContains('table.result-data.frontdoordata th.name', $translate->_('TitleAdditional'));
      $this->assertQueryContentContains('table.result-data.frontdoordata th.name', $translate->_('Series'));
      $this->assertQueryContentContains('table.result-data.frontdoordata th.name', $translate->_('PublisherName'));
      $this->assertQueryContentContains('table.result-data.frontdoordata th.name', $translate->_('PublisherPlace'));
      $this->assertQueryContentContains('table.result-data.frontdoordata th.name', $translate->_('PersonEditor'));
      $this->assertQueryContentContains('table.result-data.frontdoordata th.name', $translate->_('PersonTranslator'));
      $this->assertQueryContentContains('table.result-data.frontdoordata th.name', $translate->_('PersonContributor'));
      $this->assertQueryContentContains('table.result-data.frontdoordata th.name', $translate->_('PersonReferee'));
      $this->assertQueryContentContains('table.result-data.frontdoordata th.name', $translate->_('PersonAdvisor'));
      $this->assertQueryContentContains('table.result-data.frontdoordata th.name', $translate->_('Type'));
      $this->assertQueryContentContains('table.result-data.frontdoordata th.name', $translate->_('Language'));
      $this->assertQueryContentContains('table.result-data.frontdoordata th.name', $translate->_('CompletedDate'));
      $this->assertQueryContentContains('table.result-data.frontdoordata th.name', $translate->_('PublishedDate'));
      $this->assertQueryContentContains('table.result-data.frontdoordata th.name', $translate->_('ThesisPublisher'));
      $this->assertQueryContentContains('table.result-data.frontdoordata th.name', $translate->_('ThesisGrantor'));
      $this->assertQueryContentContains('table.result-data.frontdoordata th.name', $translate->_('ThesisDateAccepted'));
      $this->assertQueryContentContains('table.result-data.frontdoordata th.name', $translate->_('CreatingCorporation'));
      $this->assertQueryContentContains('table.result-data.frontdoordata th.name', $translate->_('ContributingCorporation'));
      $this->assertQueryContentContains('table.result-data.frontdoordata th.name', $translate->_('ServerDatePublished'));
      $this->assertQueryContentContains('table.result-data.frontdoordata th.name', $translate->_('subject_frontdoor_swd'));
      $this->assertQueryContentContains('table.result-data.frontdoordata th.name', $translate->_('subject_frontdoor_uncontrolled'));
      $this->assertQueryContentContains('table.result-data.frontdoordata th.name', $translate->_('Volume'));
      $this->assertQueryContentContains('table.result-data.frontdoordata th.name', $translate->_('Issue'));
      $this->assertQueryContentContains('table.result-data.frontdoordata th.name', $translate->_('Edition'));
      $this->assertQueryContentContains('table.result-data.frontdoordata th.name', $translate->_('PageNumber'));
      $this->assertQueryContentContains('table.result-data.frontdoordata th.name', $translate->_('PageFirst'));
      $this->assertQueryContentContains('table.result-data.frontdoordata th.name', $translate->_('PageLast'));
      $this->assertQueryContentContains('table.result-data.frontdoordata th.name', $translate->_('Note'));
      // Enrichments
      $this->assertQueryContentContains('table.result-data.frontdoordata th.name', $translate->_('EnrichmentEvent'));
      $this->assertQueryContentContains('table.result-data.frontdoordata th.name', $translate->_('EnrichmentCity'));
      $this->assertQueryContentContains('table.result-data.frontdoordata th.name', $translate->_('EnrichmentCountry'));
      // Opus3 Enrichments
      $this->assertQueryContentContains('table.result-data.frontdoordata th.name', $translate->_('EnrichmentSourceTitle'));
      $this->assertQueryContentContains('table.result-data.frontdoordata th.name', $translate->_('EnrichmentSourceSwb'));
      $this->assertQueryContentContains('table.result-data.frontdoordata th.name', $translate->_('EnrichmentClassRvk'));
      $this->assertQueryContentContains('table.result-data.frontdoordata th.name', $translate->_('EnrichmentContributorsName'));
      
      $this->assertQueryContentContains('table.result-data.frontdoordata th.name', $translate->_('default_collection_role_institutes'));
      $this->assertQueryContentContains('table.result-data.frontdoordata th.name', $translate->_('default_collection_role_ccs'));
      $this->assertQueryContentContains('table.result-data.frontdoordata th.name', $translate->_('default_collection_role_ddc'));
      $this->assertQueryContentContains('table.result-data.frontdoordata th.name', $translate->_('default_collection_role_msc'));
      $this->assertQueryContentContains('table.result-data.frontdoordata th.name', $translate->_('default_collection_role_pacs'));
      $this->assertQueryContentContains('table.result-data.frontdoordata th.name', $translate->_('default_collection_role_bk'));
      $this->assertQueryContentContains('table.result-data.frontdoordata th.name', $translate->_('default_collection_role_jel'));
      
      $this->assertQueryContentContains('table.result-data.frontdoordata th.name', $translate->_('IdentifierSerial'));
      
      $this->assertQueryContentContains('table.result-data.frontdoordata th.name', $translate->_('Licence'));
      
   }

    public function testAbstractPreserveSpace() {
        $doc = new Opus_Document();
        $doc->setLanguage("eng");
        $doc->setServerState("published");

        $abstract = new Opus_Title();
        $abstract->setLanguage("eng");
        $abstract->setValue("foo\nbar\n\nbaz");
        $doc->addTitleAbstract($abstract);

        $doc->store();

        $this->dispatch('/frontdoor/index/index/docId/' . $doc->getId());
        
        $doc->deletePermanent();
        
        $this->assertContains('<li class="abstract preserve-spaces">' . "foo\nbar\n\nbaz</li>",
            $this->getResponse()->getBody());
    }

    public function testNotePerserveSpace() {
        $doc = new Opus_Document();
        $doc->setLanguage("eng");
        $doc->setServerState("published");
        
        $note = new Opus_Note();
        $note->setMessage("foo\nbar\n\nbaz");
        $note->setVisibility("public");
        $doc->addNote($note);

        $doc->store();

        $this->dispatch('/frontdoor/index/index/docId/' . $doc->getId());
        
        $doc->deletePermanent();
        
        $this->assertContains('<pre class="preserve-spaces">' . "foo\nbar\n\nbaz</pre>",
            $this->getResponse()->getBody());
    }

    /**
     * Regression Test for OPUSVIER-2651
     */
    public function testOPUSVIER2651NameNumber() {
        $role = new Opus_CollectionRole(7);
        $displayFrontdoor = $role->getDisplayFrontdoor();
        $role->setDisplayFrontdoor('Name,Number');
        $role->store();

        $this->dispatch('/frontdoor/index/index/docId/89');

        // undo changes
        $role->setDisplayBrowsing($displayFrontdoor);
        $role->store();

        $this->assertContains('</th><td>Maschinenbau, Energietechnik, Fertigungstechnik: Allgemeines 52.00</td></tr>',
            $this->getResponse()->getBody());
    }

    /**
     * Regression Test for OPUSVIER-2651
     */
    public function testOPUSVIER2651NumberName() {
        $role = new Opus_CollectionRole(7);
        $displayFrontdoor = $role->getDisplayFrontdoor();
        $role->setDisplayFrontdoor('Number,Name');
        $role->store();

        $this->dispatch('/frontdoor/index/index/docId/89');

        // undo changes
        $role->setDisplayBrowsing($displayFrontdoor);
        $role->store();

        $this->assertContains('</th><td>52.00 Maschinenbau, Energietechnik, Fertigungstechnik: Allgemeines</td></tr>', $this->getResponse()->getBody());
    }

    /**
     * Regression Test for OPUSVIER-2651
     */
    public function testOPUSVIER2651Name() {
        $role = new Opus_CollectionRole(7);
        $displayFrontdoor = $role->getDisplayFrontdoor();
        $role->setDisplayFrontdoor('Name');
        $role->store();

        $this->dispatch('/frontdoor/index/index/docId/89');

        // undo changes
        $role->setDisplayBrowsing($displayFrontdoor);
        $role->store();        

        $this->assertContains('</th><td>Maschinenbau, Energietechnik, Fertigungstechnik: Allgemeines</td></tr>',
            $this->getResponse()->getBody());
    }

    /**
     * Regression Test for OPUSVIER-2651
     */
    public function testOPUSVIER2651Number() {
        $role = new Opus_CollectionRole(7);
        $displayFrontdoor = $role->getDisplayFrontdoor();
        $role->setDisplayFrontdoor('Number');
        $role->store();

        $this->dispatch('/frontdoor/index/index/docId/89');

        // undo changes
        $role->setDisplayBrowsing($displayFrontdoor);
        $role->store();

        $this->assertContains('</th><td>52.00</td></tr>', $this->getResponse()->getBody());
    }

    public function testCollectionDisplayed() {
        $this->useEnglish();

        $this->dispatch('/frontdoor/index/index/docId/' . $this->_document_col->getId());

        // Sichtbare Collection mit sichtbarer CollectionRole wird angezeigt (Visible = 1, RoleVisibleFrontdoor = true)
        $this->assertQueryContentContains('table.result-data.frontdoordata th.name', 'CCS-Classification:');
        $this->assertQueryContentContains('td', 'B. Hardware');
    }

    public function testRegression3148InvisibleCollectionRoleNotDisplayed() {
        $this->useEnglish();

        $this->dispatch('/frontdoor/index/index/docId/' . $this->_document_col->getId());

        // Unsichtbare CollectionRole (Visible = 0, RoleVisibleFrontdoor = false)
        $this->assertNotQueryContentContains('table.result-data.frontdoordata th.name', 'invisible-collection:');
    }

    public function testRegression3148InvisibleCollectionNotDisplayed() {
        $this->useEnglish();

        $this->dispatch('/frontdoor/index/index/docId/' . $this->_document_col->getId());

        // Unsichtbare Collection
        $this->assertNotQueryContentContains('td', '28 Christliche Konfessionen');

        // CollectionRole wird nicht angezeigt, da keine sichtbare Collection vorhanden ist
        $this->assertNotQueryContentContains('table.result-data.frontdoordata th.name',
            'Dewey Decimal Classification:');
    }

    public function testRegression3148DisplayCollectionRoleWithVisibleAndInvisibleCollections() {
        $this->useEnglish();

        $this->dispatch('/frontdoor/index/index/docId/' . $this->_document_col->getId());

        // CollectionRole wird angezeigt
        $this->assertQueryContentContains('table.result-data.frontdoordata th.name', 'series:');

        $this->assertQueryContentContains('td', 'Schriftenreihe Schiffbau');
        $this->assertNotQueryContentContains('td', 'Band 363');
    }

    public function testServerDatePublishedOnFrontdoor() {
        $this->useGerman();
        $this->dispatch('/frontdoor/index/index/docId/146');
        $this->assertContains('<td>03.01.2012</td>', $this->getResponse()->getBody());
    }
    
    /**
     * Regression Test for OPUSVIER-3159
     */
    public function testGrantorDepartmentVisibleInFrontdoor() {
        $this->useGerman();
        $this->dispatch('/frontdoor/index/index/docId/146');
        $this->assertContains(
                '<tr><th class="name">Titel verleihende Institution:</th><td>Foobar Universität, Testwissenschaftliche Fakultät</td></tr>',
                $this->getResponse()->getBody());
        
    }

    public function testValidateXHTML() {
        $this->dispatch('/frontdoor/index/index/docId/146');
        $this->assertResponseCode(200);
        $this->validateXHTML();
    }

    public function testValidateXHTMLWithShortendAbstracts() {
        // Aktiviere Kürzung von Abstrakten
        $config = Zend_Registry::get('Zend_Config');
        $config->merge(new Zend_Config(array('frontdoor' => array('numOfShortAbstractChars' => 200))));

        $this->dispatch('/frontdoor/index/index/docId/92');
        $this->assertResponseCode(200);
        $this->validateXHTML();
    }
    
    public function testDisplayFullCollectionName() {
        $this->useGerman();
        $this->dispatch('/frontdoor/index/index/docId/146');
        $this->assertQueryContentContains('td', 'Technische Universität Hamburg-Harburg / Bauwesen / Abwasserwirtschaft und Gewässerschutz B-2');
    }

}