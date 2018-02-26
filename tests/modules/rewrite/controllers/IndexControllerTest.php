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
 * @package     Rewrite
 * @author      Sascha Szott <szott@zib.de>
 * @copyright   Copyright (c) 2008-2018, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Class Rewrite_IndexControllerTest.
 *
 * @covers Rewrite_IndexController
 */
class Rewrite_IndexControllerTest extends ControllerTestCase {

    public function testIdActionWithMissingArgs() {
        $this->dispatch('/rewrite/index/id');
        $this->assertRedirect();
    }

    public function testIdActionWithMissingType() {
        $this->dispatch('/rewrite/index/id/value/rewritetest-foo');
        $this->assertRedirect();
    }

    public function testIdActionWithMissingValue() {
        $this->dispatch('/rewrite/index/id/type/opus3-id');
        $this->assertRedirect();
    }

    public function testIdActionWithUnknownType() {
        $this->dispatch('/rewrite/index/id/type/unknowntype/value/foo');
        $this->assertRedirect();
    }

    public function testIdActionWithUnknownId() {
        $this->dispatch('/rewrite/index/id/type/opus3-id/value/rewritetest-bar');
        $this->assertRedirect();
    }

    public function testIdActionWithNonUniqueId() {
        $this->dispatch('/rewrite/index/id/type/opus3-id/value/rewritetest-foo');
        $this->assertRedirect();
    }

    public function testIdAction() {
        $this->dispatch('/rewrite/index/id/type/opus3-id/value/rewritetest-baz');
        $this->assertRedirect('/frontdoor/index/index/docId/92');
    }

    public function testOpus3fileActionWithMissingArgs() {
        $this->dispatch('/rewrite/index/opus3file');
        $this->assertRedirect();
    }

    public function testOpus3fileActionWithMissingOpus3Id() {
        $this->dispatch('/rewrite/index/opus3file/filename/foo.bar');
        $this->assertRedirect();
    }

    public function testOpus3fileActionWithMissingFilename() {
        $this->dispatch('/rewrite/index/opus3file/opus3id/rewritetest-foo');
        $this->assertRedirect();
    }

    public function testOpus3fileActionWithUnknownOpus3Id() {
        $this->dispatch('/rewrite/index/opus3file/opus3id/rewritetest-bar/filename/foo.bar');
        $this->assertRedirect();
    }

    public function testOpus3fileActionWithNonUniqueOpus3Id() {
        $this->dispatch('/rewrite/index/opus3file/opus3id/rewritetest-foo/filename/foo.bar');
        $this->assertRedirect();
    }

    public function testOpus3fileAction() {
        $this->dispatch('/rewrite/index/opus3file/opus3id/rewritetest-baz/filename/test.xhtml');
        $this->assertRedirect('/92/test.xhtml', 301);
    }

    public function assertRedirect($path = '/home', $httpCode = 302) {
        $response = $this->getResponse();
        $headers = $response->getHeaders();

        $this->assertEquals('Location', $headers[0]['name']);
        $this->assertStringEndsWith($path, $headers[0]['value']);
        $this->assertEquals($httpCode, $response->getHttpResponseCode());
    }
}

