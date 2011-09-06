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
 * @copyright   Copyright (c) 2008-2011, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
class Publish_Model_ExtendedValidationTest extends ControllerTestCase {

    public function testPersonsFirstNamesWithInvalidData() {
        $session = new Zend_Session_Namespace('Publish');
        $session->documentType = 'workingpaper';
        $form = new Publish_Form_PublishingSecond(new Zend_View());
        $data = array(
            'PersonSubmitterFirstName1' => 'John',
            'PersonSubmitterLastName1' => 'Doe',
            'TitleMain1' => 'Entenhausen',
            'TitleMainLanguage1' => 'deu',
            'PersonAuthorFirstName1' => 'Icke',
            'PersonAuthorLastName1' => '',
            'CompletedDate' => '06.09.2011',
            'Language' => 'deu',
            'Licence' => 'ID:4'
        );

        $val = new Publish_Model_ExtendedValidation($form, $data);
        $result = $val->validate();
        $this->assertFalse($result);
    }

    public function testPersonsEmailWithInvalidData() {
        $session = new Zend_Session_Namespace('Publish');
        $session->documentType = 'workingpaper';
        $form = new Publish_Form_PublishingSecond(new Zend_View());
        $data = array(
            'PersonSubmitterFirstName1' => 'John',
            'PersonSubmitterLastName1' => 'Doe',
            'TitleMain1' => 'Entenhausen',
            'TitleMainLanguage1' => 'deu',
            'PersonAuthorFirstName1' => '',
            'PersonAuthorLastName1' => '',
            'PersonAuthorEmail1' => 'egal@wurscht.de',
            'PersonAuthorAllowEmailContact1' => '',
            'CompletedDate' => '06.09.2011',
            'Language' => 'deu',
            'Licence' => 'ID:4'
        );

        $val = new Publish_Model_ExtendedValidation($form, $data);
        $result = $val->validate();
        $this->assertFalse($result);
    }

    public function testPersonsEmailNotificationWithValidData() {
        $session = new Zend_Session_Namespace('Publish');
        $session->documentType = 'workingpaper';
        $form = new Publish_Form_PublishingSecond(new Zend_View());
        $data = array(
            'PersonSubmitterFirstName1' => 'John',
            'PersonSubmitterLastName1' => 'Doe',
            'TitleMain1' => 'Entenhausen',
            'TitleMainLanguage1' => 'deu',
            'PersonAuthorFirstName1' => '',
            'PersonAuthorLastName1' => '',
            'PersonAuthorEmail1' => '',
            'PersonAuthorAllowEmailContact1' => '1',
            'CompletedDate' => '06.09.2011',
            'Language' => 'deu',
            'Licence' => 'ID:4'
        );

        $val = new Publish_Model_ExtendedValidation($form, $data);
        $result = $val->validate();
        //Checkbox is deleted because no other person field is filled
        $this->assertTrue($result);
    }

    public function testPersonsEmailNotificationWithInvalidData() {
        $session = new Zend_Session_Namespace('Publish');
        $session->documentType = 'workingpaper';
        $form = new Publish_Form_PublishingSecond(new Zend_View());
        $data = array(
            'PersonSubmitterFirstName1' => 'John',
            'PersonSubmitterLastName1' => 'Doe',
            'TitleMain1' => 'Entenhausen',
            'TitleMainLanguage1' => 'deu',
            'PersonAuthorFirstName1' => 'Icke',
            'PersonAuthorLastName1' => '',
            'PersonAuthorEmail1' => '',
            'PersonAuthorAllowEmailContact1' => '1',
            'CompletedDate' => '06.09.2011',
            'Language' => 'deu',
            'Licence' => 'ID:4'
        );

        $val = new Publish_Model_ExtendedValidation($form, $data);
        $result = $val->validate();
        $this->assertFalse($result);
    }    
    
//    public function testMainTitlesWithSameLanguage() {
//        $session = new Zend_Session_Namespace('Publish');
//        $session->documentType = 'all';
//        //$session->additionalFields = array();
//        $session->additionalFields['TitleMain'] = '2';
//        $form = new Publish_Form_PublishingSecond(new Zend_View());
//        
//        $data = array(
//            'PersonSubmitterFirstName1' => 'John',
//            'PersonSubmitterLastName1' => 'Doe',
//            'PersonSubmitterEmail1' => 'egal@wurscht.de',
//            'TitleMain1' => 'Entenhausen',
//            'TitleMainLanguage1' => 'deu',
//            'TitleMain2' => 'Entenhausen Extended',
//            'TitleMainLanguage2' => 'deu',
//            'EnrichmentLegalNotices' => '1',
//            'Language' => 'deu',
//            'addMoreTitleMain' => "Einen weiteren Titel hinzufügen"
//        );
//        $form->getExtendedForm($data, true);
//
//        $val = new Publish_Model_ExtendedValidation($form, $data);
//        $result = $val->validate();
//        $this->assertFalse($result);
//    }
    
//        public function testOneMainTitleWithDifferentDocLanguage() {
//        $session = new Zend_Session_Namespace('Publish');
//        $session->documentType = 'all';
//        $form = new Publish_Form_PublishingSecond(new Zend_View());
//        $data = array(
//            'PersonSubmitterFirstName1' => 'John',
//            'PersonSubmitterLastName1' => 'Doe',
//            'PersonSubmitterMail1' => 'Doe@Doe.com',            
//            'TitleMain1' => 'Entenhausen',
//            'TitleMainLanguage1' => 'eng',                        
//            'CompletedDate' => '06.09.2011',
//            'Language' => 'deu',
//            'EnrichmentLegalNotices' => '1'
//        );
//
//        $val = new Publish_Model_ExtendedValidation($form, $data);
//        $result = $val->validate();
//        $this->assertFalse($result);
//    }

}

?>
