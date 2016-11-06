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
 * @author      Sascha Szott
 * @copyright   Copyright (c) 2016
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
class DepositTestHelper extends PHPUnit_Framework_Assert {

    const USER_AGENT = 'PHPUnit';      
    
    const CONTENT_TYPE_ZIP = 'application/zip';
    
    const CONTENT_TYPE_TAR = 'application/tar';
    
    private $collectionId = null;
    
    private $collectionName;
    
    private $collectionNumber;

    private $configBackup;
    
    private $frontdoorUrl;
    
    function __construct() {
        $config = Zend_Registry::get('Zend_Config');
        $config->sword->authFile = APPLICATION_PATH . '/tests/resources/sword-module-passwords.txt';
        Zend_Registry::set('Zend_Config', $config);
    }
    
    public function getCollectionId() {
        return $this->collectionId;
    }
    
    public function getCollectionName() {
        return $this->collectionName;
    }
    
    public function getCollectionNumber() {
        return $this->collectionNumber;
    }
    
    public function getFrontdoorUrl() {
        return $this->frontdoorUrl;
    }

    public function disableExceptionConversion() {
        PHPUnit_Framework_Error_Warning::$enabled = false;
        PHPUnit_Framework_Error_Notice::$enabled = false;
        PHPUnit_Framework_Error_Deprecated::$enabled = false;        
    }
    
    public function setValidAuthorizationHeader($request, $userAgent) {
        $authString = base64_encode("username:password");
        $request->setHeader('Authorization','Basic ' . $authString);        
        $request->setHeader('User-Agent', $userAgent);
    }
    
    public function uploadFile($request, $fileName, $checksum = null) {
        $archive = APPLICATION_PATH . '/tests/resources/sword-packages/' . $fileName;
        $handle = fopen($archive, 'rb');
        $contents = fread($handle, filesize($archive));
        $request->setRawBody($contents);        
        fclose($handle);
        
        if (is_null($checksum)) {
            // used to set an invalid checksum value
            $checksum = md5_file($archive);
        }        
        $request->setHeader('Content-MD5', $checksum);
        return $checksum;
    }
    
    public function addImportCollection() {
        if (is_null($this->collectionId)) {
            $collectionRole = Opus_CollectionRole::fetchByName('Import');
            $this->assertFalse(is_null($collectionRole), 'Collection Role "Import" is part of standard distribution since OPUS 4.5');
            $rootCollection = $collectionRole->getRootCollection();

            // create temporary collection
            $collection = new Opus_Collection();     
            $timestamp = time();
            $this->collectionNumber = 'sword-test-number-' . $timestamp;
            $collection->setNumber($this->collectionNumber);
            $this->collectionName = 'sword-test-name-' . $timestamp;
            $collection->setName($this->collectionName);
            $rootCollection->addLastChild($collection);
            $this->collectionId = $collection->store();

            $this->configBackup = Zend_Registry::get('Zend_Config');
            $config = Zend_Registry::get('Zend_Config');
            $config->sword->collection->default->number = $this->collectionNumber;
            $config->sword->collection->default->abstract = 'sword.collection.default.abstract';
            $config->sword->collection->default->collectionPolicy = 'sword.collection.default.collectionPolicy';
            $config->sword->collection->default->treatment = 'sword.collection.default.treatment';
            $config->sword->collection->default->acceptPackaging = 'sword.collection.default.acceptPackaging';
            Zend_Registry::set('Zend_Config', $config);            
        }
    }    
        
    public function removeImportCollection() {
        if (!is_null($this->collectionId)) {
            $collection = new Opus_Collection($this->collectionId);
            $collection->delete();
            $this->collectionId = null;
            Zend_Registry::set('Zend_Config', $this->configBackup);
        }        
    }    
    
    public function assertTitleValues($title, $value, $language) {
        $this->assertEquals($value, $title->getValue());
        $this->assertEquals($language, $title->getLanguage());        
    }    
    
    public function assertEmptyTmpDir() {
        $config = Zend_Registry::get('Zend_Config');
        $dirName = trim($config->workspacePath) . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR;
        $files = scandir($dirName);
        foreach ($files as $file) {
            $this->assertTrue(in_array($file, array( '.', '..', '.gitignore', 'resumption')));
        }
    }

    public function assertNodeProperties($index, $root, $nodeName, $nodeValue) {
        $domNode = $root->item($index);
        $this->assertEquals($nodeName, $domNode->nodeName);
        $this->assertEquals($nodeValue, $domNode->nodeValue);
    }
    
