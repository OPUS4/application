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
 * @author      Gunar Maiwald <maiwald@zib.de>
 * @copyright   Copyright (c) 2008-2018, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Basic unit tests for Admin_EnrichmentkeyController class.
 *
 * @covers Admin_EnrichmentkeyController
 */
class Admin_EnrichmentkeyControllerTest extends CrudControllerTestCase {

    public function setUp() {
        $this->setController('enrichmentkey');
        parent::setUp();
    }

    public function getModels() {
        return Opus_EnrichmentKey::getAll();
    }

    public function createNewModel() {
        $model = new Opus_EnrichmentKey();
        $model->setName('TestEnrichmentKey');

        return $model->store();
    }

    public function getModel($identifier) {
        return new Opus_EnrichmentKey($identifier);
    }

    /**
     * Show action is disabled for enrichment keys.
     */
    public function testShowActionBadId() {
        $this->dispatch($this->getControllerPath() . '/show/id/123');
        $this->assertRedirectTo($this->getControllerPath());
    }

    /**
     * Show action is disabled for enrichment keys.
     */
    public function testShowActionBadUnknownId() {
        $this->dispatch($this->getControllerPath() . '/show/id/City2');
        $this->assertRedirectTo($this->getControllerPath());
    }

    /**
     * Show action is disabled for enrichment keys.
     */
    public function testShowActionNoId() {
        $this->dispatch($this->getControllerPath() . '/show');
        $this->assertRedirectTo($this->getControllerPath());
    }

    public function testNewActionSave() {
        $this->createsModels = true;

        $post = array(
            'Name' => 'MyTestEnrichment',
            'Save' => 'Speichern'
        );

        $this->getRequest()->setPost($post)->setMethod('POST');

        $this->dispatch($this->getControllerPath() . '/new');

        $this->assertRedirect();
        $this->assertRedirectRegex('/^\/admin\/enrichmentkey/');
        $this->verifyFlashMessage('controller_crud_save_success', self::MESSAGE_LEVEL_NOTICE);

        $enrichment = new Opus_EnrichmentKey('MyTestEnrichment');

        $this->assertNotNull($enrichment);
        $this->assertEquals('MyTestEnrichment', $enrichment->getName());
    }

    public function testNewActionCancel() {
        $this->createsModels = true;

        $modelCount = count($this->getModels());

        $post = array(
            'Name' => 'MyTestEnrichment',
            'Cancel' => 'Abbrechen'
        );

        $this->getRequest()->setPost($post)->setMethod('POST');

        $this->dispatch($this->getControllerPath() . '/new');

        $this->assertRedirectTo('/admin/enrichmentkey', 'Should be a redirect to index action.');

        $this->assertEquals($modelCount, count(Opus_EnrichmentKey::getAll()), 'There should be no new enrichment.');
    }

    public function testNewActionSaveForExistingEnrichment() {
        $this->createsModels = true;

        $post = array(
            'Name' => 'City',
            'Save' => 'Speichern'
        );

        $this->getRequest()->setPost($post)->setMethod('POST');

        $this->dispatch($this->getControllerPath() . '/new');
        $this->assertResponseCode(200);
        $this->assertController('enrichmentkey');
        $this->assertAction('new');

        $this->assertQueryContentContains('div#Name-element', 'Enrichmentkey already exists.');
    }

    public function testEditActionShowForm() {
        $this->dispatch($this->getControllerPath() . '/edit/id/BibtexRecord');
        $this->assertResponseCode(200);
        $this->assertController('enrichmentkey');
        $this->assertAction('edit');

        $this->assertQueryContentContains('div#Name-element', 'Name');
        $this->assertQuery('li.save-element');
        $this->assertQuery('li.cancel-element');
        $this->assertQueryCount(1, 'input#Id');
    }

    public function testEditActionShowFormForProtectedEnrichment() {
        $this->dispatch($this->getControllerPath() . '/edit/id/City');

        $this->assertRedirect();
        $this->assertRedirectTo($this->getControllerPath());
        $this->verifyFlashMessage('controller_crud_model_not_modifiable', self::MESSAGE_LEVEL_FAILURE);

        $enrichmentKey = new Opus_EnrichmentKey('City');

        $this->assertNotNull($enrichmentKey);
        $this->assertEquals('City', $enrichmentKey->getName());
    }

    /**
     * @expectedException Opus_Model_NotFoundException
     * @expectedExceptionMessage No Opus_Db_EnrichmentKeys with id MyTestEnrichment in database.
     */
    public function testEditActionSave() {
        $this->createsModels = true;

        $enrichmentKey = new Opus_EnrichmentKey();
        $enrichmentKey->setName('MyTestEnrichment');
        $enrichmentKey->store();

        $this->getRequest()->setMethod('POST')->setPost(array(
            'Id' => 'MyTestEnrichment',
            'Name' => 'MyTestEnrichmentModified',
            'Save' => 'Speichern'
        ));

        $this->dispatch($this->getControllerPath() . '/edit');
        $this->assertRedirectTo($this->getControllerPath());
        $this->verifyFlashMessage('controller_crud_save_success', self::MESSAGE_LEVEL_NOTICE);

        $enrichmentKey = new Opus_EnrichmentKey('MyTestEnrichmentModified');

        $this->assertNotNull($enrichmentKey);
        $this->assertEquals('MyTestEnrichmentModified', $enrichmentKey);

        new Opus_EnrichmentKey('MyTestEnrichment');

        $this->fail('Previous statement should have thrown exception.');
    }

