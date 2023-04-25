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
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Unit tests for document types helper.
 *
 * The unit test depend on the available document types in the doctypes folder
 * as well as the configuration in the file tests.ini.
 */
class Application_Controller_Action_Helper_DocumentTypesTest extends ControllerTestCase
{
    /** @var string */
    protected $additionalResources = 'translation';

    /** @var Application_Controller_Action_Helper_DocumentTypes Instance of DocumentTypes helper for testing. */
    private $docTypeHelper;

    /**
     * Setup tests.
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->docTypeHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('DocumentTypes');
    }

    /**
     * Testet, ob die Validierung der Dokumenttypen korrekt ist.
     */
    public function testDoctypeValidation()
    {
        $validationArray = $this->docTypeHelper->validateAll();
        $this->assertTrue($validationArray['foobar'], 1);
        $this->assertTrue($validationArray['bazbar'], 1);
        $this->assertFalse($validationArray['demo_invalidfieldname']);
        $this->assertFalse($validationArray['demo_invalid']);
    }

    /**
     * Testet, ob die Dokumenttypen, die inkludiert oder exkludiert sind, ausgegeben werden
     */
    public function testActiveDoctypes()
    {
        $validationArray = $this->docTypeHelper->getDocumentTypes();
        $this->assertArrayHasKey('all', $validationArray);
        $this->assertArrayHasKey('preprint', $validationArray);
        $this->assertArrayHasKey('demo_invalid', $validationArray);
        $this->assertArrayHasKey('foobar', $validationArray);
        $this->assertArrayNotHasKey('article', $validationArray);
    }

    /**
     * Testet, ob die korrekte Fehlermeldung ausgegeben wird, wenn das Dokument nicht validiert werden kann
     */
    public function testErrorMessage()
    {
        $this->docTypeHelper->validate('demo_invalid');
        $errors            = $this->docTypeHelper->getErrors();
        $demoInvalidErrors = $errors['demo_invalid'];
        $this->assertEquals("Element '{http://www.opus-repository.org/schema/documenttype}field', "
            . "attribute 'dataType': The attribute 'dataType' is not allowed.\n", $demoInvalidErrors[0]->message);
        $this->assertEquals("Element '{http://www.opus-repository.org/schema/documenttype}default', "
            . "attribute 'Value': The attribute 'Value' is not allowed.\n", $demoInvalidErrors[1]->message);
    }

    /**
     * Tests getting document types.
     *
     * The available document types are configured in *tests.ini*.
     */
    public function testGetDocumentTypes()
    {
        $documentTypes = $this->docTypeHelper->getDocumentTypes();

        $this->assertNotNull($documentTypes);
        $this->assertEquals(8, count($documentTypes));
        $this->assertArrayHasKey('all', $documentTypes);
        $this->assertArrayHasKey('preprint', $documentTypes);
        $this->assertArrayHasKey('demo_invalid', $documentTypes);
        $this->assertArrayHasKey('demo', $documentTypes);
        $this->assertArrayHasKey('foobar', $documentTypes);
        $this->assertArrayHasKey('barbaz', $documentTypes);
        $this->assertArrayHasKey('bazbar', $documentTypes);
        $this->assertArrayHasKey('masterthesis', $documentTypes);
        $this->assertArrayNotHasKey('article', $documentTypes);
    }

    /**
     * Test getting standard template name for document type.
     */
    public function testGetTemplateName()
    {
        $template = $this->docTypeHelper->getTemplateName('preprint');

        $this->assertNotNull($template);
        $this->assertEquals('preprint', $template);
    }

    /**
     * Test getting custom template name for document type.
     *
     * The custom template name is configured in *tests.ini*.
     */
    public function testGetCustomTemplateName()
    {
        $template = $this->docTypeHelper->getTemplateName('foobar');

        $this->assertNotNull($template);
        $this->assertEquals('barfoo', $template);
    }

