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
     * Just to be there. No actions taken.
     *
     * @return void
     *
     */
    public function indexAction() {
        // Load document
        $docId = $this->getRequest()->getParam('docId');
        $document = new Opus_Document($docId);

        // Set up filter and get XML-Representation of filtered document.
        $type = new Opus_Document_Type($document->getType());
        $filter = new Opus_Model_Filter;
        $filter->setModel($document);
        $xml = $filter->toXml();

        // Set up XSLT-Stylesheet
        $xslt = new DomDocument;
        $requestData = $this->_request->getParams();
        $outputFormat = null;
        if (true === isset($requestData['output'])) $outputFormat = $requestData['output'];
        if (true === file_exists($this->view->getScriptPath('index') . '/' . $outputFormat . '.xslt')) {
            $template = $outputFormat . '.xslt';
        } else {
            $this->view->title = 'Citation Export (Export bibliographischer Daten)';
            $this->view->output = $this->view->translate('invalid_format');
            return;
        }
        $xslt->load($this->view->getScriptPath('index') . '/' . $template);

        // Set up XSLT-Processor
        $proc = new XSLTProcessor;
        $proc->registerPHPFunctions();
        $proc->importStyleSheet($xslt);

        // Transform to HTML
        	    $this->_helper->viewRenderer->setNoRender(true);
                $this->_helper->layout()->disableLayout();

            	// Send plain text response.
                $this->getResponse()->setHeader('Content-Type', 'text/plain; charset=UTF-8', true);
                $this->getResponse()->setBody($proc->transformToXML($xml));
        #echo $xml->saveXml();
        #echo $proc->transformToXML($xml);
        #$this->getResponse()->setHttpResponseCode(200);
        #$this->getResponse()->setBody($document->toXml()->saveXml());
        #$this->getResponse()->setBody($proc->transformToXML($xml));
    }

}