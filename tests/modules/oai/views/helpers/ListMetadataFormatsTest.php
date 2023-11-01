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
 * @copyright   Copyright (c) 2023, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

class Oai_View_Helper_ListMetadataFormatsTest extends ControllerTestCase
{
    /** @var string */
    protected $additionalResources = 'database';

    /**
     * @return string
     */
    protected function getExpectedMetadaFormats()
    {
        return <<<TEXT

    <metadataFormat>
      <metadataPrefix>oai_dc</metadataPrefix>
      <schema>http://www.openarchives.org/OAI/2.0/oai_dc.xsd</schema>
      <metadataNamespace>http://www.openarchives.org/OAI/2.0/oai_dc/</metadataNamespace>
    </metadataFormat>
    <metadataFormat>
      <metadataPrefix>epicur</metadataPrefix>
      <schema>http://www.persistent-identifier.de/xepicur/version1.0/xepicur.xsd</schema>
      <metadataNamespace>urn:nbn:de:1111-2004033116</metadataNamespace>
    </metadataFormat>
    <metadataFormat>
      <metadataPrefix>xMetaDissPlus</metadataPrefix>
      <schema>http://files.dnb.de/standards/xmetadissplus/xmetadissplus.xsd</schema>
      <metadataNamespace>http://www.d-nb.de/standards/xmetadissplus/</metadataNamespace>
    </metadataFormat>
    <metadataFormat>
      <metadataPrefix>MARC21</metadataPrefix>
      <schema>https://www.loc.gov/standards/marcxml/schema/MARC21slim.xsd</schema>
      <metadataNamespace>http://www.loc.gov/MARC21/slim</metadataNamespace>
    </metadataFormat>
  
TEXT;
    }

    /**
     * @return string
     */
    protected function getExpectedMetadaFormatsWithoutOaiDc()
    {
        return <<<TEXT

    <metadataFormat>
      <metadataPrefix>epicur</metadataPrefix>
      <schema>http://www.persistent-identifier.de/xepicur/version1.0/xepicur.xsd</schema>
      <metadataNamespace>urn:nbn:de:1111-2004033116</metadataNamespace>
    </metadataFormat>
    <metadataFormat>
      <metadataPrefix>xMetaDissPlus</metadataPrefix>
      <schema>http://files.dnb.de/standards/xmetadissplus/xmetadissplus.xsd</schema>
      <metadataNamespace>http://www.d-nb.de/standards/xmetadissplus/</metadataNamespace>
    </metadataFormat>
    <metadataFormat>
      <metadataPrefix>MARC21</metadataPrefix>
      <schema>https://www.loc.gov/standards/marcxml/schema/MARC21slim.xsd</schema>
      <metadataNamespace>http://www.loc.gov/MARC21/slim</metadataNamespace>
    </metadataFormat>
  
TEXT;
    }

    public function testListMetadataFormats()
    {
        $listMetaDataFormats = new Oai_View_Helper_ListMetadataFormats();
        $this->assertEquals($this->getExpectedMetadaFormats(), $listMetaDataFormats->listMetadataFormats());
    }

    public function testListMetadataFormatsFormatNotVisible()
    {
        $this->enableSecurity();

        $this->adjustConfiguration([
            'oai' => [
                'format' => [
                    'oai_dc' => [
                        'visible'   => 0,
                        'adminOnly' => 0,
                    ],
                ],
            ],
        ]);

        $listMetaDataFormats = new Oai_View_Helper_ListMetadataFormats();
        $this->assertEquals($this->getExpectedMetadaFormatsWithoutOaiDc(), $listMetaDataFormats->listMetadataFormats());
    }

