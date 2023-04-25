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
 * @copyright   Copyright (c) 2020, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\Document;
use Opus\Common\Model\NotFoundException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class Application_Console_Document_DeleteCommandTest extends ControllerTestCase
{
    /** @var string[] */
    protected $additionalResources = ['database'];

    /** @var int[] IDs of test documents */
    protected $documents;

    public function setUp(): void
    {
        parent::setUp();

        $documents = [];

        for ($i = 0; $i < 5; $i++) {
            $doc         = $this->createTestDocument();
            $docId       = $doc->store();
            $documents[] = $docId;
        }

        $this->documents = $documents;
    }

    public function testDeleteSingleDocument()
    {
        $app = new Application();

        $command = new Application_Console_Document_DeleteCommand();
        $command->setApplication($app);

        $tester = new CommandTester($command);

        $docId = $this->documents[2];
        $doc   = Document::get($docId);

        $this->assertEquals('unpublished', $doc->getServerState());

        $tester->execute([
            '--no-interaction'          => true,
            $command::ARGUMENT_START_ID => $docId,
        ], [
            'interactive' => false,
        ]);

        $doc = Document::get($this->documents[0]);
        $this->assertEquals('unpublished', $doc->getServerState());

        $doc = Document::get($this->documents[1]);
        $this->assertEquals('unpublished', $doc->getServerState());

        $doc = Document::get($this->documents[2]);
        $this->assertEquals('deleted', $doc->getServerState());

        $doc = Document::get($this->documents[3]);
        $this->assertEquals('unpublished', $doc->getServerState());

        $doc = Document::get($this->documents[4]);
        $this->assertEquals('unpublished', $doc->getServerState());
    }

    /**
     * TODO implement this once test database can be rebuild automatically (smaller test set, empty like framework)
     */
    public function testDeleteAllDocuments()
    {
        $this->markTestIncomplete('TODO application tests currently require test documents in database');
    }

    public function testDeleteDocumentRange()
    {
        $app = new Application();

        $command = new Application_Console_Document_DeleteCommand();
        $command->setApplication($app);

        $tester = new CommandTester($command);

        $tester->execute([
            '--no-interaction'          => true,
            $command::ARGUMENT_START_ID => $this->documents[1],
            $command::ARGUMENT_END_ID   => $this->documents[3],
        ], [
            'interactive' => false,
        ]);

        $doc = Document::get($this->documents[0]);
        $this->assertEquals('unpublished', $doc->getServerState());

        $doc = Document::get($this->documents[1]);
        $this->assertEquals('deleted', $doc->getServerState());

        $doc = Document::get($this->documents[2]);
        $this->assertEquals('deleted', $doc->getServerState());

        $doc = Document::get($this->documents[3]);
        $this->assertEquals('deleted', $doc->getServerState());

        $doc = Document::get($this->documents[4]);
        $this->assertEquals('unpublished', $doc->getServerState());
    }

    public function testDeleteSingleDocumentPermanently()
    {
        $app = new Application();

        $command = new Application_Console_Document_DeleteCommand();
        $command->setApplication($app);

        $tester = new CommandTester($command);

        $docId = $this->documents[2];
        $doc   = Document::get($docId);

        $this->assertEquals('unpublished', $doc->getServerState());

        $tester->execute([
            '--no-interaction'          => true,
            '--permanent'               => true,
            $command::ARGUMENT_START_ID => $docId,
        ], [
            'interactive' => false,
        ]);

        $doc = Document::get($this->documents[0]);
        $this->assertEquals('unpublished', $doc->getServerState());

        $doc = Document::get($this->documents[1]);
        $this->assertEquals('unpublished', $doc->getServerState());

        $doc = Document::get($this->documents[3]);
        $this->assertEquals('unpublished', $doc->getServerState());

        $doc = Document::get($this->documents[4]);
        $this->assertEquals('unpublished', $doc->getServerState());

        $this->expectException(NotFoundException::class);
        Document::get($this->documents[2]);
    }

    public function testDeleteAllDocumentsPermanently()
    {
        $this->markTestIncomplete('TODO application tests currently require test documents in database');
    }

    public function testDeleteDocumentRangePermanently()
    {
        $app = new Application();

        $command = new Application_Console_Document_DeleteCommand();
        $command->setApplication($app);

        $tester = new CommandTester($command);

        $tester->execute([
            '--no-interaction'          => true,
            '--permanent'               => true,
            $command::ARGUMENT_START_ID => $this->documents[1],
            $command::ARGUMENT_END_ID   => $this->documents[3],
        ], [
            'interactive' => false,
        ]);

        $doc = Document::get($this->documents[0]);
        $this->assertEquals('unpublished', $doc->getServerState());

        $doc = Document::get($this->documents[4]);
        $this->assertEquals('unpublished', $doc->getServerState());

        try {
            Document::get($this->documents[1]);
            $this->fail('Document should have been deleted permanently.');
        } catch (NotFoundException $e) {
        }

        try {
            Document::get($this->documents[2]);
            $this->fail('Document should have been deleted permanently.');
        } catch (NotFoundException $e) {
        }

        try {
            Document::get($this->documents[3]);
            $this->fail('Document should have been deleted permanently.');
        } catch (NotFoundException $e) {
        }
    }

    public function testCancelDeletion()
    {
        $this->markTestIncomplete('Version of Symfony Console does not support setInputs yet.');
    }

    public function testDeleteWithoutInteraction()
    {
        $this->markTestIncomplete('Currently all tests are without interaction.');
    }
}
