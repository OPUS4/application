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
 * @category    TODO
 * @author      Thoralf Klein <thoralf.klein@zib.de>
 * @copyright   Copyright (c) 2011, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class Controller_Helper_SendFile extends Zend_Controller_Action_Helper_Abstract {
    const FPASSTHRU = 'fpassthru';
    const XSENDFILE = 'xsendfile';

    /**
     * @var Zend_Log
     */
    private $logger = null;

    /**
     * This method to call when we use   $this->_helper->SendFile(...)   and
     * forwards to method Controller_Helper_SendFile::sendFile
     *
     * @see Controller_Helper_SendFile::sendFile
     */
    public function direct($file, $method = self::FPASSTHRU, $must_resend = false) {
        return $this->sendFile($file, $method, $must_resend);
    }

    /**
     * This method to call when we use   $this->_helper->SendFile(...)
     *
     * @param string  $file        Absoulte filename of file to send.
     * @param string  $method      defaults to self::FPASSTHRU, use self::XSENDFILE for X-Sendfile
     * @param boolean $must_resend Ignore "if-modified-since" header, defaults to false.
     * @return void
     */
    public function sendFile($file, $method = self::FPASSTHRU, $must_resend = false) {

        $response = $this->getResponse();
        if (!$response->canSendHeaders()) {
            throw new Exception("Cannot send headers");
        }

        $file = realpath($file);
        if (!is_readable($file)) {
            throw new Exception("File is not readable");
        }

        $modified = filemtime($file);
        if ($must_resend === true && $this->notModifiedSince($modified)) {
            return;
        }

        if ($method === self::XSENDFILE) {
            $this->sendFileViaXSendfile($file);
            return;
        }

        $this->sendFileViaFpassthru($file);
    }

    /**
     * Check IF_MODIFIED_SINCE header.  Return true, if header set and file
     * modified.  Return false otherwise.
     *
     * @param  string $modified  Timestamp string
     * @return boolean
     */
    public function notModifiedSince($modified) {
        if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && $modified <= strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
            $response = $this->getResponse();
            $response->setHttpResponseCode(304);
            $response->sendHeaders();
            return true;
        }
        return false;
    }

    /**
     * Sends X-Sendfile header to let the webserver deliver the file.
     * (See Apache module mod_xsendfile and HTTP header X-Sendfile.)
     *
     * @param string $file
     */
    private function sendFileViaXSendfile($file) {
        $response = $this->getResponse();
        $response->setHttpResponseCode(200);
        $response->setHeader('X-Sendfile', $file);
        $response->sendHeaders();
    }

    /**
     * Delivers the file via function "fpassthru()".
     *
     * @param string $file
     */
    private function sendFileViaFpassthru($file) {
        $response = $this->getResponse();
        $response->setHttpResponseCode(200);

        if (!is_null($this->logger)) {
            $content = ob_get_contents();
            $this->logger->err($content);
        }

        ob_end_clean();
        set_time_limit(300);

        $modified = filemtime($file);
        $response->setHeader('Last-Modified', gmdate('r', $modified), true);
        $response->setHeader('Content-Length', filesize($file), true);
        $response->sendHeaders();

        $fp = fopen($file, 'rb');
        if ($fp === false) {
            throw new Exception('fopen failed.');
        }

        $retval = fpassthru($fp);
        if ($retval === false) {
            throw new Exception('fpassthru failed.');
        }
    }

    /**
     *
     * @param Zend_Log $logger
     */
    public function setLogger($logger) {
        $this->logger = $logger;
    }

}
