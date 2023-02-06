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
 * @copyright   Copyright (c) 2022, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

class Oai_Model_RequestTest extends ControllerTestCase
{
    /** @var Oai_Model_Request */
    private $requestObj;

    public function setUp(): void
    {
        parent::setUp();

        $this->requestObj = new Oai_Model_Request();
        $this->requestObj->setPathToMetadataPrefixFiles(APPLICATION_PATH . '/modules/oai/views/scripts/index/prefixes');
    }

    /**
     * @return array[][]
     */
    public function dataFromUntilRange()
    {
        return [
            ['2020-10-01', '2021-10-01', true],
            ['2020-10-01', '2020-10-01', true],
            ['2021-10-01', '2020-10-01', false],
            ['2020/10/01', '2021/10/01', false],
            ['2020/10/01', '2021-10-01', false],
            ['2020-10-01', '2021/10/01', false],
            ['2020-10-01T00:00:00Z', '2021-10-01', false],
            ['2021-02-29', '2021-10-01', false],
            ['2020-02-29', '2021-10-01', true],
        ];
    }

    /**
     * @param string $from
     * @param string $until
     * @param bool   $result
     * @dataProvider dataFromUntilRange
     */
    public function testValidateFromUntilRange($from, $until, $result)
    {
        $request = new Oai_Model_Request();

        $this->assertEquals($result, $request->validateFromUntilRange($from, $until));
    }

    /**
     * @return array[][]
     */
    public function dataCheckDate()
    {
        return [
            ['2022-12-31', true],
            ['1960-01-01', true],
            ['2020-02-29', true],
            ['2021-02-29', false],
            ['abc', false],
            ['1996/03/15', false],
            ['2003', false],
            ['2011-06', false],
        ];
    }

    /**
     * @param string $datestr
     * @param bool   $result
     * @dataProvider dataCheckDate
     */
    public function testCheckDate($datestr, $result)
    {
        $request = new Oai_Model_Request();

        $this->assertEquals($result, $request->checkDate($datestr));
    }

    public function testValidate()
    {
        $request = $this->requestObj;

        $this->assertFalse($request->validate([]));

        $this->assertTrue($request->validate([
            'metadataPrefix' => 'xMetaDissPlus',
            'verb'           => 'ListRecords',
        ]));
    }

    public function testValidateFrom()
    {
        $request = $this->requestObj;

        $this->assertTrue($request->validate([
            'metadataPrefix' => 'xMetaDissPlus',
            'verb'           => 'ListRecords',
            'from'           => '2020-09-21',
        ]));

        $this->assertFalse($request->validate([
            'metadataPrefix' => 'xMetaDissPlus',
            'verb'           => 'ListRecords',
            'from'           => '2020/09/21',
        ]));
    }

    public function testValidateUntil()
    {
        $request = $this->requestObj;

        $this->assertTrue($request->validate([
            'metadataPrefix' => 'xMetaDissPlus',
            'verb'           => 'ListRecords',
            'until'          => '2020-09-21',
        ]));

        $this->assertFalse($request->validate([
            'metadataPrefix' => 'xMetaDissPlus',
            'verb'           => 'ListRecords',
            'until'          => '2020/09/21',
        ]));
    }
}
