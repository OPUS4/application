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
 * @package     Admin
 * @author      Jens Schwidder <schwidder@zib.de>
 * @author      Michael Lang <lang@zib.de>
 * @author      Maximilian Salomon <salomon@zib.de>
 * @copyright   Copyright (c) 2008-2018, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Class Admin_SeriesControllerTest.
 *
 * @covers Admin_SeriesController
 */
class Admin_SeriesControllerTest extends CrudControllerTestCase {

    public function setUp() {
        $this->setController('series');
        parent::setUp();
    }

    function getModels() {
        return Opus_Series::getAllSortedBySortKey();
    }

    function createNewModel() {
        $series = new Opus_Series();

        $series->setTitle('Testseries');
        $series->setInfobox('Infotext');
        $series->setVisible(1);
        $series->setSortOrder(10);

        return $series->store();
    }

    function getModel($identifier) {
        return new Opus_Series($identifier);
    }

    public function testShowAction() {
        $this->createsModels = true;

        $seriesId = $this->createNewModel();

        $this->dispatch('/admin/series/show/id/' . $seriesId);

        $this->assertResponseCode(200);
        $this->assertController('series');
        $this->assertAction('show');

        $this->assertQueryContentContains('div#Title', 'Testseries');
        $this->assertQueryContentContains('div#Infobox', 'Infotext');
        $this->assertQueryContentRegex('div#Visible', '/Yes|Ja/');
        $this->assertQueryContentContains('div#SortOrder', '10');
    }

    public function testShowNewAction() {
        $this->dispatch('/admin/series/new');

        $sortOrder = Opus_Series::getMaxSortKey() + 1;

        $this->assertXPath('//input[@type = "checkbox" and @checked = "checked"]');
        $this->assertXPath('//input[@name = "SortOrder" and @value = "' . $sortOrder .  '"]');
    }

    public function testNewActionSave() {
        $this->createsModels = true;

        $post = array(
            'Title' => 'NewSeriesTitle',
            'Infobox' => 'NewSeriesInfobox',
            'Visible' => '0',
            'SortOrder' => '33',
            'Save' => 'Speichern'
        );

        $this->getRequest()->setMethod('POST')->setPost($post);

        $this->dispatch('/admin/series/new');

        $this->assertRedirect('Should be a redirect to show action.');
        $this->assertRedirectRegex('/^\/admin\/series\/show/'); // Regex weil danach noch '/id/xxx' kommt
        $this->verifyFlashMessage('controller_crud_save_success', self::MESSAGE_LEVEL_NOTICE);

        $location = $this->getLocation();

        $this->resetRequest();
        $this->resetResponse();

        $this->dispatch($location);
        $this->assertResponseCode(200);

        $this->assertQueryContentContains('div#Title', 'NewSeriesTitle');
        $this->assertQueryContentContains('div#Infobox', 'NewSeriesInfobox');
        $this->assertQueryContentRegex('div#Visible', '/No|Nein/');
        $this->assertQueryContentContains('div#SortOrder', '33');
    }

    public function testNewActionCancel() {
        $this->createsModels = true;

        $modelCount = count($this->getModels());

        $post = array(
            'Title' => 'NewSeries',
            'Infobox' => 'NewSeriesInfobox',
            'Visible' => '1',
            'SortOrder' => '20',
            'Cancel' => 'Abbrechen'
        );

        $this->getRequest()->setMethod('POST')->setPost($post);

        $this->dispatch('/admin/series/new');

        $this->assertRedirectTo('/admin/series', 'Should redirect to index action.');

        $this->assertEquals($modelCount, count(Opus_Series::getAllSortedBySortKey()),
            'Es sollte keine neue Series geben.');
    }

