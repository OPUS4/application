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
 * @package    Module_Webapi
 * @author     Henning Gerhardt (henning.gerhardt@slub-dresden.de)
 * @copyright  Copyright (c) 2009, OPUS 4 development team
 * @license    http://www.gnu.org/licenses/gpl.html General Public License
 * @version    $Id$
 */

/**
 * Methods for REST handling of resource document.
 */
class Document extends Response {

    /**
     * Returns a xml string for a specific document.
     *
     * @param mixed $docId Requested document id.
     * @return string
     */
    public function getDocument($docId) {

        $docId = (int) $docId;
        try {
            $doc = new Opus_Document($docId);
            $xml2 = new Opus_Model_Xml();
            $xml2->setModel($doc);
            $xml2->excludeEmptyFields();
            $resourceMap = array(
                'Opus_Licence' => 'licence',
                'Opus_Person' => 'person',
                'Opus_File' => 'file',
            );
            $xml2->setResourceNameMap($resourceMap);
            $view = Zend_Layout::getMvcInstance()->getView();
            $baseUri = $this->_protocol . $this->_hostname . $view->url(array('module' => 'webapi'), 'default', true);
            $xml2->setXlinkBaseUri($baseUri);
            $xml = $xml2->getDomDocument();
            // count access to a document
            $statistic = Opus_Statistic_LocalCounter::getInstance();
            $statistic->countFrontdoor($docId);
            // add statistic information to xml structure
            $statisticXml = $xml->createElement('Statistic');
            $statisticXml->setAttribute('Frontdoor', $statistic->readTotal($docId, 'frontdoor'));
            $statisticXml->setAttribute('Files', $statistic->readTotal($docId, 'files'));
            $opusDocument = $xml->getElementsByTagName('Opus_Document')->item(0);
            $opusDocument->appendChild($statisticXml);
        } catch (Opus_Model_Exception $e) {
            $this->setError('An error occurs during getting informations. Error reason: ' . $e->getMessage(), 404);
            $xml = $this->_xml;
        }
        return $xml->saveXML();
    }

    /**
     * Retuns a xml list of available documents.
     *
     * @return string
     */
    public function getAllDocuments() {

        $xml = $this->_xml;

        $resultlist = $xml->createElement('DocumentList');
        $this->_root->appendChild($resultlist);

        $view = Zend_Layout::getMvcInstance()->getView();
        $url = $this->_protocol . $this->_hostname . $view->url(array('controller' => 'document', 'module' => 'webapi'), 'default', true);
        foreach (Opus_Document::getAllIds() as $docId) {
            $element = $xml->createElement('Document');
            $element->setAttribute('xlink:type', 'simple');
            $element->setAttribute('xlink:href', $url . '/' . $docId);
            $element->setAttribute('nr', $docId);
            $resultlist->appendChild($element);
        }
        return $xml->saveXML();
    }

    /**
     * Deletes a document. Returns only a response code!
     *
     * @param mixed $docId Document id of deleting document.
     * @return void
     */
    public function deleteDocument($docId) {

        $docId = (int) $docId;
        try {
            $doc = new Opus_Document($docId);
            $doc->delete();
            $this->setResponseCode(204);
        } catch (Exception $e) {
            $this->setResponseCode(404);
        }
    }

}