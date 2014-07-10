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
 * @package     Module_Export
 * @author      Michael Lang <lang@zib.de>
 * @copyright   Copyright (c) 2008-2014, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */


class Export_Model_XmlExportTest extends ControllerTestCase {

    public function testXmlPreparation() {
        $doc = $this->createTestDocument();
        $doc->setServerState('published');
        $title = new Opus_Title();
        $title->setLanguage('deu');
        $title->setValue('Deutscher Titel');
        $doc->setTitleMain($title);
        $doc->store();

        $xml = new DomDocument;
        $proc = new XSLTProcessor;
        $this->_request->setMethod('POST')->setPost(array(
            'searchtype' => 'all'
        ));

        $xmlExportModel = new Export_Model_XmlExport();
        $xmlExportModel->prepareXml($xml, $proc, $this->_request);

        $xpath = new DOMXPath($xml);
        $result = $xpath->query('//Opus_Document');

        // in OPUSVIER-3336 wurde die Sortierreihenfolge geändert, dh es wird nicht mehr aufsteigend nach id sortiert
        $this->assertEquals('Deutscher Titel', $result->item(0)->childNodes->item(3)->attributes->item(2)->nodeValue);
    }

    public function testXmlPreparationForFrontdoor() {
        $doc = $this->createTestDocument();
        $doc->setServerState('published');
        $title = new Opus_Title();
        $title->setLanguage('deu');
        $title->setValue('Deutscher Titel');
        $doc->setTitleMain($title);
        $docId = $doc->store();

        $xml = new DomDocument;
        $proc = new XSLTProcessor;
        $this->getRequest()->setMethod('POST')->setPost(array(
            'docId' => $docId,
            'searchtype' => 'all'
        ));
        $xmlExportModel = new Export_Model_XmlExport();
        $xmlExportModel->prepareXmlForFrontdoor($xml, $proc, $this->getRequest());

        $xpath = new DOMXPath($xml);
        $result = $xpath->query('//Opus_Document');
        $count = $result->length;

        $this->assertEquals('Deutscher Titel', $result->item(--$count)->childNodes->item(3)->attributes->item(2)->nodeValue);
    }

    public function testXmlPreparationForFrontdoorWithWrongId() {
        $docId = 199293;
        $xml = new DomDocument;
        $proc = new XSLTProcessor;

        $this->getRequest()->setMethod('POST')->setPost(array(
            'docId' => ++$docId,
            'searchtype' => 'all'
        ));
        $xmlExportModel = new Export_Model_XmlExport();

        $this->setExpectedException('Application_Exception');
        $xmlExportModel->prepareXmlForFrontdoor($xml, $proc, $this->getRequest());
    }

    public function testXmlPreparationForFrontdoorWithoutId() {
        $xml = new DomDocument;
        $proc = new XSLTProcessor;

        $this->getRequest()->setMethod('POST')->setPost(array(
            'searchtype' => 'all'
        ));
        $xmlExportModel = new Export_Model_XmlExport();

        $this->setExpectedException('Application_Exception');
        $xmlExportModel->prepareXmlForFrontdoor($xml, $proc, $this->getRequest());
    }

    public function testXmlSortOrder() {
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

        $xml = new DomDocument;
        $proc = new XSLTProcessor;
        $this->getRequest()->setMethod('POST')->setPost(array(
            'searchtype' => 'all',
            'sortfield' => 'year',
            'sortorder' => 'desc',
            'rows' => '10' // die ersten 10 Dokumente reichen
        ));
        $xmlExportModel = new Export_Model_XmlExport();
        $xmlExportModel->prepareXml($xml, $proc, $this->getRequest());

        $xpath = new DOMXPath($xml);
        $result = $xpath->query('//Opus_Document');

        $this->assertEquals($firstDocId, $result->item(0)->attributes->item(0)->nodeValue);
        $this->assertEquals($secondDocId, $result->item(1)->attributes->item(0)->nodeValue);
        $this->assertEquals($thirdDocId, $result->item(2)->attributes->item(0)->nodeValue);
        $this->assertEquals($forthDocId, $result->item(3)->attributes->item(0)->nodeValue);
    }

}
 