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
 * @package     Module_Export
 * @author      Michael Lang <lang@zib.de>
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2017, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Export plugin for exporting documents as XML.
 *
 * TODO reduce to basic XML export (move XSLT into different class)
 * TODO move database/cache access to documents to different layer
 */
class Export_Model_XmlExport extends Application_Export_ExportPluginAbstract {

    /**
     * Holds xml representation of document information to be processed.
     *
     * @var DomDocument  Defaults to null.
     *
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
     * @var XSLTProcessor  Defaults to null.
     */
    protected $_proc = null;

    /**
     * Enables/disables content-disposition attachment.
     * @var null|boolean
     */
    protected $_downloadEnabled = null;

    /**
     * Content type for response.
     * @var null|string
     */
    protected $_contentType = null;

    /**
     * Name of attached file.
     * @var null|string
     */
    protected $_attachmentFilename = null;

    /**
     * Deliver the (transformed) Xml content
     *
     * @return void
     *
     * TODO adapt
     */
    public function postDispatch()
    {
        if (!isset($this->getView()->errorMessage))
        {
            $contentType = $this->getContentType();
            $attachmentFilename = $this->getAttachmentFilename();

            $response = $this->getResponse();

            // Send Xml response.
            $response->setHeader('Content-Type', "$contentType; charset=UTF-8", true);

            if ($this->isDownloadEnabled())
            {
                $response->setHeader('Content-Disposition', "attachment; filename=$attachmentFilename", true);
            }

            if (false === is_null($this->_xslt))
            {
                $this->getResponse()->setBody($this->_proc->transformToXML($this->_xml));
            }
            else
            {
                $this->getResponse()->setBody($this->_xml->saveXml());
            }
        }
    }

    /**
     * Returns content type for response.
     * @return string
     */
    public function getContentType()
    {
        if (is_null($this->_contentType))
        {
            $config = $this->getConfig();

            if (isset($config->contentType))
            {
                $this->_contentType = $config->contentType;
            }
            else {
                $this->_contentType = 'text/xml';
            }
        }

        return $this->_contentType;
    }

    /**
     * Sets mime type for response.
     * @param $mimeType Mime type for response
     */
    public function setContentType($mimeType)
    {
        $this->_contentType = $mimeType;
    }

    public function getAttachmentFilename()
    {
        if (is_null($this->_attachmentFilename))
        {
            $config = $this->getConfig();

            if (isset($config->attachmentFilename))
            {
                $this->_attachmentFilename = $config->attachmentFilename;
            }
            else {
                $this->_attachmentFilename = 'export.xml';
            }
        }

        return $this->_attachmentFilename;
    }

    public function setAttachmentFilename($filename)
    {
        $this->_attachmentFilename = $filename;
    }

    public function isDownloadEnabled()
    {
        if (is_null($this->_downloadEnabled))
        {
            $appConfig = Application_Configuration::getInstance()->getConfig();

            if (isset($appConfig->export->download))
            {
                $value = $appConfig->export->download;
                $this->_downloadEnabled = $value !== '0' && $value !== false && $value !== '';
            }
            else
            {
                $this->_downloadEnabled = true;
            }
        }

        return $this->_downloadEnabled;
    }

    public function setDownloadEnabled($enabled)
    {
        if (!is_bool($enabled) && !is_null($enabled))
        {
            throw new InvalidArgumentException('Argument must be boolean or null.');
        }

        $this->_downloadEnabled = $enabled;
    }

    public function init() {
        // Initialize member variables.
        $this->_xml = new DomDocument();
        $this->_proc = new XSLTProcessor();
    }

    /**
     * Load an xslt stylesheet.
     *
     * @return void
     *
     * TODO adapt
     */
    protected function loadStyleSheet($stylesheet) {
        $this->_xslt = new DomDocument;
        $this->_xslt->load($stylesheet);
        $this->_proc->importStyleSheet($this->_xslt);
        if (isset($_SERVER['HTTP_HOST'])) {
            $this->_proc->setParameter('', 'host', $_SERVER['HTTP_HOST']);
        }
        $this->_proc->setParameter('', 'server', $this->getRequest()->getBaseUrl());
    }

