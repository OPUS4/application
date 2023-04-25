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

class Application_View_Helper_FormComboboxTest extends ControllerTestCase
{
    /** @var Application_View_Helper_FormCombobox */
    private $helper;

    public function setUp(): void
    {
        parent::setUp();

        $this->helper = new Application_View_Helper_FormCombobox();
    }

    public function testFormComboboxWithoutValues()
    {
        $output = $this->helper->formCombobox('email');

        $this->assertEquals(
            "<div class=\"ui-widget\">\n    <select name=\"email\" class=\"combobox\">\n</select>\n</div>\n",
            $output
        );
    }

    public function testFormComboboxWithOptions()
    {
        $output = $this->helper->formCombobox(
            'city',
            null,
            null,
            ['Berlin' => 'Berlin', 'Hamburg' => 'Hamburg']
        );

        $this->assertContains('<option value="Berlin">Berlin</option>', $output);
        $this->assertContains('<option value="Hamburg">Hamburg</option>', $output);
    }

    public function testFormComboboxWithValue()
    {
        $output = $this->helper->formCombobox(
            'city',
            'Bremen',
            null,
            ['Berlin' => 'Berlin', 'Hamburg' => 'Hamburg']
        );

        $this->assertContains('<option value="Bremen">Bremen</option>', $output);
        $this->assertContains('<option value="Berlin">Berlin</option>', $output);
        $this->assertContains('<option value="Hamburg">Hamburg</option>', $output);
    }

    public function testFormComboboxWithValueMatchingOption()
    {
        $output = $this->helper->formCombobox(
            'city',
            'Hamburg',
            null,
            ['Berlin' => 'Berlin', 'Hamburg' => 'Hamburg']
        );

        $this->assertEquals(1, substr_count($output, '<option value="Hamburg"'));
        $this->assertContains('<option value="Berlin">Berlin</option>', $output);
        $this->assertContains('<option value="Hamburg" selected="selected">Hamburg</option>', $output);
    }
}
