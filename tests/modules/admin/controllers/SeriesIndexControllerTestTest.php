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
 * @category    Selenium Test
 * @package     Module_Admin
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2011, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */


class Admin_SeriesIndexControllerTest extends ControllerTestCase {

    public function testShowSeries() {
        $this->loginUser('admin', 'adminadmin');
        $this->dispatch('/admin/series/show/id/1');

        $this->assertQuery("//div[@class='Id']");
        $this->assertQuery("//div[@class='Title']");
        $this->assertQuery("//div[@class='Visible']");
        $this->assertQuery("//div[@class='Infobox']");
    }

    public function testHideDocumentsLinkForSeriesWithoutDocuments() {
        $this->loginUser('admin', 'adminadmin');
        $this->dispatch('/admin/series');

        $this->assertQuery("//a[@href='/admin/documents/index/seriesid/1']");
        $this->assertQuery("//a[@href='/admin/documents/index/seriesid/2']");
        $this->assertQuery("//a[@href='/admin/documents/index/seriesid/3']");
        $this->assertQuery("//a[@href='/admin/documents/index/seriesid/4']");
        $this->assertQuery("//a[@href='/admin/documents/index/seriesid/5']");
        $this->assertQuery("//a[@href='/admin/documents/index/seriesid/6']");
        $this->assertNotQuery("//a[@href='/admin/documents/index/seriesid/7']");
        $this->assertNotQuery("//a[@href='/admin/documents/index/seriesid/8']");
    }

    public function testSeriesVisibilityIsDisplayedCorrectly() {
        $this->loginUser('admin', 'adminadmin');
        $this->dispatch('/admin/series');

        foreach (array(1, 2, 4, 5, 6, 8) as $visibleId) {
            $this->assertQuery('//td[@class="visible"]/a[@href="/admin/series/show/id/' . $visibleId . '"]');
        }
        foreach (array(3, 7) as $unvisibleId) {
            $this->assertQuery('//td[@class="unvisible"]/a[@href="/admin/series/show/id/' . $unvisibleId . '"]');
        }
    }

}
