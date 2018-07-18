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
class Sword_DepositControllerMultipleDocsTest extends ControllerTestCase {

    private $testHelper;
    
    public function setUp() {
        parent::setUp();
        $this->testHelper = new DepositTestHelper();
        $this->testHelper->setupTmpDir();
    }

    public function testZipArchiveWith3Docs() {
        $this->checkMultipleDocsDeposit('multiple-docs.zip', DepositTestHelper::CONTENT_TYPE_ZIP);
    }
    
    public function testTarArchiveWith3Docs() {
        $this->checkMultipleDocsDeposit('multiple-docs.tar', DepositTestHelper::CONTENT_TYPE_TAR);
    }

    private function checkMultipleDocsDeposit($fileName, $contentType) {
        $this->testHelper->assertEmptyTmpDir();
        $this->testHelper->disableExceptionConversion();
        
        $this->getRequest()->setMethod('POST');
        $this->getRequest()->setHeader('Content-Type', $contentType);
        $this->testHelper->setValidAuthorizationHeader($this->getRequest(), DepositTestHelper::USER_AGENT);
        $checksum = $this->testHelper->uploadFile($this->getRequest(), $fileName);
        $this->getRequest()->setHeader('Content-Disposition', $fileName);
        $this->testHelper->addImportCollection();

        $this->dispatch('/sword/deposit');                        
        $this->testHelper->assertEmptyTmpDir();
        
        $this->checkMultipleAtomEntryDocument($checksum, $fileName);
        
        $this->testHelper->removeImportCollection();
    }    
    
    private function checkMultipleAtomEntryDocument($checksum, $fileName) {
        $this->assertEquals(201, $this->getResponse()->getHttpResponseCode());
        
        $headers = $this->getResponse()->getHeaders();
        $this->assertEquals(1, count($headers));
        $header = $headers[0];
        // Location Header tritt bei Multiple-Doc Deposit nicht auf
        $this->assertEquals('Content-Type', $header['name']);
        $this->assertEquals('application/atom+xml; charset=UTF-8', $header['value']);
                        
        $doc = new DOMDocument();
        $doc->loadXML($this->getResponse()->getBody());
        
        $roots = $doc->childNodes;
        $this->assertEquals(1, $roots->length);
        $root = $roots->item(0);
        $this->assertEquals('opus:entries', $root->nodeName);
        $attributes = $root->attributes;
        $this->assertEquals(0, $attributes->length);
        
        $children = $root->childNodes;
        $this->assertEquals(3, $children->length);
        
        $docCount = 1;
        foreach ($children as $child) {
            $doc = $this->testHelper->checkAtomEntryDocument($child, $fileName, $checksum);
            $this->checkMetadata($docCount, $doc);
            $doc->deletePermanent();  
            $docCount++;
        }
    }
    
    private function checkMetadata($docCount, $doc) {                        
        switch ($docCount) {
            case 1:
                $this->checkBasicMetadata($docCount, $doc, 'book', 'deu');
                $files = $doc->getFile();
                $this->assertEquals(2, count($files));
                $this->checkFile($files[0], 'doc1.pdf', 'deu', 'doc1', 'comment1');
                $this->checkFile($files[1], 'doc1.txt', 'deu', '', 'comment1');
                break;
            case 2:
                $this->checkBasicMetadata($docCount, $doc, 'chapter', 'eng');
                $files = $doc->getFile();
                $this->assertEquals(1, count($files));
                $this->checkFile($files[0], 'doc2.pdf', 'eng', '', 'comment2');
                break;
            case 3:
                $this->checkBasicMetadata($docCount, $doc, 'article', 'deu');
                $files = $doc->getFile();
                $this->assertEquals(1, count($files));
                $this->checkFile($files[0], 'doc3.pdf', 'deu', 'doc3', 'comment3');
                break;
            default:
                throw new Exception('unexpected value of docCount');
        }
    }
        
    private function checkBasicMetadata($docCount, $doc, $type, $language) {
        $this->assertEquals('unpublished', $doc->getServerState());
        $this->assertEquals($type, $doc->getType());
        $this->assertEquals($language, $doc->getLanguage());                   
        $this->testHelper->assertTitleValues($doc->getTitleMain(0), 'Title Main ' . $docCount, $doc->getLanguage());
        $this->testHelper->assertTitleValues($doc->getTitleAbstract(0), 'Abstract ' . $docCount, $doc->getLanguage());        
    }
 
    private function checkFile($file, $name, $language, $displayName, $comment) {
        $this->assertEquals($name, $file->getPathName());
        $this->assertEquals($language, $file->getLanguage());
        if (!is_null($displayName)) {
            $this->assertEquals($displayName, $file->getLabel());
        }
        $this->assertEquals($comment, $file->getComment());
    }    

}