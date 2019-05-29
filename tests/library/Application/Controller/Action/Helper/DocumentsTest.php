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
 * @category    Application Unit Tests
 * @package     Application_Controller_Action_Helper
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2017, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

class Application_Controller_Action_Helper_DocumentsTest extends ControllerTestCase {

    private $documents;

    public function setUp() {
        parent::setUp();

        $this->documents = Zend_Controller_Action_HelperBroker::getStaticHelper('Documents');
    }

    public function testGetDocumentForIdForValidId() {
        $docId = 1;

        $document = $this->documents->getDocumentForId($docId);

        $this->assertNotNull($document);
        $this->assertInstanceOf('Opus_Document', $document);
    }

    public function testGetDocumentForIdForEmptyValue() {
        $docId = null;

        $this->assertNull($this->documents->getDocumentForId($docId));
    }

    public function testGetDocumentForIdForMalformedValue() {
        $docId = '<h1>123</h1>';

        $this->assertNull($this->documents->getDocumentForId($docId));
    }

    public function testGetDocumentForIdForNotExistingValue() {
        $docId = 3000;

        $this->assertNull($this->documents->getDocumentForId($docId));
    }

    public function testGetDocumentForIdForNegativeValue() {
        $docId = -1;

        $this->assertNull($this->documents->getDocumentForId($docId));
    }

    public function testGetSortedDocumentIds()
    {
        $documents = $this->documents->getSortedDocumentIds();

        $this->assertNotNull($documents);
        $this->assertInternalType('array', $documents);

        $lastId = 0;

        foreach ($documents as $value) {
            $this->assertTrue(ctype_digit($value));
            $this->assertGreaterThan($lastId, $value); // check ascending order
            $lastId = $value;
        }
    }

    public function testGetSortedDocumentIdsDescending()
    {
        $documents = $this->documents->getSortedDocumentIds(null, false);

        $this->assertNotNull($documents);
        $this->assertInternalType('array', $documents);

        $lastId = max($documents) + 1; // start with something greater than the greatest ID

        foreach ($documents as $value) {
            $this->assertTrue(ctype_digit($value));
            $this->assertLessThan($lastId, $value); // check descending order
            $lastId = $value;
        }
    }

    public function stateProvider()
    {
        return array(
            'published' => ['published'],
            'restricted' => ['restricted'],
            'unpublished' => ['unpublished'],
            'deleted' => ['deleted'],
            'inprogress' => ['inprogress'],
            'audited' => ['audited']
        );
    }

    /**
     * @param $state
     * @dataProvider stateProvider
     */
    public function testGetSortedDocumentIdsForState($state)
    {
        $documents = $this->documents->getSortedDocumentIds(null, null, $state);

        $this->assertNotNull($documents);
        $this->assertInternalType('array', $documents);
        // $this->assertGreaterThan(0, count($documents));

        if (count($documents) > 0)
        {
            foreach ($documents as $docId)
            {
                $document = new Opus_Document($docId);
                $this->assertEquals($state, $document->getServerState());
            }
        }
    }

}
