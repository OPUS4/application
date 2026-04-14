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
 * @copyright   Copyright (c) 2026, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\Document;
use Opus\Common\Identifier;

/**
 * Unit tests for authentication controller.
 *
 * TODO complete tests
 *
 * @covers ApiController
 */
class ApiControllerTest extends ControllerTestCase
{
    /** @var string[] */
    protected $additionalResources = ['view', 'mainMenu', 'translation'];

    public function testDoicheckAction()
    {
        $this->dispatch('/api/doicheck?doi=10.1002/anie.202519457');

        $this->assertResponseCode(200);
        $this->assertEquals(
            '{"doi":"10.1002\/anie.202519457","doiExists":false}',
            $this->getResponse()->getBody()
        );
    }

    protected function checkDoiProvider(): array
    {
        return [
            ['10.1002/anie.202519457'],
            ['https://doi.org/10.1002/anie.202519457'],
            ['http://doi.org/10.1002/anie.202519457 '],
        ];
    }

    /**
     * @dataProvider checkDoiProvider
     */
    public function testDoicheckActionExists(string $checkDoi): void
    {
        $doi = '10.1002/anie.202519457';

        $doc = $this->createTestDocument();
        $doc->setServerState(Document::STATE_PUBLISHED);
        $identifier = Identifier::new();
        $identifier->setType('doi');
        $identifier->setValue($doi);
        $doc->addIdentifier($identifier);
        $docId = $doc->store();

        $this->dispatch("/api/doicheck?doi={$doi}");

        $expectedDoi = preg_replace('/\//', '\/', $doi);

        $this->assertResponseCode(200);
        $this->assertEquals(
            "{\"doi\":\"{$expectedDoi}\",\"docId\":{$docId},\"doiExists\":true}",
            $this->getResponse()->getBody()
        );
    }

    public function testDoicheckActionExistsButNotPublished()
    {
        $doi = '10.1002/anie.202519457';

        $doc        = $this->createTestDocument();
        $identifier = Identifier::new();
        $identifier->setType('doi');
        $identifier->setValue($doi);
        $doc->addIdentifier($identifier);
        $docId = $doc->store();

        $this->dispatch("/api/doicheck?doi={$doi}");

        $expectedDoi = preg_replace('/\//', '\/', $doi);

        $this->assertResponseCode(200);
        $this->assertEquals(
            "{\"doi\":\"{$expectedDoi}\",\"doiExists\":true}",
            $this->getResponse()->getBody()
        );
    }

    public function testDoicheckActionNoDoiParameter()
    {
        $this->dispatch('/api/doicheck');

        $this->assertResponseCode(200);
        $this->assertEquals('[]', $this->getResponse()->getBody());
    }
}
