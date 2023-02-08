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

use Opus\Common\DnbInstitute;

class Application_Form_Element_GrantorTest extends FormElementTestCase
{
    /** @var string */
    protected $additionalResources = 'database';

    public function setUp(): void
    {
        $this->formElementClass       = 'Application_Form_Element_Grantor';
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

        $grantors = DnbInstitute::getGrantors();

        $this->assertEquals(count($grantors), count($element->getMultiOptions()));

        $index = 0;

        foreach ($element->getMultiOptions() as $modelId => $label) {
            $this->assertEquals($grantors[$index]->getId(), $modelId);
            $this->assertEquals($grantors[$index]->getDisplayName(), $label);
            $index++;
        }
    }

    public function testValidator()
    {
        $element = $this->getElement();

        $this->assertTrue($element->getValidator('Int') !== false);
    }

    public function testSetValueForNonGrantorInstitut()
    {
        $element = $this->getElement();

        $optionCount = count($element->getMultiOptions());

        $grantors   = DnbInstitute::getGrantors();
        $publishers = DnbInstitute::getPublishers();

        $nonGrantors = array_diff($publishers, $grantors);

        $this->assertGreaterThan(0, count($nonGrantors));

        $institute = $nonGrantors[0];

        $this->assertEquals(0, $institute->getIsGrantor());

        $element->setValue($institute->getId());

        $this->assertEquals($institute->getId(), $element->getValue());

        // a grantor institution is valid
        $this->assertTrue($element->isValid($grantors[0]->getId()));

        // any other institution should be valid too
        $this->assertTrue($element->isValid($institute->getId()));
        $this->assertEquals($optionCount + 1, count($element->getMultiOptions()));
    }

    public function testSetValueForUnknownId()
    {
        $element = $this->getElement();

        $optionCount = count($element->getMultiOptions());

        // getting unused id for test
        $institutes = DnbInstitute::getAll();

        $instituteIds = array_map(function ($item) {
            return $item->getId();
        }, $institutes);

        $testId = 999;

        while (in_array($testId, $instituteIds)) {
            $testId++;
        }

        $element->setValue(999);

        $this->assertFalse($element->isValid(999));
        $this->assertEquals($optionCount, count($element->getMultiOptions()));
    }

    public function testSetValueInstituteNotAddedTwice()
    {
        $element = $this->getElement();

        $grantors = DnbInstitute::getGrantors();

        $this->assertGreaterThan(0, count($grantors));

        $optionCount = count($element->getMultiOptions());

        $element->setValue($grantors[0]->getId());

        $this->assertEquals($optionCount, count($element->getMultiOptions()));
    }
}
