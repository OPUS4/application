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
 * @package     Application_Configuration
 * @author      Maximilian Salomon
 * @copyright   Copyright (c) 2018
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class Application_Configuration_SetupChecker
{
    private $_Files;
    public $_messages;
    private $_names = [];
    private $_objects = [];

    public function __construct()
    {
        $this->_Files = scandir(APPLICATION_PATH . '/library/Application/Configuration/Check');
        unset($this->_Files[0]);
        unset($this->_Files[1]);
        foreach ($this->_Files as $values) {
            $value = substr($values, 0, -4);
            array_push($this->_names, 'Application_Configuration_Check_' . $value);
        }
        foreach ($this->_names as $value) {
            $temp = new $value;
            array_push($this->_objects, $temp);
        }

    }

    public function check()
    {
        foreach ($this->_objects as $values) {
            $values->check();
            $key = $values->getErrors();
            foreach ($key as $value) {
                $this->_messages[] = $values->getMessage($value);
            }
            if ($values == FALSE) {
                return FALSE;
            }
            return TRUE;
        }
    }

    public function getMessages()
    {
        return $this->_messages;
    }

    public function getObjects()
    {
        return $this->_objects;
    }

    public function getName()
    {
        return $this->_names;
    }

    public function setName($arg)
    {
        $this->_name = $arg;
    }

    public function setObjects($arg)
    {
        $this->_objects = $arg;
    }
}