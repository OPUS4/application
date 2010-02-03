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
        if ($outputFormat === 'bibtex' && file_exists($this->view->getScriptPath('index') . '/' . $outputFormat . "_" . $document->getType() . '.xslt')) $outputFormat .= "_" . $document->getType();
        if (true === file_exists($this->view->getScriptPath('index') . '/' . $outputFormat . '.xslt')) {
            $template = $outputFormat . '.xslt';
        } else {
            $this->view->title = 'Citation Export (Export bibliographischer Daten)';
            $this->view->output = $this->view->translate('invalid_format');
            return;
        }
        /*
         * different bibtex types - they need seperate stylesheets...
book = monograph
    A book with an explicit publisher.
    Required fields: author/editor, title, publisher, year
    Optional fields: volume, series, address, edition, month, note, key
incollection = monograph_section
    A part of a book having its own title.
    Required fields: author, title, booktitle, year
    Optional fields: editor, pages, organization, publisher, address, month, note, key
inproceedings = conference_item
    An article in a conference proceedings.
    Required fields: author, title, booktitle, year
    Optional fields: editor, series, pages, organization, publisher, address, month, note, key
manual = manual
    Technical documentation.
    Required fields: title
    Optional fields: author, organization, address, edition, month, year, note, key
mastersthesis = master_thesis
    A Master's thesis.
    Required fields: author, title, school, year
    Optional fields: address, month, note, key
misc = other
    For use when nothing else fits.
    Required fields: none
    Optional fields: author, title, howpublished, month, year, note, key
phdthesis = doctoral_thesis
    A Ph.D. thesis.
    Required fields: author, title, school, year
    Optional fields: address, month, note, key
proceedings = conference
    The proceedings of a conference.
    Required fields: title, year
    Optional fields: editor, publisher, organization, address, month, note, key
techreport = report
    A report published by a school or other institution, usually numbered within a series.
    Required fields: author, title, institution, year
    Optional fields: type, number, address, month, note, key
    */
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