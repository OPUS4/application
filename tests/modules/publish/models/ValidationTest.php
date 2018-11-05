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
 * @category    Application
 * @package     Module_Publish Unit Test
 * @author      Susanne Gottwald <gottwald@zib.de>
 * @copyright   Copyright (c) 2008-2018, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

class Publish_Model_ValidationTest extends ControllerTestCase
{

    private $session;

    public function setUp()
    {
        parent::setUp();
        $this->session = new Zend_Session_Namespace();
    }

    public function testValidationWithInvalidDatatype()
    {
        $val = new Publish_Model_Validation('Irgendwas', $this->session);
        $val->validate();

        $this->assertInternalType('array', $val->validator);
    }

    public function testValidationWithCollectionWithoutCollectionRole()
    {
        $val = new Publish_Model_Validation('Collection', $this->session);
        $val->validate();
        $validator = $val->validator[0];

        $this->assertNull($validator);
    }

    public function testValidationWithDateDatatype()
    {
        $val = new Publish_Model_Validation('Date', $this->session);
        $val->validate();
        $validator = $val->validator[0];

        $this->assertInstanceOf('Zend_Validate_Date', $validator);
    }

    public function testValidationWithEmailDatatype()
    {
        $val = new Publish_Model_Validation('Email', $this->session);
        $val->validate();
        $validator = $val->validator[0];

        $this->assertInstanceOf('Zend_Validate_EmailAddress', $validator);
    }

    /**
     * TODO fix unused variable - What is this test doing?
     */
    public function testValidationWithEnrichmentDatatype()
    {
        $val = new Publish_Model_Validation('Enrichment', $this->session);
        $val->validate();
        $validator = $val->validator[0];

        $this->assertNull($val->validator);
    }

    public function testValidationWithIntegerDatatype()
    {
        $val = new Publish_Model_Validation('Integer', $this->session);
        $val->validate();
        $validator = $val->validator[0];

        $this->assertInstanceOf('Zend_Validate_Int', $validator);
    }

    public function testValidationWithLanguageDatatype()
    {
        $val = new Publish_Model_Validation('Language', $this->session);
        $val->validate();
        $validator = $val->validator[0];

        $this->assertInstanceOf('Zend_Validate_InArray', $validator);
    }

    public function testValidationWithLicenceDatatype()
    {
        $val = new Publish_Model_Validation('Licence', $this->session);
        $val->validate();
        $validator = $val->validator[0];

        $this->assertInstanceOf('Zend_Validate_InArray', $validator);
    }

    public function testValidationWithListDatatype()
    {
        $options = [];
        $options['eins'] = 'eins';
        $options['zwei'] = 'zwei';

        $val = new Publish_Model_Validation('List', $this->session, '', $options);
        $val->validate();
        $validator = $val->validator[0];

        $this->assertInstanceOf('Zend_Validate_InArray', $validator);
    }

    public function testValidationWithTextDatatype()
    {
        $val = new Publish_Model_Validation('Text', $this->session);
        $val->validate();

        $this->assertNull($val->validator);
    }

    public function testValidationWithThesisGrantorDatatype()
    {
        $val = new Publish_Model_Validation('ThesisGrantor', $this->session);
        $val->validate();
        $validator = $val->validator[0];

        $this->assertInstanceOf('Zend_Validate_InArray', $validator);
    }

    public function testValidationWithThesisPublisherDatatype()
    {
        $val = new Publish_Model_Validation('ThesisPublisher', $this->session);
        $val->validate();
        $validator = $val->validator[0];

        $this->assertInstanceOf('Zend_Validate_InArray', $validator);
    }

    public function testValidationWithTitleDatatype()
    {
        $val = new Publish_Model_Validation('Title', $this->session);
        $val->validate();

        $this->assertNull($val->validator);
    }

    public function testValidationWithYearDatatype()
    {
        $val = new Publish_Model_Validation('Year', $this->session);
        $val->validate();
        $validator = $val->validator[0];

        $this->assertInstanceOf('Zend_Validate_GreaterThan', $validator);
    }

    public function testSelectOptionsForInvalidDatatype()
    {
        $val = new Publish_Model_Validation('Irgendwas', $this->session);
        $children = $val->selectOptions();

        $this->assertInternalType('array', $val->validator);
    }

