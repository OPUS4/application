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

use Opus\Common\Job;
use Opus\Common\Model\NotFoundException;

class CronTestCase extends ControllerTestCase
{
    /** @var string */
    protected static $scriptPath;

    /** @var string */
    protected static $lockDir;

    /** @var array */
    private $jobIds = [];

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$scriptPath = realpath(dirname(__FILE__) . '/../../../scripts/cron') . '/';
        self::$lockDir    = realpath(self::$scriptPath . '/../../workspace/cache/');
    }

    public function tearDown(): void
    {
        if (! empty($this->jobIds)) {
            foreach ($this->jobIds as $jobId) {
                try {
                    $job = Job::get($jobId);
                    $job->delete();
                } catch (NotFoundException $e) {
                }
            }
        }
        parent::tearDown();
    }

    /**
     * @param string $fileName
     * @return string
     */
    protected function executeScript($fileName)
    {
        $command             = self::$scriptPath . 'cron-php-runner.sh ' . self::$scriptPath . $fileName . ' ' . self::$lockDir;
        $savedApplicationEnv = getenv('APPLICATION_ENV');
        putenv('APPLICATION_ENV=' . APPLICATION_ENV);
        $result = shell_exec($command);
        putenv('APPLICATION_ENV=' . $savedApplicationEnv);
        $this->assertNotNull($result, "Script execution failed:\n" . $command);
        $this->assertContains("job '" . self::$scriptPath . $fileName . "' done", $result);
        return $result;
    }

    /**
     * @param string $label
     * @param array  $data
     */
    protected function createJob($label, $data = [])
    {
        $job = Job::new();
        $job->setLabel($label);
        $job->setData($data);
        $this->jobIds[] = $job->store();
    }
}
