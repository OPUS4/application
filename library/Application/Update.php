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

use Opus\Database;

/**
 * Class for performing updates of OPUS 4.
 *
 * This class is used in the update script for OPUS 4. It perfoms configured steps for the update. For database changes
 * classes in the OPUS 4 Framework are used. The Application code deals with modified master data and all other changes
 * except modifications of the database schema.
 *
 * TODO logging to file
 * TODO output version numbers
 */
class Application_Update extends Application_Update_PluginAbstract
{
    /** @var string Path to update scripts. */
    private $scriptsPath = '/scripts/update';

    /** @var bool Enables confirmation before an update step is executed. */
    private $confirmSteps = false;

    /** @var string Shell command for executing scripts. */
    private $shellCommand = 'php';

    /**
     * Bootstrap Zend_Application for update process.
     */
    public function bootstrap()
    {
        $configFiles = [
            APPLICATION_PATH . '/application/configs/application.ini',
            APPLICATION_PATH . '/application/configs/config.ini',
        ];

        $consoleIniPath = APPLICATION_PATH . '/application/configs/console.ini';

        if (is_readable($consoleIniPath)) {
            $configFiles[] = $consoleIniPath;
        }

        $application = new Zend_Application(APPLICATION_ENV, ["config" => $configFiles]);

        // setup logging for updates
        $options = $application->mergeOptions($application->getOptions(), [
            'log'              => [
                'filename' => 'update.log',
                'level'    => 'INFO',
            ],
            'updateInProgress' => true,
        ]);

        $application->setOptions($options);

        $application->bootstrap(['Configuration', 'Logging']);
    }

    /**
     * Parses command line arguments and configures update.
     *
     * @param array $arguments
     */
    public function processArguments($arguments)
    {
        if (array_search('--confirm-steps', $arguments)) {
            $this->setConfirmSteps(true);
        }
    }

    /**
     * Perform update.
     *
     * TODO modify update steps dynamically based on versions involved
     */
    public function run()
    {
        $this->log('Updating OPUS 4 ...' . PHP_EOL);

        // Create console.ini if missing
        // TODO make this into a database dependent (preUpdate) update component?
        $consoleIni = new Application_Update_ConsoleIni();
        $consoleIni->run();

        // Bootstrap again with console.ini containing admin credentials
        $this->bootstrap();

        $this->log(''); // TODO better way for separating output?

        // Update database
        $database = new Application_Update_Database();
        $database->run();

        // Run all the other update scripts
        $this->log(PHP_EOL . 'Running update scripts ... ');
        try {
            $this->runUpdateScripts();
        } catch (Application_Update_Exception $aue) {
            // TODO figure out a way to log stderr output of update script
            $this->log(PHP_EOL . 'ERROR - An error occured during updating!');
            $this->log($aue->getMessage());
            $this->log('Update aborted!');
            return;
        }

        // clear translation cache
        $this->log(PHP_EOL . 'Clearing translation cache ... ');
        $cache = new Application_Util_WorkspaceCache();
        $cache->clearTranslations();
    }

    /**
     * Runs all applicable update scripts.
     *
     * @throws Application_Update_Exception
     */
    public function runUpdateScripts()
    {
        $version = $this->getVersion();

        $scripts = $this->getUpdateScripts($version);

        foreach ($scripts as $script) {
            $basename = basename($script);

            if (! $this->getConfirmSteps() || $this->confirmRunningScript($basename)) {
                $this->runScript($script);
            } else {
                $this->log("Skipping script '$basename'");
            }

            // even if a step is skipped the version is updated - scripts can be executed manually again
            $number = (int) substr($basename, 0, 3);

            $this->setVersion($number);
        }
    }

