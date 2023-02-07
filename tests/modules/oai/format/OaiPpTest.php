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
 * @copyright   Copyright (c) 2021, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * @covers Oai_IndexController
 */
class Oai_Format_OaiPpTest extends ControllerTestCase
{
    /** @var bool */
    protected $configModifiable = true;

    /** @var string[] */
    protected $additionalResources = ['database', 'view', 'mainMenu'];

    /** @var string[] */
    protected $xpathNamespaces = [
        'oai'       => "http://www.openarchives.org/OAI/2.0/",
        'oai_dc'    => "http://www.openarchives.org/OAI/2.0/oai_dc/",
        'cc'        => "http://www.d-nb.de/standards/cc/",
        'dc'        => "http://purl.org/dc/elements/1.1/",
        'ddb'       => "http://www.d-nb.de/standards/ddb/",
        'pc'        => "http://www.d-nb.de/standards/pc/",
        'xMetaDiss' => "http://www.d-nb.de/standards/xmetadissplus/",
        'epicur'    => "urn:nbn:de:1111-2004033116",
        'dcterms'   => "http://purl.org/dc/terms/",
        'thesis'    => "http://www.ndltd.org/standards/metadata/etdms/1.0/",
        'eprints'   => 'http://www.openarchives.org/OAI/1.1/eprints',
        'oaiid'     => 'http://www.openarchives.org/OAI/2.0/oai-identifier',
        'marc'      => 'http://www.loc.gov/MARC21/slim',
        'PP'        => 'http://www.proprint-service.de/xml/schemes/v1/CHECKED',
    ];

    public function testXmlXsiSchemaDeclarationPresentForOaiPpMetadata()
    {
        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=oai_pp&identifier=oai::146');

        $this->registerXpathNamespaces($this->xpathNamespaces);

        $xml = $this->getResponse()->getBody();

        $this->assertXpath('//oai:metadata');
        $this->assertXpath('//PP:ProPrint');

        if (preg_match('#<PP:ProPrint.*>#', $xml, $matches)) {
            $startTag = $matches[0];
            $this->assertContains('xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"', $startTag);
        } else {
            $this->fail('element \'PP:ProPrint\' not found');
        }
    }
}
