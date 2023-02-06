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
 * @copyright   Copyright (c) 2016, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\Log;
use Opus\Import\AdditionalEnrichments;
use Opus\Import\ImportStatusDocument;
use Opus\Import\Xml\MetadataImportInvalidXmlException;

/**
 * TODO use OPUS 4 base class?
 * TODO too much code in this controller
 * TODO change AdditionalEnrichments into something like ImportInfo and make it easy to access properties like "user"
 */
class Sword_DepositController extends Zend_Rest_Controller
{
    public function init()
    {
        $this->getHelper('Layout')->disableLayout();
        $this->getHelper('ViewRenderer')->setNoRender();
    }

    /**
     * TODO This function does too much.
     */
    public function postAction()
    {
        $request  = $this->getRequest();
        $response = $this->getResponse();

        $userName = Application_Security_BasicAuthProtection::accessAllowed($request, $response);
        if (! $userName) {
            $errorDoc = new Sword_Model_ErrorDocument($request, $response);
            $errorDoc->setForbidden();
            return;
        }

        // mediated deposit is currently not supported by OPUS
        $mediatedDeposit = $request->getHeader('X-On-Behalf-Of');
        if ($mediatedDeposit !== null && $mediatedDeposit !== false) {
            $errorDoc = new Sword_Model_ErrorDocument($request, $response);
            $errorDoc->setMediationNotAllowed();
            return;
        }

    // currently OPUS supports deposit of ZIP and TAR packages only
        try {
            $contentType    = $request->getHeader('Content-Type');
            $packageHandler = new Sword_Model_PackageHandler($contentType);
        } catch (Exception $e) {
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
            $packageHandler->setAdditionalEnrichments($additionalEnrichments);
        } catch (Exception $ex) {
            $errorDoc = new Sword_Model_ErrorDocument($request, $response);
            $errorDoc->setMissingImportEnrichmentKey();
            return;
        }

    // compare checksums (if given in HTTP request header)
        $checksum = $additionalEnrichments->getChecksum();
        if ($checksum !== null) {
            $checksumPayload = md5($payload);
            if (strcasecmp($checksum, $checksumPayload) !== 0) {
                $errorDoc = new Sword_Model_ErrorDocument($request, $response);
                $errorDoc->setErrorChecksumMismatch($checksum, $checksumPayload);
                return;
            }
        }

    // TODO data is stored again within handlePackage - that should be avoied
        $filename = $this->generatePackageFileName($additionalEnrichments);
        $config   = Application_Configuration::getInstance();
        $filePath = $config->getWorkspacePath() . 'import/' . $filename;
        file_put_contents($filePath, $payload);

        $errorDoc = null;

        try {
            $statusDoc = $packageHandler->handlePackage($payload);
            if ($statusDoc === null) {
                // im Archiv befindet sich keine Datei opus.xml oder die Datei ist leer
                $errorDoc = new Sword_Model_ErrorDocument($request, $response);
                $errorDoc->setMissingXml();
            } elseif ($statusDoc->noDocImported()) {
                // im Archiv befindet sich zwar ein nicht leeres opus.xml; es
                // konnte aber kein Dokument erfolgreich importiert werden
                $errorDoc = new Sword_Model_ErrorDocument($request, $response);
                $errorDoc->setInternalFrameworkError();
            }
        } catch (MetadataImportInvalidXmlException $ex) {
            $errorDoc = new Sword_Model_ErrorDocument($request, $response);
            $errorDoc->setInvalidXml();
        } catch (Exception $ex) {
            $errorDoc = new Sword_Model_ErrorDocument($request, $response);
        }

        if ($errorDoc !== null) {
            return;
        }

    // cleanup file after successful import
        unlink($filePath);

        $this->returnAtomEntryDocument($statusDoc, $request, $response, $userName);
    }

    /**
     * @param ImportStatusDocument          $statusDoc
     * @param Zend_Controller_Request_Http  $request
     * @param Zend_Controller_Response_Http $response
     * @param string                        $userName
     */
    private function returnAtomEntryDocument($statusDoc, $request, $response, $userName)
    {
        $atomDoc = $this->createAtomEntryDocument($statusDoc);
        $atomDoc->setResponse($request, $response, $this->getFullUrl(), $userName);
    }

    /**
     * @param ImportStatusDocument $statusDoc
     * @return Sword_Model_AtomEntryDocument
     */
    private function createAtomEntryDocument($statusDoc)
    {
        $atomEntryDoc = new Sword_Model_AtomEntryDocument();
        $atomEntryDoc->setEntries($statusDoc->getDocs());
        return $atomEntryDoc;
    }

    /**
     * @param string $payload
     * @return bool
     * @throws Zend_Exception
     */
    private function maxUploadSizeExceeded($payload)
    {
        // retrieve number of bytes (not characters) of HTTP payload (SWORD package)
        $size = mb_strlen($payload, '8bit');

        $maxUploadSize = (new Application_Configuration_MaxUploadSize())->getMaxUploadSizeInByte();
        if ($size > $maxUploadSize) {
            $log = Log::get();
            $log->warn('current package size ' . $size . ' exceeds the maximum upload size ' . $maxUploadSize);
            return true;
        }
        return false;
    }

    /**
     * @param string                       $userName
     * @param Zend_Controller_Request_Http $request
     * @return AdditionalEnrichments
     */
    private function getAdditionalEnrichments($userName, $request)
    {
        $additionalEnrichments = new AdditionalEnrichments();

        $additionalEnrichments->addUser($userName);

        $fileName = $request->getHeader('Content-Disposition');
        if ($fileName !== null && $fileName !== false) {
            $additionalEnrichments->addFile($fileName);
        }

        $checksum = $request->getHeader('Content-MD5');
        if ($checksum !== null && $checksum !== false) {
            $additionalEnrichments->addChecksum($checksum);
        }

        return $additionalEnrichments;
    }

    /**
     * @return string
     */
    private function getFullUrl()
    {
        $fullUrlHelper = new Application_View_Helper_FullUrl();
        $fullUrlHelper->setView(new Zend_View());
        return $fullUrlHelper->fullUrl();
    }

    /**
     * @param Zend_Controller_Response_Abstract $response
     */
    private function return500($response)
    {
        $response->setHttpResponseCode(500);
        $response->appendBody("Method not allowed");
    }

    public function indexAction()
    {
        $this->return500($this->getResponse());
    }

    public function getAction()
    {
        $this->return500($this->getResponse());
    }

    public function headAction()
    {
        $this->return500($this->getResponse());
    }

    public function putAction()
    {
        $this->return500($this->getResponse());
    }

    public function deleteAction()
    {
        $this->return500($this->getResponse());
    }

    /**
     * Generates a name for storing the package as a file.\
     *
     * @param AdditionalEnrichments $importInfo
     * @return string
     */
    protected function generatePackageFileName($importInfo)
    {
        $filename = $importInfo->getFileName();
        $checksum = $importInfo->getChecksum();

        return "$checksum-$filename";
    }
}
