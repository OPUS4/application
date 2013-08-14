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
 * @copyright   Copyright (c) 2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Unit Tests fuer Unterformular fuer bibliographische Information im Metadaten-Formular.
 */
class Admin_Form_DocumentBibliographicTest extends ControllerTestCase {
    
    public function testCreateForm() {
        $form = new Admin_Form_DocumentBibliographic();
        
        $this->assertNotNull($form->getElement('ContributingCorporation'));
        $this->assertNotNull($form->getElement('CreatingCorporation'));
        $this->assertNotNull($form->getElement('Edition'));
        $this->assertNotNull($form->getElement('Issue'));
        $this->assertNotNull($form->getElement('PageFirst'));
        $this->assertNotNull($form->getElement('PageLast'));
        $this->assertNotNull($form->getElement('PageCount'));
        $this->assertNotNull($form->getElement('PublisherName'));
        $this->assertNotNull($form->getElement('PublisherPlace'));
        $this->assertNotNull($form->getElement('Volume'));
        $this->assertNotNull($form->getElement('ThesisDateAccepted'));
        $this->assertNotNull($form->getElement('ThesisYearAccepted'));
        $this->assertNotNull($form->getElement('BelongsToBibliography'));
    }
    
    public function testPopulateFromModel() {
        $form = new Admin_Form_DocumentBibliographic();
        
        $doc = new Opus_Document(146);
        
        $form->populateFromModel($doc);
        
        $this->assertEquals($doc->getContributingCorporation(), 
                $form->getElement('ContributingCorporation')->getValue());
        $this->assertEquals($doc->getCreatingCorporation(), $form->getElement('CreatingCorporation')->getValue());
        $this->assertEquals($doc->getEdition(), $form->getElement('Edition')->getValue());
        $this->assertEquals($doc->getIssue(), $form->getElement('Issue')->getValue());
        $this->assertEquals($doc->getPageFirst(), $form->getElement('PageFirst')->getValue());
        $this->assertEquals($doc->getPageLast(), $form->getElement('PageLast')->getValue());
        $this->assertEquals($doc->getPageNumber(), $form->getElement('PageCount')->getValue());
        $this->assertEquals($doc->getPublisherName(), $form->getElement('PublisherName')->getValue());
        $this->assertEquals($doc->getPublisherPlace(), $form->getElement('PublisherPlace')->getValue());
        $this->assertEquals($doc->getVolume(), $form->getElement('Volume')->getValue());
        
        $datesHelper = new Controller_Helper_Dates();
        $date = $datesHelper->getDateString($doc->getThesisDateAccepted());
        $this->assertEquals($date, $form->getElement('ThesisDateAccepted')->getValue());
        
        $this->assertEquals($doc->getThesisYearAccepted(), $form->getElement('ThesisYearAccepted')->getValue());
        $this->assertEquals($doc->getBelongsToBibliography(), $form->getElement('BelongsToBibliography')->getValue());
    }
    
    public function testUpdateModel() {
        $this->setUpEnglish();
        
        $form = new Admin_Form_DocumentBibliographic();
        
        $form->getElement('ContributingCorporation')->setValue('contribcorp');
        $form->getElement('CreatingCorporation')->setValue('creatingcorp');
        $form->getElement('Edition')->setValue('2nd');
        $form->getElement('Issue')->setValue('3');
        $form->getElement('PageFirst')->setValue(34);
        $form->getElement('PageLast')->setValue(38);
        $form->getElement('PageCount')->setValue('5');
        $form->getElement('PublisherName')->setValue('Wizard');
        $form->getElement('PublisherPlace')->setValue('Oz');
        $form->getElement('Volume')->setValue('5');
        $form->getElement('ThesisDateAccepted')->setValue('2010/04/21');
        $form->getElement('ThesisYearAccepted')->setValue('2010');
        $form->getElement('BelongsToBibliography')->setValue(true);
        
        $model = new Opus_Document();
        
        $form->updateModel($model);
        
        $this->assertEquals('contribcorp', $model->getContributingCorporation());
        $this->assertEquals('creatingcorp', $model->getCreatingCorporation());
        $this->assertEquals('2nd', $model->getEdition());
        $this->assertEquals('3', $model->getIssue());
        $this->assertEquals(34, $model->getPageFirst());
        $this->assertEquals('38', $model->getPageLast());
        $this->assertEquals(5, $model->getPageNumber());
        $this->assertEquals('Wizard', $model->getPublisherName());
        $this->assertEquals('Oz', $model->getPublisherPlace());
        $this->assertEquals('5', $model->getVolume());
        
        $datesHelper = new Controller_Helper_Dates();
        $this->assertEquals('2010/04/21', $datesHelper->getDateString($model->getThesisDateAccepted()));
        
        $this->assertEquals('2010', $model->getThesisYearAccepted());
        $this->assertTrue($model->getBelongsToBibliography());
        
        $form->getElement('BelongsToBibliography')->setValue(false);
        
        $form->updateModel($model);
        
        // Funktion liefert '0' zurück, assertFalse funktioniert nicht
        $this->assertEquals(0, $model->getBelongsToBibliography());
    }
    
