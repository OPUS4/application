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
 * @copyright   Copyright (c) 2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Mock Klasse für Logging.
 *
 * TODO Unterscheidung von Nachrichten in Log-Leveln?
 */
class MockLogger extends Zend_Log
{
    /** @var array */
    private $messages = [];

    /** @var parent|null */
    private $logger;

    /**
     * @param parent|null $logger
     */
    public function __construct($logger = null)
    {
        parent::__construct();
        $this->logger = $logger;
    }

    /**
     * @param string     $message
     * @param string     $priority
     * @param null|mixed $extras
     * @throws Zend_Log_Exception
     */
    public function log($message, $priority, $extras = null)
    {
        $this->messages[] = $message;
        if ($this->logger !== null) {
            $this->logger->log($message, $priority, $extras);
        }
    }

    /**
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }

    public function clear()
    {
        $this->messages = [];
    }
}
