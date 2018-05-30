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
 * @category    Application Unit Test
 * @package     Admin_Model
 * @author      Sascha Szott <szott@zib.de>
 * @copyright   Copyright (c) 2018, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */
class Admin_Model_DoiReportTest extends ControllerTestCase {

    /**
     * @var array
     */
    private $docIds;

    private $config;

    public function tearDown() {
        // restore config
        Zend_Registry::set('Zend_Config', $this->config);

        if (!is_null($this->docIds)) {
            // removed previously created test documents from database
            foreach ($this->docIds as $docId) {
                $doc = new Opus_Document($docId);
                $doc->deletePermanent();
            }
        }
        parent::tearDown();
    }

    public function setUp() {
        parent::setUp();

        // backup config
        $this->config = Zend_Registry::get('Zend_Config');

        $config = Zend_Registry::get('Zend_Config');
        $config->merge(new Zend_Config(array(
            'doi' => array(
                'prefix' => '10.5072',
                'localPrefix' => 'opustest'
            )
        )));
        Zend_Registry::set('Zend_Config', $config);

        $this->docIds = array();

        // create some test documents with DOIs: do NOT change order of creations
        $this->createTestDocWithDoi('unpublished', null);
        $this->createTestDocWithDoi('published', null);
        $this->createTestDocWithDoi('published', 'registered');
        $this->createTestDocWithDoi('published', 'verified');
        $this->createTestDocWithDoi('published', null, false);
    }

    public function testGetDocList() {
        $doiReport = new Admin_Model_DoiReport(null);
        $docList = $doiReport->getDocList();
        $this->assertCount(4, $docList);
    }

    public function testGetDocListWithPublishedFilter() {
        $doiReport = new Admin_Model_DoiReport('registered');
        $docList = $doiReport->getDocList();
        $this->assertCount(1, $docList);
        $doiStatus = $docList[0];
        $docId = $this->docIds[2];
        $this->assertEquals($docId, $doiStatus->getDocId());
        $this->assertTrue($doiStatus->isPublished());
        $this->assertEquals('10.5072/opustest-' . $docId, $doiStatus->getDoi());
        $this->assertEquals('registered', $doiStatus->getDoiStatus());
    }

    public function testGetDocListWithUnpublishedFilter() {
        $doiReport = new Admin_Model_DoiReport('verified');
        $docList = $doiReport->getDocList();
        $this->assertCount(1, $docList);
        $doiStatus = $docList[0];
        $docId = $this->docIds[3];
        $this->assertEquals($docId, $doiStatus->getDocId());
        $this->assertTrue($doiStatus->isPublished());
        $this->assertEquals('10.5072/opustest-' . $docId, $doiStatus->getDoi());
        $this->assertEquals('verified', $doiStatus->getDoiStatus());
    }

    public function testGetNumDoisForBulkRegistration() {
        $doiReport = new Admin_Model_DoiReport(null);
        $num = $doiReport->getNumDoisForBulkRegistration();
        $this->assertEquals(1, $num);
    }

    public function testGetNumDoisForBulkVerification() {
        $doiReport = new Admin_Model_DoiReport(null);
        $num = $doiReport->getNumDoisForBulkVerification();
        $this->assertEquals(1, $num);
    }

    private function createTestDocWithDoi($serverState, $doiStatus, $local = true) {
        $doc = new Opus_Document();
        $doc->setServerState($serverState);
        $docId = $doc->store();
        $this->docIds[] = $docId;

        $doi = new Opus_Identifier();
        $doi->setType('doi');
        if ($local) {
            $doi->setValue('10.5072/opustest-' . $docId);
        }
        else {
            $doi->setValue('10.5072/anothersystem-' . $docId);
        }
        $doi->setStatus($doiStatus);
        $doc->setIdentifier(array($doi));

        $doc->store();
    }
}
