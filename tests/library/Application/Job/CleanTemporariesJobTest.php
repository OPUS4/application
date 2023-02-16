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
use Opus\Document;
use Opus\Model\NotFoundException;

class Application_Job_CleanTemporariesJobTest extends ControllerTestCase
{
    protected $additionalResources = 'database';

    private $job;
    private $doc;

    public function setUp(): void
    {
        parent::setUp();
        $this->job = new Application_Job_CleanTemporariesJob('P2D');
        $this->doc = new Mock_OpusDocumentMock();
        $this->doc->setServerState('temporary');
        $this->doc->store();
    }

    public function testRun()
    {
        $this->changeDocumentDateModified($this->doc, 3);
        $this->job->run();

        $this->expectException(NotFoundException::class);
        $doc = Document::get($this->doc->getId());
    }

    public function testRunForMultipleDocs()
    {
        $this->changeDocumentDateModified($this->doc, 3);

        $newDoc = new Mock_OpusDocumentMock();
        $newDoc->setServerState('temporary');
        $newDoc->store();
        $this->changeDocumentDateModified($newDoc, 3);

        $docArray = [$this->doc, $newDoc];

        $this->job->run();

        foreach ($docArray as $document) {
            $this->expectException(NotFoundException::class);
            $doc = Document::get($document->getId());
        }
    }

    public function testGetPreviousDate()
    {
        $job = $this->job;
        $reflector = new \ReflectionClass($job);
        $getDate = $reflector->getMethod('getPreviousDate');
        $getDate->setAccessible(true);
        $date = $getDate->invokeArgs($job, []);

        $dateTime = new DateTime();
        $expected = $dateTime->sub(new DateInterval('P2D'))->format('Y-m-d');

        $this->assertSame($expected, $date);
    }

    private function changeDocumentDateModified($document, $numDaysBeforeNow)
    {
        $date = new DateTime();
        $date->sub(new DateInterval("P{$numDaysBeforeNow}D"));
        $document->changeServerDateModified(new Date($date));
    }
}