    public function testSelectOptionsForCollection()
    {
        $val = new Publish_Model_Validation('Collection', $this->session, 'jel');
        $children = $val->selectOptions('Collection');

        $this->assertArrayHasKey('6720', $children);
    }

    public function testSelectOptionsForLanguage()
    {
        $val = new Publish_Model_Validation('Language', $this->session);
        $children = $val->selectOptions();

        $this->assertArrayHasKey('deu', $children);
    }

    public function testSelectOptionsForLicence()
    {
        $val = new Publish_Model_Validation('Licence', $this->session);
        $children = $val->selectOptions();

        $this->assertArrayHasKey('4', $children);
    }

    /**
     * Tests that the sort order of the licences in the publish form matches
     * the sort order provided from the database.
     */
    public function testSortOrderOfSelectOptionForLicence()
    {
        $licences = Opus_Licence::getAll();

        $activeLicences = [];

        foreach($licences as $licence) {
            if ($licence->getActive() == '1') {
                $activeLicences[] = $licence->getDisplayName();
            }
        }

        $val = new Publish_Model_Validation('Licence', $this->session);
        $values = $val->selectOptions();

        $this->assertEquals( count($values), count($activeLicences));

        $pos = 0;

        foreach ($values as $name) {
            $this->assertEquals($name, $activeLicences[$pos]);
            $pos++;
        }
    }

    public function testSelectOptionsForList()
    {
        $options = [];
        $options['eins'] = 'eins';
        $options['zwei'] = 'zwei';

        $val = new Publish_Model_Validation('List', $this->session, '', $options);
        $children = $val->selectOptions();

        $this->assertArrayHasKey('eins', $children);
    }

     public function testSelectOptionsForThesisGrantor()
     {
        $val = new Publish_Model_Validation('ThesisGrantor', $this->session);
        $children = $val->selectOptions();

        $this->assertArrayHasKey('1', $children);
    }

    public function testSelectOptionsForThesisPublisher()
    {
        $val = new Publish_Model_Validation('ThesisPublisher', $this->session);
        $children = $val->selectOptions();

        $this->assertArrayHasKey('2', $children);
    }

    public function testInvisibleCollectionRoleDDC()
    {
        $val = new Publish_Model_Validation('Collection', $this->session, 'ddc');

        $collectionRole = Opus_CollectionRole::fetchByName($val->collectionRole);
        $visibleFlag = $collectionRole->getVisible();
        $collectionRole->setVisible(0);
        $collectionRole->store();

        $children = $val->selectOptions('Collection');
        $this->assertNull($children);

        $collectionRole->setVisible($visibleFlag);
        $collectionRole->store();
    }

    public function testVisibleCollectionRoleDDC()
    {
        $val = new Publish_Model_Validation('Collection', $this->session, 'ddc');

        $collectionRole = Opus_CollectionRole::fetchByName($val->collectionRole);
        $visibleFlag = $collectionRole->getVisible();
        $collectionRole->setVisible(1);
        $collectionRole->store();

        $children = $val->selectOptions('Collection');
        $this->assertInternalType('array', $children);
        $this->assertArrayHasKey('3', $children);

        $collectionRole->setVisible($visibleFlag);
        $collectionRole->store();
    }

    /**
     * Regression Test for Ticket https://wiki.kobv.de/jira/browse/OPUSVIER-2209
     */
    public function testNonExistingCollectionRole()
    {
        $collRole = 'irgendwas';
        $val = new Publish_Model_Validation('Collection', $this->session, $collRole);

        $this->assertNull($val->selectOptions());
    }

    public function testVisibleSeries()
    {
        $val = new Publish_Model_Validation('Series', $this->session);

        $children = $val->selectOptions('Series');
        $this->assertInternalType('array', $children);
        $this->assertArrayHasKey('4', $children);
        //series with title: Visible Series
    }

    public function testInvisibleSeries()
    {
        $val = new Publish_Model_Validation('Series', $this->session);

        $children = $val->selectOptions('Series');
        $this->assertInternalType('array', $children);
        $this->assertArrayNotHasKey('3', $children);
        //series with title: Invisible Series
    }

    public function testSortOrderOfSeries()
    {
        $val = new Publish_Model_Validation('Series', $this->session);
        $values = $val->selectOptions();

        $series = Opus_Series::getAllSortedBySortKey();

        $visibleSeries = [];

        foreach($series as $serie) {
            if ($serie->getVisible() == '1') {
                $visibleSeries[] = $serie->getTitle();
            }
        }

        $this->assertEquals( count($values), count($visibleSeries));

        $index = 0;
        foreach ($values as $name) {
            $this->assertEquals($name, $visibleSeries[$index]);
            $index++;
        }
    }

