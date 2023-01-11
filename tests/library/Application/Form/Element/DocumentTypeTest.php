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

class Application_Form_Element_DocumentTypeTest extends FormElementTestCase
{
    /** @var string[] */
    protected $additionalResources = ['view', 'translation'];

    public function setUp(): void
    {
        $this->formElementClass       = 'Application_Form_Element_DocumentType';
        $this->expectedDecorators     = [
            'ViewHelper',
            'Errors',
            'Description',
            'ElementHtmlTag',
            'LabelNotEmpty',
            'dataWrapper',
            'ElementHint',
        ];
        $this->expectedDecoratorCount = count($this->expectedDecorators);
        $this->staticViewHelper       = 'viewFormSelect';
        parent::setUp();
    }

    public function testOptions()
    {
        $element = $this->getElement();

        $types = Zend_Controller_Action_HelperBroker::getStaticHelper('DocumentTypes')->getDocumentTypes();

        $this->assertEquals(count($types), count($element->getMultiOptions()));

        foreach ($element->getMultiOptions() as $typeId => $label) {
            $this->assertArrayHasKey($typeId, $types);
        }
    }

    public function testValidation()
    {
        $element = $this->getElement();

        $this->assertFalse($element->isValid('unknowntype'));
        $this->assertTrue($element->isValid('all'));
    }

    public function testValueAddedToOptions()
    {
        $element = $this->getElement();

        $type = 'testtype';

        $this->assertNull($element->getMultiOption($type));

        $element->setValue($type);

        $option = $element->getMultiOption($type);

        $this->assertNotNull($option);
        $this->assertEquals($type, $option);
    }
}
