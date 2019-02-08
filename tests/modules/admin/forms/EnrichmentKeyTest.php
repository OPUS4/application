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
 * @package     Admin_Form
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2019, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Unit Tests for Admin_Form_Enrichmentkey.
 *
 * @category Application Unit Test
 * @package Admin_Form
 */
class Admin_Form_EnrichmentKeyTest extends ControllerTestCase {

    public function testConstructForm() {
        $form = new Admin_Form_EnrichmentKey();

        $this->assertEquals(4, count($form->getElements()));

        $this->assertNotNull($form->getElement('Name'));

        $this->assertNotNull($form->getElement('Save'));
        $this->assertNotNull($form->getElement('Cancel'));
        $this->assertNotNull($form->getElement('Id'));
    }

    public function testPopulateFromModel() {
        $form = new Admin_Form_EnrichmentKey();
        $enrichmentKey = new Opus_EnrichmentKey();
        $enrichmentKey->setName('Test');
        $form->populateFromModel($enrichmentKey);
        $this->assertEquals('Test', $form->getElement('Name')->getValue());
    }

    public function testPopulateFromExistingModel() {
        $form = new Admin_Form_EnrichmentKey();
        $enrichment = new Opus_EnrichmentKey('City');
        $form->populateFromModel($enrichment);
        $this->assertEquals('City', $form->getElement('Name')->getValue());
    }

    public function testUpdateModel() {
        $form = new Admin_Form_EnrichmentKey();

        $form->getElement('Name')->setValue('TestEnrichmentKey');
        $enrichmentKey = new Opus_EnrichmentKey();
        $form->updateModel($enrichmentKey);

        $this->assertEquals('TestEnrichmentKey', $enrichmentKey->getName());
    }

    public function testValidationSuccess() {
        $form = new Admin_Form_EnrichmentKey();

        $this->assertTrue($form->isValid(array('Name' => 'City2')));
        $this->assertTrue($form->isValid(array('Name' => 'Test')));
        $this->assertTrue($form->isValid([
            'Name' => str_pad('Long', Opus_EnrichmentKey::getFieldMaxLength('Name'), 'g')
        ]));
        $this->assertTrue($form->isValid(array('Name' => 'small_value59.dot')));
    }

    public function testValidationFailure() {
        $form = new Admin_Form_EnrichmentKey();

        $this->assertFalse($form->isValid(array()));
        $this->assertFalse($form->isValid(array('Name' => 'City')));
        $this->assertFalse($form->isValid(array('Name' => ' ')));
        $this->assertFalse($form->isValid([
            'Name' => str_pad('toolong', Opus_EnrichmentKey::getFieldMaxLength('Name') + 1, 'g')
        ]));
        $this->assertFalse($form->isValid(array('Name' => '5zig')));
        $this->assertFalse($form->isValid(array('Name' => '_Value')));
    }

}
