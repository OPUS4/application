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
class Sword_ServicedocumentController extends Zend_Rest_Controller {
    
    public function init() {
        $this->getHelper('Layout')->disableLayout();
        $this->getHelper('ViewRenderer')->setNoRender();
    }
    
    public function indexAction() {
        $this->getAction();
    }

    public function getAction() {
        $request = $this->getRequest();
        $response = $this->getResponse();
        
        $response->setHeader('Content-Type', 'text/xml; charset=UTF-8', true);
        
        $accessAllowed = Application_Security_BasicAuthProtection::accessAllowed($request, $response);
        if (!$accessAllowed) {
            $this->setErrorDocument($response);
            return;
        }
        $this->setServiceDocument($response);
    }

    private function setErrorDocument($response) {
        $response->setHttpResponseCode(403);
        $domDocument = new DOMDocument();
        $element = $domDocument->createElement('error', 'Access to SWORD module is forbidden.');
        $domDocument->appendChild($element);
        $response->setBody($domDocument->saveXML());        
    }
    
    private function setServiceDocument($response) {
        $serviceDocument = new Sword_Model_ServiceDocument($this->getFullUrl());
        $domDocument = $serviceDocument->getDocument();
        
        $config = Zend_Registry::get('Zend_Config');
        $prettyPrinting = $config->prettyXml;
        if ($prettyPrinting == 'true') {      
            $domDocument->preserveWhiteSpace = false;
            $domDocument->formatOutput = true;            
        }        
        
        $response->setBody($domDocument->saveXml());        
    }
    
    private function getFullUrl() {
        $fullUrlHelper = new Application_View_Helper_FullUrl();
        $fullUrlHelper->setView(new Zend_View());
        return $fullUrlHelper->fullUrl();
    }
    
    public function deleteAction() {
        $this->return500($this->getResponse());
    }

    public function headAction() {
        $this->return500($this->getResponse());
    }

    public function postAction() {
        $this->return500($this->getResponse());
    }

    public function putAction() {
        $this->return500($this->getResponse());
    }

    private function return500($response) {
        $response->setHttpResponseCode(500);
        $response->appendBody("Method not allowed");        
    }
    
}
