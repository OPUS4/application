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
 * @category    TODO
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Unit tests for document types helper.
 *
 * The unit test depend on the available document types in the doctypes folder
 * as well as the configuration in the file tests.ini. 
 */
class Controller_Helper_DocumentTypesTest extends ControllerTestCase {

    /**
     * @var Controller_Helper_DocumentTypes Instance of DocumentTypes helper for testing.
     */
    private $docTypeHelper;

    /**
     * Setup tests.
     */
    public function setUp() {
        parent::setUp();

        $this->docTypeHelper =
                Zend_Controller_Action_HelperBroker::getStaticHelper('DocumentTypes');
    }

    /**
     * Tests getting document types.
     *
     * The available document types are configured in *tests.ini*.
     */
    public function testGetDocumentTypes() {
        $documentTypes = $this->docTypeHelper->getDocumentTypes();

        $this->assertNotNull($documentTypes);
        $this->assertEquals(7, count($documentTypes));
        $this->assertArrayHasKey('all', $documentTypes);
        $this->assertArrayHasKey('preprint', $documentTypes);
        $this->assertArrayHasKey('demo_invalid', $documentTypes);
        $this->assertArrayHasKey('demo', $documentTypes);
        $this->assertArrayHasKey('foobar', $documentTypes);
        $this->assertArrayHasKey('barbaz', $documentTypes);
        $this->assertArrayHasKey('bazbar', $documentTypes);
        $this->assertArrayNotHasKey('article', $documentTypes);
    }

    /**
     * Test getting standard template name for document type.
     */
    public function testGetTemplateName() {
        $template = $this->docTypeHelper->getTemplateName('preprint');

        $this->assertNotNull($template);
        $this->assertEquals('preprint', $template);
    }

    /**
     * Test getting custom template name for document type.
     *
     * The custom template name is configured in *tests.ini*.
     */
    public function testGetCustomTemplateName() {
        $template = $this->docTypeHelper->getTemplateName('foobar');

        $this->assertNotNull($template);
        $this->assertEquals('barfoo', $template);
    }

    /**
     * Test checking validity of allowed document type.
     */
    public function testIsValid() {
        $this->assertTrue($this->docTypeHelper->isValid('preprint'));
    }

    /**
     * Test checking validity of excluded document type.
     */
    public function testIsNotValid() {
        $this->assertFalse($this->docTypeHelper->isValid('article'));
    }

    /**
     * Test getting DOM for document type.
     */
    public function testGetDocument() {
        $dom = $this->docTypeHelper->getDocument('preprint');

        $this->assertNotNull($dom);
    }

    public function testGetDocumentThrowsException() {
        $this->setExpectedException('Application_Exception');
        $this->docTypeHelper->getDocument('article');
    }

    public function testGetDocumentThrowsSchemaInvalidException() {
        $this->setExpectedException('Application_Exception');
        $this->docTypeHelper->getDocument('demo_invalid');
    }

    /**
     * Testing helper without any configuration.
     */
    public function testGetAllDocumentTypes() {
        $config = Zend_Registry::get('Zend_Config');

        unset($config->documentTypes);

        $documentTypes = $this->docTypeHelper->getDocumentTypes();

        $this->assertNotNull($documentTypes);
        $this->assertArrayHasKey('article', $documentTypes);

        // TODO: restore config key "documentTypes"
    }

    /**
     * Test getting document types twice.
     */

    public function testGetDocumentTypesTwice() {
        $documentTypes = $this->docTypeHelper->getDocumentTypes();

        $documentTypes2 = $this->docTypeHelper->direct(); // test direct method

        $this->assertEquals($documentTypes, $documentTypes2);
    }

    /**
     * Test getting template name for unknown document type.
     */
    public function testGetTemplateForInvalidDocumentType() {
        $template = $this->docTypeHelper->getTemplateName('unknownDocType');

        $this->assertNull($template);
    }

    /**
     * Test getting path for document types with path not set.
     *
     * @expectedException Application_Exception
     */
    public function testGetDocumentTypesWithPathNotSet() {
        $config = Zend_Registry::get('Zend_Config');

        unset($config->publish->path->documenttypes);

        $path = $this->docTypeHelper->getDocTypesPath();

        // TODO: missing assertion(s)
        // TODO: restore config key "publish.path.documenttypes"
    }

    /**
     * Check if all document types can be translated.
     */
    public function testTranslationOfDocumentTypes() {
        $excludeFromTranslationCheck = array('demo_invalid', 'foobar', 'barbaz', 'bazbar');
        $translate = Zend_Registry::get('Zend_Translate');

        $documentTypes = $this->docTypeHelper->getDocumentTypes();

        foreach ($documentTypes as $docType) {
            if (!in_array($docType, $excludeFromTranslationCheck)) {
                $this->assertNotEquals($docType, $translate->translate($docType), 'Could not translate document type: ' . $docType);
            }
        }
    }

    /**
     * Regression test for OPUSVIER-2168
     */
    public function testValidateAllXMLDocumentTypeDefinitions() {
        $iterator = new DirectoryIterator($this->docTypeHelper->getDocTypesPath());
        
        // Enable user error handling while validating input file
        libxml_clear_errors();
        libxml_use_internal_errors(true);
        
        foreach ($iterator as $fileinfo) {
            if ($fileinfo->isFile()) {
                $this->assertTrue($fileinfo->isReadable(), $fileinfo->getFilename() . ' is not readable');
                
                $xml = new DOMDocument();
                $xml->load($fileinfo->getPathname());
		$result = $xml->schemaValidate(APPLICATION_PATH . '/library/Opus/Document/documenttype.xsd');
		if ($fileinfo->getFilename() == 'demo_invalid.xml' || $fileinfo->getFilename() == 'demo_invalidfieldname.xml') {
                   $this->assertFalse($result, $fileinfo->getFilename() . ' is valid');
		}
		else {
                   $this->assertTrue($result, $fileinfo->getFilename() . ' is not valid');
		}
            }
        }

        libxml_clear_errors();
    }


}
