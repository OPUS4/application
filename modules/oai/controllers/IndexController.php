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
 * @category    Application
 * @package     Module_Oai
 * @author      Felix Ostrowski <ostrowski@hbz-nrw.de>
 * @copyright   Copyright (c) 2009, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Main entry point for this module.
 *
 * @category    Application
 * @package     Module_Oai
 */
class Oai_IndexController extends Zend_Controller_Action {

    /**
     * Holds xml representation of document information to be processed.
     *
     * @var DomDocument  Defaults to null.
     */
    protected $_xml = null;

    /**
     * Holds the stylesheet for the transformation.
     *
     * @var DomDocument  Defaults to null.
     */
    protected $_xslt = null;

    /**
     * Holds the xslt processor.
     *
     * @var DomDocument  Defaults to null.
     */
    protected $_proc = null;

    /**
     * Do some initialization on startup of every action
     *
     * @return void
     */
    public function init()
    {
        // Module outputs plain Xml, so rendering and layout are disabled.
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout()->disableLayout();

        // Initialize member variables.
        $this->_xml = new DomDocument;
        $this->_xslt = new DomDocument;
        $this->_xslt->load($this->view->getScriptPath('index') . '/oai-pmh.xslt');
        $this->_proc = new XSLTProcessor;
        $this->_proc->importStyleSheet($this->_xslt);
    }

    /**
     * Entry point for all OAI-PMH requests.
     *
     * @return void
     */
    public function indexAction() {
        $verb = $this->getRequest()->getParam('verb');
        switch($verb) {
            case 'Identify':
                $this->_forward('badverb');
                //$this->_forward('identify');
                break;
            case 'ListIdentifiers':
                $this->_forward('badverb');
                //$this->_forward('listidentifiers');
                break;
            case 'ListMetadataFormats':
                $this->_forward('badverb');
                //$this->_forward('listmetadataformats');
                break;
            case 'ListRecords':
                $this->_forward('listrecords');
                break;
            case 'GetRecord':
                $this->_forward('getrecord');
                break;
            case 'ListSets':
                $this->_forward('badverb');
                //$this->_forward('listsets');
                break;
            default:
                $this->_forward('badverb');
        }
    }

    /**
     * Implements response for OAI-PMH verb 'GetRecord'.
     *
     * @return void
     */
    public function getrecordAction() {
        // TODO: Identifier should reference Urn, not Id!
        $identifier = $this->getRequest()->getParam('identifier');
        $metadataPrefix = $this->getRequest()->getParam('metadataPrefix');
        $document = new Opus_Model_Document($identifier);
        $this->_proc->setParameter('', 'oai_metadataPrefix', $metadataPrefix);
        $this->_xml->loadXml('<Document>' . $document->toXml() . '</Document>');
        $this->_sendOaiResponse('GetRecord');
    }

    /**
     * Implements response for OAI-PMH verb 'Identify'.
     *
     * @return void
     */
    public function identifyAction() {

    }

    /**
     * Implements response for OAI-PMH verb 'ListIdentifiers'.
     *
     * @return void
     */
    public function listidentifiersAction() {

    }

    /**
     * Implements response for OAI-PMH verb 'ListMetadataFormats'.
     *
     * @return void
     */
    public function listmetadataformatsAction() {

    }

    /**
     * Implements response for OAI-PMH verb 'ListRecords'.
     *
     * @return void
     */
    public function listrecordsAction() {
        $documents = Opus_Model_Document::getAll();
        $xml = '<Documents>';
        foreach ($documents as $document) {
            $xml .= '<Document>' . $document->toXml() . '</Document>';
        }
        $xml .= '</Documents>';
        $this->_xml->loadXml($xml);
        $metadataPrefix = $this->getRequest()->getParam('metadataPrefix');
        $this->_proc->setParameter('', 'oai_metadataPrefix', $metadataPrefix);
        $this->_sendOaiResponse('ListRecords');
    }

    /**
     * Implements response for OAI-PMH verb 'ListSets'.
     *
     * @return void
     */
    public function listsetsAction() {

    }

    /**
     * Implements bad verb for OAI-PMH.
     *
     * @return void
     */
    public function badverbAction() {
        $verb = $this->getRequest()->getParam('verb');
        $this->_sendOaiResponse($verb);
    }

    /**
     * Sends an OAI-PMH response.
     *
     * @return void
     */
    private function _sendOaiResponse($requestVerb) {

        // Set XSLTProcessor parameters.
        $this->_proc->setParameter('', 'dateTime', date('c'));
        $this->_proc->setParameter('', 'oai_verb', $requestVerb);

        // Send Xml response.
        $this->getResponse()->setHeader('Content-Type', 'text/xml; charset=UTF-8', true);
        $this->getResponse()->setBody($this->_proc->transformToXML($this->_xml));
    }

}
