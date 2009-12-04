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
    protected $_targetPrefix;

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
     * @param string              $targetPrefix (Optional) Path prefix of resources to deliver.
     *                                          Default is "/files".
     * @param Zend_Log            $logger       (Optional) Logger instance to issue log messages to.
     * @param Opus_Security_Realm $logger       (Optional) Security realm instance to check permissions.
     * @return void
     */
    public function __construct($targetPrefix = '/files', Zend_Log $logger = null,
        Opus_Security_Realm $realm = null)
    {
        if (null === $logger) {
            $logger = new Zend_Log(new Zend_Log_Writer_Mock);
        }

        if (null === $realm) {
            $realm = Opus_Security_Realm::getInstance();
        }

        $this->_targetPrefix = $targetPrefix;
        $this->_logger = $logger;
        $this->_realm = $realm;
    }



    /**
     * Rewrite document requests.
     *
     * @param string $request Input from apache, containing requested address and
     *                        some information about the user.
     * @param string $ip      (Optional) IP of the requesting host.
     * @param string $cookie  (Optional) Cookie content holding authentication information
     *                        if submitted.
     * return string
     */
    public function rewriteRequest($request, $ip = null, $cookies = null) {
        $this->_logger->info("got request '$request'");
        // parse and normalize request
        // remove leading slash
        $request = preg_replace('/^\/(.*)$/', '$1', $request);
        if (preg_match('/^[\d]+[\/]?$/', $request) === 1) {
            // no file name submitted, trying index.html for compatibility reasons
            $this->_logger->info("no filename submitted, trying /index.html");
            if (preg_match('/\/$/', $request) === 0) {
                $request .= "/";
            }
            $request .= 'index.html';
        }
        list($docId, $path) = preg_split('/\//', $request, 2);
        // check input: docId should only be numbers, path should not contain ../
        if ((mb_strlen($docId) < 1) ||
                (mb_strlen($path) < 1) ||
                (preg_match('/^[\d]+$/', $docId) === 0) ||
                (preg_match('/\.\.\//', $path) === 1)) {
            $this->_logger->info("return " . $this->_targetPrefix . "/error/send403.php'");
            return $this->_targetPrefix ."/error/send403.php"; // Forbidden, indipendent from authorization.
        }

        // check for security
		# if (config === '0') {  //FIXME
        #     // security switched off, deliver everything
        #     $this->_logger->info("return " . $this->_targetPrefix . "'files/$docId/$path'");
        #     return $this->_targetPrefix ."/$docId/$path";
        # }
		
		$this->__setupIdentity($ip, $cookies);

        // lookup the target file
		$target = null;
		//get all files
        $files = $this->__getFilesForDocumentId($docId);
        // look for the right file
        foreach ($files as $file) {
            $pathnames = $file->getPathName();
            if (is_array($pathnames) === false) {
                if ($pathnames === $path) {
                    $target = $file;
                    break;
                }
            } else {
	            // if one day a Opus_File can belong to more then one file in the filesystem:
		        foreach ($pathnames as $pathname) {
			        if ($pathname === $path) {
				        $target = $file;
					}
				}
            }
        }

        if (is_null($target) === true) {
            // file not found
            return $this->_targetPrefix . "/error/send404.php"; //not found
        }

		// check if we have access
		try {
			if (true === $this->_realm->readFile($target)) {
                return $this->_targetPrefix . "/$docId/$path";
			} else {
				// 403 Forbidden
				return $this->_targetPrefix . "/error/send403.php";
			}
        } catch (Exception $e) {
			// 500 Internal Server Error
            return $this->_targetPrefix . "/error/send500.php";
        }

        return  $this->_targetPrefix . "/error/send401.php"; // Unauthorized
    }


    /**
     * Get all Files for the given document identifier.
     *
     * @param integer $docId Document identfier
     * @return array Array of Opus_File objects.
     */
    private function __getFilesForDocumentId($docId) {
        $fileTable = Opus_Db_TableGateway::getInstance('Opus_Db_DocumentFiles');
        $fileRows = $fileTable->fetchAll($fileTable->select()->where('document_id = ?', $docId));
        $result = array();
        foreach ($fileRows as $fileRow) {
            $fileObj = new Opus_File($fileRow);
            $result[] = $fileObj;
        }
        return $result;
    }

	private function __setupIdentity($ip, $cookiestring) {
		// set/reset IP
		try {
			$this->_realm->setIp($ip);
		} catch (Opus_Security_Exception $e) {
			$this->_realm->setIp(null);
		}

		// set/reset User
        // check the identity
		if (false === is_null($cookiestring)) {
	        $cookies = explode('; ', $cookiestring);
		    $session_id = null;
			foreach ($cookies as $cookie) {
				    if (preg_match('/'.ini_get('session.name').'=(.*)[\/]?$/',
					        $cookie, $matches)) {
						$session_id = $matches[1];
					}
	        }
		    if (is_null($session_id) === false) {
			    Zend_Session::setId($session_id);
				Zend_Session::start();
				$identity = Zend_Auth::getInstance()->getIdentity();
				if (false === empty($identiy)) {
					$this->_realm->setUser($identity);
				} else {
					$this->_realm->setUser(null);
				}
			} else {
				$this->_realm->setUser(null);
			}
		}
	}
}