    /**
     * Testet, ob eine Collection, bei der visiblePublish=false gesetzt ist, im Publish-Modul ausgegeben wird.
     */
    public function testCollectionFieldVisiblePublish()
    {
        $collectionRole = new Opus_CollectionRole();
        $collectionRole->setName("test");
        $collectionRole->setOaiName("test");
        $collectionRole->setDisplayBrowsing("Name");
        $collectionRole->setDisplayFrontdoor("Name");
        $collectionRole->setPosition(101);
        $collectionRole->setVisible(true);
        $collectionRole->store();

        $rootCollection = $collectionRole->addRootCollection();
        $rootCollection->store();

        $invisibleCollection = new Opus_Collection();
        $invisibleCollection->setName("invisible collection");
        $invisibleCollection->setNumber("123");
        $invisibleCollection->setVisible(true);
        $invisibleCollection->setVisiblePublish(false);
        $rootCollection->addFirstChild($invisibleCollection);
        $invisibleCollection->store();

        $visibleCollection = new Opus_Collection();
        $visibleCollection->setName("visible collection");
        $visibleCollection->setNumber("987");
        $visibleCollection->setVisiblePublish(true);
        $visibleCollection->setVisible(true);
        $rootCollection->addLastChild($visibleCollection);
        $visibleId = $visibleCollection->store();

        $mixedVisibilityCollection = new Opus_Collection();
        $mixedVisibilityCollection->setName("mixed visibility");
        $mixedVisibilityCollection->setNumber("456");
        $mixedVisibilityCollection->setVisiblePublish(true);
        $mixedVisibilityCollection->setVisible(false);
        $rootCollection->addLastChild($mixedVisibilityCollection);
        $mixedVisibilityCollection->store();

        $val = new Publish_Model_Validation('Collection', $this->session, 'test');
        $children = $val->selectOptions('Collection');

        // clean-up
        $collectionRole->delete();

        $this->assertEquals(1, count($children), "only 'visible collection' has the correct visibility settings");
        $this->assertEquals('visible collection', $children[$visibleId]);
    }

    /**
     * Wenn eine übergeordnete Collection (z.B. die Root-Collection) für das Attribut visiblePublish = false gesetzt ist,
     * sollen die Kinder auch unsichtbar sein im Publish-Modul.
     */
    public function testRootCollectionFieldVisiblePublish()
    {
        $collectionRole = new Opus_CollectionRole();
        $collectionRole->setName("test");
        $collectionRole->setOaiName("test");
        $collectionRole->setDisplayBrowsing("Name");
        $collectionRole->setDisplayFrontdoor("Name");
        $collectionRole->setPosition(101);
        $collectionRole->setVisible(true);
        $collectionRole->store();

        $rootCollection = $collectionRole->addRootCollection();
        $rootCollection->setName("rootInvisible");
        $rootCollection->setVisible(true);
        $rootCollection->setVisiblePublish(false);
        $rootCollection->store();

        $visibleCollection = new Opus_Collection();
        $visibleCollection->setName("visible collection");
        $visibleCollection->setNumber("123");
        $visibleCollection->setVisible(true);
        $visibleCollection->setVisiblePublish(true);
        $rootCollection->addFirstChild($visibleCollection);
        $visibleCollection->store();

        $invisibleCollection = new Opus_Collection();
        $invisibleCollection->setName("collection to invisible root collection");
        $invisibleCollection->setNumber("123");
        $invisibleCollection->setVisible(true);
        $invisibleCollection->setVisiblePublish(false);
        $rootCollection->addFirstChild($invisibleCollection);
        $invisibleCollection->store();

        $childCollection = new Opus_Collection();
        $childCollection->setName("collection child");
        $childCollection->setNumber("123");
        $childCollection->setVisible(true);
        $childCollection->setVisiblePublish(true);
        $invisibleCollection->addFirstChild($childCollection);
        $childCollection->store();

        $val = new Publish_Model_Validation('Collection', $this->session, 'test');
        $children = $val->selectOptions('Collection');

        // clean-up
        $collectionRole->delete();

        $this->assertEquals(0, count($children), "root collection should be invisible in publish");
    }
}
