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
 * @package     Admin_Form
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2017, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

class Admin_Form_Person_ChangesTest extends ControllerTestCase
{

    public function testGetPreparedChanges()
    {
        $form = new Admin_Form_Person_Changes();

        $form->setOldValues(array(
            'LastName' => 'Mueller',
            'FirstName' => 'Michael',
            'IdentifierMisc' => null
        ));

        $form->setChanges(array(
            'FirstName' => 'Thomas',
            'IdentifierMisc' => 'id1234'
        ));

        $prepared = $form->getPreparedChanges();

        $this->assertNotNull($prepared);
        $this->assertInternalType('array', $prepared);
        $this->assertCount(3, $prepared);

        $this->assertArrayHasKey('LastName', $prepared);

        $lastName = $prepared['LastName'];
        $this->assertNotNull($lastName);
        $this->assertInternalType('array', $prepared);
        $this->assertCount(3, $lastName);

        $this->assertArrayHasKey('old', $lastName);
        $this->assertContains('Mueller', $lastName['old']);
        $this->assertCount(1, $lastName['old']);

        $this->assertArrayHasKey('new', $lastName);
        $this->assertContains('Mueller', $lastName['new']);
        $this->assertCount(1, $lastName['new']);

        $this->assertArrayHasKey('action', $lastName);
        $this->assertInternalType('string', $lastName['action']);
        $this->assertEquals('notmodified', $lastName['action']);
    }

    public function testGetPreparedChangesModified()
    {
        $form = new Admin_Form_Person_Changes();

        $form->setOldValues(array(
            'LastName' => 'Mueller',
            'FirstName' => 'Michael',
            'IdentifierMisc' => null
        ));

        $form->setChanges(array(
            'FirstName' => 'Thomas',
            'IdentifierMisc' => 'id1234'
        ));

        $prepared = $form->getPreparedChanges();

        $this->assertNotNull($prepared);
        $this->assertInternalType('array', $prepared);
        $this->assertCount(3, $prepared);
        $this->assertArrayHasKey('FirstName', $prepared);

        $firstName = $prepared['FirstName'];
        $this->assertNotNull($firstName);
        $this->assertInternalType('array', $prepared);
        $this->assertCount(3, $firstName);

        $this->assertArrayHasKey('old', $firstName);
        $this->assertContains('Michael', $firstName['old']);
        $this->assertCount(1, $firstName['old']);

        $this->assertArrayHasKey('new', $firstName);
        $this->assertContains('Thomas', $firstName['new']);
        $this->assertCount(1, $firstName['new']);

        $this->assertArrayHasKey('action', $firstName);
        $this->assertInternalType('string', $firstName['action']);
        $this->assertEquals('modified', $firstName['action']);
    }

    public function testGetPreparedChangesAdded()
    {
        $form = new Admin_Form_Person_Changes();

        $form->setOldValues(array(
            'LastName' => 'Mueller',
            'FirstName' => 'Michael',
            'IdentifierMisc' => null
        ));

        $form->setChanges(array(
            'FirstName' => 'Thomas',
            'IdentifierMisc' => 'id1234'
        ));

        $prepared = $form->getPreparedChanges();

        $this->assertNotNull($prepared);
        $this->assertInternalType('array', $prepared);
        $this->assertCount(3, $prepared);
        $this->assertArrayHasKey('IdentifierMisc', $prepared);

        $miscId = $prepared['IdentifierMisc'];
        $this->assertNotNull($miscId);
        $this->assertInternalType('array', $prepared);
        $this->assertCount(3, $miscId);

        $this->assertArrayHasKey('old', $miscId);
        $this->assertCount(0, $miscId['old']);

        $this->assertArrayHasKey('new', $miscId);
        $this->assertContains('id1234', $miscId['new']);
        $this->assertCount(1, $miscId['new']);

        $this->assertArrayHasKey('action', $miscId);
        $this->assertInternalType('string', $miscId['action']);
        $this->assertEquals('added', $miscId['action']);
    }

    public function testGetPreparedChangesMerged()
    {
        $form = new Admin_Form_Person_Changes();

        $form->setOldValues(array(
            'FirstName' => array('T.', 'Tom', 'Thomas')
        ));

        $form->setChanges(array(
            'FirstName' => 'Thomas',
        ));

        $prepared = $form->getPreparedChanges();

        $this->assertNotNull($prepared);
        $this->assertInternalType('array', $prepared);
        $this->assertCount(1, $prepared);

        $this->assertArrayHasKey('FirstName', $prepared);

        $firstName = $prepared['FirstName'];

        $this->assertNotNull($firstName);
        $this->assertInternalType('array', $prepared);
        $this->assertCount(3, $firstName);

        $this->assertArrayHasKey('old', $firstName);
        $this->assertCount(3, $firstName['old']);
        $this->assertContains('T.', $firstName['old']);
        $this->assertContains('Tom', $firstName['old']);
        $this->assertContains('Thomas', $firstName['old']);

        $this->assertArrayHasKey('new', $firstName);
        $this->assertCount(1, $firstName['new']);
        $this->assertContains('Thomas', $firstName['new']);

        $this->assertArrayHasKey('action', $firstName);
        $this->assertInternalType('string', $firstName['action']);
        $this->assertEquals('merged', $firstName['action']);
    }

    public function testRender()
    {
        $form = new Admin_Form_Person_Changes();

        $form->setOldValues(array(
            'LastName' => array('Muller', 'Mueller')
        ));

        $form->setChanges(array(
            'LastName' => array('Mueller')
        ));

        $output = $form->__toString();

        $this->getResponse()->setBody($output);

        $this->assertXpathContentContains('//th', 'admin_change_fieldname');
        $this->assertXpathContentContains('//th', 'admin_change_old_value');
        $this->assertXpathContentContains('//th', 'admin_change_new_value');

        $this->assertXpathContentContains('//td[@class = "fieldname"]', 'Last Name');
        $this->assertXpathContentContains('//td[@class = "old-value"]', 'Muller');
        $this->assertXpathContentContains('//td[@class = "old-value"]', 'Mueller');

        $this->assertXpathContentContains('//td[@class = "new-value"]', 'Mueller');

        // TODO more testing
    }

    public function testForceArray()
    {
        $form = new Admin_Form_Person_Changes();

        $values = $form->forceArray('test');

        $this->assertNotNull($values);
        $this->assertInternalType('array', $values);
        $this->assertCount(1, $values);
        $this->assertContains('test', $values);
    }

    public function testForceArrayForNull()
    {
        $form = new Admin_Form_Person_Changes();

        $values = $form->forceArray(null);

        $this->assertNotNull($values);
        $this->assertInternalType('array', $values);
        $this->assertCount(0, $values);
    }

    public function testForceArrayForArray()
    {
        $form = new Admin_Form_Person_Changes();

        $values = $form->forceArray(array('value1', 'value2'));

        $this->assertNotNull($values);
        $this->assertInternalType('array', $values);
        $this->assertCount(2, $values);
        $this->assertEquals(array('value1', 'value2'), $values);
    }

}