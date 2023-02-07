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
 * @copyright   Copyright (c) 2019, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\Model\ModelException;

class Export_Marc21ExportTest extends ControllerTestCase
{
    /** @var string */
    protected $additionalResources = 'all';

    /**
     * Nicht freigeschaltete Dokumente können nur dann im Format MARC21-XML exportiert werden,
     * wenn der Benutzer das Recht 'resource_documents' besitzt.
     *
     * @throws ModelException
     * @throws Zend_Exception
     */
    public function testMarc21XmlExportWithUnpublishedDocNotAllowed()
    {
        $removeAccess = $this->addModuleAccess('export', 'guest');
        $this->enableSecurity();
        $this->adjustConfiguration([
            'plugins'
                => [
                    'export'
                    => ['marc21' => ['adminOnly' => self::CONFIG_VALUE_FALSE]],
                ],
        ]);

        $doc = $this->createTestDocument();
        $doc->setServerState('unpublished');
        $doc->setType('article');
        $doc->setLanguage('eng');
        $docId = $doc->store();

        Application_Security_AclProvider::init();

        $this->dispatch("/export/index/marc21/docId/{$docId}/searchtype/id");

        // revert configuration changes
        $this->restoreSecuritySetting();

        if ($removeAccess) {
            $this->removeModuleAccess('export', 'guest');
        }

        $this->assertResponseCode(401);
        $this->assertContains('export of unpublished documents is not allowed', $this->getResponse()->getBody());
    }

    public function testMarc21XmlExportWithUnpublishedDocAllowedForAdmin()
    {
        $this->enableSecurity();
        $this->adjustConfiguration([
            'plugins'
                => [
                    'export'
                    => ['marc21' => ['adminOnly' => self::CONFIG_VALUE_FALSE]],
                ],
        ]);

        $doc = $this->createTestDocument();
        $doc->setServerState('unpublished');
        $doc->setType('article');
        $doc->setLanguage('eng');
        $docId = $doc->store();

        $this->loginUser('admin', 'adminadmin');

        $this->dispatch("/export/index/marc21/docId/{$docId}/searchtype/id");

        // revert configuration changes
        $this->restoreSecuritySetting();

        $this->assertResponseCode(200);
        $this->assertXpathContentContains('//marc:leader', '00000naa a22000005  4500');
        $this->assertXpathContentContains('//marc:controlfield[@tag="001"]', 'docId-' . $docId);
        $this->assertXpathContentContains('//marc:controlfield[@tag="007"]', 'cr uuu---uunan');
        $this->assertXpathContentContains('//marc:datafield[@tag="041"]/marc:subfield[@code="a"]', 'eng');
        $this->assertXpathContentContains('//marc:datafield[@tag="655"]/marc:subfield[@code="a"]', 'article');
        $this->assertXpathContentContains('//marc:datafield[@tag="856"]/marc:subfield[@code="u"]', 'http:///frontdoor/index/index/docId/' . $docId);

        $this->assertNotXpath('//marc:datafield[@tag="245"]');
        $this->assertNotXpath('//marc:datafield[@tag="264"]');
    }

    public function testMarc21XmlExportWithUnpublishedDocAllowedForNonAdminUserWithPermission()
    {
        $removeAccess = $this->addModuleAccess('export', 'docsadmin');
        $this->enableSecurity();
        $this->adjustConfiguration([
            'plugins'
                => [
                    'export'
                    => ['marc21' => ['adminOnly' => self::CONFIG_VALUE_FALSE]],
                ],
        ]);

        $doc = $this->createTestDocument();
        $doc->setServerState('unpublished');
        $doc->setType('article');
        $doc->setLanguage('eng');
        $docId = $doc->store();

        $this->loginUser('security8', 'security8pwd');

        $this->dispatch("/export/index/marc21/docId/{$docId}/searchtype/id");

        // revert configuration changes
        $this->restoreSecuritySetting();

        if ($removeAccess) {
            $this->removeModuleAccess('export', 'docsadmin');
        }

        $this->assertResponseCode(200);
        $this->assertXpathContentContains('//marc:leader', '00000naa a22000005  4500');
        $this->assertXpathContentContains('//marc:controlfield[@tag="001"]', 'docId-' . $docId);
        $this->assertXpathContentContains('//marc:controlfield[@tag="007"]', 'cr uuu---uunan');
        $this->assertXpathContentContains('//marc:datafield[@tag="041"]/marc:subfield[@code="a"]', 'eng');
        $this->assertXpathContentContains('//marc:datafield[@tag="655"]/marc:subfield[@code="a"]', 'article');
        $this->assertXpathContentContains('//marc:datafield[@tag="856"]/marc:subfield[@code="u"]', 'http:///frontdoor/index/index/docId/' . $docId);

        $this->assertNotXpath('//marc:datafield[@tag="245"]');
        $this->assertNotXpath('//marc:datafield[@tag="264"]');
    }

