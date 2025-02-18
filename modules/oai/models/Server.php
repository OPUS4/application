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
 * @copyright   Copyright (c) 2017, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\Document;
use Opus\Common\DocumentInterface;
use Opus\Common\Log;
use Opus\Common\Model\NotFoundException;
use Opus\Common\Repository;
use Opus\Model\Xml;
use Opus\Model\Xml\Version1;

class Oai_Model_Server extends Application_Model_Abstract
{
    /**
     * Holds xml representation of document information to be processed.
     *
     * @var DOMDocument Defaults to null.
     */
    protected $xml;

    /**
     * Holds the stylesheet for the transformation.
     *
     * @var DOMDocument Defaults to null.
     */
    protected $xslt;

    /**
     * Holds the xslt processor.
     *
     * @var XSLTProcessor Defaults to null.
     */
    protected $proc;

    /**
     * Holds information about which document state aka server_state
     * are delivered out
     *
     * @var array
     */
    private $deliveringDocumentStates = ['published', 'deleted'];  // maybe deleted documents too

    /**
     * Holds restriction types for xMetaDiss
     *
     * @var array
     */
    private $xMetaDissRestriction = ['doctoralthesis', 'habilitation'];

    /**
     * Hold oai module configuration model.
     *
     * @var Oai_Model_Configuration
     */
    protected $configuration;

    /** @var Oai_Model_XmlFactory */
    private $xmlFactory;

    /** @var string */
    private $scriptPath;

    /** @var string */
    private $baseUrl;

    /** @var string */
    private $baseUri;

    /** @var Zend_Controller_Response_Http */
    private $response; // TODO temporary hack

    /**
     * Gather configuration before action handling.
     */
    public function init()
    {
        $config = $this->getConfig();

        $this->xml           = new DOMDocument();
        $this->proc          = new XSLTProcessor();
        $this->configuration = new Oai_Model_Configuration($config);
        $this->xmlFactory    = new Oai_Model_XmlFactory();
    }

    /**
     * @param array  $parameters
     * @param string $requestUri
     * @return false|string|null
     * @throws Oai_Model_Exception
     * @throws Zend_Controller_Response_Exception
     * @throws Zend_Exception
     */
    public function handleRequest($parameters, $requestUri)
    {
        // TODO move error handling into Oai_Model_Server
        try {
            // handle request
            return $this->handleRequestIntern($parameters, $requestUri);
        } catch (Oai_Model_Exception $e) {
            $errorCode = Oai_Model_Error::mapCode($e->getCode());
            $this->getLogger()->err($errorCode);
            $this->proc->setParameter('', 'oai_error_code', $errorCode);
            $this->getLogger()->err($e->getMessage());
            $this->proc->setParameter('', 'oai_error_message', htmlentities($e->getMessage(), ENT_NOQUOTES));
        } catch (Oai_Model_ResumptionTokenException $e) {
            $this->getLogger()->err($e);
            $this->proc->setParameter('', 'oai_error_code', 'unknown');
            $this->proc->setParameter(
                '',
                'oai_error_message',
                'An error occured while processing the resumption token.'
            );
            $this->getResponse()->setHttpResponseCode(500);
        } catch (Exception $e) {
            $this->getLogger()->err($e);
            $this->proc->setParameter('', 'oai_error_code', 'unknown');
            $this->proc->setParameter('', 'oai_error_message', 'An internal error occured.');
            $this->getResponse()->setHttpResponseCode(500);
        }

        $this->xml = new DOMDocument();

        return $this->proc->transformToXML($this->xml);
    }

