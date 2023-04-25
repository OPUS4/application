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

use Opus\Common\CollectionInterface;
use Opus\Common\Document;

class Admin_Form_Document_CollectionsTest extends ControllerTestCase
{
    /** @var string[] */
    protected $additionalResources = ['database', 'view'];

    public function testConstructForm()
    {
        $form = new Admin_Form_Document_Collections();

        $this->assertEquals(1, count($form->getElements()));
        $this->assertNotNull($form->getElement('Add'));

        $this->assertNotNull($form->getLegend());
    }

    /**
     * Just checking that form names can contain dashes.
     *
     * Unfortunately when the form is rendered as HTML the dashes are removed.
     */
    public function testAddGetSubformWithDashInName()
    {
        $form = new Admin_Form_Document_Collections();

        $subform = new Zend_Form_SubForm();

        $form->addSubForm($subform, 'ddc-2');

        $subform2 = $form->getSubForm('ddc-2');

        $this->assertNotNull($subform2);
    }

    public function testPopulateFromPost()
    {
        $form = new Admin_Form_Document_Collections();

        $form->constructFromPost([
            'ddc2' => [
                'collection0' => ['Id' => 40],
                'collection1' => ['Id' => 68],
            ],
        ]);

        $subforms = $form->getSubForms();

        $this->assertInternalType('array', $subforms);
        $this->assertArrayHasKey('ddc2', $subforms);

        $ddcform = $subforms['ddc2'];

        $this->assertInstanceOf('Admin_Form_Document_Section', $ddcform);

        $colforms = $ddcform->getSubforms();

        $this->assertCount(2, $colforms);
    }

    /**
     * Just checking that Zend_Form renders form name without '-'.
     */
    public function testFormNameRendering()
    {
        $form = new Zend_Form();
        $form->setName('ddc-2');

        $html = $form->render();

        $this->assertContains('id="ddc2"', $html);
    }

    public function testGetGroupedCollections()
    {
        $document = Document::get(146);

        $form = new Admin_Form_Document_Collections();

        $grouped = $form->getGroupedCollections($document);

        $this->assertCount(8, $grouped);
        $this->assertArrayHasKey('ddc', $grouped);

        $ddc = $grouped['ddc'];

        $this->assertCount(4, $ddc);
        $this->assertInstanceOf(CollectionInterface::class, $ddc[0]);
    }
}