    public function testListMetadataFormatsFormatNotVisibleAndAdminOnly()
    {
        $this->enableSecurity();

        $this->adjustConfiguration([
            'oai' => [
                'format' => [
                    'oai_dc' => [
                        'visible'   => 0,
                        'adminOnly' => 1,
                    ],
                ],
            ],
        ]);

        $listMetaDataFormats = new Oai_View_Helper_ListMetadataFormats();
        $this->assertEquals($this->getExpectedMetadaFormatsWithoutOaiDc(), $listMetaDataFormats->listMetadataFormats());
    }

    public function testListMetadataFormatsFormatVisible()
    {
        $this->enableSecurity();

        $this->adjustConfiguration([
            'oai' => [
                'format' => [
                    'oai_dc' => [
                        'visible'   => 1,
                        'adminOnly' => 0,
                    ],
                ],
            ],
        ]);

        $listMetaDataFormats = new Oai_View_Helper_ListMetadataFormats();
        $this->assertEquals($this->getExpectedMetadaFormats(), $listMetaDataFormats->listMetadataFormats());
    }

    public function testListMetadataFormatsFormatVisibleAndAdminOnly()
    {
        $this->enableSecurity();

        $this->adjustConfiguration([
            'oai' => [
                'format' => [
                    'oai_dc' => [
                        'visible'   => 1,
                        'adminOnly' => 1,
                    ],
                ],
            ],
        ]);

        $listMetaDataFormats = new Oai_View_Helper_ListMetadataFormats();
        $this->assertEquals($this->getExpectedMetadaFormatsWithoutOaiDc(), $listMetaDataFormats->listMetadataFormats());
    }

    public function testListMetadataFormatsAsAdminWithFormatNotVisible()
    {
        $this->enableSecurity();
        $this->loginUser('admin', 'adminadmin');

        $this->adjustConfiguration([
            'oai' => [
                'format' => [
                    'oai_dc' => [
                        'visible'   => 0,
                        'adminOnly' => 0,
                    ],
                ],
            ],
        ]);

        $listMetaDataFormats = new Oai_View_Helper_ListMetadataFormats();
        $formatList          = $listMetaDataFormats->listMetadataFormats();
        $this->assertEquals($this->getExpectedMetadaFormatsWithoutOaiDc(), $listMetaDataFormats->listMetadataFormats());
    }

    public function testListMetadataFormatsAsAdminWithFormatNotVisibleAndAdminOnly()
    {
        $this->enableSecurity();
        $this->loginUser('admin', 'adminadmin');

        $this->adjustConfiguration([
            'oai' => [
                'format' => [
                    'oai_dc' => [
                        'visible'   => 0,
                        'adminOnly' => 1,
                    ],
                ],
            ],
        ]);

        $listMetaDataFormats = new Oai_View_Helper_ListMetadataFormats();
        $this->assertEquals($this->getExpectedMetadaFormatsWithoutOaiDc(), $listMetaDataFormats->listMetadataFormats());
    }

    public function testListMetadataFormatsAsAdminWithFormatVisible()
    {
        $this->enableSecurity();
        $this->loginUser('admin', 'adminadmin');

        $this->adjustConfiguration([
            'oai' => [
                'format' => [
                    'oai_dc' => [
                        'visible'   => 1,
                        'adminOnly' => 0,
                    ],
                ],
            ],
        ]);

        $listMetaDataFormats = new Oai_View_Helper_ListMetadataFormats();
        $this->assertEquals($this->getExpectedMetadaFormats(), $listMetaDataFormats->listMetadataFormats());
    }

    public function testListMetadataFormatsAsAdminWithFormatVisibleAndAdminOnly()
    {
        $this->enableSecurity();
        $this->loginUser('admin', 'adminadmin');

        $this->adjustConfiguration([
            'oai' => [
                'format' => [
                    'oai_dc' => [
                        'visible'   => 1,
                        'adminOnly' => 1,
                    ],
                ],
            ],
        ]);

        $listMetaDataFormats = new Oai_View_Helper_ListMetadataFormats();
        $this->assertEquals($this->getExpectedMetadaFormats(), $listMetaDataFormats->listMetadataFormats());
    }
}
