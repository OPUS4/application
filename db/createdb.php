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

use Opus\Database;

/**
 * Script for creating OPUS 4 database with optional name and version
 * parameters.
 */

defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(dirname(__FILE__))));

defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production');

// Configure include path.
set_include_path(
    implode(
        PATH_SEPARATOR,
        [
            '.',
            dirname(__FILE__),
            APPLICATION_PATH . '/library',
            APPLICATION_PATH . '/vendor',
            get_include_path(),
        ]
    )
);

require_once 'autoload.php';

// TODO OPUSVIER-4420 remove after switching to Laminas/ZF3
require_once APPLICATION_PATH . '/vendor/opus4-repo/framework/library/OpusDb/Mysqlutf8.php';

$application = new Zend_Application(
    APPLICATION_ENV,
    [
        "config" => [
            APPLICATION_PATH . '/application/configs/application.ini',
            APPLICATION_PATH . '/application/configs/config.ini',
            APPLICATION_PATH . '/application/configs/console.ini',
        ],
    ]
);

$options                                        = $application->getOptions();
$options['opus']['disableDatabaseVersionCheck'] = true;
$application->setOptions($options);

// Bootstrapping application
$application->bootstrap('Backend');

$options = getopt('v:n:');

$database = new Database();

$database->drop();
$database->create();
$database->importSchema();

$database->import(APPLICATION_PATH . '/db/masterdata'); // TODO only difference to createdb.php in framework
