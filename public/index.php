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
 * @category    Application
 * @author      Ralf Claussnitzer (ralf.claussnitzer@slub-dresden.de)
 * @author      Thoralf Klein <thoralf.klein@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

// Saving start time for profiling.
$GLOBALS['start_mtime'] = microtime(true);

// Define path to application directory
defined('APPLICATION_PATH')
        || define('APPLICATION_PATH', realpath(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'application');

// Define application environment
//defined('APPLICATION_ENV')
//    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));
// Thoralf: Override environment for now...
define('APPLICATION_ENV', 'testing');

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
            realpath(dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'library'),
            get_include_path(),
        )));

///** Zend_Application */
//require_once 'Zend/Application.php';
//
//// Create application, bootstrap, and run
//$application = new Zend_Application(
//                APPLICATION_ENV,
//                // APPLICATION_PATH . '/configs/config.ini'
//                APPLICATION_PATH . '/configs/application.ini'
//);
//$application->bootstrap()->run();

// Handover control to bootstrap.php. The Parameter passes the root
// path of the application (where all the modules live).

require_once 'Application/Bootstrap.php';
$app = new Application_Bootstrap();
$app->run(
        APPLICATION_PATH,
        Application_Bootstrap::CONFIG_TEST,
        APPLICATION_PATH . DIRECTORY_SEPARATOR . 'configs');
