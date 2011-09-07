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
class Publish_Form_PublishingSecondTest extends ControllerTestCase {

    /**
     * @expectedException Publish_Model_FormSessionTimeoutException
     * exception because of missing session documentType 
     */
    public function testConstructorWithoutDocTypeInSession() {
        $form = new Publish_Form_PublishingSecond();
    }

    /**
     * A sucessful creation of PublishingSecond should result in having at least two buttons send and back
     */
    public function testConstructorWithDocTypeInSession() {
        $session = new Zend_Session_Namespace('Publish');
        $session->documentType = 'preprint';
        $form = new Publish_Form_PublishingSecond();
        $this->assertNotNull($form->getElement('back'));
        $this->assertNotNull($form->getElement('send'));
    }

    /**
     * Data is invalid because doc type workingpaper need more field entries.
     */
    public function testIsValidWithInvalidData() {
        $config = Zend_Registry::get('Zend_Config');
        $config->documentTypes->include = 'all,preprint,article,demo,workingpaper';
        $session = new Zend_Session_Namespace('Publish');
        $session->documentType = 'workingpaper';
        $form = new Publish_Form_PublishingSecond();
        $data = array(
            'PersonSubmitterFirstName1' => 'John',
            'PersonSubmitterLastName1' => 'Doe'
        );

        $valid = $form->isValid($data);
        $this->assertFalse($valid);
    }

    /**
     * Doc Type has only two fields which are already filled.
     */
    public function testIsValidWithValidData() {
        $config = Zend_Registry::get('Zend_Config');
        $config->documentTypes->include = 'all,preprint,article,demo,workingpaper';
        $session = new Zend_Session_Namespace('Publish');
        $session->documentType = 'demo';
        $form = new Publish_Form_PublishingSecond();
        $data = array(
            'PersonSubmitterFirstName1' => 'John',
            'PersonSubmitterLastName1' => 'Doe'
        );

        $valid = $form->isValid($data);
        $this->assertTrue($valid);
    }
    
    /**
     * Demo has 2 fields which are stored in elements and 2 new buttons are created.
     */
    public function testPrepareCheckMethodWithDemoType() {
        $config = Zend_Registry::get('Zend_Config');
        $config->documentTypes->include = 'all,preprint,article,demo,workingpaper';
        $session = new Zend_Session_Namespace('Publish');
        $session->documentType = 'demo';
        
        $form = new Publish_Form_PublishingSecond();
        $data = array(
            'PersonSubmitterFirstName1' => 'John',
            'PersonSubmitterLastName1' => 'Doe'
        );
        $form->prepareCheck();
        $this->assertNotNull($form->getElement('back'));
        $this->assertNotNull($form->getElement('send'));
        $this->assertTrue($session->elements['PersonSubmitterFirstName1']['value']=='John');
        $this->assertTrue($session->elements['PersonSubmitterLastName1']['value']=='Doe');
    }
    
    /**
     * Button pressed: Add one more Title Main
     */
    public function testGetExtendedFormMethodWithAddButton() {
        $config = Zend_Registry::get('Zend_Config');
        $config->documentTypes->include = 'all,preprint,article,demo,workingpaper';
        $session = new Zend_Session_Namespace('Publish');
        $session->documentType = 'preprint';
        $session->additionalFields = array();
                
        $data = array(
            'PersonSubmitterFirstName1' => '',
            'PersonSubmitterLastName1' => '', 
            'PersonSubmitterEmail1' => '',            
            'TitleMain1' => '',
            'TitleMainLanguage1' => '',
            'addMoreTitleMain' => 'Einen+weiteren+Titel+hinzufügen',
            'TitleAbstract1' => '',
            'TitleAbstractLanguage1' => '',
            'PersonAuthorFirstName1' => '',
            'PersonAuthorLastName1' => '',
            'PersonAuthorAcademicTitle1' => '',
            'PersonAuthorEmail1' => '', 
            'PersonAuthorAllowEmailContact1' => '0',
            'PersonAuthorDateOfBirth1' => '',
            'PersonAuthorPlaceOfBirth1' => '',
            'CompletedYear' => '',
            'CompletedDate' => '07.09.2011',
            'PageNumber' => '',
            'SubjectUncontrolled1' => '',
            'SubjectUncontrolledLanguage1' => '',
            'Institute1' => '',
            'IdentifierUrn' => '',
            'Note' => '',
            'Language' => 'deu',
            'Licence' => '',
            'SeriesNumber1' => '',
            'Series1' => ''
        );
        
        $form = new Publish_Form_PublishingSecond($data);
        $form->getExtendedForm($data, true);               
        $this->assertTrue($session->additionalFields['TitleMain']=='2');        
    }
    