    /**
     * Performs XML export.
     * @throws Application_Exception
     * @throws Application_SearchException
     * @throws Exception
     * @throws Zend_View_Exception
     *
     * TODO exportParam is not needed anymore, but can be supported (exportParam = action)
     * TODO stylesheet can be configured in plugin configuration rather than a parameter
     */
    public function execute() {
        $request = $this->getRequest();

        $exportParam = $request->getParam('export');

        if (is_null($exportParam)) {
            throw new Application_Exception('export format is not specified');
        }

        // currently only xml is supported here
        if ($exportParam !== 'xml') {
            throw new Application_Exception('export format is not supported' . $exportParam);
        }

        // parameter stylesheet is mandatory (only administrator is able to see raw output)
        // non-administrative users can only reference user-defined stylesheets
        if (is_null($request->getParam('stylesheet')) && !Opus_Security_Realm::getInstance()->checkModule('admin')) {
            throw new Application_Exception('missing parameter stylesheet');
        }

        $stylesheet = $request->getParam('stylesheet');
        $stylesheetDirectory = 'stylesheets-custom';

        $this->loadStyleSheet(
            $this->buildStylesheetPath(
                $stylesheet,
                $this->getView()->getScriptPath('') . $stylesheetDirectory
            )
        );

        $this->prepareXml();
    }

    /**
     * Prepares xml export for solr search results.
     *
     * @throws Application_SearchException
     */
    public function prepareXml() {
        $request = $this->getRequest();

        $searchType = $request->getParam('searchtype');

        if (is_null($searchType))
        {
            // TODO move/handle somewhere else (cleanup)
            throw new Application_Search_QueryBuilderException('Unspecified search type: unable to create query');
        }

        $resultList = null;

        switch ($searchType)
        {
        case Application_Util_Searchtypes::ID_SEARCH:
            // TODO handle ID search like any other search
            $resultList = $this->buildResultListForIdSearch($request);
            break;
        default:
            $searchFactory = new Solrsearch_Model_Search();
            $search = $searchFactory->getSearchPlugin($searchType);
            $search->setExport(true);
            $search->setMaxRows($this->getMaxRows());
            $query = $search->buildExportQuery($request);
            $resultList = $search->performSearch($query);
            break;
        }

        $this->handleResults($resultList->getResults(), $resultList->getNumberOfHits());
    }

    /**
     * Returns maximum number of rows for export depending on autentication.
     *
     * @return int
     */
    public function getMaxRows()
    {
        $maxRows = Opus_SolrSearch_Query::MAX_ROWS;

        $config = $this->getConfig();

        if (!Opus_Security_Realm::getInstance()->skipSecurityChecks())
        {
            $identity = Zend_Auth::getInstance()->getIdentity();

            if (empty($identity) === true)
            {
                if (isset($config->maxDocumentsGuest))
                {
                    $maxRows = $this->getValueIfValid($config->maxDocumentsGuest, $maxRows);
                }

            }
            else
            {
                if (isset($config->maxDocumentsUser))
                {
                    $maxRows = $this->getValueIfValid($config->maxDocumentsUser, $maxRows);
                }
            }
        }

        return $maxRows;
    }

    /**
     * Returns value if it is a valid number, otherwise returns default.
     *
     * @param $value
     * @param $default
     * @return string
     */
    public function getValueIfValid($value, $default)
    {
        $value = trim($value);

        if (ctype_digit($value) && $value > 0)
        {
            return $value;
        }

        return $default;
    }

