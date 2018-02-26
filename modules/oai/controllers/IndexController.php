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
 * @author      Simone Finkbeiner <simone.finkbeiner@ub.uni-stuttgart.de>
 * @author      Henning Gerhardt <henning.gerhardt@slub-dresden.de>
 * @author      Michael Lang <lang@zib.de>
 * @author      Thoralf Klein <thoralf.klein@zib.de>
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2009 - 2017, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 *
 * TODO move all processing into model classes for testing and reuse
 * TODO refactor code for returning multiple errors
 */

class Oai_IndexController extends Application_Controller_Xml {

    /**
     * Holds information about which document state aka server_state
     * are delivered out
     *
     * @var array
     */
    private $_deliveringDocumentStates = array('published', 'deleted');  // maybe deleted documents too

    /**
     * Holds restriction types for xMetaDiss
     *
     * @var array
     */
    private $_xMetaDissRestriction = array('doctoralthesis', 'habilitation');

    /**
     * Hold oai module configuration model.
     *
     * @var Oai_Model_Configuration
     */
    protected $_configuration = null;

    private $_xmlFactory = null;

    /**
     * Gather configuration before action handling.
     *
     * @return void
     */
    public function init() {
        parent::init();

        $config = $this->getConfig();

        $this->_configuration = new Oai_Model_Configuration($config);
        $this->_xmlFactory = new Oai_Model_XmlFactory();
    }

    /**
     * Entry point for all OAI-PMH requests.
     *
     * @return void
     */
    public function indexAction() {

        // to handle POST and GET Request, take any given parameter
        $oaiRequest = $this->getRequest()->getParams();
        // remove parameters which are "safe" to remove
        $safeRemoveParameters = array('module', 'controller', 'action', 'role');
        foreach ($safeRemoveParameters as $parameter) {
            unset($oaiRequest[$parameter]);
        }

        try {
            // handle request
            $this->__handleRequest($oaiRequest, $this->getRequest()->getRequestUri());
            return;
        }
        catch (Oai_Model_Exception $e) {
            $errorCode = Oai_Model_Error::mapCode($e->getCode());
            $this->getLogger()->err($errorCode);
            $this->_proc->setParameter('', 'oai_error_code', $errorCode);
            $this->getLogger()->err($e->getMessage());
            $this->_proc->setParameter('', 'oai_error_message', htmlentities($e->getMessage()));
        }
        catch (Oai_Model_ResumptionTokenException $e) {
            $this->getLogger()->err($e);
            $this->_proc->setParameter('', 'oai_error_code', 'unknown');
            $this->_proc->setParameter(
                '', 'oai_error_message', 'An error occured while processing the resumption token.'
            );
            $this->getResponse()->setHttpResponseCode(500);
        }
        catch (Exception $e) {
            $this->getLogger()->err($e);
            $this->_proc->setParameter('', 'oai_error_code', 'unknown');
            $this->_proc->setParameter('', 'oai_error_message', 'An internal error occured.');
            $this->getResponse()->setHttpResponseCode(500);
        }

        $this->_xml = new DomDocument;
    }

    private function getOaiBaseUrl() {
        $oaiBaseUrl = $this->_configuration->getOaiBaseUrl();

        // if no OAI base url is set, use local information as base url
        if (true === empty($oaiBaseUrl)) {
            $request = $this->getRequest();
            $base = $request->getBaseUrl();
            $host = $request->getHttpHost();
            $scheme = $request->getScheme();
            $module = $request->getModuleName();
            $oaiBaseUrl = $scheme . '://' . $host . $base . '/' . $module;
        }

        return $oaiBaseUrl;
    }

