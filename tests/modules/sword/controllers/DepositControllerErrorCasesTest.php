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
 * @category    Tests
 * @package     Sword
 * @author      Sascha Szott
 * @copyright   Copyright (c) 2016-2018
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 *
 * @covers Sword_DepositController
 */
class Sword_DepositControllerErrorCasesTest extends ControllerTestCase {

    private $testHelper;
    
    public function setUp() {
        parent::setUp();
        $this->testHelper = new DepositTestHelper();
        $this->testHelper->setupTmpDir();
    }
    
    public function testPostActionWithoutPassword() {
        $this->getRequest()->setMethod('POST');
        
        $this->dispatch('/sword/deposit');
        $this->assertResponseCode(403);
    }    
    
    public function testPostActionWithWrongPassword() {
        $this->getRequest()->setMethod('POST');
        $authString = base64_encode("username:badpassword");
        $this->getRequest()->setHeader('Authorization','Basic ' . $authString);        
        
        $this->dispatch('/sword/deposit');
        $this->assertResponseCode(403);
    }    

    public function testPostActionMediatedDepositNotAllowed() {
        $this->getRequest()->setMethod('POST');
        $this->getRequest()->setHeader('X-On-Behalf-Of', 'test');
        $this->testHelper->setValidAuthorizationHeader($this->getRequest(), DepositTestHelper::USER_AGENT);
        
        $this->dispatch('/sword/deposit');        
        $this->checkErrorDocument(412, 'http://purl.org/net/sword/error/MediationNotAllowed');        
    }
    
    public function testPostActionEmptyPayload() {
        $this->getRequest()->setMethod('POST');
        $this->testHelper->setValidAuthorizationHeader($this->getRequest(), DepositTestHelper::USER_AGENT);
        
        $this->dispatch('/sword/deposit');
        $this->checkErrorDocument(415, 'http://purl.org/net/sword/error/ErrorContent');        
    }
    
    public function testPostActionUnsupportedType() {
        $this->getRequest()->setMethod('POST');
        $this->getRequest()->setHeader('Content-Type', 'text/plain');
        $this->testHelper->setValidAuthorizationHeader($this->getRequest(), DepositTestHelper::USER_AGENT);
        $this->dispatch('/sword/deposit');
        
        $this->checkErrorDocument(415, 'http://purl.org/net/sword/error/ErrorContent');
    }    
    
    public function testPostActionTooLargePayload() {
        $this->getRequest()->setMethod('POST');
        $this->getRequest()->setHeader('Content-Type', DepositTestHelper::CONTENT_TYPE_ZIP);
        $this->testHelper->setValidAuthorizationHeader($this->getRequest(), DepositTestHelper::USER_AGENT);
        
        $maxUploadSize = new Application_Configuration_MaxUploadSize();
        $numOfBytes = 1 + $maxUploadSize->getMaxUploadSizeInByte();
        $payload = '';
        for ($i = 0; $i < $numOfBytes; $i++) {
            $payload .= chr(rand(0,255));
        }
        $this->getRequest()->setRawBody($payload);
        
        $this->dispatch('/sword/deposit');
        $this->checkErrorDocument(413, 'http://www.opus-repository.org/sword/error/PayloadToLarge');        
    }
    
    public function testPostActionMissingImportEnrichmentKey() {
        $this->getRequest()->setMethod('POST');
        $this->getRequest()->setHeader('Content-Type', DepositTestHelper::CONTENT_TYPE_ZIP);
        $this->testHelper->setValidAuthorizationHeader($this->getRequest(), DepositTestHelper::USER_AGENT);
        
        $this->getRequest()->setRawBody('some content');
        
        // remove enrichment key opus.import.user
        $enrichmentKey = new Opus_EnrichmentKey(Application_Import_AdditionalEnrichments::OPUS_IMPORT_USER);
        $enrichmentKey->delete();
        
        $this->dispatch('/sword/deposit');
        
        $enrichmentKey = new Opus_EnrichmentKey();
        $enrichmentKey->setName(Application_Import_AdditionalEnrichments::OPUS_IMPORT_USER);
        $enrichmentKey->store();
        
        $this->checkErrorDocument(400, 'http://www.opus-repository.org/sword/error/MissingImportEnrichmentKey');
    }
    
    public function testZipArchiveWithInvalidXml() {
        $this->depositError('invalid-xml.zip', DepositTestHelper::CONTENT_TYPE_ZIP, 400, 'http://www.opus-repository.org/sword/error/InvalidXml');
    }
    
    public function testTarArchiveWithInvalidXml() {
        $this->depositError('invalid-xml.tar', DepositTestHelper::CONTENT_TYPE_TAR, 400, 'http://www.opus-repository.org/sword/error/InvalidXml');
    }    
    
    public function testZipArchiveWithBadlyFormedXml() {
        $this->depositError('badlyformed-xml.zip', DepositTestHelper::CONTENT_TYPE_ZIP, 400, 'http://www.opus-repository.org/sword/error/InvalidXml');
    }
    
    public function testTarArchiveWithBadlyFormedXml() {
        $this->depositError('badlyformed-xml.tar', DepositTestHelper::CONTENT_TYPE_TAR, 400, 'http://www.opus-repository.org/sword/error/InvalidXml');
    }    

