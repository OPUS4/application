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
 * @package     Import
 * @author      Michael Lang <lang@zib.de>
 * @copyright   Copyright (c) 2009-2011, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */


class Opus3Migration_Base {

    protected $logger;
    protected $config;

    function __construct () {
        $this->configMigrationLogger();
        $this->config = Zend_Registry::get('Zend_Config');
    }

    public function configMigrationLogger() {
        $this->logger = new Zend_Log();

        $writer = $this->createWriter($this->config->migration->error->logfile);
        $writer->addFilter(new Zend_Log_Filter_Priority(Zend_Log::WARN));
        $this->logger->addWriter($writer);

        $writer = $this->createWriter($this->config->migration->debug->logfile);
        // $writer->addFilter(new Zend_Log_Filter_Priority(Zend_Log::DEBUG));
        $this->logger->addWriter($writer);

        $writer = $this->createWriter();
        $writer->addFilter(new Zend_Log_Filter_Priority(Zend_Log::DEBUG, '=='));
        $this->logger->addWriter($writer);

        $writer = $this->createWriter();
        $writer->addFilter(new Zend_Log_Filter_Priority(Zend_Log::ERR, '=='));
        $this->logger->addWriter($writer);

        $writer = $this->createWriter();
        $writer->addFilter(new Zend_Log_Filter_Priority(Zend_Log::WARN, '=='));
        $this->logger->addWriter($writer);

        Zend_Registry::set('Zend_Log', $this->logger);
    }

    private function createWriter($logfilePath = null) {
        $GLOBALS['id_string'] = uniqid(); // Write ID string to global variables, so we can identify/match individual runs.
        $format = '%timestamp% %priorityName% (%priority%, ID ' . $GLOBALS['id_string'] . '): %message%' . PHP_EOL;
        $formatter = new Zend_Log_Formatter_Simple($format);
        if (is_null($logfilePath)) {
            $writer = new Zend_Log_Writer_Stream('php://output');
        }
        else {
            $logfile = @fopen($logfilePath, 'a', false);
            if ( $logfile === false ) {
                // TODO use Opus exception
                throw new Exception('Failed to open logging file:' . $logfilePath);
            }
            $writer = new Zend_Log_Writer_Stream($logfile);
        }
        $writer->setFormatter($formatter);
        return $writer;
    }

} 