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

class Admin_Form_Document_FilesTest extends ControllerTestCase
{
    /** @var string[] */
    protected $additionalResources = ['database', 'translation'];

    public function testConstructForm()
    {
        $form = new Admin_Form_Document_Files();

        $this->assertNotNull($form->getLegend());
        $this->assertEquals(0, count($form->getElements()));
        $this->assertEquals(1, count($form->getSubForms()));
        $this->assertNotNull($form->getSubForm('Header'));

        $this->assertEquals(5, count($form->getDecorators()));
        $this->assertNotNull($form->getDecorator('FormElements'));
        $this->assertNotNull($form->getDecorator('table'));
        $this->assertNotNull($form->getDecorator('fieldsWrapper'));
        $this->assertNotNull($form->getDecorator('Fieldset'));
        $this->assertNotNull($form->getDecorator('divWrapper'));
    }

    public function testPopulateFromModel()
    {
        $form = new Admin_Form_Document_Files();

        $document = Document::get(84);

        $this->assertEquals(1, count($form->getSubForms()));

        $form->populateFromModel($document);

        $this->assertEquals(3, count($form->getSubForms()));

        $this->assertNotNull($form->getSubForm('File0'));
        $this->assertInstanceOf('Admin_Form_Document_File', $form->getSubForm('File0'));
        $this->assertNotNull($form->getSubForm('File0')->getModel());

        $this->assertNotNull($form->getSubForm('File1'));
        $this->assertInstanceOf('Admin_Form_Document_File', $form->getSubForm('File1'));
        $this->assertNotNull($form->getSubForm('File1')->getModel());
    }

    public function testColumnLabelTranslations()
    {
        $form = new Admin_Form_Document_Files();

        $property = new ReflectionProperty('Admin_Form_Document_Files', 'header');
        $property->setAccessible(true);

        $header = $property->getValue($form);

        $translate = Application_Translate::getInstance();

        foreach ($header as $column) {
            if (isset($column['label']) && $column['label'] !== null) {
                $label = $column['label'];
                $this->assertTrue($translate->isTranslated($label), "Label '$label' is not translated.");
            }
        }
    }
}
