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
 * @copyright   Copyright (c) 2016, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\Collection;
use Opus\Common\CollectionRole;
use Opus\Common\Config;
use Opus\Common\Document;
use Opus\Common\DocumentInterface;
use Opus\Common\Model\NotFoundException;
use Opus\Common\TitleInterface;
use Opus\Import\AdditionalEnrichments;
use PHPUnit\Framework\Assert;

class DepositTestHelper extends Assert
{
    public const USER_AGENT = 'PHPUnit';

    public const CONTENT_TYPE_ZIP = 'application/zip';

    public const CONTENT_TYPE_TAR = 'application/tar';

    /** @var int */
    private $collectionId;

    /** @var string */
    private $collectionName;

    /** @var string */
    private $collectionNumber;

    /** @var string */
    private $frontdoorUrl;

    /**
     * @return int
     */
    public function getCollectionId()
    {
        return $this->collectionId;
    }

    /**
     * @return string
     */
    public function getCollectionName()
    {
        return $this->collectionName;
    }

    /**
     * @return string
     */
    public function getCollectionNumber()
    {
        return $this->collectionNumber;
    }

    /**
     * @return string
     */
    public function getFrontdoorUrl()
    {
        return $this->frontdoorUrl;
    }

    public function disableExceptionConversion()
    {
        /* TODO BUG this does not work with PHPUnit 8.5 anymore
        Warning::$enabled    = false;
        Notice::$enabled     = false;
        Deprecated::$enabled = false;
        */
    }

    /**
     * @param Zend_Controller_Request_Http $request
     * @param string                       $userAgent
     */
    public function setValidAuthorizationHeader($request, $userAgent)
    {
        $authString = base64_encode('sworduser:sworduserpwd');
        $request->setHeader('Authorization', 'Basic ' . $authString);
        $request->setHeader('User-Agent', $userAgent);
    }

    /**
     * @param Zend_Controller_Request_Http $request
     * @param string                       $fileName
     * @param string|null                  $checksum
     * @return string
     */
    public function uploadFile($request, $fileName, $checksum = null)
    {
        $archive  = APPLICATION_PATH . '/tests/resources/sword-packages/' . $fileName;
        $handle   = fopen($archive, 'rb');
        $contents = fread($handle, filesize($archive));
        $request->setRawBody($contents);
        fclose($handle);

        if ($checksum === null) {
            // used to set an invalid checksum value
            $checksum = md5_file($archive);
        }
        $request->setHeader('Content-MD5', $checksum);
        return $checksum;
    }

    public function addImportCollection()
    {
        if ($this->collectionId === null) {
            $collectionRole = CollectionRole::fetchByName('Import');
            $this->assertNotNull(
                $collectionRole,
                'Collection Role "Import" is part of standard distribution since OPUS 4.5'
            );
            $rootCollection = $collectionRole->getRootCollection();

            // create temporary collection
            $collection             = Collection::new();
            $timestamp              = time();
            $this->collectionNumber = 'sword-test-number-' . $timestamp;
            $collection->setNumber($this->collectionNumber);
            $this->collectionName = 'sword-test-name-' . $timestamp;
            $collection->setName($this->collectionName);
            $rootCollection->addLastChild($collection);
            $this->collectionId = (int) $collection->store();

            $config                                               = Config::get();
            $config->sword->collection->default->number           = $this->collectionNumber;
            $config->sword->collection->default->abstract         = 'sword.collection.default.abstract';
            $config->sword->collection->default->collectionPolicy = 'sword.collection.default.collectionPolicy';
            $config->sword->collection->default->treatment        = 'sword.collection.default.treatment';
            $config->sword->collection->default->acceptPackaging  = 'sword.collection.default.acceptPackaging';
        }
    }

    public function removeImportCollection()
    {
        if ($this->collectionId !== null) {
            $collection = Collection::get($this->collectionId);
            $collection->delete();
            $this->collectionId = null;
        }
    }

    /**
     * @param TitleInterface $title
     * @param string         $value
     * @param string         $language
     */
    public function assertTitleValues($title, $value, $language)
    {
        $this->assertEquals($value, $title->getValue());
        $this->assertEquals($language, $title->getLanguage());
    }

    /**
     * Creates separate tmp folder for sword tests.
     *
     * TODO folder is reused, but never removed
     */
    public function setupTmpDir()
    {
        $appConfig = Application_Configuration::getInstance();
        $tempPath  = $appConfig->getTempPath() . 'sword';
        if (! file_exists($tempPath)) {
            mkdir($tempPath);
        }
        $appConfig->setTempPath($tempPath);
    }

    /**
     * Check if workspace/tmp folder does not contain unexpected files.
     *
     * @throws Zend_Exception
     */
    public function assertEmptyTmpDir()
    {
        $dirName = Application_Configuration::getInstance()->getTempPath();
        $files   = scandir($dirName);
        foreach ($files as $file) {
            $this->assertTrue(in_array($file, ['.', '..', '.gitignore', 'resumption']), $dirName);
        }
    }