    /**
     * Handles an OAI request.
     *
     * @param Oai_Model_Request|array $oaiRequest Contains full request information TODO BUG check parameter type
     * @param string                  $requestUri
     * @throws Oai_Model_Exception Thrown if the request could not be handled.
     * @return string Generated XML
     */
    protected function handleRequestIntern($oaiRequest, $requestUri)
    {
        $this->init();

        // Setup stylesheet
        $this->loadStyleSheet($this->getScriptPath() . '/oai-pmh.xslt');

        $this->setupProcessor();

        $metadataPrefixPath = $this->getScriptPath() . DIRECTORY_SEPARATOR . 'prefixes';
        $resumptionPath     = $this->configuration->getResumptionTokenPath();

        $request = new Oai_Model_Request();
        $request->setPathToMetadataPrefixFiles($metadataPrefixPath);
        $request->setResumptionPath($resumptionPath);

        // check for duplicate parameters
        foreach ($oaiRequest as $name => $value) {
            if (substr_count($requestUri, "&$name") > 1) {
                throw new Oai_Model_Exception(
                    'Parameters must not occur more than once.',
                    Oai_Model_Error::BADARGUMENT
                );
            }
        }

        if (true !== $request->validate($oaiRequest)) {
            throw new Oai_Model_Exception($request->getErrorMessage(), $request->getErrorCode());
        }

        // TODO refactor - temporary hack to have all lower case version of metadataPrefix to use in XSLT
        if (isset($oaiRequest['metadataPrefix'])) {
            $oaiRequest['metadataPrefixMode'] = strtolower($oaiRequest['metadataPrefix']);
            $metadataPrefix                   = $oaiRequest['metadataPrefixMode'];
        } else {
            $metadataPrefix = null;
        }

        foreach ($oaiRequest as $parameter => $value) {
             Log::get()->debug("'oai_' . $parameter, $value");
            $this->proc->setParameter('', 'oai_' . $parameter, $value);
        }

        switch ($oaiRequest['verb']) {
            case 'GetRecord':
                $this->handleGetRecord($oaiRequest);
                break;

            case 'Identify':
                $this->handleIdentify();
                break;

            case 'ListIdentifiers':
                $this->handleListIdentifiers($oaiRequest);
                break;

            case 'ListMetadataFormats':
                $this->handleListMetadataFormats($oaiRequest);
                break;

            case 'ListRecords':
                $this->handleListRecords($oaiRequest);
                break;

            case 'ListSets':
                $this->handleListSets();
                break;

            default:
                throw new Exception('The verb provided in the request is illegal.', Oai_Model_Error::BADVERB);
        }

        $doc = $this->proc->transformToDoc($this->xml);

        // Requests with resumptionToken do not provide metadataPrefix in the URL
        if ($metadataPrefix === null && isset($oaiRequest['metadataPrefixMode'])) {
            $metadataPrefix = $oaiRequest['metadataPrefixMode'];
        }

        // TODO is this something that should happen for all metadataPrefixes (OPUSVIER-4531)
        $metadataPrefixTags = [
            'oai_dc'        => 'dc',
            'oai_pp'        => 'ProPrint',
            'xmetadissplus' => 'xMetaDiss',
            'epicur'        => 'epicur',
            'marc21'        => 'collection',
        ];

        if ($metadataPrefix !== null && isset($metadataPrefixTags[$metadataPrefix])) {
            $tagName = $metadataPrefixTags[$metadataPrefix];

            $records = $doc->getElementsByTagName($tagName);
            foreach ($records as $record) {
                $record->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
            }
        }

        $doc->formatOutput = true;
        return $doc->saveXML();
    }

    /**
     * @throws Zend_Exception
     *
     * TODO factory (function) for processor
     */
    protected function setupProcessor()
    {
        $this->proc->registerPHPFunctions('Opus\Common\Language::getLanguageCode');
        Application_Xslt::registerViewHelper(
            $this->proc,
            [
                'optionValue',
                'fileUrl',
                'frontdoorUrl',
                'transferUrl',
                'dcmiType',
                'dcType',
                'openAireType',
            ]
        );
        $this->proc->setParameter('', 'urnResolverUrl', $this->getConfig()->urn->resolverUrl);
        $this->proc->setParameter('', 'doiResolverUrl', $this->getConfig()->doi->resolverUrl);

        // Set response time
        $this->proc->setParameter(
            '',
            'dateTime',
            str_replace(
                '+00:00',
                'Z',
                (new DateTime())->setTimezone(new DateTimeZone('UTC'))->format(DateTime::RFC3339)
            )
        );

        // set OAI base url
        $this->proc->setParameter('', 'oai_base_url', $this->getOaiBaseUrl());
    }