    /**
     * Handles an OAI request.
     *
     * @param  array  $oaiRequest Contains full request information
     * @throws Oai_Model_Exception Thrown if the request could not be handled.
     * @return void
     */
    private function __handleRequest(array $oaiRequest, $requestUri) {
        // Setup stylesheet
        $this->loadStyleSheet($this->view->getScriptPath('index') . '/oai-pmh.xslt');

        $this->_proc->registerPHPFunctions('Oai_Model_Language::getLanguageCode');
        Application_Xslt::registerViewHelper($this->_proc, array('optionValue'));
        $this->_proc->setParameter('', 'urnResolverUrl', $this->getConfig()->urn->resolverUrl);

        // Set response time
        $this->_proc->setParameter(
            '', 'dateTime', str_replace('+00:00', 'Z', Zend_Date::now()->setTimeZone('UTC')->getIso())
        );

        // set OAI base url
        $this->_proc->setParameter('', 'oai_base_url', $this->getOaiBaseUrl());

        $metadataPrefixPath = $this->view->getScriptPath('index') . DIRECTORY_SEPARATOR . 'prefixes';
        $resumptionPath = $this->_configuration->getResumptionTokenPath();

        $request = new Oai_Model_Request();
        $request->setPathToMetadataPrefixFiles($metadataPrefixPath);
        $request->setResumptionPath($resumptionPath);

        // check for duplicate parameters
        foreach ($oaiRequest as $name => $value)
        {
            if (substr_count($requestUri, "&$name") > 1)
            {
                throw new Oai_Model_Exception(
                    'Parameters must not occur more than once.',
                    Oai_Model_Error::BADARGUMENT
                );
            }
        }

        if (true !== $request->validate($oaiRequest)) {
            throw new Oai_Model_Exception($request->getErrorMessage(), $request->getErrorCode());
        }

        foreach ($oaiRequest as $parameter => $value) {
            Zend_Registry::get('Zend_Log')->debug("'oai_' . $parameter, $value");
            $this->_proc->setParameter('', 'oai_' . $parameter, $value);
        }

        switch ($oaiRequest['verb']) {
            case 'GetRecord':
                $this->__handleGetRecord($oaiRequest);
                break;

            case 'Identify':
                $this->__handleIdentify();
                break;

            case 'ListIdentifiers':
                $this->__handleListIdentifiers($oaiRequest);
                break;

            case 'ListMetadataFormats':
                $this->__handleListMetadataFormats($oaiRequest);
                break;

            case 'ListRecords':
                $this->__handleListRecords($oaiRequest);
                break;

            case 'ListSets':
                $this->__handleListSets();
                break;

            default:
                throw new Exception('The verb provided in the request is illegal.', Oai_Model_Error::BADVERB);
                break;
        }
    }

    /**
     * Implements response for OAI-PMH verb 'GetRecord'.
     *
     * @param  array &$oaiRequest Contains full request information
     * @return void
     */
    private function __handleGetRecord(array &$oaiRequest) {

        // Identifier references metadata Urn, not plain Id!
        // Currently implemented as 'oai:foo.bar.de:{docId}' or 'urn:nbn...-123'
        $docId = $this->getDocumentIdByIdentifier($oaiRequest['identifier']);

        $document = null;
        try {
            $document = new Opus_Document($docId);
        }
        catch (Opus_Model_NotFoundException $ex) {
            throw new Oai_Model_Exception(
                'The value of the identifier argument is unknown or illegal in this repository.',
                Oai_Model_Error::IDDOESNOTEXIST
            );
        }

        $metadataPrefix = $oaiRequest['metadataPrefix'];

        // do not deliver documents which are restricted by document state
        if (is_null($document)
            or (false === in_array($document->getServerState(), $this->_deliveringDocumentStates))
            or (false === $document->hasEmbargoPassed() and stripos($metadataPrefix, 'xmetadiss') === 0)) {
            throw new Oai_Model_Exception('Document is not available for OAI export!', Oai_Model_Error::NORECORDSMATCH);
        }

        // for xMetaDiss it must be habilitation-thesis or doctoral-thesis
        if ('xMetaDiss' === $metadataPrefix) {
            $type = $document->getType();
            $isHabOrDoc = in_array($type, $this->_xMetaDissRestriction);
            if (false === $isHabOrDoc) {
               throw new Oai_Model_Exception(
                   "The combination of the given values results in an empty list (xMetaDiss only for habilitation"
                   . " and doctoralthesis).", Oai_Model_Error::NORECORDSMATCH
               );
            }
        }
        $this->_xml->appendChild($this->_xml->createElement('Documents'));

        $this->createXmlRecord($document);
    }