    public function assertImportEnrichments($doc, $fileName, $checksum, $expectedNumOfEnrichments) {
        $enrichments = $doc->getEnrichment();
        $this->assertEquals($expectedNumOfEnrichments, count($enrichments));
        
        foreach ($enrichments as $enrichment) {
            switch ($enrichment->getKeyName()) {
                case Application_Import_AdditionalEnrichments::OPUS_IMPORT_CHECKSUM:
                    $this->assertEquals($checksum, $enrichment->getValue());
                    break;
                case Application_Import_AdditionalEnrichments::OPUS_IMPORT_DATE:
                    $dateStr = $enrichment->getValue();
                    $this->assertTrue(trim($dateStr) !== '');
                    $date = new DateTime($dateStr, new DateTimeZone('GMT'));
                    $this->assertTrue($date <= new DateTime());
                    break;
                case Application_Import_AdditionalEnrichments::OPUS_IMPORT_FILE:
                    $this->assertEquals($fileName, $enrichment->getValue());
                    break;
                case Application_Import_AdditionalEnrichments::OPUS_IMPORT_USER:
                    $this->assertEquals('username', $enrichment->getValue());
                    break;
                default:
                    if ($expectedNumOfEnrichments == 4) {
                        throw new Exception('unexpected enrichment key ' . $enrichment->getKeyName());
                    }
            }
        }        
    }
    
    public function checkAtomEntryDocument($root, $fileName, $checksum, $abstractExist = true, $numOfEnrichments = 4, $numOfCollections = 1) {
        $this->assertEquals('entry', $root->nodeName);
        $attributes = $root->attributes;
        $this->assertEquals(0, $attributes->length);        

        $entryChildren = $root->childNodes;
        if ($abstractExist) {
            $this->assertEquals(12, $entryChildren->length);
        }
        else {
            $this->assertEquals(11, $entryChildren->length);
        }

        $idNode = $entryChildren->item(0);
        $this->assertEquals('id', $idNode->nodeName);
        $docId = $idNode->nodeValue;
        $doc = new Opus_Document($docId);

        $this->assertNodeProperties(1, $entryChildren, 'updated', $doc->getServerDateCreated());
        $this->assertNodeProperties(2, $entryChildren, 'title', $doc->getTitleMain(0)->getValue());

        $authorNode = $entryChildren->item(3);
        $this->assertEquals('author', $authorNode->nodeName);
        $authorChildren = $authorNode->childNodes;
        $this->assertEquals(1, $authorChildren->length);
        $nameNode = $authorChildren->item(0);
        $this->assertEquals('name', $nameNode->nodeName);
        $this->assertEquals('username', $nameNode->nodeValue);

        $offset = 0;
        if ($abstractExist) {
            $this->assertNodeProperties(4, $entryChildren, 'summary', $doc->getTitleAbstract(0)->getValue());
            $offset = 1;
        }                

        $contentNode = $entryChildren->item(4 + $offset);
        $this->assertEquals('content', $contentNode->nodeName);
        $attributes = $contentNode->attributes;
        $this->assertEquals(2, $attributes->length);

        $attribute = $attributes->item(0);
        $this->assertEquals('type', $attribute->nodeName);
        $this->assertEquals('text/html', $attribute->nodeValue);

        $attribute = $attributes->item(1);
        $this->assertEquals('src', $attribute->nodeName);
        $this->frontdoorUrl = 'http:///frontdoor/index/index/docId/' . $docId;
        $this->assertEquals($this->frontdoorUrl, $attribute->nodeValue);

        $config = Zend_Registry::get('Zend_Config');
        $generatorValue = $config->sword->generator;
        $this->assertNodeProperties(5 + $offset, $entryChildren, 'generator', $generatorValue);

        $this->assertNodeProperties(6 + $offset, $entryChildren, 'sword:userAgent', self::USER_AGENT);

        $treatmentValue = $config->sword->treatment;
        $this->assertNodeProperties(7 + $offset, $entryChildren, 'sword:treatment', $treatmentValue);
        
        $this->assertNodeProperties(8 + $offset, $entryChildren, 'sword:packaging', 'sword.collection.default.acceptPackaging');
        $this->assertNodeProperties(9 + $offset, $entryChildren, 'sword:verboseDescription', '');
        $this->assertNodeProperties(10 + $offset, $entryChildren, 'sword:noOp', 'false');

        $this->assertImportEnrichments($doc, $fileName, $checksum, $numOfEnrichments);
        $this->assertImportCollection($doc, $numOfCollections);      
        
        return $doc;
    }
            
    private function assertImportCollection($doc, $numOfCollections = 1) {
        $collections = $doc->getCollection();
        $this->assertEquals($numOfCollections, count($collections));
        $collection = $collections[$numOfCollections - 1];
        $this->assertEquals($this->collectionId, $collection->getId());        
    }

    
}
