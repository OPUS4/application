<?php

/**
 * This file is part of OPUS. The software OPUS has been originally developed
 * at the University of Stuttgart with funding from the German Research Net,
 * the Federal Department of Higher Education and Research and the Ministry
 * of Science, Research and the Arts of the State of Baden-Wuerttemberg.
 *
 * OPUS 4 is a complete rewrite of the original OPUS software and was developed
 * by the Stuttgart University Library, the Library Service Center
 * Baden-Wuerttemberg, the North Rhine-Westphalian Library Service Center,
 * the Cooperative Library Network Berlin-Brandenburg, the Saarland University
 * and State Library, the Saxon State Library - Dresden State and University
 * Library, the Bielefeld University Library and the University Library of
 * Hamburg University of Technology with funding from the German Research
 * Foundation and the European Regional Development Fund.
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
 * @category   Application
 * @package    Tests
 * @author     Ralf Claussnitzer (ralf.claussnitzer@slub-dresden.de)
 * @author     Thoralf Klein <thoralf.klein@zib.de>
 * @copyright  Copyright (c) 2009, OPUS 4 development team
 * @license    http://www.gnu.org/licenses/gpl.html General Public License
 * @version    $Id$
 */

/**
 * Tests for Rewritemap_Apache.
 *
 * @category   Application
 * @package    Tests
 *
 * @group RewritemapApacheTest
 */
class Rewritemap_ApacheTest extends PHPUnit_Framework_TestCase {

    public function bootstrapFramework() {
        // Resetting singletons or other kinds of persistent objects.
        Opus_Db_TableGateway::clearInstances();

        // FIXME Does it help with the mystery bug?
        Zend_Registry::set('Opus_Navigation', null);

        $application = new Zend_Application(
            APPLICATION_ENV,
            array(
                "config" => array(
                    APPLICATION_PATH . '/application/configs/application.ini',
                    APPLICATION_PATH . '/tests/tests.ini',
                    APPLICATION_PATH . '/tests/config.ini'
                )
            )
        );

        $application->bootstrap();
    }

    protected function mockConfig($prefix) {
        $config = new Zend_Config(array(
            'deliver' => array(
                'target' => array(
                    'prefix' => $prefix
                )
            )
        ));
        return $config;
    }

    /**
     * Test if the target prefix is set properly.
     *
     * @return void
     */
    public function testTargetPrefix() {
        $rwm = new Rewritemap_Apache;
        $this->assertEquals('/files/error/send403.php', $rwm->rewriteRequest(null),
                'Wrong error status file URL for empty request.');

        $rwm = new Rewritemap_Apache($this->mockConfig('/foobar'));
        $this->assertEquals('/foobar/error/send403.php', $rwm->rewriteRequest(null),
                'Wrong error status file URL for empty request.');
    }

    /**
     * Test if the 403 error file URL is delivered on empty request.
     *
     * @return void
     */
    public function testRewriteCallWithEmptyArgumentReturns403ErrorFile() {
        $rwm = new Rewritemap_Apache;

        $this->assertEquals('/files/error/send403.php', $rwm->rewriteRequest(null),
                'Wrong error status file URL for empty request.');

        $this->assertEquals('/files/error/send403.php', $rwm->rewriteRequest(''),
                'Wrong error status file URL for empty request.');
    }

    /**
     * Test if the 403 error file URL is delivered on invalid requests.
     *
     * @return void
     */
    public function testRewriteCallWithInvalidFileArgumentReturns403ErrorFile() {
        $rwm = new Rewritemap_Apache;

        $this->assertEquals('/files/error/send403.php', $rwm->rewriteRequest("123\t"),
                'Wrong error status file URL for empty filename in request.');

        $this->assertEquals('/files/error/send403.php', $rwm->rewriteRequest("\tfoo\t"),
                'Wrong error status file URL for empty docId in request.');

        $this->assertEquals('/files/error/send403.php', $rwm->rewriteRequest("1f\tbla.pdf\t\t"),
                'Wrong error status file URL for invalid docId in request.');

        $this->assertEquals('/files/error/send403.php', $rwm->rewriteRequest("1f\tfoo/../bla.pdf\t\t"),
                'Wrong error status file URL for ".." in request filename.');
    }

    /**
     * Test if the 404 error file URL is delivered on non-existent documents.
     *
     * @return void
     */
    public function testRewriteCallWithUnknownDocumentReturns404ErrorFile() {
        $this->bootstrapFramework();
        $rwm = new Rewritemap_Apache;

        $this->assertEquals('/files/error/send404.php', $rwm->rewriteRequest("123\tbla.pdf\t\t"),
                'Wrong error status file URL for unknown document in request.');
    }