    /**
     * Implements response for OAI-PMH verb 'Identify'.
     *
     * @param  array &$oaiRequest Contains full request information
     * @return void
     */
    private function __handleIdentify() {

        $email = $this->_configuration->getEmailContact();
        $repName = $this->_configuration->getRepositoryName();
        $repIdentifier = $this->_configuration->getRepositoryIdentifier();
        $sampleIdentifier = $this->_configuration->getSampleIdentifier();

        // Set backup date if database query does not return a date.
        $earliestDate = new Zend_Date('1970-01-01', Zend_Date::ISO_8601);

        $earliestDateFromDb = Opus_Document::getEarliestPublicationDate();
        if (!is_null($earliestDateFromDb)) {
            $earliestDate = new Zend_Date($earliestDateFromDb, Zend_Date::ISO_8601);
        }
        $earliestDateIso = $earliestDate->get('yyyy-MM-dd');

        // set parameters for oai-pmh.xslt
        $this->_proc->setParameter('', 'emailAddress', $email);
        $this->_proc->setParameter('', 'repName', $repName);
        $this->_proc->setParameter('', 'repIdentifier', $repIdentifier);
        $this->_proc->setParameter('', 'sampleIdentifier', $sampleIdentifier);
        $this->_proc->setParameter('', 'earliestDate', $earliestDateIso);
        $this->_xml->appendChild($this->_xml->createElement('Documents'));
    }

    /**
     * Implements response for OAI-PMH verb 'ListIdentifiers'.
     *
     * @param  array &$oaiRequest Contains full request information
     * @return void
     */
    private function __handleListIdentifiers(array &$oaiRequest)
    {
        $maxIdentifier = $this->_configuration->getMaxListIdentifiers();
        $this->_handlingOfLists($oaiRequest, $maxIdentifier);
    }

    /**
     * Implements response for OAI-PMH verb 'ListMetadataFormats'.
     *
     * @param  array &$oaiRequest Contains full request information
     * @return void
     */
    private function __handleListMetadataFormats($oaiRequest)
    {
        if (isset($oaiRequest['identifier']))
        {
            try
            {
                // check for document identifier, but ignore because all documents have same list of formats
                $docId = $this->getDocumentIdByIdentifier($oaiRequest['identifier']);
            }
            catch (Oai_Model_Exception $ome)
            {
                // set second error so 'badArgument' and 'idDoesNotExist' are reported back
                $this->_proc->setParameter(
                    '', 'oai_error_code2',
                    Oai_Model_Error::mapCode(Oai_Model_Error::IDDOESNOTEXIST)
                );
                $this->_proc->setParameter(
                    '', 'oai_error_message2',
                    'Identifier is invalid and does not exist.'
                );
                throw $ome;
            }
        }

        $this->_xml->appendChild($this->_xml->createElement('Documents'));
    }

    /**
     * Implements response for OAI-PMH verb 'ListRecords'.
     *
     * @param  array &$oaiRequest Contains full request information
     * @return void
     */
    private function __handleListRecords(array &$oaiRequest) {

        $maxRecords = $this->_configuration->getMaxListRecords();
        $this->_handlingOfLists($oaiRequest, $maxRecords);

    }

