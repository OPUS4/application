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
 * @copyright   Copyright (c) 2014, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\Document;
use Opus\Common\Identifier;

class Oai_Model_DocumentListTest extends ControllerTestCase
{
    /** @var string */
    protected $additionalResources = 'database';

    /**
     * Testet, ob beim MetaDataPrefix "epicur" nur Dokumente mit URN ausgegeben werden.
     */
    public function testDocumentOutputUrn()
    {
        $docWithUrn = $this->createTestDocument();
        $docWithUrn->setServerState('published');
        $identifier = Identifier::new();
        $identifier->setValue('urn_value1');
        $identifier->setType('urn');
        $docWithUrn->addIdentifier($identifier);
        $docWithUrnId = $docWithUrn->store();

        $docWoUrn = $this->createTestDocument();
        $docWoUrn->setServerState('published');
        $docWoUrnId = $docWoUrn->store();

        $oaiRequest                             = ['metadataPrefix' => 'epicur'];
        $docListModel                           = new Oai_Model_DocumentList();
        $docListModel->deliveringDocumentStates = ['published'];
        $docIds                                 = $docListModel->query($oaiRequest);

        $this->assertTrue(in_array($docWithUrnId, $docIds), 'Document with URN is not returned.');
        $this->assertFalse(in_array($docWoUrnId, $docIds), 'Document without URN is returned.');
    }

    /**
     * Test list document ids, metadataPrefix=XMetaDissPlus, different intervals
     * list possible intervals containing "2010-06-05"
     */
    public function testIntervalOAIPMHQueries()
    {
        $doc = $this->createTestDocument();
        $doc->setServerState('published');
        $file = $this->createOpusTestFile('article.txt');
        $file->setVisibleInOai(1);
        $doc->addFile($file);
        $this->docId = $doc->store();

        $doc                = Document::get($this->docId);
        $serverDateModified = $doc->getServerDateModified();

        $today = new DateTime();
        $today->setDate(
            $serverDateModified->getYear(),
            $serverDateModified->getMonth(),
            $serverDateModified->getDay()
        );

        $yesterday = clone $today;
        $yesterday->modify('-1 day');

        $tomorrow = clone $today;
        $tomorrow->modify('+1 day');

        $todayStr     = date_format($today, 'Y-m-d');
        $yesterdayStr = date_format($yesterday, 'Y-m-d');
        $tomorrowStr  = date_format($tomorrow, 'Y-m-d');

        $intervals = [
            [],
            ['from' => $todayStr],
            ['until' => $todayStr],
            ['from' => $yesterdayStr],
            ['until' => $tomorrowStr],
            ['from' => $todayStr, 'until' => $todayStr],
            ['from' => $yesterdayStr, 'until' => $todayStr],
            ['from' => $todayStr, 'until' => $tomorrowStr],
            ['from' => $yesterdayStr, 'until' => $tomorrowStr],
        ];

        foreach ($intervals as $interval) {
            $oaiRequest = ['verb' => 'ListRecords', 'metadataPrefix' => 'XMetaDissPlus'];
            $oaiRequest = array_merge($interval, $oaiRequest);

            $docListModel                           = new Oai_Model_DocumentList();
            $docListModel->deliveringDocumentStates = ['published', 'deleted'];
            $docListModel->xMetaDissRestriction     = [];
            $docIds                                 = $docListModel->query($oaiRequest);

            $this->assertTrue(
                in_array($this->docId, $docIds),
                "Response must contain document id $this->docId: " . var_export($interval, true)
            );
        }
    }

    /**
     * Test list document ids, metadataPrefix=XMetaDissPlus, different intervals
     * list possible intervals *NOT* containing "2010-06-05"
     */
    public function testIntervalOAIPMHQueryWithoutTestDoc()
    {
        $doc = $this->createTestDocument();
        $doc->setServerState('published');
        $this->docId = $doc->store();

        $doc                = Document::get($this->docId);
        $serverDateModified = $doc->getServerDateModified();

        $today = new DateTime();
        $today->setDate(
            $serverDateModified->getYear(),
            $serverDateModified->getMonth(),
            $serverDateModified->getDay()
        );

        $yesterday = clone $today;
        $yesterday->modify('-1 day');

        $dayBeforeYesterday = clone $yesterday;
        $dayBeforeYesterday->modify('-1 day');

        $tomorrow = clone $today;
        $tomorrow->modify('+1 day');

        $dayAfterTomorrow = clone $tomorrow;
        $dayAfterTomorrow->modify('+1 day');

        $yesterdayStr          = date_format($yesterday, 'Y-m-d');
        $dayBeforeYesterdayStr = date_format($dayBeforeYesterday, 'Y-m-d');
        $tomorrowStr           = date_format($tomorrow, 'Y-m-d');
        $dayAfterTomorrowStr   = date_format($dayAfterTomorrow, 'Y-m-d');

        $intervals = [
            ['from' => $tomorrowStr],
            ['until' => $yesterdayStr],
            ['from' => $tomorrowStr, 'until' => $dayAfterTomorrowStr],
            ['from' => $dayBeforeYesterdayStr, 'until' => $yesterdayStr],
        ];

        foreach ($intervals as $interval) {
            $oaiRequest = ['verb' => 'ListRecords', 'metadataPrefix' => 'XMetaDissPlus'];
            $oaiRequest = array_merge($interval, $oaiRequest);

            $docListModel                           = new Oai_Model_DocumentList();
            $docListModel->deliveringDocumentStates = ['published', 'deleted'];
            $docListModel->xMetaDissRestriction     = [];
            $docIds                                 = $docListModel->query($oaiRequest);

            $this->assertFalse(in_array($this->docId, $docIds), "Response must NOT contain document id $this->docId: " . var_export($interval, true));
        }
    }

    public function testOnlyFilesVisibleInOaiIncludedForXMetaDissPlus()
    {
        $doc = $this->createTestDocument();
        $doc->setServerState('published');
        $file = $this->createOpusTestFile('article.txt');
        $file->setVisibleInOai(1);
        $doc->addFile($file);
        $docIdIncluded = $doc->store();

        $doc = $this->createTestDocument();
        $doc->setServerState('published');
        $file = $this->createOpusTestFile('fulltext.txt');
        $file->setVisibleInOai(0);
        $doc->addFile($file);
        $docIdNotIncluded = $doc->store();

        $oaiRequest = ['verb' => 'ListRecords', 'metadataPrefix' => 'XMetaDissPlus'];

        $docListModel                           = new Oai_Model_DocumentList();
        $docListModel->deliveringDocumentStates = ['published', 'deleted'];
        $docListModel->xMetaDissRestriction     = [];
        $docIds                                 = $docListModel->query($oaiRequest);

        $this->assertTrue(
            in_array($docIdIncluded, $docIds),
            "Response must contain document id $docIdIncluded"
        );

        $this->assertFalse(
            in_array($docIdNotIncluded, $docIds),
            "Response must not contain document id $docIdIncluded"
        );
        // test "xMetaDissPlus"

        $oaiRequest = ['verb' => 'ListRecords', 'metadataPrefix' => 'xMetaDissPlus'];

        $docListModel                           = new Oai_Model_DocumentList();
        $docListModel->deliveringDocumentStates = ['published', 'deleted'];
        $docListModel->xMetaDissRestriction     = [];
        $docIds                                 = $docListModel->query($oaiRequest);

        $this->assertTrue(
            in_array($docIdIncluded, $docIds),
            "Response must contain document id $docIdIncluded"
        );

        $this->assertFalse(
            in_array($docIdNotIncluded, $docIds),
            "Response must not contain document id $docIdIncluded"
        );
    }
}
