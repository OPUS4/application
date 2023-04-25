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

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 1);

// Define path to application directory
defined('APPLICATION_PATH')
        || define('APPLICATION_PATH', realpath(dirname(dirname(__FILE__))));

// Define application environment (use 'production' by default)
defined('APPLICATION_ENV')
        || define('APPLICATION_ENV', 'testing');

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, [
    APPLICATION_PATH . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'support', // Support-Klassen fuer Tests
    APPLICATION_PATH . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'library', // tests/library
    APPLICATION_PATH . DIRECTORY_SEPARATOR . 'library', // Server library
    APPLICATION_PATH . DIRECTORY_SEPARATOR . 'vendor', // 3rd party library
    get_include_path(),
]));

$catalogPath = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR
    . 'resources' . DIRECTORY_SEPARATOR . 'opus4-catalog.xml';

putenv("XML_CATALOG_FILES=$catalogPath");

require_once 'autoload.php';

// TODO OPUSVIER-4420 remove after switching to Laminas/ZF3
require_once APPLICATION_PATH . '/vendor/opus4-repo/framework/library/OpusDb/Mysqlutf8.php';

// enable fallback autoloader for testing
$autoloader = Zend_Loader_Autoloader::getInstance();
$autoloader->suppressNotFoundWarnings(false);
$autoloader->setFallbackAutoloader(true);

// make sure necessary directories are available
ensureDirectory(APPLICATION_PATH . '/tests/workspace');
ensureDirectory(APPLICATION_PATH . '/tests/workspace/tmp');
ensureDirectory(APPLICATION_PATH . '/tests/workspace/tmp/resumption');
ensureDirectory(APPLICATION_PATH . '/tests/workspace/incoming');
ensureDirectory(APPLICATION_PATH . '/tests/workspace/log');
ensureDirectory(APPLICATION_PATH . '/tests/workspace/filecache');
ensureDirectory(APPLICATION_PATH . '/tests/workspace/files');
ensureDirectory(APPLICATION_PATH . '/tests/workspace/cache');
ensureDirectory(APPLICATION_PATH . '/tests/workspace/export');

/**
 * @param string $path
 */
function ensureDirectory($path)
{
    if (! is_dir($path)) {
        mkdir($path);
        echo "Created directory '$path'" . PHP_EOL;
    }
}
