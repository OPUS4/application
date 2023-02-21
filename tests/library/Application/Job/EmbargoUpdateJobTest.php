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
 * @category    Application Unit Test
 * @package     Application
 * @author      Kaustabh Barman <barman@zib.de>
 * @copyright   Copyright (c) 2021, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\Date;
use Opus\Common\Repository;
use Opus\Document;

class Application_Job_EmbargoUpdateJobTest extends ControllerTestCase
{
    protected $additionalResources = 'database';

    private $job;

    public function setup(): void
    {
        parent::setUp();
        $this->job = new Application_Job_EmbargoUpdateJob();
    }

    public function testRun()
    {
        $twoDaysAgo = new Date();
        $twoDaysAgo->setDateTime(new DateTime(date('Y-m-d H:i:s', strtotime('-2 day'))));

        $yesterday = date('Y-m-d', strtotime('-1 day'));

        $today = date('Y-m-d', time());

        $doc = Document::new();
        $doc->setEmbargoDate($yesterday);
        $expiredId = $doc->store();

        $doc = Document::new();
        $noEmbargoId = $doc->store();

        $doc = Document::new();
        $doc->setEmbargoDate($today);
        $notExpiredId = $doc->store();

        $documents = Repository::getInstance()->getModelRepository(Document::class);
        $documents->setServerDateModifiedForDocuments($twoDaysAgo, [$expiredId, $noEmbargoId, $notExpiredId]);

        $this->job->run();

        // document embargo until yesterday -> therefore ServerDateModified got updated
        $doc = Document::get($expiredId);
        $this->assertTrue($this->sameDay(new DateTime($today), $doc->getServerDateModified()->getDateTime()));

        // document embargo until today -> therefore ServerDateModified not yet updated
        $doc = Document::get($notExpiredId);
        $this->assertTrue($this->sameDay($twoDaysAgo->getDateTime(), $doc->getServerDateModified()->getDateTime()));

        // no document embargo -> therefore ServerDateModified unchanged
        $doc = Document::get($noEmbargoId);
        $this->assertTrue($this->sameDay($twoDaysAgo->getDateTime(), $doc->getServerDateModified()->getDateTime()));
    }

    private function sameDay($firstDate, $secondDate)
    {
        $first = $firstDate->format('Y-m-d');
        $second = $secondDate->format('Y-m-d');
        return $first == $second;
    }
}
