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
 * @category    Application
 * @package     Tests
 * @author      Sascha Szott <szott@zib.de>
 * @copyright   Copyright (c) 2008-2011, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class Export_IndexControllerTest extends ControllerTestCase {

    public function testIndexActionWithoutFormat() {
        $this->dispatch('/export');
        $this->assertResponseCode(500);
        $response = $this->getResponse();
        $this->assertContains('export format is not specified', $response->getBody());
    }

    public function testIndexActionWithUnsupportedFormat() {
        $this->dispatch('/export/index/index/export/unsupporedformat');
        $this->assertResponseCode(500);
        $response = $this->getResponse();
        $this->assertContains('export format is not supported', $response->getBody());
    }

    public function testIndexActionWithoutQuery() {
        $this->dispatch('/export/index/index/export/xml');
        $this->assertResponseCode(500);
        $response = $this->getResponse();
        $this->assertContains('Unspecified search type', $response->getBody());
    }


    public function testIndexActionWithoutStylesheetParam() {
        $this->dispatch('/export/index/index/export/xml/query/foo/searchtype/latest');
        $this->assertResponseCode(200);
        $response = $this->getResponse();
        $this->assertContains('<?xml version="1.0" encoding="utf-8"?>', $response->getBody());
        $this->assertContains('<export timestamp=', $response->getBody());
    }

    public function testIndexActionWithStylesheetParam() {
        $this->dispatch('/export/index/index/export/xml/query/foo/searchtype/latest/stylesheet/example');
        $this->assertResponseCode(200);
        $response = $this->getResponse();
        $this->assertContains('<?xml version="1.0" encoding="utf-8"?>', $response->getBody());
        $this->assertContains('<export-example>', $response->getBody());
    }
}
?>