    public function testEditActionShowForm() {
        $this->dispatch('/admin/series/edit/id/4');
        $this->assertResponseCode(200);
        $this->assertController('series');
        $this->assertAction('edit');

        $this->assertQueryContentContains('div#Title-element', 'Visible Series');
        $this->assertQuery('li.save-element');
        $this->assertQuery('li.cancel-element');
        $this->assertQueryCount('input#Id', 1);
    }

    public function testEditActionSave() {
        $this->createsModels = true;

        $seriesId = $this->createNewModel();

        $this->getRequest()->setMethod('POST')->setPost(array(
            'Id' => $seriesId,
            'Title' => 'ModifiedTitle',
            'Infobox' => 'ModifiedInfo',
            'Visible' => '0',
            'SortOrder' => '12',
            'Save' => 'Abspeichern'
        ));

        $this->dispatch('/admin/series/edit');
        $this->assertRedirectTo('/admin/series/show/id/' . $seriesId);
        $this->verifyFlashMessage('controller_crud_save_success', self::MESSAGE_LEVEL_NOTICE);

        $series = new Opus_Series($seriesId);

        $this->assertEquals('ModifiedTitle', $series->getTitle());
        $this->assertEquals('ModifiedInfo', $series->getInfobox());
        $this->assertEquals(0, $series->getVisible());
        $this->assertEquals(12, $series->getSortOrder());
    }

    public function testEditActionCancel() {
        $this->createsModels = true;

        $seriesId = $this->createNewModel();

        $this->getRequest()->setMethod('POST')->setPost(array(
            'Id' => $seriesId,
            'Title' => 'ModifiedTitle',
            'Infobox' => 'ModifiedInfo',
            'Visible' => '0',
            'SortOrder' => '12',
            'Cancel' => 'Cancel'
        ));

        $this->dispatch('/admin/series/edit');
        $this->assertRedirectTo('/admin/series');

        $series = new Opus_Series($seriesId);

        $this->assertEquals('Testseries', $series->getTitle());
    }

    public function testDeleteActionShowForm() {
        $this->useEnglish();

        $this->dispatch('/admin/series/delete/id/4');

        $this->assertQueryContentContains('legend', 'Delete Series');
        $this->assertQueryContentContains('span.displayname', 'Visible Series');
        $this->assertQuery('input#ConfirmYes');
        $this->assertQuery('input#ConfirmNo');
    }

    public function testHideDocumentsLinkForSeriesWithoutDocuments() {
        $this->dispatch('/admin/series');

        $allSeries = Opus_Series::getAll();

        foreach ($allSeries as $series) {
            $seriesId = $series->getId();
            if ($series->getNumOfAssociatedDocuments() > 0) {
                $this->assertQuery("//a[@href='/admin/documents/index/seriesid/$seriesId']");
            }
            else {
                $this->assertNotQuery("//a[@href='/admin/documents/index/seriesid/$seriesId']");
            }
        }
    }

    public function testSeriesVisibilityIsDisplayedCorrectly() {
        $this->dispatch('/admin/series');

        $allSeries = Opus_Series::getAll();

        foreach ($allSeries as $series) {
            $seriesId = $series->getId();
            if ($series->getVisible()) {
                $this->assertXPath('//a[@href="/admin/series/show/id/' . $seriesId . '" and @class="displayname"]');
            }
            else {
                $this->assertXPath('//a[@href="/admin/series/show/id/' . $seriesId . '" and @class="displayname invisible"]');
            }
        }
    }

    public function testSeriesIdIsShownInTable() {
        $this->dispatch('/admin/series');

        $allSeries = Opus_Series::getAll();

        foreach ($allSeries as $series) {
            $seriesId = $series->getId();
            $this->assertXPathContentContains('//td', "(ID = $seriesId)");
        }
    }

    public function testHiddenIdElementNotWrappedInLiTag() {
        $this->dispatch('/admin/series/show/id/1');

        $this->assertNotQuery('//li/input[@name="Id"]');
        $this->assertQuery('//div[@class="wrapper"]/input[@name="Id"]');
    }

}