    /**
     * Implements response for OAI-PMH verb 'GetRecord'.
     */
    protected function handleGetRecord(array &$oaiRequest)
    {
        // Identifier references metadata Urn, not plain Id!
        // Currently implemented as 'oai:foo.bar.de:{docId}' or 'urn:nbn...-123'
        $docId = $this->getDocumentIdByIdentifier($oaiRequest['identifier']);

        $document = null;
        try {
            $document = Document::get($docId);
        } catch (NotFoundException $ex) {
            throw new Oai_Model_Exception(
                'The value of the identifier argument is unknown or illegal in this repository.',
                Oai_Model_Error::IDDOESNOTEXIST
            );
        }

        $metadataPrefix = $oaiRequest['metadataPrefix'];

        // do not deliver documents which are restricted by document state
        if (
            $document === null
            || (false === in_array($document->getServerState(), $this->deliveringDocumentStates))
            || (false === $document->hasEmbargoPassed() && stripos($metadataPrefix, 'xmetadiss') === 0)
        ) {
            throw new Oai_Model_Exception('Document is not available for OAI export!', Oai_Model_Error::NORECORDSMATCH);
        }

        // for xMetaDiss it must be habilitation-thesis or doctoral-thesis
        if ('xMetaDiss' === $metadataPrefix) {
            $type       = $document->getType();
            $isHabOrDoc = in_array($type, $this->xMetaDissRestriction);
            if (false === $isHabOrDoc) {
                throw new Oai_Model_Exception(
                    "The combination of the given values results in an empty list (xMetaDiss only for habilitation"
                    . " and doctoralthesis).",
                    Oai_Model_Error::NORECORDSMATCH
                );
            }
        }
        $this->xml->appendChild($this->xml->createElement('Documents'));

        $this->createXmlRecord($document);
    }

    /**
     * Implements response for OAI-PMH verb 'Identify'.
     */
    protected function handleIdentify()
    {
        $email            = $this->configuration->getEmailContact();
        $repName          = $this->configuration->getRepositoryName();
        $repIdentifier    = $this->configuration->getRepositoryIdentifier();
        $sampleIdentifier = $this->configuration->getSampleIdentifier();

        // Set backup date if database query does not return a date.
        $earliestDate = DateTime::createFromFormat("Y-m-d", '1970-01-01');

        $earliestDateFromDb = Repository::getInstance()->getModelRepository(Document::class)
            ->getEarliestPublicationDate();

        if ($earliestDateFromDb !== null) {
            // TODO: Do we expect the full ISO format or Y-m-d? ZEND_DATE::ISO_8601 was probably less strict here.
            $earliestDate = DateTime::createFromFormat(DateTime::ATOM, $earliestDateFromDb);
            if ($earliestDate === false) {
                $earliestDate = DateTime::createFromFormat("Y-m-d", $earliestDateFromDb);
            }
        }

        $earliestDateIso = $earliestDate->format('Y-m-d');

        // set parameters for oai-pmh.xslt
        $this->proc->setParameter('', 'emailAddress', $email);
        $this->proc->setParameter('', 'repName', $repName);
        $this->proc->setParameter('', 'repIdentifier', $repIdentifier);
        $this->proc->setParameter('', 'sampleIdentifier', $sampleIdentifier);
        $this->proc->setParameter('', 'earliestDate', $earliestDateIso);
        $this->xml->appendChild($this->xml->createElement('Documents'));
    }

