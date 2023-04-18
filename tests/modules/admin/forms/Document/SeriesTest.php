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
 * @copyright   Copyright (c) 2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\Document;
use Opus\Model\Dependent\Link\DocumentSeries;

/**
 * Unit Tests fuer Unterformular fuer Verknuepfung mit Schriftenreihe in Metadaten-Formular.
 */
class Admin_Form_Document_SeriesTest extends ControllerTestCase
{
    /** @var string[] */
    protected $additionalResources = ['database'];

    public function testCreateForm()
    {
        $form = new Admin_Form_Document_Series();

        $this->assertCount(4, $form->getElements());
        $this->assertNotNull($form->getElement('Id'));
        $this->assertNotNull($form->getElement('SeriesId'));
        $this->assertNotNull($form->getElement('Number'));
        $this->assertNotNull($form->getElement('SortOrder'));
    }

    public function testPopulateFromModel()
    {
        $form = new Admin_Form_Document_Series();

        $doc = Document::get(146);

        $series = $doc->getSeries();
        $model  = $series[0];

        $form->populateFromModel($model);

        $this->assertEquals($doc->getId(), $form->getElement('Id')->getValue());
        $this->assertEquals($model->getModel()->getId(), $form->getElement('SeriesId')->getValue());
        $this->assertEquals($model->getNumber(), $form->getElement('Number')->getValue());
        $this->assertEquals($model->getDocSortOrder(), $form->getElement('SortOrder')->getValue());
    }

    public function testUpdateModel()
    {
        $form = new Admin_Form_Document_Series();

        $form->getElement('SeriesId')->setValue(3);
        $form->getElement('Number')->setValue('III');
        $form->getElement('SortOrder')->setValue(2);

        $model = new DocumentSeries();

        $form->updateModel($model);

        $this->assertEquals(3, $model->getModel()->getId());
        $this->assertEquals('III', $model->getNumber());
        $this->assertEquals('2', $model->getDocSortOrder());
    }

    public function testGetModel()
    {
        $form = new Admin_Form_Document_Series();

        $doc    = Document::get(146);
        $series = $doc->getSeries();

        $form->getElement('Id')->setValue($doc->getId());
        $form->getElement('SeriesId')->setValue($series[0]->getModel()->getId());
        $form->getElement('Number')->setValue('b');
        $form->getElement('SortOrder')->setValue(7);

        $model   = $form->getModel();
        $modelId = $model->getId();

        $this->assertEquals(146, $modelId[0]);
        $this->assertEquals($series[0]->getModel()->getId(), $modelId[1]);
        $this->assertEquals('b', $model->getNumber());
        $this->assertEquals(7, $model->getDocSortOrder());
    }

    public function testGetNewModel()
    {
        $form = new Admin_Form_Document_Series();

        $form->getElement('SeriesId')->setValue(3);
        $form->getElement('Number')->setValue('VI');
        $form->getElement('SortOrder')->setValue(2);

        $model = $form->getModel();

        $this->assertNull($model->getId());
        $this->assertEquals(3, $model->getModel()->getId());
        $this->assertEquals('VI', $model->getNumber());
        $this->assertEquals(2, $model->getDocSortOrder());
    }

    public function testGetModelWithoutSortOrder()
    {
        $form = new Admin_Form_Document_Series();

        $form->getElement('SeriesId')->setValue(3);
        $form->getElement('Number')->setValue('VI');

        $model = $form->getModel();

        $this->assertNull($model->getId());
        $this->assertEquals(3, $model->getModel()->getId());
        $this->assertEquals('VI', $model->getNumber());
        $this->assertNull($model->getDocSortOrder());
    }

    public function testValidationRequired()
    {
        $form = new Admin_Form_Document_Series();

        $post = [
            'Number'   => ' ',
            'SeriesId' => ' ',
        ];

        $this->assertFalse($form->isValid($post));

        $this->assertContains('isEmpty', $form->getErrors('Number'));
        $this->assertContains('isEmpty', $form->getErrors('SeriesId'));
    }

    public function testValidationSortOrder()
    {
        $form = new Admin_Form_Document_Series();

        $post = [
            'SortOrder' => '1st',
        ];

        $this->assertFalse($form->isValid($post));

        $this->assertContains('notInt', $form->getErrors('SortOrder'));

        $post = [
            'SeriesId'  => '2', // required
            'Number'    => '800', // required
            'SortOrder' => '-1',
        ];

        $this->assertFalse($form->isValid($post));

        $this->assertContains('notGreaterThan', $form->getErrors('SortOrder'));
    }

    public function testValidationSeriesId()
    {
        $form = new Admin_Form_Document_Series();

        $post = [
            'SeriesId' => 'a',
        ];

        $this->assertFalse($form->isValid($post));

        $this->assertContains('notInt', $form->getErrors('SeriesId'));
    }

    public function testValidationAlreadyUsedNumber()
    {
        $form = new Admin_Form_Document_Series();

        $post = [
            'Id'       => '250',
            'SeriesId' => '1',
            'Number'   => '5/5', // used by document ID = 146
        ];

        $this->assertTrue($form->isValid($post));
        // TODO duplicate numbers are now allowed OPUSVIER-3917
        // $this->assertContains('notAvailable', $form->getErrors('Number'));
    }

    public function testValidationNumberCurrentDocument()
    {
        $form = new Admin_Form_Document_Series();

        $post = [
            'Id'       => '146',
            'SeriesId' => '1',
            'Number'   => '5/5', // used by document ID = 146
        ];

        $this->assertTrue($form->isValid($post));
    }
}
