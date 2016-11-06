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
class Sword_DepositController extends Zend_Rest_Controller {

    public function init() {
        $this->getHelper('Layout')->disableLayout();
        $this->getHelper('ViewRenderer')->setNoRender();
    }
    
    public function postAction() {
        $request = $this->getRequest();
        $response = $this->getResponse();
        
        $userName = Application_Security_BasicAuthProtection::accessAllowed($request, $response);
        if (!$userName) {
            $errorDoc = new Sword_Model_ErrorDocument($request, $response);
            $errorDoc->setForbidden();
            return;
        }
                
        // mediated deposit is currently not supported by OPUS
        $mediatedDeposit = $request->getHeader('X-On-Behalf-Of');
        if (!is_null($mediatedDeposit) && $mediatedDeposit !== false) {
            $errorDoc = new Sword_Model_ErrorDocument($request, $response);
            $errorDoc->setMediationNotAllowed();
            return;                       
        }
       
        // currently OPUS supports deposit of ZIP and TAR packages only
        $contentType = $request->getHeader('Content-Type');
        if (is_null($contentType) || $contentType === false || ($contentType != 'application/zip' && $contentType != 'application/tar')) {
            $errorDoc = new Sword_Model_ErrorDocument($request, $response);
            $errorDoc->setErrorContent();
            return;           
        }
        
        // check that package size does not exceed maximum upload size 
        $payload = $request->getRawBody();       
        if ($this->maxUploadSizeExceeded($payload)) {
            $errorDoc = new Sword_Model_ErrorDocument($request, $response);
            $errorDoc->setPayloadTooLarge();
            return;
        }
        
        // check that all import enrichment keys are present
        try {
            $additionalEnrichments = $this->getAdditionalEnrichments($userName, $request);
        } catch (Exception $ex) {
            $errorDoc = new Sword_Model_ErrorDocument($request, $response);
            $errorDoc->setMissingImportEnrichmentKey();
            return;
        }
        
        // compare checksums (if given in HTTP request header)
        $checksum = $additionalEnrichments->getChecksum();        
        if (!is_null($checksum)) {
            $checksumPayload = md5($payload);
            if (strcasecmp($checksum, $checksumPayload) != 0) {
                $errorDoc = new Sword_Model_ErrorDocument($request, $response);
                $errorDoc->setErrorChecksumMismatch($checksum, $checksumPayload);
                return;
            }
        }
                
        try {
            $packageHandler = new Sword_Model_PackageHandler($additionalEnrichments, $contentType);
            $statusDoc = $packageHandler->handlePackage($payload);
            if (is_null($statusDoc)) {
                // im Archiv befindet sich keine Datei opus.xml oder die Datei ist leer
                $errorDoc = new Sword_Model_ErrorDocument($request, $response);
                $errorDoc->setMissingXml();
                return;                
            }
            
            if ($statusDoc->noDocImported()) {
                // im Archiv befindet sich zwar ein nicht leeres opus.xml; es 
                // konnte aber kein Dokument erfolgreich importiert werden
                $errorDoc = new Sword_Model_ErrorDocument($request, $response);
                $errorDoc->setInternalFrameworkError();
                return;
            }
        } 
        catch (Application_Import_MetadataImportInvalidXmlException $ex) {
            $errorDoc = new Sword_Model_ErrorDocument($request, $response);
            $errorDoc->setInvalidXml();
            return;        
        } 
        catch (Exception $ex) {
            $errorDoc = new Sword_Model_ErrorDocument($request, $response);
            return;
        }
        
        $this->returnAtomEntryDocument($statusDoc, $request, $response, $userName);
    }
    
    private function returnAtomEntryDocument($statusDoc, $request, $response, $userName) {
        $atomDoc = $this->createAtomEntryDocument($statusDoc);
        $atomDoc->setResponse($request, $response, $this->getFullUrl(), $userName);        
    }
    
    /**
     * 
     * @param Application_Import_ImportStatusDocument $statusDoc
     */
    private function createAtomEntryDocument($statusDoc) {
        $atomEntryDoc = new Sword_Model_AtomEntryDocument();
        $atomEntryDoc->setEntries($statusDoc->getDocs());
        return $atomEntryDoc;
    }    
    
    private function maxUploadSizeExceeded($payload) {
        if (function_exists('mb_strlen')) {
            $size = mb_strlen($payload, '8bit');
        } else {
            $size = strlen($payload);
        }
        
        $maxUploadSize = (new Application_Util_MaxUploadSize())->getMaxUploadSizeInByte();
        if ($size > $maxUploadSize) {
            $log = Zend_Registry::get('Zend_Log');
            $log->warn('current package size ' . $size . ' exceeds the maximum upload size ' . $maxUploadSize);
            return true;
        }
        return false;
    }
    
    private function getAdditionalEnrichments($userName, $request) {
        $additionalEnrichments = new Application_Import_AdditionalEnrichments();
        if (!$additionalEnrichments->checkKeysExist()) {
            throw new Exception('at least one import specific enrichment key does not exist');
        }
        
        $additionalEnrichments->addUser($userName);
        $additionalEnrichments->addDate(gmdate('c'));
        
        $fileName = $request->getHeader('Content-Disposition');
        if (!is_null($fileName) && $fileName !== false) {
            $additionalEnrichments->addFile($fileName);
        }
        
        $checksum = $request->getHeader('Content-MD5');
        if (!is_null($checksum) && $checksum !== false) {
            $additionalEnrichments->addChecksum($checksum);
        }
        
        return $additionalEnrichments;        
    }

    private function getFullUrl() {
        $fullUrlHelper = new Application_View_Helper_FullUrl();
        $fullUrlHelper->setView(new Zend_View());
        return $fullUrlHelper->fullUrl();
    }

    private function return500($response) {
        $response->setHttpResponseCode(500);
        $response->appendBody("Method not allowed");        
    }
    
    public function indexAction() {
        $this->return500($this->getResponse());
    }

    public function getAction() {
        $this->return500($this->getResponse());
    }

    public function headAction() {
        $this->return500($this->getResponse());
    }

    public function putAction() {
        $this->return500($this->getResponse());
    }

    public function deleteAction() {
        $this->return500($this->getResponse());
    }
    
}
