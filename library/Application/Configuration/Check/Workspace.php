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
 * @package     Application_Configuration_Check
 * @author      Maximilian Salomon
 * @copyright   Copyright (c) 2018
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

class Application_Configuration_Check_Workspace implements Application_Configuration_CheckInterface
{
    private $_path;
    private $_directories;

    const MSG_PERMISSION = "permission";
    const MSG_EXIST = "exist";

    private $_errors = array();

    protected $_messageTemplates = array(
        self::MSG_EXIST => "Some workspace-directories do not exist.",
        self::MSG_PERMISSION => "Some workspace-directories, have not enough permissions."
    );

    public function check()
    {
        $this->getWorkspacePath();
        $this->_directories = array('cache', 'export', 'files', 'incoming', 'log', 'tmp', 'tmp/resumption');
        foreach ($this->_directories as $value) {
            if (!is_dir($this->_path . '/' . $value)) {
                $this->setErrors(self::MSG_EXIST);
                $return = FALSE;
                continue;
            }
            if (!is_readable($this->_path . '/' . $value) OR !is_writable($this->_path . '/' . $value)) {
                $this->setErrors(self::MSG_PERMISSION);
                $return = FALSE;

            }

        }
        if (isset($return)) {
            return FAlSE;
        }
        return TRUE;

    }

    public function getWorkspacePath()
    {
        if ($this->_path == NULL) {
            $this->_path = Application_Configuration::getInstance()->getWorkspacePath();
        }
    }

    public function setWorkspacePath($path)
    {
        $this->_path = $path;
    }

    public function setErrors($message)
    {
        array_push($this->_errors, $message);
    }

    public function getErrors()
    {
        return $this->_errors;
    }

    public function getMessage($key)
    {
        return $this->_messageTemplates[$key];
    }
}
