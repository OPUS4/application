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
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\Document;
use Opus\Common\DocumentInterface;
use Opus\Common\Model\NotFoundException;

class CitationExport_Model_Helper extends Application_Model_Abstract
{
    /** @var string */
    private $baseUrl;

    /** @var string */
    private $scriptPath;

    /**
     * @param string $baseUrl
     * @param string $scriptPath
     */
    public function __construct($baseUrl, $scriptPath)
    {
        $this->baseUrl    = $baseUrl;
        $this->scriptPath = $scriptPath;
    }

    /**
     * @param Zend_Controller_Request_Abstract $request
     * @return string
     * @throws Application_Exception
     * @throws CitationExport_Model_Exception
     */
    public function getOutput($request)
    {
        $outputFormat = $request->getParam('output');

        $document = $this->getDocument($request);

        $template = $this->getTemplateForDocument($document, $outputFormat);

        return $this->getPlainOutput($document, $template);
    }

    /**
     * @param Zend_Controller_Request_Abstract $request
     * @throws CitationExport_Model_Exception In case of an invalid parameter value.
     * @return DocumentInterface
     */
    public function getDocument($request)
    {
        $docId = $request->getParam('docId');
        if ($docId === null) {
            throw new CitationExport_Model_Exception('invalid_docid');
        }

        $document = null;
        try {
            $document = Document::get($docId);
        } catch (NotFoundException $e) {
            throw new CitationExport_Model_Exception('invalid_docid', 0, $e);
        }

        // check if document access is allowed
        // TODO document access check will be refactored in later releases
        new Application_Util_Document($document);

        return $document;
    }

    /**
     * Returns file extension for output format.
     *
     * @param string $outputFormat
     * @return string
     */
    public function getExtension($outputFormat)
    {
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

        return $extension;
    }

    /**
     * @param DocumentInterface $document
     * @param string            $outputFormat
     * @throws CitationExport_Model_Exception In case of an invalid parameter value.
     * @return string
     */
    public function getTemplateForDocument($document, $outputFormat)
    {
        if ($outputFormat === null) {
            throw new CitationExport_Model_Exception('invalid_format');
        }

        $stylesheetsAvailable = $this->getAvailableStylesheets();

        // check for document type specific stylesheet
        $pos = array_search($outputFormat . '_' . $document->getType(), $stylesheetsAvailable);
        if ($pos !== false) {
            return $stylesheetsAvailable[$pos] . '.xslt';
        }

        // check for generic stylesheet for format
        $pos = array_search($outputFormat, $stylesheetsAvailable);
        if ($pos !== false) {
            return $stylesheetsAvailable[$pos] . '.xslt';
        }

        // no applicable stylesheet found
        throw new CitationExport_Model_Exception('invalid_format');
    }

    /**
     * @return array
     */
    public function getAvailableStylesheets()
    {
        $stylesheetsAvailable = [];

        $dir = new DirectoryIterator($this->getScriptPath());

        foreach ($dir as $file) {
            if ($file->isFile() && $file->getExtension() === 'xslt' && $file->isReadable()) {
                array_push($stylesheetsAvailable, $file->getBasename('.xslt'));
            }
        }

        return $stylesheetsAvailable;
    }

    /**
     * @return string
     */
    public function getScriptPath()
    {
        return $this->scriptPath;
    }

    /**
     * transform XML output to desired output format
     *
     * @param DocumentInterface $document Document that should be transformed
     * @param string            $template XSLT stylesheet that should be applied
     * @return string document in the given output format as plain text
     */
    public function getPlainOutput($document, $template)
    {
        $xml = $document->toXml();

        // Set up XSLT-Stylesheet
        $xslt = new DOMDocument();
        $xslt->load($this->getScriptPath() . DIRECTORY_SEPARATOR . $template);

        // find Enrichment that should be included in bibtex-output as note
        // TODO document this feature
        $enrichmentNote = null;
        $config         = $this->getConfig();
        if (
            isset($config->citationExport->bibtex->enrichment)
            && ! empty($config->citationExport->bibtex->enrichment)
        ) {
            $enrichmentNote = $config->citationExport->bibtex->enrichment;
        }

        // Set up XSLT-Processor
        try {
            $proc = new XSLTProcessor();
            $proc->setParameter('', 'enrichment_note', $enrichmentNote ?? '');
            $proc->setParameter('', 'url_prefix', $this->baseUrl);
            $proc->setParameter('', 'urnResolverUrl', $config->urn->resolverUrl);
            $proc->registerPHPFunctions();
            $proc->importStyleSheet($xslt);

            return $proc->transformToXML($xml);
        } catch (Exception $e) {
            throw new Application_Exception($e->getMessage(), null, $e);
        }
    }
}
