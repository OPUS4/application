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
 * @package     Opus_Console
 * @author      Felix Ostrowski <ostrowski@hbz-nrw.de>
 * @copyright   Copyright (c) 2009, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * This class provides a static initializiation method for setting up
 * the console environment including php include path, configuration and
 * database setup.
 *
 * @category    Console
 */
class OpusConsole extends Opus_Application_Bootstrap {


    /**
     * Perform basic bootstrapping. Setup environment variables, load
     * configuration and initialize database connection.
     *
     * @return void
     */
    public static function init() {
        // For logging base path.
        self::$applicationRootDirectory = dirname(dirname(__FILE__));
        self::$applicationWorkspaceDirectory = dirname(dirname(__FILE__)) . '/workspace';
        self::setupEnvironment();
        self::configure(self::CONFIG_TEST, dirname(__FILE__));
        self::setupDatabase();
        self::setupLogging();

        $registry = Zend_Registry::getInstance();
        $locale = new Zend_Locale();
        $availableLanguages = $locale->getLanguageTranslationList();
        asort($availableLanguages);
        $registry->set('Available_Languages', $availableLanguages);
        Opus_Document_Type::setXmlDoctypePath(dirname(dirname(__FILE__)) . '/config/xmldoctypes');
    }

    /**
     * Starts an Opus console.
     *
     * @return void
     */
    public function start() {
        while (1) {
            $input = readline('opus> ');
            readline_add_history($input);
            try {
                eval($input);
            } catch (Exception $e) {
                echo 'Caught exception: ' . $e->getMessage() . "\n";
            }
        }
    }
}
