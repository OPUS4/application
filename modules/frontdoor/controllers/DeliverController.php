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
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 *
 */
class Frontdoor_DeliverController extends Controller_Action {

    public function indexAction() {
        
        $docId = $this->_getParam('docId', null);
        $path = $this->_getParam('file', null);

        $realm = Opus_Security_Realm::getInstance();
        $file_model = new Frontdoor_Model_File($docId, $path);
        $file_object = null;
        
        try {
            $file_object = $file_model->getFileObject($realm);
        } catch(Frontdoor_Model_FrontdoorDeliveryException $e) {
            $this->handleDeliveryError($e);
            return;
        }

        $full_filename = $file_object->getPath();
        $base_filename = basename($full_filename);

        $this->disableViewRendering();

        $this->getResponse()
                ->clearAllHeaders()
                ->setHeader('Content-Disposition', "attachment; filename=$base_filename", true)
                ->setHeader('Content-type', $file_object->getMimeType(), true)
                ->setHeader('Cache-Control', 'private', true)
                ->setHeader('Pragma', 'cache', true);

        $this->_helper->SendFile->setLogger( Zend_Registry::get('Zend_Log') );
        try {
            $this->_helper->SendFile($full_filename);
        } catch (Exception $e) {
            $this->logError($e);
        }

        return;
    }

    private function logError($exception) {
        $logger = Zend_Registry::get("Zend_Log");
        $logger->err($exception);
    }

    private function handleDeliveryError($exception) {
        $this->view->translateKey = $exception->getTranslateKey();
        $this->view->code = $exception->getCode();
        $this->render('error');
    }

    private function disableViewRendering() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
    }
}