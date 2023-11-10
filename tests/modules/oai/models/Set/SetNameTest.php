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
 * @copyright   Copyright (c) 2023, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

class Oai_Model_Set_SetNameTest extends ControllerTestCase
{
    public function testConstructorNoSet()
    {
        $this->expectException(Oai_Model_Exception::class);
        $setName = new Oai_Model_Set_SetName('');
    }

    public function testConstructorNoSetButSubset()
    {
        $this->expectException(Oai_Model_Exception::class);
        $setName = new Oai_Model_Set_SetName(':Article');
    }

    public function testConstructorNoSetAndNoSubset()
    {
        $this->expectException(Oai_Model_Exception::class);
        $setName = new Oai_Model_Set_SetName(':');
    }

    public function testGetSetName()
    {
        $setName = new Oai_Model_Set_SetName('doc-type:Article');
        $this->assertEquals('doc-type', $setName->getSetName());
    }

    public function testGetSubsetName()
    {
        $setName = new Oai_Model_Set_SetName('doc-type:Article');
        $this->assertEquals('Article', $setName->getSubsetName());
    }

    public function testGetFullSetName()
    {
        $setName = new Oai_Model_Set_SetName('doc-type:Article');
        $this->assertEquals('doc-type:Article', $setName->getFullSetName());
    }

    public function testGetSetPartsCount()
    {
        $setName = new Oai_Model_Set_SetName('doc-type:Article:extra');
        $this->assertEquals(3, $setName->getSetPartsCount());
    }

    public function testIsValidSetName()
    {
        $this->assertTrue(Oai_Model_Set_SetName::isValidSetName('doc-type'));
        $this->assertTrue(Oai_Model_Set_SetName::isValidSetName('doc-type:Article'));
        $this->assertTrue(Oai_Model_Set_SetName::isValidSetName('doc-type:Article:extra'));
        $this->assertFalse(Oai_Model_Set_SetName::isValidSetName('doc-$type:Article'));
        $this->assertFalse(Oai_Model_Set_SetName::isValidSetName('doc-type:$Article'));
        $this->assertFalse(Oai_Model_Set_SetName::isValidSetName(':Article'));
        $this->assertFalse(Oai_Model_Set_SetName::isValidSetName('doc-type:'));
    }

    public function testIsValidSubsetName()
    {
        $this->assertTrue(Oai_Model_Set_SetName::isValidSubsetName('doc-type'));
        $this->assertFalse(Oai_Model_Set_SetName::isValidSubsetName('doc-$type'));
        $this->assertFalse(Oai_Model_Set_SetName::isValidSubsetName(''));
        $this->assertFalse(Oai_Model_Set_SetName::isValidSubsetName(' '));
    }
}
