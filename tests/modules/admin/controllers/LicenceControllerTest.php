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
 */

/**
 * Unit Tests für Klasse Admin_LicenceController.
 *
 * @category    Tests
 * @package     Admin
 * @author      Jens Schwidder <schwidder@zib.de>
 * @author      Maximilian Salomon <salomon@zib.de>
 * @copyright   Copyright (c) 2008-2018, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 *
 * @covers Admin_LicenceController
 */
class Admin_LicenceControllerTest extends CrudControllerTestCase {

    public function setUp() {
        $this->setController('licence');

        parent::setUp();
    }

    public function getModels() {
        return Opus_Licence::getAll();
    }

    /**
     * Tests 'show' action.
     */
    public function testShowAction() {
        $this->createsModels = true;

        $licence = new Opus_Licence();

        $licence->setActive(true);
        $licence->setNameLong('TestNameLong');
        $licence->setLanguage('deu');
        $licence->setLinkLicence('www.example.org/licence');
        $licence->setLinkLogo('www.example.org/licence/logo.png');
        $licence->setLinkSign('TestLinkSign'); // wird nicht angezeigt (soll später entfernt werden - OPUSVIER-1492)
        $licence->setDescText('TestDescText');
        $licence->setDescMarkup('TestDescMarkup');
        $licence->setCommentInternal('TestCommentInternal');
        $licence->setMimeType('text/plain');
        $licence->setPodAllowed(false);
        $licence->setSortOrder(100);

        $licenceId = $licence->store();

        $this->dispatch('/admin/licence/show/id/' . $licenceId);

        $licence = new Opus_Licence($licenceId);
        $licence->delete();

        $this->assertResponseCode(200);
        $this->assertController('licence');
        $this->assertAction('show');

        $this->assertQueryContentRegex('div#Active', '/Yes|Ja/');
        $this->assertQueryContentContains('div#NameLong', 'TestNameLong');
        $this->assertQueryContentRegex('div#Language', '/German|Deutsch/');
        $this->assertQueryContentContains('div#LinkLicence', 'www.example.org/licence');
        $this->assertQueryContentContains('div#LinkLogo', 'www.example.org/licence/logo.png');
        $this->assertQueryContentContains('div#DescText', 'TestDescText');
        $this->assertQueryContentContains('div#DescMarkup', 'TestDescMarkup');
        $this->assertQueryContentContains('div#CommentInternal', 'TestCommentInternal');
        $this->assertQueryContentContains('div#MimeType', 'text/plain');
        $this->assertQueryContentRegex('div#PodAllowed', '/No|Nein/');
        $this->assertQueryContentContains('div#SortOrder', '100');

        // wird nicht angezeigt - OPUSVIER-1492
        $this->assertQueryCount('div#LinkSign', 0);
        $this->assertNotQueryContentContains('div#content', 'TestLinkSign');

        $this->validateXHTML();
    }

    /**
     * Test, ob Active Status für Wert false (0) angezeigt wird.
     */
    public function testShowActiveValueForInactiveLicence() {
        $this->dispatch('/admin/licence/show/id/20');
        $this->assertResponseCode(200);
        $this->assertController('licence');
        $this->assertAction('show');

        $this->assertQueryContentRegex('div#Active', '/No|Nein/');
    }

    public function testNewActionSave() {
        $this->createsModels = true;

        $post = array(
            'Active' => '1',
            'NameLong' => 'TestNameLong',
            'Language' => 'eng',
            'LinkLicence' => 'www.example.org/licence',
            'LinkLogo' => 'www.example.org/licence/logo.png',
            'DescText' => 'TestDescText',
            'DescMarkup' => 'TestDescMarkup',
            'CommentInternal' => 'TestCommentInternal',
            'MimeType' => 'text/plain',
            'PodAllowed' => '0',
            'SortOrder' => '100',
            'Save' => 'Speichern'
        );

        $this->getRequest()->setPost($post)->setMethod('POST');

        $this->dispatch('/admin/licence/new');

        $this->assertRedirect('Should be a redirect to show action.');
        $this->assertRedirectRegex('/^\/admin\/licence\/show/'); // Regex weil danach noch '/id/xxx' kommt
        $this->verifyFlashMessage('controller_crud_save_success', self::MESSAGE_LEVEL_NOTICE);

        // Neue Lizenz anzeigen
        $location = $this->getLocation();

        $this->resetRequest();
        $this->resetResponse();

        $this->dispatch($location);
        $this->assertResponseCode(200);

        $this->assertQueryContentRegex('div#Active', '/Yes|Ja/');
        $this->assertQueryContentContains('div#NameLong', 'TestNameLong');
        $this->assertQueryContentRegex('div#Language', '/English|Englisch/');
        $this->assertQueryContentContains('div#LinkLicence', 'www.example.org/licence');
        $this->assertQueryContentContains('div#LinkLogo', 'www.example.org/licence/logo.png');
        $this->assertQueryContentContains('div#DescText', 'TestDescText');
        $this->assertQueryContentContains('div#DescMarkup', 'TestDescMarkup');
        $this->assertQueryContentContains('div#CommentInternal', 'TestCommentInternal');
        $this->assertQueryContentContains('div#MimeType', 'text/plain');
        $this->assertQueryContentRegex('div#PodAllowed', '/No|Nein/');
        $this->assertQueryContentContains('div#SortOrder', '100');
    }