    /**
     * Implements response for OAI-PMH verb 'ListSets'.
     *
     * @param  array &$oaiRequest Contains full request information
     * @return void
     */
    private function __handleListSets() {
        $logger = $this->getLogger();

        $repIdentifier = $this->_configuration->getRepositoryIdentifier();

        $this->_proc->setParameter('', 'repIdentifier', $repIdentifier);
        $this->_xml->appendChild($this->_xml->createElement('Documents'));

        $oaiSets = new Oai_Model_Sets();

        $sets = $oaiSets->getSets();


        foreach ($sets as $type => $name) {
            $opusDoc = $this->_xml->createElement('Opus_Sets');
            $typeAttr = $this->_xml->createAttribute('Type');
            $typeValue = $this->_xml->createTextNode($type);
            $typeAttr->appendChild($typeValue);
            $opusDoc->appendChild($typeAttr);
            $nameAttr = $this->_xml->createAttribute('TypeName');
            $nameValue = $this->_xml->createTextNode($name);
            $nameAttr->appendChild($nameValue);
            $opusDoc->appendChild($nameAttr);
            $this->_xml->documentElement->appendChild($opusDoc);
        }
    }

    /**
     * Set parameters for resumptionToken-line.
     *
     * @param  string  $res value of the resumptionToken
     * @param  int     $cursor value of the cursor
     * @param  int     $totalIds value of the total Ids
     */
    private function setParamResumption($res, $cursor, $totalIds) {

       $tomorrow = str_replace('+00:00', 'Z', Zend_Date::now()->addDay(1)->setTimeZone('UTC')->getIso());
       $this->_proc->setParameter('', 'dateDelete', $tomorrow);
       $this->_proc->setParameter('', 'res', $res);
       $this->_proc->setParameter('', 'cursor', $cursor);
       $this->_proc->setParameter('', 'totalIds', $totalIds);
    }

    /**
     *
     * @param Opus_Document $document
     * @return DOMNode
     * @throws Exception
     */
    private function getDocumentXmlDomNode($document) {
        if (!in_array($document->getServerState(), $this->_deliveringDocumentStates)) {
            $message = 'Trying to get a document in server state "' . $document->getServerState() . '"';
            Zend_Registry::get('Zend_Log')->err($message);
            throw new Exception($message);
        }

        $xmlModel = new Opus_Model_Xml();
        $xmlModel->setModel($document);
        $xmlModel->excludeEmptyFields();
        $xmlModel->setStrategy(new Opus_Model_Xml_Version1);
        $xmlModel->setXmlCache(new Opus_Model_Xml_Cache);
        return $xmlModel->getDomDocument()->getElementsByTagName('Opus_Document')->item(0);
    }

    /**
     * Create xml structure for one record
     *
     * @param  Opus_Document $document
     * @param  string        $metadataPrefix
     * @return void
     */
    private function createXmlRecord(Opus_Document $document) {
        $docId = $document->getId();
        $domNode = $this->getDocumentXmlDomNode($document);

        // add frontdoor url
        $this->_addFrontdoorUrlAttribute($domNode, $docId);

        // add ddb transfer element
        $this->_addDdbTransferElement($domNode, $docId);

        // add access rights to element
        $this->_addAccessRights($domNode, $document);

        // remove file elements which should not be exported through OAI
        // Iterating over DOMNodeList is only save for readonly-operations;
        // copy element-by-element before removing!
        $filenodes = $domNode->getElementsByTagName('File');
        $filenodesList = array();
        foreach ($filenodes as $filenode) {
            $filenodesList[] = $filenode;

            // add file download urls
            $this->_addFileUrlAttribute($filenode, $docId, $filenode->getAttribute('PathName'));
        }

        // remove file elements which should not be exported through OAI
        foreach ($filenodesList AS $filenode) {
            if ((false === $filenode->hasAttribute('VisibleInOai'))
                    or ('1' !== $filenode->getAttribute('VisibleInOai'))) {
                $domNode->removeChild($filenode);
            }
        }

        $node = $this->_xml->importNode($domNode, true);

        $type = $document->getType();
        $this->_addSpecInformation($node, 'doc-type:' . $type);

        $bibliography = $document->getBelongsToBibliography() == 1 ? 'true' : 'false';
        $this->_addSpecInformation($node, 'bibliography:' . $bibliography);

        $logger = $this->getLogger();
        $setSpecs = Oai_Model_SetSpec::getSetSpecsFromCollections($document->getCollection());
        foreach ($setSpecs AS $setSpec) {
            if (preg_match("/^([A-Za-z0-9\-_\.!~\*'\(\)]+)(:[A-Za-z0-9\-_\.!~\*'\(\)]+)*$/", $setSpec)) {
                $this->_addSpecInformation($node, $setSpec);
                continue;
            }
            $logger->info("skipping invalid setspec: " . $setSpec);
        }

        $this->_xml->documentElement->appendChild($node);
    }

