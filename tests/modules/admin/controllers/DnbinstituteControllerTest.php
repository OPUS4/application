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
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2018, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Class Admin_DnbinstituteControllerTest
 *
 * @covers Admin_DnbinstituteController
 */
class Admin_DnbinstituteControllerTest extends CrudControllerTestCase {

    private $roleId;
    private $userId;

    public function setUp() {
        $this->setController('dnbinstitute');
        parent::setUp();
    }

    public function tearDown() {
        if (isset ($this->roleId) && isset($this->userId)) {
            $testRole = new Opus_UserRole($this->roleId);
            $testRole->delete();
            $userAccount = new Opus_Account($this->userId);
            $userAccount->delete();
        }
        parent::tearDown();
    }

    public function getModels() {
        return Opus_DnbInstitute::getAll();
    }

    public function createNewModel() {
        $model = new Opus_DnbInstitute();

        $model->setName('TestName');
        $model->setCity('TestCity');
        $model->setDepartment('TestDepartment');
        $model->setAddress('TestAddress');
        $model->setPhone('TestPhone');
        $model->setDnbContactId('TestDnbContactId');
        $model->setIsGrantor(true);
        $model->setIsPublisher(false);

        return $model->store();
    }

    public function getModel($identifier) {
        return new Opus_DnbInstitute($identifier);
    }

    private function verifyShow() {
        $this->assertQueryContentContains('div#Name', 'TestName');
        $this->assertQueryContentContains('div#Department', 'TestDepartment');
        $this->assertQueryContentContains('div#City', 'TestCity');
        $this->assertQueryContentContains('div#Address', 'TestAddress');
        $this->assertQueryContentContains('div#Phone', 'TestPhone');
        $this->assertQueryContentContains('div#DnbContactId', 'TestDnbContactId');
        $this->assertQueryContentRegex('div#IsGrantor', '/Yes|Ja/');
        $this->assertQueryContentRegex('div#IsPublisher', '/No|Nein/');
    }

    public function testShowAction() {
        $this->createsModels= true;

        $modelId = $this->createNewModel();

        $this->dispatch('/admin/dnbinstitute/show/id/' . $modelId);


        $model = $this->getModel($modelId);
        $model->delete();

        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('dnbinstitute');
        $this->assertAction('show');

        $this->verifyShow();
        // TODO $this->validateXHTML();
    }

    public function testNewActionSave() {
        $this->createsModels = true;

        $post = array(
            'Name' => 'TestName',
            'Department' => 'TestDepartment',
            'City' => 'TestCity',
            'Address' => 'TestAddress',
            'Phone' => 'TestPhone',
            'DnbContactId' => 'TestDnbContactId',
            'IsGrantor' => '1',
            'IsPublisher' => '0',
            'Save' => 'Speichern',
        );

        $this->getRequest()->setPost($post)->setMethod('POST');

        $this->dispatch('/admin/dnbinstitute/new');

        $this->assertRedirect('Should be a redirect to show action.');
        $this->assertRedirectRegex('/^\/admin\/dnbinstitute\/show/'); // Regex weil danach noch '/id/xxx' kommt
        $this->verifyFlashMessage('controller_crud_save_success', self::MESSAGE_LEVEL_NOTICE);

        // Neue Lizenz anzeigen
        $location = $this->getLocation();

        $this->resetRequest();
        $this->resetResponse();

        $this->dispatch($location);
        $this->assertResponseCode(200);

        $this->verifyShow();
    }

    public function testNewActionCancel() {
        $this->createsModels = true;

        $modelCount = count($this->getModels());

        $post = array(
            'Name' => 'TestName',
            'City' => 'TestCity',
            'Cancel' => 'Abbrechen'
        );

        $this->getRequest()->setPost($post)->setMethod('POST');

        $this->dispatch('/admin/dnbinstitute/new');

        $this->assertRedirectTo('/admin/dnbinstitute', 'Should be a redirect to index action.');

        $this->assertEquals($modelCount, count(Opus_DnbInstitute::getAll()),
            'Es sollte keine neue Sprache geben.');
    }

    public function testEditActionShowForm() {
        $this->dispatch('/admin/dnbinstitute/edit/id/1');
        $this->assertResponseCode(200);
        $this->assertController('dnbinstitute');
        $this->assertAction('edit');

        $this->assertQueryContentContains('div#Name-element', 'Foobar Universität');
        $this->assertQuery('li.save-element');
        $this->assertQuery('li.cancel-element');
        $this->assertQueryCount(1, 'input#Id');
    }

    public function testEditActionSave() {
        $this->createsModels = true;

        $modelId = $this->createNewModel();

        $post = array(
            'Id' => $modelId,
            'Name' => 'NameModified',
            'Department' => 'DepartmentModified',
            'Address' => 'AddressModified',
            'City' => 'CityModified',
            'Phone' => 'PhoneModified',
            'DnbContactId' => 'DnbContactIdModified',
            'IsGrantor' => '0',
            'IsPublisher' => '1',
            'Save' => 'Speichern'
        );

        $this->getRequest()->setPost($post)->setMethod('POST');

        $this->dispatch('/admin/dnbinstitute/edit');

        $this->assertRedirectTo('/admin/dnbinstitute/show/id/' . $modelId);
        $this->verifyFlashMessage('controller_crud_save_success', self::MESSAGE_LEVEL_NOTICE);

        $model = $this->getModel($modelId);

        $this->assertEquals('NameModified', $model->getName());
        $this->assertEquals('DepartmentModified', $model->getDepartment());
        $this->assertEquals('AddressModified', $model->getAddress());
        $this->assertEquals('CityModified', $model->getCity());
        $this->assertEquals('PhoneModified', $model->getPhone());
        $this->assertEquals('DnbContactIdModified', $model->getDnbContactId());
        $this->assertEquals('0', $model->getIsGrantor());
        $this->assertEquals('1', $model->getIsPublisher());
    }

