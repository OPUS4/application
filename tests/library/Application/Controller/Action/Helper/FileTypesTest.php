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
 * @copyright   Copyright (c) 2017, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

class Application_Controller_Action_Helper_FileTypesTest extends ControllerTestCase
{
    /** @var Application_Controller_Action_Helper_FileTypes */
    private $helper;

    public function setUp(): void
    {
        parent::setUp();
        $this->makeConfigurationModifiable();
        $this->helper = new Application_Controller_Action_Helper_FileTypes();
    }

    public function testGetValidMimeTypes()
    {
        $types = $this->helper->getValidMimeTypes();

        $this->assertNotNull($types);
        $this->assertInternalType('array', $types);

        $this->assertArrayHasKey('pdf', $types);
        $this->assertEquals('application/pdf', $types['pdf']);

        $this->assertArrayHasKey('txt', $types);
        $this->assertEquals('text/plain', $types['txt']);

        $this->assertArrayNotHasKey('default', $types);
    }

    public function testMimeTypeAddedToBaseConfigurationFromApplicationIni()
    {
        $this->adjustConfiguration([
            'filetypes' => [
                'xml' => [
                    'mimeType' => [
                        'text/xml',
                        'application/xml',
                    ],
                ],
            ],
        ]);

        $types = $this->helper->getValidMimeTypes();

        $this->assertNotNull($types);
        $this->assertCount(5, $types);
        $this->assertArrayHasKey('xml', $types);

        $xmlTypes = $types['xml'];

        $this->assertCount(2, $xmlTypes);
        $this->assertContains('application/xml', $xmlTypes);
        $this->assertContains('text/xml', $xmlTypes);
    }

    public function testGetContentDisposition()
    {
        $this->assertEquals('attachment', $this->helper->getContentDisposition('text/plain'));
        $this->assertEquals('inline', $this->helper->getContentDisposition('application/pdf'));
    }

    public function testIsValidMimeType()
    {
        $this->assertTrue($this->helper->isValidMimeType('text/plain'));
        $this->assertTrue($this->helper->isValidMimeType('text/html'));

        $this->assertFalse($this->helper->isValidMimeType('text/xslt'));
        $this->assertFalse($this->helper->isValidMimeType('application/doc'));
    }

    public function testIsValidMimeTypeForExtensionWithMultipleTypes()
    {
        $this->adjustConfiguration([
            'filetypes' => [
                'xml' => [
                    'mimeType' => [
                        'text/xml',
                        'application/xml',
                    ],
                ],
            ],
        ]);

        $this->assertTrue($this->helper->isValidMimeType('application/xml'));
        $this->assertTrue($this->helper->isValidMimeType('text/xml'));
    }

    public function testIsValidMimeTypeForExtension()
    {
        $this->adjustConfiguration([
            'filetypes' => [
                'xml' => [
                    'mimeType' => [
                        'text/xml',
                        'application/xml',
                    ],
                ],
            ],
        ]);

        $this->assertTrue($this->helper->isValidMimeType('application/xml', 'xml'));
        $this->assertTrue($this->helper->isValidMimeType('text/xml', 'xml'));
        $this->assertTrue($this->helper->isValidMimeType('text/plain', 'txt'));
        $this->assertTrue($this->helper->isValidMimeType('application/pdf', 'pdf'));

        $this->assertFalse($this->helper->isValidMimeType('text/plain', 'xml'));
        $this->assertFalse($this->helper->isValidMimeType('application/pdf', 'doc'));
        $this->assertFalse($this->helper->isValidMimeType('image/jpeg', 'jpeg'));
        $this->assertFalse($this->helper->isValidMimeType('audio/mpeg', 'txt'));
    }

    public function testIsValidMimeTypeForNull()
    {
        $this->assertFalse($this->helper->isValidMimeType(null));
        $this->assertFalse($this->helper->isValidMimeType(null, 'txt'));
    }

    public function testExtensionCaseInsensitive()
    {
        $this->adjustConfiguration([
            'filetypes' => ['XML' => ['mimeType' => 'text/xml']],
        ]);

        $this->assertTrue($this->helper->isValidMimeType('text/xml', 'xml'));
        $this->assertTrue($this->helper->isValidMimeType('text/xml', 'XML'));
        $this->assertTrue($this->helper->isValidMimeType('text/xml', 'XmL'));
    }
}