    /**
     * Sets up an xml document out of the result list.
     * @param array $results An array of Opus_SolrSearch_Result objects.
     */
    private function handleResults($results, $numOfHits) {
        $proc = $this->_proc;
        $xml = $this->_xml;

        $proc->setParameter('', 'timestamp', str_replace('+00:00', 'Z', Zend_Date::now()->setTimeZone('UTC')->getIso()));
        $proc->setParameter('', 'docCount', count($results));
        $proc->setParameter('', 'queryhits', $numOfHits);

        $xml->appendChild($xml->createElement('Documents'));

        $resultIds = array();

        foreach ($results as $result) {
            $resultIds[] = $result->getId();
        }

        if (!empty($resultIds)) {
            $documents = $this->getDocumentsXml($resultIds);

            foreach ($resultIds as $docId) {
                $domNode = $xml->importNode($documents[$docId], true);
                $xml->documentElement->appendChild($domNode);
            }
        }
    }

    public function getXml() {
        return $this->_xml;
    }

    /**
     * Returns result for ID search of a single document.
     * @param $request HTTP request object
     * @return Opus_SolrSearch_ResultList
     */
    private function buildResultListForIdSearch($request) {
        $docId = $request->getParam('docId');
        if (is_null($docId)) {
            throw new Application_Exception();
        }
        $result = array();
        try {
            $doc = new Opus_Document($docId);
            // SOLR index currently only contains published documents
            if ($doc->getServerState() == 'published') {
                $result[] = $doc;
            }
        }
        catch (Exception $e) {
            // do nothing; return result with empty array
        }
        return new Opus_SolrSearch_ResultList($result);
    }

    /**
     * Returns array with document XML nodes.
     * @param $documentIds IDs of documents
     * @return array Map of document IDs and DOM nodes
     */
    private function getDocumentsXml($documentIds) {
        $documents = $this->getDocumentsFromCache($documentIds);

        $idsOfUncachedDocs = array_diff($documentIds, array_keys($documents));

        $uncachedDocs = $this->getDocumentsFromDatabase($idsOfUncachedDocs);

        return $documents + $uncachedDocs;
    }

    /**
     * Returns a list of documents from the database.
     * @param $resultIds ids of documents for export
     * @return array [docId] DocumentXml
     */
    private function getDocumentsFromDatabase($documentIds) {
        $documents = array();

        foreach ($documentIds as $docId) {
            $document = new Opus_Document($docId);
            $documentXml = new Application_Util_Document($document);
            $documents[$docId] = $documentXml->getNode();
        }

        return $documents;
    }

    /**
     * Returns a list of documents from cache.
     * @param $resultIds ids of documents for export
     * @return array Map of docId to  Document XML
     */
    private function getDocumentsFromCache($documentIds) {
        $documents = array();

        $documentCacheTable = new Opus_Db_DocumentXmlCache();
        $docXmlCache = $documentCacheTable->fetchAll(
            $documentCacheTable->select()->where(
                'document_id IN (?)',
                $documentIds
            )
        );//->find($this->document->getId(), '1')->current()->xml_data;

        foreach ($docXmlCache as $row) {
            $fragment = new DomDocument();
            $fragment->loadXML($row->xml_data);
            $node = $fragment->getElementsByTagName('Opus_Document')->item(0);
            $documents[$row->document_id] = $node;
        }

        return $documents;
    }

    /**
     * Searches for available stylesheets and builds the path of the selected stylesheet.
     */
    public function buildStylesheetPath($stylesheet, $path) {
        if (!is_null($stylesheet)) {

            $stylesheetsAvailable = array();
            $dir = new DirectoryIterator($path);
            foreach ($dir as $file) {
                if ($file->isFile() && $file->getFilename() != '.' && $file->getFilename() != '..'
                    && $file->isReadable()) {
                    array_push($stylesheetsAvailable, $file->getBasename('.xslt'));
                }
            }
            $pos = array_search($stylesheet, $stylesheetsAvailable);
            if ($pos !== FALSE) {
                return $path . DIRECTORY_SEPARATOR .  $stylesheetsAvailable[$pos] . '.xslt';
            }
            throw new Application_Exception('given stylesheet does not exist or is not readable');
        }
        $pos = strrpos($path, '/');
        $scriptPath = substr($path, 0, ++$pos);
        return $scriptPath . 'stylesheets' . DIRECTORY_SEPARATOR . 'raw.xslt';
    }

}
