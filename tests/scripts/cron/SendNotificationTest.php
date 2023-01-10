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

require_once 'CronTestCase.php';

use Opus\Common\Job;
use Opus\Job\MailNotification;

/**
 * Dieser Test benötigt das opus-smtp-dumpserver.php ausgeführt wird, um prüfen zu können, ob Nachrichten verschickt
 * werden (Siehe Dokumentation im Wiki).
 *
 * php opus-smtp-dumpserver.php 2>&1 >> opus-smtp-dumpserver.log &
 *
 * TODO kann man das besser automatisieren
 */
class SendNotificationTest extends CronTestCase
{
    /** @var string */
    protected $additionalResources = 'database';

    public function testSendNotification()
    {
        $this->createJob(MailNotification::LABEL, [
            'subject' => 'SendNotification Test',
            'message' => 'This is a test message generated in ' . __FILE__,
            'users'   => [['address' => 'user@example.org', 'name' => 'Test User']],
        ]);
        $this->executeScript('cron-send-notification.php');
        $allJobs = Job::getByLabels([MailNotification::LABEL], null, Job::STATE_UNDEFINED);
        $this->assertTrue(empty($allJobs), 'Expected no more jobs in queue');
        $failedJobs = Job::getByLabels([MailNotification::LABEL], null, Job::STATE_FAILED);
        $this->assertTrue(empty($failedJobs), 'Expected no failed jobs in queue');
    }

    public function testFailSendNotification()
    {
        $this->createJob(MailNotification::LABEL, [
            'subject' => 'SendNotification Test',
            'message' => 'This is a test message generated in ' . __FILE__,
            'users'   => '',
        ]);
        $this->executeScript('cron-send-notification.php');
        $failedJobs = Job::getByLabels([MailNotification::LABEL], null, Job::STATE_FAILED);
        $this->assertEquals(1, count($failedJobs), 'Expected one failed job in queue');
    }
}