    public function testMarc21XmlExportWithPublishedDocNotAllowedForGuest()
    {
        $removeAccess = $this->addModuleAccess('export', 'guest');
        $this->enableSecurity();

        $this->adjustConfiguration([
            'plugins' => [
                'export' => [
                    'marc21' => ['adminOnly' => self::CONFIG_VALUE_TRUE],
                ],
            ],
        ]);

        $doc = $this->createTestDocument();
        $doc->setServerState('published');
        $doc->setType('article');
        $doc->setLanguage('eng');
        $docId = $doc->store();

        $this->dispatch("/export/index/marc21/docId/{$docId}/searchtype/id");

        // revert configuration changes
        $this->restoreSecuritySetting();

        if ($removeAccess) {
            $this->removeModuleAccess('export', 'guest');
        }

        $this->assertResponseCode(401);
        $this->assertContains('Unauthorized: Access to module not allowed.', $this->getResponse()->getBody());
    }

    public function testMarc21XmlExportWithPublishedDocAllowedForAdmin()
    {
        $removeAccess = $this->addModuleAccess('export', 'docsadmin');
        $this->enableSecurity();

        $this->adjustConfiguration([
            'plugins'
                => [
                    'export'
                    => ['marc21' => ['adminOnly' => self::CONFIG_VALUE_TRUE]],
                ],
        ]);

        $doc = $this->createTestDocument();
        $doc->setServerState('published');
        $doc->setType('article');
        $doc->setLanguage('eng');
        $docId = $doc->store();

        $this->loginUser('security8', 'security8pwd');

        $this->dispatch("/export/index/marc21/docId/{$docId}/searchtype/id");

        // revert configuration changes
        $this->restoreSecuritySetting();

        if ($removeAccess) {
            $this->removeModuleAccess('export', 'docsadmin');
        }

        $this->assertResponseCode(200);

        $currentYear = date('Y');

        $this->assertXpathContentContains('//marc:leader', '00000naa a22000005  4500');
        $this->assertXpathContentContains('//marc:controlfield[@tag="001"]', 'docId-' . $docId);
        $this->assertXpathContentContains('//marc:controlfield[@tag="007"]', 'cr uuu---uunan');
        $this->assertXpathContentContains('//marc:datafield[@tag="041"]/marc:subfield[@code="a"]', 'eng');
        $this->assertXpathContentContains('//marc:datafield[@tag="264"]/marc:subfield[@code="c"]', $currentYear);
        $this->assertXpathContentContains('//marc:datafield[@tag="655"]/marc:subfield[@code="a"]', 'article');
        $this->assertXpathContentContains('//marc:datafield[@tag="856"]/marc:subfield[@code="u"]', 'http:///frontdoor/index/index/docId/' . $docId);
        $this->assertNotXpath('//marc:datafield[@tag="245"]');
    }

    public function testMarc21XmlExportWithPublishedDocAllowedForGuest()
    {
        $removeAccess = $this->addModuleAccess('export', 'guest');
        $this->enableSecurity();

        $this->adjustConfiguration([
            'plugins'
                => [
                    'export'
                    => ['marc21' => ['adminOnly' => self::CONFIG_VALUE_FALSE]],
                ],
        ]);

        $doc = $this->createTestDocument();
        $doc->setServerState('published');
        $doc->setType('article');
        $doc->setLanguage('eng');
        $docId = $doc->store();

        $this->dispatch("/export/index/marc21/docId/{$docId}/searchtype/id");

        // revert configuration changes
        $this->restoreSecuritySetting();

        if ($removeAccess) {
            $this->removeModuleAccess('export', 'guest');
        }

        $this->assertResponseCode(200);

        $currentYear = date('Y');

        $this->assertXpathContentContains('//marc:leader', '00000naa a22000005  4500');
        $this->assertXpathContentContains('//marc:controlfield[@tag="001"]', 'docId-' . $docId);
        $this->assertXpathContentContains('//marc:controlfield[@tag="007"]', 'cr uuu---uunan');
        $this->assertXpathContentContains('//marc:datafield[@tag="041"]/marc:subfield[@code="a"]', 'eng');
        $this->assertXpathContentContains('//marc:datafield[@tag="264"]/marc:subfield[@code="c"]', $currentYear);
        $this->assertXpathContentContains('//marc:datafield[@tag="655"]/marc:subfield[@code="a"]', 'article');
        $this->assertXpathContentContains('//marc:datafield[@tag="856"]/marc:subfield[@code="u"]', 'http:///frontdoor/index/index/docId/' . $docId);
        $this->assertNotXpath('//marc:datafield[@tag="245"]');
    }

    public function testMarc21XmlExportWithUnpublishedDocAllowedForNonAdminUserWithoutPermission()
    {
        $removeAccess = $this->addModuleAccess('export', 'collectionsadmin');
        $this->enableSecurity();

        $this->adjustConfiguration([
            'plugins'
                => [
                    'export'
                    => ['marc21' => ['adminOnly' => self::CONFIG_VALUE_FALSE]],
                ],
        ]);

        $doc = $this->createTestDocument();
        $doc->setServerState('unpublished');
        $doc->setType('article');
        $doc->setLanguage('eng');
        $docId = $doc->store();

        $this->loginUser('security9', 'security9pwd');

        $this->dispatch("/export/index/marc21/docId/{$docId}/searchtype/id");

        // revert configuration changes
        $this->restoreSecuritySetting();

        if ($removeAccess) {
            $this->removeModuleAccess('export', 'collectionsadmin');
        }

        $this->assertResponseCode(401);
        $this->assertContains('export of unpublished documents is not allowed', $this->getResponse()->getBody());
    }
}