    /**
     * Implements response for OAI-PMH verb 'ListIdentifiers'.
     */
    protected function handleListIdentifiers(array &$oaiRequest)
    {
        $maxIdentifier = $this->configuration->getMaxListIdentifiers();
        $this->handlingOfLists($oaiRequest, $maxIdentifier);
    }

    /**
     * Implements response for OAI-PMH verb 'ListMetadataFormats'.
     *
     * @param  array $oaiRequest Contains full request information
     */
    protected function handleListMetadataFormats($oaiRequest)
    {
        if (isset($oaiRequest['identifier'])) {
            try {
                // check for document identifier, but ignore because all documents have same list of formats
                $docId = $this->getDocumentIdByIdentifier($oaiRequest['identifier']);
            } catch (Oai_Model_Exception $ome) {
                // set second error so 'badArgument' and 'idDoesNotExist' are reported back
                $this->proc->setParameter(
                    '',
                    'oai_error_code2',
                    Oai_Model_Error::mapCode(Oai_Model_Error::IDDOESNOTEXIST)
                );
                $this->proc->setParameter(
                    '',
                    'oai_error_message2',
                    'Identifier is invalid and does not exist.'
                );
                throw $ome;
            }
        }

        $this->xml->appendChild($this->xml->createElement('Documents'));
    }

    /**
     * Implements response for OAI-PMH verb 'ListRecords'.
     */
    protected function handleListRecords(array &$oaiRequest)
    {
        $maxRecords = $this->configuration->getMaxListRecords();
        $this->handlingOfLists($oaiRequest, $maxRecords);
    }

    /**
     * Implements response for OAI-PMH verb 'ListSets'.
     */
    protected function handleListSets()
    {
        $repIdentifier = $this->configuration->getRepositoryIdentifier();

        $this->proc->setParameter('', 'repIdentifier', $repIdentifier);
        $this->xml->appendChild($this->xml->createElement('Documents'));

        $oaiSets = new Oai_Model_Sets();

        $sets = $oaiSets->getSets();

        foreach ($sets as $type => $name) {
            $opusDoc   = $this->xml->createElement('Opus_Sets');
            $typeAttr  = $this->xml->createAttribute('Type');
            $typeValue = $this->xml->createTextNode($type);
            $typeAttr->appendChild($typeValue);
            $opusDoc->appendChild($typeAttr);
            $nameAttr  = $this->xml->createAttribute('TypeName');
            $nameValue = $this->xml->createTextNode($name);
            $nameAttr->appendChild($nameValue);
            $opusDoc->appendChild($nameAttr);
            $this->xml->documentElement->appendChild($opusDoc);
        }
    }

