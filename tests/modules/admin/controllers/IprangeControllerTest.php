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
 * @package     Admin
 * @author      Jens Schwidder <schwidder@zib.de>
 * @author      Maximilian Salomon <salomon@zib.de>
 * @copyright   Copyright (c) 2008-2018, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Basic unit tests for IP range controller in admin module.
 *
 * @covers Admin_IprangeController
 */
class Admin_IprangeControllerTest extends CrudControllerTestCase {

    public function setUp()
    {
        $this->setController('iprange');

        parent::setUp();
    }

    public function getModels()
    {
        return Opus_Iprange::getAll();
    }

    public function createNewModel()
    {
        $this->createsModels = true;

        $ipRange = new Opus_Iprange();
        $ipRange->setName('localhost');
        $ipRange->setStartingip('127.0.0.1');
        $ipRange->setEndingip('127.0.0.2');
        $ipRange->setRole(array(
            Opus_UserRole::fetchByName('reviewer'),
            Opus_UserRole::fetchByName('docsadmin')
        ));

        return $ipRange->store();
    }

    public function getModel($identifier)
    {
        return new Opus_Iprange($identifier);
    }

    public function testShowAction()
    {
        $this->createsModels = true;

        $iprangeId = $this->createNewModel();

        $this->dispatch("/admin/iprange/show/id/$iprangeId");

        $this->assertResponseCode(200);
        $this->assertController('iprange');
        $this->assertAction('show');

        $this->assertQueryContentContains('div#Name', 'localhost');
        $this->assertQueryContentContains('div#Startingip', '127.0.0.1');
        $this->assertQueryContentContains('div#Endingip', '127.0.0.2');
        $this->assertQueryContentContains('div#Roles', 'reviewer, docsadmin');

        $this->validateXHTML();
    }

    public function testNewActionSave()
    {
        $this->createsModels = true;

        $post = array(
            'Name' => 'test range',
            'Startingip' => '127.0.0.3',
            'Endingip' => '127.0.0.4',
            'Roles' => array('docsadmin', 'guest'),
            'Save' => 'Speichern'
        );

        $this->getRequest()->setPost($post)->setMethod('POST');

        $this->dispatch('/admin/iprange/new');

        $this->assertRedirect('Should be a redirect to show action.');
        $this->assertRedirectRegex('/^\/admin\/iprange\/show/'); // Regex weil danach noch '/id/xxx' kommt
        $this->verifyFlashMessage('controller_crud_save_success', self::MESSAGE_LEVEL_NOTICE);

        // Neue Lizenz anzeigen
        $location = $this->getLocation();

        $this->resetRequest();
        $this->resetResponse();

        $this->dispatch($location);
        $this->assertResponseCode(200);

        $this->assertQueryContentContains('div#Name', 'test range');
        $this->assertQueryContentContains('div#Startingip', '127.0.0.3');
        $this->assertQueryContentContains('div#Endingip', '127.0.0.4');
        $this->assertQueryContentContains('div#Roles', 'guest');
        $this->assertQueryContentContains('div#Roles', 'docsadmin');
    }

    public function testNewActionCancel()
    {
        $this->createsModels = true;

        $modelCount = count($this->getModels());

        $post = array(
            'Name' => 'test range',
            'Startingip' => '127.0.0.5',
            'Endingip' => '127.0.0.6',
            'Cancel' => 'Abbrechen'
        );

        $this->getRequest()->setPost($post)->setMethod('POST');

        $this->dispatch('/admin/iprange/new');

        $this->assertRedirectTo('/admin/iprange', 'Should redirect to index action.');

        $this->assertEquals($modelCount, count(Opus_Iprange::getAll()), 'There should be no new ip range.');
    }

    public function testEditActionShowForm()
    {
        $iprangeId = $this->createNewModel();

        $this->dispatch("/admin/iprange/edit/id/$iprangeId");

        $this->assertResponseCode(200);
        $this->assertController('iprange');
        $this->assertAction('edit');

        $this->assertQueryContentContains('div#Name-element', 'localhost');
        $this->assertQueryContentContains('div#Startingip-element', '127.0.0.1');
        $this->assertQueryContentContains('div#Endingip-element', '127.0.0.2');
        $this->assertQuery('li.save-element');
        $this->assertQuery('li.cancel-element');
        $this->assertQueryCount('input#Id', 1);
    }

    public function testEditActionSave()
    {
        $iprangeId = $this->createNewModel();

        $this->getRequest()->setMethod('POST')->setPost(array(
            'Id' => $iprangeId,
            'Name' => 'ModifiedName',
            'Startingip' => '127.0.0.99',
            'Endingip' => '127.0.0.100',
            'Roles' => array('docsadmin', 'jobaccess'),
            'Save' => 'Abspeichern'
        ));

        $this->dispatch('/admin/iprange/edit');
        $this->assertRedirectTo("/admin/iprange/show/id/$iprangeId");
        $this->verifyFlashMessage('controller_crud_save_success', self::MESSAGE_LEVEL_NOTICE);

        $iprange = new Opus_IpRange($iprangeId);

        $this->assertEquals('ModifiedName', $iprange->getName());
        $this->assertEquals('127.0.0.99', $iprange->getStartingip());
        $this->assertEquals('127.0.0.100', $iprange->getEndingip());

        $roles = $iprange->getRole();

        $this->assertCount(2, $roles);

        $this->verifyRoles($roles, array('docsadmin', 'jobaccess'));
    }

    public function testEditActionCancel()
    {
        $iprangeId = $this->createNewModel();

        $this->getRequest()->setMethod('POST')->setPost(array(
            'Id' => $iprangeId,
            'Name' => 'ModifiedName',
            'Startingip' => '200.0.0.1',
            'Endingip' => '200.0.0.2',
            'Cancel' => 'Abbrechen'
        ));

        $this->dispatch("/admin/iprange/edit");
        $this->assertRedirectTo('/admin/iprange');

        $iprange = new Opus_Iprange($iprangeId);

        $this->assertEquals('localhost', $iprange->getName());
    }

    public function testDeleteActionShowForm()
    {
        $this->useEnglish();

        $iprangeId = $this->createNewModel();

        $this->dispatch("/admin/iprange/delete/id/$iprangeId");

        $this->assertQueryContentContains('legend', 'Delete IP Range');
        $this->assertQueryContentContains('span.displayname', 'localhost');
        $this->assertQuery('input#ConfirmYes');
        $this->assertQuery('input#ConfirmNo');
    }

    public function verifyRoles($roles, $expectedRoles)
    {
        $this->assertCount(count($expectedRoles), $roles);

        foreach ($roles as $role)
        {
            $name = $role->getName();
            $this->assertContains($name, $expectedRoles);
            $expectedRoles = array_diff($expectedRoles, array($name));
        }

        $this->assertEmpty($expectedRoles);
    }

}

