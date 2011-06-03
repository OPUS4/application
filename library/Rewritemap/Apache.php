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
 * @author     Ralf Claussnitzer <ralf.claussnitzer@slub-dresden.de>
 * @copyright  Copyright (c) 2009, OPUS 4 development team
 * @license    http://www.gnu.org/licenses/gpl.html General Public License
 * @version    $Id$
 */

/**
 * Rewriting request handler for security enabled deliver of requested files.
 *
 * @category Application
 * @package  Opus_Deliver
 */
class Rewritemap_Apache {

    /**
     * Sets URL to file directory.
     *
     * @var string  Defaults to '/files'.
     */
    protected $_targetPrefix = '/files';

    /**
     * For logging output.
     *
     * @var Zend_Log
     */
    protected $_logger = null;

    /**
     * Security realm to check permissions.
     *
     * @var Opus_Security_Realm
     */
    protected $_realm = null;

    /**
     * Initialize the rewritemap instance.
     *
     * @param Zend_Config         $config       (Optional) Config instance to determing targetPrefix
     * @param Zend_Log            $logger       (Optional) Logger instance to issue log messages to.
     * @param Opus_Security_Realm $realm        (Optional) Security realm instance to check permissions.
     * @return void
     */
    public function __construct(Zend_Config $config = null, Zend_Log $logger = null, Opus_Security_Realm $realm = null) {
        if (null === $logger) {
            $logger = new Zend_Log(new Zend_Log_Writer_Mock);
        }

        if (null === $realm) {
            $realm = Opus_Security_Realm::getInstance();
        }

        // set target prefix
        if (isset($config, $config->deliver->target->prefix) === true) {
            $this->_targetPrefix = $config->deliver->target->prefix;
        }

        $this->_logger = $logger;
        $this->_realm = $realm;
    }

    /**
     *
     * @param  array  $arguments
     * @return string target path, if any.
     */
    private function parseRequestArgumentString($arguments = null) {
        $this->_logger->info("got request '$arguments'");

        // check input
        if (!is_string($arguments)) {
            return null;
        }

        // Parse input parameters (given as tab-separated string)
        $parsed = preg_split('/\t/', $arguments, 4);
        while (count($parsed) < 4) {
            $parsed[] = '';
        }

        $docId = $parsed[0];
        $path = $parsed[1];
        $remoteAddress = $parsed[2];
        $cookies = $parsed[3];

        $this->_logger->info("got request '$docId', $path', '$remoteAddress', '$cookies'");

        // set ip/username in realm
        $this->__setupIdentity($remoteAddress, $cookies);

        return array($docId, $path);
    }

    /**
     * Rewrite document requests.
     *
     * @param string $request Input from apache, containing requested address and
     *                        some information about the user.
     * @param string $ip      (Optional) IP of the requesting host.
     * @param string $cookies  (Optional) Cookie content holding authentication information
     *                        if submitted.
     * return string
     */
    public function rewriteRequest($arguments) {

        $request = $this->parseRequestArgumentString($arguments);
        if (!is_array($request) or count($request) < 2) {
            $this->_logger->err('Internal error: Received unexpected arguments, will send 403');
            $this->_logger->err('Apache Input: "' . $arguments . '"');
            return $this->_targetPrefix . "/error/send403.php";
        }

        $docId = $request[0];
        $path = $request[1];

        // check input: docId should only be numbers, path should not contain ../
        if ((mb_strlen($docId) < 1) ||
                (mb_strlen($path) < 1) ||
                (preg_match('/^[\d]+$/', $docId) === 0) ||
                (preg_match('/\.\.\//', $path) === 1)) {
            $this->_logger->err("Error: Got path: $path and docId: $docId, will send "
                    . $this->_targetPrefix . "/error/send403.php'");
            return $this->_targetPrefix . "/error/send403.php"; // Forbidden, independent from authorization.
        }

        $document = null;
        try {
            $document = new Opus_Document($docId);
        }
        catch (Opus_Model_NotFoundException $e) {
            $this->_logger->debug("Document with id '$docId' does not exist, will send "
                    . $this->_targetPrefix . "/error/send404.php'");
            return $this->_targetPrefix . "/error/send404.php"; //not found
        }

        // check for security
        if ($document->getServerState() !== 'published' and !$this->_realm->checkDocument($docId)) {
            $this->_logger->debug("Document with id '$docId' not allowed, will send "
                    . $this->_targetPrefix . "/error/send403.php'");
            return $this->_targetPrefix . "/error/send403.php"; // Forbidden, independent from authorization.
        }

        // lookup the target file
        $targetFile = $this->findFileForDocument($document, $path);

        if (is_null($targetFile) === true) {
            // file not found
            return $this->_targetPrefix . "/error/send404.php"; // not found
        }

        // check if we have access
        if (true === $this->_realm->checkFile($targetFile->getId())) {
            return $this->_targetPrefix . "/$docId/$path";
        }

        return $this->_targetPrefix . "/error/send403.php"; // Unauthorized
    }

    public function sendServerError() {
        return $this->_targetPrefix . "/error/send500.php"; // Internal Server Error.
    }

    private function findFileForDocument($document, $path) {
        foreach ($document->getFile() as $file) {
            $this->_logger->debug("Found file " . $file->getId() . ": " . $file->getPathName());

            $pathnames = $file->getPathName();
            if ($pathnames === $path) {
                return $file;
            }
        }

        return null;
    }


    private function __setupIdentity($ip, $cookiestring) {
        $this->_realm->setIp(null);
        $this->_realm->setUser(null);

        // set/reset IP
        try {
            $this->_realm->setIp($ip);
        }
        catch (Opus_Security_Exception $e) {
            $this->_logger->warn("RewriteMap got an invalid IP address: '$ip'!\n");
        }

        // look for a session to set user/identity in realm.
        if (!is_string($cookiestring) or $cookiestring == '') {
            return;
        }

        $cookies = explode('; ', $cookiestring);
        $session_id = null;
        foreach ($cookies as $cookie) {
            if (preg_match('/' . ini_get('session.name') . '=(.*)[\/]?$/',
                            $cookie, $matches)) {
                $session_id = $matches[1];
            }
        }

        // found an open session?
        if (!is_string($session_id) or $session_id == '') {
            return;
        }

        Zend_Session::setId($session_id);
        Zend_Session::start();
        $identity = Zend_Auth::getInstance()->getIdentity();

        // found an username?
        if (false === empty($identity)) {
            // set session and return.
            $this->_realm->setUser($identity);
        }

        return;
    }

}

