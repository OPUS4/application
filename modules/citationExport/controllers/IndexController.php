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
 * @author      Pascal-Nicolas Becker <becker@zib.de>
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Main entry point for this module.
 *
 * @category    Application
 * @package     Module_CitationExport
 */
class CitationExport_IndexController extends Zend_Controller_Action {

    /**
     * Output data to index view
     *
     * @return void
     *
     */
    public function indexAction() {
        $docId = $this->getRequest()->getParam('docId');
        $outputFormat = $this->getRequest()->getParam('output');
        
        $output = $this->getPlainOutput($docId, $outputFormat);

        // Send output to view
        $this->view->title = $this->view->translate('citationExport_modulename');
        if ($output === false) $this->view->output = $this->view->translate('invalid_format');
        else {
        	$this->view->output = $output;
            $this->view->downloadUrl = $this->view->url(array('module' => 'citationExport', 'controller' => 'index', 'action' => 'download', 'docId' => $docId, 'output' => $outputFormat), false, null);
        }
    }

    /**
     * Output data as downloadable file
     *
     * @return void
     *
     */
    public function downloadAction() {
        $docId = $this->getRequest()->getParam('docId');
        $outputFormat = $this->getRequest()->getParam('output');
        
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
        
        $output = $this->getPlainOutput($docId, $outputFormat);

        // Transform to HTML
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout()->disableLayout();

        // Send plain text response.
        $this->getResponse()->setHeader('Content-Type', 'text/plain; charset=UTF-8', true);
        $this->getResponse()->setHeader('Content-Disposition', 'attachment; filename=' . $outputFormat . '-' . $docId . '.' . $extension, true);
        $this->getResponse()->setBody($output);
    }

    /**
     * transform XML output to desired output format
     * 
     * @param int $docId Document ID that should be transformed
     * @param string $outputFormat Output format the document should be transformed into
     * 
     * @return string document in the given output format as plain text, if the output format is not found, this method will return boolean false
     */
     public function getPlainOutput($docId, $outputFormat) {
     	// Load document
        $docId = $this->getRequest()->getParam('docId');
        $document = new Opus_Document($docId);

        // Set up filter and get XML-Representation of filtered document.
        $filter = new Opus_Model_Filter;
        $filter->setModel($document);
        $xml = $filter->toXml();

        // Set up XSLT-Stylesheet
        $xslt = new DomDocument;
        if (true === file_exists($this->view->getScriptPath('index') . '/' . $outputFormat . '.xslt')) {
            $template = $outputFormat . '.xslt';
        } else {
            return false;
        }
        $xslt->load($this->view->getScriptPath('index') . '/' . $template);

        // Set up XSLT-Processor
        try {
            $proc = new XSLTProcessor;
            
            $url_prefix_array = explode('/', $_SERVER["REQUEST_URI"]);
            $url_prefix = 'http://';
            $url_prefix .= $_SERVER["SERVER_NAME"];
            if ($_SERVER["SERVER_PORT"] !== '80') $url_prefix .= ':' . $_SERVER["SERVER_PORT"];
            foreach ($url_prefix_array as $up) {
            	if ($up !== 'citationExport' && $up !== '') {
            		$url_prefix .= '/' . $up;
            	}
            	// leave at position citationExport - its just to identify a prefix
            	if ($up === 'citationExport') break;
            }

            $proc->setParameter('', 'url_prefix', $url_prefix );
            $proc->registerPHPFunctions();
            $proc->importStyleSheet($xslt);
        	
            $transform = $proc->transformToXML($xml);
        }
        catch (Exception $e) {
        	$transform = $e->getMessage();
        }
        return    $transform;
     }
}