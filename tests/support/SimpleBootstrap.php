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
 * @copyright   Copyright (c) 2019, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\Config;
use Opus\Common\Log;
use Opus\Common\Log\LogService;

/**
 * TODO take care of duplicated code (from regular bootstrap) - maybe SimpleBootstrap is not needed anymore?
 */
class SimpleBootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    /**
     * Load application configuration file and register the configuration
     * object with the Zend registry under 'Zend_Config'.
     *
     * To access parts of the configuration you have to retrieve the registry
     * instance and call the get() method:
     * <code>
     * $registry = Zend_Registry::getInstance();
     * $config = $registry->get('Zend_Config');
     * </code>
     *
     * @return Zend_Config
     * @throws Exception Exception is thrown if configuration level is invalid.
     * @phpcs:disable PSR2.Methods.MethodDeclaration
     */
    protected function _initConfiguration()
    {
        // @phpcs:enable
        $config = new Zend_Config($this->getOptions(), true);
        Config::set($config);
        return $config;
    }

    /**
     * Setup Logging
     *
     * @return Zend_Log
     * @throws Exception If logging file couldn't be opened.
     * @phpcs:disable PSR2.Methods.MethodDeclaration
     */
    protected function _initLogging()
    {
        // @phpcs:enable
        $this->bootstrap('Configuration');

        $logFilename = "opus-console.log";

        $logService = LogService::getInstance();

        $logger = $logService->createLog(LogService::DEFAULT_LOG, null, null, $logFilename);

        Log::set($logger);

        $logger->debug('Logging initialized');

        return $logger;
    }
}
