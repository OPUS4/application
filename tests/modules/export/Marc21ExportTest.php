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
 * @category    Tests
 * @package     Export
 * @author      Sascha Szott <opus-development@saschaszott.de>
 * @copyright   Copyright (c) 2019, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

class Export_Marc21ExportTest extends ControllerTestCase
{

    protected $additionalResources = 'all';

    /**
     * Standardmäßig kann der MARC21-XML-Export auch für Dokumente, die nicht freigeschaltet sind,
     * abgerufen werden.
     *
     * @throws Opus_Model_Exception
     */
    public function testMarc21XmlExportWithUnpublishedDoc()
    {
        $doc = $this->createTestDocument();
        $doc->setServerState('unpublished');
        $doc->setType('article');
        $doc->setLanguage('eng');
        $docId = $doc->store();

        $this->dispatch("/export/index/marc21/docId/${docId}/searchtype/id");

        $this->assertResponseCode(200);

        $this->assertXpathContentContains('//marc:leader', '00000nab a22000005  4500');
        $this->assertXpathContentContains('//marc:controlfield[@tag="001"]', 'docId-' . $docId);
        $this->assertXpathContentContains('//marc:controlfield[@tag="007"]', 'cr uuu---uunan');
        $this->assertXpathContentContains('//marc:datafield[@tag="041"]/marc:subfield[@code="a"]', 'eng');
        $this->assertXpathContentContains('//marc:datafield[@tag="655"]/marc:subfield[@code="a"]', 'article');
        $this->assertXpathContentContains('//marc:datafield[@tag="856"]/marc:subfield[@code="u"]', 'http:///frontdoor/index/index/docId/' . $docId);

        $this->assertNotXpath('//marc:datafield[@tag="245"]');
        $this->assertNotXpath('//marc:datafield[@tag="264"]');
    }

    /**
     * Wenn die Konfigurationseinstellung plugins.export.marc21.restrictExportToPublishedDocuments
     * den Wert true hat, so kann ein Dokument nur dann im Format MARC21-XML exportiert werden,
     * wenn das Dokument freigeschaltet wurde.
     *
     * @throws Opus_Model_Exception
     * @throws Zend_Exception
     */
    public function testMarc21XmlExportWithUnpublishedDocAndRestrictedOptionEnabled()
    {
        $config = Zend_Registry::get('Zend_Config');

        Zend_Registry::get('Zend_Config')->merge(
            new Zend_Config(
                ['plugins' =>
                    ['export' =>
                        ['marc21' => ['restrictExportToPublishedDocuments' => self::CONFIG_VALUE_TRUE]]
                    ]
                ]
            )
        );

        $doc = $this->createTestDocument();
        $doc->setServerState('unpublished');
        $doc->setType('article');
        $doc->setLanguage('eng');
        $docId = $doc->store();

        $this->dispatch("/export/index/marc21/docId/${docId}/searchtype/id");

        // revert configuration changes
        Zend_Registry::set('Zend_Config', $config);

        $this->assertResponseCode(200);
        $this->assertEmpty($this->getResponse()->getBody());
    }
}