    public function testUpdateModelForEmptyFields() {
        $this->setUpEnglish();
        
        $form = new Admin_Form_DocumentBibliographic();
        
        $form->getElement('ContributingCorporation')->setValue(' ');
        $form->getElement('CreatingCorporation')->setValue(' ');
        $form->getElement('Edition')->setValue(' ');
        $form->getElement('Issue')->setValue(' ');
        $form->getElement('PageFirst')->setValue(' ');
        $form->getElement('PageLast')->setValue(' ');
        $form->getElement('PageCount')->setValue(' ');
        $form->getElement('PublisherName')->setValue(' ');
        $form->getElement('PublisherPlace')->setValue(' ');
        $form->getElement('Volume')->setValue(' ');
        $form->getElement('ThesisDateAccepted')->setValue('  ');
        $form->getElement('ThesisYearAccepted')->setValue('  ');
        
        $model = new Opus_Document();
        $form->updateModel($model);
        
        $this->assertNull($model->getContributingCorporation(), 'ContributingCorporation not null');
        $this->assertNull($model->getCreatingCorporation(), 'CreatingCorportation not null');
        $this->assertNull($model->getEdition(), 'Edition not null');
        $this->assertNull($model->getIssue(), 'Issue not null');
        $this->assertNull($model->getPageFirst(), 'PageFirst not null');
        $this->assertNull($model->getPageLast(), 'PageLast not null');
        $this->assertNull($model->getPageNumber(), 'PageNumber not null');
        $this->assertNull($model->getPublisherName(), 'PublisherName not null');
        $this->assertNull($model->getPublisherPlace(), 'PublisherPlace not null');
        $this->assertNull($model->getVolume(), 'Volume not null');
        $this->assertNull($model->getThesisDateAccepted(), 'ThesisDateAccepted not null');
        $this->assertNull($model->getThesisYearAccepted(), 'ThesisYearAccepted not null');
    }
    
    public function testUpdateModelForValue0() {
        $form = new Admin_Form_DocumentBibliographic();
        
        $form->getElement('ContributingCorporation')->setValue('0');
        $form->getElement('CreatingCorporation')->setValue('0');
        $form->getElement('Edition')->setValue('0');
        $form->getElement('Issue')->setValue('0');
        $form->getElement('PageFirst')->setValue('0');
        $form->getElement('PageLast')->setValue('0');
        $form->getElement('PublisherName')->setValue('0');
        $form->getElement('PublisherPlace')->setValue('0');
        $form->getElement('Volume')->setValue('0');
        $form->getElement('ThesisYearAccepted')->setValue('0');
        
        $model = new Opus_Document();
        $form->updateModel($model);
        
        $this->assertEquals('0', $model->getContributingCorporation());
        $this->assertEquals('0', $model->getCreatingCorporation());
        $this->assertEquals('0', $model->getEdition());
        $this->assertEquals('0', $model->getIssue());
        $this->assertEquals('0', $model->getPageFirst());
        $this->assertEquals('0', $model->getPageLast());
        $this->assertEquals('0', $model->getPublisherName());
        $this->assertEquals('0', $model->getPublisherPlace());
        $this->assertEquals('0', $model->getVolume());
        $this->assertEquals('0', $model->getThesisYearAccepted());
    }
    
    public function testValidation() {
        $this->useEnglish();
        
        $form = new Admin_Form_DocumentBibliographic();
        
        $post = array(
            'ThesisDateAccepted' => '2010/02/31', // muss korrektes Datum sein
            'ThesisYearAccepted' => 'Jahr' // muss Zahl sein
        );
        
        $this->assertFalse($form->isValid($post));
        
        $this->assertContains('dateInvalidDate', $form->getErrors('ThesisDateAccepted'));
        $this->assertContains('notInt', $form->getErrors('ThesisYearAccepted'));
        
        $post = array();
        
        $this->assertTrue($form->isValid($post)); // keine Pflichteingaben
        
        $post = array(
            'ThesisDateAccepted' => '20. Feb 2010',
            'ThesisYearAccepted' => '-1'
        );

        $this->assertFalse($form->isValid($post)); // keine Pflichteingaben
        $this->assertContains('notGreaterThan', $form->getErrors('ThesisYearAccepted'));
        $this->assertContains('dateFalseFormat', $form->getErrors('ThesisDateAccepted'));
    }

    public function testRegression3046AlphanumericPageFirstLastAndNumberValues() {
        $form = new Admin_Form_DocumentBibliographic();

        $post = array(
            'PageFirst' => 'XI',
            'PageLast' => '12',
            'PageCount' => 'iiv'
        );

        $this->assertTrue($form->isValid($post));
    }
    
}
