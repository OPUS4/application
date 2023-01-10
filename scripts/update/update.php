<?php

/**
 * This file is part of OPUS. The software OPUS has been originally developed
 * at the University of Stuttgart with funding from the German Research Net,
 * the Federal Department of Higher Education and Research and the Ministry
 * of Science, Research and the Arts of the State of Baden-Wuerttemberg.
 *
 * OPUS 4 is a complete rewrite of the original OPUS software and was developed
 * by the Stuttgart University Library, the Library Service Center
 * Baden-Wuerttemberg, the North Rhine-Westphalian Library Service Center,
 * the Cooperative Library Network Berlin-Brandenburg, the Saarland University
 * and State Library, the Saxon State Library - Dresden State and University
 * Library, the Bielefeld University Library and the University Library of
 * Hamburg University of Technology with funding from the German Research
 * Foundation and the European Regional Development Fund.
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
 * @copyright   Copyright (c) 2016, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/*
 * Script for executing OPUS 4 update.
 *
 * For an update it is assumed that the relevant configuration files exist.
 */

set_include_path(
    implode(
        PATH_SEPARATOR,
        [
            dirname(dirname(dirname(__FILE__))) . '/library',
            dirname(dirname(dirname(__FILE__))) . '/vendor',
            get_include_path(),
        ]
    )
);

defined('APPLICATION_PATH') || define('APPLICATION_PATH', realpath(dirname(dirname(dirname(__FILE__)))));

defined('APPLICATION_ENV') || define('APPLICATION_ENV', 'production');

require_once 'autoload.php';
require_once 'opus-php-compatibility.php';

// TODO OPUSVIER-4420 remove after switching to Laminas/ZF3
require_once APPLICATION_PATH . '/vendor/opus4-repo/framework/library/OpusDb/Mysqlutf8.php';

$autoloader = Zend_Loader_Autoloader::getInstance();
$autoloader->suppressNotFoundWarnings(false);
$autoloader->setFallbackAutoloader(true);

$update = new Application_Update();
$update->processArguments($argv);
$update->bootstrap();
$update->run();
