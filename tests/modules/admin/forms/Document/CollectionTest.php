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

use Opus\Common\Collection;

/**
 * Tests fuer Admin_Form_Document_Collection Unterformular Klasse.
 */
class Admin_Form_Document_CollectionTest extends ControllerTestCase
{
    /** @var string[] */
    protected $additionalResources = ['database', 'translation'];

    public function createForm()
    {
        $form = new Admin_Form_Document_Collection();

        $this->assertCount(3, $form->getElements());

        $this->assertNotNull($form->getElement('Id'));
        $this->assertNotNull($form->getElement('Edit'));
        $this->assertNotNull($form->getElement('Remove'));
    }

    public function testPopulateFromModel()
    {
        $form = new Admin_Form_Document_Collection();

        $collection = Collection::get(499);

        $form->populateFromModel($collection);

        $this->assertEquals($collection->getDisplayName(), $form->getLegend());
        $this->assertEquals($collection->getId(), $form->getElement('Id')->getValue());
    }

    public function testPopulateFromModelWithRootCollectionWithoutName()
    {
        $this->useEnglish();

        $form = new Admin_Form_Document_Collection();

        $collection = Collection::get(2); // Root-Collection DDC-Klassifikation

        $form->populateFromModel($collection);

        $this->assertEquals(2, $form->getElement('Id')->getValue());
        $this->assertNotEmpty($form->getLegend());
        $this->assertNotEmpty($form->getElement('Edit')->getLabel());
        $this->assertEquals('Dewey Decimal Classification', $form->getElement('Edit')->getLabel());
    }

    public function testProcessPostRemove()
    {
        $form = new Admin_Form_Document_Collection();

        $post = ['Id' => '499', 'Remove' => 'Remove Collection'];

        $this->assertEquals('remove', $form->processPost($post, null));
    }

    public function testProcessPostEdit()
    {
        $form = new Admin_Form_Document_Collection();

        $post = ['Id' => '499', 'Edit' => 'Edit Collection'];

        $this->assertEquals('edit', $form->processPost($post, null));
    }

    public function testProcessPostEmpty()
    {
        $form = new Admin_Form_Document_Collection();

        $this->assertNull($form->processPost([], null));
    }

    public function testGetModel()
    {
        $form = new Admin_Form_Document_Collection();

        $form->getElement('Id')->setValue(499);

        $collection = $form->getModel();

        $this->assertEquals(499, $collection->getId());
    }

    public function testPopulateFromPost()
    {
        $form = new Admin_Form_Document_Collection();

        $post = ['Id' => '499'];

        $form->populateFromPost($post);

        $collection = Collection::get(499);

        $this->assertEquals($collection->getDisplayName(), $form->getLegend());
        $this->assertEquals($collection->getId(), $form->getElement('Id')->getValue());
    }
}