    /**
     * Helper method for handling lists.
     *
     * @param mixed $maxRecords
     */
    private function handlingOfLists(array &$oaiRequest, $maxRecords)
    {
        if (true === empty($maxRecords)) {
            $maxRecords = 100;
        }

        $repIdentifier = $this->configuration->getRepositoryIdentifier();
        $tempPath      = $this->configuration->getResumptionTokenPath();

        $this->proc->setParameter('', 'repIdentifier', $repIdentifier);
        $this->xml->appendChild($this->xml->createElement('Documents'));

        // do some initialisation
        $cursor   = 0;
        $totalIds = 0;
        $start    = $maxRecords + 1;
        $restIds  = [];

        $metadataPrefix = null;
        if (true === array_key_exists('metadataPrefix', $oaiRequest)) {
            $metadataPrefix = $oaiRequest['metadataPrefix'];
        }

        $set = null;
        if (true === array_key_exists('set', $oaiRequest)) {
            $set = $oaiRequest['set'];
        }

        $tokenWorker = new Oai_Model_Resumptiontokens();
        $tokenWorker->setResumptionPath($tempPath);

        $resumed = false;

        if (false === empty($oaiRequest['resumptionToken'])) {
            // parameter resumptionToken is given
            $resParam = $oaiRequest['resumptionToken'];
            $token    = $tokenWorker->getResumptionToken($resParam);

            if ($token === null) {
                throw new Oai_Model_Exception("file could not be read.", Oai_Model_Error::BADRESUMPTIONTOKEN);
            }

            $cursor         = $token->getStartPosition() - 1;
            $start          = $token->getStartPosition() + $maxRecords;
            $totalIds       = $token->getTotalIds();
            $restIds        = $token->getDocumentIds();
            $metadataPrefix = $token->getMetadataPrefix();
            $set            = $token->getSet();

            $oaiRequest['metadataPrefix']     = $metadataPrefix;
            $oaiRequest['metadataPrefixMode'] = strtolower($metadataPrefix);
            $this->proc->setParameter('', 'oai_metadataPrefix', $metadataPrefix);
            $this->proc->setParameter('', 'oai_metadataPrefixMode', strtolower($metadataPrefix));
            if ($set !== null) {
                $this->proc->setParameter('', 'oai_set', $set);
            }
            $resumed = true;
        } else {
            // no resumptionToken is given
            $docListModel                           = new Oai_Model_DocumentList();
            $docListModel->deliveringDocumentStates = $this->deliveringDocumentStates;
            $docListModel->xMetaDissRestriction     = $this->xMetaDissRestriction;
            $restIds                                = $docListModel->query($oaiRequest);
            $totalIds                               = count($restIds);
        }

        // handling of document ids
        $workIds = array_splice($restIds, 0, $maxRecords);

        foreach ($workIds as $docId) {
            $document = Document::get($docId);
            $this->createXmlRecord($document);
        }

        // no records returned
        if (true === empty($workIds)) {
            throw new Oai_Model_Exception(
                "The combination of the given values results in an empty list.",
                Oai_Model_Error::NORECORDSMATCH
            );
        }

        // store the further Ids in a resumption-file
        $countRestIds = count($restIds);

        if ($countRestIds > 0) {
            $token = new Oai_Model_Resumptiontoken();
            $token->setStartPosition($start);
            $token->setTotalIds($totalIds);
            $token->setDocumentIds($restIds);
            $token->setMetadataPrefix($metadataPrefix);
            $token->setSet($set);

            $tokenWorker->storeResumptionToken($token);

            // set parameters for the resumptionToken-node
            $res = $token->getResumptionId();

            $this->setParamResumption($res, $cursor, $totalIds);
        } elseif ($resumed) {
            // generate empty resumptionToken element for last block of records
            $this->setParamResumption('', null, $totalIds);
        }
    }

    /**
     * Set parameters for resumptionToken-line.
     *
     * @param string $res value of the resumptionToken
     * @param int    $cursor value of the cursor
     * @param int    $totalIds value of the total Ids
     */
    private function setParamResumption($res, $cursor, $totalIds)
    {
        $tomorrow = str_replace(
            '+00:00',
            'Z',
            (new DateTime())->modify('+1 day')->setTimezone(new DateTimeZone('UTC'))->format(DateTime::RFC3339)
        );

        $this->proc->setParameter('', 'dateDelete', $tomorrow);
        $this->proc->setParameter('', 'res', $res);
        $this->proc->setParameter('', 'cursor', $cursor ?? '');
        $this->proc->setParameter('', 'totalIds', $totalIds);
    }

