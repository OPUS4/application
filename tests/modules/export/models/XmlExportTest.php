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
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\Repository;
use Opus\Common\Title;
use Opus\Search\Util\Query;

/**
 * @covers \Export_Model_XmlExport
 */
class Export_Model_XmlExportTest extends ControllerTestCase
{
    /** @var string[] */
    protected $additionalResources = ['database'];

    /** @var Export_Model_XmlExport */
    private $plugin;

    public function setUp(): void
    {
        parent::setUp();

        $plugin = new Export_Model_XmlExport();
        $plugin->setRequest($this->getRequest());
        $plugin->setResponse($this->getResponse());
        $plugin->init();
        $plugin->setConfig(new Zend_Config([
            'class'             => 'Export_Model_XmlExport',
            'maxDocumentsGuest' => '100',
            'maxDocumentsUser'  => '500',
        ]));

        $this->plugin = $plugin;
    }

    public function testXmlPreparation()
    {
        $doc = $this->createTestDocument();
        $doc->setServerState('published');
        $title = Title::new();
        $title->setLanguage('deu');
        $title->setValue('Deutscher Titel');
        $doc->setTitleMain($title);
        $docId = $doc->store();

        $this->_request->setMethod('POST')->setPost([
            'searchtype' => 'all',
        ]);

        $this->plugin->prepareXml();

        $xml   = $this->plugin->getXml();
        $xpath = new DOMXPath($xml);

        $result = $xpath->query("//Opus_Document[@Id=\"$docId\"]");

        $this->assertEquals(1, $result->length);

        $this->assertEquals(
            'Deutscher Titel',
            $xpath->query("//Opus_Document[@Id=\"$docId\"]/TitleMain/@Value")->item(0)->nodeValue
        );
    }

    public function testXmlPreparationForFrontdoor()
    {
        $doc = $this->createTestDocument();
        $doc->setServerState('published');
        $title = Title::new();
        $title->setLanguage('deu');
        $title->setValue('Deutscher Titel');
        $doc->setTitleMain($title);
        $docId = $doc->store();

        $this->getRequest()->setMethod('POST')->setPost([
            'docId'      => $docId,
            'searchtype' => 'id',
        ]);

        $this->plugin->prepareXml();

        $xpath  = new DOMXPath($this->plugin->getXml());
        $result = $xpath->query('//Opus_Document');
        $count  = $result->length;

        $this->assertEquals('Deutscher Titel', $result->item(--$count)->childNodes->item(3)->attributes->item(2)->nodeValue);
    }

    public function testXmlPreparationForFrontdoorWithWrongDocId()
    {
        $this->getRequest()->setMethod('POST')->setPost([
            'docId'      => 'docId',
            'searchtype' => 'id',
        ]);

        $this->plugin->prepareXml();
        $xpath  = new DOMXPath($this->plugin->getXml());
        $result = $xpath->query('//Opus_Document');
        $this->assertEquals(0, $result->length);
    }

    public function testXmlPreparationForFrontdoorWithMissingDocId()
    {
        $this->getRequest()->setMethod('POST')->setPost([
            'searchtype' => 'id',
        ]);
        $this->plugin->prepareXml();
        $xpath  = new DOMXPath($this->plugin->getXml());
        $result = $xpath->query('//Opus_Document');
        $this->assertEquals(0, $result->length);
    }

    public function testXmlSortOrder()
    {
        $firstDoc = $this->createTestDocument();
        $firstDoc->setPublishedYear(9999);
        $firstDoc->setServerState('published');
        $firstDocId = $firstDoc->store();

        $secondDoc = $this->createTestDocument();
        $secondDoc->setPublishedYear(9998);
        $secondDoc->setServerState('published');
        $secondDocId = $secondDoc->store();

        $forthDoc = $this->createTestDocument();
        $forthDoc->setPublishedYear(9996);
        $forthDoc->setServerState('published');
        $forthDocId = $forthDoc->store();

        $thirdDoc = $this->createTestDocument();
        $thirdDoc->setPublishedYear(9997);
        $thirdDoc->setServerState('published');
        $thirdDocId = $thirdDoc->store();

        // Dokument aus dem Cache löschen
        $documentCache = Repository::getInstance()->getDocumentXmlCache();
        $documentCache->remove($secondDocId);
        $documentCache->remove($firstDocId);

        $this->getRequest()->setMethod('POST')->setPost([
            'searchtype' => 'all',
            'sortfield'  => 'year',
            'sortorder'  => 'desc',
            'rows'       => '10', // die ersten 10 Dokumente reichen
        ]);

        $this->plugin->prepareXml();

        $xpath  = new DOMXPath($this->plugin->getXml());
        $result = $xpath->query('//Opus_Document');

        $this->assertEquals(10, $result->length);

        $this->assertEquals($firstDocId, $result->item(0)->attributes->item(0)->nodeValue);
        $this->assertEquals($secondDocId, $result->item(1)->attributes->item(0)->nodeValue);
        $this->assertEquals($thirdDocId, $result->item(2)->attributes->item(0)->nodeValue);
        $this->assertEquals($forthDocId, $result->item(3)->attributes->item(0)->nodeValue);
    }

