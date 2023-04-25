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
use Opus\Common\Licence;
use Opus\Common\Repository;

class Application_Update_AddCC30LicenceShotNamesTest extends ControllerTestCase
{
    /** @var string */
    protected $additionalResources = 'database';

    /** @var Application_Update_AddCC30LicenceShortNames */
    private $update;

    public function setUp(): void
    {
        parent::setUp();

        $this->update = new Application_Update_AddCC30LicenceShortNames();
        $this->update->setLogger(new MockLogger());
        $this->update->setQuietMode(true);
    }

    public function tearDown(): void
    {
    }

    /**
     * @return string[][]
     */
    public function licenceMatchProvider()
    {
        return [
            ['CC BY 3.0', 'Creative Commons - Namensnennung'],
            ['CC BY 3.0', 'Creative Commons-Namensnennung'],
            ['CC BY 3.0', 'Creative CommonsNamensnennung'],
            ['CC BY 3.0', 'CC Namensnennung'],
            ['CC BY-ND 3.0', 'Creative Commons - Namensnennung - Keine Bearbeitung'],
            ['CC BY-NC-ND 3.0', 'Creative Commons - Namensnennung - Nicht kommerziell - Keine Bearbeitung'],
            ['CC BY-NC-SA 3.0', 'Creative Commons - Namensnennung - Keine kommerzielle Nutzung - Weitergabe unter gleichen Bedingungen'],
            ['CC BY-NC-SA 3.0', 'Creative Commons - Namensnennung - Nicht kommerziell - Weitergabe unter gleichen Bedingungen'],
            ['CC BY-NC 3.0', 'Creative Commons - Namensnennung - Nicht kommerziell'],
            ['CC BY-ND 3.0', 'Creative Commons - Namensnennung - Keine Bearbeitung'],
            ['CC BY-ND 3.0', 'Creative Commons - Namensnennung - KeineBearbeitung'],
            ['CC BY-SA 3.0', 'Creative Commons - Namensnennung - Weitergabe unter gleichen Bedingungen'],
        ];
    }

    /**
     * @dataProvider licenceMatchProvider
     * @param string $expected
     * @param string $longName
     */
    public function testGetShortName($expected, $longName)
    {
        $this->assertEquals($expected, $this->update->getShortName($longName), $longName);
    }

    /**
     * @outputBuffering enabled
     */
    public function testUpdateLicenceWithoutVersion()
    {
        $licence = Licence::new();
        $licence->setNameLong('Creative Commons - Namensnennung');
        $licence->setLanguage('deu');
        $licence->setLinkLicence('http://opus4.kobv.org/test-licence');
        $licenceId = $licence->store();

        $licence = Licence::get($licenceId);
        $this->assertNull($licence->getName());

        $this->update->run();

        $licence = Licence::get($licenceId);
        $licence->delete();

        $this->assertEquals('CC BY 3.0', $licence->getName());
    }

    public function testDoNotUpdate40Licence()
    {
        $licence = Licence::new();
        $licence->setNameLong('Creative Commons 4.0 - Namensnennung');
        $licence->setLanguage('deu');
        $licence->setLinkLicence('http://opus4.kobv.org/test-licence');
        $licenceId = $licence->store();

        $licence = Licence::get($licenceId);
        $this->assertNull($licence->getName());

        $this->update->run();

        $licence = Licence::get($licenceId);
        $licence->delete();

        $this->assertNull($licence->getName());
    }

    public function testUpdateForUnknownLicence()
    {
        $licence = Licence::new();
        $licence->setNameLong('Custom licence');
        $licence->setLanguage('deu');
        $licence->setLinkLicence('http://opus4.kobv.org/test-licence');
        $licenceId = $licence->store();

        $licence = Licence::get($licenceId);
        $this->assertNull($licence->getName());

        $this->update->run();

        $licence = Licence::get($licenceId);
        $licence->delete();

        $this->assertNull($licence->getName());
    }

    public function testUpdateForLicenceWithShortName()
    {
        $licence = Licence::new();
        $licence->setName('CC BY 5.0');
        $licence->setNameLong('Creative Commons - Namensnennung');
        $licence->setLanguage('deu');
        $licence->setLinkLicence('http://opus4.kobv.org/test-licence');
        $licenceId = $licence->store();

        $this->update->run();

        $licence = Licence::get($licenceId);
        $licence->delete();

        $this->assertEquals('CC BY 5.0', $licence->getName());
    }

    public function testRemoveLicence()
    {
        $name = $this->update->getShortName('Creative Commons - Namensnennung');

        $this->assertEquals('CC BY 3.0', $name);

        $this->update->removeLicence($name);

        $name = $this->update->getShortName('Creative Commons - Namensnennung');

        $this->assertNull($name);
    }

    public function testDoNotUpdateServerDateModified()
    {
        $licence = Licence::new();
        $licence->setNameLong('Creative Commons - Namensnennung');
        $licence->setLanguage('deu');
        $licence->setLinkLicence('http://opus4.kobv.org/test-licence');
        $licenceId = $licence->store();

        $doc = $this->createTestDocument();

        $doc->addLicence($licence);
        $docId = $doc->store();

        $cache = Repository::getInstance()->getDocumentXmlCache();

        $this->assertNotNull($cache->getData($docId, '1.0'));

        $doc = Document::get($docId);

        $dateModified = $doc->getServerDateModified();

        sleep(2);

        $licence = Licence::get($licenceId);
        $this->assertNull($licence->getName());

        $this->update->run();

        $doc = Document::get($docId);

        $cacheResult = $cache->getData($docId, '1.0');

        // clean up licence first
        $licence = Licence::get($licenceId);
        $licence->delete();

        $this->assertNull($cacheResult);
        $this->assertEquals($dateModified->getUnixTimestamp(), $doc->getServerDateModified()->getUnixTimestamp());
        $this->assertEquals('CC BY 3.0', $licence->getName());
    }
}
