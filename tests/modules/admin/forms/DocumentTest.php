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
 * @category    Application Unit Test
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Unit Tests für Metadaten-Formular Klasse.
 */
class Admin_Form_DocumentTest extends ControllerTestCase {

    public function testConstructForm() {
        $form = new Admin_Form_Document();

        $this->assertEquals(0, count($form->getElements()));

        $subformNames = array(
            'ActionBox',
            'InfoBox',
            'General',
            'Persons',
            'Titles',
            'Bibliographic',
            'Series',
            'Enrichments',
            'Collections',
            'Content',
            'Identifiers',
            'Licences',
            'Patents',
            'Notes',
            'Actions'
        );

        $this->verifySubForms($form, $subformNames);
    }

    public function testPopulateFromModel() {
        $this->markTestIncomplete('implement');
    }

    public function testGetInstanceFromPost() {
        $this->markTestIncomplete('implement');
    }

    public function testProcessPost() {
        $this->markTestIncomplete('implement');
    }

    public function textContinueEdit() {
        $this->markTestIncomplete('implement');
    }

    public function testIsValid() {
        $this->markTestIncomplete('implement');
    }

    public function testSetMessage() {
        $this->markTestIncomplete('implement');
    }

    public function testGetMessage() {
        $this->markTestIncomplete('implement');
    }

    public function testPrepareRenderingAsViewFullDocument() {
        $form = new Admin_Form_Document();

        $document = new Opus_Document(146);

        $form->populateFromModel($document);
        $form->prepareRenderingAsView();

        $this->verifySubForms($form, array(
            'ActionBox',
            'InfoBox',
            'General',
            'Persons',
            'Titles',
            'Bibliographic',
            'Series',
            'Enrichments',
            'Collections',
            'Content',
            'Identifiers',
            'Licences',
            'Patents',
            'Notes',
        ));
    }

    /**
     * Für ein leeres Dokument werden fast alle Unterformulare entfernt. Weiterhin angezeigt werden die ActionBox, und
     * die InfoBox,
     */
    public function testPrepareRenderingAsViewForEmptyDocument() {
        $form = new Admin_Form_Document();

        $document = new Opus_Document();

        $form->populateFromModel($document);
        $form->prepareRenderingAsView();

        $this->verifySubForms($form, array('ActionBox', 'InfoBox'));
    }

    protected function verifySubForms($form, $names) {
        $this->assertEquals(count($names), count($form->getSubForms()));

        foreach ($names as $name) {
            $this->assertNotNull($form->getSubForm($name), "Unterformular '$name' fehlt.");
        }
    }

}