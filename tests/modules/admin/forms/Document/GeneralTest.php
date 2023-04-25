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
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\Document;

/**
 * Unit Tests fuer Admin_Form_Document_General.
 */
class Admin_Form_Document_GeneralTest extends ControllerTestCase
{
    /** @var string[] */
    protected $additionalResources = ['view', 'translation'];

    public function testCreateForm()
    {
        $form = new Admin_Form_Document_General();

        $this->assertEquals(7, count($form->getElements()));

        $this->assertNotNull($form->getElement('Language'));
        $this->assertNotNull($form->getElement('Type'));
        $this->assertNotNull($form->getElement('PublishedDate'));
        $this->assertNotNull($form->getElement('PublishedYear'));
        $this->assertNotNull($form->getElement('CompletedDate'));
        $this->assertNotNull($form->getElement('CompletedYear'));
        $this->assertNotNull($form->getElement('EmbargoDate'));
    }

    /**
     * TODO use temporary Document instead of doc from test data
     */
    public function testPopulateFromModel()
    {
        $this->useEnglish();

        $document = Document::get(146);

        $form = new Admin_Form_Document_General();

        $form->populateFromModel($document);

        $this->assertEquals('deu', $form->getElement('Language')->getValue());
        $this->assertEquals('masterthesis', $form->getElement('Type')->getValue());
        $this->assertEquals('2007/04/30', $form->getElement('PublishedDate')->getValue());
        $this->assertEquals('2008', $form->getElement('PublishedYear')->getValue());
        $this->assertEquals('2011/12/01', $form->getElement('CompletedDate')->getValue());
        $this->assertEquals('2009', $form->getElement('CompletedYear')->getValue());
        $this->assertEquals('1984/06/05', $form->getElement('EmbargoDate')->getValue());
    }

    public function testUpdateModel()
    {
        $this->useEnglish();

        $form = new Admin_Form_Document_General();

        $form->getElement('Language')->setValue('eng');
        $form->getElement('Type')->setValue('masterthesis');
        $form->getElement('PublishedDate')->setValue('2005/06/17');
        $form->getElement('PublishedYear')->setValue('2006');
        $form->getElement('CompletedDate')->setValue('2006/07/03');
        $form->getElement('CompletedYear')->setValue('2007');
        $form->getElement('EmbargoDate')->setValue('1986/03/29');

        $document = $this->createTestDocument();

        $form->updateModel($document);

        $this->assertEquals('eng', $document->getLanguage());
        $this->assertEquals('masterthesis', $document->getType());

        $this->assertNotNull($document->getPublishedDate());
        $this->assertEquals('2005/06/17', date('Y/m/d', $document->getPublishedDate()->getTimestamp()));
        $this->assertEquals('2006', $document->getPublishedYear());

        $this->assertNotNull($document->getCompletedDate());
        $this->assertEquals('2006/07/03', date('Y/m/d', $document->getCompletedDate()->getTimestamp()));
        $this->assertEquals('2007', $document->getCompletedYear());

        $this->assertNotNull($document->getEmbargoDate());
        $this->assertEquals('1986/03/29', date('Y/m/d', $document->getEmbargoDate()->getTimestamp()));
    }

    public function testValidation()
    {
        $this->useEnglish();

        $form = new Admin_Form_Document_General();

        $post = [
            'Language'      => '',
            'Type'          => '',
            'PublishedDate' => 'date1', // muss Datum sein
            'PublishedYear' => 'year1', // muss Integer sein
            'CompletedDate' => '2008/02/31', // muss korrektes Datum sein
            'CompletedYear' => '-1', // muss groesser als 0 sein
            'EmbargoDate'   => '2008/02/31', // muss korrektes Datum sein
        ];

        $this->assertFalse($form->isValid($post));

        $this->assertContains('isEmpty', $form->getErrors('Language'));
        $this->assertContains('isEmpty', $form->getErrors('Type'));
        $this->assertContains('dateFalseFormat', $form->getErrors('PublishedDate'));
        $this->assertContains('notInt', $form->getErrors('PublishedYear'));
        $this->assertContains('dateInvalidDate', $form->getErrors('CompletedDate'));
        $this->assertContains('notGreaterThan', $form->getErrors('CompletedYear'));
        $this->assertContains('dateInvalidDate', $form->getErrors('EmbargoDate'));
    }

    public function testValidationGerman()
    {
        $this->useGerman();

        $form = new Admin_Form_Document_General();

        $post = [
            'Language'      => 'deu',
            'Type'          => 'demo',
            'CompletedDate' => '30.01.2010', // korrektes Datum
        ];

        $this->assertTrue($form->isValid($post));

        $post = [
            'Language'      => 'bla', // ungültige Sprache
            'Type'          => 'unknown', // ungültiger Typ
            'CompletedDate' => '30.02.2010', // ungültiges Datum
        ];

        $this->assertFalse($form->isValid($post));
        $this->assertContains('notInArray', $form->getErrors('Language'));
        $this->assertContains('notInArray', $form->getErrors('Type'));
        $this->assertContains('dateInvalidDate', $form->getErrors('CompletedDate'));
    }

    public function testTranslationOfLabels()
    {
        $this->useGerman();

        $form = new Admin_Form_Document_General();

        $element = $form->getElement(Admin_Form_Document_General::ELEMENT_PUBLISHED_DATE);
        $this->assertEquals("Datum der Erstveröffentlichung", $element->getLabel());

        $element = $form->getElement(Admin_Form_Document_General::ELEMENT_LANGUAGE);
        $this->assertEquals("Sprache", $element->getLabel());

        $element = $form->getElement(Admin_Form_Document_General::ELEMENT_TYPE);
        $this->assertEquals("Dokumentart", $element->getLabel());
    }
}