    /**
     * Create xml structure for one record
     *
     * @param DocumentInterface $document
     */
    private function createXmlRecord($document)
    {
        $docId   = $document->getId();
        $domNode = $this->getDocumentXmlDomNode($document);

        // add frontdoor url
        $this->addFrontdoorUrlAttribute($domNode, $docId);

        // add ddb transfer element
        $this->addDdbTransferElement($domNode, $docId);

        // add access rights to element
        $this->addAccessRights($domNode, $document);

        // remove file elements which should not be exported through OAI
        // Iterating over DOMNodeList is only save for readonly-operations;
        // copy element-by-element before removing!
        $filenodes     = $domNode->getElementsByTagName('File');
        $filenodesList = [];
        foreach ($filenodes as $filenode) {
            $filenodesList[] = $filenode;

            // add file download urls
            $this->addFileUrlAttribute($filenode, $docId, $filenode->getAttribute('PathName'));
        }

        // remove file elements which should not be exported through OAI
        foreach ($filenodesList as $filenode) {
            if (
                (false === $filenode->hasAttribute('VisibleInOai'))
                || ('1' !== $filenode->getAttribute('VisibleInOai'))
            ) {
                $domNode->removeChild($filenode);
            }
        }

        $node = $this->xml->importNode($domNode, true);

        $dcTypeHelper = new Application_View_Helper_DcType();

        $type = $document->getType();
        $this->addSpecInformation($node, 'doc-type:' . $dcTypeHelper->dcType($type));

        $bibliography = $document->getBelongsToBibliography() === 1 ? 'true' : 'false';
        $this->addSpecInformation($node, 'bibliography:' . $bibliography);

        $logger   = $this->getLogger();
        $setSpecs = Oai_Model_SetSpec::getSetSpecsFromCollections($document->getCollection());
        foreach ($setSpecs as $setSpec) {
            if (preg_match("/^([A-Za-z0-9\-_\.!~\*'\(\)]+)(:[A-Za-z0-9\-_\.!~\*'\(\)]+)*$/", $setSpec)) {
                $this->addSpecInformation($node, $setSpec);
                continue;
            }
            $logger->info("skipping invalid setspec: " . $setSpec);
        }

        $this->xml->documentElement->appendChild($node);
    }

    /**
     * Add spec header information to DOM document.
     *
     * @param mixed $information
     */
    private function addSpecInformation(DOMNode $document, $information)
    {
        $setSpecAttribute      = $this->xml->createAttribute('Value');
        $setSpecAttributeValue = $this->xml->createTextNode($information);
        $setSpecAttribute->appendChild($setSpecAttributeValue);

        $setSpecElement = $this->xml->createElement('SetSpec');
        $setSpecElement->appendChild($setSpecAttribute);
        $document->appendChild($setSpecElement);
    }

    /**
     * Add the frontdoorurl attribute to Document XML output.
     *
     * @param DOMNode $document Document XML serialisation
     * @param string  $docid    Id of the document
     */
    private function addFrontdoorUrlAttribute(DOMNode $document, $docid)
    {
        $url = $this->getBaseUrl() . '/frontdoor/index/index/docId/' . $docid;

        $owner = $document->ownerDocument;
        $attr  = $owner->createAttribute('frontdoorurl');
        $attr->appendChild($owner->createTextNode($url));
        $document->appendChild($attr);
    }

    /**
     * Add download link url attribute to Document XML output.
     *
     * @param DOMNode $file     Document XML serialisation
     * @param string  $docid    Id of the document
     * @param string  $filename File path name
     */
    private function addFileUrlAttribute($file, $docid, $filename)
    {
        $url = $this->getBaseUrl() . '/files/' . $docid . '/' . rawurlencode($filename);

        $owner = $file->ownerDocument;
        $attr  = $owner->createAttribute('url');
        $attr->appendChild($owner->createTextNode($url));
        $file->appendChild($attr);
    }

    /**
     * Add <ddb:transfer> element for ddb container file.
     *
     * @param DOMNode $document Document XML serialisation
     * @param string  $docid    Document ID
     */
    private function addDdbTransferElement(DOMNode $document, $docid)
    {
        $url = $this->getBaseUrl() . '/oai/container/index/docId/' . $docid;

        $fileElement = $document->ownerDocument->createElement('TransferUrl');
        $fileElement->setAttribute('PathName', $url);
        $document->appendChild($fileElement);
    }

    /**
     * Add rights element to output.
     *
     * @param DOMNode           $domNode
     * @param DocumentInterface $doc
     */
    private function addAccessRights($domNode, $doc)
    {
        $fileElement = $domNode->ownerDocument->createElement('Rights');
        $fileElement->setAttribute('Value', $this->xmlFactory->getAccessRights($doc));
        $domNode->appendChild($fileElement);
    }