    /**
     * @param int    $index
     * @param mixed  $root
     * @param string $nodeName
     * @param string $nodeValue
     */
    public function assertNodeProperties($index, $root, $nodeName, $nodeValue)
    {
        $domNode = $root->item($index);
        $this->assertEquals($nodeName, $domNode->nodeName);
        $this->assertEquals($nodeValue, $domNode->nodeValue);
    }

    /**
     * @param DocumentInterface $doc
     * @param string            $fileName
     * @param string            $checksum
     * @param int               $expectedNumOfEnrichments
     * @throws Exception
     */
    public function assertImportEnrichments($doc, $fileName, $checksum, $expectedNumOfEnrichments)
    {
        $enrichments = $doc->getEnrichment();
        $this->assertEquals($expectedNumOfEnrichments, count($enrichments));

        foreach ($enrichments as $enrichment) {
            switch ($enrichment->getKeyName()) {
                case AdditionalEnrichments::OPUS_IMPORT_CHECKSUM:
                    $this->assertEquals($checksum, $enrichment->getValue());
                    break;
                case AdditionalEnrichments::OPUS_IMPORT_DATE:
                    $dateStr = $enrichment->getValue();
                    $this->assertTrue(trim($dateStr) !== '');
                    $date = new DateTime($dateStr, new DateTimeZone('GMT'));
                    $this->assertTrue($date <= new DateTime());
                    break;
                case AdditionalEnrichments::OPUS_IMPORT_FILE:
                    $this->assertEquals($fileName, $enrichment->getValue());
                    break;
                case AdditionalEnrichments::OPUS_IMPORT_USER:
                    $this->assertEquals('sworduser', $enrichment->getValue());
                    break;
                case AdditionalEnrichments::OPUS_SOURCE:
                    $this->assertEquals('sword', $enrichment->getValue());
                    break;
                default:
                    if ($expectedNumOfEnrichments === 5) {
                        throw new Exception('unexpected enrichment key ' . $enrichment->getKeyName());
                    }
            }
        }
    }

    /**
     * @param mixed  $root
     * @param string $fileName
     * @param string $checksum
     * @param bool   $abstractExist
     * @param int    $numOfEnrichments
     * @param int    $numOfCollections
     * @return DocumentInterface
     * @throws NotFoundException
     */
    public function checkAtomEntryDocument(
        $root,
        $fileName,
        $checksum,
        $abstractExist = true,
        $numOfEnrichments = 5,
        $numOfCollections = 1
    ) {
        $this->assertEquals('entry', $root->nodeName);
        $attributes = $root->attributes;
        $this->assertEquals(0, $attributes->length);

        $entryChildren = $root->childNodes;
        if ($abstractExist) {
            $this->assertEquals(12, $entryChildren->length);
        } else {
            $this->assertEquals(11, $entryChildren->length);
        }

        $idNode = $entryChildren->item(0);
        $this->assertEquals('id', $idNode->nodeName);
        $docId = $idNode->nodeValue;
        $doc   = Document::get($docId);

        $this->assertNodeProperties(1, $entryChildren, 'updated', $doc->getServerDateCreated());
        $this->assertNodeProperties(2, $entryChildren, 'title', $doc->getTitleMain(0)->getValue());

        $authorNode = $entryChildren->item(3);
        $this->assertEquals('author', $authorNode->nodeName);
        $authorChildren = $authorNode->childNodes;
        $this->assertEquals(1, $authorChildren->length);
        $nameNode = $authorChildren->item(0);
        $this->assertEquals('name', $nameNode->nodeName);
        $this->assertEquals('sworduser', $nameNode->nodeValue);

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

        $config         = Config::get();
        $generatorValue = $config->sword->generator;
        $this->assertNodeProperties(5 + $offset, $entryChildren, 'generator', $generatorValue);

        $this->assertNodeProperties(6 + $offset, $entryChildren, 'sword:userAgent', self::USER_AGENT);

        $treatmentValue = $config->sword->treatment;
        $this->assertNodeProperties(7 + $offset, $entryChildren, 'sword:treatment', $treatmentValue);

        $this->assertNodeProperties(
            8 + $offset,
            $entryChildren,
            'sword:packaging',
            'sword.collection.default.acceptPackaging'
        );
        $this->assertNodeProperties(9 + $offset, $entryChildren, 'sword:verboseDescription', '');
        $this->assertNodeProperties(10 + $offset, $entryChildren, 'sword:noOp', 'false');

        $this->assertImportEnrichments($doc, $fileName, $checksum, $numOfEnrichments);
        $this->assertImportCollection($doc, $numOfCollections);

        return $doc;
    }

    /**
     * @param DocumentInterface $doc
     * @param int               $numOfCollections
     */
    private function assertImportCollection($doc, $numOfCollections = 1)
    {
        $collections = $doc->getCollection();
        $this->assertEquals($numOfCollections, count($collections));
        $collection = $collections[$numOfCollections - 1];
        $this->assertEquals($this->collectionId, (int) $collection->getId());
    }
}
