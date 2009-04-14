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
 * @package    Controller
 * @author     Henning Gerhardt (henning.gerhardt@slub-dresden.de)
 * @copyright  Copyright (c) 2009, OPUS 4 development team
 * @license    http://www.gnu.org/licenses/gpl.html General Public License
 * @version    $Id$
 */


/**
 * Generic controller for REST actions. Actions itself are programmed out in specific controllers.
 *
 */
class Controller_Rest extends Zend_Controller_Action {

    protected $_hostname = '';

    protected $_protocol = 'http://';

    /**
     * Holds request data for later processing
     *
     * @var array
     */
    protected $requestData = null;

    /**
     * General message for not implemented REST requests.
     *
     * @return void
     */
    protected function _notImplemented() {
        $xml = new DOMDocument('1.0', 'utf-8');
        $xml->formatOutput = true;
        $error = $xml->createElement('error');
        $error->setAttribute('message', 'Required REST method not implemented.');
        $xml->appendChild($error);
        $this->getResponse()->setHttpResponseCode(501);
        $this->getResponse()->setBody($xml->saveXML());
    }

    /**
     * Overide standard init method with default values for REST.
     *
     * @return void
     */
    public function init() {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout()->disableLayout();
        $this->requestData = $this->getRequest()->getParams();
        $this->getResponse()->setHeader('Content-Type', 'text/xml; charset=UTF-8', true);

        $this->_hostname = $_SERVER['HTTP_HOST'];

    }

    /**
     * Handling for get requests.
     *
     * @return void
     */
    public function getAction() {
        $this->_notImplemented();
    }

    /**
     * Handling for post requests.
     *
     * @return void
     */
    public function postAction() {
        $this->_notImplemented();
    }

    /**
     * Handling for delete requests.
     *
     * @return void
     */
    public function deleteAction() {
        $this->_notImplemented();
    }

    /**
     * Handling for put requests.
     *
     * @return void
     */
    public function putAction() {
        $this->_notImplemented();
    }

}