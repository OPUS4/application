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

use Opus\Common\Document;

class Oai_Model_XmlFactoryTest extends ControllerTestCase
{
    /** @var string */
    protected $additionalResources = 'database';

    /** @var Oai_Model_XmlFactory */
    private $xmlFactory;

    public function setUp(): void
    {
        parent::setUp();

        $this->xmlFactory = new Oai_Model_XmlFactory();
    }

    public function testGetAccessRights()
    {
        $doc = $this->createTestDocument();

        // document with no files
        $this->assertEquals('info:eu-repo/semantics/closedAccess', $this->xmlFactory->getAccessRights($doc));

        $file = $this->createOpusTestFile('article.pdf');
        $file->setVisibleInOai(1);
        $file->setVisibleInFrontdoor(1);
        $doc->addFile($file);
        $doc = Document::get($doc->store()); // store and get fresh object

        // document with file accessible in OAI and frontdoor
        $this->assertEquals('info:eu-repo/semantics/openAccess', $this->xmlFactory->getAccessRights($doc));

        $file = $doc->getFile(0);
        $file->setVisibleInOai(0);
        $doc->store();

        // document with file accessible in frontdoor, but not OAI
        $this->assertEquals('info:eu-repo/semantics/restrictedAccess', $this->xmlFactory->getAccessRights($doc));

        $file->setVisibleInFrontdoor(0);
        $doc->store();

        // document with file that is not visible in frontdoor or OAI
        $this->assertEquals('info:eu-repo/semantics/closedAccess', $this->xmlFactory->getAccessRights($doc));

        $file2 = $this->createOpusTestFile('article.doc');
        $file2->setVisibleInOai(1);
        $file2->setVisibleInFrontdoor(1);
        $doc->addFile($file2);
        $doc = Document::get($doc->store());

        $this->assertCount(2, $doc->getFile());

        // document with two files, one file accessible in OAI and frontdoor
        $this->assertEquals('info:eu-repo/semantics/openAccess', $this->xmlFactory->getAccessRights($doc));

        $file2 = $doc->getFile(1);
        $file2->setVisibleInOai(0);
        $doc->store();

        // document with two files, one file accessible in frontdoor
        $this->assertEquals('info:eu-repo/semantics/restrictedAccess', $this->xmlFactory->getAccessRights($doc));

        $file2->setVisibleInOai(1);
        $file2->setVisibleInFrontdoor(0);
        $doc->store();

        // document with two files, none visible in frontdoor, one visible in OAI
        // TODO file is accessible
        $this->assertEquals('info:eu-repo/semantics/closedAccess', $this->xmlFactory->getAccessRights($doc));
    }

    public function testGetAccessRightsEmbargoedAccess()
    {
        $doc = $this->createTestDocument();

        $tomorrow  = new DateTime('tomorrow');
        $yesterday = new DateTime('yesterday');

        $doc->setEmbargoDate($tomorrow->format('Y-m-d'));

        // embargoed access for document with or without files
        $this->assertFalse($doc->hasEmbargoPassed());
        $this->assertEquals('info:eu-repo/semantics/embargoedAccess', $this->xmlFactory->getAccessRights($doc));

        $doc->setEmbargoDate($yesterday->format('Y-m-d'));

        // closed access for document without files after embargo is over
        $this->assertTrue($doc->hasEmbargoPassed());
        $this->assertEquals('info:eu-repo/semantics/closedAccess', $this->xmlFactory->getAccessRights($doc));
    }
}
