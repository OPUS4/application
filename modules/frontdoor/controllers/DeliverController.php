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
 * @package     Module_Frontdoor
 * @author      Felix Ostrowski <ostrowski@hbz-nrw.de>
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2017, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 *
 * Controller for handling file downloads in the frontdoor.
 */
class Frontdoor_DeliverController extends Application_Controller_Action {

    /**
     * Handles file downloads.
     */
    public function indexAction() {

        $docId = $this->_getParam('docId', null);
        $path = $this->_getParam('file', null);

        $realm = Opus_Security_Realm::getInstance();

        $fileModel = null;

        try {
            $fileModel = new Frontdoor_Model_File($docId, $path);
        }
        catch (Frontdoor_Model_FrontdoorDeliveryException $e) {
            $this->handleDeliveryError($e);
            return;
        }

        $fileObject = null;

        try {
            $fileObject = $fileModel->getFileObject($realm);
        }
        catch(Frontdoor_Model_FrontdoorDeliveryException $e) {
            $this->handleDeliveryError($e);
            return;
        }

        if (!$fileObject->exists()) {
            $this->handleDeliveryError(new Frontdoor_Model_FileNotFoundException());
            return;
        }

        $fullFilename = $fileObject->getPath();
        $baseFilename = basename($fullFilename);
        $baseFilename = self::quoteFileName($baseFilename);

        $this->disableViewRendering();

        $mimeType = $fileObject->getMimeType();
        $contentDisposition = $this->_helper->fileTypes->getContentDisposition($mimeType);

        $this->getResponse()
                ->clearAllHeaders()
                ->setHeader('Content-Disposition', "$contentDisposition; filename=\"$baseFilename\"", true)
                ->setHeader('Content-type', $mimeType, true)
                ->setHeader('Cache-Control', 'private', true)
                ->setHeader('Pragma', 'cache', true);

        $this->_helper->SendFile->setLogger($this->getLogger());
        try {
            $this->_helper->SendFile($fullFilename);
        }
        catch (Exception $e) {
            $this->logError($e);
            $response = $this->getResponse();
            $response->clearAllHeaders();
            $response->clearBody();
            $response->setHttpResponseCode(500);
        }

        return;
    }

    /**
     * Replace "tspecials" (RFC 2183, RFC 2045) for clean HTTP headers.
     * See also ticket OPUSVIER-2455.
     *
     * tspecials: [\(\)<>@,;:\\\"\/\[\]\?=\s]
     *
     * @param  string $filename
     * @return string quoted/mime-encoded
     *
     * TODO move to model class - unit test
     */
    public static function quoteFileName($filename) {
        if (preg_match('/[^A-Za-z0-9_., -]/', $filename)) {
            return '=?UTF-8?B?'.base64_encode($filename).'?=';
        }
        return $filename;
    }

    private function logError($exception) {
        $this->getLogger()->err($exception);
    }

    private function handleDeliveryError($exception) {
        $this->view->translateKey = $exception->getTranslateKey();
        $this->render('error');
    }

}
