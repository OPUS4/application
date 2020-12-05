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
 * @copyright   Copyright (c) 2018-2019, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Document;
use Opus\Log;

class Application_Import_ImporterTest extends ControllerTestCase
{

    protected $additionalResources = 'database';

    public function testImportEnrichmentWithoutValue()
    {
        $xml = file_get_contents(APPLICATION_PATH . '/tests/import/test_import_enrichment_without_value.xml');

        $importer = new Application_Import_Importer($xml, false, Log::get());

        $importer->run();

        $document = $importer->getDocument();

        $this->assertNotNull($document);
        $this->assertInstanceOf('Opus\Document', $document);

        $this->assertCount(1, $document->getEnrichment());
        $this->assertEquals('Berlin', $document->getEnrichmentValue('City'));
    }

    /**
     * Bei der Angabe eines EmbargoDate im Import-XML muss eine Tages- und Monatsangabe sowie
     * eine Jahresangabe enthalten sein. Eine alleinige Jahresangabe ist nicht zulässig.
     */
    public function testImportInvalidEmbargoDate()
    {
        $xml = file_get_contents(APPLICATION_PATH . '/tests/resources/import/incomplete-embargo-date.xml');

        $importer = new Application_Import_Importer($xml, false, Log::get());

        $this->setExpectedException(Application_Import_MetadataImportSkippedDocumentsException::class);
        $importer->run();
    }

    public function testValidEmbargoDate()
    {
        $xml = file_get_contents(APPLICATION_PATH . '/tests/resources/import/embargo-date.xml');

        $importer = new Application_Import_Importer($xml, false, Log::get());

        $importer->run();

        $document = $importer->getDocument();

        $this->assertNotNull($document);
        $this->assertInstanceOf('Opus\Document', $document);

        $embargoDate = $document->getEmbargoDate();
        $this->assertEquals(12, $embargoDate->getDay());
        $this->assertEquals(11, $embargoDate->getMonth());
        $this->assertEquals(2042, $embargoDate->getYear());
    }

    public function testFromArray()
    {
        $doc = Document::get(146);

        // var_dump($doc->toArray());
    }
}
