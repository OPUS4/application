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
 * @package     Form_Element
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2019, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */
class Application_Form_Element_FileLinkTest extends FormElementTestCase
{

    protected $additionalResources = 'database';

    public function setUp()
    {
        $this->_formElementClass = 'Application_Form_Element_FileLink';
        $this->_expectedDecoratorCount = 8;
        $this->_expectedDecorators = ['ViewHelper', 'Placeholder', 'Description', 'ElementHint', 'Errors',
            'ElementHtmlTag', 'LabelNotEmpty', 'dataWrapper'];
        $this->_staticViewHelper = 'fileLink';
        parent::setUp();
    }

    public function testDisableLabelFor()
    {
        $element = $this->getElement();

        $this->assertTrue($element->getDecorator('LabelNotEmpty')->getOption('disableFor'));
    }

    public function testSetValueWithFile()
    {
        $file = new Opus_File(130);

        $element = $this->getElement();

        $element->setValue($file);

        $this->assertEquals($file, $element->getValue());
    }

    public function testSetValueWithFileId()
    {
        $element = $this->getElement();

        $element->setValue(130);

        $file = $element->getValue();

        $this->assertInstanceOf('Opus_File', $file);
        $this->assertEquals(130, $file->getId());
    }

    public function testSetValueWithMissingFile()
    {
        $file = new Opus_File(123);

        $element = $this->getElement();

        $element->setValue($file);

        $messages = $element->getErrorMessages();

        $this->assertEquals(1, count($messages));
        $this->assertEquals('admin_filemanager_file_does_not_exist', $messages[0]);
    }

    /**
     * @expectedException Application_Exception
     * @expectedExceptionMessage File with ID = 5555 not found.
     */
    public function testSetValueWithUnknownFileId()
    {
        $element = $this->getElement();

        $element->setValue(5555);
    }

    /**
     * @expectedException Application_Exception
     * @expectedExceptionMessage Value must not be null.
     */
    public function testSetValueNull()
    {
        $element = $this->getElement();

        $element->setValue(null);
    }

    public function testIsValid()
    {
        $element = $this->getElement();

        $this->assertTrue($element->isValid(123)); // File 123 exists in database, but file is missing (document 122)
        $this->assertTrue($element->isValid(116)); // File 116 exists (document 91)
    }

    /**
     * @expectedException Application_Exception
     * @expectedExceptionMessage File with ID = 5555 not found.
     */
    public function testIsValidUnknownId()
    {
        $element = $this->getElement();

        $this->assertFalse($element->isValid(5555)); // File 5555 does not exist
    }
}
