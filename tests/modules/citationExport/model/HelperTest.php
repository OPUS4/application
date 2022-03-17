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
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2019, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\DnbInstitute;
use Opus\Document;

class CitationExport_Model_HelperTest extends ControllerTestCase
{

    protected $additionalResources = ['database'];

    private $_documentId;

    private $_helper;

    public function setUp()
    {
        parent::setUp();

        $document = $this->createTestDocument();
        $document->setServerState('published');

        $this->_documentId = $document->store();
        $this->assertNotNull($this->_documentId);

        $this->_helper = new CitationExport_Model_Helper(
            '/testpath',
            APPLICATION_PATH . '/modules/citationExport/views/scripts/index'
        );
    }

    public function testGetScriptPath()
    {
        $this->assertEquals(
            APPLICATION_PATH . '/modules/citationExport/views/scripts/index',
            $this->_helper->getScriptPath()
        );
    }

    public function testGetAvailableStylesheets()
    {
        $stylesheets = $this->_helper->getAvailableStylesheets();

        $this->assertContains('ris', $stylesheets);
        $this->assertContains('bibtex', $stylesheets);

        $this->assertNotContains('index.phtml', $stylesheets);
        $this->assertNotContains('download.phtml', $stylesheets);
    }

    public function testBibtexAttributeSchoolForMasterThesis()
    {
        $document = Document::get($this->_documentId);

        $document->setType('masterthesis');
        $institute = new DnbInstitute(4);
        $document->addThesisPublisher($institute);

        $document->store();

        $request = $this->getRequest();

        $request->setParam('docId', $this->_documentId);
        $request->setParam('output', 'bibtex');

        $output = $this->_helper->getOutput($request);

        $this->assertContains('school      = {School of Life},', $output);
    }

    public function testBibtexAttributeSchoolWithDepartment()
    {
        $document = Document::get($this->_documentId);

        $document->setType('masterthesis');

        $institute = new DnbInstitute();
        $institute->setName('Test Uni');
        $institute->setDepartment('Test Dep');
        $institute->setIsPublisher(true);
        $institute->setCity('Berlin');
        $institute->store();

        $document->addThesisPublisher($institute);

        $document->store();

        $request = $this->getRequest();

        $request->setParam('docId', $this->_documentId);
        $request->setParam('output', 'bibtex');

        $output = $this->_helper->getOutput($request);

        $institute->delete();

        $this->assertContains('school      = {Test Uni, Test Dep},', $output);
    }

    public function testBibtexAttributeSchoolForDoctoralThesis()
    {
        $document = Document::get($this->_documentId);

        $document->setType('doctoralthesis');
        $institute = new DnbInstitute(4);
        $document->addThesisPublisher($institute);

        $document->store();

        $request = $this->getRequest();

        $request->setParam('docId', $this->_documentId);
        $request->setParam('output', 'bibtex');

        $output = $this->_helper->getOutput($request);

        $this->assertContains('school      = {School of Life},', $output);
    }

    public function testGetExtension()
    {
        $this->assertEquals('bib', $this->_helper->getExtension('bibtex'));
        $this->assertEquals('ris', $this->_helper->getExtension('ris'));
        $this->assertEquals('txt', $this->_helper->getExtension('unknown'));
        $this->assertEquals('txt', $this->_helper->getExtension(null));
        $this->assertEquals('txt', $this->_helper->getExtension(''));
    }

    public function testGetTemplateForDocument()
    {
        $document = Document::get($this->_documentId);

        $document->setType('masterthesis');
        $document->store();

        $this->assertEquals('bibtex_masterthesis.xslt', $this->_helper->getTemplateForDocument($document, 'bibtex'));
        $this->assertEquals('ris.xslt', $this->_helper->getTemplateForDocument($document, 'ris'));

        $document->setType('lecture');
        $document->store();

        $this->assertEquals('bibtex.xslt', $this->_helper->getTemplateForDocument($document, 'bibtex'));
    }

    /**
     * @expectedException CitationExport_Model_Exception
     * @expectedExceptionMessage invalid_format
     */
    public function testGetTemplateForDocumentInvalidFormat()
    {
        $document = Document::get($this->_documentId);

        $document->setType('masterthesis');
        $document->store();

        $this->assertEquals('bibtex_masterthesis.xslt', $this->_helper->getTemplateForDocument($document, 'plain'));
    }

    /**
     * @expectedException CitationExport_Model_Exception
     * @expectedExceptionMessage invalid_docid
     */
    public function testGetDocumentMissingDocId()
    {
        $this->_helper->getDocument($this->getRequest());
    }

    /**
     * @expectedException CitationExport_Model_Exception
     * @expectedExceptionMessage invalid_docid
     */
    public function testGetDocumentInvalidDocId()
    {
        $request = $this->getRequest();
        $request->setParam('docId', '9999');
        $this->_helper->getDocument($request);
    }

    public function testGetDocument()
    {
        $request = $this->getRequest();
        $request->setParam('docId', '146');
        $document = $this->_helper->getDocument($request);
        $this->assertNotNull($document);
        $this->assertEquals(146, $document->getId());
    }

    /**
     * Check if non-admin user has access to unpublished documents.
     * @expectedException Application_Exception
     * @expectedExceptionMessage not allowed
     */
    public function testGetDocumentUnpublished()
    {
        $this->enableSecurity();
        $this->loginUser('security7', 'security7pwd');

        $document = Document::get($this->_documentId);
        $document->setServerState('unpublished');
        $document->store();

        $request = $this->getRequest();
        $request->setParam('docId', $this->_documentId);
        $document = $this->_helper->getDocument($request);
        $this->assertNotNull($document);
        $this->assertEquals($this->_documentId, $document->getId());
    }

    public function testGetPlainOutputRis()
    {
        $document = Document::get(146);

        $output = $this->_helper->getPlainOutput($document, 'ris.xslt');

        $this->assertContains('T1  - KOBV', $output);
        $this->assertContains('T1  - COLN', $output);
        $this->assertContains('T2  - Parent Title', $output);
    }
}
