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
    *
    * @var string  Defaults to '/files'.
    */
    protected $_targetPrefix;

    /**
     * opus.config
     *
     * @var Zend_Config
     */
    protected $_config;

    /**
     * For logging output.
     *
     * @var Zend_Log
     */
    protected $_logger;


    public function getTargetPrefix() {
        return $this->_targetPrefix;
    }

    public function log($msg) {
        return $this->_logger->info($msg);
    }

    /**
     * Static function to rewrite document requests.
     *
     * @param string $request Input from apache, containing requested address and some information about the user.
     *
     * return string
     */
    public function rewriteRequest($request, $ip = null, $cookie = null) {
//        $logger->info("got request '$request'");
        // parse and normalize request
        // remove leading slashes
        $request = preg_replace('/^\/(.*)$/', '$1', $request);
        if (preg_match('/^[\d]+[\/]?$/', $request) === 1) {
            // no file name submitted, trying index.html for compatibility reasons
//            $logger->info("no filename submitted, trying /index.html");
            if (preg_match('/\/$/', $request) === 0) {
                $request .= "/";
            }
            $request .= 'index.html';
        }
        list($docId, $path) = preg_split('/\//', $request, 2);
        // check input: docId should only be numbers, path should not leave to upper directory
        if (mb_strlen($docId) < 1 || mb_strlen($path) < 1 ||
                preg_match('/^[\d]+$/', $docId) === 0 ||
                preg_match('/\.\.\//', $path) === 1) {
//            $logger->info("return " . $this->_targetPrefix . "/error/send403.php'");
            return $this->_targetPrefix ."/error/send403.php\n"; // Forbidden, indipendent from authorization.
        }

        // check for security
        if ($this->_config->security === '0') {
            // security switched off, deliver everything
//            $logger->info("return " . $this->_targetPrfix . "'files/$docId/$path'");
            return $this->_targetPrefix ."/$docId/$path\n";
        } else {
            $this->_logger->info(print_r($this->_config, true));
        }

        // setup realm and acl
        $realm = Opus_Security_Realm::getInstance();
        $realm->setAcl(new Opus_Security_Acl());
        $acl = $realm->getAcl();

        // lookup the resourceId
        $resourceId = null;
        // get document
        $doc = new Opus_Document($docId);
        // load files
        $files = $doc->getFile();
        if (is_array($file) === false) {
            $files = array($files);
        }
        // look for the right file and get its ResourceId
        foreach ($files as $file) {
            $pathnames = $file->getPathName();
            if (is_array($pathnames) === false) {
                if ($pathnames === $path) {
                    $resourceId = $file->getResourceId();
                    break;
                }
            }
            // if one day a Opus_File can belong to more then one file in the filesystem:
            foreach ($pathnames as $pathname) {
                if ($pathname === $path) {
                    $resourceId = $file->getResourceId();
                }
            }
        }
        if (is_null($resourcId) === true) {
            // resource ID not found
            return $this->_targetPrefix . "/error/send404.php\n"; //not found
        }

        // first we check if guest role is allowed to access the file
        if ($acl->isAllowed('guest', $resourceId, 'read') === true) {
            return $this->_targetPrefix . "/$docId/$path\n";
        }

        // now we check if we have a role, that's allowed to read the file
        // check the ip address first
        $roles = $realm->getIpAdressRole();
        if (is_array($roles) === false) {
            $roles = array($roles);
        }
        foreach ($roles as $role) {
            if (is_null($role) === false) {
                if ($acl->isAllowed($role, $resourceId, 'read') === true) {
                    return $this->_targetPrefix . "/$docId/$path\n";
                }
            }
        }

        // check now the identity
        $cookies = explode('; ', $cookiestring);
        $session_id = null;
        foreach ($cookies as $cookie) {
                if (preg_match('/'.ini_get('session.name').'=(.*)\/$/',
                        $cookie, $matches)) {
                    $session_id = $matches[1];
                }
        }
        if (is_null($session_id) === false) {
            Zend_Session::setId($session_id);
            Zend_Session::regenerateId();
            Zend_Session::start();
            $auth = Zend_Auth::getInstance();
            if ($auth->hasIdentity()) {
                $roles = $realm->getIdentityRole($auth->getIdentity());
                if (is_array($roles) === false) {
                    $roles = array($roles);
                }
                foreach ($roles as $role) {
                    if (is_null($role) === false) {
                        if ($acl->isAllowed($role, $resourceId, 'read') === true) {
                            return $this->_targetPrefix ."/$docId/$path\n";
                        }
                    }
                }
            }
        }
        return  $this->_targetPrefix . "/error/send401.php\n"; // Unauthorized
    }

    /**
     * Empty method to not setup a backend.
     *
     * @return void
     */
    protected function _setupBackend() {
        $this->_setupLogging();
        $this->_config = Zend_Registry::get('Zend_Config');
        if (is_null($this->_config) === true) {
            die('Can not load Config');
        }
        $this->_logger = Zend_Registry::get('Zend_Log');
        if (empty($this->_config->deliver->target->prefix) === true) {
            $this->_targetPrefix = '';
            $this->_logger->info('Error: no target prefix.');
        } else {
            $this->_targetPrefix = $this->_config->deliver->target->prefix;
        }
    }

    /**
     * Starts an Opus console.
     *
     * @return void
     */
    protected function _run() {
    }

}

// Read request
$line = trim($argv[1]);

// Bootstrap Zend
$rwmap = new OpusApacheRewritemap();
$rwmap->run(
    // application root directory
    dirname(dirname(__FILE__)),
    // config level
    Opus_Bootstrap_Base::CONFIG_TEST,
    // path to config file
    dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'config');

// check input
if (preg_match('/\t.*\t/', $line) === 0) {
    $rwmap->log('Internal fatal error! Input from Apache was not as predicted, unparsable by RewriteMap!');
    $rwmap->log('Apache Input: \'' . $line . '\'');
    return $rwmap->getTargetPrefix() ."/error/send500.php";
}

// instantiate cookie
$cookie = null;
// split input
list($path, $remoteAddress, $cookie) = preg_split('/\t/', $line, 3);

echo $rwmap->rewriteRequest($path, $remoteAddress, $cookie) . "\n";