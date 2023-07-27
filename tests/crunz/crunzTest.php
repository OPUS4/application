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
 * @copyright   Copyright (c) 2022, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/*
 * This script is for development purposes to simulate a configured cronjob for
 * the crunz scheduler. The simulated cron runs every minute, just like the real one should,
 * and echos the tasks output.
 * The tasks to be run are configured in dummytasks.ini,
 * The default dummytask.ini defines 2 dummy tasks. DummyTask1 runs every minute
 * and DummyTask2 every 2 minutes or as configured in the ini.
 *
 * Before running this script, the dummtasks.ini has to be activated in tests/tests.ini
 * After that it can be started with:  php tests/crunz/crunzTest.php
 *
 * TODO Should use the testing environment and use the dummy tasks in tests/support directory.
 * At the moment the production environment is still used.
 * The reason for this is that crunz:list calls the task script in the scripts directory
 * and scripts need their own bootstrapping. Which in this case always uses the production environment
 * As a workaround could be to configure the path to the dummytask.ini in the produktion appplication.ini
 * before running this script, but then the dummy task classes are not found.
 */

// Show the active tasks.
echo passthru("vendor/bin/crunz schedule:list");

for ($i=1; $i <= 10; $i++) {
    // Simulate the needed cron job run every minute
    if ($i > 1) {
        echo "Waiting for the next scheduler run ... \n";
        sleep(60);
    }
    echo "\nCycle: $i of 10 \n";
    echo "Time:  " . date("H:i:s") . " - vendor/bin/crunz schedule:run \n";
    echo passthru("vendor/bin/crunz schedule:run") . "\n";
}
