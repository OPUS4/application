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
 * @coversDefaultClass Admin_EnrichmentkeyController
 */
class Admin_EnrichmentkeyControllerTest extends CrudControllerTestCase
{

    protected $additionalResources = 'all';

    /**
     * @var all enrichment keys
     */
    private $allEnrichmentKeys = [];

    public function setUp()
    {
        $this->setController('enrichmentkey');
        parent::setUp();
        foreach (Opus_EnrichmentKey::getAll() as $value) {
            array_push($this->allEnrichmentKeys, $value->getDisplayName());
        }
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
            'Validation' => '0',
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
        $this->assertNull($enrichmentKey->getOptions());
    }

    public function testNewActionSaveEnrichmentKeyWithTypeOptionsAndStrictValidation()
    {
        $this->createsModels = true;

        $post = [
            'Name' => 'MyTestEnrichment',
            'Type' => 'RegexType',
            'Options' => '^.*$',
            'Validation' => '1',
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
        $this->assertEquals(json_encode(["regex" => "^.*$", "validation" => "strict"]), $enrichmentKey->getOptions());
    }

    public function testNewActionSaveEnrichmentKeyWithTypeOptionsAndNoValidation()
    {
        $this->createsModels = true;

        $post = [
            'Name' => 'MyTestEnrichment',
            'Type' => 'RegexType',
            'Options' => '^.*$',
            'Validation' => '0',
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
        $this->assertEquals(json_encode(["regex" => "^.*$", "validation" => "none"]), $enrichmentKey->getOptions());
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

        $this->assertRedirect();
        $this->assertRedirectRegex('/^\/admin\/enrichmentkey/');
        $this->verifyFlashMessage('controller_crud_save_success', self::MESSAGE_LEVEL_NOTICE);

        $enrichmentKey = new Opus_EnrichmentKey('MyTestEnrichment');
        $this->assertNotNull($enrichmentKey);
        $this->assertEquals('MyTestEnrichment', $enrichmentKey->getName());
        $this->assertNull($enrichmentKey->getOptions());
    }

    public function testNewActionSaveEmptyEnrichmentType()
    {
        $this->createsModels = true;

        $post = [
            'Name' => 'MyTestEnrichment',
            'Type' => '',
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
        $this->assertNull($enrichmentKey->getOptions());
    }

    public function testNewActionSaveUnknownEnrichmentType()
    {
        $this->createsModels = true;

        $post = [
            'Name' => 'MyTestEnrichment',
            'Type' => 'FooBarType',
            'Options' => '',
            'Validation' => '0',
            'Save' => 'Speichern'
        ];

        $this->getRequest()->setPost($post)->setMethod('POST');

        $this->dispatch($this->getControllerPath() . '/new');

        $this->assertResponseCode(200);
        $this->assertXpath('//div[@id = "admin_enrichmentkey_type-element"]/ul[@class="errors"]');

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

    /**
     * @dataProvider enrichmentKeyNamesProvider
     */
    public function testEditActionShowFormForUnprotectedEnrichmentKey($enrichmentKeyName)
    {
        $this->dispatch($this->getControllerPath() . '/edit/id/' . $enrichmentKeyName);
        $this->assertResponseCode(200);
        $this->assertController('enrichmentkey');
        $this->assertAction('edit');

        $this->assertQueryContentContains('div#Name-element', 'Name');
        $this->assertQuery('li.save-element');
        $this->assertQuery('li.cancel-element');
        $this->assertQueryCount('input#Id', 1);
    }

    public function testEditActionShowFormForProtectedEnrichmentKey()
    {
        $protectedEnrichmentKeyName = 'ClassRvk';
        $this->assertNotNull(new Opus_EnrichmentKey($protectedEnrichmentKeyName));

        $this->dispatch($this->getControllerPath() . '/edit/id/' . $protectedEnrichmentKeyName);

        $this->assertRedirect();
        $this->assertRedirectTo($this->getControllerPath());
        $this->verifyFlashMessage('controller_crud_model_not_modifiable', self::MESSAGE_LEVEL_FAILURE);

        $enrichmentKey = new Opus_EnrichmentKey($protectedEnrichmentKeyName);
        $this->assertEquals($protectedEnrichmentKeyName, $enrichmentKey->getName());
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
            'Validation' => '1',
            'Save' => 'Speichern'
        ]);

        $this->dispatch($this->getControllerPath() . '/edit');
        $this->assertRedirectTo($this->getControllerPath());
        $this->verifyFlashMessage('controller_crud_save_success', self::MESSAGE_LEVEL_NOTICE);

        $enrichmentKey = new Opus_EnrichmentKey('MyTestEnrichmentModified');
        $this->assertNotNull($enrichmentKey);
        $this->assertEquals('MyTestEnrichmentModified', $enrichmentKey->getName());
        $this->assertEquals('RegexType', $enrichmentKey->getType());
        $this->assertEquals(json_encode(["regex" => "^.*$", "validation" => "strict"]), $enrichmentKey->getOptions());

        new Opus_EnrichmentKey('MyTestEnrichment');

        $this->fail('Previous statement should have thrown exception.');
    }

    /**
     * @expectedException Opus_Model_NotFoundException
     * @expectedExceptionMessage No Opus_Db_EnrichmentKeys with id ClassRvkModified in database.
     */
    public function testEditActionSaveForProtectedEnrichment()
    {
        $protectedEnrichmentKeyName = 'ClassRvk';
        $this->assertNotNull(new Opus_EnrichmentKey($protectedEnrichmentKeyName));

        $this->getRequest()->setMethod('POST')->setPost([
            'Id' => $protectedEnrichmentKeyName,
            'Name' => "${protectedEnrichmentKeyName}Modified",
            'Type' => 'TextType',
            'Options' => '',
            'Validation' => '0',
            'Save' => 'Speichern'
        ]);

        $this->dispatch($this->getControllerPath() . '/edit');

        $this->assertRedirect();
        $this->assertRedirectTo($this->getControllerPath());
        $this->verifyFlashMessage('controller_crud_model_not_modifiable', self::MESSAGE_LEVEL_FAILURE);

        $enrichmentKey = new Opus_EnrichmentKey($protectedEnrichmentKeyName);
        $this->assertEquals($protectedEnrichmentKeyName, $enrichmentKey->getName());

        new Opus_EnrichmentKey("${protectedEnrichmentKeyName}Modified");
        $this->fail('Previous statement should have thrown exception.');
    }

    public function testEditActionSaveWithoutEnrichmentType()
    {
        $this->createsModels = true;

        $enrichmentKey = new Opus_EnrichmentKey();
        $enrichmentKey->setName('MyTestEnrichment');
        $enrichmentKey->setType('TextType');
        $enrichmentKey->store();

        $this->getRequest()->setMethod('POST')->setPost([
            'Id' => 'MyTestEnrichment',
            'Name' => 'MyTestEnrichment',
            'Save' => 'Speichern'
        ]);

        $this->dispatch($this->getControllerPath() . '/edit');
        $this->assertResponseCode(200);

        $this->assertXpath("//form/div[2]/div[2]/ul[@class='errors']", $this->getResponse()->getBody());
    }

    public function testEditActionSaveWithEmptyEnrichmentType()
    {
        $this->createsModels = true;

        $enrichmentKey = new Opus_EnrichmentKey();
        $enrichmentKey->setName('MyTestEnrichment');
        $enrichmentKey->setType('TextType');
        $enrichmentKey->store();

        $this->getRequest()->setMethod('POST')->setPost([
            'Id' => 'MyTestEnrichment',
            'Name' => 'MyTestEnrichment',
            'Type' => '',
            'Save' => 'Speichern'
        ]);

        $this->dispatch($this->getControllerPath() . '/edit');
        $this->assertResponseCode(200);

        $this->assertXpath("//form/div[2]/div[2]/ul[@class='errors']", $this->getResponse()->getBody());
    }

    public function testEditActionSaveWithExistingEnrichmentType()
    {
        $this->createsModels = true;

        $enrichmentKey = new Opus_EnrichmentKey();
        $enrichmentKey->setName('MyTestEnrichment');
        $enrichmentKey->setType('TextType');
        $enrichmentKey->store();

        $this->getRequest()->setMethod('POST')->setPost([
            'Id' => 'MyTestEnrichment',
            'Name' => 'MyTestEnrichment',
            'Type' => 'BooleanType',
            'Save' => 'Speichern'
        ]);

        $this->dispatch($this->getControllerPath() . '/edit');
        $this->assertResponseCode(302);

        $enrichmentKey = Opus_EnrichmentKey::fetchByName('MyTestEnrichment');
        $this->assertEquals('BooleanType', $enrichmentKey->getEnrichmentType()->getName());
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
            'Validation' => '0',
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
     * @expectedExceptionMessage No Opus_Db_EnrichmentKeys with id ClassRvkModified in database.
     */
    public function testEditActionCancelForProtectedEnrichment()
    {
        $protectedEnrichmentKeyName = 'ClassRvk';
        $this->assertNotNull(new Opus_EnrichmentKey($protectedEnrichmentKeyName));

        $this->getRequest()->setMethod('POST')->setPost([
            'Id' => $protectedEnrichmentKeyName,
            'Name' => "${protectedEnrichmentKeyName}Modified",
            'Cancel' => 'Abbrechen'
        ]);

        $this->dispatch($this->getControllerPath() . '/edit');

        $this->assertRedirect();
        $this->assertRedirectTo($this->getControllerPath());

        $enrichmentKey = new Opus_EnrichmentKey($protectedEnrichmentKeyName);
        $this->assertEquals($protectedEnrichmentKeyName, $enrichmentKey->getName());

        new Opus_EnrichmentKey("${protectedEnrichmentKeyName}Modified");
        $this->fail('Previous statement should have thrown exception.');
    }

    /**
     * @dataProvider enrichmentKeyNamesProvider
     */
    public function testDeleteActionShowFormForUnprotectedEnrichmentKey($enrichmentKeyName)
    {
        $this->useEnglish();

        $this->dispatch($this->getControllerPath() . '/delete/id/' . $enrichmentKeyName);

        $this->assertQueryContentContains('legend', 'Delete EnrichmentKey');
        $this->assertQueryContentContains('span.displayname', $enrichmentKeyName);
        $this->assertQuery('input#ConfirmYes');
        $this->assertQuery('input#ConfirmNo');

        $enrichmentKey = new Opus_EnrichmentKey($enrichmentKeyName);

        $this->assertNotNull($enrichmentKey);
        $this->assertEquals($enrichmentKeyName, $enrichmentKey->getName());
    }

    public function testDeleteActionShowFormForProtectedEnrichmentKey()
    {
        $protectedEnrichmentKeyName = 'ClassRvk';
        $this->assertNotNull(new Opus_EnrichmentKey($protectedEnrichmentKeyName));

        $this->dispatch($this->getControllerPath() . '/delete/id/' . $protectedEnrichmentKeyName);

        $this->assertRedirect();
        $this->assertRedirectTo($this->getControllerPath());
        $this->verifyFlashMessage('controller_crud_model_cannot_delete', self::MESSAGE_LEVEL_FAILURE);

        $enrichmentKey = new Opus_EnrichmentKey($protectedEnrichmentKeyName);
        $this->assertEquals($protectedEnrichmentKeyName, $enrichmentKey->getName());
    }

    public function testRemoveFromDocsShowFormForProtectedEnrichment()
    {
        $protectedEnrichmentKeyName = 'ClassRvk';
        $this->assertNotNull(new Opus_EnrichmentKey($protectedEnrichmentKeyName));

        $this->dispatch($this->getControllerPath() . '/removeFromDocs/id/' . $protectedEnrichmentKeyName);

        $this->assertRedirect();
        $this->assertRedirectTo($this->getControllerPath());
        $this->verifyFlashMessage('controller_crud_model_cannot_delete', self::MESSAGE_LEVEL_FAILURE);

        $enrichmentKey = new Opus_EnrichmentKey($protectedEnrichmentKeyName);
        $this->assertEquals($protectedEnrichmentKeyName, $enrichmentKey->getName());
    }

    public function testRemoveFromDocsShowFormForUnprotectedEnrichment()
    {
        $enrichmentKeyName = 'Audience'; // wird von einem Dokument verwendet
        $enrichmentKey = new Opus_EnrichmentKey($enrichmentKeyName);
        $this->assertEquals($enrichmentKeyName, $enrichmentKey->getName());

        $this->useEnglish();

        $this->dispatch($this->getControllerPath() . '/removeFromDocs/id/' . $enrichmentKeyName);

        $this->assertQueryContentContains('legend', "Remove enrichment key $enrichmentKeyName from all documents");
        $this->assertQueryContentContains('fieldset.headline', $enrichmentKeyName);
        $this->assertQuery('input#ConfirmYes');
        $this->assertQuery('input#ConfirmNo');

        $enrichmentKey = new Opus_EnrichmentKey($enrichmentKeyName);
        $this->assertEquals($enrichmentKeyName, $enrichmentKey->getName());
    }

    public function testRemoveFromDocsForProtectedEnrichmentKey()
    {
        $protectedEnrichmentKeyName = 'ClassRvk';
        $this->assertNotNull(new Opus_EnrichmentKey($protectedEnrichmentKeyName));

        $post = [
            'Id' => $protectedEnrichmentKeyName,
            'ConfirmYes' => 'Yes',
        ];

        $this->getRequest()->setPost($post)->setMethod('POST');
        $this->dispatch($this->getControllerPath() . '/removeFromDocs/id/' . $protectedEnrichmentKeyName);

        $this->assertRedirect();
        $this->assertRedirectTo($this->getControllerPath());
        $this->verifyFlashMessage('controller_crud_model_cannot_delete', self::MESSAGE_LEVEL_FAILURE);

        $enrichmentKey = new Opus_EnrichmentKey($protectedEnrichmentKeyName);
        $this->assertEquals($protectedEnrichmentKeyName, $enrichmentKey->getName());
    }

    public function testRemoveFromDocsForUnprotectedEnrichmentKey()
    {
        $enrichmentKeyName = 'testRemoveFromDocsForUnprotectedEnrichmentKey';
        $this->createsModels = true; // damit am Ende des Test ein Cleanup durchgeführt wird (neu angelegter EK wird gelöscht)

        $enrichmentKey = new Opus_EnrichmentKey();
        $enrichmentKey->setName($enrichmentKeyName);
        $enrichmentKey->setType('TextType');
        $enrichmentKey->store();

        $this->assertContains($enrichmentKeyName, Opus_EnrichmentKey::getAll(true));

        // assign test document to enrichment key
        $doc = $this->createTestDocument();

        $enrichment = new Opus_Enrichment();
        $enrichment->setKeyName($enrichmentKeyName);
        $enrichment->setValue('foo');
        $doc->addEnrichment($enrichment);
        $docId = $doc->store();

        $doc = new Opus_Document($docId);
        $this->assertCount(1, $doc->getEnrichment());

        $this->useEnglish();

        $post = [
            'Id' => $enrichmentKeyName,
            'ConfirmYes' => 'Yes',
        ];

        $this->getRequest()->setPost($post)->setMethod('POST');
        $this->dispatch($this->getControllerPath() . '/removeFromDocs/id/' . $enrichmentKeyName);

        $this->assertRedirect();
        $this->assertRedirectTo($this->getControllerPath());
        $this->verifyFlashMessage('controller_crud_delete_success', self::MESSAGE_LEVEL_NOTICE);

        // EnrichmentKey muss noch vorhanden sein, aber das entsprechende Enrichment im Testdokument wurde gelöscht
        $enrichmentKey = new Opus_EnrichmentKey($enrichmentKeyName);
        $this->assertEquals($enrichmentKeyName, $enrichmentKey->getName());

        $doc = new Opus_Document($docId);
        $this->assertCount(0, $doc->getEnrichment());
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
            'Validation' => '1',
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
        $this->assertTrue($enrichmentKey->getEnrichmentType()->isStrictValidation());

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
        $this->assertXpath('//*[@id="admin_enrichmentkey_validation" and @checked="checked" and @value="' . $post['Validation'] . '"]');

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
            'Validation' => '0',
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
        $this->assertNull($enrichmentKey->getOptions());
        $this->assertNull($enrichmentKey->getEnrichmentType()->getOptionsAsString());
        $this->assertFalse($enrichmentKey->getEnrichmentType()->isStrictValidation());

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
        $this->assertNotXpath('//*[@id="admin_enrichmentkey_validation" and @checked="checked"]');

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

    /**
     * Tests the function isProtected()
     *
     * @covers ::isProtected
     */
    public function testProtectedCssClassIsSet()
    {
        $enrichmentKeys = new Admin_Model_EnrichmentKeys();
        $protectedKeys = $enrichmentKeys->getProtectedEnrichmentKeys();
        $this->dispatch($this->getControllerPath());
        $response = $this->getResponse();
        $this->checkForBadStringsInHtml($response->getBody());
        foreach ($protectedKeys as &$value) {
            if (strpos($response->getBody(), $value) !== false) {
                // Xpath looks, if value has an protected css-class in enrichmentkeyTable
                $this->assertXpathContentContains('//table[@id="enrichmentkeyTable"]
                //tr[contains(@class,\'protected\')]', $value);
            }
        }
    }

    /**
     * Tests the function isUsed()
     *
     * @covers ::isUsed
     */
    public function testUsedCssClassIsSet()
    {
        $usedKeys = Opus_EnrichmentKey::getAllReferenced();
        $this->dispatch($this->getControllerPath());
        $response = $this->getResponse();
        $this->checkForBadStringsInHtml($response->getBody());
        foreach ($usedKeys as &$value) {
            if (strpos($response->getBody(), $value) !== false) {
                // Xpath looks, if value has an used css-class in enrichmentkeyTable
                $this->assertXpathContentContains('//table[@id="enrichmentkeyTable"]
                //tr[contains(@class,\'used\')]', $value);
            }
        }
    }

    /**
     * Tests the function isProtected()
     *
     * @covers ::isProtected
     */
    public function testProtectedCssClassIsNotSet()
    {
        $enrichmentKeys = new Admin_Model_EnrichmentKeys();
        $unprotectedKeys = array_diff($this->allEnrichmentKeys, $enrichmentKeys->getProtectedEnrichmentKeys());
        $this->dispatch($this->getControllerPath());
        $response = $this->getResponse();
        $this->checkForBadStringsInHtml($response->getBody());
        foreach ($unprotectedKeys as &$value) {
            if (strpos($response->getBody(), $value) !== false) {
                // Xpath looks, if value has an protected css-class in enrichmentkeyTable
                $this->assertNotXpathContentContains('//table[@id="enrichmentkeyTable"]
                //tr[contains(@class,\'protected\')]', $value);
            }
        }
    }

    /**
     * Tests the function isUsed()
     *
     * @covers ::isUsed
     */
    public function testUsedCssClassIsNotSet()
    {
        $unusedKeys = array_diff($this->allEnrichmentKeys, Opus_EnrichmentKey::getAllReferenced());
        $this->dispatch($this->getControllerPath());
        $response = $this->getResponse();
        $this->checkForBadStringsInHtml($response->getBody());
        foreach ($unusedKeys as &$value) {
            if (strpos($response->getBody(), $value) !== false) {
                // Xpath looks, if value has an unused css-class in enrichmentkeyTable
                $this->assertXpathContentContains('//table[@id="enrichmentkeyTable"]
                //tr[contains(@class,\'unused\')]', $value);
            }
        }
    }

    public function testInitializedNewFormWithoutKeyName()
    {
        $this->dispatch($this->getControllerPath() . '/new');

        // Formularfeld "Name" soll nicht gefüllt sein
        $this->assertXpath('//form//input[@id="Name" and @value=""]');
    }

    public function testInitializedNewFormWithUnregisteredKeyName()
    {
        $doc = $this->createTestDocument();

        $enrichment = new Opus_Enrichment();
        $enrichment->setKeyName('unregistered');
        $enrichment->setValue('value');

        $doc->addEnrichment($enrichment);
        $doc->store();

        $this->dispatch($this->getControllerPath() . '/new/id/unregistered');

        // Formularfeld "Name" muss gefüllt sein
        $this->assertXpath('//form//input[@id="Name" and @value="unregistered"]');
    }

    public function testInitializedNewFormWithRegisteredUnusedKeyName()
    {
        $enrichmentKey = new Opus_EnrichmentKey();
        $enrichmentKey->setName('unused');
        $id = $enrichmentKey->store();

        $this->dispatch($this->getControllerPath() . '/new/id/unused');

        $enrichmentKey = new Opus_EnrichmentKey($id);
        $enrichmentKey->delete();

        // Formularfeld "Name" darf nicht gefüllt sein
        $this->assertXpath('//form//input[@id="Name" and @value=""]');
    }

    public function testInitializedNewFormWithRegisteredUsedKeyName()
    {
        $enrichmentKey = new Opus_EnrichmentKey();
        $enrichmentKey->setName('used');
        $id = $enrichmentKey->store();

        $doc = $this->createTestDocument();

        $enrichment = new Opus_Enrichment();
        $enrichment->setKeyName('used');
        $enrichment->setValue('value');

        $doc->addEnrichment($enrichment);
        $doc->store();

        $this->dispatch($this->getControllerPath() . '/new/id/used');

        $enrichmentKey = new Opus_EnrichmentKey($id);
        $enrichmentKey->delete();

        // Formularfeld "Name" darf nicht gefüllt sein
        $this->assertXpath('//form//input[@id="Name" and @value=""]');
    }

    public function testInitializedNewFormWithUnknownKeyName()
    {
        $this->dispatch($this->getControllerPath() . '/new/id/testInitializedNewFormWithUnknownKeyName');

        // Formularfeld "Name" darf nicht gefüllt sein
        $this->assertXpath('//form//input[@id="Name" and @value=""]');
    }

    public function testRemoveFromDocsActionWithoutId()
    {
        $this->dispatch($this->getControllerPath() . '/removeFromDocs');
        $this->assertRedirectTo('/admin/enrichmentkey');
    }

    public function testRemoveFromDocsActionWithoutEmptyId()
    {
        $this->dispatch($this->getControllerPath() . '/removeFromDocs/id/');
        $this->assertRedirectTo('/admin/enrichmentkey');
    }

    public function testRemoveFromDocsActionWithoutUnknownId()
    {
        $this->dispatch($this->getControllerPath() . '/removeFromDocs/id/testRemoveFromDocsActionWithoutUnknownId');
        $this->assertRedirectTo('/admin/enrichmentkey');
    }

    public function testRemoveFromDocsActionWithUnregisteredId()
    {
        $doc = $this->createTestDocument();

        $enrichment = new Opus_Enrichment();
        $enrichment->setKeyName('testRemoveFromDocsActionWithUnregisteredId');
        $enrichment->setValue('value');

        $doc->addEnrichment($enrichment);
        $doc->store();

        $this->dispatch($this->getControllerPath() . '/removeFromDocs/id/testRemoveFromDocsActionWithUnregisteredId');
        $this->assertResponseCode(200);
        $this->assertXpathContentContains('//form/fieldset/legend/text()', 'testRemoveFromDocsActionWithUnregisteredId');
    }

    public function testRemoveFromDocsActionWithRegisteredUnusedId()
    {
        $enrichmentKey = new Opus_EnrichmentKey();
        $enrichmentKey->setName('unused');
        $id = $enrichmentKey->store();

        $this->dispatch($this->getControllerPath() . '/removeFromDocs/id/unused');

        $enrichmentKey = new Opus_EnrichmentKey($id);
        $enrichmentKey->delete();

        $this->assertRedirectTo('/admin/enrichmentkey');
    }

    public function testRemoveFromDocsActionWithRegisteredUsedId()
    {
        $enrichmentKey = new Opus_EnrichmentKey();
        $enrichmentKey->setName('used');
        $id = $enrichmentKey->store();

        $doc = $this->createTestDocument();

        $enrichment = new Opus_Enrichment();
        $enrichment->setKeyName('used');
        $enrichment->setValue('value');

        $doc->addEnrichment($enrichment);
        $doc->store();

        $this->dispatch($this->getControllerPath() . '/removeFromDocs/id/used');

        $enrichmentKey = new Opus_EnrichmentKey($id);
        $enrichmentKey->delete();

        $this->assertResponseCode(200);
        $this->assertXpathContentContains('//form/fieldset/legend/text()', 'used');
    }

    public function testRemoveFromDocsActionPostWithUnregisteredKey()
    {
        $doc = $this->createTestDocument();

        $enrichment = new Opus_Enrichment();
        $enrichment->setKeyName('unregistered');
        $enrichment->setValue('value');

        $doc->addEnrichment($enrichment);
        $id = $doc->store();

        $post = [
            'Id' => 'unregistered',
            'ConfirmYes' => 'Yes'
        ];

        $this->getRequest()->setPost($post)->setMethod('POST');
        $this->dispatch($this->getControllerPath() . '/removeFromDocs');

        $this->assertRedirectTo('/admin/enrichmentkey');

        // das Testdokument sollte kein Enrichment mehr haben
        $doc = new Opus_Document($id);
        $this->assertNull($doc->getEnrichment('unregistered'));
    }

    public function testRemoveFromDocsActionPostWithRegisteredKey()
    {
        $enrichmentKey = new Opus_EnrichmentKey();
        $enrichmentKey->setName('used');
        $id = $enrichmentKey->store();

        $doc = $this->createTestDocument();

        $enrichment = new Opus_Enrichment();
        $enrichment->setKeyName('used');
        $enrichment->setValue('value');

        $doc->addEnrichment($enrichment);
        $docId = $doc->store();

        $post = [
            'Id' => 'used',
            'ConfirmYes' => 'Yes'
        ];

        $this->getRequest()->setPost($post)->setMethod('POST');
        $this->dispatch($this->getControllerPath() . '/removeFromDocs');

        $this->assertRedirectTo('/admin/enrichmentkey');

        // das Testdokument sollte kein Enrichment mehr haben; der EnrichmentKey muss weiterhin existieren
        $doc = new Opus_Document($docId);
        $this->assertNull($doc->getEnrichment('used'));
        $enrichmentKey = new Opus_EnrichmentKey($id);
        $this->assertNotNull($enrichmentKey);

        // cleanup
        $enrichmentKey->delete();
    }

    public function testIndexPageShowUnregisteredKeys()
    {
        $doc = $this->createTestDocument();

        $enrichment = new Opus_Enrichment();
        $enrichment->setKeyName('unregistered');
        $enrichment->setValue('value');

        $doc->addEnrichment($enrichment);
        $doc->store();

        $this->dispatch($this->getControllerPath() . '/');

        $this->assertResponseCode(200);
        $this->assertContains('admin/enrichmentkey/new/id/unregistered', $this->getResponse()->getBody());
        $this->assertNotContains('admin/enrichmentkey/edit/id/unregistered', $this->getResponse()->getBody());
        $this->assertContains('admin/enrichmentkey/removeFromDocs/id/unregistered', $this->getResponse()->getBody());
        $this->assertNotContains('admin/enrichmentkey/delete/id/unregistered', $this->getResponse()->getBody());
    }

    public function testIndexPageShowRegisteredUnusedKeys()
    {
        $enrichmentKey = new Opus_EnrichmentKey();
        $enrichmentKey->setName('unused');
        $id = $enrichmentKey->store();

        $this->dispatch($this->getControllerPath() . '/');

        $enrichmentKey = new Opus_EnrichmentKey($id);
        $enrichmentKey->delete();

        $this->assertResponseCode(200);
        $this->assertNotContains('admin/enrichmentkey/new/id/unused', $this->getResponse()->getBody());
        $this->assertContains('admin/enrichmentkey/edit/id/unused', $this->getResponse()->getBody());
        $this->assertNotContains('admin/enrichmentkey/removeFromDocs/id/unused', $this->getResponse()->getBody());
        $this->assertContains('admin/enrichmentkey/delete/id/unused', $this->getResponse()->getBody());
    }

    public function testIndexPageShowRegisteredUsedKeys()
    {
        $enrichmentKey = new Opus_EnrichmentKey();
        $enrichmentKey->setName('used');
        $id = $enrichmentKey->store();

        $doc = $this->createTestDocument();

        $enrichment = new Opus_Enrichment();
        $enrichment->setKeyName('used');
        $enrichment->setValue('value');

        $doc->addEnrichment($enrichment);
        $doc->store();

        $this->dispatch($this->getControllerPath() . '/');

        $enrichmentKey = new Opus_EnrichmentKey($id);
        $enrichmentKey->delete();

        $this->assertResponseCode(200);
        $this->assertNotContains('admin/enrichmentkey/new/id/used', $this->getResponse()->getBody());
        $this->assertContains('admin/enrichmentkey/edit/id/used', $this->getResponse()->getBody());
        $this->assertContains('admin/enrichmentkey/removeFromDocs/id/used', $this->getResponse()->getBody());
        $this->assertContains('admin/enrichmentkey/delete/id/used', $this->getResponse()->getBody());
    }

    public function testNewActionWithRenamingUnregisteredKey()
    {
        // prepare test document with unregistered enrichment key
        $doc = $this->createTestDocument();
        $enrichment = new Opus_Enrichment();
        $enrichment->setKeyName('unregistered');
        $enrichment->setValue('value');
        $doc->addEnrichment($enrichment);
        $id = $doc->store();

        $post = [
            'Name' => 'unregisterednew',
            'Type' => 'TextType',
            'Options' => '',
            'Save' => 'Speichern'
        ];

        $this->getRequest()->setPost($post)->setMethod('POST');
        $this->dispatch($this->getControllerPath() . '/new/id/unregistered');

        $this->assertRedirect();
        $this->assertRedirectRegex('/^\/admin\/enrichmentkey/');

        $enrichmentKey = Opus_EnrichmentKey::fetchByName('unregistered');
        $this->assertNull($enrichmentKey);

        $enrichmentKey = Opus_EnrichmentKey::fetchByName('unregisterednew');
        $this->assertNotNull($enrichmentKey);

        $doc = new Opus_Document($id);
        $enrichments = $doc->getEnrichment();
        $this->assertCount(1, $enrichments);
        $this->assertEquals('unregisterednew', $enrichments[0]->getKeyName());

        // Cleanup
        $enrichmentKey->delete();
    }

    public function testNewActionWithoutRenamingUnregisteredKey()
    {
        // prepare test document with unregistered enrichment key
        $doc = $this->createTestDocument();
        $enrichment = new Opus_Enrichment();
        $enrichment->setKeyName('unregistered');
        $enrichment->setValue('value');
        $doc->addEnrichment($enrichment);
        $id = $doc->store();

        $post = [
            'Name' => 'unregistered',
            'Type' => 'TextType',
            'Options' => '',
            'Save' => 'Speichern'
        ];

        $this->getRequest()->setPost($post)->setMethod('POST');
        $this->dispatch($this->getControllerPath() . '/new/id/unregistered');

        $this->assertRedirect();
        $this->assertRedirectRegex('/^\/admin\/enrichmentkey/');

        $enrichmentKey = Opus_EnrichmentKey::fetchByName('unregistered');
        $this->assertNotNull($enrichmentKey);

        $doc = new Opus_Document($id);
        $enrichments = $doc->getEnrichment();
        $this->assertCount(1, $enrichments);
        $this->assertEquals('unregistered', $enrichments[0]->getKeyName());

        // Cleanup
        $enrichmentKey->delete();
    }

    public function enrichmentKeyNamesProvider()
    {
        return [
            ['BibtexRecord'], // unreferenced enrichment key
            ['Audience']      // referenced enrichment key
        ];
    }
}