    /**
     * Button pressed: Delete the last Title Main
     */
    public function testGetExtendedFormMethodWithDeleteButton() {
        $config = Zend_Registry::get('Zend_Config');
        $config->documentTypes->include = 'all,preprint,article,demo,workingpaper';
        $session = new Zend_Session_Namespace('Publish');
        $session->documentType = 'preprint';
        $session->additionalFields = array();
        $session->additionalFields['TitleMain'] = '2';                
                
        $data = array(
            'PersonSubmitterFirstName1' => '',
            'PersonSubmitterLastName1' => '', 
            'PersonSubmitterEmail1' => '',            
            'TitleMain1' => '',
            'TitleMainLanguage1' => '',
            'TitleMain2' => '',
            'TitleMainLanguage2' => '',
            'deleteMoreTitleMain' => 'Den+letzten+Titel+löschen',
            'TitleAbstract1' => '',
            'TitleAbstractLanguage1' => '',
            'PersonAuthorFirstName1' => '',
            'PersonAuthorLastName1' => '',
            'PersonAuthorAcademicTitle1' => '',
            'PersonAuthorEmail1' => '', 
            'PersonAuthorAllowEmailContact1' => '0',
            'PersonAuthorDateOfBirth1' => '',
            'PersonAuthorPlaceOfBirth1' => '',
            'CompletedYear' => '',
            'CompletedDate' => '07.09.2011',
            'PageNumber' => '',
            'SubjectUncontrolled1' => '',
            'SubjectUncontrolledLanguage1' => '',
            'Institute1' => '',
            'IdentifierUrn' => '',
            'Note' => '',
            'Language' => 'deu',
            'Licence' => '',
            'SeriesNumber1' => '',
            'Series1' => ''
        );
        
        $form = new Publish_Form_PublishingSecond($data);
        $form->getExtendedForm($data, true);               
        $this->assertTrue($session->additionalFields['TitleMain']=='1');                 
    }
    
    /**
     * Button pressed: Browse down Institute
     */
    public function testGetExtendedFormMethodWithBrowseDownButton() {
        $config = Zend_Registry::get('Zend_Config');
        $config->documentTypes->include = 'all,preprint,article,demo,workingpaper';
        $session = new Zend_Session_Namespace('Publish');
        $session->documentType = 'preprint';
        $session->additionalFields = array();
        $session->additionalFields['Institute'] = '1';                
        $session->additionalFields['collId0Institute1'] = '1'; 
        $session->additionalFields['stepInstitute1'] = '1';         
           
        $data = array(
            'PersonSubmitterFirstName1' => '',
            'PersonSubmitterLastName1' => '', 
            'PersonSubmitterEmail1' => '',            
            'TitleMain1' => '',
            'TitleMainLanguage1' => '',            
            'TitleAbstract1' => '',
            'TitleAbstractLanguage1' => '',
            'PersonAuthorFirstName1' => '',
            'PersonAuthorLastName1' => '',
            'PersonAuthorAcademicTitle1' => '',
            'PersonAuthorEmail1' => '', 
            'PersonAuthorAllowEmailContact1' => '0',
            'PersonAuthorDateOfBirth1' => '',
            'PersonAuthorPlaceOfBirth1' => '',
            'CompletedYear' => '',
            'CompletedDate' => '07.09.2011',
            'PageNumber' => '',
            'SubjectUncontrolled1' => '',
            'SubjectUncontrolledLanguage1' => '',
            'Institute1' => 'ID:15994',
            'browseDownInstitute' => 'runter',
            'IdentifierUrn' => '',
            'Note' => '',
            'Language' => 'deu',
            'Licence' => '',
            'SeriesNumber1' => '',
            'Series1' => ''
        );
        
        $form = new Publish_Form_PublishingSecond($data);
        $form->getExtendedForm($data, true);           
        $this->assertTrue($session->additionalFields['collId1Institute1']=='15994');       
        $this->assertTrue($session->additionalFields['stepInstitute1']=='2');       
    }

    /**
     * Button pressed: Browse up Institute
     */
    public function testGetExtendedFormMethodWithBrowseUpButton() {
        $config = Zend_Registry::get('Zend_Config');
        $config->documentTypes->include = 'all,preprint,article,demo,workingpaper';
        $session = new Zend_Session_Namespace('Publish');
        $session->documentType = 'preprint';
        $session->additionalFields = array();
        $session->additionalFields['Institute'] = '1';                
        $session->additionalFields['collId0Institute1'] = '1';
        $session->additionalFields['collId1Institute1'] = '15994';
        $session->additionalFields['stepInstitute1'] = '2';         
           
        $data = array(
            'PersonSubmitterFirstName1' => '',
            'PersonSubmitterLastName1' => '', 
            'PersonSubmitterEmail1' => '',            
            'TitleMain1' => '',
            'TitleMainLanguage1' => '',            
            'TitleAbstract1' => '',
            'TitleAbstractLanguage1' => '',
            'PersonAuthorFirstName1' => '',
            'PersonAuthorLastName1' => '',
            'PersonAuthorAcademicTitle1' => '',
            'PersonAuthorEmail1' => '', 
            'PersonAuthorAllowEmailContact1' => '0',
            'PersonAuthorDateOfBirth1' => '',
            'PersonAuthorPlaceOfBirth1' => '',
            'CompletedYear' => '',
            'CompletedDate' => '07.09.2011',
            'PageNumber' => '',
            'SubjectUncontrolled1' => '',
            'SubjectUncontrolledLanguage1' => '',            
            'collId2Institute1' => 'ID:15995',
            'browseUpInstitute' => 'hoch',
            'IdentifierUrn' => '',
            'Note' => '',
            'Language' => 'deu',
            'Licence' => '',
            'SeriesNumber1' => '',
            'Series1' => ''
        );
        
        $form = new Publish_Form_PublishingSecond($data);
        $form->getExtendedForm($data, true);                   
        $this->assertTrue($session->additionalFields['stepInstitute1']=='1');  
        $this->assertTrue($session->additionalFields['collId2Institute1']=='15995');  
    }    
}