    /**
     * Add spec header information to DOM document.
     *
     * @param DOMNode $document
     * @param mixed   $information
     * @return void
     */
    private function _addSpecInformation(DOMNode $document, $information) {

        $setSpecAttribute = $this->_xml->createAttribute('Value');
        $setSpecAttributeValue = $this->_xml->createTextNode($information);
        $setSpecAttribute->appendChild($setSpecAttributeValue);

        $setSpecElement = $this->_xml->createElement('SetSpec');
        $setSpecElement->appendChild($setSpecAttribute);
        $document->appendChild($setSpecElement);
    }

    /**
     * Add the frontdoorurl attribute to Opus_Document XML output.
     *
     * @param DOMNode $document Opus_Document XML serialisation
     * @param string  $docid    Id of the document
     * @return void
     */
    private function _addFrontdoorUrlAttribute(DOMNode $document, $docid) {
        $url = $this->view->fullUrl() . '/frontdoor/index/index/docId/' . $docid;

        $owner = $document->ownerDocument;
        $attr = $owner->createAttribute('frontdoorurl');
        $attr->appendChild($owner->createTextNode($url));
        $document->appendChild($attr);
    }

    /**
     * Add download link url attribute to Opus_Document XML output.
     *
     * @param DOMNode $document Opus_Document XML serialisation
     * @param string  $docid    Id of the document
     * @param string  $filename File path name
     * @return void
     */
    private function _addFileUrlAttribute(DOMNode $file, $docid, $filename) {
        $url = $this->view->fullUrl() . '/files/' . $docid . '/' . rawurlencode($filename);

        $owner = $file->ownerDocument;
        $attr = $owner->createAttribute('url');
        $attr->appendChild($owner->createTextNode($url));
        $file->appendChild($attr);
    }

    /**
     * Adds ddb contact id based on resource information.
     *
     * @param DOMNode $document
     * @param string  $docId
     * @return void
     */


    /**
     * Add <ddb:transfer> element for ddb container file.
     *
     * @param DOMNode $document Opus_Document XML serialisation
     * @param string  $docid    Document ID
     * @return void
     */
    private function _addDdbTransferElement(DOMNode $document, $docid) {
        $url = $this->view->serverUrl() . $this->view->baseUrl() . '/oai/container/index/docId/' . $docid;

        $fileElement = $document->ownerDocument->createElement('TransferUrl');
        $fileElement->setAttribute('PathName', $url);
        $document->appendChild($fileElement);
    }

    /**
     * Add rights element to output.
     *
     * @param DOMNode $domNode
     * @param Opus_Document $doc
     */
    private function _addAccessRights(DOMNode $domNode, Opus_Document $doc)
    {
        $fileElement = $domNode->ownerDocument->createElement('Rights');
        $fileElement->setAttribute('Value', $this->_xmlFactory->getAccessRights($doc));
        $domNode->appendChild($fileElement);
    }

