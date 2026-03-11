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
 * @copyright   Copyright (c) 2026, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\App\Common\ApplicationException;
use Opus\Common\Document;
use Opus\Common\Repository;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Export documents as OPUS XML.
 *
 * TODO reduce to basic XML export (move XSLT into different class)
 * TODO move database/cache access to documents to different layer
 *
 * TODO this class should do the export and nothing else
 * TODO What is the difference between XML and XSLT export? Usage of XSLT.
 */
class Application_Export_XmlExport
{
    /**
     * Holds xml representation of document information to be processed.
     *
     * @var DOMDocument  Defaults to null.
     */
    protected $xml;

    /**
     * Holds the stylesheet for the transformation.
     *
     * @var DOMDocument  Defaults to null.
     */
    protected $xslt;

    /**
     * Holds the xslt processor.
     *
     * @var XSLTProcessor  Defaults to null.
     */
    protected $proc;

    /** @var OutputInterface */
    protected $output;

    /**
     *
     */
    public function __construct()
    {
        $this->init();
    }

    public function init()
    {
        // TODO this is an internal function
        // Initialize member variables.
        $this->xml  = new DOMDocument();
        $this->proc = new XSLTProcessor();
        $this->registerPhpFunctions();
    }

    /**
     * Load an xslt stylesheet.
     *
     * @param string $stylesheet
     *
     * TODO adapt
     */
    public function loadStyleSheet($stylesheet)
    {
        $stylesheetDirectory = 'stylesheets-custom';

        $stylesheetPath = $this->buildStylesheetPath(
            $stylesheet,
            APPLICATION_PATH . '/modules/export/views/scripts/stylesheets'
        );

        $this->xslt = new DOMDocument();
        $this->xslt->load($stylesheetPath);
        $this->proc->importStyleSheet($this->xslt);

        /** TODO provide fullUrl for export
        $view = $this->getView();

        $this->proc->setParameter('', 'opusUrl', $view->fullUrl());
         */
    }

    /**
     * Performs XML export.
     *
     * @return int
     * @throws ApplicationException
     * @throws Application_SearchException
     * @throws Exception
     * @throws Zend_View_Exception
     *
     * TODO exportParam is not needed anymore, but can be supported (exportParam = action)
     * TODO stylesheet can be configured in plugin configuration rather than a parameter
     */
    public function execute(Application_Export_SearchResult $documents)
    {
        $this->handleResults($documents->getDocumentIds(), $documents->getTotalResults());

        // TODO transform XML
        if ($this->xslt !== null) {
            $output = $this->proc->transformToXML($this->xml);
        } else {
            $this->xml->formatOutput = true;
            $this->xml->preserveWhiteSpace = true;
            $output = $this->xml->saveXml();
        }

        // TODO optionally output result to file
        $this->output->writeln($output);



        return 0;
    }

    /**
     * Sets up an xml document out of the result list.
     *
     * @param array $resultIds An array of document IDs.
     * @param int   $numOfHits total number of hits.
     */
    private function handleResults($resultIds, $numOfHits)
    {
        $proc = $this->proc;
        $xml  = $this->xml;

        $proc->setParameter('', 'timestamp', $this->getTimestamp());
        $proc->setParameter('', 'docCount', count($resultIds));
        $proc->setParameter('', 'queryhits', $numOfHits);

        // TODO allow extension/configuration of registered view helpers
        Application_Xslt::registerViewHelper($proc, [
            'optionValue',
            'fileUrl',
            'frontdoorUrl',
            'transferUrl',
        ]);

        $xml->appendChild($xml->createElement('Documents'));

        if (! empty($resultIds)) {
            $documents = $this->getDocumentsXml($resultIds);

            foreach ($resultIds as $docId) {
                $domNode = $xml->importNode($documents[$docId], true);
                $xml->documentElement->appendChild($domNode);
            }
        }
    }

