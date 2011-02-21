<?php
/**
 * LICENCE
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This code is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * @category    Application
 * @author      Ralf Claussnitzer (ralf.claussnitzer@slub-dresden.de)
 * @copyright   Copyright (c) 2009-2010
 *              Saechsische Landesbibliothek - Staats- und Universitaetsbibliothek Dresden (SLUB)
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id: DnbXmlPostprocess.php 5765 2010-06-07 14:15:00Z claussni $
 */

/**
 * Xml output reshaping for weird DNB validation procedures.
 *
 * @category    Application
 * @package     Controller
 */
class Oai_Plugin_Controller_DnbXmlPostprocess extends Zend_Controller_Plugin_Abstract {

    /**
     * Add extra namespace declaration for each individual 
     * metadata element to XML output.
     *
     * Actions are explicitly taken for module named 'oai'.
     *
     * @param  Zend_Controller_Request_Abstract $request
     * @return void
     */
    public function postDispatch(Zend_Controller_Request_Abstract $request) {
        return;
        
        // only contaminate OAI output :|
        if ('oai' !== $request->getModuleName()) {
            return;
        }

        $front = Zend_Controller_Front::getInstance();

        // check for action exceptions
        if ($front->getResponse()->isException()) {
            return;
        }

        // check for empty output
        $body = $front->getResponse()->getBody();
        if (true === empty($body)) {
            return;
        }

        // build xml document
        $dom = new DOMDocument('1.0', 'utf-8');
        $dom->loadXml($front->getResponse()->getBody());  

        // patch xmlns:xsi namespace attribute
        $this->_addXsiNamespaceAttribute($dom, 'xMetaDiss');
        $this->_addXsiNamespaceAttribute($dom, 'epicur');

        $front->getResponse()->setBody($dom->saveXML());
    }

    /**
     * Patch XML elements with extra namespace declaration if
     * not already present.
     *
     * @param DOMDocument $dom     DOMDocument containing the specified element
     * @param string      $element Name of the element to augment
     */
    private function _addXsiNamespaceAttribute(DOMDocument $dom, $element) {
        $elements = $dom->getElementsByTagName($element);
        foreach ($elements as $domElement) {
            if (false === $domElement->hasAttribute('xmlns:xsi')) {
                $attr = $dom->createAttribute('xmlns:xsi');
                $text = $dom->createTextNode('http://www.w3.org/2001/XMLSchema-instance');
                $attr->appendChild($text);
                $domElement->appendChild($attr);
            }
        }
    }

}