    /**
     * Test if the 403 error file URL is delivered for unpublished documents.
     *
     * @return void
     */
    public function testRewriteCallWithUnknownFileReturns404ErrorFile() {
        $this->bootstrapFramework();
        $rwm = new Rewritemap_Apache;

        $config = Zend_Registry::get('Zend_Config');
        $config->security = '0';

        $this->assertEquals('/files/error/send404.php', $rwm->rewriteRequest("100\tfoo.pdf\t\t"),
                'Wrong error status file URL for unpublished document.');
    }

    /**
     * Test if the 403 error file URL is not permitted.
     *
     * @return void
     */
    public function testRewriteCallWithKnownFileReturns403ErrorWithSecurity() {
        $this->bootstrapFramework();
        $rwm = new Rewritemap_Apache;

        $config = Zend_Registry::get('Zend_Config');
        $config->security = '1';

        $this->assertEquals('/files/error/send403.php', $rwm->rewriteRequest("93\ttest.txt\t\t"),
                'Wrong error status file URL for protected file in request.');
    }

    /**
     * Test if the proper file URL is delivered when security is off.
     *
     * @return void
     */
    public function testRewriteCallWithKnownFileReturnsRightFileWithoutSecurity() {
        $this->bootstrapFramework();
        $rwm = new Rewritemap_Apache;

        $config = Zend_Registry::get('Zend_Config');
        $config->security = '0';

        $this->assertEquals('/files/41/Dissertation_Pick.pdf', $rwm->rewriteRequest("41\tDissertation_Pick.pdf\t\t"),
                'Wrong error status file URL for accessible file in request.');
    }

    /**
     * Test if we get the right file URL when security is off but complete data.
     *
     * @return void
     */
    public function testRewriteCallWithCompleteDataWithoutSecurity() {
        $this->bootstrapFramework();
        Zend_Session::$_unitTestEnabled = true;

        $rwm = new Rewritemap_Apache;

        $config = Zend_Registry::get('Zend_Config');
        $config->security = '0';

        $this->assertEquals('/files/41/Dissertation_Pick.pdf', $rwm->rewriteRequest("41\tDissertation_Pick.pdf\t127.0.0.1\tPHPSESSID=123"),
                'Wrong error status file URL for accessible file in request.');

        $this->assertEquals('/files/41/Dissertation_Pick.pdf', $rwm->rewriteRequest("41\tDissertation_Pick.pdf\t127.0.0.1\tPHPSESSID="),
                'Wrong error status file URL for accessible file in request.');

        $this->assertEquals('/files/41/Dissertation_Pick.pdf', $rwm->rewriteRequest("41\tDissertation_Pick.pdf\t127.0.0.1\t"),
                'Wrong error status file URL for accessible file in request.');
    }

    /**
     * Test if the 403 error file URL is delivered for unpublished documents.
     *
     * @return void
     */
    public function testRewriteCallWithUnpublishedDocumentReturns403WithSecurity() {
        $this->bootstrapFramework();
        $rwm = new Rewritemap_Apache;

        $config = Zend_Registry::get('Zend_Config');
        $config->security = '1';

        $this->assertEquals('/files/error/send403.php', $rwm->rewriteRequest("100\tfoo.pdf\t\t"),
                'Wrong error status file URL for unpublished document.');
    }

    /**
     * Test if we get the right file URL when security is off but complete data.
     *
     * @return void
     */
    public function testRewriteCallCanHandleInvalidIpv4() {
        $this->bootstrapFramework();
        Zend_Session::$_unitTestEnabled = true;

        $rwm = new Rewritemap_Apache;

        $config = Zend_Registry::get('Zend_Config');
        $config->security = '0';

        $this->assertEquals('/files/41/Dissertation_Pick.pdf', $rwm->rewriteRequest("41\tDissertation_Pick.pdf\t1207.0.0.1.23\tPHPSESSID=123"),
                'Wrong error status file URL for accessible file in request.');
    }

    /**
     * Test if a "got request" log message gets logged.
     *
     * @return void
     */   
    public function testGotRequestLogMessage() {
        $logWriter = new Zend_Log_Writer_Mock();
        $log = new Zend_Log($logWriter);
        $rwm = new Rewritemap_Apache($this->mockConfig('/foobar'), $log);
        $rwm->rewriteRequest("\t\t\t");

        $this->assertContains('got request \'\'', $logWriter->events[1]['message'],
            'Message log expected "got request" entry.');
    }

}