    /**
     * Retrieve a document id by an oai identifier.
     *
     * @param string $oaiIdentifier
     * @result int
     */
    private function getDocumentIdByIdentifier($oaiIdentifier) {
        $identifierParts = explode(":", $oaiIdentifier);

        $docId = null;
        switch ($identifierParts[0]) {
            case 'urn':
                $finder = new Opus_DocumentFinder();
                $finder->setIdentifierTypeValue('urn', $oaiIdentifier);
                $finder->setServerStateInList($this->_deliveringDocumentStates);
                $docIds = $finder->ids();
                $docId = $docIds[0];
                break;
            case 'oai':
                if (isset($identifierParts[2])) {
                    $docId = $identifierParts[2];
                }
                break;
            default:
                throw new Oai_Model_Exception(
                    'The prefix of the identifier argument is unknown.', Oai_Model_Error::BADARGUMENT
                );
                break;
        }

        if (empty($docId) or !preg_match('/^\d+$/', $docId)) {
            throw new Oai_Model_Exception(
                'The value of the identifier argument is unknown or illegal in this repository.',
                Oai_Model_Error::IDDOESNOTEXIST
            );
        }

        return $docId;
    }

    /**
     * Helper method for handling lists.
     *
     * @param array &$oaiRequest
     * @param mixed $maxRecords
     * @return void
     */
    private function _handlingOfLists(array &$oaiRequest, $maxRecords) {

        if (true === empty($maxRecords)) {
            $maxRecords = 100;
        }

        $repIdentifier = $this->_configuration->getRepositoryIdentifier();
        $tempPath = $this->_configuration->getResumptionTokenPath();

        $this->_proc->setParameter('', 'repIdentifier', $repIdentifier);
        $this->_xml->appendChild($this->_xml->createElement('Documents'));
        // do some initialisation
        $cursor = 0;
        $totalIds = 0;
        $start = $maxRecords + 1;
        $reldocIds = array();

        $metadataPrefix = null;
        if (true === array_key_exists('metadataPrefix', $oaiRequest)) {
            $metadataPrefix = $oaiRequest['metadataPrefix'];
        }

        $tokenWorker = new Oai_Model_Resumptiontokens;
        $tokenWorker->setResumptionPath($tempPath);

        // parameter resumptionToken is given
        if (false === empty($oaiRequest['resumptionToken'])) {

            $resParam = $oaiRequest['resumptionToken'];
            $token = $tokenWorker->getResumptionToken($resParam);

            if (true === is_null($token)) {
                throw new Oai_Model_Exception("file could not be read.", Oai_Model_Error::BADRESUMPTIONTOKEN);
            }

            $cursor = $token->getStartPosition() - 1;
            $start = $token->getStartPosition() + $maxRecords;
            $totalIds = $token->getTotalIds();
            $reldocIds = $token->getDocumentIds();
            $metadataPrefix = $token->getMetadataPrefix();
            $this->_proc->setParameter('', 'oai_metadataPrefix', $metadataPrefix);

        // no resumptionToken is given
        }
        else {
            $docListModel = new Oai_Model_DocumentList();
            $docListModel->deliveringDocumentStates = $this->_deliveringDocumentStates;
            $docListModel->xMetaDissRestriction = $this->_xMetaDissRestriction;
            $reldocIds = $docListModel->query($oaiRequest);
            $totalIds = count($reldocIds);
        }

        // handling of document ids
        $restIds = $reldocIds;
        $workIds = array_splice($restIds, 0, $maxRecords);
        foreach ($workIds as $docId) {
            $document = new Opus_Document($docId);
            $this->createXmlRecord($document);
        }

        // no records returned
        if (true === empty($workIds)) {
            throw new Oai_Model_Exception(
                "The combination of the given values results in an empty list.", Oai_Model_Error::NORECORDSMATCH
            );
        }

        // store the further Ids in a resumption-file
        $countRestIds = count($restIds);
        if ($countRestIds > 0) {

            $token = new Oai_Model_Resumptiontoken;
            $token->setStartPosition($start);
            $token->setTotalIds($totalIds);
            $token->setDocumentIds($restIds);
            $token->setMetadataPrefix($metadataPrefix);

            $tokenWorker->storeResumptionToken($token);

            // set parameters for the resumptionToken-node
            $res = $token->getResumptionId();
            $this->setParamResumption($res, $cursor, $totalIds);
        }
    }

}
