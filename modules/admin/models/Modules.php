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
 * @package     Module_Admin
 * @author      Thoralf Klein <thoralf.klein@zib.de>
 * @authro      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2012, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Model for getting list of modules in server application.
 * 
 * Die Liste der Module wird verwendet, damit ein Administration bestimmen kann auf welche Module eine Role zugreifen
 * darf.
 */
class Admin_Model_Modules {

    /**
     * Directory path for modules.
     * @var String
     */
    private $_moduleDirectory;

    public function __construct($moduleDirectory = null) {
        $this->_moduleDirectory = $moduleDirectory;
    }

    /**
     * Iterates over module directories and returns all module names
     * @return array List of module names
     */
    public function getAll() {
        $moduleDir = $this->_moduleDirectory;
        $deadPaths = Array( ".", "..", ".svn");
        $modules = array();

        $temp = array_diff(scandir($moduleDir), $deadPaths);
        foreach ($temp as $module) {
            if (!is_dir($moduleDir . '/' . $module)) {
                continue;
            }

            if (!is_dir($moduleDir . '/' . $module . '/controllers/')) {
                continue;
            }

            // Zugriff auf 'default' muß immer erlaubt sein
            if ($module !== 'default') {
                $modules[] = $module;
            }
        }
        return $modules;
    }

}
