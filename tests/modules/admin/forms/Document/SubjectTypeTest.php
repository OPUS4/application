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
 * Unit Tests für Unterformular, daß Subjects eines bestimmten Typs anzeigt.
 */
class Admin_Form_Document_SubjectTypeTest extends ControllerTestCase
{
    /** @var string[] */
    protected $additionalResources = ['view', 'translation'];

    public function testCreateForm()
    {
        $this->useEnglish();

        $form = new Admin_Form_Document_SubjectType('swd');

        $this->assertEquals(0, count($form->getSubForms()));
        $this->assertEquals(1, count($form->getElements()));
        $this->assertNotNull($form->getElement('Add'));
        $this->assertEquals('swd', $form->getSubjectType());
        $this->useEnglish();
        // translation key is 'admin_document_section_subjectswd'
        $this->assertEquals('GND Keywords', $form->getLegend());
    }

    public function testCreateNewSubFormInstance()
    {
        $form = new Admin_Form_Document_SubjectType('psyndex');

        $subform = $form->createNewSubFormInstance();

        $this->assertInstanceOf('Admin_Form_Document_Subject', $subform);
        $this->assertEquals('psyndex', $subform->getSubjectType());
        $this->assertNull($subform->getLanguage());
    }

    public function testCreateNewSubFormInstanceSwd()
    {
        $form = new Admin_Form_Document_SubjectType('swd');

        $subform = $form->createNewSubFormInstance();

        $this->assertInstanceOf('Admin_Form_Document_Subject', $subform);
        $this->assertEquals('swd', $subform->getSubjectType());
        $this->assertEquals('deu', $subform->getLanguage());
    }

    public function testGetFieldValues()
    {
        $form = new Admin_Form_Document_SubjectType('swd');

        $document = Document::get(146);

        $values = $form->getFieldValues($document);

        $this->assertEquals(1, count($values));
        $this->assertEquals('Berlin', $values[0]->getValue());
    }

    /**
     * Dieser Test soll sicherstellen, das updateModel überschrieben wurde und das Dokument in Ruhe lässt.
     */
    public function testUpdateModel()
    {
        $form = new Admin_Form_Document_SubjectType('swd'); // Formular ohne Schlagwörter

        $document = Document::get(200);

        $this->assertEquals(2, count($document->getSubject()));

        $form->updateModel($document); // würde normalerweise alle Subjects löschen, wurde aber überschrieben

        $this->assertEquals(2, count($document->getSubject()));
    }
}
