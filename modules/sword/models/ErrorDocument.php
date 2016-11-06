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
 * @package     Module_Sword
 * @author      Sascha Szott
 * @copyright   Copyright (c) 2016
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
class Sword_Model_ErrorDocument {
    
    private $request;
    
    private $response;
    
    private $logger;
    
    public function __construct($request, $response) {
        $this->request = $request;
        $this->response = $response;        
        $this->logger = Zend_Registry::get('Zend_Log');
    }
    
    /**
     * Used where a client has attempted a mediated deposit, but this is not 
     * supported by the server. The server MUST also return a status code of
     * 412 Precondition Failed.
     */
    public function setMediationNotAllowed() {
        $this->setResponse(412, 'http://purl.org/net/sword/error/MediationNotAllowed');
    }
    
    /**
     * Some parameters sent with the POST were not understood.
     * The server MUST also return a status code of 400 Bad Request.
     */
    public function setErrorBadRequest() {
        $this->setResponse(400, 'http://purl.org/net/sword/error/ErrorBadRequest');
    }
    
    /**
     * Checksum sent does not match the calculated checksum. 
     * The server MUST also return a status code of 412 Precondition Failed.
     */
    public function setErrorChecksumMismatch($checksumHeader, $checksumPayload) {
        $this->logger->warn('Checksum mismatch: checksum header value ' . $checksumHeader . ' - checksum of payload ' . $checksumPayload);
        $this->setResponse(412, 'http://purl.org/net/sword/error/ErrorChecksumMismatch');
    }
    
    /**
     * The supplied format is not the same as that identified in the X-Packaging 
     * header and/or that supported by the server 
     */
    public function setErrorContent() {
        $this->setResponse(415, 'http://purl.org/net/sword/error/ErrorContent');
    }
    
    public function setForbidden() {
        $this->setResponse(403, 'http://www.opus-repository.org/sword/error/Forbidden');
    }
    
    public function setPayloadTooLarge() {
        $this->setResponse(413, 'http://www.opus-repository.org/sword/error/PayloadToLarge');
    }
    
    public function setMissingImportEnrichmentKey() {
        $this->setResponse(400, 'http://www.opus-repository.org/sword/error/MissingImportEnrichmentKey');
    }
    
    public function setInvalidXml() {
        $this->setResponse(400, 'http://www.opus-repository.org/sword/error/InvalidXml');
    }
    
    public function setMissingXml() {
        $this->setResponse(400, 'http://www.opus-repository.org/sword/error/MissingXml');
    }
    
    /**
     * Es wurde ein valides opus.xml mit mindestens einem Metadatensatz eingeliefert.
     * Allerdings konnte kein Datensatz erfolgreich in OPUS angelegt werden. Eine
     * mögliche Ursache ist eine URN Collision (wenn ein Datensatz einen Identifier
     * vom Typ urn verwendet und bereits ein Datensatz im Repositorium existiert,
     * der diese URN besitzt. Es sollte das Logfile konsultiert werden, um die
     * Fehlerursache genauer zu analysieren.
     */
    public function setInternalFrameworkError() {
        $this->setResponse(400, 'http://www.opus-repository.org/sword/error/InternalFrameworkError');
    }

    private function setResponse($statusCode, $errorCond) {
        $this->response->setHeader('Content-Type', 'text/xml; charset=UTF-8', true);
        $this->response->setHttpResponseCode($statusCode);
        $this->response->setBody($this->getDocument($errorCond));
    }
    
    private function getDocument($errorCond) {
        $root = new SimpleXMLElement('<sword:error xmlns="http://www.w3.org/2005/Atom" xmlns:sword="http://purl.org/net/sword/"></sword:error>');
        $root->addAttribute('href', $errorCond);
        $root->addChild('title', 'ERROR');
        
        $config = Zend_Registry::get('Zend_Config');
        $generator = $config->sword->generator;
        $root->addChild('generator', $generator);
        
        $root->addChild('summary', '');
        
        // should we sanitize the value of $userAgent before setting HTTP response header?
        $userAgent = $this->request->getHeader('User-Agent');
        if (is_null($userAgent) || $userAgent === false) {
            $userAgent = 'n/a';
        }
        $root->addChild('sword:userAgent', $userAgent, 'http://purl.org/net/sword/');
        
        return $root->asXML();
    }    
}
