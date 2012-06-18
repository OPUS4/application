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
 * @author      Sascha Szott <szott@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class Remotecontrol_Model_DownloadListTest extends ControllerTestCase {

    public function setUp() {
        parent::setUp();
        $this->requireSolrConfig();
    }

    public function testGetCsvFileForCollectionNumber() {
        $downloadList = new Remotecontrol_Model_DownloadList();
        $csv = $downloadList->getCvsFile('ddc', '004', null);
        $this->assertRegExp('/^10\D/', $csv);
        $this->assertRegExp('/\n/', $csv);
    }

    public function testGetEmptyCsvFile() {
        $downloadList = new Remotecontrol_Model_DownloadList();
        $csv = $downloadList->getCvsFile('ddc', '621', null);
        $this->assertRegExp('/^$/', $csv);
    }

    /**
     * Regression test for OPUSVIER-2518
     */
    public function testExceptionIfSolrServerIsUnavailable() {
        // manipulate solr configuration
        $config = Zend_Registry::get('Zend_Config');
        $host = $config->searchengine->index->host;
        $port = $config->searchengine->index->port;
        $oldValue = $config->searchengine->index->app;
        $config->searchengine->index->app = 'solr/corethatdoesnotexist';
        Zend_Registry::set('Zend_Config', $config);

        $downloadList = new Remotecontrol_Model_DownloadList();
        $exception = null;
        try {
            $downloadList->getCvsFile('ddc', '000');
        }
        catch (Exception $e) {
            $exception = $e;
        }
        $this->assertType('Remotecontrol_Model_Exception', $e);
        $this->assertFalse($e->collectionIsNotUnique());
        $this->assertTrue($e->getPrevious() instanceof Opus_SolrSearch_Exception);
        $this->assertEquals($e->getPrevious()->getCode(), Opus_SolrSearch_Exception::SERVER_UNREACHABLE);

        // restore configuration
        $config = Zend_Registry::get('Zend_Config');
        $config->searchengine->index->app = $oldValue;
        Zend_Registry::set('Zend_Config', $config);
    }

    /**
     * Regression test for OPUSVIER-2434
     */
    public function testGetListItemsExceptionIfSolrQueryIsInvalid() {
        $this->markTestIncomplete('es scheint nicht möglich zu sein eine "raw fq query" so zu schreiben, dass sie einen Parserfehler auslöst');
        $this->requireSolrConfig();

        $downloadList = new Remotecontrol_Model_DownloadList();

        $class = new ReflectionClass('Remotecontrol_Model_DownloadList');
        $method = $class->getMethod('getListItems');
        $method->setAccessible(true);
        $exception = null;
        try {
            $method->invoke($downloadList, '"\""');
        }
        catch (Exception $e) {
            $exception = $e;
        }

        $this->assertNotNull($exception);
        $this->assertType('Opus_SolrSearch_Exception', $exception);
        $this->assertEquals($exception->getCode(), Opus_SolrSearch_Exception::INVALID_QUERY);
    }

    /**
     * Regression test for OPUSVIER-2518
     */
    public function testGetListItemsExceptionIfSolrServerIsUnreachable() {
        $this->requireSolrConfig();

        // manipulate solr configuration
        $config = Zend_Registry::get('Zend_Config');
        $host = $config->searchengine->index->host;
        $port = $config->searchengine->index->port;
        $oldValue = $config->searchengine->index->app;
        $config->searchengine->index->app = 'solr/corethatdoesnotexist';
        Zend_Registry::set('Zend_Config', $config);

        $downloadList = new Remotecontrol_Model_DownloadList();

        $class = new ReflectionClass('Remotecontrol_Model_DownloadList');
        $method = $class->getMethod('getListItems');
        $method->setAccessible(true);
        $exception = null;
        try {
            $method->invoke($downloadList, '123');
        }
        catch (Exception $e) {
            $exception = $e;
        }
        $this->assertNotNull($exception);
        $this->assertType('Opus_SolrSearch_Exception', $exception);
        $this->assertEquals($exception->getCode(), Opus_SolrSearch_Exception::SERVER_UNREACHABLE);

        // restore configuration
        $config = Zend_Registry::get('Zend_Config');
        $config->searchengine->index->app = $oldValue;
        Zend_Registry::set('Zend_Config', $config);
    }

}
