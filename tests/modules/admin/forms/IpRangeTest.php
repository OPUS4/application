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
 * @category    TODO
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Basic unit tests for IP range form.
 */
class Admin_Form_IpRangeTest extends ControllerTestCase {

    /**
     * Test creating an IP range form.
     */
    public function testCreateForm() {
        $form = new Admin_Form_IpRange();
        $this->assertNotNull($form);
    }

    /**
     * Test parsing selected roles from POST data.
     */
    public function testParseSelectedRoles() {
        $postData = array();
        $postData['roleadministrator'] = '1';
        $postData['roleguest'] = '0';

        $roles = Admin_Form_IpRange::parseSelectedRoles($postData);

        $this->assertNotNull($roles);
        $this->assertEquals(1, count($roles));
        $this->assertEquals('administrator', $roles[0]->getDisplayName());
    }

    /**
     * Test setting selected roles.
     */
    public function testSetSelectedRoles() {
        $form = new Admin_Form_IpRange();

        $roles = array();

        $roles[] = Opus_UserRole::fetchByName('administrator');

        $form->setSelectedRoles($roles);

        $this->assertEquals(1, $form->getElement('roleadministrator')->getValue());
        // TODO 'guest' is always selected because of policy
        $this->assertEquals(1, $form->getElement('roleguest')->getValue());
    }

    public function testValidateValidIp() {
        $form = new Admin_Form_IpRange();

        $postData = array(
            'name' => 'ValidIpTest',
            'startingip' => '127.0.0.1',
            'endingip' => '127.0.0.2');

        $this->assertTrue($form->isValid($postData));

        $errors = $form->getElement('startingip')->getErrors();

        $this->assertTrue(empty($errors));

        $errors = $form->getElement('endingip')->getErrors();

        $this->assertTrue(empty($errors));
    }

    public function testValidateInvalidIpShortStartingIp() {
        $form = new Admin_Form_IpRange();

        $postData = array(
            'name' => 'ValidIpTest',
            'startingip' => '127.0.1',
            'endingip' => '127.0.0.2');

        $this->assertFalse($form->isValid($postData));

        $errors = $form->getElement('startingip')->getErrors();

        $this->assertFalse(empty($errors));
        $this->assertTrue($errors[0] === 'notIpAddress');

        $errors = $form->getElement('endingip')->getErrors();

        $this->assertTrue(empty($errors));
    }

    public function testValidateInvalidIpShortEndingIp() {
        $form = new Admin_Form_IpRange();

        $postData = array(
            'name' => 'ValidIpTest',
            'startingip' => '127.0.0.1',
            'endingip' => '127.0.2');

        $this->assertFalse($form->isValid($postData));

        $errors = $form->getElement('startingip')->getErrors();

        $this->assertTrue(empty($errors));

        $errors = $form->getElement('endingip')->getErrors();

        $this->assertFalse(empty($errors));
        $this->assertTrue($errors[0] === 'notIpAddress');
    }

    public function testValidateInvalidIpHostname() {
        $form = new Admin_Form_IpRange();

        $postData = array(
            'name' => 'ValidIpTest',
            'startingip' => 'opus4.kobv.de',
            'endingip' => 'opus4.kobv.de');

        $this->assertFalse($form->isValid($postData));

        $errors = $form->getElement('startingip')->getErrors();

        $this->assertFalse(empty($errors));
        $this->assertTrue($errors[0] === 'notIpAddress');

        $errors = $form->getElement('endingip')->getErrors();

        $this->assertFalse(empty($errors));
        $this->assertTrue($errors[0] === 'notIpAddress');
    }

    public function testValidateInvalidIpV6() {
        $form = new Admin_Form_IpRange();

        $postData = array(
            'name' => 'ValidIpTest',
            'startingip' => '2001:0db8:85a3:0000:0000:8a2e:0370:7334',
            'endingip' => '2001:0db8:85a3:0000:0000:8a2e:0370:7334');

        $this->assertFalse($form->isValid($postData));

        $errors = $form->getElement('startingip')->getErrors();

        $this->assertFalse(empty($errors));
        $this->assertTrue($errors[0] === 'notIpAddress');

        $errors = $form->getElement('endingip')->getErrors();

        $this->assertFalse(empty($errors));
        $this->assertTrue($errors[0] === 'notIpAddress');
    }

}

