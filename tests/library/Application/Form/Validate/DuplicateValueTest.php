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
 * @copyright   Copyright (c) 2012, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Test validation of same element values across multiple subforms.
 */
class Application_Form_Validate_DuplicateValueTest extends TestCase
{
    public function testConstruct()
    {
        $validator = new Application_Form_Validate_DuplicateValue(['deu', 'eng'], 1, 'testmessage');

        $this->assertEquals(['deu', 'eng'], $validator->getValues());
        $this->assertEquals(1, $validator->getPosition());
        $messageTemplates = $validator->getMessageTemplates();
        $this->assertInternalType('array', $messageTemplates);
        $this->assertArrayHasKey('notValid', $messageTemplates);
        $this->assertEquals('testmessage', $messageTemplates['notValid']);
    }

    public function testIsSelectionValidTrue()
    {
        $values = ['deu', 'eng'];

        $validator = new Application_Form_Validate_DuplicateValue($values, 0); // erstes Unterformular

        $this->assertTrue($validator->isValid('deu'));
    }

    public function testIsSelectionValidTrueForFirstOccurence()
    {
        $values = ['deu', 'deu'];

        $validator = new Application_Form_Validate_DuplicateValue($values, 0); // erstes Unterformular

        $this->assertTrue($validator->isValid('deu'));
    }

    public function testIsSelectionValidFalse()
    {
        $values = ['deu', 'deu'];

        $validator = new Application_Form_Validate_DuplicateValue($values, 1); // zweites Unterformular

        $this->assertFalse($validator->isValid('deu'));
    }

    public function testIsSelectionValidFalseForSecondOccurence()
    {
        $values = ['deu', 'deu', 'deu'];

        $validator = new Application_Form_Validate_DuplicateValue($values, 2); // drittes Unterformular

        $this->assertFalse($validator->isValid('deu'));
    }
}
