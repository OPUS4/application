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

// Saving start time for profiling.
$GLOBALS['start_mtime'] = microtime(true);

// Define path to application directory
defined('APPLICATION_PATH')
        || define('APPLICATION_PATH', realpath(dirname(dirname(__FILE__))));

// Define application environment (use 'production' by default)
defined('APPLICATION_ENV')
        || define(
            'APPLICATION_ENV',
            getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'
        );

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, [
    realpath(dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'library'),
    realpath(dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'vendor'),
    get_include_path(),
]));

require_once 'autoload.php';
require_once 'opus-php-compatibility.php';

// TODO OPUSVIER-4420 remove after switching to Laminas/ZF3
require_once APPLICATION_PATH . '/vendor/opus4-repo/framework/library/OpusDb/Mysqlutf8.php';

// Zend_Application
$config = new Zend_Config_Ini(
    APPLICATION_PATH . '/application/configs/application.ini',
    APPLICATION_ENV,
    ['allowModifications' => true]
);

$localConfig = new Zend_Config_Ini(
    APPLICATION_PATH . '/application/configs/config.ini',
    APPLICATION_ENV,
    ['allowModifications' => true]
);

$config->merge($localConfig);

// configuration file that is modified via application user interface
if (is_readable(APPLICATION_PATH . '/application/configs/config.xml')) {
    $onlineConfig = new Zend_Config_Xml(
        APPLICATION_PATH . '/application/configs/config.xml'
    );
    $config->merge($onlineConfig);
}

// Create application, bootstrap, and run
$application = new Zend_Application(APPLICATION_ENV, $config);

try {
    $application->bootstrap()->run();
} catch (Exception $e) {
    if (APPLICATION_ENV === 'production') {
        header("HTTP/1.0 500 Internal Server Error");
        echo '<b>OPUS 4</b>' . PHP_EOL;
        echo '<p>Internal server error - See server logs for more information.</p>' . PHP_EOL;
        echo 'Timestamp: ' . date('Y-m-d H:i:s', time()) . PHP_EOL;
        error_log($e->getMessage());
    } else {
        throw $e;
    }
}
