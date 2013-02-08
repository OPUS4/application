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

    protected $_logger;

    public function setUp() {
	$writer = new Zend_Log_Writer_Null;
	$this->_logger = new Zend_Log($writer);
	parent::setUp();
    }

    /**
     * @expectedException Publish_Model_FormSessionTimeoutException
     * exception because of missing session documentType 
     */
    public function testConstructorWithoutDocTypeInSession() {
        $form = new Publish_Form_PublishingSecond($this->_logger);
    }

    /**
     * A sucessful creation of PublishingSecond should result in having at least two buttons send and back
     */
    public function testConstructorWithDocTypeInSession() {
        $session = new Zend_Session_Namespace('Publish');
        $session->documentType = 'preprint';
        $form = new Publish_Form_PublishingSecond($this->_logger);
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
        $form = new Publish_Form_PublishingSecond($this->_logger);
        $data = array(
            'PersonSubmitterFirstName_1' => 'John',
            'PersonSubmitterLastName_1' => 'Doe'
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
        $form = new Publish_Form_PublishingSecond($this->_logger);
        $data = array(
            'PersonSubmitterFirstName_1' => 'John',
            'PersonSubmitterLastName_1' => 'Doe'
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
        
        $form = new Publish_Form_PublishingSecond($this->_logger);
        $data = array(
            'PersonSubmitterFirstName_1' => 'John',
            'PersonSubmitterLastName_1' => 'Doe'
        );
        $form->prepareCheck();
        $this->assertNotNull($form->getElement('back'));
        $this->assertNotNull($form->getElement('send'));
        $this->assertTrue($session->elements['PersonSubmitterFirstName_1']['value']=='John');
        $this->assertTrue($session->elements['PersonSubmitterLastName_1']['value']=='Doe');
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
            'PersonSubmitterFirstName_1' => '',
            'PersonSubmitterLastName_1' => '', 
            'PersonSubmitterEmail_1' => '',            
            'TitleMain_1' => '',
            'TitleMainLanguage_1' => '',
            'addMoreTitleMain' => 'Einen+weiteren+Titel+hinzufügen',
            'TitleAbstract_1' => '',
            'TitleAbstractLanguage_1' => '',
            'PersonAuthorFirstName_1' => '',
            'PersonAuthorLastName_1' => '',
            'PersonAuthorAcademicTitle_1' => '',
            'PersonAuthorEmail_1' => '', 
            'PersonAuthorAllowEmailContact_1' => '0',
            'PersonAuthorDateOfBirth_1' => '',
            'PersonAuthorPlaceOfBirth_1' => '',
            'CompletedYear' => '',
            'CompletedDate' => '07.09.2011',
            'PageNumber' => '',
            'SubjectUncontrolled_1' => '',
            'SubjectUncontrolledLanguage_1' => '',
            'Institute_1' => '',
            'IdentifierUrn' => '',
            'Note' => '',
            'Language' => 'deu',
            'Licence' => '',
            'SeriesNumber_1' => '',
            'Series_1' => ''
        );
        
        $form = new Publish_Form_PublishingSecond($this->_logger, $data);
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
            'PersonSubmitterFirstName_1' => '',
            'PersonSubmitterLastName_1' => '', 
            'PersonSubmitterEmail_1' => '',            
            'TitleMain_1' => '',
            'TitleMainLanguage_1' => '',
            'TitleMain_2' => '',
            'TitleMainLanguage_2' => '',
            'deleteMoreTitleMain' => 'Den+letzten+Titel+löschen',
            'TitleAbstract_1' => '',
            'TitleAbstractLanguage_1' => '',
            'PersonAuthorFirstName_1' => '',
            'PersonAuthorLastName_1' => '',
            'PersonAuthorAcademicTitle_1' => '',
            'PersonAuthorEmail_1' => '', 
            'PersonAuthorAllowEmailContact_1' => '0',
            'PersonAuthorDateOfBirth_1' => '',
            'PersonAuthorPlaceOfBirth_1' => '',
            'CompletedYear' => '',
            'CompletedDate' => '07.09.2011',
            'PageNumber' => '',
            'SubjectUncontrolled_1' => '',
            'SubjectUncontrolledLanguage_1' => '',
            'Institute_1' => '',
            'IdentifierUrn' => '',
            'Note' => '',
            'Language' => 'deu',
            'Licence' => '',
            'SeriesNumber_1' => '',
            'Series_1' => ''
        );
        
        $form = new Publish_Form_PublishingSecond($this->_logger, $data);
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
        $session->additionalFields['collId0Institute_1'] = '1'; 
        $session->additionalFields['stepInstitute_1'] = '1';         
           
        $data = array(
            'PersonSubmitterFirstName_1' => '',
            'PersonSubmitterLastName_1' => '', 
            'PersonSubmitterEmail_1' => '',            
            'TitleMain_1' => '',
            'TitleMainLanguage_1' => '',            
            'TitleAbstract_1' => '',
            'TitleAbstractLanguage_1' => '',
            'PersonAuthorFirstName_1' => '',
            'PersonAuthorLastName_1' => '',
            'PersonAuthorAcademicTitle_1' => '',
            'PersonAuthorEmail_1' => '', 
            'PersonAuthorAllowEmailContact_1' => '0',
            'PersonAuthorDateOfBirth_1' => '',
            'PersonAuthorPlaceOfBirth_1' => '',
            'CompletedYear' => '',
            'CompletedDate' => '07.09.2011',
            'PageNumber' => '',
            'SubjectUncontrolled_1' => '',
            'SubjectUncontrolledLanguage_1' => '',
            'Institute_1' => '15994',
            'browseDownInstitute' => 'runter',
            'IdentifierUrn' => '',
            'Note' => '',
            'Language' => 'deu',
            'Licence' => '',
            'SeriesNumber_1' => '',
            'Series_1' => ''
        );
        
        $form = new Publish_Form_PublishingSecond($this->_logger, $data);
        $form->getExtendedForm($data, true);           
        $this->assertTrue($session->additionalFields['collId1Institute_1']=='15994');       
        $this->assertTrue($session->additionalFields['stepInstitute_1']=='2');       
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
        $session->additionalFields['collId0Institute_1'] = '1';
        $session->additionalFields['collId1Institute_1'] = '15994';        
        $session->additionalFields['stepInstitute_1'] = '2';         
           
        $data = array(
            'PersonSubmitterFirstName_1' => '',
            'PersonSubmitterLastName_1' => '', 
            'PersonSubmitterEmail_1' => '',            
            'TitleMain_1' => '',
            'TitleMainLanguage_1' => '',            
            'TitleAbstract_1' => '',
            'TitleAbstractLanguage_1' => '',
            'PersonAuthorFirstName_1' => '',
            'PersonAuthorLastName_1' => '',
            'PersonAuthorAcademicTitle_1' => '',
            'PersonAuthorEmail_1' => '', 
            'PersonAuthorAllowEmailContact_1' => '0',
            'PersonAuthorDateOfBirth_1' => '',
            'PersonAuthorPlaceOfBirth_1' => '',
            'CompletedYear' => '',
            'CompletedDate' => '07.09.2011',
            'PageNumber' => '',
            'SubjectUncontrolled_1' => '',
            'SubjectUncontrolledLanguage_1' => '',            
            'collId2Institute_1' => '15995',
            'browseUpInstitute' => 'hoch',
            'IdentifierUrn' => '',
            'Note' => '',
            'Language' => 'deu',
            'Licence' => '',
            'SeriesNumber_1' => '',
            'Series_1' => ''
        );
        
        $form = new Publish_Form_PublishingSecond($this->_logger, $data);       
        $form->getExtendedForm($data, true);                   
        $this->assertTrue($session->additionalFields['stepInstitute_1']=='1');          
        $this->assertFalse(array_key_exists('collId2Institute_1', $session->additionalFields));  
    }    
    
    /**
     * Button pressed: no button pressed 
     */
    public function testGetExtendedFormMethodWithMissingButton() {
        $config = Zend_Registry::get('Zend_Config');
        $config->documentTypes->include = 'all,preprint,article,demo,workingpaper';
        $session = new Zend_Session_Namespace('Publish');
        $session->documentType = 'preprint';
        $session->additionalFields = array();               
           
        $data = array(
            'PersonSubmitterFirstName_1' => '',
            'PersonSubmitterLastName_1' => '', 
            'PersonSubmitterEmail_1' => '',            
            'TitleMain_1' => '',
            'TitleMainLanguage_1' => '',            
            'TitleAbstract_1' => '',
            'TitleAbstractLanguage_1' => '',
            'PersonAuthorFirstName_1' => '',
            'PersonAuthorLastName_1' => '',
            'PersonAuthorAcademicTitle_1' => '',
            'PersonAuthorEmail_1' => '', 
            'PersonAuthorAllowEmailContact_1' => '0',
            'PersonAuthorDateOfBirth_1' => '',
            'PersonAuthorPlaceOfBirth_1' => '',
            'CompletedYear' => '',
            'CompletedDate' => '07.09.2011',
            'PageNumber' => '',
            'SubjectUncontrolled_1' => '',
            'SubjectUncontrolledLanguage_1' => '',            
            'Institute_1' => '',
            'IdentifierUrn' => '',
            'Note' => '',
            'Language' => 'deu',
            'Licence' => '',
            'SeriesNumber_1' => '',
            'Series_1' => ''
        );
        // no send button in post => FormController calls getExtendedForm() to find add or remove button 
        $form = new Publish_Form_PublishingSecond($this->_logger, $data);
        //additionalFields in intial state: all fields have value 1
        $this->assertEquals(9, count($session->additionalFields));          
        $this->assertEquals(1, $session->additionalFields['PersonSubmitter']);
        $this->assertEquals(1, $session->additionalFields['TitleMain']);
        $this->assertEquals(1, $session->additionalFields['TitleAbstract']);
        $this->assertEquals(1, $session->additionalFields['PersonAuthor']);
        $this->assertEquals(1, $session->additionalFields['SubjectUncontrolled']);
        $this->assertEquals(1, $session->additionalFields['stepInstitute_1']);
        $this->assertEquals(1, $session->additionalFields['collId0Institute_1']);
        $this->assertEquals(1, $session->additionalFields['Institute']);
        $this->assertEquals(1, $session->additionalFields['Series']);
        
        $form->getExtendedForm($data, true);        
        //no button pressed, additionalFields still in intial state
        //in case of pressed button -> some values differ from 1 
        //size: 9 (Submitter, TitleMain, TitleAbstract, Author, Uncontrolled, 3xInstitute, Series)
        $this->assertEquals(9, count($session->additionalFields));          
        $this->assertEquals(1, $session->additionalFields['PersonSubmitter']);
        $this->assertEquals(1, $session->additionalFields['TitleMain']);
        $this->assertEquals(1, $session->additionalFields['TitleAbstract']);
        $this->assertEquals(1, $session->additionalFields['PersonAuthor']);
        $this->assertEquals(1, $session->additionalFields['SubjectUncontrolled']);
        $this->assertEquals(1, $session->additionalFields['stepInstitute_1']);
        $this->assertEquals(1, $session->additionalFields['collId0Institute_1']);
        $this->assertEquals(1, $session->additionalFields['Institute']);
        $this->assertEquals(1, $session->additionalFields['Series']);
        
    }  
    
    public function testExternalElementLegalNotices() {
        $session = new Zend_Session_Namespace('Publish');
        $session->documentType = 'all';
        $session->additionalFields = array();   
        
        $elementData = array(
            'id' => 'LegalNotices',
            'label' => 'LegalNotices',
            'req' => 'required',
            'type' => 'Zend_Form_Element_Checkbox',
            'createType' => 'checkbox',
            'header' => 'header_LegalNotices',
            'value' => '0',
            'check' => '',
            'disabled' => '0',
            'error' => array(),
            'DT_external' => true            
            );

        $session->DT_externals['LegalNotices'] = $elementData;
        
        $form = new Publish_Form_PublishingSecond($this->_logger);
        $this->assertNotNull($form->getElement('LegalNotices'));
                
    }
}
