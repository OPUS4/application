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
 * @copyright   Copyright (c) 2009, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Main entry point for this module.
 *
 * @category    Application
 * @package     Module_Oai
 */
class Oai_IndexController extends Controller_Xml {

    const BADVERB = 0;
    const BADARGUMENT = 1;
    const CANNOTDISSEMINATEFORMAT = 2;
    const BADRESUMPTIONTOKEN = 3;
    const NORECORDSMATCH = 4;
    /**
     * Holds valid OAI parameters.
     *
     * @var array  Valid OAI parameters.
     */
    protected static $_validArguments = array(
                'verb',
                'identifier',
                'metadataPrefix',
                'from',
                'until',
                'set',
                'resumptionToken',
            );

    /**
     * Holds valid OAI queries, i.e. parameter combinations.
     *
     * @var array  Valid OAI queries.
     */
    protected static $_validQueries = array(
                'GetRecord' => array(
                    array('required' => array('identifier', 'metadataPrefix'),
                          'optional' => array()),
                    ),
                'ListRecords' => array(
                    array('required' => array('metadataPrefix'),
                          'optional' => array('from', 'until', 'set','resumptionToken')),
                    ),
                'ListIdentifiers' => array(
                    array('required' => array('metadataPrefix'),
                          'optional' => array('from', 'until', 'set','resumptionToken')),
//                    array('required' => array('resumptionToken'),
//                          'optional' => array()),
                    ),
                'ListSets' => array(
                    array('required' => array(),
                          'optional' => array()),
                    ),
                'ListMetadataFormats' => array(
                    array('required' => array(),
                          'optional' => array()),
                    ),
                'Identify' => array(
                    array('required' => array(),
                          'optional' => array()),
                    ),
            );

    /**
     * Entry point for all OAI-PMH requests.
     *
     * @return void
     */
    public function indexAction() {
        try {
            $this->__handleRequest($this->getRequest()->getQuery());
        } catch (Exception $e) {
            switch ($e->getCode()) {
                case self::BADVERB:
                    $errorCode = 'badVerb';
                    break;
                case self::BADARGUMENT:
                    $errorCode = 'badArgument';
                    break;
                case self::NORECORDSMATCH:
                    $errorCode = 'noRecordsMatch';
                    break;
                    case self::CANNOTDISSEMINATEFORMAT:
                    $errorCode = 'cannotDisseminateFormat';
                    break;
                case self::BADRESUMPTIONTOKEN:
                    $errorCode = 'badResumptionToken';
                    break;
                default:
            }
            $this->_proc->setParameter('', 'oai_error_code', $errorCode);
            $this->_proc->setParameter('', 'oai_error_message', $e->getMessage());
        }
    }

