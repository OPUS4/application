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
use Opus\Common\Model\NotFoundException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class Application_Console_Collection_MoveCommandTest extends ControllerTestCase
{
    /** @var string[] */
    protected $additionalResources = ['database'];

    /** @var Application_Console_Collection_CollectionCommandFixture */
    protected $fixture;

    /** @var Application_Console_Collection_MoveCommand */
    protected $command;

    /** @var CommandTester */
    protected $tester;

    public function setUp(): void
    {
        parent::setUp();

        $this->fixture = new Application_Console_Collection_CollectionCommandFixture();
        $this->fixture->setUp();

        $app = new Application();

        $this->command = new Application_Console_Collection_MoveCommand();
        $this->command->setApplication($app);

        $this->tester = new CommandTester($this->command);
    }

    public function tearDown(): void
    {
        $this->fixture->tearDown();
        parent::tearDown();
    }

    public function testMoveDocumentsUsingCollectionId()
    {
        $collections = $this->fixture->getCollections();

        $col1 = $collections[0];
        $col2 = $collections[1];

        $documents = $col1->getDocumentIds();

        $this->assertCount(2, $documents);
        $this->assertCount(0, $col2->getDocumentIds());

        $this->tester->execute([
            '--no-interaction'                       => true,
            '--' . $this->command::OPTION_SRC_COL_ID => $col1->getId(),
            '--' . $this->command::OPTION_DST_COL_ID => $col2->getId(),
        ], [
            'interactive' => false,
        ]);

        $this->assertCount(0, $col1->getDocumentIds());

        $copiedDocuments = $col2->getDocumentIds();
        $this->assertCount(2, $copiedDocuments);

        $this->assertEquals($documents, $copiedDocuments);
    }

    public function testMoveDocumentsUsingRoleNameAndCollectionNumber()
    {
        $role     = $this->fixture->getRole();
        $roleName = $role->getName();

        $collections = $this->fixture->getCollections();

        $col1 = $collections[0];
        $col2 = $collections[1];

        $colNumber1 = $col1->getNumber();
        $colNumber2 = $col2->getNumber();

        $documents = $col1->getDocumentIds();

        $this->assertCount(2, $documents);
        $this->assertCount(0, $col2->getDocumentIds());

        $this->tester->execute([
            '--no-interaction'                           => true,
            '--' . $this->command::OPTION_SRC_ROLE_NAME  => $roleName,
            '--' . $this->command::OPTION_SRC_COL_NUMBER => $colNumber1,
            '--' . $this->command::OPTION_DST_COL_ID     => $col2->getId(),
        ], [
            'interactive' => false,
        ]);

        $this->assertCount(0, $col1->getDocumentIds());

        $copiedDocuments = $col2->getDocumentIds();
        $this->assertCount(2, $copiedDocuments);

        $this->assertEquals($documents, $copiedDocuments);
    }

    public function testMoveDocumentsFromEmptyCollection()
    {
        $collections = $this->fixture->getCollections();

        $col1 = $collections[0];
        $col2 = $collections[1];

        $this->assertCount(2, $col1->getDocumentIds());
        $this->assertCount(0, $col2->getDocumentIds());

        $this->tester->execute([
            '--no-interaction'                       => true,
            '--' . $this->command::OPTION_SRC_COL_ID => $col2->getId(),
            '--' . $this->command::OPTION_DST_COL_ID => $col1->getId(),
        ], [
            'interactive' => false,
        ]);

        $this->assertCount(2, $col1->getDocumentIds());
        $this->assertCount(0, $col2->getDocumentIds());

        $output = $this->tester->getDisplay();

        $colId1 = $col2->getId();

        $this->assertStringContainsString("Collection (ID = ${colId1}) does not contain documents.", $output);
    }

    public function testCollectionIdNotFound()
    {
        $collections = $this->fixture->getCollections();

        $col1 = $collections[0];
        $col2 = $collections[1];

        $this->assertCount(2, $col1->getDocumentIds());
        $this->assertCount(0, $col2->getDocumentIds());

        $this->expectException(NotFoundException::class);

        $this->tester->execute([
            '--no-interaction'                       => true,
            '--' . $this->command::OPTION_SRC_COL_ID => $col1->getId(),
            '--' . $this->command::OPTION_DST_COL_ID => 1000000, // ID should not exist in test data
        ], [
            'interactive' => false,
        ]);
    }

    public function testSourceCollectionUsingUnknownRoleNameNotFound()
    {
        $collections = $this->fixture->getCollections();

        $col1 = $collections[0];
        $col2 = $collections[1];

        $this->assertCount(2, $col1->getDocumentIds());
        $this->assertCount(0, $col2->getDocumentIds());

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('CollectionRole with name \'UnknownRole\' not found.');

        $this->tester->execute([
            '--no-interaction'                           => true,
            '--' . $this->command::OPTION_SRC_ROLE_NAME  => 'UnknownRole',
            '--' . $this->command::OPTION_SRC_COL_NUMBER => 'col1',
            '--' . $this->command::OPTION_DST_COL_ID     => $col2->getId(),
        ], [
            'interactive' => false,
        ]);
    }

    public function testSourceCollectionUsingUnknownColNumberNotFound()
    {
        $role        = $this->fixture->getRole();
        $roleName    = $role->getName();
        $collections = $this->fixture->getCollections();

        $col1 = $collections[0];
        $col2 = $collections[1];

        $this->assertCount(2, $col1->getDocumentIds());
        $this->assertCount(0, $col2->getDocumentIds());

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("No collection found for role '${roleName}' and number 'col3'.");

        $this->tester->execute([
            '--no-interaction'                           => true,
            '--' . $this->command::OPTION_SRC_ROLE_NAME  => $roleName,
            '--' . $this->command::OPTION_SRC_COL_NUMBER => 'col3',
            '--' . $this->command::OPTION_DST_COL_ID     => $col2->getId(),
        ], [
            'interactive' => false,
        ]);
    }

    public function testDestinationCollectionUsingUnknownRoleNameNotFound()
    {
        $collections = $this->fixture->getCollections();

        $col1 = $collections[0];
        $col2 = $collections[1];

        $this->assertCount(2, $col1->getDocumentIds());
        $this->assertCount(0, $col2->getDocumentIds());

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('CollectionRole with name \'UnknownRole\' not found.');

        $this->tester->execute([
            '--no-interaction'                           => true,
            '--' . $this->command::OPTION_SRC_COL_ID     => $col1->getId(),
            '--' . $this->command::OPTION_DST_ROLE_NAME  => 'UnknownRole',
            '--' . $this->command::OPTION_DST_COL_NUMBER => 'col1',
        ], [
            'interactive' => false,
        ]);
    }

    public function testDestinationCollectionUsingUnknownColNumberNotFound()
    {
        $role        = $this->fixture->getRole();
        $roleName    = $role->getName();
        $collections = $this->fixture->getCollections();

        $col1 = $collections[0];
        $col2 = $collections[1];

        $this->assertCount(2, $col1->getDocumentIds());
        $this->assertCount(0, $col2->getDocumentIds());

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("No collection found for role '${roleName}' and number 'col3'.");

        $this->tester->execute([
            '--no-interaction'                           => true,
            '--' . $this->command::OPTION_SRC_COL_ID     => $col1->getId(),
            '--' . $this->command::OPTION_DST_ROLE_NAME  => $roleName,
            '--' . $this->command::OPTION_DST_COL_NUMBER => 'col3',
        ], [
            'interactive' => false,
        ]);
    }

    public function testDocumentsAlreadyPresentInTargetCollection()
    {
        $collections = $this->fixture->getCollections();

        $col1 = $collections[0];
        $col2 = $collections[1];

        $col1->copyDocuments($col2->getId());

        $documents = $col1->getDocumentIds();

        $this->assertCount(2, $documents);
        $this->assertCount(2, $col2->getDocumentIds());

        $this->tester->execute([
            '--no-interaction'                       => true,
            '--' . $this->command::OPTION_SRC_COL_ID => $col1->getId(),
            '--' . $this->command::OPTION_DST_COL_ID => $col2->getId(),
        ], [
            'interactive' => false,
        ]);

        $this->assertCount(0, $col1->getDocumentIds());

        $copiedDocuments = $col2->getDocumentIds();
        $this->assertCount(2, $copiedDocuments);

        $this->assertEquals($documents, $copiedDocuments);
    }

    public function testDefaultDoesNotUpdateServerDateModified()
    {
        $collections = $this->fixture->getCollections();

        $col1 = $collections[0];
        $col2 = $collections[1];

        $documents = $col1->getDocumentIds();

        $this->assertCount(2, $documents);
        $this->assertCount(0, $col2->getDocumentIds());

        $doc1 = Document::get($documents[0]);
        $doc2 = Document::get($documents[1]);

        $lastModified1 = $doc1->getServerDateModified();
        $lastModified2 = $doc2->getServerDateModified();

        sleep(2);

        $this->tester->execute([
            '--no-interaction'                       => true,
            '--' . $this->command::OPTION_SRC_COL_ID => $col1->getId(),
            '--' . $this->command::OPTION_DST_COL_ID => $col2->getId(),
        ], [
            'interactive' => false,
        ]);

        $doc1 = Document::get($documents[0]);
        $doc2 = Document::get($documents[1]);

        $this->assertCount(0, $col1->getDocumentIds());

        $copiedDocuments = $col2->getDocumentIds();
        $this->assertCount(2, $copiedDocuments);

        $this->assertEquals($documents, $copiedDocuments);

        $this->assertEquals(0, $lastModified1->compare($doc1->getServerDateModified()));
        $this->assertEquals(0, $lastModified2->compare($doc2->getServerDateModified()));
    }

    public function testUpdateServerDateModifiedOption()
    {
        $collections = $this->fixture->getCollections();

        $col1 = $collections[0];
        $col2 = $collections[1];

        $documents = $col1->getDocumentIds();

        $this->assertCount(2, $documents);
        $this->assertCount(0, $col2->getDocumentIds());

        $doc1 = Document::get($documents[0]);
        $doc2 = Document::get($documents[1]);

        $lastModified1 = $doc1->getServerDateModified();
        $lastModified2 = $doc2->getServerDateModified();

        sleep(2);

        $this->tester->execute([
            '--no-interaction'                                 => true,
            '--' . $this->command::OPTION_SRC_COL_ID           => $col1->getId(),
            '--' . $this->command::OPTION_DST_COL_ID           => $col2->getId(),
            '--' . $this->command::OPTION_UPDATE_DATE_MODIFIED => true,
        ], [
            'interactive' => false,
        ]);

        $doc1 = Document::get($documents[0]);
        $doc2 = Document::get($documents[1]);

        $this->assertCount(0, $col1->getDocumentIds());

        $copiedDocuments = $col2->getDocumentIds();
        $this->assertCount(2, $copiedDocuments);

        $this->assertEquals($documents, $copiedDocuments);

        $this->assertEquals(-1, $lastModified1->compare($doc1->getServerDateModified()));
        $this->assertEquals(-1, $lastModified2->compare($doc2->getServerDateModified()));
    }

    public function testCancelMove()
    {
        $this->markTestIncomplete('Version of Symfony Console does not support setInputs yet.');
    }
}
