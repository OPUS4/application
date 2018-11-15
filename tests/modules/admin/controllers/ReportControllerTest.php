<?php
/*
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
 * @category    Unit Tests
 * @author      Sascha Szott <szott@zib.de>
 * @author      Maximilian Salomon <salomon@zib.de>
 * @copyright   Copyright (c) 2018, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Unit tests for Admin_ReportController
 *
 * @coversDefaultClass Admin_ReportController
 */
class Admin_ReportControllerTest extends ControllerTestCase {

    private $config;

    private $docIds;

    public function setUp() {
        parent::setUp();

        // backup config
        $this->config = Zend_Registry::get('Zend_Config');

        // modify DOI config
        $config = Zend_Registry::get('Zend_Config');
        $config->merge(new Zend_Config(array(
            'doi' => array(
                'prefix' => '10.5072',
                'localPrefix' => 'opustest',
                'registration' =>
                    array(
                        'datacite' =>
                            array(
                                'username' => 'test',
                                'password' => 'secret',
                                'serviceUrl' => 'http://192.0.2.1:54321'
                            )
                    )
            )
        )));
        Zend_Registry::set('Zend_Config', $config);
    }

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

    public function testDoiActionWithEmptyResult() {
        $this->useEnglish();
        $this->dispatch('/admin/report/doi');
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('report');
        $this->assertAction('doi');

        $this->assertQueryContentContains('//div["wrapper"]/div/i', 'Could not find matching local DOIs.');
    }

    public function testDoiActionWithNonEmptyResult() {
        $this->createTestDocs();

        $this->dispatch('/admin/report/doi');
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('report');
        $this->assertAction('doi');

        $this->assertNotQueryContentContains('//div["wrapper"]/div/i', 'Could not find matching local DOIs.');
        $this->assertXpath('//div["wrapper"]/table/tbody');
        $this->assertXpathCount('//div["wrapper"]/table/tbody/tr', 4);
    }

    public function testDoiActionWithNonEmptyResultAndUnregisteredFilter() {
        $this->createTestDocs();

        $this->dispatch('/admin/report/doi/filter/unregistered');
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('report');
        $this->assertAction('doi');

        $this->assertNotQueryContentContains('//div["wrapper"]/div/i', 'Could not find matching local DOIs.');
        $this->assertXpath('//div["wrapper"]/table/tbody');
        $this->assertXpathCount('//div["wrapper"]/table/tbody/tr', 2);
    }

    public function testDoiActionWithNonEmptyResultAndRegisteredFilter() {
        $this->createTestDocs();

        $this->dispatch('/admin/report/doi/filter/registered');
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('report');
        $this->assertAction('doi');

        $this->assertNotQueryContentContains('//div["wrapper"]/div/i', 'Could not find matching local DOIs.');
        $this->assertXpath('//div["wrapper"]/table/tbody');
        $this->assertXpathCount('//div["wrapper"]/table/tbody/tr', 1);
    }

    public function testDoiActionWithNonEmptyResultAndVerifiedFilter() {
        $this->createTestDocs();

        $this->dispatch('/admin/report/doi/filter/verified');
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('report');
        $this->assertAction('doi');

        $this->assertNotQueryContentContains('//div["wrapper"]/div/i', 'Could not find matching local DOIs.');
        $this->assertXpath('//div["wrapper"]/table/tbody');
        $this->assertXpathCount('//div["wrapper"]/table/tbody/tr', 1);
    }

    public function testRegisterSingle() {
        $this->createTestDocs();
        $docId = $this->docIds[1];

        $this->request->setMethod('POST')
            ->setPost(array(
                'op' => 'register',
                'docId' => $docId
            ));
        $this->dispatch('/admin/report/doi');
        $this->assertResponseCode(302);
        $this->assertRedirectTo('/admin/report/doi');

        // erfolgreiche Registrierung der DOI kann hier nicht geprüft werden: dazu Aufruf des DataCite-Service erforderlich
    }

    public function testVerifySingle() {
        $this->createTestDocs();
        $docId = $this->docIds[2];

        $this->request->setMethod('POST')
            ->setPost(array(
                'op' => 'verify',
                'docId' => $docId
            ));
        $this->dispatch('/admin/report/doi');
        $this->assertResponseCode(302);
        $this->assertRedirectTo('/admin/report/doi');

        // erfolgreiche Prüfung der DOI kann hier nicht geprüft werden: dazu Aufruf des DataCite-Service erforderlich
    }

    public function testReverifySingle() {
        $this->createTestDocs();
        $docId = $this->docIds[3];

        $this->request->setMethod('POST')
            ->setPost(array(
                'op' => 'verify',
                'docId' => $docId
            ));
        $this->dispatch('/admin/report/doi');
        $this->assertResponseCode(302);
        $this->assertRedirectTo('/admin/report/doi');

        // erfolgreiche (erneute) Prüfung der DOI kann hier nicht geprüft werden: dazu Aufruf des DataCite-Service erforderlich
    }

    public function testRegisterBulk() {
        $this->createTestDocs();

        $this->request->setMethod('POST')
            ->setPost(array(
                'op' => 'register'
            ));
        $this->dispatch('/admin/report/doi');
        $this->assertResponseCode(302);
        $this->assertRedirectTo('/admin/report/doi');

        // erfolgreiche Registrierung der DOIs kann hier nicht geprüft werden: dazu Aufruf des DataCite-Service erforderlich
    }

    public function testVerifyBulk() {
        $this->createTestDocs();

        $this->request->setMethod('POST')
            ->setPost(array(
                'op' => 'verify'
            ));
        $this->dispatch('/admin/report/doi');
        $this->assertResponseCode(302);
        $this->assertRedirectTo('/admin/report/doi');

        // erfolgreiche Prüfung der DOIs kann hier nicht geprüft werden: dazu Aufruf des DataCite-Service erforderlich
    }

    /**
     * create some test documents with DOIs: do NOT change order of creations
     */
    private function createTestDocs() {
        $this->docIds = array();

        $this->createTestDocWithDoi('unpublished', null);
        $this->createTestDocWithDoi('published', null);
        $this->createTestDocWithDoi('published', 'registered');
        $this->createTestDocWithDoi('published', 'verified');
        $this->createTestDocWithDoi('published', null, false);
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
