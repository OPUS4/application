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
 * @author      Thoralf Klein <thoralf.klein@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
class Publish_FormControllerTest extends ControllerTestCase {

    /**
     * Method to initialize Zend_Application for each test.
     */
    public function setUp() {

        $_FILES = array(
            'fileupload' => array(
                'name' => dirname(__FILE__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'test.pdf',
                'type' => 'application/pdf',
                'size' => 8,
                'tmp_name' => dirname(__FILE__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'test.pdf',
                'error' => 0));

        $this->adapter = new MockAdapter();

        parent::setUp();

    }

    /**
     * Simple test action to check form action in FormController
     */
    public function testUploadActionWithOutPost() {
        $this->dispatch('/publish/form/upload');
        $this->assertResponseCode(302);
        $this->assertController('form');
        $this->assertAction('upload');
    }

    /**
     * Simple test action to check form action with invalid POST
     */
    public function testUploadActionWithInvalidDummyPost() {
        $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'foo' => 'bar',
                ));

        $this->dispatch('/publish/form/upload');
        $this->assertResponseCode(200);
        $this->assertController('form');
        $this->assertAction('upload');
    }

    /**
     * Simple test action to check form action with valid POST
     */
    public function testUploadActionWithValidDummyPost() {
              $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'documentType' => 'preprint',
                    'type1' => 'preprint',
                    'MAX_FILE_SIZE' => '134217728',
                    'fileupload' => '',
                    'send' => 'Send'
                ));

        $this->dispatch('/publish/form/upload');
        $this->assertResponseCode(200);
        $this->assertController('form');
        $this->assertAction('upload');
    }
    
    /**
     * Simple test action to check check action in FormController
     */
    public function testCheckActionWithoutPost() {
        $this->dispatch('/publish/form/check');
        $this->assertResponseCode(302);
        $this->assertController('form');
        $this->assertAction('check');
    }

    public function testChechActionWithInvalidDummyPost() {
         $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'foo' => 'bar',
                ));

        $this->dispatch('/publish/form/check');
        $this->assertResponseCode(200);
        $this->assertController('form');
        $this->assertAction('check');

    }

    public function testChechActionWithValidDummyPost() {
         $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'PersonAuthor1FirstName' => 'Testi',
                    'PersonAuthor1LastName' => 'Tester',
                    'addMorePersonAuthor' => 'Einen weiteren Autoren hinzufügen',
                    'Institute1' => 'Zuse Institute Berlin (ZIB)',
                    'addMoreInstitute' => 'Ein weiteres Institut hinzufügen',
                    'countMoreInstitute' => '1',
                    'Language' => 'eng',
                    'TitleMain1' => 'Title',
                    'TitleMain1Language' => 'eng',
                    'addMoreTitleMain' => 'Einen weiteren Titel hinzufügen',
                    'countMoreTitleMain' => '1',

                ));

        $this->dispatch('/publish/form/check');
        $this->assertResponseCode(200);
        $this->assertController('form');
        $this->assertAction('check');

    }

}

class MockAdapter extends Zend_File_Transfer_Adapter_Http {

    public function isValid($files = null) {
        return true;
    }

}

?>
