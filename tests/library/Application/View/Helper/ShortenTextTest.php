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

class Application_View_Helper_ShortenTextTest extends ControllerTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->makeConfigurationModifiable();
    }

    public function testShortenText()
    {
        $helper = new Application_View_Helper_ShortenText();

        $this->adjustConfiguration([
            'frontdoor' => ['numOfShortAbstractChars' => '10'],
        ]);

        $this->assertEquals('short text', $helper->shortenText('short text'));
        $this->assertEquals('shortened', $helper->shortenText('shortened text'));

        $this->assertEquals('The text', $helper->shortenText('The text does not get cut in the middle of a word.'));

        $helper->setMaxLength(36);
        $this->assertEquals(
            'The text does not get cut in the',
            $helper->shortenText('The text does not get cut in the middle of a word.')
        );

        $helper->setMaxLength(40);
        $this->assertEquals(
            'The text does not get cut in the middle',
            $helper->shortenText('The text does not get cut in the middle of a word.')
        );
    }

    public function testGetMaxLength()
    {
        $helper = new Application_View_Helper_ShortenText();

        $this->adjustConfiguration([
            'frontdoor' => ['numOfShortAbstractChars' => '10'],
        ]);

        $this->assertEquals(10, $helper->getMaxLength());

        $helper->setMaxLength(null);

        $this->adjustConfiguration([
            'frontdoor' => ['numOfShortAbstractChars' => 'bla'],
        ]);

        $this->assertEquals(0, $helper->getMaxLength());
    }

    public function testSetMaxLength()
    {
        $helper = new Application_View_Helper_ShortenText();

        $this->adjustConfiguration([
            'frontdoor' => ['numOfShortAbstractChars' => '10'],
        ]);

        $this->assertEquals(10, $helper->getMaxLength());

        $helper->setMaxLength(20);

        $this->assertEquals(20, $helper->getMaxLength());

        $helper->setMaxLength(1.2);

        $this->assertEquals(0, $helper->getMaxLength());

        $helper->setMaxLength('bla');

        $this->assertEquals(0, $helper->getMaxLength());
    }
}
