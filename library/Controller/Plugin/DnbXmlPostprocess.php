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
 * @package     Controller
 * @author      Ralf Claussnitzer (ralf.claussnitzer@slub-dresden.de)
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Xml output reshaping for weird DNB validation procedures.
 *
 * @category    Application
 * @package     Controller
 */
class Controller_Plugin_DnbXmlPostprocess extends Zend_Controller_Plugin_Abstract {


    /**
     * Add extra namespace declaration for each individual
     * metadata element to XML output.
     *
     * Actions are explicitly taken for module named 'oai'
     *
     * @param Zend_Controller_Request_Abstract $request
     * @return void
     */
    public function postDispatch(Zend_Controller_Request_Abstract $request) {
        // only contaminate OAI output
        if ('oai' !== $request->getModuleName()) {
            return;
        }
        $front = Zend_Controller_Front::getInstance();

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
     * not already present
     *
     * @param DOMDocument $dom       DOMDocument containing the specified element
     * @param string      $element   Name of the element to augment
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
