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
 * @package     Application_Translate
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2019, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 *
 * TODO define and use interface
 */

abstract class Application_Translate_Help
{

    static private $instance;

    private $config;

    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new Home_Model_HelpFiles();
        }

        return self::$instance;
    }

    public function getConfig()
    {
        if (is_null($this->config)) {
            $this->config = Zend_Registry::get('Zend_Config');
        }
        return $this->config;
    }

    public function getSeparateViewEnabled()
    {
        $config = $this->getConfig();

        if (isset($config->help->separate)) {
            return filter_var($config->help->separate, FILTER_VALIDATE_BOOLEAN);
        } else {
            return false;
        }
    }

    abstract public function getHelpEntries();

    abstract public function getContent($key);
}
