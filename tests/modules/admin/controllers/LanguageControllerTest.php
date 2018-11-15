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
 * @author      Maximilian Salomon <salomon@zib.de>
 * @copyright   Copyright (c) 2008-2018, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Basic unit tests for Admin_LanguageController class.
 *
 * @covers Admin_LanguageController
 */
class Admin_LanguageControllerTest extends CrudControllerTestCase {

    public function setUp() {
        $this->setController('language');
        parent::setUp();
    }

    public function getModels() {
        return Opus_Language::getAll();
    }

    public function testShowAction() {
        $this->createsModels = true;

        $language = new Opus_Language();

        $language->setActive(true);
        $language->setRefName('German');
        $language->setPart2T('deu');
        $language->setPart2B('ger');
        $language->setPart1('de');
        $language->setScope('I');
        $language->setType('L');
        $language->setComment('test comment');

        $modelId = $language->store();

        $this->dispatch('/admin/language/show/id/' . $modelId);

        $model = new Opus_Language($modelId);
        $model->delete();

        $this->assertResponseCode(200);
        $this->assertController('language');
        $this->assertAction('show');

        $this->assertQueryContentRegex('div#Active', '/Yes|Ja/');
        $this->assertQueryContentContains('div#RefName', 'German');
        $this->assertQueryContentContains('div#Part2T', 'deu');
        $this->assertQueryContentContains('div#Part2B', 'ger');
        $this->assertQueryContentContains('div#Part1', 'de');
        $this->assertQueryContentRegex('div#Scope', '/Individual|Individuell/');
        $this->assertQueryContentRegex('div#Type', '/Living|Lebend/');
        $this->assertQueryContentContains('div#Comment', 'test comment');

        // TODO $this->validateXHTML();
    }

    /**
     * Test, ob Active Status für Wert false (0) angezeigt wird.
     */
    public function testShowActiveValueForInactiveLicence() {
        $this->dispatch('/admin/language/show/id/3'); // Italian (3) is disabled
        $this->assertResponseCode(200);
        $this->assertController('language');
        $this->assertAction('show');

        $this->assertQueryContentRegex('div#Active', '/No|Nein/');
    }

    public function testNewActionSave() {
        $this->createsModels = true;

        $post = array(
            'Active' => '1',
            'RefName' => 'German',
            'Part2T' => 'deu',
            'Part2B' => 'ger',
            'Part1' => 'de',
            'Scope' => 'I',
            'Type' => 'L',
            'Comment' => 'test comment',
            'Save' => 'Speichern'
        );

        $this->getRequest()->setPost($post)->setMethod('POST');

        $this->dispatch('/admin/language/new');

        $this->assertRedirect('Should be a redirect to show action.');
        $this->assertRedirectRegex('/^\/admin\/language\/show/'); // Regex weil danach noch '/id/xxx' kommt
        $this->verifyFlashMessage('controller_crud_save_success', self::MESSAGE_LEVEL_NOTICE);

        // Neue Lizenz anzeigen
        $location = $this->getLocation();

        $this->resetRequest();
        $this->resetResponse();

        $this->dispatch($location);
        $this->assertResponseCode(200);

        $this->assertQueryContentRegex('div#Active', '/Yes|Ja/');
        $this->assertQueryContentContains('div#RefName', 'German');
        $this->assertQueryContentContains('div#Part2T', 'deu');
        $this->assertQueryContentContains('div#Part2B', 'ger');
        $this->assertQueryContentContains('div#Part1', 'de');
        $this->assertQueryContentRegex('div#Scope', '/Individual|Individuell/');
        $this->assertQueryContentRegex('div#Type', '/Living|Lebend/');
        $this->assertQueryContentContains('div#Comment', 'test comment');
    }

    public function testNewActionCancel() {
        $this->createsModels = true;

        $modelCount = count($this->getModels());

        $post = array(
            'RefName' => 'TestGerman',
            'Part2T' => 'tge',
            'Cancel' => 'Abbrechen'
        );

        $this->getRequest()->setPost($post)->setMethod('POST');

        $this->dispatch('/admin/language/new');

        $this->assertRedirectTo('/admin/language', 'Should be a redirect to index action.');

        $this->assertEquals($modelCount, count(Opus_Language::getAll()),
            'Es sollte keine neue Sprache geben.');
    }


    /**
     * Tests 'edit' action.
     */
    public function testEditActionShowForm() {
        $this->dispatch('/admin/language/edit/id/3');
        $this->assertResponseCode(200);
        $this->assertController('language');
        $this->assertAction('edit');

        $this->assertQueryContentContains('div#RefName-element', 'Italian');
        $this->assertQuery('li.save-element');
        $this->assertQuery('li.cancel-element');
        $this->assertQueryCount('input#Id', 1);
    }

    public function testEditActionSave() {
        $this->createsModels = true;

        $model = new Opus_Language();

        $model->setRefName('Test');
        $model->setPart2T('tst');

        $modelId = $model->store();

        $this->getRequest()->setMethod('POST')->setPost(array(
            'Id' => $modelId,
            'Active' => '1',
            'RefName' => 'RefNameModified',
            'Part2T' => 'n2t',
            'Part2B' => 'tet',
            'Part1' => 'us',
            'Scope' => 'I',
            'Type' => 'L',
            'Comment' => 'test comment',
            'Save' => 'Speichern'
        ));

        $this->dispatch('/admin/language/edit');
        $this->assertRedirectTo('/admin/language/show/id/' . $modelId);
        $this->verifyFlashMessage('controller_crud_save_success', self::MESSAGE_LEVEL_NOTICE);

        $model = new Opus_Language($modelId);

        $this->assertEquals(1, $model->getActive());
        $this->assertEquals('RefNameModified', $model->getRefName());
        $this->assertEquals('n2t', $model->getPart2T());
        $this->assertEquals('tet', $model->getPart2B());
        $this->assertEquals('us', $model->getPart1());
        $this->assertEquals('I', $model->getScope());
        $this->assertEquals('L', $model->getType());
        $this->assertEquals('test comment', $model->getComment());
    }

    public function testEditActionCancel() {
        $this->createsModels = true;

        $model = new Opus_Language();

        $model->setRefName('Test');
        $model->setPart2T('tst');

        $modelId = $model->store();

        $this->getRequest()->setMethod('POST')->setPost(array(
            'Id' => $modelId,
            'RefName' => 'RefNameModified',
            'Part2T' => 'tes',
            'Cancel' => 'Abbrechen'
        ));

        $this->dispatch('/admin/language/edit');
        $this->assertRedirectTo('/admin/language');

        $model = new Opus_Language($modelId);

        $this->assertEquals('Test', $model->getRefName());
    }

    public function testDeleteActionShowForm() {
        $this->useEnglish();

        $this->dispatch('/admin/language/delete/id/3');

        $this->assertQueryContentContains('legend', 'Delete Language');
        $this->assertQueryContentContains('span.displayname', 'Italian');
        $this->assertQuery('input#ConfirmYes');
        $this->assertQuery('input#ConfirmNo');
    }

    public function createNewModel() {
        $model = new Opus_Language();

        $model->setRefName('TestLang');
        $model->setPart2T('lan');

        return $model->store();
    }

    public function getModel($identifier) {
        return new Opus_Language($identifier);
    }

}