    public function testZipArchiveWithMissingXml() {
        $this->depositError('missing-xml.zip', DepositTestHelper::CONTENT_TYPE_ZIP, 400, 'http://www.opus-repository.org/sword/error/MissingXml');
    }
    
    public function testTarArchiveWithMissingXml() {
        $this->depositError('missing-xml.tar', DepositTestHelper::CONTENT_TYPE_TAR, 400, 'http://www.opus-repository.org/sword/error/MissingXml');
    }
    
    public function testZipArchiveWithEmptyXml() {
        $this->depositError('empty-xml.zip', DepositTestHelper::CONTENT_TYPE_ZIP, 400, 'http://www.opus-repository.org/sword/error/MissingXml');
    }
    
    public function testTarArchiveWithEmptyXml() {
        $this->depositError('empty-xml.tar', DepositTestHelper::CONTENT_TYPE_TAR, 400, 'http://www.opus-repository.org/sword/error/MissingXml');
    }

    public function testZipArchiveInvalidChecksum() {
        $this->depositError('minimal-record.zip', DepositTestHelper::CONTENT_TYPE_ZIP, 412, 'http://purl.org/net/sword/error/ErrorChecksumMismatch', '01234567890123456789012345678901');
    }

    public function testTarArchiveInvalidChecksum() {
        $this->depositError('minimal-record.tar', DepositTestHelper::CONTENT_TYPE_TAR, 412, 'http://purl.org/net/sword/error/ErrorChecksumMismatch', '01234567890123456789012345678901');
    }
    
    public function testZipArchiveProvokeUrnCollision() {
        $doc = $this->addDocWithUrn();
        $this->depositError('one-doc-with-urn.zip', DepositTestHelper::CONTENT_TYPE_ZIP, 400, 'http://www.opus-repository.org/sword/error/InternalFrameworkError');
        $doc->deletePermanent();
    }
    
    public function testTarArchiveProvokeUrnCollision() {
        $doc = $this->addDocWithUrn();
        $this->depositError('one-doc-with-urn.tar', DepositTestHelper::CONTENT_TYPE_TAR, 400, 'http://www.opus-repository.org/sword/error/InternalFrameworkError');
        $doc->deletePermanent();        
    }
    
    private function addDocWithUrn() {
        $doc = new Opus_Document();
        $doc->addIdentifier()->setType('urn')->setValue('colliding-urn');
        $doc->store();
        return $doc;
    }
        
    public function testIndexAction() {
        $this->getRequest()->setMethod('POST');
        $this->dispatch('/sword/deposit/index');
        $this->assertResponseCode(500);
    }

    public function testHeadAction() {
        $this->getRequest()->setMethod('HEAD');
        $this->dispatch('/sword/deposit');
        $this->assertResponseCode(500);
    }
    
    public function testGetAction() {
        $this->getRequest()->setMethod('GET');
        $this->dispatch('/sword/deposit');
        $this->assertResponseCode(500);
    }

    public function testPutAction() {
        $this->getRequest()->setMethod('PUT');
        $this->dispatch('/sword/deposit');
        $this->assertResponseCode(500);
    }

    public function testDeleteAction() {
        $this->getRequest()->setMethod('DELETE');
        $this->dispatch('/sword/deposit');
        $this->assertResponseCode(500);
    }    
        
    private function depositError($fileName, $contentType, $responseCode, $responseBody, $invalidChecksum = null) {
        $this->testHelper->assertEmptyTmpDir();
        $this->testHelper->disableExceptionConversion();
        
        $this->getRequest()->setMethod('POST');
        $this->getRequest()->setHeader('Content-Type', $contentType);
        $this->testHelper->setValidAuthorizationHeader($this->getRequest(), DepositTestHelper::USER_AGENT);
        $this->testHelper->uploadFile($this->getRequest(), $fileName, $invalidChecksum);
        
        $this->dispatch('/sword/deposit');
        $this->testHelper->assertEmptyTmpDir();
        $this->checkErrorDocument($responseCode, $responseBody);
    }   
    
    private function checkErrorDocument($responseCode, $hrefValue) {
        $this->assertResponseCode($responseCode);
        
        $doc = new DOMDocument();
        $doc->loadXML($this->getResponse()->getBody());
        
        $roots = $doc->childNodes;
        $this->assertEquals(1, $roots->length);
        $root = $roots->item(0);
        $this->assertEquals('sword:error', $root->nodeName);
        $attributes = $root->attributes;
        $this->assertEquals(1, $attributes->length);
        $attribute = $attributes->item(0);
        $this->assertEquals('href', $attribute->nodeName);
        $this->assertEquals($hrefValue, $attribute->nodeValue);
        
        $children = $root->childNodes;
        $this->assertEquals(4, $children->length);
        
        $this->testHelper->assertNodeProperties(0, $children, 'sword:title', 'ERROR');
        $this->testHelper->assertNodeProperties(1, $children, 'sword:generator', 'OPUS 4');
        $this->testHelper->assertNodeProperties(2, $children, 'sword:summary', '');
        $this->testHelper->assertNodeProperties(3, $children, 'sword:userAgent', DepositTestHelper::USER_AGENT);
    }
    
}