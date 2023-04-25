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

use Opus\Common\UserRole;

class Application_Export_ExporterTest extends ControllerTestCase
{
    /** @var string */
    protected $additionalResources = 'all';

    /** @var bool */
    private $guestExportEnabled;

    public function setUp(): void
    {
        parent::setUp();

        $guest   = UserRole::fetchByName('guest');
        $modules = $guest->listAccessModules();

        $this->guestExportEnabled = in_array('export', $modules);
    }

    public function tearDown(): void
    {
        // restore guest access to export module
        $guest = UserRole::fetchByName('guest');

        if ($this->guestExportEnabled) {
            $guest->appendAccessModule('export');
        } else {
            $guest->removeAccessModule('export');
        }

        $guest->store();

        parent::tearDown();
    }

    public function testAddFormats()
    {
        // TODO remove HACK to setup route for tests below
        $this->dispatch('/home');

        $exporter = new Application_Export_Exporter();

        $exporter->addFormats([
            'bibtex' => [
                'name'        => 'BibTeX',
                'description' => 'Export BibTeX',
                'module'      => 'citationExport',
                'controller'  => 'index',
                'action'      => 'download',
                'params'      => [
                    'output' => 'bibtex',
                ],
            ],
        ]);

        $formats = $exporter->getFormats();

        $this->assertInternalType('array', $formats);
        $this->assertCount(1, $formats);
        $this->assertArrayHasKey('bibtex', $formats);

        $bibtex = $formats['bibtex'];

        $this->assertInstanceOf('Zend_Navigation_Page_Mvc', $bibtex);
        $this->assertEquals('/citationExport/index/download/output/bibtex', $bibtex->getHref());

        $bibtex->setParam('docId', 146);

        $this->assertEquals('/citationExport/index/download/output/bibtex/docId/146', $bibtex->getHref());
    }

    public function testGetFormats()
    {
        // TODO remove HACK to setup route
        $this->dispatch('/home');

        $this->markTestIncomplete('more testing?');
    }

    public function testContextProperties()
    {
        $exporter = new Application_Export_Exporter();

        $exporter->addFormats([
            'bibtex' => [
                'name'        => 'BibTeX',
                'description' => 'Export BibTeX',
                'module'      => 'citationExport',
                'controller'  => 'index',
                'action'      => 'download',
                'frontdoor'   => true,
                'search'      => false,
                'params'      => [
                    'output' => 'bibtex',
                ],
            ],
        ]);

        $formats = $exporter->getFormats();

        $this->assertInternalType('array', $formats);
        $this->assertCount(1, $formats);
        $this->assertArrayHasKey('bibtex', $formats);

        // Zend_Navigation_Page_Mvc
        $bibtex = $formats['bibtex'];

        $this->assertInstanceOf('Zend_Navigation_Page_Mvc', $bibtex);

        $this->assertTrue($bibtex->get('frontdoor'));
        $this->assertFalse($bibtex->get('search'));
        $this->assertNull($bibtex->get('admin'));
    }

    public function testGetAllowedFormats()
    {
        // Restricted format are only setup during request processing (OPUS4/application#516)
        $this->dispatch('/home');

        $exporter = Zend_Registry::get('Opus_Exporter');

        $formats = $exporter->getAllowedFormats();

        $this->assertCount(9, $formats);

        $this->enableSecurity();

        $guest = UserRole::fetchByName('guest');
        $guest->removeAccessModule('export');
        $guest->store();

        $formats = $exporter->getAllowedFormats();

        $this->assertCount(2, $formats);
    }

    public function testGetAllowedFormatsSorted()
    {
        $exporter = Zend_Registry::get('Opus_Exporter');

        $formats = $exporter->getAllowedFormats();

        $lastName = '';

        foreach ($formats as $format) {
            $name = $format->get('name');
            $this->assertGreaterThanOrEqual($lastName, $name);
            $lastName = $name;
        }
    }
}
