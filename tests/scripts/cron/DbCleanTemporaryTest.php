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
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\Date;
use Opus\Common\Document;
use Opus\Common\DocumentInterface;
use Opus\Common\Model\ModelException;
use Opus\Common\Model\NotFoundException;

class DbCleanTemporaryTest extends CronTestCase
{
    /** @var string */
    protected $additionalResources = 'database';

    /** @var DocumentInterface */
    private $doc;

    public function setUp(): void
    {
        parent::setUp();
        $this->doc = Document::new();
        $this->doc->setLifecycleListener(new DocumentLifecycleListenerMock());
        $this->doc->setServerState('temporary');
        $this->doc->store();
    }

    public function testCleanUpDocumentOlderThan2Days()
    {
        $this->changeDocumentDateModified(3);
        $this->executeScript('cron-db-clean-temporary.php');
        try {
            $doc = Document::get($this->doc->getId());
            $doc->delete();
            $this->fail("expected Opus\Common\Model\NotFoundException");
        } catch (NotFoundException $e) {
        }
    }

    public function testKeepDocumentNewerThan3Days()
    {
        $this->changeDocumentDateModified(2);
        $this->executeScript('cron-db-clean-temporary.php');
        try {
            $doc = Document::get($this->doc->getId());
            $doc->delete();
        } catch (NotFoundException $e) {
            $this->fail("expected existing document.");
        }
    }

    /**
     * @param int $numDaysBeforeNow
     * @throws ModelException
     */
    private function changeDocumentDateModified($numDaysBeforeNow)
    {
        $date = new DateTime();
        $date->sub(new DateInterval("P{$numDaysBeforeNow}D"));
        $this->doc->setServerDateModified(new Date($date));
        $this->doc->store();
    }
}
