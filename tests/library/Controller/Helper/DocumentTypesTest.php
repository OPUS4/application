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
 */
class Controller_Helper_DocumentTypesTest extends ControllerTestCase {

    /**
     * Tests getting document types.
     *
     * The available document types are configured in *tests.ini*.
     */
    public function testGetDocumentTypes() {
        $docTypeHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('DocumentTypes');

        $documentTypes = $docTypeHelper->getDocumentTypes();

        $this->assertNotNull($documentTypes);
        $this->assertEquals(3, count($documentTypes));
        $this->assertArrayHasKey('all', $documentTypes);
        $this->assertArrayHasKey('preprint', $documentTypes);
        $this->assertArrayNotHasKey('article', $documentTypes);
    }

    /**
     * Test getting standard template name for document type.
     */
    public function testGetTemplateName() {
        $docTypeHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('DocumentTypes');

        $template = $docTypeHelper->getTemplateName('preprint');

        $this->assertNotNull($template);
        $this->assertEquals('preprint', $template);
    }

    /**
     * Test getting custom template name for document type.
     */
    public function testGetCustomTemplateName() {
        $docTypeHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('DocumentTypes');

        $template = $docTypeHelper->getTemplateName('all');

        $this->assertNotNull($template);
        $this->assertEquals('all', $template);
    }

    /**
     * Test checking validity of allowed document type.
     */
    public function testIsValid() {
        $docTypeHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('DocumentTypes');

        $this->assertTrue($docTypeHelper->isValid('preprint'));
    }

    /**
     * Test checking validity of excluded document type.
     */
    public function testIsNotValid() {
        $docTypeHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('DocumentTypes');

        $this->assertFalse($docTypeHelper->isValid('article'));
    }

    /**
     * Test getting DOM for document type.
     */
    public function testGetDocument() {
        $docTypeHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('DocumentTypes');

        $dom = $docTypeHelper->getDocument('preprint');

        $this->assertNotNull($dom);
    }

    /**
     * Testing helper without any configuration.
     */
    public function testGetAllDocumentTypes() {
        $config = Zend_Registry::get('Zend_Config');

        unset($config->documentTypes);

        $docTypeHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('DocumentTypes');

        $documentTypes = $docTypeHelper->getDocumentTypes();

        $this->assertNotNull($documentTypes);
        $this->assertArrayHasKey('article', $documentTypes);
    }

    /**
     * Test getting document types twice.
     */

    public function testGetDocumentTypesTwice() {
        $docTypeHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('DocumentTypes');

        $documentTypes = $docTypeHelper->getDocumentTypes();

        $documentTypes2 = $docTypeHelper->direct(); // test direct method

        $this->assertEquals($documentTypes, $documentTypes2);
    }

    /**
     * Test getting template name for unknown document type.
     */
    public function testGetTemplateForInvalidDocumentType() {
        $docTypeHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('DocumentTypes');

        $template = $docTypeHelper->getTemplateName('unknownDocType');

        $this->assertNull($template);
    }

    /**
     * Test getting path for document types with path not set.
     *
     * @expectedException Exception
     */
    public function testGetDocumentTypesWithPathNotSet() {
        $docTypeHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('DocumentTypes');

        $config = Zend_Registry::get('Zend_Config');

        unset($config->publish->path->documenttypes);

        $path = $docTypeHelper->getDocTypesPath();
    }

}

?>