    /**
     * If only one document is exported, searchtype 'id' is used. It is not necessary the invoke solr search, because
     * the document can be constructed in XmlExport.
     */
    public function testXmlExportForSearchtypeId()
    {
        parent::setUpWithEnv('production');
        $this->enableSecurity();
        $this->assertSecurityConfigured();

        $doc = $this->createTestDocument();
        $doc->setServerState('published');
        $docId = $doc->store();

        $this->getRequest()->setMethod('POST')->setPost([
            'searchtype' => 'id',
            'docId'      => $docId,
        ]);

        $this->plugin->prepareXml();

        $xpath  = new DOMXPath($this->plugin->getXml());
        $result = $xpath->query('//Opus_Document');

        $this->restoreSecuritySetting();
        $this->assertEquals($docId, $result->item(0)->attributes->item(0)->nodeValue);
    }

    /**
     * Only published documents should be exported.
     */
    public function testXmlExportForSearchtypeIdWithUnpublishedDocument()
    {
        parent::setUpWithEnv('production');
        $this->enableSecurity();
        $this->assertSecurityConfigured();

        $doc   = $this->createTestDocument();
        $docId = $doc->store();

        $this->getRequest()->setMethod('POST')->setPost([
            'searchtype' => 'id',
            'docId'      => $docId,
        ]);

        $this->expectException(Application_Export_Exception::class);
        $this->plugin->prepareXml();

        $this->restoreSecuritySetting();
    }

    public function testGetMaxRows()
    {
        $this->assertEquals(Query::MAX_ROWS, $this->plugin->getMaxRows());

        $this->enableSecurity();

        $this->assertEquals(100, $this->plugin->getMaxRows());

        $this->loginUser('security7', 'security7pwd');

        $this->assertEquals(500, $this->plugin->getMaxRows());

        $this->loginUser('admin', 'adminadmin');

        $this->assertEquals(Query::MAX_ROWS, $this->plugin->getMaxRows());
    }

    public function testGetValueIfValid()
    {
        $this->assertEquals(20, $this->plugin->getValueIfValid(20, 100));
        $this->assertEquals(50, $this->plugin->getValueIfValid(' 50 ', 100));
        $this->assertEquals(100, $this->plugin->getValueIfValid('0', 100));
        $this->assertEquals(100, $this->plugin->getValueIfValid('-1', 100));
        $this->assertEquals(100, $this->plugin->getValueIfValid('a', 100));
        $this->assertEquals(100, $this->plugin->getValueIfValid(-1, 100));
        $this->assertEquals(100, $this->plugin->getValueIfValid(0, 100));
    }

    public function testIsDownloadEnabled()
    {
        $plugin = $this->plugin;

        $this->assertTrue($plugin->isDownloadEnabled());

        $plugin->setDownloadEnabled(false);

        $this->assertFalse($plugin->isDownloadEnabled());

        $plugin->setDownloadEnabled(null);

        $this->adjustConfiguration([
            'export' => ['download' => self::CONFIG_VALUE_FALSE],
        ]);

        $this->assertFalse($plugin->isDownloadEnabled());
    }

    /**
     * @return array
     */
    public function setDownloadEnabledInvalidArgumentProvider()
    {
        return [
            ['on'],
            [123],
            [1],
        ];
    }

    /**
     * @dataProvider setDownloadEnabledInvalidArgumentProvider
     * @param mixed $argument
     */
    public function testSetDownloadEnabledInvalidArgument($argument)
    {
        $this->expectException(InvalidArgumentException::class);
        $this->plugin->setDownloadEnabled($argument);
    }

    public function testGetContentTypeFromConfiguration()
    {
        $plugin = $this->plugin;

        $this->assertEquals('text/xml', $plugin->getContentType());

        $config = new Zend_Config(['contentType' => 'text/plain']);

        $plugin->setContentType(null); // clear cached content type

        $plugin->setConfig($config);

        $this->assertEquals('text/plain', $plugin->getContentType());
    }

    public function testGetContentTypeFallback()
    {
        $plugin = $this->plugin;

        $plugin->setContentType(null);

        $plugin->setConfig(new Zend_Config([]));

        $this->assertEquals('text/xml', $plugin->getContentType());
    }

    public function testSetContentType()
    {
        $plugin = $this->plugin;

        $plugin->setContentType('text/html');

        $this->assertEquals('text/html', $plugin->getContentType());
    }

    public function testGetAttachmentFilename()
    {
        $plugin = $this->plugin;

        $this->assertEquals('export.xml', $plugin->getAttachmentFilename());

        $plugin->setAttachmentFilename(null); // clear cached name

        $plugin->setConfig(new Zend_Config(['attachmentFilename' => 'article.pdf']));

        $this->assertEquals('article.pdf', $plugin->getAttachmentFilename());
    }

    public function testSetAttachmentFilename()
    {
        $plugin = $this->plugin;

        $plugin->setAttachmentFilename('fulltext.pdf');

        $this->assertEquals('fulltext.pdf', $plugin->getAttachmentFilename());
    }

    public function testGetDocumentsFromCache()
    {
        $plugin = $this->plugin;

        $xml = $plugin->getDocumentsFromCache([146, 89, 90]);

        $this->assertCount(3, $xml);
        $this->assertEquals([146, 89, 90], array_keys($xml)); // order is preserved
    }
}
