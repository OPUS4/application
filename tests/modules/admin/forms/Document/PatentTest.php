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
use Opus\Common\Patent;

/**
 * Unit Tests fuer Admin_Form_Document_Patent.
 */
class Admin_Form_Document_PatentTest extends ControllerTestCase
{
    /** @var string[] */
    protected $additionalResources = ['translation'];

    public function testCreateForm()
    {
        $form = new Admin_Form_Document_Patent();

        $this->assertEquals(6, count($form->getElements()));

        $this->assertNotNull($form->getElement('Id'));
        $this->assertNotNull($form->getElement('Number'));
        $this->assertTrue($form->getElement('Number')->isRequired());
        $this->assertNotNull($form->getElement('Countries'));
        $this->assertNotNull($form->getElement('YearApplied'));
        $this->assertNotNull($form->getElement('Application'));
        $this->assertNotNull($form->getElement('DateGranted'));
    }

    public function testPopulateFromModel()
    {
        $this->useEnglish();

        $form = new Admin_Form_Document_Patent();

        $document = Document::get(146);
        $patents  = $document->getPatent();
        $patent   = $patents[0];
        $patentId = $patent->getId();

        $form->populateFromModel($patent);

        $datesHelper = new Application_Controller_Action_Helper_Dates();

        $this->assertEquals($patentId, $form->getElement('Id')->getValue());
        $this->assertEquals('1234', $form->getElement('Number')->getValue());
        $this->assertEquals('DDR', $form->getElement('Countries')->getValue());
        $this->assertEquals('1970', $form->getElement('YearApplied')->getValue());
        $this->assertEquals('The foo machine.', $form->getElement('Application')->getValue());
        $this->assertEquals('1970/01/01', $form->getElement('DateGranted')->getValue());
    }

    public function testUpdateModel()
    {
        $this->useEnglish();

        $form = new Admin_Form_Document_Patent();

        $form->getElement('Number')->setValue('323');
        $form->getElement('Countries')->setValue('Germany');
        $form->getElement('YearApplied')->setValue('1987');
        $form->getElement('Application')->setValue('Patent Title');
        $form->getElement('DateGranted')->setValue('2008/03/20');

        $patent = Patent::new();

        $form->updateModel($patent);

        $datesHelper = new Application_Controller_Action_Helper_Dates();

        $this->assertEquals('323', $patent->getNumber());
        $this->assertEquals('Germany', $patent->getCountries());
        $this->assertEquals('1987', $patent->getYearApplied());
        $this->assertEquals('Patent Title', $patent->getApplication());
        $this->assertEquals('2008/03/20', $datesHelper->getDateString($patent->getDateGranted()));
    }

    public function testGetModelNew()
    {
        $this->useEnglish();

        $form = new Admin_Form_Document_Patent();

        $form->getElement('Number')->setValue('323');
        $form->getElement('Countries')->setValue('Germany');
        $form->getElement('YearApplied')->setValue('1987');
        $form->getElement('Application')->setValue('Patent Title');
        $form->getElement('DateGranted')->setValue('2008/03/20');

        $patent = $form->getModel();

        $datesHelper = new Application_Controller_Action_Helper_Dates();

        $this->assertNull($patent->getId());
        $this->assertEquals('323', $patent->getNumber());
        $this->assertEquals('Germany', $patent->getCountries());
        $this->assertEquals('1987', $patent->getYearApplied());
        $this->assertEquals('Patent Title', $patent->getApplication());
        $this->assertEquals('2008/03/20', $datesHelper->getDateString($patent->getDateGranted()));
    }

