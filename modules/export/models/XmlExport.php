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
use Opus\Common\Repository;
use Opus\Common\Security\Realm;
use Opus\Search\Util\Query;

/**
 * Export plugin for exporting documents as XML.
 *
 * TODO reduce to basic XML export (move XSLT into different class)
 * TODO move database/cache access to documents to different layer
 */
class Export_Model_XmlExport extends Application_Export_ExportPluginAbstract
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

    /**
     * Enables/disables content-disposition attachment.
     *
     * @var null|bool
     */
    protected $downloadEnabled;

    /**
     * Content type for response.
     *
     * @var null|string
     */
    protected $contentType;

    /**
     * Name of attached file.
     *
     * @var null|string
     */
    protected $attachmentFilename;

    /**
     * Deliver the (transformed) Xml content
     *
     * TODO adapt
     */
    public function postDispatch()
    {
        if (! isset($this->getView()->errorMessage)) {
            $contentType        = $this->getContentType();
            $attachmentFilename = $this->getAttachmentFilename();

            $response = $this->getResponse();

            // Send Xml response.
            $response->setHeader('Content-Type', "$contentType; charset=UTF-8", true);

            if ($this->isDownloadEnabled()) {
                $response->setHeader('Content-Disposition', "attachment; filename=$attachmentFilename", true);
            }

            if ($this->xslt !== null) {
                $this->getResponse()->setBody($this->proc->transformToXML($this->xml));
            } else {
                $this->getResponse()->setBody($this->xml->saveXml());
            }
        }
    }

    /**
     * Returns content type for response.
     *
     * @return string
     */
    public function getContentType()
    {
        if ($this->contentType === null) {
            $config = $this->getConfig();

            if (isset($config->contentType)) {
                $this->contentType = $config->contentType;
            } else {
                $this->contentType = 'text/xml';
            }
        }

        return $this->contentType;
    }

    /**
     * Sets mime type for response.
     *
     * @param string $mimeType Mime type for response
     */
    public function setContentType($mimeType)
    {
        $this->contentType = $mimeType;
    }

    /**
     * @return string
     * @throws Zend_Exception
     */
    public function getAttachmentFilename()
    {
        if ($this->attachmentFilename === null) {
            $config = $this->getConfig();

            if (isset($config->attachmentFilename)) {
                $this->attachmentFilename = $config->attachmentFilename;
            } else {
                $this->attachmentFilename = 'export.xml';
            }
        }

        return $this->attachmentFilename;
    }

    /**
     * @param string $filename
     */
    public function setAttachmentFilename($filename)
    {
        $this->attachmentFilename = $filename;
    }

    /**
     * @return bool
     */
    public function isDownloadEnabled()
    {
        if ($this->downloadEnabled === null) {
            $appConfig = Application_Configuration::getInstance()->getConfig();

            $this->downloadEnabled = isset($appConfig->export->download) ?
                filter_var($appConfig->export->download, FILTER_VALIDATE_BOOLEAN) : true;
        }

        return $this->downloadEnabled;
    }

    /**
     * @param bool $enabled
     */
    public function setDownloadEnabled($enabled)
    {
        if (! is_bool($enabled) && $enabled !== null) {
            throw new InvalidArgumentException('Argument must be boolean or null.');
        }

        $this->downloadEnabled = $enabled;
    }

    public function init()
    {
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
    protected function loadStyleSheet($stylesheet)
    {
        $this->xslt = new DOMDocument();
        $this->xslt->load($stylesheet);
        $this->proc->importStyleSheet($this->xslt);

        $view = $this->getView();

        $this->proc->setParameter('', 'opusUrl', $view->fullUrl());
    }

    /**
     * Performs XML export.
     *
     * @return int
     * @throws Application_Exception
     * @throws Application_SearchException
     * @throws Exception
     * @throws Zend_View_Exception
     *
     * TODO exportParam is not needed anymore, but can be supported (exportParam = action)
     * TODO stylesheet can be configured in plugin configuration rather than a parameter
     */
    public function execute()
    {
        $request = $this->getRequest();

        $exportParam = $request->getParam('export');

        if ($exportParam === null) {
            throw new Application_Exception('export format is not specified');
        }

        // currently only xml is supported here
        if ($exportParam !== 'xml') {
            throw new Application_Exception('export format is not supported: ' . $exportParam);
        }

        // parameter stylesheet is mandatory (only administrator is able to see raw output)
        // non-administrative users can only reference user-defined stylesheets
        if ($request->getParam('stylesheet') === null && ! Realm::getInstance()->checkModule('admin')) {
            throw new Application_Exception('missing parameter stylesheet');
        }

        $stylesheet          = $request->getParam('stylesheet');
        $stylesheetDirectory = 'stylesheets-custom';

        $this->loadStyleSheet(
            $this->buildStylesheetPath(
                $stylesheet,
                $this->getView()->getScriptPath('') . $stylesheetDirectory
            )
        );

        $this->prepareXml();

        return 0;
    }

    /**
     * Prepares XML export of Solr search results.
     *
     * @throws Application_SearchException
     */
    public function prepareXml()
    {
        $request = $this->getRequest();

        $searchType = $request->getParam('searchtype');

        if ($searchType === null) {
            // TODO move/handle somewhere else (cleanup)
            throw new Application_Search_QueryBuilderException('Unspecified search type: unable to create query');
        }

        $resultIds = [];

        switch ($searchType) {
            case Application_Util_Searchtypes::ID_SEARCH:
                $resultIds    = $this->getAndValidateDocId($request);
                $numberOfHits = count($resultIds);
                break;
            default:
                $searchFactory = new Solrsearch_Model_Search();
                $search        = $searchFactory->getSearchPlugin($searchType);
                $search->setExport(true);
                $search->setMaxRows($this->getMaxRows());

                // im Solr-Index sind auch nicht freigeschaltete Dokumente: die Einschränkung der
                // Suche auf ausschließlich freigeschaltete Dokumente erfolgt in der Suchklasse und
                // muss daher hier nicht vorgenommen werden
                $query      = $search->buildExportQuery($request);
                $resultList = $search->performSearch($query);
                foreach ($resultList->getResults() as $result) {
                    $resultIds[] = $result->getId();
                }
                $numberOfHits = $resultList->getNumberOfHits();
                break;
        }

        $this->handleResults($resultIds, $numberOfHits);
    }

    /**
     * Returns maximum number of rows for export depending on autentication.
     *
     * IMPORTANT: maxRows must not exceed 2147483647 (java,lang.Integer.MAX_VALUE)
     *
     * @return int
     */
    public function getMaxRows()
    {
        $maxRows = Query::MAX_ROWS;

        $config = $this->getConfig();

        if (! Realm::getInstance()->skipSecurityChecks()) {
            $identity = Zend_Auth::getInstance()->getIdentity();

            if (empty($identity) === true) {
                if (isset($config->maxDocumentsGuest)) {
                    $maxRows = $this->getValueIfValid($config->maxDocumentsGuest, $maxRows);
                }
            } else {
                if (isset($config->maxDocumentsUser)) {
                    $maxRows = $this->getValueIfValid($config->maxDocumentsUser, $maxRows);
                }
            }
        }

        // Do not allows configured values to exceed java.lang.Integer.MAX_VALUE (Solr)
        if ($maxRows > Query::MAX_ROWS) {
            $maxRows = Query::MAX_ROWS;
        }

        return $maxRows;
    }

    /**
     * Returns value if it is a valid number, otherwise returns default.
     *
     * @param string $value
     * @param string $default
     * @return string
     */
    public function getValueIfValid($value, $default)
    {
        $value = trim($value ?? '');

        if (ctype_digit($value) && $value > 0) {
            return $value;
        }

        return $default;
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

        $proc->setParameter(
            '',
            'timestamp',
            str_replace(
                '+00:00',
                'Z',
                (new DateTime())->setTimezone(new DateTimeZone('UTC'))->format(DateTime::RFC3339)
            )
        );

        $proc->setParameter('', 'docCount', count($resultIds));
        $proc->setParameter('', 'queryhits', $numOfHits);

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

    /**
     * @return DOMDocument
     */
    public function getXml()
    {
        return $this->xml;
    }

    /**
     * Checks for existence of a document with the ID given in request parameter docId.
     * Additionally restrict search to documents with serverState published only if user does
     * not have 'resource_documents' permission, otherwise all documents are considered.
     *
     * Returns an empty array, if ID is not present in request, is unknown or the corresponding
     * document is not in serverState published. Otherwise returns a one-element array with docId.
     *
     * @param Zend_Controller_Request_Http $request HTTP request object
     * @return array empty array or one-element array with docId.
     */
    private function getAndValidateDocId($request)
    {
        $docId = $request->getParam('docId');

        $result = [];

        // TODO hier bessere Differenzierung zwischen unterschiedlichen Fehlerzuständen (docId Parameter
        // TODO fehlt im Request, hat falschen Typ, zugehöriges Dokument existiert nicht bzw. ist nicht publiziert)
        if ($docId !== null) {
            $doc = null;
            try {
                $doc = Document::get($docId);
            } catch (Exception $e) {
                // do nothing: return empty array
            }

            if ($doc !== null) {
                if ($doc->getServerState() !== 'published' && ! $this->isAllowExportOfUnpublishedDocs()) {
                    // Export von nicht freigeschalteten Dokumente ist verboten
                    throw new Application_Export_Exception('export of unpublished documents is not allowed');
                }
                $result[] = $doc->getId();
            }
        }

        return $result;
    }

    /**
     * Returns array with document XML nodes.
     *
     * @param int[] $documentIds IDs of documents
     * @return array Map of document IDs and DOM nodes
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
     * @throws Application_Exception
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
            throw new Application_Exception('given stylesheet does not exist or is not readable');
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
        ]);
    }
}
