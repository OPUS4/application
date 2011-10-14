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
 * @author      Thoralf Klein <thoralf.klein@zib.de>
 * @author      Sascha Szott <szott@zib.de>
 * @copyright   Copyright (c) 2008-2011, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class Oai_IndexControllerTest extends ControllerTestCase {

    /**
     * Basic test for invalid verbs.
     */
    public function testInvalidVerb() {
        $this->dispatch('/oai?verb=InvalidVerb');
        $this->assertResponseCode(200);

        $response = $this->getResponse();
        $this->assertContains('badVerb', $response->getBody(),
           "Response must contain 'badVerb'");
    }

    /**
     * Basic test for requests without verb.
     */
    public function testNoVerb() {
        $this->dispatch('/oai');
        $this->assertResponseCode(200);

        $response = $this->getResponse();
        $this->assertContains('badVerb', $response->getBody(),
           "Response must contain 'badVerb'");
    }

    /**
     * Test verb=Identify.
     */
    public function testIdentify() {
        $this->dispatch('/oai?verb=Identify');
        $this->assertResponseCode(200);

        $response = $this->getResponse();
        $this->checkForBadStringsInHtml($response->getBody());
    }

    /**
     * Test verb=ListMetadataFormats.
     */
    public function testListMetadataFormats() {
        $this->dispatch('/oai?verb=ListMetadataFormats');
        $this->assertResponseCode(200);

        $response = $this->getResponse();
        $this->checkForBadStringsInHtml($response->getBody());
    }

    /**
     * Test verb=ListSets.
     */
    public function testListSets() {
        $this->dispatch('/oai?verb=ListSets');
        $this->assertResponseCode(200);

        $response = $this->getResponse();
        $this->checkForBadStringsInHtml($response->getBody());
    }

    /**
     * Test verb=GetRecord, prefix=xMetaDiss.
     */
    public function testGetRecordxMetaDiss() {
        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=xMetaDiss&identifier=oai::80');
        $this->assertResponseCode(200);

        $response = $this->getResponse();
        $this->checkForBadStringsInHtml($response->getBody());

        $this->assertContains('oai::80', $response->getBody(),
           "Response must contain 'oai::80'");
    }

    /**
     * Test verb=GetRecord, prefix=oai_dc.
     */
    public function testGetRecordOaiDc() {
        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=oai_dc&identifier=oai::35');
        $this->assertResponseCode(200);

        $response = $this->getResponse();
        $this->checkForBadStringsInHtml($response->getBody());
    }

    /**
     * Test verb=GetRecord, prefix=XMetaDissPlus.
     */
    public function testGetRecordXMetaDissPlus() {
        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=XMetaDissPlus&identifier=oai::41');
        $this->assertResponseCode(200);

        $response = $this->getResponse();
        $badStrings = array("Exception", "Error", "Stacktrace", "badVerb");
        $this->checkForCustomBadStringsInHtml($response->getBody(), $badStrings);

        $this->assertContains('oai::41', $response->getBody(),
           "Response must contain 'oai::41'");

        $this->assertContains('xMetaDiss', $response->getBody(),
           "Response must contain 'xMetaDiss'");
    }

    /**
     * Test verb=GetRecord, prefix=xMetaDissPlus.
     */
    public function testGetRecordXMetaDissPlusAlternativeSpelling() {
        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=xMetaDissPlus&identifier=oai::41');
        $this->assertResponseCode(200);

        $response = $this->getResponse();
        $badStrings = array("Exception", "Error", "Stacktrace", "badVerb");
        $this->checkForCustomBadStringsInHtml($response->getBody(), $badStrings);

        $this->assertContains('oai::41', $response->getBody(),
           "Response must contain 'oai::41'");

        $this->assertContains('xMetaDiss', $response->getBody(),
           "Response must contain 'xMetaDiss'");
    }

    /**
     * Test verb=ListIdentifiers.
     */
    public function testListIdentifiers() {
        $this->dispatch('/oai?verb=ListIdentifiers&metadataPrefix=oai_dc');
        $this->assertResponseCode(200);

        $response = $this->getResponse();
        $this->checkForBadStringsInHtml($response->getBody());
    }

    /**
     * Test verb=ListRecords, metadataPrefix=oai_dc.
     */
    public function testListRecords() {
        $this->dispatch('/oai?verb=ListRecords&metadataPrefix=oai_dc&from=2006-01-01');
        $this->assertResponseCode(200);

        $response = $this->getResponse();
        $badStrings = array("Exception", "Stacktrace", "badVerb");
        $this->checkForCustomBadStringsInHtml($response->getBody(), $badStrings);

        $this->assertContains('<ListRecords>', $response->getBody(),
           "Response must contain '<ListRecords>'");
        $this->assertContains('<record>', $response->getBody(),
           "Response must contain '<record>'");
    }

    /**
     * Test verb=ListRecords, metadataPrefix=XMetaDissPlus, different intervals
     * list possible intervals containing "2010-06-05"
     */
    public function testListRecordsFromUntilDateWithDoc3() {
        $interval_strings = array(
            '',
            '&from=2010-06-04',
            '&until=2010-06-04',
            '&from=2010-06-03',
            '&until=2010-06-05',
            '&from=2010-06-04&until=2010-06-04',
            '&from=2010-06-03&until=2010-06-04',
            '&from=2010-06-04&until=2010-06-05',
            '&from=2010-06-03&until=2010-06-04',
        );

        foreach ($interval_strings AS $interval_string) {
            $this->dispatch('/oai?verb=ListRecords&metadataPrefix=XMetaDissPlus'.$interval_string);
            $this->assertResponseCode(200);

            $response = $this->getResponse();
            $badStrings = array("Exception", "Stacktrace", "badVerb");
            $this->checkForCustomBadStringsInHtml($response->getBody(), $badStrings);

            $this->assertContains('<ListRecords>', $response->getBody(),
               "Response for $interval_string must contain '<ListRecords>'");
            $this->assertContains('<record>', $response->getBody(),
               "Response for $interval_string must contain '<record>'");
            $this->assertContains(':3</identifier>', $response->getBody(),
               "Response for $interval_string must contain '<record>'");
        }
    }

    /**
     * Test verb=ListRecords, metadataPrefix=XMetaDissPlus, different intervals
     * list possible intervals *NOT* containing "2010-06-05"
     */
    public function testListRecordsFromUntilDateWithoutDoc3() {
        $interval_strings = array(
            '&from=2010-06-05',
            '&until=2010-06-03',
            '&from=2010-06-05&until=2010-06-06',
            '&from=2010-06-02&until=2010-06-03',
        );

        foreach ($interval_strings AS $interval_string) {
            $this->dispatch('/oai?verb=ListRecords&metadataPrefix=XMetaDissPlus'.$interval_string);
            $this->assertResponseCode(200);

            $response = $this->getResponse();
            $badStrings = array("Exception", "Stacktrace", "badVerb");
            $this->checkForCustomBadStringsInHtml($response->getBody(), $badStrings);

            $this->assertNotContains(':3</identifier>', $response->getBody(),
               "Response for $interval_string must contain '<record>'");
        }
    }
    
    /**
     * Test that proves the bugfix for OPUSVIER-1710 is working as intended.
     */
    public function testGetDeletedDocumentReturnsStatusDeleted() {
        $r = Opus_UserRole::fetchByName('guest');

        $modules = $r->listAccessModules();
        $addOaiModuleAccess = !in_array('oai', $modules);
        if ($addOaiModuleAccess) {
            $r->appendAccessModule('oai');
            $r->store();
        }

        // enable security
        $config = Zend_Registry::get('Zend_Config');
        $security = $config->security;
        $config->security = '1';
        Zend_Registry::set('Zend_Config', $config);

        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=copy_xml&identifier=oai::123');
        
        if ($addOaiModuleAccess) {
            $r->removeAccessModule('oai');
            $r->store();
        }

        // restore security settings
        $config->security = $security;
        Zend_Registry::set('Zend_Config', $config);

        $this->assertEquals(200, $this->getResponse()->getHttpResponseCode());
        $this->assertContains('<GetRecord>', $this->getResponse()->getBody());
        $this->assertContains('<header status="deleted">', $this->getResponse()->getBody());

        $this->assertNotContains('<error>', $this->getResponse()->getBody());
        $this->assertNotContains('<error>Unauthorized: Access to module not allowed.</error>', $this->getResponse()->getBody());
        $this->assertNotContains('<error code="unknown">An internal error occured.</error>', $this->getResponse()->getBody());
    }

    public function testTransferUrlIsPresent() {
        $doc = new Opus_Document();
        $doc->setServerState('published');        
        $file = new Opus_File();
        $file->setVisibleInOai(true);
        $file->setPathName('foobar.pdf');
        $doc->addFile($file);
        $doc->store();

        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=xMetaDissPlus&identifier=oai::' . $doc->getId());
        $this->assertResponseCode(200);
        $this->assertContains('<ddb:transfer ddb:type="dcterms:URI">', $this->getResponse()->getBody());
        $this->assertContains($this->getRequest()->getBaseUrl() . '/oai/container/index/docId/' . $doc->getId() . '</ddb:transfer>', $this->getResponse()->getBody());
        
        $doc->deletePermanent();
    }

    public function testTransferUrlIsNotPresent() {
        $doc = new Opus_Document();
        $doc->setServerState("published");
        $doc->store();
        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=xMetaDissPlus&identifier=oai::' . $doc->getId());
        $this->assertResponseCode(200);
        $this->assertNotContains('<ddb:transfer ddb:type="dcterms:URI">', $this->getResponse()->getBody());
        $doc->deletePermanent();
    }
    
    /**
     * Test if the flag "VisibileInOai" affects all files of a document
     */
    public function testDifferentFilesVisibilityOfOneDoc() {

        //create document with two files
        $d = new Opus_Document();
        $d->setServerState('published');
        
        $f1 = new Opus_File();
        $f1->setPathName('foo.pdf');
        $f1->setVisibleInOai(false);
        $d->addFile($f1);

        $f2 = new Opus_File();
        $f2->setPathName('bar.pdf');
        $f2->setVisibleInOai(false);
        $d->addFile($f2);

        $d->store();
        $id = $d->getId();
        
        //oai query of that document
        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=copy_xml&identifier=oai::' . $id);
        $response = $this->getResponse()->getBody();
        $this->assertContains('<Opus_Document xmlns="" Id="' . $id . '"', $response);
        $this->assertNotContains('<File', $response);        

    }

}
