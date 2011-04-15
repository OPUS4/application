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
 * @author      Thoralf Klein <thoralf.klein@zib.de>
 * @copyright   Copyright (c) 2009 - 2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class Oai_IndexController extends Controller_Xml {

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

    /**
     * Gather configuration before action handling.
     *
     * @return void
     */
    public function init() {
        parent::init();

        $registry = Zend_Registry::getInstance();
        $config = $registry->get('Zend_Config');

        $this->_configuration = new Oai_Model_Configuration($config);
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
            if (true === array_key_exists($parameter, $oaiRequest)) {
                unset($oaiRequest[$parameter]);
            }
        }

        try {
            $this->__handleRequest($oaiRequest);
        } catch (Exception $e) {
            switch ($e->getCode()) {
                case Oai_Model_Error::BADVERB:
                    $errorCode = 'badVerb';
                    break;
                case Oai_Model_Error::BADARGUMENT:
                    $errorCode = 'badArgument';
                    break;
                case Oai_Model_Error::NORECORDSMATCH:
                    $errorCode = 'noRecordsMatch';
                    break;
                    case Oai_Model_Error::CANNOTDISSEMINATEFORMAT:
                    $errorCode = 'cannotDisseminateFormat';
                    break;
                case Oai_Model_Error::BADRESUMPTIONTOKEN:
                    $errorCode = 'badResumptionToken';
                    break;
                default:
                    $errorCode = 'unknown';
            }
            Zend_Registry::get('Zend_Log')->err($errorCode);
            $this->_proc->setParameter('', 'oai_error_code', $errorCode);
            Zend_Registry::get('Zend_Log')->err($e->getMessage());
            $this->_proc->setParameter('', 'oai_error_message', htmlentities($e->getMessage()));
        }
    }

    private function getOaiBaseUrl() {
        $oai_base_url = $this->_configuration->getOaiBaseUrl();

        // if no OAI base url is set, use local information as base url
        if (true === empty($oai_base_url)) {
            $request = $this->getRequest();
            $base = $request->getBaseUrl();
            $host = $request->getHttpHost();
            $scheme = $request->getScheme();
            $module = $request->getModuleName();
            $oai_base_url = $scheme . '://' . $host . $base . '/' . $module;
        }

        return $oai_base_url;
    }

    /**
     * Handles an OAI request.
     *
     * @param  array  $oaiRequest Contains full request information
     * @throws Exception Thrown if the request could not be handled.
     * @return void
     */
    private function __handleRequest(array $oaiRequest) {
        // Setup stylesheet
        $this->loadStyleSheet($this->view->getScriptPath('index') . '/oai-pmh.xslt');

        // Set response time
        $this->_proc->setParameter('', 'dateTime', str_replace('+00:00', 'Z', Zend_Date::now()->setTimeZone('UTC')->getIso()));

        // set OAI base url
        $this->_proc->setParameter('', 'oai_base_url', $this->getOaiBaseUrl());

        try {

            $metadataPrefixPath = $this->view->getScriptPath('index') . DIRECTORY_SEPARATOR . 'prefixes';
            $resumptionPath = $this->_configuration->getResumptionTokenPath();

            $request = new Oai_Model_Request();
            $request->setPathToMetadataPrefixFiles($metadataPrefixPath);
            $request->setResumptionPath($resumptionPath);

            if (true === $request->validate($oaiRequest)) {
                foreach ($oaiRequest as $parameter => $value) {
                    Zend_Registry::get('Zend_Log')->err("'oai_' . $parameter, $value");
                    $this->_proc->setParameter('', 'oai_' . $parameter, $value);
                }

                $callname = '__handle' . $oaiRequest['verb'];
                $this->$callname($oaiRequest);
            } else {
                throw new Exception($request->getErrorMessage(), $request->getErrorCode());
            }
        } catch (Exception $e) {
            throw $e;
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
        // Currently implemented as 'oai:foo.bar.de:{docId}'
        $docId = substr(strrchr($oaiRequest['identifier'], ':'), 1);

        $document = null;
        try {
            $document = new Opus_Document($docId);
        } catch (Exception $ex) {
            throw new Exception('The value of the identifier argument is unknown or illegal in this repository.', Oai_Model_Error::BADARGUMENT);
        }

        // do not deliver documents which are restricted by document state
        if (is_null($document) or false === in_array($document->getServerState(), $this->_deliveringDocumentStates)) {
            throw new Exception('Document is not available for OAI export!', Oai_Model_Error::NORECORDSMATCH);
        }

        // for xMetaDiss it must be habilitation-thesis or doctoral-thesis
        if ('xMetaDiss' === $oaiRequest['metadataPrefix']) {
            $type = $document->getType();
            $isHabOrDoc = in_array($type, $this->_xMetaDissRestriction);
            if (false === $isHabOrDoc) {
               throw new Exception("The combination of the given values results in an empty list (xMetaDiss only for habilitation and doctoralthesis).", Oai_Model_Error::NORECORDSMATCH);
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
    private function __handleIdentify(array &$oaiRequest) {

        $email = $this->_configuration->getEmailContact();
        $repName = $this->_configuration->getRepositoryName();
        $repIdentifier = $this->_configuration->getRepositoryIdentifier();
        $sampleIdentifier = $this->_configuration->getSampleIdentifier();

        // Set backup date if database query does not return a date.
        $earliestDate = new Zend_Date('1970-01-01', Zend_Date::ISO_8601);

        $earliestDateFromDb = Opus_Document::getEarliestPublicationDate();
        if (!is_null($earliestDateFromDb) and trim($earliestDateFromDb) != '') {
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
    private function __handleListIdentifiers(array &$oaiRequest) {

        $max_identifier = $this->_configuration->getMaxListIdentifiers();
        $this->_handlingOfLists($oaiRequest, $max_identifier);

    }

    /**
     * Implements response for OAI-PMH verb 'ListMetadataFormats'.
     *
     * @param  array &$oaiRequest Contains full request information
     * @return void
     */
    private function __handleListMetadataFormats(array &$oaiRequest) {
        $this->_xml->appendChild($this->_xml->createElement('Documents'));

    }

    /**
     * Implements response for OAI-PMH verb 'ListRecords'.
     *
     * @param  array &$oaiRequest Contains full request information
     * @return void
     */
    private function __handleListRecords(array &$oaiRequest) {

        $max_records = $this->_configuration->getMaxListRecords();
        $this->_handlingOfLists($oaiRequest, $max_records);

    }

    /**
     * Implements response for OAI-PMH verb 'ListSets'.
     *
     * @param  array &$oaiRequest Contains full request information
     * @return void
     */
    private function __handleListSets(array &$oaiRequest) {
        $repIdentifier = $this->_configuration->getRepositoryIdentifier();

        $this->_proc->setParameter('', 'repIdentifier', $repIdentifier);
        $this->_xml->appendChild($this->_xml->createElement('Documents'));

        $sets = array();
        foreach (Opus_Document::fetchDocumentTypes() AS $doctype) {
            $sets['pub-type:'.$doctype] = $doctype;
        }

        foreach ($sets as $type => $name) {
            $opus_doc = $this->_xml->createElement('Opus_Sets');
            $type_attr = $this->_xml->createAttribute('Type');
            $type_value = $this->_xml->createTextNode($type);
            $type_attr->appendChild($type_value);
            $opus_doc->appendChild($type_attr);
            $name_attr = $this->_xml->createAttribute('TypeName');
            $name_value = $this->_xml->createTextNode($name);
            $name_attr->appendChild($name_value);
            $opus_doc->appendChild($name_attr);
            $this->_xml->documentElement->appendChild($opus_doc);
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
     * Create xml structure for one record
     *
     * @param  Opus_Document $document
     * @return void
     */
    private function createXmlRecord(Opus_Document $document) {
        $docId = $document->getId();
        $documentXml = new Util_DocumentXmlCache($docId);
        $domNode = $documentXml->getNode();

        // add frontdoor url
        $this->_addFrontdoorUrlAttribute($domNode, $docId);

        // add container file element
        $this->_addContainerFileElement($domNode, $docId);

        // remove file elements which should not be exported through OAI
        $filenodes = $domNode->getElementsByTagName('File');
        foreach ($filenodes as $filenode) {
            if ((false === $filenode->hasAttribute('VisibleInOai'))
                or ('1' !== $filenode->getAttribute('VisibleInOai'))) {
                $domNode->removeChild($filenode);
            }
        }

        // add file download urls
        $filenodes = $domNode->getElementsByTagName('File');
        foreach ($filenodes as $filenode) {
            $this->_addFileUrlAttribute($filenode, $docId, $filenode->getAttribute('PathName'));
        }

        $node = $this->_xml->importNode($domNode, true);

        $type = $document->getType();
        $this->_addSpecInformation($node, 'pub-type:' . $type);

        $bibliography = $document->getBelongsToBibliography() == 1 ? 'true' : 'false';
        $this->_addSpecInformation($node, 'bibliography:' . $bibliography);

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

        $set_spec_attribute = $this->_xml->createAttribute('Value');
        $set_spec_attribute_value = $this->_xml->createTextNode($information);
        $set_spec_attribute->appendChild($set_spec_attribute_value);

        $set_spec_element = $this->_xml->createElement('SetSpec');
        $set_spec_element->appendChild($set_spec_attribute);
        $document->appendChild($set_spec_element);
    }

    /**
     * Add the frontdoorurl attribute to Opus_Document XML output.
     *
     * @param DOMNode $document Opus_Document XML serialisation
     * @param string  $docid    Id of the document
     * @return void
     */
    private function _addFrontdoorUrlAttribute(DOMNode $document, $docid) {
        $url = $this->view->serverUrl() . $this->getRequest()->getBaseUrl() . '/frontdoor/index/index/docId/' . $docid;
        
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
        $url = $this->view->serverUrl() . $this->getRequest()->getBaseUrl() . '/files/' . $docid . '/' . $filename;

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
     * Add <File> element for container file if present.
     *
     * @param DOMNode $document Opus_Document XML serialisation
     * @param string  $docId    Document id
     * @return void
     */
    private function _addContainerFileElement(DOMNode $document, $docId) {
        // TODO
        return;

        $config = Zend_Registry::get('Zend_Config');
        $destPath = $config->file->destinationPath;
        $containerFile = "$destPath/$docId/container/container.zip";

        // TODO Remove hard coded container path
        if (true === file_exists($containerFile)) {
            $owner = $document->ownerDocument;
            $fileElement = $owner->createElement('File');
            $fileElement->setAttribute('PathName', 'container/container.zip');
            $fileElement->setAttribute('FileSize', filesize($containerFile));
            $fileElement->setAttribute('MimeType', mime_content_type($containerFile));
            $fileElement->setAttribute('OaiExport', '1');
            $fileElement->setAttribute('DnbContainer', '1');
            $document->appendChild($fileElement);
        }

    }

    /**
     * Retrieve a document id by an oai identifier.
     * 
     * @param string $oaiIdentifier
     * @result int
     */
    private function getDocumentIdByOaiIdentifier($oaiIdentifier) {
        // currently oai identifers are not stored in database
        // workaround this by urn identifier
        $urnPrefix = 'urn:nbn:de';
        $localPrefix = '%'; // Workaround for different local prefixes
        $identifierInfo = mb_substr(mb_strrchr($oaiIdentifier, ':'), 1);
        $urnIdentifier = $urnPrefix . ':' . $localPrefix . ':' . $identifierInfo;

        $result = Opus_Document::getDocumentByIdentifier($urnIdentifier);
        if (null === $result) {
            $result = -1;
        }
        return $result;
    }

    /**
     * Helper method for handling lists.
     *
     * @param array &$oaiRequest
     * @param mixed $max_records
     * @return void
     */
    private function _handlingOfLists(array &$oaiRequest, $max_records) {

        if (true === empty($max_records)) {
            $max_records = 100;
        }

        $repIdentifier = $this->_configuration->getRepositoryIdentifier();
        $tempPath = $this->_configuration->getResumptionTokenPath();

        $this->_proc->setParameter('', 'repIdentifier', $repIdentifier);
        $this->_xml->appendChild($this->_xml->createElement('Documents'));
        // do some initialisation
        $cursor = 0;
        $totalIds = 0;
        $res = '';
        $resParam = '';
        $start = $max_records + 1;
        $restIds = array();
        $reldocIds = array();

        $metadataPrefix = null;
        if (true === array_key_exists('metadataPrefix', $oaiRequest)) {
            $metadataPrefix = $oaiRequest['metadataPrefix'];
        }

        $token = new Oai_Model_Resumptiontoken;

        $tokenWorker = new Oai_Model_Resumptiontokens;
        $tokenWorker->setResumptionPath($tempPath);

        // parameter resumptionToken is given
        if (false === empty($oaiRequest['resumptionToken'])) {

            $resParam = $oaiRequest['resumptionToken'];
            $token = $tokenWorker->getResumptionToken($resParam);

            if (true === is_null($token)) {
                throw new Exception("file could not be read.", Oai_Model_Error::BADRESUMPTIONTOKEN);
            }

            $cursor = $token->getStartPosition() - 1;
            $start = $token->getStartPosition() + $max_records;
            $totalIds = $token->getTotalIds();
            $reldocIds = $token->getDocumentIds();
            $metadataPrefix = $token->getMetadataPrefix();
            $this->_proc->setParameter('', 'oai_metadataPrefix', $metadataPrefix);

        // no resumptionToken is given
        } else {
            $reldocIds = $this->getDocumentIdsByOaiRequest($oaiRequest);
        }

        // handling of document ids
        $restIds = $reldocIds;
        $workIds = array_splice($restIds, 0, $max_records);
        foreach ($workIds as $docId) {
            $document = new Opus_Document($docId);
            $this->createXmlRecord($document);
        }

        // no records returned
        if (true === empty($workIds)) {
            throw new Exception("The combination of the given values results in an empty list.", Oai_Model_Error::NORECORDSMATCH);
        }

        // store the further Ids in a resumption-file
        $countRestIds = count($restIds);
        if ($countRestIds > 0) {
            if (0 === $totalIds) {
                $totalIds = $max_records + $countRestIds;
            }

            $token->setStartPosition($start);
            $token->setTotalIds($totalIds);
            $token->setDocumentIds($restIds);
            $token->setMetadataPrefix($metadataPrefix);

            $tokenWorker->storeResumptionToken($token);

            $res = $token->getResumptionId();
        }

        // set parameters for the resumptionToken-node
        if ((false === empty($resParam)) || ($countRestIds > 0)) {
            $this->setParamResumption($res, $cursor, $totalIds);
        }
    }

    /**
     * Retrieve all document ids for a valid oai request.
     *
     * @param array &$oaiRequest
     * @return array
     */
    private function getDocumentIdsByOaiRequest(array &$oaiRequest) {
        $result = array();

        $restriction = array();
        $restriction['ServerState'] = $this->_deliveringDocumentStates;

        $setInfo = null;
        if (true === array_key_exists('set', $oaiRequest)) {
            $setarray = explode(':', $oaiRequest['set']);
            $setInfo = $setarray[1];
        }

        $restriction['Type'] = array();
        if (('xMetaDiss' === $oaiRequest['metadataPrefix']) or
            ('xmetadissfis' === $oaiRequest['metadataPrefix'])) {
            $restriction['Type'] = $this->_xMetaDissRestriction;
            // if we have xMetaDiss as metadataPrefix format
            // then we could not accept sets with other values than above
            if (false === empty($setInfo)) {
                if (false === in_array($setInfo, $restriction['Type'])) {
                    throw new Exception("The combination of the given values results in an empty list.", Oai_Model_Error::NORECORDSMATCH);
                }
                $restriction['Type'] = array($setInfo);
            }
        } else {
            if (false === empty($setInfo)) {
                $restriction['Type'][] = $setInfo;
            }
        }

        $fromDate = null;
        if (true === array_key_exists('from', $oaiRequest)) {
            $fromDate = $oaiRequest['from'];
        }

        $untilDate = null;
        if (true === array_key_exists('until', $oaiRequest)) {
            $untilDate = $oaiRequest['until'];
        }

        $restriction['Date'] = array(
            'from' => $fromDate,
            'until' => $untilDate,
            'dateFormat' => 'yyyy-MM-dd'
            );

        $result = Opus_Document::getIdsOfOaiRequest($restriction);
        return $result;
    }
}