    /**
     * Test checking validity of allowed document type.
     */
    public function testIsValid()
    {
        $this->assertTrue($this->docTypeHelper->isValid('preprint'));
    }

    /**
     * Test checking validity of excluded document type.
     */
    public function testIsNotValid()
    {
        $this->assertFalse($this->docTypeHelper->isValid('article'));
    }

    /**
     * Test getting DOM for document type.
     */
    public function testGetDocument()
    {
        $dom = $this->docTypeHelper->getDocument('preprint');

        $this->assertNotNull($dom);
    }

    public function testGetDocumentThrowsException()
    {
        $this->expectException(Application_Exception::class);
        $this->docTypeHelper->getDocument('article');
    }

    public function testGetDocumentThrowsSchemaInvalidException()
    {
        $this->expectException(Application_Exception::class);
        $this->docTypeHelper->getDocument('demo_invalid');
    }

    /**
     * Testing helper without any configuration.
     */
    public function testGetAllDocumentTypes()
    {
        $config = $this->getConfig();
        unset($config->documentTypes);

        $documentTypes = $this->docTypeHelper->getDocumentTypes();

        $this->assertNotNull($documentTypes);
        $this->assertArrayHasKey('article', $documentTypes);
    }

    /**
     * Test getting document types twice.
     */
    public function testGetDocumentTypesTwice()
    {
        $documentTypes = $this->docTypeHelper->getDocumentTypes();

        $documentTypes2 = $this->docTypeHelper->direct(); // test direct method

        $this->assertEquals($documentTypes, $documentTypes2);
    }

    /**
     * Test getting template name for unknown document type.
     */
    public function testGetTemplateForInvalidDocumentType()
    {
        $template = $this->docTypeHelper->getTemplateName('unknownDocType');

        $this->assertNull($template);
    }

    /**
     * Test getting path for document types with path not set.
     */
    public function testGetDocumentTypesWithPathNotSet()
    {
        $config = $this->getConfig();

        unset($config->publish->path->documenttypes);

        $this->expectException(Application_Exception::class);
        $this->expectExceptionMessage('Path to document types not configured');
        $this->docTypeHelper->getDocTypesPath();
    }

    /**
     * Check if all document types can be translated.
     */
    public function testTranslationOfDocumentTypes()
    {
        $excludeFromTranslationCheck = ['demo_invalid', 'foobar', 'barbaz', 'bazbar'];
        $translate                   = Application_Translate::getInstance();

        $documentTypes = $this->docTypeHelper->getDocumentTypes();

        foreach ($documentTypes as $docType => $docTypePath) {
            if (! in_array($docType, $excludeFromTranslationCheck)) {
                $this->assertNotEquals($docType, $translate->translate($docType), 'Could not translate document type: ' . $docType);
            }
        }
    }

    /**
     * Regression test for OPUSVIER-2168
     */
    public function testValidateAllXMLDocumentTypeDefinitions()
    {
        $iterator = $this->docTypeHelper->getDirectoryIterator($this->docTypeHelper->getDocTypesPath());

        // Enable user error handling while validating input file
        libxml_clear_errors();
        $useInternalErrors = libxml_use_internal_errors(true);

        foreach ($iterator as $fileinfo) {
            if ($fileinfo->isFile()) {
                $this->assertTrue($fileinfo->isReadable(), $fileinfo->getFilename() . ' is not readable');

                $xml = new DOMDocument();
                $xml->load($fileinfo->getPathname());
                $result = $xml->schemaValidate($this->docTypeHelper->getXmlSchemaPath());
                if ($fileinfo->getFilename() === 'demo_invalid.xml' || $fileinfo->getFilename() === 'demo_invalidfieldname.xml') {
                    $this->assertFalse($result, $fileinfo->getFilename() . ' is valid');
                } else {
                    $this->assertTrue($result, $fileinfo->getFilename() . ' is not valid');
                }
            }
        }
        libxml_use_internal_errors($useInternalErrors);
        libxml_clear_errors();
    }

