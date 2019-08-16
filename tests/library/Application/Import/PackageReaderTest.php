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
 * @category    Application Unit Tests
 * @package     Application
 * @author      Jens Schwidder <schwidder@zib.de>
 * @author      Sascha Szott <opus-development@saschaszott.de>
 * @copyright   Copyright (c) 2018-2019, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

class Application_Import_PackageReaderTest extends ControllerTestCase
{
    private $mockReader;

    protected $additionalResources = 'database';

    public function setUp()
    {
        parent::setUp();

        $this->mockReader = $this->getMockForAbstractClass('Application_Import_PackageReader');
    }

    public function testCreateExtractionDir()
    {
        $method = $this->getMethod('createExtractionDir');

        $baseDir = APPLICATION_PATH . '/tests/workspace/tmp/Application_Import_PackageReaderTest_createExtractionDir';
        mkdir($baseDir);

        $extractDir = $method->invokeArgs($this->mockReader, [$baseDir]);

        $this->assertTrue(is_dir($extractDir));
        $this->assertTrue(is_writable($extractDir));
        $this->assertStringStartsWith($baseDir, $extractDir);

        rmdir($extractDir);
        rmdir($baseDir);

        $this->assertFalse(is_dir($extractDir));
    }

    public function testProcessPackageWithMissingFile()
    {
        $method = $this->getMethod('processPackage');

        $extractDir = APPLICATION_PATH . '/tests/workspace/tmp/Application_Import_PackageReaderTest_processPackage_1';
        mkdir($extractDir);

        $statusDoc = $method->invokeArgs($this->mockReader, [$extractDir]);
        $this->assertNull($statusDoc);

        rmdir($extractDir);
    }

    public function testProcessPackageWithEmptyFile()
    {
        $method = $this->getMethod('processPackage');

        $extractDir = APPLICATION_PATH . '/tests/workspace/tmp/Application_Import_PackageReaderTest_processPackage_2';
        mkdir($extractDir);

        $metadataFile = $extractDir . DIRECTORY_SEPARATOR . Application_Import_PackageReader::METADATA_FILENAME;
        touch($metadataFile);

        $statusDoc = $method->invokeArgs($this->mockReader, [$extractDir]);
        $this->assertNull($statusDoc);

        unlink($metadataFile);
        rmdir($extractDir);
    }

    public function testProcessPackageWithInvalidFile()
    {
        $method = $this->getMethod('processPackage');

        $extractDir = APPLICATION_PATH . '/tests/workspace/tmp/Application_Import_PackageReaderTest_processPackage_3';
        mkdir($extractDir);

        $metadataFile = $extractDir . DIRECTORY_SEPARATOR . Application_Import_PackageReader::METADATA_FILENAME;
        touch($metadataFile);
        file_put_contents($metadataFile, '<import><opusDocument></opusDocument></import>');

        try {
            $this->setExpectedException('Application_Import_MetadataImportInvalidXmlException');
            $method->invokeArgs($this->mockReader, [$extractDir]);
        }
        finally {
            unlink($metadataFile);
            rmdir($extractDir);
        }
    }

    public function testProcessPackageWithValidFile()
    {
        $method = $this->getMethod('processPackage');

        $extractDir = APPLICATION_PATH . '/tests/workspace/tmp/Application_Import_PackageReaderTest_processPackage_4';
        mkdir($extractDir);

        $metadataFile = $extractDir . DIRECTORY_SEPARATOR . Application_Import_PackageReader::METADATA_FILENAME;
        touch($metadataFile);

        $xml = <<<XML
<import>
    <opusDocument language="eng" type="article" serverState="unpublished">
        <titlesMain>
            <titleMain language="eng">This is a test document</titleMain>
        </titlesMain>   
    </opusDocument>
</import>
XML;

        file_put_contents($metadataFile, $xml);

        $statusDoc = $method->invokeArgs($this->mockReader, [$extractDir]);
        $this->assertFalse($statusDoc->noDocImported());
        $this->assertCount(1, $statusDoc->getDocs());

        $doc = $statusDoc->getDocs()[0];
        $this->assertEquals('eng', $doc->getLanguage());
        $this->assertEquals('article', $doc->getType());
        $this->assertEquals('unpublished', $doc->getServerState());
        $this->assertCount(1, $doc->getTitleMain());
        $title = $doc->getTitleMain()[0];
        $this->assertEquals('eng', $title->getLanguage());
        $this->assertEquals('This is a test document', $title->getValue());

        $this->addTestDocument($doc); // for cleanup

        unlink($metadataFile);
        rmdir($extractDir);
    }

    protected static function getMethod($name)
    {
        $class = new ReflectionClass('Application_Import_PackageReader');
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }

    public static function cleanupTmpDir($tmpDirName)
    {
        $it = new RecursiveDirectoryIterator($tmpDirName, RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($files as $file) {
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }
        rmdir($tmpDirName);
    }
}
