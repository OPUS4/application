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

/**
 * This test class is used to document weird behavior of PHP functions
 * that might be important to consider during development.
 */
class PhpTest extends ControllerTestCase
{
    /**
     * Test strange behavior of 'in_array' function.
     */
    public function testInArray()
    {
        $array = [
            'egg'     => true,
            'cheese'  => false,
            'hair'    => 765,
            'goblins' => null,
            'ogres'   => 'no ogres allowed in this array',
        ];

        // correct
        $this->assertTrue(in_array(null, $array));
        $this->assertTrue(in_array(false, $array));
        $this->assertTrue(in_array(765, $array));

        // weird, apparently "correct", but not as expected
        $this->assertTrue(in_array(763, $array));
        $this->assertTrue(in_array('egg', $array));
        $this->assertTrue(in_array('hhh', $array));
        $this->assertTrue(in_array([], $array));

        // using strict, it works as expected
        $this->assertTrue(in_array(null, $array, true));
        $this->assertTrue(in_array(false, $array, true));
        $this->assertTrue(in_array(765, $array, true));
        $this->assertFalse(in_array(763, $array, true));
        $this->assertFalse(in_array('egg', $array, true));
        $this->assertFalse(in_array('hhh', $array, true));
        $this->assertFalse(in_array([], $array, true));
    }
}
