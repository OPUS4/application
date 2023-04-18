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
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\Iprange;
use Opus\Common\UserRole;

/**
 * Basic unit tests for IP range form.
 */
class Admin_Form_IpRangeTest extends ControllerTestCase
{
    /** @var string[] */
    protected $additionalResources = ['database'];

    /** @var int */
    private $modelId;

    public function setUp(): void
    {
        parent::setUp();

        $model = Iprange::new();
        $model->setName('localhost');
        $model->setStartingIp('127.0.0.1');
        $model->setEndingIp('127.0.0.2');
        $this->modelId = $model->store();
    }

    public function tearDown(): void
    {
        if ($this->modelId !== null) {
            $range = Iprange::get($this->modelId);
            $range->delete();
        }

        parent::tearDown();
    }

    public function testConstructForm()
    {
        $form = new Admin_Form_IpRange();

        $this->assertEquals(7, count($form->getElements()));

        $this->assertNotNull($form->getElement('Name'));
        $this->assertNotNull($form->getElement('Startingip'));
        $this->assertNotNull($form->getElement('Endingip'));
        $this->assertNotNull($form->getElement('Roles'));

        $this->assertNotNull($form->getElement('Id'));
        $this->assertNotNull($form->getElement('Save'));
        $this->assertNotNull($form->getElement('Cancel'));
    }

    public function testPopulateFromModel()
    {
        $form = new Admin_Form_IpRange();

        $range = Iprange::new();
        $range->setName('localhost');
        $range->setStartingIp('127.0.0.1');
        $range->setEndingIp('127.0.0.100');
        $range->setRole([
            UserRole::fetchByName('docsadmin'),
            UserRole::fetchByName('jobaccess'),
        ]);

        $form->populateFromModel($range);

        $this->assertEquals('localhost', $form->getElement('Name')->getValue());
        $this->assertEquals('127.0.0.1', $form->getElement('Startingip')->getValue());
        $this->assertEquals('127.0.0.100', $form->getElement('Endingip')->getValue());

        $roles = $form->getElement('Roles')->getValue();

        $this->assertNotNull($roles);
        $this->assertCount(2, $roles);
        $this->assertContains('docsadmin', $roles);
        $this->assertContains('jobaccess', $roles);
    }

    public function testPopulateFromModelWithIp()
    {
        $form = new Admin_Form_IpRange();

        $model = Iprange::get($this->modelId);

        $form->populateFromModel($model);

        $this->assertEquals($this->modelId, $form->getElement('Id')->getValue());
    }

    public function testUpdateModel()
    {
        $form = new Admin_Form_IpRange();

        $form->getElement('Id')->setValue(99);
        $form->getElement('Name')->setValue('localhost');
        $form->getElement('Startingip')->setValue('127.0.0.1');
        $form->getElement('Endingip')->setValue('127.0.0.3');

        $model = Iprange::new();

        $form->updateModel($model);

        $this->assertNull($model->getId()); // ID won't be set in update
        $this->assertEquals('localhost', $model->getName());
        $this->assertEquals('127.0.0.1', $model->getStartingIp());
        $this->assertEquals('127.0.0.3', $model->getEndingIp());
    }

    public function testValidationEmptyPost()
    {
        $form = new Admin_Form_IpRange();

        $this->assertFalse($form->isValid([]));

        $this->assertContains('isEmpty', $form->getErrors('Name'));
        $this->assertContains('isEmpty', $form->getErrors('Startingip'));
        $this->assertContains('ipInvalid', $form->getErrors('Startingip'));
    }

    public function testValidationEmptyFields()
    {
        $form = new Admin_Form_IpRange();

        $this->assertFalse($form->isValid([
            'Name'       => '  ',
            'Startingip' => '  ',
        ]));

        $this->assertContains('isEmpty', $form->getErrors('Name'));
        $this->assertContains('isEmpty', $form->getErrors('Startingip'));
    }

    public function testValidationTrue()
    {
        $form = new Admin_Form_IpRange();

        $postData = [
            'Name'       => 'ValidIpTest',
            'Startingip' => '127.0.0.1',
            'Endingip'   => '127.0.0.2',
            'Roles'      => ['docsadmin', 'reviewer'],
        ];

        $this->assertTrue($form->isValid($postData));

        $this->assertEmpty($form->getErrors('Name'));
        $this->assertEmpty($form->getErrors('Startingip'));
        $this->assertEmpty($form->getErrors('Endingip'));
    }

    public function testValidationTrueWithoutOptionalFields()
    {
        $form = new Admin_Form_IpRange();

        $postData = [
            'Name'       => 'ValidIpTest',
            'Startingip' => '127.0.0.1',
        ];

        $this->assertTrue($form->isValid($postData));

        $this->assertEmpty($form->getErrors('Name'));
        $this->assertEmpty($form->getErrors('Startingip'));
        $this->assertEmpty($form->getErrors('Endingip'));
    }

