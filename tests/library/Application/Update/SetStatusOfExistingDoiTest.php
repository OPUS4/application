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
 * @copyright   Copyright (c) 2018, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\Date;
use Opus\Common\Document;
use Opus\Common\Identifier;
use Opus\Common\Model\ModelException;
use Opus\Db\Documents;
use Opus\Db\TableGateway;

class Application_Update_SetStatusOfExistingDoiTest extends ControllerTestCase
{
    /** @var string */
    protected $additionalResources = 'database';

    /**
     * @throws ModelException
     *
     * TODO test sets Status of all DOI identifier of published documents to 'registered' (side effect)
     * TODO Remove debug code no longer needed.
     */
    public function testRunDoesNotModifyServerDateModified()
    {
        $doc = $this->createTestDocument();
        $doc->setServerState('published');

        $doi = Identifier::new();
        $doi->setType('doi');
        $doi->setValue('testdoi');

        $doc->addIdentifier($doi);
        $docId = $doc->store();

        // ServerDateModified wird manchmal gerundet beim Speichern = deshalb muss das Dokument noch mal geladen werden
        // TODO https://github.com/OPUS4/framework/issues/228
        $doc = Document::get($docId);

        $modified = $doc->getServerDateModified();

        $debug = "$modified (before)" . PHP_EOL;

        $doc       = Document::get($docId);
        $modified2 = $doc->getServerDateModified();

        $debug .= "$modified2 (before - from new object)" . PHP_EOL;

        $time1 = Date::getNow();
        sleep(2);
        $time2 = Date::getNow();

        $debug .= "$time1 - sleep(2) - $time2" . PHP_EOL;
        $debug .= $this->getServerDateModifiedFromDatabase($docId) . ' (before - from database)' . PHP_EOL;

        $update = new Application_Update_SetStatusOfExistingDoi();
        $update->setLogger(new MockLogger());
        $update->setQuietMode(true);

        $update->run();

        $debug .= $this->getServerDateModifiedFromDatabase($docId) . ' (after - from database)' . PHP_EOL;

        $doc = Document::get($docId);

        $debug .= "{$doc->getServerDateModified()} (after - from new object)" . PHP_EOL;

        $this->assertEquals(0, $doc->getServerDateModified()->compare($modified), $debug);
        $this->assertEquals('registered', $doc->getIdentifierDoi(0)->getStatus());
    }

    /**
     * @param int $docId
     * @return string
     */
    protected function getServerDateModifiedFromDatabase($docId)
    {
        $table  = TableGateway::getInstance(Documents::class);
        $select = $table->select()
            ->from($table, ['server_date_modified'])
            ->where("id = $docId");
        $result = $table->getAdapter()->fetchCol($select);

        return $result[0];
    }
}
