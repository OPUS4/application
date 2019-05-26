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
 * @category    Tests
 * @author      Gunar Maiwald <maiwald@zib.de>
 * @author      Maximilian Salomon <salomon@zib.de>
 * @author      Jens Schwidder <schwidder@zib.de>
 * @author      Sascha Szott <opus-development@saschaszott.de>
 * @copyright   Copyright (c) 2008-2019, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Basic unit tests for Admin_EnrichmentkeyController class.
 *
 * @covers Admin_EnrichmentkeyController
 */
class Admin_EnrichmentkeyControllerTest extends CrudControllerTestCase
{

    public function setUp()
    {
        $this->setController('enrichmentkey');
        parent::setUp();
    }

    public function getModels()
    {
        return Opus_EnrichmentKey::getAll();
    }

    public function createNewModel()
    {
        $model = new Opus_EnrichmentKey();
        $model->setName('TestEnrichmentKey');
        return $model->store();
    }

    public function getModel($identifier)
    {
        return new Opus_EnrichmentKey($identifier);
    }

    /**
     * Show action is disabled for enrichment keys.
     */
    public function testShowActionBadId()
    {
        $this->dispatch($this->getControllerPath() . '/show/id/123');
        $this->assertRedirectTo($this->getControllerPath());
    }

    /**
     * Show action is disabled for enrichment keys.
     */
    public function testShowActionBadUnknownId()
    {
        $this->dispatch($this->getControllerPath() . '/show/id/City2');
        $this->assertRedirectTo($this->getControllerPath());
    }

    /**
     * Show action is disabled for enrichment keys.
     */
    public function testShowActionNoId()
    {
        $this->dispatch($this->getControllerPath() . '/show');
        $this->assertRedirectTo($this->getControllerPath());
    }

    public function testNewActionSave()
    {
        $this->createsModels = true;

        $post = [
            'Name' => 'MyTestEnrichment',
            'Type' => 'TextType',
            'Options' => '',
            'Save' => 'Speichern'
        ];

        $this->getRequest()->setPost($post)->setMethod('POST');

        $this->dispatch($this->getControllerPath() . '/new');

        $this->assertRedirect();
        $this->assertRedirectRegex('/^\/admin\/enrichmentkey/');
        $this->verifyFlashMessage('controller_crud_save_success', self::MESSAGE_LEVEL_NOTICE);

        $enrichmentKey = new Opus_EnrichmentKey('MyTestEnrichment');
        $this->assertNotNull($enrichmentKey);
        $this->assertEquals('MyTestEnrichment', $enrichmentKey->getName());
    }

    public function testNewActionSaveEnrichmentKeyWithTypeOptions()
    {
        $this->createsModels = true;

        $post = [
            'Name' => 'MyTestEnrichment',
            'Type' => 'RegexType',
            'Options' => '^.*$',
            'Save' => 'Speichern'
        ];

        $this->getRequest()->setPost($post)->setMethod('POST');

        $this->dispatch($this->getControllerPath() . '/new');

        $this->assertRedirect();
        $this->assertRedirectRegex('/^\/admin\/enrichmentkey/');
        $this->verifyFlashMessage('controller_crud_save_success', self::MESSAGE_LEVEL_NOTICE);

        $enrichmentKey = new Opus_EnrichmentKey('MyTestEnrichment');
        $this->assertNotNull($enrichmentKey);
        $this->assertEquals('MyTestEnrichment', $enrichmentKey->getName());
        $this->assertEquals('RegexType', $enrichmentKey->getType());
        $this->assertEquals(json_encode(array("regex" => "^.*$")), $enrichmentKey->getOptions());
    }

    public function testNewActionSaveMissingEnrichmentType()
    {
        $this->createsModels = true;

        $post = [
            'Name' => 'MyTestEnrichment',
            'Save' => 'Speichern'
        ];

        $this->getRequest()->setPost($post)->setMethod('POST');

        $this->dispatch($this->getControllerPath() . '/new');

        $this->assertResponseCode(200);
        $this->assertXpath("//form/div[2]/div[2]/ul[@class='errors']", $this->getResponse()->getBody());

        $this->assertNull(Opus_EnrichmentKey::fetchByName('MyTestEnrichment'));
    }

    public function testNewActionSaveUnknownEnrichmentType()
    {
        $this->createsModels = true;

        $post = [
            'Name' => 'MyTestEnrichment',
            'Type' => 'FooBarType',
            'Options' => '',
            'Save' => 'Speichern'
        ];

        $this->getRequest()->setPost($post)->setMethod('POST');

        $this->dispatch($this->getControllerPath() . '/new');

        $this->assertResponseCode(200);
        $this->assertXpath("//form/div[2]/div[2]/ul[@class='errors']", $this->getResponse()->getBody());

        $this->assertNull(Opus_EnrichmentKey::fetchByName('MyTestEnrichment'));
    }