    public function testValidationInvalidName()
    {
        $form = new Admin_Form_IpRange();

        $this->assertFalse($form->isValid([
            'Name'       => '0local',
            'Startingip' => '127.0.0.1',
        ]));

        $this->assertEmpty($form->getErrors('Startingip'));

        $this->assertContains('regexNotMatch', $form->getErrors('Name'));
    }

    public function testValidationInvalidNameTooShort()
    {
        $form = new Admin_Form_IpRange();

        $this->assertFalse($form->isValid([
            'Name'       => 'To',
            'Startingip' => '127.0.0.1',
        ]));

        $this->assertEmpty($form->getErrors('Startingip'));

        $this->assertContains('stringLengthTooShort', $form->getErrors('Name'));
    }

    public function testValidationInvalidNameTooLong()
    {
        $form = new Admin_Form_IpRange();

        $this->assertFalse($form->isValid([
            'Name'       => 'To12345678901234567890',
            'Startingip' => '127.0.0.1',
        ]));

        $this->assertEmpty($form->getErrors('Startingip'));

        $this->assertContains('stringLengthTooLong', $form->getErrors('Name'));
    }

    public function testValidationInvalidIp()
    {
        $form = new Admin_Form_IpRange();

        $postData = [
            'Name'       => 'ValidIpTest',
            'Startingip' => '127.0.1',
            'Endingip'   => '127.0.0.2',
        ];

        $this->assertFalse($form->isValid($postData));

        $this->assertEmpty($form->getErrors('Name'));
        $this->assertContains('notIpAddress', $form->getErrors('Startingip'));
        $this->assertEmpty($form->getErrors('Endingip'));
    }

    public function testValidationInvalidEndingIp()
    {
        $form = new Admin_Form_IpRange();

        $postData = [
            'Name'       => 'ValidIpTest',
            'Startingip' => '127.0.0.1',
            'Endingip'   => '1a7.0.2.0',
        ];

        $this->assertFalse($form->isValid($postData));

        $this->assertEmpty($form->getErrors('Name'));
        $this->assertEmpty($form->getErrors('Startingip'));
        $this->assertContains('notIpAddress', $form->getErrors('Endingip'));
    }

    public function testValidationInvalidIpHostname()
    {
        $form = new Admin_Form_IpRange();

        $postData = [
            'Name'       => 'ValidIpTest',
            'Startingip' => 'opus4.kobv.de',
            'Endingip'   => 'opus4.kobv.de',
        ];

        $this->assertFalse($form->isValid($postData));

        $this->assertEmpty($form->getErrors('Name'));
        $this->assertContains('notIpAddress', $form->getErrors('Startingip'));
        $this->assertContains('notIpAddress', $form->getErrors('Endingip'));
    }

    public function testValidationInvalidIpV6()
    {
        $form = new Admin_Form_IpRange();

        $postData = [
            'Name'       => 'ValidIpTest',
            'Startingip' => '2001:0db8:85a3:0000:0000:8a2e:0370:7334',
            'Endingip'   => '2001:0db8:85a3:0000:0000:8a2e:0370:7334',
        ];

        $this->assertFalse($form->isValid($postData));

        $this->assertEmpty($form->getErrors('Name'));
        $this->assertContains('notIpAddress', $form->getErrors('Startingip'));
        $this->assertContains('notIpAddress', $form->getErrors('Endingip'));
    }

    public function testValidationFalseUnknownRoles()
    {
        $form = new Admin_Form_IpRange();

        $postData = [
            'Name'       => 'ValidIpTest',
            'Startingip' => '127.0.0.1',
            'Endingip'   => '127.0.0.2',
            'Roles'      => ['docsadmin', 'unknown'],
        ];

        $this->assertFalse($form->isValid($postData));

        $this->assertEmpty($form->getErrors('Name'));
        $this->assertEmpty($form->getErrors('Startingip'));
        $this->assertEmpty($form->getErrors('Endingip'));
        $this->assertContains('notInArray', $form->getErrors('Roles'));
    }

    public function testTranslation()
    {
        $this->application->bootstrap('translation');

        $form = new Admin_Form_IpRange();

        $translator = $form->getTranslator();

        $this->assertTrue($translator->isTranslated('validation_error_iprange_name_regexNotMatch'));
        $this->assertTrue($translator->isTranslated('validation_error_stringLengthTooShort'));
        $this->assertTrue($translator->isTranslated('validation_error_stringLengthTooLong'));
    }
}