    protected function getTimestamp(): string
    {
        return str_replace(
            '+00:00',
            'Z',
            (new DateTime())->setTimezone(new DateTimeZone('UTC'))->format(DateTime::RFC3339)
        );
    }

    /**
     * @return DOMDocument
     */
    public function getXml()
    {
        return $this->xml;
    }

    /**
     * Returns array with document XML nodes.
     *
     * @param int[] $documentIds IDs of documents
     * @return array Map of document IDs and DOM nodes
     *
     * TODO cache usage should be transparent and outside of this class (DocumentStoreInterface or similar)
     */
    private function getDocumentsXml($documentIds)
    {
        $documents = $this->getDocumentsFromCache($documentIds);

        $idsOfUncachedDocs = array_diff($documentIds, array_keys($documents));

        $uncachedDocs = $this->getDocumentsFromDatabase($idsOfUncachedDocs);

        return $documents + $uncachedDocs;
    }

    /**
     * Returns a list of documents from the database.
     *
     * @param int[] $documentIds ids of documents for export
     * @return array [docId] DocumentXml
     *
     * TODO hide in external component
     */
    private function getDocumentsFromDatabase($documentIds)
    {
        $documents = [];

        foreach ($documentIds as $docId) {
            $document          = Document::get($docId);
            $documentXml       = new Application_Util_Document($document);
            $documents[$docId] = $documentXml->getNode();
        }

        return $documents;
    }

    /**
     * Returns a list of documents from cache.
     *
     * @param int[] $documentIds ids of documents for export
     * @return array Map of docId to  Document XML
     *
     * TODO getting documents from cache or database should be in separate class and transparent here
     */
    public function getDocumentsFromCache($documentIds)
    {
        $documents = [];

        $documentCache = Repository::getInstance()->getDocumentXmlCache();
        $documentXml   = $documentCache->getData($documentIds, '1'); // TODO what version is used?

        foreach ($documentIds as $docId) {
            if (! isset($documentXml[$docId])) {
                continue;
            }
            $xml      = $documentXml[$docId];
            $fragment = new DOMDocument();
            $fragment->loadXML($xml);
            $node              = $fragment->getElementsByTagName('Opus_Document')->item(0);
            $documents[$docId] = $node;
        }

        return $documents;
    }

    /**
     * Searches for available stylesheets and builds the path of the selected stylesheet.
     *
     * @param string $stylesheet
     * @param string $path
     * @return string
     * @throws ApplicationException
     *
     * TODO review and if possible simplify this function
     */
    public function buildStylesheetPath($stylesheet, $path)
    {
        if ($stylesheet !== null) {
            $stylesheetsAvailable = [];
            $dir                  = new DirectoryIterator($path);
            foreach ($dir as $file) {
                if (
                    $file->isFile() && $file->getFilename() !== '.' && $file->getFilename() !== '..'
                    && $file->isReadable()
                ) {
                    array_push($stylesheetsAvailable, $file->getBasename('.xslt'));
                }
            }
            $pos = array_search($stylesheet, $stylesheetsAvailable);
            if ($pos !== false) {
                return $path . DIRECTORY_SEPARATOR . $stylesheetsAvailable[$pos] . '.xslt';
            }
            throw new ApplicationException('given stylesheet does not exist or is not readable');
        }
        $pos        = strrpos($path, '/');
        $scriptPath = substr($path, 0, ++$pos);
        return $scriptPath . 'stylesheets' . DIRECTORY_SEPARATOR . 'raw.xslt';
    }

    /**
     * TODO create test verifying expected functions are available
     */
    protected function registerPhpFunctions()
    {
        Application_Xslt::registerViewHelper($this->proc, [
            'accessAllowed',
            'isAuthenticated',
            'translate',
            'dcType',
        ]);
    }

    public function setOutput(OutputInterface $output): self
    {
        $this->output = $output;
        return $this;
    }
}