    /**
     * @expectedException Opus_Model_NotFoundException
     * @expectedExceptionMessage No Opus_Db_EnrichmentKeys with id CityModified in database.
     */
    public function testEditActionSaveForProtectedEnrichment() {
        $this->createsModels = true;

        $enrichmentKey = new Opus_EnrichmentKey();
        $enrichmentKey->setName('MyTestEnrichment');
        $enrichmentKey->store();

        $this->getRequest()->setMethod('POST')->setPost(array(
            'Id' => 'City',
            'Name' => 'CityModified',
            'Save' => 'Speichern'
        ));

        $this->dispatch($this->getControllerPath() . '/edit');
        $this->assertRedirectTo($this->getControllerPath());
        $this->verifyFlashMessage('controller_crud_model_not_modifiable', self::MESSAGE_LEVEL_FAILURE);

        $enrichmentKey = new Opus_EnrichmentKey('City');

        $this->assertNotNull($enrichmentKey);
        $this->assertEquals('City', $enrichmentKey->getName());

        new Opus_EnrichmentKey('CityModified');

        $this->fail('Previous statement should have thrown exception.');
    }

    /**
     * @expectedException Opus_Model_NotFoundException
     * @expectedExceptionMessage No Opus_Db_EnrichmentKeys with id MyTestEnrichmentModified in database.
     */
    public function testEditActionCancel() {
        $this->createsModels = true;

        $enrichmentKey = new Opus_EnrichmentKey();
        $enrichmentKey->setName('MyTestEnrichment');
        $enrichmentKey->store();

        $this->getRequest()->setMethod('POST')->setPost(array(
            'Id' => 'MyTestEnrichment',
            'Name' => 'MyTestEnrichmentModified',
            'Cancel' => 'Abbrechen'
        ));

        $this->dispatch($this->getControllerPath() . '/edit');
        $this->assertRedirectTo($this->getControllerPath());

        $enrichmentKey = new Opus_EnrichmentKey('MyTestEnrichment');

        $this->assertNotNull($enrichmentKey);
        $this->assertEquals('MyTestEnrichment', $enrichmentKey->getName());

        new Opus_EnrichmentKey('MyTestEnrichmentModified');

        $this->fail('Previous statement should have thrown exception.');
    }

    /**
     * @expectedException Opus_Model_NotFoundException
     * @expectedExceptionMessage No Opus_Db_EnrichmentKeys with id CityModified in database.
     */
    public function testEditActionCancelForProtectedEnrichment() {
        $this->createsModels = true;

        $enrichmentKey = new Opus_EnrichmentKey();
        $enrichmentKey->setName('MyTestEnrichment');
        $enrichmentKey->store();

        $this->getRequest()->setMethod('POST')->setPost(array(
            'Id' => 'City',
            'Name' => 'CityModified',
            'Cancel' => 'Abbrechen'
        ));

        $this->dispatch($this->getControllerPath() . '/edit');
        $this->assertRedirectTo($this->getControllerPath());

        $enrichmentKey = new Opus_EnrichmentKey('City');

        $this->assertNotNull($enrichmentKey);
        $this->assertEquals('City', $enrichmentKey->getName());

        new Opus_EnrichmentKey('CityModified');

        $this->fail('Previous statement should have thrown exception.');
    }

    public function testDeleteActionShowForm() {
        $this->useEnglish();

        $this->dispatch($this->getControllerPath() . '/delete/id/BibtexRecord');

        $this->assertQueryContentContains('legend', 'Delete EnrichmentKey');
        $this->assertQueryContentContains('span.displayname', 'BibtexRecord');
        $this->assertQuery('input#ConfirmYes');
        $this->assertQuery('input#ConfirmNo');

        $enrichmentKey = new Opus_EnrichmentKey('BibtexRecord');

        $this->assertNotNull($enrichmentKey);
        $this->assertEquals('BibtexRecord', $enrichmentKey->getName());
    }

    public function testDeleteActionShowFormForProtectedEnrichment() {
        $this->useEnglish();

        $this->dispatch($this->getControllerPath() . '/delete/id/City');

        $this->assertRedirect();
        $this->assertRedirectTo($this->getControllerPath());
        $this->verifyFlashMessage('controller_crud_model_not_modifiable', self::MESSAGE_LEVEL_FAILURE);

        $enrichmentKey = new Opus_EnrichmentKey('City');

        $this->assertNotNull($enrichmentKey);
        $this->assertEquals('City', $enrichmentKey->getName());
    }

}
