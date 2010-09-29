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
 * @category    Application
 * @author      Felix Ostrowski <ostrowski@hbz-nrw.de>
 * @author      Ralf Claussnitzer <ralf.claussnitzer@slub-dresden.de>
 * @copyright   Copyright (c) 2009, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

// Configure include path.
set_include_path('.' . PATH_SEPARATOR
            . PATH_SEPARATOR . dirname(__FILE__)
            . PATH_SEPARATOR . dirname(dirname(__FILE__)) . '/library'
            . PATH_SEPARATOR . get_include_path());

// Define path to application directory
defined('APPLICATION_PATH')
        || define('APPLICATION_PATH', realpath(dirname(dirname(__FILE__))));

define('APPLICATION_ENV', 'testing');

// Zend_Loader is'nt available yet. We have to do a require_once
// in order to find the bootstrap class.
//require_once 'Application/Bootstrap.php';

/**
 * Bootstraps and runs a console application.
 *
 * @category    Application
 */
class OpusConsole {

    /**
     * Starts an Opus console.
     *
     * @return void
     */
    public function run() {
    
        $config = Zend_Registry::get('Zend_Config');
        if ($config->security !== '0') {
            // setup realm 
            $realm = Opus_Security_Realm::getInstance();
        }

        $register_argc_argv = ini_get('register_argc_argv');
        if (false === is_null($register_argc_argv) 
            && $register_argc_argv == 1
            && $_SERVER['argc'] > 1)
        {
            $files = $_SERVER['argv'];
            // removes script name
            array_shift($files);
            foreach ($files as $file) {
                if (true === file_exists($file)) {
                    include_once($file);
                }
            }
        }
    
        while (1) {
            $input = readline('opus> ');
            readline_add_history($input);
            try {
                eval($input);
            } catch (Exception $e) {
                echo 'Caught exception ' . get_class($e) . ': ' . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
            }
        }
    }
}

// Start console
//$console = new OpusConsole;
//$console->run(
//    // application root directory
//    dirname(dirname(__FILE__)),
//    // config level
//    Opus_Bootstrap_Base::CONFIG_TEST,
//    // path to config file
//    dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'config');

require_once 'Zend/Application.php';

// environment initializiation

$application = new Zend_Application(
    APPLICATION_ENV,
    array(
        "config"=>array(
            APPLICATION_PATH . '/application/configs/application.ini',
            APPLICATION_PATH . '/application/configs/config.ini'
        )
    )
);

$bootstrap_ressources = array('Configuration', 'Logging', 'Database');
$application->bootstrap($bootstrap_ressources);

$console = new OpusConsole();

$console->run();