    public function testNewActionCancel() {
        $this->createsModels = true;

        $modelCount = count($this->getModels());

        $post = array(
            'NameLong' => 'TestNameLong',
            'Language' => 'eng',
            'LinkLicence' => 'www.example.org/licence',
            'Cancel' => 'Abbrechen'
        );

        $this->getRequest()->setPost($post)->setMethod('POST');

        $this->dispatch('/admin/licence/new');

        $this->assertRedirectTo('/admin/licence', 'Should be a redirect to index action.');

        $this->assertEquals($modelCount, count(Opus_Licence::getAll()),
            'Es sollte keine neue Lizenz geben.');
    }

    /**
     * Tests 'edit' action.
     */
    public function testEditActionShowForm() {
        $this->dispatch('/admin/licence/edit/id/4');
        $this->assertResponseCode(200);
        $this->assertController('licence');
        $this->assertAction('edit');

        $this->assertQueryContentContains('div#NameLong-element', 'Creative Commons - CC BY-ND - Namensnennung');
        $this->assertQuery('li.save-element');
        $this->assertQuery('li.cancel-element');
        $this->assertQueryCount('input#Id', 1);
    }

    public function testEditActionSave() {
        $this->createsModels = true;

        $licence = new Opus_Licence();

        $licence->setNameLong('NameLong');
        $licence->setLanguage('deu');
        $licence->setLinkLicence('LinkLicence');

        $licenceId = $licence->store();

        $this->getRequest()->setMethod('POST')->setPost(array(
            'Id' => $licenceId,
            'Active' => '1',
            'NameLong' => 'NameLongModified',
            'Language' => 'eng',
            'LinkLicence' => 'LinkLicenceModified',
            'LinkLogo' => 'LinkLogoAdded',
            'DescText' => 'DescTextAdded',
            'DescMarkup' => 'DescMarkupAdded',
            'CommentInternal' => 'CommentInternalAdded',
            'MimeType' => 'text/plain',
            'PodAllowed' => '1',
            'SortOrder' => '5',
            'Save' => 'Abspeichern'
        ));

        $this->dispatch('/admin/licence/edit');
        $this->assertRedirectTo('/admin/licence/show/id/' . $licenceId);
        $this->verifyFlashMessage('controller_crud_save_success', self::MESSAGE_LEVEL_NOTICE);

        $licence = new Opus_Licence($licenceId);

        $this->assertEquals(1, $licence->getActive());
        $this->assertEquals('NameLongModified', $licence->getNameLong());
        $this->assertEquals('eng', $licence->getLanguage());
        $this->assertEquals('LinkLicenceModified', $licence->getLinkLicence());
        $this->assertEquals('LinkLogoAdded', $licence->getLinkLogo());
        $this->assertEquals('DescTextAdded', $licence->getDescText());
        $this->assertEquals('DescMarkupAdded', $licence->getDescMarkup());
        $this->assertEquals('CommentInternalAdded', $licence->getCommentInternal());
        $this->assertEquals('text/plain', $licence->getMimeType());
        $this->assertEquals(1, $licence->getPodAllowed());
        $this->assertEquals(5, $licence->getSortOrder());
    }

    public function testEditActionCancel() {
        $this->createsModels = true;

        $licence = new Opus_Licence();

        $licence->setNameLong('NameLong');
        $licence->setLanguage('deu');
        $licence->setLinkLicence('LinkLicence');

        $licenceId = $licence->store();

        $this->getRequest()->setMethod('POST')->setPost(array(
            'Id' => $licenceId,
            'NameLong' => 'NameLongModified',
            'Language' => 'eng',
            'LinkLicence' => 'LinkLicenceModified',
            'Cancel' => 'Cancel'
        ));

        $this->dispatch('/admin/licence/edit');
        $this->assertRedirectTo('/admin/licence');

        $licence = new Opus_Licence($licenceId);

        $this->assertEquals('NameLong', $licence->getNameLong());
    }

    public function testDeleteActionShowForm() {
        $this->useEnglish();

        $this->dispatch('/admin/licence/delete/id/4');

        $this->assertQueryContentContains('legend', 'Delete Licence');
        $this->assertQueryContentContains('span.displayname', 'Creative Commons - CC BY-ND - Namensnennung');
        $this->assertQuery('input#ConfirmYes');
        $this->assertQuery('input#ConfirmNo');
    }

    public function createNewModel() {
        $licence = new Opus_Licence();

        $licence->setNameLong('Test Licence (LicenceControllerTest::testDeleteAction)');
        $licence->setLinkLicence('testlink');
        $licence->setLanguage('rus');

        return $licence->store();
    }

    public function getModel($identifier) {
        return new Opus_Licence($identifier);
    }

}