    public function testNewActionCancel()
    {
        $this->createsModels = true;

        $modelCount = count($this->getModels());

        $post = [
            'Name' => 'MyTestEnrichment',
            'Cancel' => 'Abbrechen'
        ];

        $this->getRequest()->setPost($post)->setMethod('POST');

        $this->dispatch($this->getControllerPath() . '/new');

        $this->assertRedirectTo('/admin/enrichmentkey', 'Should be a redirect to index action.');

        $this->assertEquals($modelCount, count(Opus_EnrichmentKey::getAll()), 'There should be no new enrichment.');
    }

    public function testNewActionSaveForExistingEnrichment()
    {
        $this->useEnglish();
        $this->createsModels = true;

        $post = [
            'Name' => 'City',
            'Save' => 'Speichern'
        ];

        $this->getRequest()->setPost($post)->setMethod('POST');

        $this->dispatch($this->getControllerPath() . '/new');
        $this->assertResponseCode(200);
        $this->assertController('enrichmentkey');
        $this->assertAction('new');

        $this->assertQueryContentContains('div#Name-element', 'Enrichmentkey already exists.');
    }

    public function testEditActionShowForm()
    {
        $this->dispatch($this->getControllerPath() . '/edit/id/BibtexRecord');
        $this->assertResponseCode(200);
        $this->assertController('enrichmentkey');
        $this->assertAction('edit');

        $this->assertQueryContentContains('div#Name-element', 'Name');
        $this->assertQuery('li.save-element');
        $this->assertQuery('li.cancel-element');
        $this->assertQueryCount('input#Id', 1);
    }

