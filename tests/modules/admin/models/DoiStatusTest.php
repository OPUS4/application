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
class Admin_Model_DoiStatusTest extends ControllerTestCase {

    private $docId;

    protected function tearDown() {
        if (!is_null($this->docId)) {
            // removed previously created test document from database
            $doc = new Opus_Document($this->docId);
            $doc->deletePermanent();
        }
        parent::tearDown();
    }

    public function testWithPublishedDoc() {
        $this->createTestDocWithDoi('published');
        $doc = new Opus_Document($this->docId);
        $identifiers = $doc->getIdentifier();
        $doi = $identifiers[0];

        $doiStatus = new Admin_Model_DoiStatus($doc, $doi);

        $this->assertEquals($this->docId, $doiStatus->getDocId());
        $this->assertTrue($doiStatus->isPublished());
        $this->assertEquals($doi->getValue(), $doiStatus->getDoi());
        $this->assertEquals($doi->getStatus(), $doiStatus->getDoiStatus());
    }

    public function testWithUnpublishedDoc() {
        $this->createTestDocWithDoi('unpublished');
        $doc = new Opus_Document($this->docId);
        $identifiers = $doc->getIdentifier();
        $doi = $identifiers[0];

        $doiStatus = new Admin_Model_DoiStatus($doc, $doi);

        $this->assertEquals($this->docId, $doiStatus->getDocId());
        $this->assertFalse($doiStatus->isPublished());
        $this->assertEquals($doi->getValue(), $doiStatus->getDoi());
        $this->assertEquals($doi->getStatus(), $doiStatus->getDoiStatus());
    }

    private function createTestDocWithDoi($serverState) {
        $doc = new Opus_Document();
        $doc->setServerState($serverState);
        $this->docId = $doc->store();

        $doi = new Opus_Identifier();
        $doi->setType('doi');
        $doi->setValue('10.5027/opustest-' . $this->docId);
        $doi->setStatus('registered');
        $doc->setIdentifier(array($doi));

        $doc->store();
    }
}
