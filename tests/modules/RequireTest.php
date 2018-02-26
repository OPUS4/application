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
 * @copyright   Copyright (c) 2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
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
 */
class RequireTest extends PHPUnit_Framework_TestCase {

    /**
     * Overwrite standard setUp method, no database connection needed.  Will
     * create a file listing of class files instead.
     *
     * @return void
     */
    public function setUp() {
        require_once 'Zend/Application.php';

        set_include_path('../modules'
                . PATH_SEPARATOR . get_include_path());

        // Do test environment initializiation.
        $application = new Zend_Application(
                        APPLICATION_ENV,
                        array(
                            "config" => array(
                                APPLICATION_PATH . '/application/configs/application.ini',
                                APPLICATION_PATH . '/tests/config.ini'
                            )
                        )
        );
        $application->bootstrap();
    }

    /**
     * Overwrite standard tearDown method, no cleanup needed.
     *
     * @return void
     */
    public function tearDown() {
    }

    /**
     * Data provider for all classes which should be loadable.
     *
     * @return array
     */
    public static function serverClassesDataProvider() {
        $cmd = 'find ../modules -type f -iname "*php" |cut -d/ -f3-';
        $classFiles = array();
        exec($cmd, $classFiles);

        $checkClassFiles = array();
        foreach ($classFiles AS $file) {
            if (strstr($file, 'statistic/models/StatisticGraph')
                    or strstr($file, '/views/') ) {
                continue;
            }
            $checkClassFiles[] = array($file);
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
    public function testRequire($file) {
        require_once($file);
    }
}
