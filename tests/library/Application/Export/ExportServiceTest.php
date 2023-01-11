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

class Application_Export_ExportServiceTest extends ControllerTestCase
{
    /** @var Application_Export_ExportService */
    private $service;

    public function setUp(): void
    {
        parent::setUp();

        $this->makeConfigurationModifiable();

        $this->service = new Application_Export_ExportService();
    }

    public function testLoadPlugins()
    {
        $this->service->loadPlugins();

        $plugins = $this->service->getAllPlugins();

        $this->assertInternalType('array', $plugins);
        $this->assertCount(7, $plugins);
        $this->assertArrayHasKey('index', $plugins);
        $this->assertArrayHasKey('bibtex', $plugins);
        $this->assertArrayHasKey('ris', $plugins);
        $this->assertArrayHasKey('csv', $plugins);
        $this->assertArrayHasKey('publist', $plugins);
        $this->assertArrayHasKey('datacite', $plugins);

        $this->assertInstanceOf(Zend_Config::class, $plugins['index']);

        $bibtexConfig = $plugins['bibtex'];

        $this->assertEquals('Export_Model_XsltExport', $bibtexConfig->class);
        $this->assertEquals('bibtex', $bibtexConfig->stylesheet);
        $this->assertEquals('text/plain', $bibtexConfig->contentType);
        $this->assertEquals('export.bib', $bibtexConfig->attachmentFilename);
        $this->assertEquals(100, $bibtexConfig->maxDocumentsGuest);
        $this->assertEquals(500, $bibtexConfig->maxDocumentsUser);
    }

    public function testGetPlugin()
    {
        $this->service->loadPlugins();

        $plugin = $this->service->getPlugin('index');

        $this->assertNotNull($plugin);
        $this->assertInstanceOf('Export_Model_XmlExport', $plugin);

        $pluginConfig = $plugin->getConfig();

        $this->assertNotNull($pluginConfig);
        $this->assertInstanceOf(Zend_Config::class, $pluginConfig);

        $this->assertEquals(100, $pluginConfig->maxDocumentsGuest);
    }

    public function testGetDefaults()
    {
        $defaults = $this->service->getDefaults();

        $this->assertNotNull($defaults);
        $this->assertInstanceOf(Zend_Config::class, $defaults);

        $this->assertEquals('Export_Model_XmlExport', $defaults->class);
    }

    public function testSetDefaults()
    {
        $this->service->setDefaults(new Zend_Config([
            'class' => 'Export_Model_XsltExport',
        ]));

        $defaults = $this->service->getDefaults();

        $this->assertEquals('Export_Model_XsltExport', $defaults->class);
    }

    public function testAddPlugin()
    {
        $this->service->addPlugin('marc', new Zend_Config([
            'class'      => 'Export_Model_XsltExport',
            'stylesheet' => 'marc.xslt',
        ]));

        $plugins = $this->service->getAllPlugins();

        $this->assertCount(1, $plugins);

        $plugin = $this->service->getPlugin('marc');

        $this->assertNotNull($plugin);
        $this->assertInstanceOf('Export_Model_XsltExport', $plugin);

        $config = $plugin->getConfig();

        $this->assertEquals(100, $config->maxDocumentsGuest);
        $this->assertEquals('marc.xslt', $config->stylesheet);
    }
}
