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
 * @category    Application Unit Test
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Unit Tests fuer Admin_Form_DocumentPatent.
 */
class Admin_Form_DocumentPatentTest extends ControllerTestCase {
    
    public function testCreateForm() {
        $form = new Admin_Form_DocumentPatent();
        
        $this->assertNotNull($form->getElement('Id'));
        $this->assertNotNull($form->getElement('Number'));
        $this->assertNotNull($form->getElement('Countries'));
        $this->assertNotNull($form->getElement('YearApplied'));
        $this->assertNotNull($form->getElement('Application'));
        $this->assertNotNull($form->getElement('DateGranted'));
    }
    
    public function testPopulateFromModel() {
        $this->setUpEnglish();
        
        $form = new Admin_Form_DocumentPatent();

        $document = new Opus_Document(146);
        $patents = $document->getPatent();
        $patent = $patents[0];
        
        $form->populateFromModel($patent);
        
        $datesHelper = new Controller_Helper_Dates();
        
        $this->assertEquals($patent->getId(), $form->getElement('Id')->getValue());
        $this->assertEquals($patent->getNumber(), $form->getElement('Number')->getValue());
        $this->assertEquals($patent->getCountries(), $form->getElement('Countries')->getValue());
        $this->assertEquals($patent->getYearApplied(), $form->getElement('YearApplied')->getValue());
        $this->assertEquals($patent->getApplication(), $form->getElement('Application')->getValue());
        $this->assertEquals($datesHelper->getDateString($patent->getDateGranted()), 
                $form->getElement('DateGranted')->getValue());
    }
    
    public function testUpdateModel() {
        $this->setUpEnglish();
        
        $form = new Admin_Form_DocumentPatent();
        
        $form->getElement('Number')->setValue('323');
        $form->getElement('Countries')->setValue('Germany');
        $form->getElement('YearApplied')->setValue('1987');
        $form->getElement('Application')->setValue('Patent Title');
        $form->getElement('DateGranted')->setValue('2008/03/20');
        
        $patent = new Opus_Patent();
        
        $form->updateModel($patent);
        
        $datesHelper = new Controller_Helper_Dates();
        
        $this->assertEquals('323', $patent->getNumber());
        $this->assertEquals('Germany', $patent->getCountries());
        $this->assertEquals('1987', $patent->getYearApplied());
        $this->assertEquals('Patent Title', $patent->getApplication());
        $this->assertEquals('2008/03/20', $datesHelper->getDateString($patent->getDateGranted()));
    }
    
    /**
     * TODO test getModel for existing Opus_Patent (with ID)
     */
    public function testGetModel() {
        $this->setUpEnglish();
        
        $form = new Admin_Form_DocumentPatent();
        
        $form->getElement('Number')->setValue('323');
        $form->getElement('Countries')->setValue('Germany');
        $form->getElement('YearApplied')->setValue('1987');
        $form->getElement('Application')->setValue('Patent Title');
        $form->getElement('DateGranted')->setValue('2008/03/20');
        
        $patent = $form->getModel();
        
        $datesHelper = new Controller_Helper_Dates();
        
        $this->assertNull($patent->getId());
        $this->assertEquals('323', $patent->getNumber());
        $this->assertEquals('Germany', $patent->getCountries());
        $this->assertEquals('1987', $patent->getYearApplied());
        $this->assertEquals('Patent Title', $patent->getApplication());
        $this->assertEquals('2008/03/20', $datesHelper->getDateString($patent->getDateGranted()));
    }
    
    public function testValidation() {
        $this->setUpEnglish();
        
        $form = new Admin_Form_DocumentPatent();

        $post = array(
            'Number' => '', // ist Pflichtfeld
            'YearApplied' => 'year', // muss Integer sein
            'DateGranted' => '2008/02/31' // muss gültiges Datum sein
        );
        
        $this->assertFalse($form->isValid($post));
        $this->assertContains('isEmpty', $form->getErrors('Number'));
        $this->assertContains('notInt', $form->getErrors('YearApplied'));
        $this->assertContains('dateInvalidDate', $form->getErrors('DateGranted'));
        
        
        $post = array(
            'Number' => '1',
            'YearApplied' => '-1'
        );
                
        $this->assertFalse($form->isValid($post));
        $this->assertContains('notGreaterThan', $form->getErrors('YearApplied'));
        
        $post = array(
            'Number' => '1',
            'YearApplied' => '1980',
            'Countries' => 'Deutschland',
            'Application' => 'Meine tolle Erfindung',
            'DateGranted' => '2000/03/25'
        );
        
        $this->assertTrue($form->isValid($post));
    }
    
    public function testRegressionOpusvier2824() {
        $this->setUpEnglish();

        $this->setUpEnglish();
        
        $form = new Admin_Form_DocumentPatent();
                
        $form->getElement('Number')->setValue('323');
        $form->getElement('YearApplied')->setValue(''); // Leeres Feld
        
        $patent = new Opus_Patent();
        
        $form->updateModel($patent);
        
        $document = new Opus_Document();
        $document->addPatent($patent);
        
        $document->store();
        
        $documentId = $document->getId();
        
        $document = new Opus_Document($documentId);
        
        $patents = $document->getPatent();
        $patent = $patents[0];
        
        $document->deletePermanent();
        
        $this->assertEquals('323', $patent->getNumber());
        $this->assertNotEquals('0000', $patent->getYearApplied());
    }
    
}
