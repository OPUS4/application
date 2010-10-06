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
 * along with OPUS; >if not, write to the Free Software Foundation, Inc., 51
 * Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * @category    TODO
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class Admin_Model_FileHelperTest extends ControllerTestCase {

    protected function _getFileHelper() {
        $document = new Opus_Document(91);

        $files = $document->getFile();

        $bootstrap = $this->application->getBootstrap();

        $view = $bootstrap->getResource('view');

        if(count($files) === 0) {
            $this->markTestSkipped('Test document (docId = 91) does not have file.');
        }

        return new Admin_Model_FileHelper($view, $document, $files[0]);
    }

    public function testCreateFileHelper() {
        $fileHelper = $this->_getFileHelper();

        $this->assertNotNull($fileHelper);
    }

    /**
     * @depends testCreateFileHelper
     */
    public function testGetDeleteForm() {
        $this->markTestSkipped('Have not figured out yet how to get view for unit tests.');
        $fileHelper = $this->_getFileHelper();
        $form = $fileHelper->getDeleteForm();
    }

    public function testGetFileName() {
        $fileHelper = $this->_getFileHelper();

        $this->assertNotNull($fileHelper->getFileName());
    }

    public function testGetHashes() {
        $fileHelper = $this->_getFileHelper();

        $this->assertNotNull($fileHelper->getHashes());
    }

}

?>
