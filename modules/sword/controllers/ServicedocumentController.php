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

use Opus\Common\Config;

class Sword_ServicedocumentController extends Zend_Rest_Controller
{
    public function init()
    {
        $this->getHelper('Layout')->disableLayout();
        $this->getHelper('ViewRenderer')->setNoRender();
    }

    public function indexAction()
    {
        $this->getAction();
    }

    /**
     * @throws Zend_Auth_Adapter_Exception
     *
     * TODO BUG function is called get... and does not return anything
     */
    public function getAction()
    {
        $request  = $this->getHttpRequest();
        $response = $this->getHttpResponse();

        $response->setHeader('Content-Type', 'text/xml; charset=UTF-8', true);

        $accessAllowed = Application_Security_BasicAuthProtection::accessAllowed($request, $response);
        if (! $accessAllowed) {
            $this->setErrorDocument($response);
            return;
        }
        $this->setServiceDocument($response);
    }

    /**
     * @param Zend_Controller_Response_Abstract $response
     * @throws DOMException
     */
    private function setErrorDocument($response)
    {
        $response->setHttpResponseCode(403);
        $domDocument = new DOMDocument();
        $element     = $domDocument->createElement('error', 'Access to SWORD module is forbidden.');
        $domDocument->appendChild($element);
        $response->setBody($domDocument->saveXML());
    }

    /**
     * @param Zend_Controller_Response_Abstract $response
     */
    private function setServiceDocument($response)
    {
        $fullUrl         = $this->view->fullUrl();
        $serviceDocument = new Sword_Model_ServiceDocument($fullUrl);
        $domDocument     = $serviceDocument->getDocument();

        $config         = Config::get();
        $prettyPrinting = isset($config->prettyXml) && filter_var($config->prettyXml, FILTER_VALIDATE_BOOLEAN);
        if ($prettyPrinting) {
            $domDocument->preserveWhiteSpace = false;
            $domDocument->formatOutput       = true;
        }

        $response->setBody($domDocument->saveXml());
    }

    public function deleteAction()
    {
        $this->return500($this->getResponse());
    }

    public function headAction()
    {
        $this->return500($this->getResponse());
    }

    public function postAction()
    {
        $this->return500($this->getResponse());
    }

    public function putAction()
    {
        $this->return500($this->getResponse());
    }

    /**
     * @param Zend_Controller_Response_Abstract $response
     */
    private function return500($response)
    {
        $response->setHttpResponseCode(500);
        $response->appendBody("Method not allowed");
    }

    /**
     * @return Zend_Controller_Request_Http|Zend_Controller_Request_Abstract
     *
     * TODO LAMINAS function exists to typecast
     */
    protected function getHttpRequest()
    {
        return $this->getRequest();
    }

    /**
     * @return Zend_Controller_Response_Http|Zend_Controller_Response_Abstract
     *
     * TODO LAMINAS function exists to typecast
     */
    protected function getHttpResponse()
    {
        return $this->getResponse();
    }
}
