<?php
/**
 *
 */

// Configure include path.
set_include_path('.' . PATH_SEPARATOR
            . PATH_SEPARATOR . dirname(dirname(__FILE__)) . '/library'
            . PATH_SEPARATOR . get_include_path());

// Handover control to bootstrap.php. The Parameter passes the root
// path of the application (where all the modules live).

require_once 'Opus/Application/Bootstrap.php';
Opus_Application_Bootstrap::run(
    dirname(dirname(__FILE__)),
    Opus_Application_Bootstrap::CONFIG_TEST,
    dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'config');