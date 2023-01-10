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
 * @copyright   Copyright (c) 2017, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\DocumentInterface;

class Admin_Form_Person_DocumentsTest extends ControllerTestCase
{
    public function testConstruct()
    {
        $form = new Admin_Form_Person_Documents();

        $elements = $form->getElements();

        $this->assertCount(1, $elements);
        $this->assertArrayHasKey('Documents', $elements);
    }

    public function testPopulate()
    {
        $form = new Admin_Form_Person_Documents();

        $form->setDocuments([1, 2]);

        $data = [
            'Documents' => [2],
        ];

        $form->populate($data);

        $selected = $form->getSelectedDocuments();

        $this->assertNotNull($selected);
        $this->assertInternalType('array', $selected);
        $this->assertCount(1, $selected);
        $this->assertEquals(2, $selected[0]);
    }

    public function testSetDocuments()
    {
        $form = new Admin_Form_Person_Documents();

        $form->setDocuments([1, 2, 5]);

        $element = $form->getElement('Documents');

        $options = $element->getMultiOptions();

        $this->assertCount(3, $options);

        $this->assertArrayHasKey(1, $options);
        $this->assertArrayHasKey(2, $options);
        $this->assertArrayHasKey(5, $options);

        foreach ($options as $docId => $doc) {
            $this->assertInstanceOf(DocumentInterface::class, $doc);
            $this->assertEquals($docId, $doc->getId());
        }
    }

    public function testSetDocumentsAndPerson()
    {
        $form = new Admin_Form_Person_Documents();

        $person = [
            'last_name'  => 'Tester',
            'first_name' => 'John',
        ];

        $form->setDocuments([2, 6], $person);

        $element = $form->getElement('Documents');

        $this->assertEquals(['LastName' => 'Tester', 'FirstName' => 'John'], $element->getAttrib('person'));
    }
}
