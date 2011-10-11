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
 * @category    Application Unit Tests
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class Admin_FilemanagerControllerTest extends ControllerTestCase {

    /**
     * Basic unit test checks that error controller is not called.
     */
    public function testIndexAction() {
        $this->dispatch('/admin/filemanager/index/docId/91');
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('filemanager');
        $this->assertAction('index');
    }

    /**
     * Verifies that the MD5 hash value is displayed twice (ist, soll).
     */
    public function testMd5HashValuesPresent() {
        $hash = '1ba50dc8abc619cea3ba39f77c75c0fe';
        $this->dispatch('/admin/filemanager/index/docId/91');
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('filemanager');
        $this->assertAction('index');
        $response = $this->getResponse()->getBody();
        $this->assertTrue(substr_count($response, $hash) == 2);
    }

    /**
     * Verifies that the SHA512 hash value is displayed twice (ist, soll).
     */
    public function testSha512HashValuesPresent() {
        $hash = '24bb2209810bacb3f9c05e08a08aec9ead4ac606fdc7c9d6c5fadffcf66f1e56396fdf46424cf52ef916f9e51f8178fb618c787f952d35aaf6d9079bbc9a50ad';
        $this->dispatch('/admin/filemanager/index/docId/91');
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('filemanager');
        $this->assertAction('index');
        $response = $this->getResponse()->getBody();
        $this->assertTrue(substr_count($response, $hash) == 2);
    }

}

?>
