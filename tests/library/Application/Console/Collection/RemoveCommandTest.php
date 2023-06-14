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

use Opus\Common\Document;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class Application_Console_Collection_RemoveCommandTest extends ControllerTestCase
{
    /** @var string[] */
    protected $additionalResources = ['database'];

    /** @var Application_Console_Collection_CollectionCommandFixture */
    protected $fixture;

    /** @var Application_Console_Collection_RemoveCommand */
    protected $command;

    /** @var CommandTester */
    protected $tester;

    public function setUp(): void
    {
        parent::setUp();

        $this->fixture = new Application_Console_Collection_CollectionCommandFixture();
        $this->fixture->setUp();

        $app = new Application();

        $this->command = new Application_Console_Collection_RemoveCommand();
        $this->command->setApplication($app);

        $this->tester = new CommandTester($this->command);
    }

    public function tearDown(): void
    {
        $this->fixture->tearDown();
        parent::tearDown();
    }

    public function testRemoveAllDocumentsUsingColId()
    {
        $collections = $this->fixture->getCollections();

        $col1 = $collections[0];

        $this->assertCount(2, $col1->getDocumentIds());

        $this->tester->execute([
            '--no-interaction'                   => true,
            '--' . $this->command::OPTION_COL_ID => $col1->getId(),
        ], [
            'interactive' => false,
        ]);

        $this->assertCount(0, $col1->getDocumentIds());
    }

    public function testRemoveAllDocumentsUsingRoleNameAndColNumber()
    {
        $role        = $this->fixture->getRole();
        $collections = $this->fixture->getCollections();

        $col1 = $collections[0];

        $this->assertCount(2, $col1->getDocumentIds());

        $this->tester->execute([
            '--no-interaction'                       => true,
            '--' . $this->command::OPTION_ROLE_NAME  => $role->getName(),
            '--' . $this->command::OPTION_COL_NUMBER => $col1->getNumber(),
        ], [
            'interactive' => false,
        ]);

        $this->assertCount(0, $col1->getDocumentIds());
    }

    public function testRemoveAllDocumentsUsingRoleOaiNameAndColNumber()
    {
        $role        = $this->fixture->getRole();
        $collections = $this->fixture->getCollections();

        $col1 = $collections[0];

        $this->assertCount(2, $col1->getDocumentIds());

        $this->tester->execute([
            '--no-interaction'                          => true,
            '--' . $this->command::OPTION_ROLE_OAI_NAME => $role->getOaiName(),
            '--' . $this->command::OPTION_COL_NUMBER    => $col1->getNumber(),
        ], [
            'interactive' => false,
        ]);

        $this->assertCount(0, $col1->getDocumentIds());
    }

    public function testRemoveDocumentsUsingFilterCollection()
    {
        $collections = $this->fixture->getCollections();

        $col1 = $collections[0];
        $col2 = $collections[1];

        $documentIds = $col1->getDocumentIds();

        $this->assertCount(2, $documentIds);
        $this->assertCount(0, $col2->getDocumentIds());

        $doc1 = Document::get($documentIds[0]);
        $doc2 = Document::get($documentIds[1]);

        $doc1->addCollection($col2);
        $doc1->store();

        $this->tester->execute([
            '--no-interaction'                          => true,
            '--' . $this->command::OPTION_COL_ID        => $col1->getId(),
            '--' . $this->command::OPTION_FILTER_COL_ID => $col2->getId(),
        ], [
            'interactive' => false,
        ]);

        $documentIds = $col1->getDocumentIds();
        $this->assertCount(1, $col1->getDocumentIds());
        $this->assertContains($doc2->getId(), $documentIds); // not removed, because not in 2nd collection (filter)
    }

    public function testRemoveDocumentsUsingFilterCollectionRoleNameAndColNumber()
    {
        $role        = $this->fixture->getRole();
        $collections = $this->fixture->getCollections();

        $col1 = $collections[0];
        $col2 = $collections[1];

        $documentIds = $col1->getDocumentIds();

        $this->assertCount(2, $documentIds);
        $this->assertCount(0, $col2->getDocumentIds());

        $doc1 = Document::get($documentIds[0]);
        $doc2 = Document::get($documentIds[1]);

        $doc1->addCollection($col2);
        $doc1->store();

        $this->tester->execute([
            '--no-interaction'                              => true,
            '--' . $this->command::OPTION_COL_ID            => $col1->getId(),
            '--' . $this->command::OPTION_FILTER_ROLE_NAME  => $role->getName(),
            '--' . $this->command::OPTION_FILTER_COL_NUMBER => $col2->getNumber(),
        ], [
            'interactive' => false,
        ]);

        $documentIds = $col1->getDocumentIds();
        $this->assertCount(1, $col1->getDocumentIds());
        $this->assertContains($doc2->getId(), $documentIds); // not removed, because not in 2nd collection (filter)
    }

    public function testRemoveDocumentsUsingFilterCollectionRoleOaiNameAndColNumber()
    {
        $role        = $this->fixture->getRole();
        $collections = $this->fixture->getCollections();

        $col1 = $collections[0];
        $col2 = $collections[1];

        $documentIds = $col1->getDocumentIds();

        $this->assertCount(2, $documentIds);
        $this->assertCount(0, $col2->getDocumentIds());

        $doc1 = Document::get($documentIds[0]);
        $doc2 = Document::get($documentIds[1]);

        $doc1->addCollection($col2);
        $doc1->store();

        $this->tester->execute([
            '--no-interaction'                                 => true,
            '--' . $this->command::OPTION_COL_ID               => $col1->getId(),
            '--' . $this->command::OPTION_FILTER_ROLE_OAI_NAME => $role->getOaiName(),
            '--' . $this->command::OPTION_FILTER_COL_NUMBER    => $col2->getNumber(),
        ], [
            'interactive' => false,
        ]);

        $documentIds = $col1->getDocumentIds();
        $this->assertCount(1, $col1->getDocumentIds());
        $this->assertContains($doc2->getId(), $documentIds); // not removed, because not in 2nd collection (filter)
    }

    public function testDefaultDoesNotUpdateServerDateModified()
    {
        $collections = $this->fixture->getCollections();

        $col1 = $collections[0];

        $documentIds = $col1->getDocumentIds();

        $doc1 = Document::get($documentIds[0]);
        $doc2 = Document::get($documentIds[1]);

        $lastModified1 = $doc1->getServerDateModified();
        $lastModified2 = $doc2->getServerDateModified();

        $this->assertCount(2, $documentIds);

        sleep(2);

        $this->tester->execute([
            '--no-interaction'                   => true,
            '--' . $this->command::OPTION_COL_ID => $col1->getId(),
        ], [
            'interactive' => false,
        ]);

        $doc1 = Document::get($documentIds[0]);
        $doc2 = Document::get($documentIds[1]);

        $this->assertCount(0, $col1->getDocumentIds());
        $this->assertEquals(0, $lastModified1->compare($doc1->getServerDateModified()));
        $this->assertEquals(0, $lastModified2->compare($doc2->getServerDateModified()));
    }

    public function testOptionUpdateServerDateModified()
    {
        $collections = $this->fixture->getCollections();

        $col1 = $collections[0];

        $documentIds = $col1->getDocumentIds();

        $doc1 = Document::get($documentIds[0]);
        $doc2 = Document::get($documentIds[1]);

        $lastModified1 = $doc1->getServerDateModified();
        $lastModified2 = $doc2->getServerDateModified();

        $this->assertCount(2, $documentIds);

        sleep(2);

        $this->tester->execute([
            '--no-interaction'                                 => true,
            '--' . $this->command::OPTION_COL_ID               => $col1->getId(),
            '--' . $this->command::OPTION_UPDATE_DATE_MODIFIED => true,
        ], [
            'interactive' => false,
        ]);

        $doc1 = Document::get($documentIds[0]);
        $doc2 = Document::get($documentIds[1]);

        $this->assertCount(0, $col1->getDocumentIds());
        $this->assertEquals(-1, $lastModified1->compare($doc1->getServerDateModified()));
        $this->assertEquals(-1, $lastModified2->compare($doc2->getServerDateModified()));
    }
}
