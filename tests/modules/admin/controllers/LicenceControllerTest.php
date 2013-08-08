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
 * @category    Application Unit Test
 * @package     Admin
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
class Admin_LicenceControllerTest extends ControllerTestCase {

    private $licences;

    private $createsLicences = false;

    public function setUp() {
        parent::setUp();

        $this->licences = array();

        foreach (Opus_Licence::getAll() as $licence) {
            $this->licences[] = $licence->getId();
        }
    }

    public function tearDown() {
        if ($this->createsLicences) {
            $this->deleteNewLicences();
        }
        parent::tearDown();
    }

    private function deleteNewLicences() {
        foreach (Opus_Licence::getAll() as $licence) {
            if (!in_array($licence->getId(), $this->licences)) {
                $licence->delete();
            }
        }
    }

    /**
     * Tests routing to and successfull execution of 'index' action.
     */
    public function testIndexAction() {
        $this->dispatch('/admin/licence');
        $this->assertResponseCode(200);
        $this->assertController('licence');
        $this->assertAction('index');

        $licences = Opus_Licence::getAll();

        $this->assertQuery('a.add', 'Kein Add Button gefunden.');
        $this->assertQueryCount('td.edit', count($licences)); // Edit-Zellen für Lizenzen (erste Spalte hat kein class)

        foreach ($licences as $licence) {
            $this->assertQuery('th', $licence->getDisplayName());
        }
    }

    public function testBreadcrumbsDefined() {
        $this->verifyBreadcrumbDefined('/admin/licence/index');
        $this->verifyBreadcrumbDefined('/admin/licence/show');
        $this->verifyBreadcrumbDefined('/admin/licence/new');
        $this->verifyBreadcrumbDefined('/admin/licence/edit');
        $this->verifyBreadcrumbDefined('/admin/licence/delete');
    }

    /**
     * Tests 'show' action.
     */
    public function testShowAction() {
        $this->createsLicences = true;

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
    }

    public function testShowActionBadId() {
        $this->dispatch('/admin/licence/show/id/bla');
        $this->assertRedirectTo('/admin/licence');
        $this->verifyFlashMessage('controller_crud_invalid_id');
    }

    public function testShowActionBadUnknownId() {
        $this->dispatch('/admin/licence/show/id/1000');
        $this->assertRedirectTo('/admin/licence');
        $this->verifyFlashMessage('controller_crud_invalid_id');
    }

    public function testShowActionNoId() {
        $this->dispatch('/admin/licence/show');
        $this->assertRedirectTo('/admin/licence');
        $this->verifyFlashMessage('controller_crud_invalid_id');
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

    /**
     * Tests 'new' action.
     */
    public function testNewActionShowForm() {
        $this->dispatch('/admin/licence/new');
        $this->assertResponseCode(200);
        $this->assertController('licence');
        $this->assertAction('new');

        $this->assertQuery('li.save-element');
        $this->assertQuery('li.cancel-element');
        $this->assertQueryCount(1, 'input#Id');
    }

    public function testNewActionSave() {
        $this->createsLicences = true;

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
        $this->createsLicences = true;

        $post = array(
            'NameLong' => 'TestNameLong',
            'Language' => 'eng',
            'LinkLicence' => 'www.example.org/licence',
            'Cancel' => 'Abbrechen'
        );

        $this->getRequest()->setPost($post)->setMethod('POST');

        $this->dispatch('/admin/licence/new');

        $this->assertRedirectTo('/admin/licence', 'Should be a redirect to index action.');

        $this->assertEquals(count($this->licences), count(Opus_Licence::getAll()),
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

        $this->assertQueryContentContains('div#NameLong-element', 'Creative Commons - Namensnennung');
        $this->assertQuery('li.save-element');
        $this->assertQuery('li.cancel-element');
        $this->assertQueryCount(1, 'input#Id');
    }

    public function testEditActionSave() {
        $this->createsLicences = true;

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
        $this->createsLicences = true;

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
        $this->assertNotEquals('NameLongModified', $licence->getNameLong());
    }

    public function testEditActionBadId() {
        $this->dispatch('/admin/licence/edit/id/notanid');
        $this->assertRedirectTo('/admin/licence');
        $this->verifyFlashMessage('controller_crud_invalid_id');
    }

    public function testEditActionUnknownId() {
        $this->dispatch('/admin/licence/edit/id/1000');
        $this->assertRedirectTo('/admin/licence');
        $this->verifyFlashMessage('controller_crud_invalid_id');
    }

    public function testEditActionNoId() {
        $this->dispatch('/admin/licence/edit');
        $this->assertRedirectTo('/admin/licence');
        $this->verifyFlashMessage('controller_crud_invalid_id');
    }

    public function testDeleteActionShowForm() {
        $this->useEnglish();

        $this->dispatch('/admin/licence/delete/id/4');

        $this->assertQueryContentContains('legend', 'Delete Licence');
        $this->assertQueryContentContains('span.displayname', 'Creative Commons - Namensnennung');
        $this->assertQuery('input#ConfirmYes');
        $this->assertQuery('input#ConfirmNo');
    }

    public function testDeleteActionYes() {
        $this->createsLicences = true;

        $licence = new Opus_Licence();

        $licence->setNameLong('Test Licence (LicenceControllerTest::testDeleteAction)');
        $licence->setLinkLicence('testlink');
        $licence->setLanguage('rus');

        $licenceId = $licence->store();

        $this->getRequest()->setMethod('POST')->setPost(array(
            'Id' => $licenceId,
            'ConfirmYes' => 'Ja'
        ));

        $this->dispatch('/admin/licence/delete');

        try {
            new Opus_Licence($licenceId);
        }
        catch (Opus_Model_NotFoundException $omnfe) {
            // alles gut, Lizenz wurde geloescht
        }

        $this->assertRedirectTo('/admin/licence');
        $this->verifyFlashMessage('controller_crud_delete_success', self::MESSAGE_LEVEL_NOTICE);
    }

    public function testDeleteActionNo() {
        $this->createsLicences = true;
        $this->useEnglish();

        $licence = new Opus_Licence();

        $licence->setNameLong('Test Licence (LicenceControllerTest::testDeleteAction)');
        $licence->setLinkLicence('testlink');
        $licence->setLanguage('rus');

        $licenceId = $licence->store();

        $this->getRequest()->setMethod('POST')->setPost(array(
            'Id' => $licenceId,
            'ConfirmNo' => 'Nein'
        ));

        $this->dispatch('/admin/licence/delete/id/' . $licenceId);

        $this->assertNotNull(new Opus_Licence($licenceId)); // Lizenz nicht geloescht, alles gut

        $this->assertRedirectTo('/admin/licence');
    }

    public function testDeleteActionBadId() {
        $this->dispatch('/admin/licence/delete/id/notanid');
        $this->assertRedirectTo('/admin/licence');
        $this->verifyFlashMessage('controller_crud_invalid_id');
    }

    public function testDeleteActionUnknownId() {
        $this->dispatch('/admin/licence/delete/id/1000');
        $this->assertRedirectTo('/admin/licence');
        $this->verifyFlashMessage('controller_crud_invalid_id');
    }

    public function testDeleteActionNoId() {
        $this->dispatch('/admin/licence/delete');
        $this->assertRedirectTo('/admin/licence');
        $this->verifyFlashMessage('controller_crud_invalid_id');
    }

}