    /**
     * Retrieve a document id by an oai identifier.
     *
     * @param string $oaiIdentifier
     * @return int
     */
    private function getDocumentIdByIdentifier($oaiIdentifier)
    {
        $identifierParts = explode(":", $oaiIdentifier);

        $docId = null;
        switch ($identifierParts[0]) {
            case 'urn':
                $finder = Repository::getInstance()->getDocumentFinder();
                $finder->setIdentifierValue('urn', $oaiIdentifier);
                $finder->setServerState($this->deliveringDocumentStates);
                $docIds = $finder->getIds();
                $docId  = $docIds[0];
                break;
            case 'oai':
                if (isset($identifierParts[2])) {
                    $docId = $identifierParts[2];
                }
                break;
            default:
                throw new Oai_Model_Exception(
                    'The prefix of the identifier argument is unknown.',
                    Oai_Model_Error::BADARGUMENT
                );
                break;
        }

        if (empty($docId) || ! preg_match('/^\d+$/', $docId)) {
            throw new Oai_Model_Exception(
                'The value of the identifier argument is unknown or illegal in this repository.',
                Oai_Model_Error::IDDOESNOTEXIST
            );
        }

        return $docId;
    }

    /**
     * @param DocumentInterface $document
     * @return DOMNode
     * @throws Exception
     */
    private function getDocumentXmlDomNode($document)
    {
        if (! in_array($document->getServerState(), $this->deliveringDocumentStates)) {
            $message = 'Trying to get a document in server state "' . $document->getServerState() . '"';
             Log::get()->err($message);
            throw new Exception($message);
        }

        $xmlModel = new Xml();
        $xmlModel->setModel($document);
        $xmlModel->excludeEmptyFields();
        $xmlModel->setStrategy(new Version1());
        $xmlModel->setXmlCache(Repository::getInstance()->getDocumentXmlCache());
        return $xmlModel->getDomDocument()->getElementsByTagName('Opus_Document')->item(0);
    }

    /**
     * @return string
     */
    private function getOaiBaseUrl()
    {
        $oaiBaseUrl = $this->configuration->getOaiBaseUrl();

        // if no OAI base url is set, use local information as base url
        if (true === empty($oaiBaseUrl)) {
            $oaiBaseUrl = $this->getBaseUrl() . '/oai'; // TODO . $module;
        }

        return $oaiBaseUrl;
    }

    /**
     * Load an xslt stylesheet.
     *
     * @param string $stylesheet
     */
    protected function loadStyleSheet($stylesheet)
    {
        $this->xslt = new DOMDocument();
        $this->xslt->load($stylesheet);
        $this->proc->importStyleSheet($this->xslt);
        if (isset($_SERVER['HTTP_HOST'])) {
            $this->proc->setParameter('', 'host', $_SERVER['HTTP_HOST']);
        }
        $this->proc->setParameter('', 'server', $this->getBaseUri());
    }

    /**
     * @return string
     */
    public function getScriptPath()
    {
        return $this->scriptPath;
    }

    /**
     * @param string $scriptPath
     */
    public function setScriptPath($scriptPath)
    {
        $this->scriptPath = $scriptPath;
    }

    /**
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * @param string $baseUrl
     */
    public function setBaseUrl($baseUrl)
    {
        $this->baseUrl = $baseUrl;
    }

    /**
     * @return string
     */
    public function getBaseUri()
    {
        return $this->baseUri;
    }

    /**
     * @param string $baseUri
     */
    public function setBaseUri($baseUri)
    {
        $this->baseUri = $baseUri;
    }

    /**
     * @param Zend_Controller_Response_Http $response
     */
    public function setResponse($response)
    {
        $this->response = $response;
    }

    /**
     * @return Zend_Controller_Response_Http
     */
    public function getResponse()
    {
        return $this->response;
    }
}