    public function testEditActionShowFormForProtectedEnrichment()
    {
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
    public function testEditActionSave()
    {
        $this->createsModels = true;

        $enrichmentKey = new Opus_EnrichmentKey();
        $enrichmentKey->setName('MyTestEnrichment');
        $enrichmentKey->setType('TextType');
        $enrichmentKey->store();

        $this->getRequest()->setMethod('POST')->setPost([
            'Id' => 'MyTestEnrichment',
            'Name' => 'MyTestEnrichmentModified',
            'Type' => 'RegexType',
            'Options' => '^.*$',
            'Save' => 'Speichern'
        ]);

        $this->dispatch($this->getControllerPath() . '/edit');
        $this->assertRedirectTo($this->getControllerPath());
        $this->verifyFlashMessage('controller_crud_save_success', self::MESSAGE_LEVEL_NOTICE);

        $enrichmentKey = new Opus_EnrichmentKey('MyTestEnrichmentModified');
        $this->assertNotNull($enrichmentKey);
        $this->assertEquals('MyTestEnrichmentModified', $enrichmentKey->getName());
        $this->assertEquals('RegexType', $enrichmentKey->getType());
        $this->assertEquals(json_encode(array("regex" => "^.*$")), $enrichmentKey->getOptions());

        new Opus_EnrichmentKey('MyTestEnrichment');

        $this->fail('Previous statement should have thrown exception.');
    }

    /**
     * @expectedException Opus_Model_NotFoundException
     * @expectedExceptionMessage No Opus_Db_EnrichmentKeys with id CityModified in database.
     */
    public function testEditActionSaveForProtectedEnrichment()
    {
        $this->createsModels = true;

        $enrichmentKey = new Opus_EnrichmentKey();
        $enrichmentKey->setName('MyTestEnrichment');
        $enrichmentKey->store();

        $this->getRequest()->setMethod('POST')->setPost([
            'Id' => 'City',
            'Name' => 'CityModified',
            'Type' => 'TextType',
            'Options' => '',
            'Save' => 'Speichern'
        ]);

        $this->dispatch($this->getControllerPath() . '/edit');
        $this->assertRedirectTo($this->getControllerPath());
        $this->verifyFlashMessage('controller_crud_model_not_modifiable', self::MESSAGE_LEVEL_FAILURE);

        $enrichmentKey = new Opus_EnrichmentKey('City');

        $this->assertNotNull($enrichmentKey);
        $this->assertEquals('City', $enrichmentKey->getName());

        new Opus_EnrichmentKey('CityModified');

        $this->fail('Previous statement should have thrown exception.');
    }

    public function testEditActionSaveWithoutEnrichmentType()
    {
        $this->createsModels = true;

        $enrichmentKey = new Opus_EnrichmentKey();
        $enrichmentKey->setName('MyTestEnrichment');
        $enrichmentKey->store();

        $this->getRequest()->setMethod('POST')->setPost([
            'Id' => 'MyTestEnrichment',
            'Name' => 'MyTestEnrichmentModified',
            'Save' => 'Speichern'
        ]);

        $this->dispatch($this->getControllerPath() . '/edit');
        $this->assertResponseCode(200);

        $this->assertNull(Opus_EnrichmentKey::fetchByName('MyTestEnrichmentModified'));
        $this->assertNotNull(Opus_EnrichmentKey::fetchByName('MyTestEnrichment'));
    }

    public function testEditActionSaveWithUnknownEnrichmentType()
    {
        $this->createsModels = true;

        $enrichmentKey = new Opus_EnrichmentKey();
        $enrichmentKey->setName('MyTestEnrichment');
        $enrichmentKey->store();

        $this->getRequest()->setMethod('POST')->setPost([
            'Id' => 'MyTestEnrichment',
            'Name' => 'MyTestEnrichmentModified',
            'Type' => 'FooBarType',
            'Save' => 'Speichern'
        ]);

        $this->dispatch($this->getControllerPath() . '/edit');
        $this->assertResponseCode(200);

        $this->assertNull(Opus_EnrichmentKey::fetchByName('MyTestEnrichmentModified'));
        $this->assertNotNull(Opus_EnrichmentKey::fetchByName('MyTestEnrichment'));
    }

    /**
     * @expectedException Opus_Model_NotFoundException
     * @expectedExceptionMessage No Opus_Db_EnrichmentKeys with id MyTestEnrichmentModified in database.
     */
    public function testEditActionCancel()
    {
        $this->createsModels = true;

        $enrichmentKey = new Opus_EnrichmentKey();
        $enrichmentKey->setName('MyTestEnrichment');
        $enrichmentKey->setType('TextType');
        $enrichmentKey->store();

        $this->getRequest()->setMethod('POST')->setPost([
            'Id' => 'MyTestEnrichment',
            'Name' => 'MyTestEnrichmentModified',
            'Type' => 'RegexType',
            'Options' => '^.*$',
            'Cancel' => 'Abbrechen'
        ]);

        $this->dispatch($this->getControllerPath() . '/edit');
        $this->assertRedirectTo($this->getControllerPath());

        $enrichmentKey = new Opus_EnrichmentKey('MyTestEnrichment');

        $this->assertNotNull($enrichmentKey);
        $this->assertEquals('MyTestEnrichment', $enrichmentKey->getName());
        $this->assertEquals('TextType', $enrichmentKey->getType());
        $this->assertNull($enrichmentKey->getOptions());

        new Opus_EnrichmentKey('MyTestEnrichmentModified');

        $this->fail('Previous statement should have thrown exception.');
    }

    /**
     * @expectedException Opus_Model_NotFoundException
     * @expectedExceptionMessage No Opus_Db_EnrichmentKeys with id CityModified in database.
     */
    public function testEditActionCancelForProtectedEnrichment()
    {
        $this->createsModels = true;

        $enrichmentKey = new Opus_EnrichmentKey();
        $enrichmentKey->setName('MyTestEnrichment');
        $enrichmentKey->store();

        $this->getRequest()->setMethod('POST')->setPost([
            'Id' => 'City',
            'Name' => 'CityModified',
            'Cancel' => 'Abbrechen'
        ]);

        $this->dispatch($this->getControllerPath() . '/edit');
        $this->assertRedirectTo($this->getControllerPath());

        $enrichmentKey = new Opus_EnrichmentKey('City');

        $this->assertNotNull($enrichmentKey);
        $this->assertEquals('City', $enrichmentKey->getName());

        new Opus_EnrichmentKey('CityModified');

        $this->fail('Previous statement should have thrown exception.');
    }

    public function testDeleteActionShowForm()
    {
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

    public function testDeleteActionShowFormForProtectedEnrichment()
    {
        $this->useEnglish();

        $this->dispatch($this->getControllerPath() . '/delete/id/City');

        $this->assertRedirect();
        $this->assertRedirectTo($this->getControllerPath());
        $this->verifyFlashMessage('controller_crud_model_cannot_delete', self::MESSAGE_LEVEL_FAILURE);

        $enrichmentKey = new Opus_EnrichmentKey('City');

        $this->assertNotNull($enrichmentKey);
        $this->assertEquals('City', $enrichmentKey->getName());
    }

    public function testEnrichmentTypeHandlingRoundTrip()
    {
        $enrichmentKeyName = 'RegexTypeEnrichmentKey';

        $this->createsModels = true;

        // neuen Enrichmentkey mit Typ und Optionen anlegen
        $post = [
            'Name' => $enrichmentKeyName,
            'Type' => 'RegexType',
            'Options' => '^abc$',
            'Save' => 'Speichern'
        ];

        $this->getRequest()->setPost($post)->setMethod('POST');
        $this->dispatch($this->getControllerPath() . '/new');


        // prüfe, dass Enrichmentkey erfolgreich in Datenbank gespeichert wurde
        $this->assertRedirect();
        $this->assertRedirectRegex('/^\/admin\/enrichmentkey/');
        $this->verifyFlashMessage('controller_crud_save_success', self::MESSAGE_LEVEL_NOTICE);

        $enrichmentKey = new Opus_EnrichmentKey($enrichmentKeyName);
        $this->assertNotNull($enrichmentKey);
        $this->assertEquals($enrichmentKeyName, $enrichmentKey->getName());
        $this->assertEquals($post['Type'], $enrichmentKey->getType());
        $this->assertEquals($post['Options'], $enrichmentKey->getOptionsPrintable());


        // Enrichmentkey-Übersichtseite sollte nun den Typnamen und einen Tooltip anzeigen
        $this->resetRequest();
        $this->resetResponse();
        $this->getRequest()->setMethod('GET');
        $this->dispatch($this->getControllerPath());

        $this->assertResponseCode(200);
        $this->assertController('enrichmentkey');
        $this->assertAction('index');

        $this->assertContains($post['Type'], $this->getResponse()->getBody());
        $this->assertXpathCount('//i[@class="fa fa-info-circle" and @title="' . $post['Options'] . '"]', 1);


        // prüfe, dass Edit-Formular Typnamen und Optionen anzeigt
        $this->resetRequest();
        $this->resetResponse();
        $this->getRequest()->setMethod('GET');
        $this->dispatch($this->getControllerPath() . '/edit/id/' . $enrichmentKeyName);

        $this->assertResponseCode(200);
        $this->assertController('enrichmentkey');
        $this->assertAction('edit');

        $this->assertXpathContentContains('//*[@id="Name"]/@value', $enrichmentKeyName);
        $this->assertXpathContentContains('//*[@id="admin_enrichmentkey_type"]/option[@selected="selected"]/@value', $post['Type']);
        $this->assertXpathContentContains('//*[@id="admin_enrichmentkey_options"]/text()', $post['Options']);

        // Cleanup-Schritt
        $enrichmentKey->delete();
    }

    public function testEnrichmentTypeWithInvalidOptions()
    {
        $enrichmentKeyName = 'MySelectEnrichmentKey';

        $this->createsModels = true;

        $post = [
            'Name' => $enrichmentKeyName,
            'Type' => 'RegexType',
            'Options' => '[', // dieser Regex ist ungültig
            'Save' => 'Speichern'
        ];

        $this->getRequest()->setPost($post)->setMethod('POST');
        $this->dispatch($this->getControllerPath() . '/new');

        $this->assertRedirect();
        $this->assertRedirectRegex('/^\/admin\/enrichmentkey/');
        $this->verifyFlashMessage('controller_crud_save_success', self::MESSAGE_LEVEL_NOTICE);

        $enrichmentKey = new Opus_EnrichmentKey($enrichmentKeyName);
        $this->assertNotNull($enrichmentKey);
        $this->assertEquals($enrichmentKeyName, $enrichmentKey->getName());

        $this->resetRequest();
        $this->resetResponse();

        $this->getRequest()->setMethod('GET');
        $this->dispatch($this->getControllerPath() . '/edit/id/' . $enrichmentKeyName);

        $this->assertResponseCode(200);
        $this->assertController('enrichmentkey');
        $this->assertAction('edit');

        // prüfe, dass die Formularfelder korrekt initialisiert wurden
        $this->assertXpathContentContains('//*[@id="Name"]/@value', $enrichmentKeyName);
        $this->assertXpathContentContains('//*[@id="admin_enrichmentkey_type"]/option[@selected="selected"]/@value', $post['Type']);
        $this->assertNotXpath('//*[@id="admin_enrichmentkey_options"]/text()'); // invalider Regex sollte nicht übernommen worden sein

        $enrichmentKey->delete();
    }

    public function testAllEnrichmentTypesAreAvailableInEditForm()
    {
        $this->getRequest()->setMethod('GET');
        $this->dispatch($this->getControllerPath() . '/new');

        $allEnrichmentTypes = Opus_Enrichment_AbstractType::getAllEnrichmentTypes(false);
        $this->assertXpathCount('//select/option', 1 + count($allEnrichmentTypes)); // +1, weil Standardauswahl leer ist

        $this->assertXpathContentRegex('//select/option[1]', "//");
        $index = 2;
        foreach ($allEnrichmentTypes as $enrichmentType) {
            $this->assertXpathContentContains('//select/option[' . $index . ']', $enrichmentType);
            $index++;
        }
    }
}
