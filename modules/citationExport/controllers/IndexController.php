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
 * @package     Module_CitationExport
 * @author      Sascha Szott <szott@zib.de>
 * @author      Pascal-Nicolas Becker <becker@zib.de>
 * @copyright   Copyright (c) 2008-2011, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class CitationExport_IndexController extends Controller_Action {

    /**
     * Output data to index view
     *
     * @return void
     *
     */
    public function indexAction() {
        $this->view->title = $this->view->translate('citationExport_modulename');
        $output = null;

        try {
            $document = $this->getDocument();
            $template = $this->getTemplateForDocument($document);
            $output = $this->getPlainOutput($document, $template);
        }
        catch (CitationExport_Model_Exception $e) {
            $this->view->output = $this->view->translate($e->getMessage());
            $this->getResponse()->setHttpResponseCode(400);
            return;
        }
                        
        $this->view->output = $output;
        $this->view->downloadUrl = $this->view->url(array('action' => 'download'), false, null);
    }

    /**
     * Output data as downloadable file
     *
     * @return void
     *
     */
    public function downloadAction() {
        $this->view->title = $this->view->translate('citationExport_modulename');
        $output = null;

        try {
            $document = $this->getDocument();
            $template = $this->getTemplateForDocument($document);
            $output = $this->getPlainOutput($document, $template);
        }
        catch (CitationExport_Model_Exception $e) {
            $this->view->output = $this->view->translate($e->getMessage());
            $this->getResponse()->setHttpResponseCode(400);
            return;
        }
        
        // Transform to HTML
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout()->disableLayout();

        // Send plain text response.
        $this->getResponse()->setHeader('Content-Type', 'text/plain; charset=UTF-8', true);

        $outputFormat = $this->getRequest()->getParam('output');
        $extension = null;
        switch ($outputFormat) {
            case 'bibtex':
                $extension = 'bib';
                break;
            case 'ris':
                $extension = 'ris';
                break;
            default:
                $extension = 'txt';
        }               
        $this->getResponse()->setHeader('Content-Disposition', 'attachment; filename=' . $outputFormat . '-' . $this->getRequest()->getParam('docId') . '.' . $extension, true);
        $this->getResponse()->setBody($output);
    }

    /**
     *
     * @param string $docId
     * @throws CitationExport_Module_Exception in case of an invalid parameter value
     *
     * @return Opus_Document
     */
    private function getDocument() {
        $docId = $this->getRequest()->getParam('docId');
        if (is_null($docId)) {
            throw new CitationExport_Model_Exception('invalid_docid');
        }

        $document = null;
        try {
            $document = new Opus_Document($docId);
        }
        catch (Opus_Model_NotFoundException $e) {
            throw new CitationExport_Model_Exception('invalid_docid', null, $e);
        }

        // check if document access is allowed
        // TODO document access check will be refactored in later releases
        new Util_Document($document);
        
        return $document;
    }

    /**
     *
     * @param Opus_Document $document
     * @throws CitationExport_Module_Exception in case of an invalid parameter value
     *
     * @return string
     */
    private function getTemplateForDocument($document) {
        $outputFormat = $this->getRequest()->getParam('output');
        if (is_null($outputFormat)) {
            throw new CitationExport_Model_Exception('invalid_format');
        }

        if (is_readable($this->view->getScriptPath('index') . DIRECTORY_SEPARATOR . $outputFormat . '_' . $document->getType() . '.xslt')) {
           return $outputFormat . '_' . $document->getType() . '.xslt';
        }
        if (is_readable($this->view->getScriptPath('index') . DIRECTORY_SEPARATOR . $outputFormat . '.xslt')) {
            return $outputFormat . '.xslt';
        }

        throw new CitationExport_Model_Exception('invalid_format');
    }

    /**
     * transform XML output to desired output format
     * 
     * @param Opus_Document $document Document that should be transformed
     * @param string $template XSLT stylesheet that should be applied
     * 
     * @return string document in the given output format as plain text
     */
     private function getPlainOutput($document, $template) {
        // Set up filter and get XML-Representation of filtered document.
        $filter = new Opus_Model_Filter;
        $filter->setModel($document);
        $xml = $filter->toXml();

        // Set up XSLT-Stylesheet
        $xslt = new DomDocument;
        $xslt->load($this->view->getScriptPath('index') . DIRECTORY_SEPARATOR . $template);

        // Set up XSLT-Processor
        try {
            $proc = new XSLTProcessor;
            $proc->setParameter('', 'url_prefix', $this->view->serverUrl() . $this->getRequest()->getBaseUrl());
            $proc->registerPHPFunctions();
            $proc->importStyleSheet($xslt);
        	
            return $proc->transformToXML($xml);
        }
        catch (Exception $e) {
            throw new Application_Exception($e->getMessage(), null, $e);
        }       
     }
}