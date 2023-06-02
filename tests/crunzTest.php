<?php

/*
Script to test/demo the execution of the 2 dummy tasks triggered by one cron job,
which will be simulated here. Prints the output of each task.
DummyTask1 runs every minute and DummyTask2 every 2 minutes or as configured in the task.ini.
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
