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
 * @package     Module_Publish Unit Test
 * @author      Susanne Gottwald <gottwald@zib.de>
 * @author      Sascha Szott <szott@zib.de>
 * @copyright   Copyright (c) 2008-2012, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class Publish_IndexControllerTest extends ControllerTestCase{

    
    public function testIndexAction() {
        $this->dispatch('/publish');  
        $this->assertResponseCode(200);
        $this->assertController('index');
        $this->assertAction('index');
    }

    public function testShowFileUpload() {
        $config = Zend_Registry::get('Zend_Config');
        
        // manipulate config
        $oldval = null;
        if (isset($config->form->first->enable_upload)) {
            $oldval = $config->form->first->enable_upload;
        }
        $config->form->first->enable_upload = 1;

        $this->dispatch('/publish');

        // undo config changes before asserting anything
        if (is_null($oldval)) {
            unset($config->form->first->enable_upload);
        }
        else {
            $config->form->first->enable_upload = $oldval;
        }

        $this->assertResponseCode(200);
        $this->assertController('index');
        $this->assertAction('index');

        $this->assertContains('<h3 class="document-type">Dokumenttyp und Datei wählen</h3>', $this->getResponse()->getBody());
        $this->assertContains('<legend>Dokument(e) hochladen</legend>', $this->getResponse()->getBody());
        $this->assertContains("<input type='hidden' name='MAX_FILE_SIZE' id='MAX_FILE_SIZE' value='10240000' />", $this->getResponse()->getBody());
        $this->assertContains("<label for='fileupload'>Datei wählen</label>", $this->getResponse()->getBody());
        $this->assertContains("<input type='file' name='fileupload' id='fileupload' enctype='multipart/form-data' title='Bitte wählen Sie eine Datei, die Sie hochladen möchten ' size='30' />", $this->getResponse()->getBody());
        $this->assertContains("<label for='uploadComment'>Kommentar</label>", $this->getResponse()->getBody());
        $this->assertContains("<textarea name='uploadComment' class='form-textarea' cols='30' rows='5'  title='hint_uploadComment'  id='uploadComment'></textarea>", $this->getResponse()->getBody());                                
    }

    public function testDoNotShowFileUpload() {
        $config = Zend_Registry::get('Zend_Config');
        
        // manipulate config
        $oldval = null;
        if (isset($config->form->first->enable_upload)) {
            $oldval = $config->form->first->enable_upload;
        }
        $config->form->first->enable_upload = 0;

        $this->dispatch('/publish');

        // undo config changes
        if (is_null($oldval)) {
            unset($config->form->first->enable_upload);
        }
        else {
            $config->form->first->enable_upload = $oldval;
        }        

        $this->assertResponseCode(200);
        $this->assertController('index');
        $this->assertAction('index');

        $this->assertContains('<h3 class="document-type">Dokumenttyp wählen</h3>', $this->getResponse()->getBody());
        $this->assertNotContains('<legend>Dokument(e) hochladen</legend>', $this->getResponse()->getBody());
        $this->assertNotContains("<input type='hidden' name='MAX_FILE_SIZE' id='MAX_FILE_SIZE' value='10240000' />", $this->getResponse()->getBody());
        $this->assertNotContains("<label for='fileupload'>Datei wählen</label>", $this->getResponse()->getBody());
        $this->assertNotContains("<input type='file' name='fileupload' id='fileupload' enctype='multipart/form-data' title='Bitte wählen Sie eine Datei, die Sie hochladen möchten ' size='30' />", $this->getResponse()->getBody());
        $this->assertNotContains("<label for='uploadComment'>Kommentar</label>", $this->getResponse()->getBody());
        $this->assertNotContains("<textarea name='uploadComment' class='form-textarea' cols='30' rows='5'  title='hint_uploadComment'  id='uploadComment'></textarea>", $this->getResponse()->getBody());
    }


    public function testShowBibliographyCheckbox() {
        $config = Zend_Registry::get('Zend_Config');

        // manipulate config
        $oldval = null;
        if (isset($config->form->first->bibliographie)) {
            $oldval = $config->form->first->bibliographie;
        }
        $config->form->first->bibliographie = 1;

        $this->dispatch('/publish');

        // undo config changes before asserting anything
        if (is_null($oldval)) {
            unset($config->form->first->bibliographie);
        }
        else {
            $config->form->first->bibliographie = $oldval;
        }

        $this->assertResponseCode(200);
        $this->assertController('index');
        $this->assertAction('index');

        $this->assertContains('<h3 class="document-type">Dokumenttyp und Datei wählen</h3>', $this->getResponse()->getBody());
        $this->assertContains('<legend>Bibliographie</legend>', $this->getResponse()->getBody());
        $this->assertContains("<input type='checkbox' class='form-checkbox' name='bibliographie' id='bibliographie' value='1'  />", $this->getResponse()->getBody());        
        $this->assertContains("<label for='bibliographie'>Zur Bibliographie hinzufügen?</label>", $this->getResponse()->getBody());
        $this->assertContains("<input type='hidden' name='bibliographie' value='0' />", $this->getResponse()->getBody());
    }

    public function testDoNotShowBibliographyCheckbox() {
        $config = Zend_Registry::get('Zend_Config');

        // manipulate config
        $oldval = null;
        if (isset($config->form->first->bibliographie)) {
            $oldval = $config->form->first->bibliographie;
        }
        $config->form->first->bibliographie = 0;

        $this->dispatch('/publish');

        // undo config changes before asserting anything
        if (is_null($oldval)) {
            unset($config->form->first->bibliographie);
        }
        else {
            $config->form->first->bibliographie = $oldval;
        }

        $this->assertResponseCode(200);
        $this->assertController('index');
        $this->assertAction('index');

        $this->assertContains('<h3 class="document-type">Dokumenttyp und Datei wählen</h3>', $this->getResponse()->getBody());
        $this->assertNotContains('<legend>Bibliographie</legend>', $this->getResponse()->getBody());
        $this->assertNotContains("<input type='checkbox' class='form-checkbox' name='bibliographie' id='bibliographie' value='1'  />", $this->getResponse()->getBody());
        $this->assertNotContains("<label for='bibliographie'>Zur Bibliographie hinzufügen?</label>", $this->getResponse()->getBody());
        $this->assertNotContains("<input type='hidden' name='bibliographie' value='0' />", $this->getResponse()->getBody());
    }

}