    /**
     * Asks user if update script should be run.
     *
     * If not the script is skipped, but the version is updated anyway. This is meant as a way
     * to skip update steps if necessary, while still updating to the current version.
     *
     * @param string $name
     * @return bool
     */
    public function confirmRunningScript($name)
    {
        $answer = readline("Run script '$name' [Y|n]?");

        return $answer === false || strlen(trim($answer)) === 0 || $answer === 'Y' || $answer === 'y';
    }

    /**
     * Execute update script.
     *
     * @param string $script
     * @throws Application_Update_Exception
     */
    public function runScript($script)
    {
        if (! is_readable($script)) {
            throw new Application_Update_Exception("Update script '$script' not found!");
        }

        if (! is_executable($script)) {
            throw new Application_Update_Exception("Update script '$script' can not be executed!");
        }

        $basename = basename($script);

        $this->log("Running '$basename' ... ");

        $shellCommand = $this->getShellCommand();

        passthru("{$shellCommand} {$script}", $exitCode);

        if ($exitCode !== 0) {
            $message = "Error ($exitCode) running '$basename'!";
            $this->log($message);
            throw new Application_Update_Exception($message, $exitCode);
        }
    }

    /**
     * Returns necessary scripts for update.
     *
     * Returns all PHP files starting with a three digit number.
     *
     * @param int|null $version Current version of the installation
     * @param int|null $targetVersion Target version of update
     * @return string[]
     *
     * TODO only accepts all lowercase '.php'
     */
    public function getUpdateScripts($version = null, $targetVersion = null)
    {
        $files = new DirectoryIterator(APPLICATION_PATH . $this->scriptsPath);

        $updateScripts = [];

        foreach ($files as $file) {
            $filename = $file->getBasename();
            if (strrchr($filename, '.') === '.php' && preg_match('/^\d{3}-.*/', $filename)) {
                $updateScripts[] = $file->getPathname();
            }
        }

        if ($version !== null) {
            $updateScripts = array_filter($updateScripts, function ($value) use ($version) {
                $number = substr(basename($value), 0, 3);
                return $number > $version;
            });
        }

        if ($targetVersion !== null) {
            $updateScripts = array_filter($updateScripts, function ($value) use ($targetVersion) {
                $number = substr(basename($value), 0, 3);
                return $number <= $targetVersion;
            });
        }

        sort($updateScripts);

        return $updateScripts;
    }

    /**
     * Determines current version of OPUS 4 installation.
     *
     * This version is not the release version, but an internal version number used to controll updates.
     *
     * @return string|null
     */
    public function getVersion()
    {
        $database = new Database();

        $pdo = $database->getPdo($database->getName());

        $version = null;

        try {
            $sql = 'SELECT * FROM `opus_version`';

            $result = $pdo->query($sql)->fetch();

            if (isset($result['version'])) {
                $version = (int) $result['version'];
            }
        } catch (PDOException $pdoex) {
            // TODO logging
        }

        return $version;
    }

    /**
     * Sets version of OPUS in database.
     *
     * This version is the internal version used for controlling updates and not the release version.
     *
     * @param int $version
     *
     * TODO escaping $version before logging?
     */
    public function setVersion($version)
    {
        if (! is_int($version) && ! ctype_digit($version)) {
            $this->log("Cannot set OPUS version '$version'.");
            return;
        }

        $database = new Database();

        try {
            $sql = "TRUNCATE TABLE `opus_version`; INSERT INTO `opus_version` (`version`) VALUES ($version);";

            $database->exec($sql);
        } catch (PDOException $pdoex) {
        }
    }

    /**
     * Sets if every update step should be confirmed before running.
     *
     * @param bool $enabled
     */
    public function setConfirmSteps($enabled)
    {
        $this->confirmSteps = $enabled;
    }

    /**
     * Returns current setting for confirming update steps.
     *
     * @return bool
     */
    public function getConfirmSteps()
    {
        return $this->confirmSteps;
    }

    /**
     * @return string
     */
    public function getShellCommand()
    {
        return $this->shellCommand;
    }
}
