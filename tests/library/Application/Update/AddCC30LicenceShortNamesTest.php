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
 * @category    Application Unit Test
 * @package     Application_Update
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2017, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */
class Application_Update_AddCC30LicenceShotNamesTest extends ControllerTestCase
{

    private $_update = null;

    public function setUp()
    {
        parent::setUp();

        $this->_update = new Application_Update_AddCC30LicenceShortNames();
        $this->_update->setLogger(new MockLogger());
        $this->_update->setQuietMode(true);
    }

    public function tearDown()
    {

    }

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
            ['CC BY-SA 3.0', 'Creative Commons - Namensnennung - Weitergabe unter gleichen Bedingungen']
        ];
    }

    /**
     * @dataProvider licenceMatchProvider
     */
    public function testGetShortName($expected, $longName)
    {
        $this->assertEquals($expected, $this->_update->getShortName($longName), $longName);
    }

    /**
     * @outputBuffering enabled
     */
    public function testUpdateLicenceWithoutVersion()
    {
        $licence = new Opus_Licence();
        $licence->setNameLong('Creative Commons - Namensnennung');
        $licence->setLanguage('deu');
        $licence->setLinkLicence('http://opus4.kobv.org/test-licence');
        $licenceId = $licence->store();

        $licence = new Opus_Licence($licenceId);
        $this->assertNull($licence->getName());

        $this->_update->run();

        $licence = new Opus_Licence($licenceId);
        $licence->delete();

        $this->assertEquals('CC BY 3.0', $licence->getName());
    }

    public function testDoNotUpdate40Licence()
    {
        $licence = new Opus_Licence();
        $licence->setNameLong('Creative Commons 4.0 - Namensnennung');
        $licence->setLanguage('deu');
        $licence->setLinkLicence('http://opus4.kobv.org/test-licence');
        $licenceId = $licence->store();

        $licence = new Opus_Licence($licenceId);
        $this->assertNull($licence->getName());

        $this->_update->run();

        $licence = new Opus_Licence($licenceId);
        $licence->delete();

        $this->assertNull($licence->getName());
    }

    public function testUpdateForUnknownLicence()
    {
        $licence = new Opus_Licence();
        $licence->setNameLong('Custom licence');
        $licence->setLanguage('deu');
        $licence->setLinkLicence('http://opus4.kobv.org/test-licence');
        $licenceId = $licence->store();

        $licence = new Opus_Licence($licenceId);
        $this->assertNull($licence->getName());

        $this->_update->run();

        $licence = new Opus_Licence($licenceId);
        $licence->delete();

        $this->assertNull($licence->getName());
    }

    public function testUpdateForLicenceWithShortName()
    {
        $licence = new Opus_Licence();
        $licence->setName('CC BY 5.0');
        $licence->setNameLong('Creative Commons - Namensnennung');
        $licence->setLanguage('deu');
        $licence->setLinkLicence('http://opus4.kobv.org/test-licence');
        $licenceId = $licence->store();

        $this->_update->run();

        $licence = new Opus_Licence($licenceId);
        $licence->delete();

        $this->assertEquals('CC BY 5.0', $licence->getName());
    }

    public function testRemoveLicence()
    {
        $name = $this->_update->getShortName('Creative Commons - Namensnennung');

        $this->assertEquals('CC BY 3.0', $name);

        $this->_update->removeLicence($name);

        $name = $this->_update->getShortName('Creative Commons - Namensnennung');

        $this->assertNull($name);
    }

    public function testDoNotUpdateServerDateModified()
    {
        $licence = new Opus_Licence();
        $licence->setNameLong('Creative Commons - Namensnennung');
        $licence->setLanguage('deu');
        $licence->setLinkLicence('http://opus4.kobv.org/test-licence');
        $licenceId = $licence->store();

        $doc = $this->createTestDocument();

        $doc->addLicence($licence);
        $docId = $doc->store();

        $cache = new Opus_Model_Xml_Cache();

        $this->assertNotNull($cache->getData($docId, '1.0'));

        $doc = new Opus_Document($docId);

        $dateModified = $doc->getServerDateModified();

        sleep(2);

        $licence = new Opus_Licence($licenceId);
        $this->assertNull($licence->getName());

        $this->_update->run();

        $doc = new Opus_Document($docId);

        $cacheResult = $cache->getData($docId, '1.0');

        // clean up licence first
        $licence = new Opus_Licence($licenceId);
        $licence->delete();

        $this->assertNull($cacheResult);
        $this->assertEquals($dateModified->getUnixTimestamp(), $doc->getServerDateModified()->getUnixTimestamp());
        $this->assertEquals('CC BY 3.0', $licence->getName());

    }

}
