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
 **/

use Opus\Common\Document;

/**
 * Unit Tests fuer Unterformular fuer eine mit einem Dokument verknuepfte Person.
 */
class Admin_Form_Document_PersonTest extends ControllerTestCase
{
    /** @var string[] */
    protected $additionalResources = ['database'];

    public function testCreateForm()
    {
        $form = new Admin_Form_Document_Person();

        $this->assertEquals(5, count($form->getElements()));

        $this->assertNotNull($form->getElement('PersonId'));
        $this->assertNotNull($form->getElement('AllowContact'));
        $this->assertNotNull($form->getElement('Role'));
        $this->assertNotNull($form->getElement('SortOrder'));

        $this->assertNotNull($form->getElement('Edit'));
    }

    public function testProcessPostEmpty()
    {
        $form = new Admin_Form_Document_Person();

        $this->assertNull($form->processPost([], null));
    }

    public function testProcessPostEdit()
    {
        $form = new Admin_Form_Document_Person();

        $form->getElement('PersonId')->setValue('1234');

        $post = [
            'Edit' => 'Editieren',
        ];

        $result = $form->processPost($post, null);

        $this->assertNotNull($result);

        $this->assertArrayHasKey('result', $result);
        $this->assertEquals(Admin_Form_Document::RESULT_SWITCH_TO, $result['result']);

        $this->assertArrayHasKey('target', $result);

        $target = $result['target'];

        $this->assertArrayHasKey('module', $target);
        $this->assertEquals('admin', $target['module']);

        $this->assertArrayHasKey('controller', $target);
        $this->assertEquals('person', $target['controller']);

        $this->assertArrayHasKey('action', $target);
        $this->assertEquals('editlinked', $target['action']);

        $this->assertArrayHasKey('personId', $target);
        $this->assertEquals('1234', $target['personId']);
    }

    public function testGetLinkModel()
    {
        $form = new Admin_Form_Document_Person();

        $document = Document::get(146);

        $authors = $document->getPersonAuthor();

        $form->populateFromModel($authors[0]);
        $form->getElement('Role')->setValue(null); // nicht teil des POST beim Metadaten-Formular

        $person = $form->getLinkModel(146, 'author');

        $this->assertEquals($person->getId(), $authors[0]->getId());
        $this->assertNotNull($person->getModel());
        $this->assertEquals('author', $person->getRole());
    }

    public function testGetLinkModelNew()
    {
        $form = new Admin_Form_Document_Person();

        $form->getElement('PersonId')->setValue(310);
        // $form->getElement('Role')->setValue('submitter'); // nicht teil des POST beim Metadaten-Formular
        $form->getElement('SortOrder')->setValue(3);
        $form->getElement('AllowContact')->setChecked(true);

        $person = $form->getLinkModel(146, 'submitter');

        $this->assertNull($person->getId());
        $this->assertEquals(310, $person->getModel()->getId());
        $this->assertEquals(3, $person->getSortOrder());
        $this->assertEquals(1, $person->getAllowEmailContact());
        $this->assertEquals('submitter', $person->getRole());
    }

    public function testPrepareRenderingAsView()
    {
        $form = new Admin_Form_Document_Person();

        $form->getElement('SortOrder')->setValue(2); // wird entfernt auch wenn nicht leer

        $form->prepareRenderingAsView();

        $this->assertNull($form->getElement('SortOrder'));
    }

    public function testSetOrder()
    {
        $form = new Admin_Form_Document_Person();

        $form->setOrder(5);

        $this->assertEquals(5, $form->getOrder());
        $this->assertEquals(6, $form->getElementValue('SortOrder'));
    }
}
