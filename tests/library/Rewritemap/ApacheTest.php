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

    /**
     * Test if the 403 error file URL is delivered on empty request.
     *
     * @return void
     */
    public function testRewriteCallWithEmptyArgumentReturns403ErrorFile() {
        $rwm = new Rewritemap_Apache;
        $result =$rwm->rewriteRequest('');
        
        $this->assertEquals('/files/error/send403.php', $result, 'Wrong error status file URL.');
    }
 
    /**
     * Test if a "got request" log message gets logged.
     *
     * @return void
     */   
    public function testGotRequestLogMessage() {
        $logWriter = new Zend_Log_Writer_Mock();
        $log = new Zend_Log($logWriter);
        $rwm = new Rewritemap_Apache('/files', $log);
        $rwm->rewriteRequest('');

        $this->assertContains('got request \'\'', $logWriter->events[1]['message'],
            'Message log expected "got request" entry.');
    }

}
