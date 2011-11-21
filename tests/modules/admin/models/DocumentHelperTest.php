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
 * along with OPUS; >if not, write to the Free Software Foundation, Inc., 51
 * Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * @category    Application Unit Tests
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class Admin_Model_DocumentHelperTest extends ControllerTestCase {

    public function testGetFieldNamesForGroupGeneral() {
        $doc = new Opus_Document();

        $helper = new Admin_Model_DocumentHelper($doc);

        $fieldNames = $helper->getFieldNamesForGroup('general');

        $this->assertEquals(3, count($fieldNames));
        $this->assertContains('Language', $fieldNames);
        $this->assertContains('ServerState', $fieldNames);
        $this->assertContains('Type', $fieldNames);
    }

    public function testGetFieldsForGroupGeneralWithFiltering() {
        $doc = new Opus_Document();

        $helper = new Admin_Model_DocumentHelper($doc);

        $fields = $helper->getFieldsForGroup('general', true);

        $this->assertEquals(0, count($fields));
    }

    public function testGetFieldsForGroupGeneralWithoutFiltering() {
        $doc = new Opus_Document();

        $helper = new Admin_Model_DocumentHelper($doc);

        $fields = $helper->getFieldsForGroup('general', false);

        $this->assertEquals(3, count($fields));
    }

    public function testGetFieldsForGroupGeneralWithFilterungForTestDoc() {
        $doc = new Opus_Document(40);

        $helper = new Admin_Model_DocumentHelper($doc);

        $fields = $helper->getFieldsForGroup('general', false);

        $this->assertEquals(3, count($fields));
    }

    public function testGetGroups() {
        $doc = new Opus_Document();

        $helper = new Admin_Model_DocumentHelper($doc);

        $groups = $helper->getGroups();

        $this->assertEquals(16, count($groups));
        $this->assertContains('dates', $groups);
        $this->assertContains('general', $groups);
    }

    /**
     * This test makes sure all configured fields in 'sections.ini' actually
     * exist for Opus_Document.
     */
    public function testIfAllConfiguredFieldsExistInOpusDocument() {
        $doc = new Opus_Document();

        $helper = new Admin_Model_DocumentHelper($doc);

        $groups = $helper->getGroups();

        foreach ($groups as $section) {
            $fields = $helper->getFieldNamesForGroup($section);

            foreach($fields as $fieldName) {
                $this->assertNotNull($doc->getField($fieldName), 'Field \'' . $fieldName . '\' does not exist for Opus_Document.');
            }
        }
    }

    public function testHasValueForEmptySection() {
        $doc = new Opus_Document(40);

        $helper = new Admin_Model_DocumentHelper($doc);

        $this->assertFalse($helper->hasValues('patents'));
    }

    public function testHasValueForSectionWithValues() {
        $doc = new Opus_Document(40);

        $helper = new Admin_Model_DocumentHelper($doc);

        $this->assertTrue($helper->hasValues('identifiers'));
    }

    public function testHasValueForEmtpyDocAllSections() {
        $doc = new Opus_Document();

        $helper = new Admin_Model_DocumentHelper($doc);

        $sections = $helper->getGroups();

        foreach($sections as $section) {
            $this->assertFalse($helper->hasValues($section), 'Section \''
                    . $section . '\ should be empty.');
        }
    }

    /**
     * Test that getFields returns the fields of a model exluding empty ones.
     */
    public function testGetFields() {
        $doc = new Opus_Document();

        $helper = new Admin_Model_DocumentHelper($doc);

        $person = new Opus_Person();

        $person->setFirstName('John');
        $person->setLastName('Doe');
        $person->setEmail('john@test.org.dummy');

        $doc->addPerson($person);

        $persons = $doc->getPerson();

        $person = $persons[0];

        $fields = $helper->getFields($person);

        $this->assertNotEmpty($fields);
        $this->assertTrue(count($fields) == 4, count($fields));

        $keys = array_keys($fields);

        // Check that the expected fields are present
        $this->assertContains('FirstName', $keys);
        $this->assertContains('LastName', $keys);
        $this->assertContains('SortOrder', $keys);
        $this->assertContains('Email', $keys);

        // Check that each key is associated with the matching field
        foreach($fields as $name => $field) {
            $this->assertEquals($name, $field->getName());
        }
    }

    public function testIsValidGroupTrue() {
       $groups = Admin_Model_DocumentHelper::getGroups();

       foreach ($groups as $group) {
           $this->assertTrue(Admin_Model_DocumentHelper::isValidGroup($group),
                   'Group name \'' . $group . '\' should be valid.');
       }
    }

    public function testIsValidGroupFalse() {
        $this->assertFalse(
                Admin_Model_DocumentHelper::isValidGroup('doesnotexist'));
    }

    public function testIsValidGroupWithNull() {
        $this->assertFalse(
                Admin_Model_DocumentHelper::isValidGroup(null));
    }

}

?>