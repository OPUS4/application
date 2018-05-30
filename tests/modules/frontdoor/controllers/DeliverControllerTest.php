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
 * @category    Tests
 * @package     Frontdoor
 * @author      Thoralf Klein <thoralf.klein@zib.de>
 * @copyright   Copyright (c) 2012-2018, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Class Frontdoor_DeliverControllerTest.
 *
 * @covers Frontdoor_DeliverController
 */
class Frontdoor_DeliverControllerTest extends ControllerTestCase {

    /**
     * Use this test to trigger class loader on 'Frontdoor_DeliverController'.
     */
    public function testClassLoaded() {
        $this->dispatch('/frontdoor/deliver/index');
        $this->assertController('deliver');
        $this->assertAction('index');

        // Use this assertion to trigger autoloader
        $this->assertTrue(class_exists('Frontdoor_DeliverController'),
                'could not load tested class');
    }

    /**
     * Regression test for OPUSVIER-2455.  (Special chars in HTTP/MIME headers)
     *
     * @depends testClassLoaded
     */
    public function testQuoteAsciiFileName() {
        $testcase = array(
            'my-file.txt' => 'my-file.txt',
            'my file.txt' => 'my file.txt',
            'my,file.txt' => 'my,file.txt',
        );

        foreach ($testcase AS $string => $expected_output) {
            $output = Frontdoor_DeliverController::quoteFileName($string);
            $this->assertEquals($expected_output, $output);
        }
    }

    /**
     * Regression test for OPUSVIER-2455.  (Special chars in HTTP/MIME headers)
     *
     * @depends testClassLoaded
     */
    public function testQuoteUnicodeFileName() {
        $testcase = array(
            'schrödinger-equation.pdf' => '=?UTF-8?B?c2NocsO2ZGluZ2VyLWVxdWF0aW9uLnBkZg==?=',
            'with "weird" chars.pdf'   => '=?UTF-8?B?d2l0aCAid2VpcmQiIGNoYXJzLnBkZg==?=',
        );

        foreach ($testcase AS $string => $expected_output) {
            $output = Frontdoor_DeliverController::quoteFileName($string);
            $this->assertEquals($expected_output, $output);
        }
    }

}
