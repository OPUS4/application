<?php
/*
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
 * @category    Application Unit Test
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2019, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Log;

/**
 * Base class for application tests.
 *
 * TODO any effect vvv ?
 * @preserveGlobalState disabled
 */
class TestCase extends \Zend_Test_PHPUnit_ControllerTestCase
{

    protected $application;

    protected $applicationEnv = APPLICATION_ENV;

    protected $configModifiable = false;

    /**
     * Allows specifying additional resources that should be loaded during bootstrapping, e.g. 'database'.
     * @var string|array
     */
    protected $additionalResources;

    /**
     * Overwrite standard setUp method, no database connection needed.  Will
     * create a file listing of class files instead.
     *
     * @return void
     */
    public function setUp()
    {
        $this->cleanupBefore();

        $this->application = $this->getApplication();
        $this->bootstrap = [$this, 'appBootstrap'];

        parent::setUp();

        if ($this->configModifiable) {
            $this->makeConfigurationModifiable();
        }
    }

    public function tearDown()
    {
        $this->application = null; // IMPORTANT: this helps reduce memory usage when running lots of tests

        parent::tearDown();
        // echo PHP_EOL . 'memory usage ' . ( memory_get_usage() / 1024 / 1024 ) . PHP_EOL;

        $this->closeLogfile();
    }

    public function cleanupBefore()
    {
        // FIXME Does it help with the mystery bug?
        \Zend_Registry::_unsetInstance();

        $this->resetAutoloader();
    }

    public function getApplication()
    {
        return new \Zend_Application(
            $this->applicationEnv,
            ["config" => [
                APPLICATION_PATH . '/tests/simple.ini'
            ]]
        );
    }

    /**
     */
    public function appBootstrap()
    {
        $resources = ['configuration', 'logging', 'modules'];

        if (isset($this->additionalResources)) {
            if (is_array($this->additionalResources)) {
                $resources = array_merge($resources, $this->additionalResources);
            } else {
                if ($this->additionalResources !== 'all') {
                    $resources[] = $this->additionalResources;
                } else {
                    $resources = null;
                }
            }
        }

        $this->application->bootstrap($resources);
    }

    /**
     * Reset autoloader to fix huge memory/cpu-time leak.
     */
    public function resetAutoloader()
    {
        \Zend_Loader_Autoloader::resetInstance();
        $autoloader = \Zend_Loader_Autoloader::getInstance();
        $autoloader->suppressNotFoundWarnings(false);
        $autoloader->setFallbackAutoloader(true);
    }

    /**
     * Close logfile to prevent plenty of open logfiles.
     */
    protected function closeLogfile()
    {
        if (! \Zend_Registry::isRegistered('Zend_Log')) {
            return;
        }

        $log = \Zend_Registry::get('Zend_Log');
        if (isset($log)) {
            $log->__destruct();
            \Zend_Registry::set('Zend_Log', null);
        }

        Log::drop();
    }

    public function makeConfigurationModifiable()
    {
        $config = new \Zend_Config([], true);
        \Zend_Registry::set('Zend_Config', $config->merge(\Zend_Registry::get('Zend_Config')));
    }
}
