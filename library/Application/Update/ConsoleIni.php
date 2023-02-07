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
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Creates console.ini during update, if necessary.
 */
class Application_Update_ConsoleIni extends Application_Update_PluginAbstract
{
    public const DEFAULT_ADMIN_USER = 'opus4admin';

    public const CONSOLE_INI_PATH = '/application/configs/console.ini';

    public const CONSOLE_INI_TEMPLATE_PATH = '/application/configs/console.ini.template';

    public const CREATEDB_SCRIPT_PATH = '/db/createdb.sh';

    public function run()
    {
        $this->log('Checking if console.ini exists ...');

        $consoleIniPath = $this->getConsoleIniPath();

        if (file_exists($consoleIniPath)) {
            $this->log('console.ini found');
            return;
        }

        $this->log('console.ini not found');
        $this->log('Looking for admin credentials in ' . self::CREATEDB_SCRIPT_PATH);

        // get credentials from createdb.sh
        $createDbPath = $this->getCreateDbScriptPath();

        $adminUser     = null;
        $adminPassword = null;

        if (is_readable($createDbPath)) {
            $shellScript = new Application_Util_ShellScript($createDbPath);

            $adminUser     = $shellScript->getProperty('user');
            $adminPassword = $shellScript->getProperty('password');
        } else {
            $this->log("Script '$createDbPath' not found");
        }

        // if necessary ask user to enter credentials
        $adminUser     = $this->readAdminUser($adminUser);
        $adminPassword = $this->readAdminPassword($adminPassword);

        // create console.ini
        $properties = [
            '@db.admin.name@'     => $adminUser,
            '@db.admin.password@' => $adminPassword,
        ];

        $fileUtil = new Application_Util_File();
        $fileUtil->copyAndFilter($this->getConsoleIniTemplatePath(), $consoleIniPath, $properties);

        if (is_readable($consoleIniPath)) {
            $this->log('console.ini created');
        }
    }

    /**
     * Returns path to console.ini file.
     *
     * @return string
     */
    public function getConsoleIniPath()
    {
        return APPLICATION_PATH . self::CONSOLE_INI_PATH;
    }

    /**
     * Returns path to createdb.sh file.
     *
     * @return string
     */
    public function getCreateDbScriptPath()
    {
        return APPLICATION_PATH . self::CREATEDB_SCRIPT_PATH;
    }

    /**
     * Returns path to console.ini.template file.
     *
     * @return string
     */
    public function getConsoleIniTemplatePath()
    {
        return APPLICATION_PATH . self::CONSOLE_INI_TEMPLATE_PATH;
    }

    /**
     * Queries admin user from console.
     *
     * @param string $adminUser
     * @return string
     */
    public function readAdminUser($adminUser)
    {
        if ($adminUser === null || strlen(trim($adminUser)) === 0) {
            $adminUser = self::DEFAULT_ADMIN_USER;
            $input     = readline("Please enter OPUS admin user name [$adminUser]: ");

            if ($input !== null && strlen(trim($input)) > 0) {
                $adminUser = $input;
            }
        }

        return $adminUser;
    }

    /**
     * Queries admin password from console.
     *
     * @param string $adminPassword
     * @return null|string
     */
    public function readAdminPassword($adminPassword)
    {
        if ($adminPassword === null || strlen(trim($adminPassword)) === 0) {
            $adminPassword   = null;
            $confirmPassword = null;

            while ($adminPassword === null || $adminPassword !== $confirmPassword) {
                while ($adminPassword === null || strlen(trim($adminPassword)) === 0) {
                    $adminPassword = readline('Please enter OPUS admin password: ');
                }

                $confirmPassword = readline('Please enter OPUS admin password again: ');

                if ($adminPassword !== $confirmPassword) {
                    $this->println('Passwords do not match.');
                    $adminPassword = null;
                }
            }
        }

        return $adminPassword;
    }
}