    /**
     * Handles an OAI request.
     *
     * @param  array  $oaiRequest
     * @throws Exception Thrown if the request could not be handled.
     * @return void
     */
    private function __handleRequest(array $oaiRequest) {
        // Setup stylesheet
        $this->loadStyleSheet($this->view->getScriptPath('index') . '/oai-pmh.xslt');
        // Set response time
        $this->_proc->setParameter('', 'dateTime', date('c'));
        $base = $this->getRequest()->getBaseUrl();
        $host = $this->getRequest()->getHttpHost();
        $scheme = $this->getRequest()->getScheme();
        $module = $this->getRequest()->getModuleName();
        $oai_base_url = $scheme . '://' . $host . $base . '/' . $module;
        $this->_proc->setParameter('', 'oai_base_url', $oai_base_url);

        try {
            foreach ($oaiRequest as $parameter => $value) {
                $this->_proc->setParameter('', 'oai_' . $parameter, $value);
            }
            $this->__validateRequest($oaiRequest);
            $callname = '__handle' . $oaiRequest['verb'];
            $this->$callname($oaiRequest);
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Validates parameters of an OAI request.
     *
     * @param  array  $oaiRequest The request to validate.
     * @throws Exception Thrown if the request is not valid.
     * @return void
     */
    private function __validateRequest(array $oaiRequest) {
        // Evaluate if a proper verb was supplied.
        if (false === array_key_exists('verb', $oaiRequest) or
            false === in_array($oaiRequest['verb'], array_keys(self::$_validQueries))) {
            // Invalid or unspecified Verb
            throw new Exception('The verb provided in the request is illegal.', self::BADVERB);
        }

        // Evaluate if any invalid parameters are provided
        $invalidArguments = array_diff(array_keys($oaiRequest), self::$_validArguments);
        if (false === empty($invalidArguments)) {
            // Error occured
            throw new Exception(implode(', ', $invalidArguments), self::BADARGUMENT);
        }

        // Evaluate if the query is valid, i.e. check for proper parameter combinations.
        $oaiParameters = array_diff(array_keys($oaiRequest), array('verb'));
        foreach (self::$_validQueries[$oaiRequest['verb']] as $validRequest) {
            $missingRequiredParameters = array_diff($validRequest['required'], $oaiParameters);
            $unknownParameters = array_diff($oaiParameters, array_merge($validRequest['required'],
                        $validRequest['optional']));
            if (false === empty($missingRequiredParameters)) {
                // Missing required parameter
                throw new Exception('Missing parameter(s) ' . implode(', ', $missingRequiredParameters), self::BADARGUMENT);
            } else if (false === empty($unknownParameters)) {
                // Superflous parameter
                throw new Exception('badArgument', self::BADARGUMENT);
            } else {
                foreach ($oaiRequest as $parameter => $value) {
                    $callname = '__validate' . ucfirst($parameter);
                    if (true === method_exists($this, $callname)) {
                        try {
                            $this->$callname($value);
                            if ($parameter == 'from') {
                                $fromdate = $value;
                            }
                            if ($parameter == 'until') {
                                $untildate = $value;
                            }
                        } catch (Exception $e) {
                            throw $e;
                        }
                    }
                }
                // Proof combination of from and until
                if (!empty($fromdate) && !empty($untildate)) {
                    try {
                        $this->__validateFromUntil($fromdate,$untildate);
                    }  catch (Exception $e) {
                         throw $e;
                    }
                }
                break;
            }
        }
    }

    /**
     * Checks the availability of a metadataPrefix.
     *
     * @param  string  $oaiMetadataPrefix The metadataPrefix to check for.
     * @throws Exception Thrown if the metadataPrefix is not available.
     * @return void
     */
    private function __validateMetadataPrefix($oaiMetadataPrefix) {
        $availableMetadataPrefixes = array();
        $prefixPath = $this->view->getScriptPath('index') . '/prefixes';
        foreach (glob($prefixPath . '/*.xslt') as $prefixFile) {
           $availableMetadataPrefixes[] = basename($prefixFile, '.xslt');
        }
        if (false === in_array($oaiMetadataPrefix, $availableMetadataPrefixes)) {
            // MetadataPrefix not available.
            throw new Exception("The metadata format $oaiMetadataPrefix given by metadataPrefix is not supported by the item or this repository.",self::CANNOTDISSEMINATEFORMAT);
        }
    }


    /**
     * Checks the availability of given parameter from.
     *
     * @param  string  $oaiFrom The date to check for.
     * @throws Exception Thrown if the date isn't a correct date.
     * @return void
     */
    private function __validateFrom($oaiFrom) {
        try {
          $from = new Zend_Date($oaiFrom);
        } catch(exception $e) {
             throw new Exception('The date from is not a correct date',self::BADARGUMENT);
          }
    }

    /**
     * Checks the availability of given parameter until.
     *
     * @param  string  $oaiUntil The date to check for.
     * @throws Exception Thrown if the date isn't a correct date.
     * @return void
     */
    private function __validateUntil($oaiUntil) {
        try {
          $until = new Zend_Date($oaiUntil);
        } catch(exception $e) {
             throw new Exception('The date until is not a correct date.',self::BADARGUMENT);
          }
    }

    /**
     * Checks wheather from <= until.
     *
     * @param  string  $oaiFrom,$oaiUntil The dates to check for.
     * @throws Exception Thrown if $oaiFrom > $oaiUntil.
     * @return void
     */
    private function __validateFromUntil($from,$until) {
        $datefrom = new DateTime($from);
        $dateuntil = new DateTime($until);
        if ($datefrom > $dateuntil) {
            throw new Exception("The date $from is greater than the date $until.",self::BADARGUMENT);
        }
    }
    /**
     * Validates resumption token.
     *
     * @param  string  $oaiResumptionToken The resumption token to validate.
     * @throws Exception Thrown if the resumptionToken is not valid.
     * @return void
     */
    private function __validateResumptionToken($oaiResumptionToken) {
        $registry = Zend_Registry::getInstance();
        $config = $registry->get('Zend_Config');
        $tempPath = $config->path->workspace->temp;
        $fn = $tempPath.'/resumption/rs_'.$oaiResumptionToken.'.txt';
        if (!file_exists($fn)) {
            throw new Exception("The resumptionToken $oaiResumptionToken does not exist or has already expired.",self::BADRESUMPTIONTOKEN);
        }
    }

    /**
     * Implements response for OAI-PMH verb 'GetRecord'.
     *
     * @return void
     */
    private function __handleGetRecord($oaiRequest) {
        // Identifier references metadata Urn, not plain Id!
        // Currently implemented as 'oai:foo.bar.de:{docId}'
        $docId = substr(strrchr($oaiRequest['identifier'], ':'), 1);
        $document = new Opus_Document($docId);
        // for xMetaDiss it must be habilitation or doctoral-thesis
        if ($oaiRequest['metadataPrefix'] == 'xMetaDiss') {
            $is_hab_doc = $this->filterDocType($document);
            if ($is_hab_doc == 0) {
               throw new Exception("The combination of the given values results in an empty list (xMetaDiss only for habilitation and doctoral_thesis).", self::NORECORDSMATCH);
            }
        }
        $this->_xml->appendChild($this->_xml->createElement('Documents'));
        $node = $this->_xml->importNode($document->toXml()->getElementsByTagName('Opus_Document')->item(0), true);
        $this->_xml->documentElement->appendChild($node);
    }

    /**
     * Implements response for OAI-PMH verb 'Identify'.
     *
     * @return void
     */
    private function __handleIdentify($oaiRequest) {
        // get values from config.ini
        $registry = Zend_Registry::getInstance();
        $config = $registry->get('Zend_Config');
        $email = $config->mail->opus->address;
        $repName = $config->oai->repository->name;
        $repIdentifier = $config->oai->repository->identifier;
        $sampleIdentifier = $config->oai->sample->identifier;
        $earliestDate = Opus_Document::getEarliestPublicationDate();
        // set parameters for oai-pmh.xslt
        $this->_proc->setParameter('', 'emailAddress', 'mailto:'.$email);
        $this->_proc->setParameter('', 'repName', $repName);
        $this->_proc->setParameter('', 'repIdentifier', $repIdentifier);
        $this->_proc->setParameter('', 'sampleIdentifier', $sampleIdentifier);
        $this->_proc->setParameter('', 'earliestDate', $earliestDate);
        $this->_xml->appendChild($this->_xml->createElement('Documents'));
    }

    /**
     * Implements response for OAI-PMH verb 'ListIdentifiers'.
     *
     * @return void
     */
    private function __handleListIdentifiers($oaiRequest) {
        // get values from config.ini
        $registry = Zend_Registry::getInstance();
        $config = $registry->get('Zend_Config');
        $repIdentifier = $config->oai->repository->identifier;
        $max_identifier = $config->oai->max->listidentifiers;
        $tempPath = $config->path->workspace->temp;
        $this->_proc->setParameter('', 'repIdentifier', $repIdentifier);
        $this->_xml->appendChild($this->_xml->createElement('Documents'));
        // do some initialisation
        $id_max = 0;
        $cursor = 0;
        $totalIds = 0;
        $res = '';
        $resParam = '';
        $start = $max_identifier + 1;
        $restIds = array();
        $ri = 0;
        // parameter resumptionToken is given
        if (!empty($oaiRequest['resumptionToken'])) {
            // read the resumption file
            $resParam = $oaiRequest['resumptionToken'];
            $fn = $tempPath.'/resumption/rs_'.$resParam.'.txt';
            $data = file_get_contents($fn);
            if ($data != false) {
                $data = explode(' ',$data);
                // first entry is startposition, second entry is total number
                $cursor = $data[0] - 1;
                $start = $data[0] + $max_identifier;
                $totalIds = $data[1];
                $reldocIds = array();
                $j = 0;
                for ($i=2; $i <= count($data)-2; $i++) {
                    $reldocIds[$j] = $data[$i];
                    $j++;
                }
                // handling all Ids of the resumption file
                foreach ($reldocIds as $docId) {
                  $id_max++;
                  // create xml-document
                  if ($id_max <= $max_identifier) {
                     $document = new Opus_Document($docId);
                     $this->xmlCreationIdentifiers($document);
                  }
                  // store the further Ids
                  else {
                      $restIds[$ri] = $docId;
                      $ri++;
                  }
                }
            } else {
                 throw new Exception("file could not be read.", self::NORECORDSMATCH);
            }
        // TODO cronjob for removing files and not here, because token has to be repeatable
        unlink($fn);

        // no resumptionToken is given
        } else {
            // get document-Ids depending given daterange
            $docIds = $this->filterDocDate($oaiRequest);
            // handling all documents
            foreach ($docIds as $docId) {
                $document = new Opus_Document($docId);
                $in_output = 1;
                // for xMetaDiss only give habilitation and doctoral-thesis
                if ($oaiRequest['metadataPrefix'] == 'xMetaDiss') {
                    $in_output = $this->filterDocType($document);
                }
                // only published documents
                if ($in_output == 1) {
                    $in_output = $this->filterDocPublished($document);
                }
                // TODO if ($in_output == 1) $in_output = $this->filterDocSet($document);
                if ($in_output == 1) {$id_max++;}
                if ($in_output == 1) {
                    // create xml-document
                    if ($id_max <= $max_identifier) {
                      $this->xmlCreationIdentifiers($document);
                   }
                   // store the further Ids
                   else {
                      $restIds[$ri] = $docId;
                      $ri++;
                   }
                }
          }
        }
        // no records returned
        if ($id_max == 0) {
            throw new Exception("The combination of the given values results in an empty list.", self::NORECORDSMATCH);
           }

        // store the further Ids in a resumption-file
        if (count($restIds) > 0) {
            if ($totalIds == 0) $totalIds = $max_identifier + count($restIds);
            $res = $this->writeResumptionFile($start,$totalIds,$tempPath,$restIds);
        }

        // set parameters for the resumptionToken-node
        if (!empty($resParam) || count($restIds) > 0) {
            $this->setParamResumption($res,$cursor,$totalIds);
        }

    }


    /**
     * Implements response for OAI-PMH verb 'ListMetadataFormats'.
     *
     * @return void
     */
    private function __handleListMetadataFormats($oaiRequest) {
        $this->_xml->appendChild($this->_xml->createElement('Documents'));

    }

    /**
     * Implements response for OAI-PMH verb 'ListRecords'.
     *
     * @return void
     */
    private function __handleListRecords($oaiRequest) {
        // get values from config.ini
        $registry = Zend_Registry::getInstance();
        $config = $registry->get('Zend_Config');
        $repIdentifier = $config->oai->repository->identifier;
        $max_records = $config->oai->max->listrecords;
        $tempPath = $config->path->workspace->temp;
        $this->_proc->setParameter('', 'repIdentifier', $repIdentifier);
        $this->_xml->appendChild($this->_xml->createElement('Documents'));
        // do some initialisation
        $id_max = 0;
        $cursor = 0;
        $totalIds = 0;
        $res = '';
        $resParam = '';
        $start = $max_records + 1;
        $restIds = array();
        $ri = 0;
        // parameter resumptionToken is given
        if (!empty($oaiRequest['resumptionToken'])) {
            // read the resumption file
            $resParam = $oaiRequest['resumptionToken'];
            $fn = $tempPath.'/resumption/rs_'.$resParam.'.txt';
            $data = file_get_contents($fn);
            if ($data != false) {
                $data = explode(' ',$data);
                // first entry is startposition, second entry is total number
                $cursor = $data[0] - 1;
                $start = $data[0] + $max_records;
                $totalIds = $data[1];
                $reldocIds = array();
                $j = 0;
                for ($i=2; $i <= count($data)-2; $i++) {
                    $reldocIds[$j] = $data[$i];
                    $j++;
                }
                // handling all Ids of the resumption file
                foreach ($reldocIds as $docId) {
                  $id_max++;
                  if ($id_max <= $max_records) {
                      $document = new Opus_Document($docId);
                      $node = $this->_xml->importNode($document->toXml()->getElementsByTagName('Opus_Document')->item(0), true);
                      $this->_xml->documentElement->appendChild($node);
                  }
                  else {
                      $restIds[$ri] = $docId;
                      $ri++;
                  }
                }
            } else {
                 throw new Exception("file could not be read.", self::NORECORDSMATCH);
            }
        // TODO cronjob for removing files and not here, because token has to be repeatable
        unlink($fn);

        // no resumptionToken is given
        } else {
            // get document-Ids depending given daterange
            $docIds = $this->filterDocDate($oaiRequest);
            // handling all documents
            foreach ($docIds as $docId) {
               $document = new Opus_Document($docId);
               $in_output = 1;
               // for xMetaDiss only give Habilitation or doctoral-thesis
               if ($oaiRequest['metadataPrefix'] == 'xMetaDiss') {
                   $in_output = $this->filterDocType($document);
               }
               // only published documents
               if ($in_output == 1) {
                   $in_output = $this->filterDocPublished($document);
               }
               // TODO if ($in_output == 1) $in_output = $this->filterDocSet($document);
               if ($in_output == 1) {$id_max++;}
               if ($in_output == 1) {
                   if ($id_max <= $max_records) {
                      $node = $this->_xml->importNode($document->toXml()->getElementsByTagName('Opus_Document')->item(0), true);
                      $this->_xml->documentElement->appendChild($node);
                   }
                   else {
                       $restIds[$ri] = $docId;
                       $ri++;
                   }
               }
          }
        }
        // no records returned
        if ($id_max == 0) {
            throw new Exception("The combination of the given values results in an empty list.", self::NORECORDSMATCH);
           }

        // store the further Ids in a resumption-file
        if (count($restIds) > 0) {
            if ($totalIds == 0) $totalIds = $max_records + count($restIds);
            $res = $this->writeResumptionFile($start,$totalIds,$tempPath,$restIds);
        }

        // set parameters for the resumptionToken-node
        if (!empty($resParam) || count($restIds) > 0) {
            $this->setParamResumption($res,$cursor,$totalIds);
        }
    }


    /**
     * Implements response for OAI-PMH verb 'ListSets'.
     *
     * @return void
     */
    private function __handleListSets($oaiRequest) {
        $registry = Zend_Registry::getInstance();
        $config = $registry->get('Zend_Config');
        $repIdentifier = $config->oai->repository->identifier;
        $this->_proc->setParameter('', 'repIdentifier', $repIdentifier);
        $this->_xml->appendChild($this->_xml->createElement('Documents'));
        $types = Opus_Document_Type::getAvailableTypeNames();
        foreach ($types as $type) {
            $opus_doc = $this->_xml->createElement('Opus_Sets');
            $type_attr = $this->_xml->createAttribute("Type");
            $type_value = $this->_xml->createTextNode($type);
            $type_attr->appendChild($type_value);
            $opus_doc->appendChild($type_attr);
            $name_attr = $this->_xml->createAttribute("TypeName");
            $name_value = $this->_xml->createTextNode($type);
            $name_attr->appendChild($name_value);
            $opus_doc->appendChild($name_attr);
            $this->_xml->documentElement->appendChild($opus_doc);
        }
    }

    /**
     * Give Document-Ids, which are in daterange.
     *
     * @param  array  $oaiRequest
     * @return array $docIds, which are in daterange
     */
    private function filterDocDate($oaiRequest) {
        $docIds = array();
        $from = NULL;
        $until = NULL;
        if (true === array_key_exists('from',$oaiRequest)) {
            $from = $oaiRequest['from'];
        }
        if (true === array_key_exists('until',$oaiRequest)) {
            $until = $oaiRequest['until'];
        }
        $docIds = Opus_Document::getIdsForDateRange($from,$until);
        if (count($docIds) == 0) {
            throw new Exception("The combination of the given values results in an empty list.", self::NORECORDSMATCH);
        }
       return $docIds;

    }


    /**
     * Handles, if a Document has state published.
     *
     * @param  Opus_Document  $document the document to be proofed
     * @return int $result, 1 oder 0, decides, wheather document is in output or not
     */
    private function filterDocPublished($document) {
       $result = 0;
       $server_state = $document->getServerState();
       if ($server_state == 'published') {
           $result = 1;
       }
       return $result;
    }

    /**
     * Handles, if a Document belongs to a given set.
     *
     * @param  Opus_Document  $document the document to be proofed
     * @return int $result, 1 oder 0, decides, wheather document is in output or not
     */
    private function filterDocSet($document) {

    }

    /**
     * Handles, if a Document belongs to type habilitation or doctoral_thesis.
     *
     * @param  Opus_Document  $document the document to be proofed
     * @return int $result, 1 oder 0, decides, wheather document is in output or not
     */
    private function filterDocType($document) {
       $result = 0;
       $type = $document->getType();
       if ($type == 'habilitation' || $type == 'doctoral_thesis') {
           $result = 1;
       }
       return $result;
    }


    /**
     * Set parameters for resumptionToken-line.
     *
     * @param  string  $res value of the resumptionToken
     * @param  int     $cursor value of the cursor
     * @param  int     $totalIds value of the total Ids
     */
    private function setParamResumption($res,$cursor,$totalIds) {
       $today = new Zend_Date();
       $today->add(1,Zend_Date::DAY);
       $tomorrow = $today->get('yyyy-MM-ddThh-mm-ss');
       $this->_proc->setParameter('', 'dateDelete', $tomorrow);
       $this->_proc->setParameter('', 'res', $res);
       $this->_proc->setParameter('', 'cursor', $cursor);
       $this->_proc->setParameter('', 'totalIds', $totalIds);
    }


    /**
     * Set parameters for resumptionToken-line.
     *
     * @param  int     $start, start value of the file
     * @param  int     $totalIds, value of all Ids
     * @param  string  $tempPath, path for the resumption files
     * @param  array   $restIds, array of ids to store
     * @return string  $res, value for resumptionToken
     */
    private function writeResumptionFile($start,$totalIds,$tempPath,$restIds) {
            $fc = 0;
            $fn = $tempPath.'/resumption/rs_'.(string) time();
            while (file_exists($file=sprintf('%s%02d.txt',$fn,$fc++)));
            if ($fp = fopen($file,'w+')) {
                fwrite($fp,$start.' '.$totalIds.' ');
                foreach ($restIds as $restId) {
                    if (fwrite($fp,$restId.' ')) {
                       } else {
                           throw new Exception("file could not be written.", self::NORECORDSMATCH);
                         }
                    }
                fclose($fp);
            } else {
            throw new Exception("file could not be opened.", self::NORECORDSMATCH);
            }
            $start_res = strpos($file,'rs_');
            $res = substr($file,$start_res+3,12);
            return $res;
    }

    /**
     * Create xml for ListIdentifiers, only information for header is necessary
     *
     * @param  Opus_Document $document
     */
    private function xmlCreationIdentifiers($document) {
       $date_mod = $document->getServerDateModified();
       $date_pub = $document->getServerDatePublished();
       $opus_doc = $this->_xml->createElement('Opus_Document');
       if (!empty($date_mod)) {
            $date_mod_attr = $this->_xml->createAttribute("ServerDateModified");
            $date_mod_value = $this->_xml->createTextNode($date_mod);
            $date_mod_attr->appendChild($date_mod_value);
            $opus_doc->appendChild($date_mod_attr);
       }
       $date_pub_attr = $this->_xml->createAttribute("ServerDatePublished");
       $date_pub_value = $this->_xml->createTextNode($date_pub);
       $date_pub_attr->appendChild($date_pub_value);
       $opus_doc->appendChild($date_pub_attr);
       $this->_xml->documentElement->appendChild($opus_doc);
    }

}
