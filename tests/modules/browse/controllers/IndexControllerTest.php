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
 * @package     Tests
 * @author      Thoralf Klein <thoralf.klein@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
class Browse_IndexControllerTest extends Zend_Test_PHPUnit_ControllerTestCase {

    /**
     * Method to initialize Zend_Application for each test.
     */
    public function setUp() {
        $this->bootstrap = new Zend_Application(
                        APPLICATION_ENV,
                        array(
                            "config" => array(
                                APPLICATION_PATH . '/application/configs/application.ini',
                                APPLICATION_PATH . '/tests/config.ini'
                            )
                        )
        );
        parent::setUp();

    }

    /**
     * Method to check response for "bad" strings.
     */
    protected function checkBadStrings() {
        // Test output for "bad" strings.
        // Dirty hack to have some kind of error-checking.  Bad tests are better
        // than no tests!
        $bad_strings = array("Exception", "Error", "Fehler", "Stacktrace");
        $body = strtolower($this->getResponse()->getBody());
        foreach ($bad_strings AS $bad) {
            $this->assertNotContains(
                    strtolower($bad),
                    $body,
                    "Response must not contain '$bad'"
            );
        }

    }

    /**
     * Simple test action to check "index" module.
     */
    public function testIndexAction() {
        $this->dispatch('/browse');
        $this->assertResponseCode(200);
        $this->assertController('index');
        $this->assertAction('index');

        $this->checkBadStrings();

    }

    /**
     * Example test target to demonstrate POST requests.
     */
    public function testFoobarLogin() {
        $this->markTestIncomplete('This test is only a POST-example.');

        $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'username' => 'foobar',
                    'password' => 'foobar'
                ));
        $this->dispatch('/user/login');
        $this->assertTrue(Zend_Auth::getInstance()->hasIdentity());
        $this->assertRedirectTo('/user/view');

    }

}

?>