    public function testEditActionCancel() {
        $this->createsModels = true;

        $modelId = $this->createNewModel();

        $this->getRequest()->setMethod('POST')->setPost(array(
            'Id' => $modelId,
            'Name' => 'NameModified',
            'City' => 'Berlin',
            'Cancel' => 'Abbrechen'
        ));

        $this->dispatch('/admin/dnbinstitute/edit');
        $this->assertRedirectTo('/admin/dnbinstitute');

        $model = $this->getModel($modelId);

        $this->assertEquals('TestName', $model->getName());
        $this->assertEquals('TestCity', $model->getCity());
    }

    /*
     * Testet, ob der Benutzer auf DNB-Institute zugreifen kann, wenn ihm keine Rechte dazu verliehen wurden.
     */

    public function testDeleteActionShowForm() {
        $this->useEnglish();

        $this->dispatch('/admin/dnbinstitute/delete/id/1');

        $this->assertQueryContentContains('legend', 'Delete Institute');
        $this->assertQueryContentContains('span.displayname', 'Foobar Universität, Testwissenschaftliche Fakultät');
        $this->assertQuery('input#ConfirmYes');
        $this->assertQuery('input#ConfirmNo');
    }
    /*
     * Testet, ob der Benutzer auf DNB-Institute zugreifen kann, wenn ihm Rechte dazu verliehen wurden.
     */


    public function testUserAccessToInstituteWithInstituteRights() {
        $testRole = new Opus_UserRole();
        $testRole->setName('TestRole');
        $testRole->appendAccessModule('admin');
        $testRole->appendAccessModule('resource_institutions');
        $this->roleId = $testRole->store();

        $userAccount = new Opus_Account();
        $userAccount->setLogin('role_tester')
                ->setPassword('role_tester');
        $userAccount->setRole($testRole);
        $this->userId = $userAccount->store();

        $this->enableSecurity();
        $this->loginUser('role_tester', 'role_tester');
        $this->useEnglish();

        $this->dispatch('/admin/dnbinstitute/edit/id/1');
        $this->assertResponseCode(200);
        $this->assertNotRedirectTo('/auth', 'User is not able to edit dnb-institutions, although he has the right to do it');
        $this->assertQueryContentContains('//label', 'Department', 'User is not able to edit dnb-institutions, '.
            'although he has the right to do it');
    }


    /*
     * Testet, ob der Benutzer auf DNB-Institute zugreifen kann, wenn ihm keine Rechte dazu verliehen wurden.
     */
    public function testUserAccessToInstituteWithoutInstituteRights() {
        $testRole = new Opus_UserRole();
        $testRole->setName('TestRole');
        $testRole->appendAccessModule('admin');
        $testRole->appendAccessModule('resource_languages');
        $this->roleId = $testRole->store();

        $userAccount = new Opus_Account();
        $userAccount->setLogin('role_tester')
            ->setPassword('role_tester');
        $userAccount->setRole($testRole);
        $this->userId = $userAccount->store();

        $this->enableSecurity();
        $this->loginUser('role_tester', 'role_tester');
        $this->useEnglish();

        $this->dispatch('/admin/dnbinstitute/edit/id/1');
        $this->assertResponseCode(302);
        $this->assertRedirectTo(
            '/auth/index/rmodule/admin/rcontroller/dnbinstitute/raction/edit/id/1',
            'User is able to edit dnb-institutes, although he has no rights'
        );
    }

    /*
     * Testet, ob der Benutzer auf DNB-Institute zugreifen kann, wenn ihm Rechte dazu verliehen wurden.
     */
    public function testUserAccessToInstituteWithInstituteRightsRegression3245() {
        $testRole = new Opus_UserRole();
        $testRole->setName('TestRole');
        $testRole->appendAccessModule('admin');
        $testRole->appendAccessModule('resource_institutions');
        $this->roleId = $testRole->store();

        $userAccount = new Opus_Account();
        $userAccount->setLogin('role_tester')
            ->setPassword('role_tester');
        $userAccount->setRole($testRole);
        $this->userId = $userAccount->store();

        $this->enableSecurity();
        $this->loginUser('role_tester', 'role_tester');
        $this->useEnglish();

        $this->dispatch('/admin/dnbinstitute/edit/id/1');

        $this->assertNotRedirect();
        $this->assertNotRedirectTo('/auth', 'User is not able to edit dnb-institutions, '.
            'although he has the right to do it');
        $this->assertQueryContentContains('//label', 'Department', 'User is not able to edit dnb-institutions, '.
            'although he has the right to do it');
    }

}