    public function testGetModel()
    {
        $this->useEnglish();

        $document = Document::get(146);
        $patents  = $document->getPatent();
        $patentId = $patents[0]->getId();

        $form = new Admin_Form_Document_Patent();

        $form->getElement('Id')->setValue($patentId);
        $form->getElement('Number')->setValue('323');
        $form->getElement('Countries')->setValue('Germany');
        $form->getElement('YearApplied')->setValue('1987');
        $form->getElement('Application')->setValue('Patent Title');
        $form->getElement('DateGranted')->setValue('2008/03/20');

        $patent = $form->getModel();

        $datesHelper = new Application_Controller_Action_Helper_Dates();

        $this->assertEquals($patentId, $patent->getId());
        $this->assertEquals('323', $patent->getNumber());
        $this->assertEquals('Germany', $patent->getCountries());
        $this->assertEquals('1987', $patent->getYearApplied());
        $this->assertEquals('Patent Title', $patent->getApplication());
        $this->assertEquals('2008/03/20', $datesHelper->getDateString($patent->getDateGranted()));
    }

    /**
     * Kann nur passieren wenn POST manipuliert wurde.
     *
     * Ungültige IDs werden ignoriert und Patent wie ein neues behandelt.
     */
    public function testGetModelInvalidId()
    {
        $this->useEnglish();

        $form = new Admin_Form_Document_Patent();

        $form->getElement('Id')->setValue('notvalid');
        $form = new Admin_Form_Document_Patent();

        $form->getElement('Number')->setValue('323');
        $form->getElement('Countries')->setValue('Germany');
        $form->getElement('YearApplied')->setValue('1987');
        $form->getElement('Application')->setValue('Patent Title');
        $form->getElement('DateGranted')->setValue('2008/03/20');

        $patent = $form->getModel();

        $datesHelper = new Application_Controller_Action_Helper_Dates();

        $this->assertNull($patent->getId());
        $this->assertEquals('323', $patent->getNumber());
        $this->assertEquals('Germany', $patent->getCountries());
        $this->assertEquals('1987', $patent->getYearApplied());
        $this->assertEquals('Patent Title', $patent->getApplication());
        $this->assertEquals('2008/03/20', $datesHelper->getDateString($patent->getDateGranted()));
    }

    public function testValidationFalse()
    {
        $this->useEnglish();

        $form = new Admin_Form_Document_Patent();

        $post = [
            'Number'      => '', // ist Pflichtfeld
            'YearApplied' => 'year', // muss Integer sein
            'DateGranted' => '2008/02/31', // muss gültiges Datum sein
        ];

        $this->assertFalse($form->isValid($post));
        $this->assertContains('isEmpty', $form->getErrors('Number'));
        $this->assertContains('notInt', $form->getErrors('YearApplied'));
        $this->assertContains('dateInvalidDate', $form->getErrors('DateGranted'));
        $this->assertContains('isEmpty', $form->getErrors('Countries'));
        $this->assertContains('isEmpty', $form->getErrors('Application'));

        $post = [
            'Number'      => '1',
            'YearApplied' => '-1',
        ];

        $this->assertFalse($form->isValid($post));
        $this->assertContains('notGreaterThan', $form->getErrors('YearApplied'));
    }

    public function testValidationTrue()
    {
        $this->useEnglish();

        $form = new Admin_Form_Document_Patent();

        $post = [
            'Number'      => '1',
            'YearApplied' => '1980',
            'Countries'   => 'Deutschland',
            'Application' => 'Meine tolle Erfindung',
            'DateGranted' => '2000/03/25',
        ];

        $this->assertTrue($form->isValid($post));
    }

    public function testRegressionOpusvier2824()
    {
        $this->useEnglish();

        $form = new Admin_Form_Document_Patent();

        $form->getElement('Number')->setValue('323');
        $form->getElement('YearApplied')->setValue(''); // Leeres Feld
        $form->getElement('Countries')->setValue('Germany');
        $form->getElement('Application')->setValue('description');

        $patent = Patent::new();

        $form->updateModel($patent);

        $document = $this->createTestDocument();
        $document->addPatent($patent);

        $document->store();

        $documentId = $document->getId();

        $document = Document::get($documentId);

        $patents = $document->getPatent();
        $patent  = $patents[0];

        $this->assertEquals('323', $patent->getNumber());
        $this->assertNotEquals('0000', $patent->getYearApplied());
        $this->assertNull($patent->getYearApplied());
    }
}
