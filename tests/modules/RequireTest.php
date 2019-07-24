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
 * @category    Tests
 * @package     Application
 * @author      Thoralf Klein <thoralf.klein@zib.de>
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2010-2019, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Test cases to load all class files.
 *
 * @package Application
 * @category Tests
 *
 * @group RequireTest
 *
 * @coversNothing
 *
 * TODO following annotations necessary/desired?
 * @runTestsInSeparateProcess
 * @preserveGlobalState disabled
 */
class RequireTest extends Zend_Test_PHPUnit_ControllerTestCase
{

    public $application;

    /**
     * Overwrite standard setUp method, no database connection needed.  Will
     * create a file listing of class files instead.
     *
     * @return void
     */
    public function setUp()
    {
        $this->closeLogfile();
        $this->resetAutoloader();

        $this->application = new Zend_Application(
            APPLICATION_ENV,
            ["config" => [
                APPLICATION_PATH . '/tests/simple.ini'
            ]]
        );
        $this->bootstrap = [$this, 'appBootstrap'];

        parent::setUp();
    }

    /**
     * TODO specifying which resources to initalize leads to modules autoloading not being setup
     */
    public function appBootstrap()
    {
        $this->application->bootstrap();
    }

    public function resetAutoloader()
    {
        // Reset autoloader to fix huge memory/cpu-time leak
        Zend_Loader_Autoloader::resetInstance();
        $autoloader = Zend_Loader_Autoloader::getInstance();
        $autoloader->suppressNotFoundWarnings(false);
        $autoloader->setFallbackAutoloader(true);
    }

    /**
     * Close logfile to prevent plenty of open logfiles.
     */
    protected function closeLogfile()
    {
        if (!Zend_Registry::isRegistered('Zend_Log')) {
            return;
        }

        $log = Zend_Registry::get('Zend_Log');
        if (isset($log)) {
            $log->__destruct();
            Zend_Registry::set('Zend_Log', null);
        }

        Opus_Log::drop();
    }

    public function tearDown()
    {
        $this->application = null;

        parent::tearDown();
        // DEBUG echo 'memory usage ' . ( memory_get_usage() / 1024 / 1024 ) . PHP_EOL;
    }

    /**
     * Data provider for all classes which should be loadable.
     *
     * @return array
     */
    public static function serverClassesDataProvider()
    {
        $modulesPath = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'modules';

        $cmd = "find $modulesPath -type f -iname \"*php\"";
        $classFiles = [];
        exec($cmd, $classFiles);

        $blacklist = [
            'statistic/models/StatisticGraph',
            'statistic/models/StatisticGraphThumb'
        ];

        $checkClassFiles = array();
        foreach ($classFiles AS $file) {
            foreach ($blacklist as $excluded) {
                if (strstr($file, $excluded)) {
                    $file = null;
                    continue;
                }
            }
            if (!is_null($file)) {
                $checkClassFiles[] = [$file];
            }
        }

        return $checkClassFiles;
    }

    /**
     * Try to load all class files, just to make sure no syntax error have
     * been introduced.  As a side effect, all classes will be visible to
     * code coverage report.
     *
     * @dataProvider serverClassesDataProvider
     */
    public function testRequire($file)
    {
        require_once($file);
    }
}
