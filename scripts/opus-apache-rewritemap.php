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
 * @package    Opus_Deliver
 * @author     Pascal-Nicolas Becker <becker@zib.de>
 * @copyright  Copyright (c) 2009, OPUS 4 development team
 * @license    http://www.gnu.org/licenses/gpl.html General Public License
 * @version    $Id$
 */

// Configure include path.
set_include_path('.' . PATH_SEPARATOR
            . PATH_SEPARATOR . dirname(__FILE__)
            . PATH_SEPARATOR . dirname(dirname(__FILE__)) . '/library'
            . PATH_SEPARATOR . get_include_path());

// Zend_Loader is'nt available yet. We have to do a require_once
// in order to find the bootstrap class.
require_once 'Opus/Bootstrap/Base.php';

/**
 * Bootstraps and runs the application.
 *
 * @category    Application
 */
class OpusApacheRewritemap extends Opus_Bootstrap_Base {

    /**
     * Sets URL to file directory.
     * TODO: make configurable
     *
     * @var string  Defaults to '/workspace/files'.
     */
    protected static $_absoluteFileDirURL = '/workspace/files';

    /**
     * Static function to rewrite document requests.
     *
     * @param string $request Input from apache, containing requested address and some information about the user.
     *
     * return string
     */
    public function rewriteRequest($request) {
        // TODO: make pathes configurable
//      $logger = Zend_Registry::get('Zend_Log');
//      $logger->info("got request: '" . $request . "'");
        return self::$_absoluteFileDirURL . '/' . $request . "\n";
    }

    /**
     * Empty method to not setup a backend.
     *
     * @return void
     */
    protected function _setupBackend() {
        $this->_setupLogging();
    }

    /**
     * Starts an Opus console.
     *
     * @return void
     */
    protected function _run() {
    }

}

// Loop to read requests given by apache.
while($line = trim(fgets(STDIN))) {
    // split input
    list($path, $remoteAddress, $userAgent, $cookie) = preg_split('/\t/', $line, 4);
    $_COOKIE=$cookie;

    // Bootstrap Zend
    $rwmap = new OpusApacheRewritemap;
    $rwmap->run(
        // application root directory
        dirname(dirname(__FILE__)),
        // config level
        Opus_Bootstrap_Base::CONFIG_TEST,
        // path to config file
        dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'config');
    echo $rwmap->rewriteRequest($path) . "\n";
}
// ATTENTION: CODE BELONG THIS LINE WILL NEVER BE REACHED!