    /**
     * Dieser Test vergleicht jedes DokumentTemplate mit jedem DokumentTyp und umgekehrt und validiert,
     * dass beide in Paaren vorkommen.
     */
    public function testDocumentTypesAndTemplates()
    {
        $config    = $this->getConfig();
        $templates = $this->getFileNames($config->publish->path->documenttemplates, '.phtml');
        $types     = $this->getFileNames($config->publish->path->documenttypes, '.xml');

        // Test-Templates und -Dokumenttypen werden von der Prüfung ausgenommen
        unset($templates['barfoo']);
        unset($types['bazbar']);
        unset($types['demo_invalidfieldname']);
        unset($types['demo_invalid']);
        unset($types['foobar']);
        unset($types['barbaz']);

        $array1 = array_diff($templates, $types);
        $array2 = array_diff($types, $templates);

        $this->assertEmpty($array1, 'Doctype missing for template: ' . implode(", ", $array1));
        $this->assertEmpty($array2, 'Template missing for doctype: ' . implode(", ", $array2));
    }

    /**
     * @param string $path
     * @param string $extension
     * @return array
     */
    private function getFileNames($path, $extension)
    {
        $fileNames = [];

        if ($path instanceof Zend_Config) {
            $path = $path->toArray();
        }

        $iterator = $this->docTypeHelper->getDirectoryIterator($path);
        foreach ($iterator as $fileinfo) {
            if ($fileinfo->isFile()) {
                $fileNames[$fileinfo->getBaseName($extension)] = $fileinfo->getBaseName($extension);
            }
        }
        return $fileNames;
    }

    public function testGetDocTypesPathAsArray()
    {
        $paths = $this->docTypeHelper->getDocTypesPath();

        $this->assertNotNull($paths);
        $this->assertInternalType("array", $paths);
        $this->assertCount(2, $paths);
        $this->assertContains(APPLICATION_PATH . '/application/configs/doctypes', $paths);
        $this->assertContains(APPLICATION_PATH . '/tests/resources/doctypes', $paths);
    }

    public function testGetDocTypesPath()
    {
        $this->adjustConfiguration([
            'publish' => [
                'path' => [
                    'documenttypes' => APPLICATION_PATH . '/application/configs/doctypes',
                ],
            ],
        ]);

        $paths = $this->docTypeHelper->getDocTypesPath();

        $this->assertNotNull($paths);
        $this->assertInternalType("string", $paths);
        $this->assertEquals(APPLICATION_PATH . '/application/configs/doctypes', $paths);
    }

    public function testGetTemplates()
    {
        $templates = $this->docTypeHelper->getTemplates();

        $this->assertCount(32, $templates);

        foreach ($templates as $name => $path) {
            $file = new SplFileInfo($path);
            $this->assertEquals($file->getFilename(), $name . '.phtml');
            $this->assertTrue($file->isReadable(), "Document type template '$name' cannot be read.");
        }
    }

    public function testTemplateForEveryDocumentType()
    {
        $config = $this->getConfig();
        unset($config->documentTypes);

        $docTypes = $this->docTypeHelper->getAllDocumentTypes();

        // remove document types without templates
        unset($docTypes['demo_invalid']);
        unset($docTypes['barbaz']);
        unset($docTypes['bazbar']);
        unset($docTypes['demo_invalidfieldname']);
        unset($docTypes['foobar']);

        foreach ($docTypes as $docType => $path) {
            $templateName = $this->docTypeHelper->getTemplateName($docType);
            $templatePath = $this->docTypeHelper->getTemplatePath($templateName);

            $this->assertTrue(is_readable($templatePath), "Document type '$docType' has no template.");
        }
    }

    public function testGetTemplatePathUnknownName()
    {
        $this->assertNull($this->docTypeHelper->getTemplatePath('unknown'));
    }
}
