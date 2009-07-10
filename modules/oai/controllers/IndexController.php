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
                          'optional' => array('from', 'until', 'set')),
                    array('required' => array('resumptionToken'),
                          'optional' => array()),
                    ),
                'ListIdentifiers' => array(
                    array('required' => array('metadataPrefix'),
                          'optional' => array('from', 'until', 'set')),
                    array('required' => array('resumptionToken'),
                          'optional' => array()),
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
                        } catch (Exception $e) {
                            throw $e;
                        }
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
     * Validates resumption token.
     *
     * @param  string  $oaiResumptionToken The resumption token to validate.
     * @throws Exception Thrown if the resumptionToken is not valid.
     * @return void
     */
    private function __validateResumptionToken($oaiResumptionToken) {
        // TODO: Implement resumption token handling.
        if (true === empty($oaiResumptionToken)) {
            // Resumption token not valid.
            throw new Exception("The resumptionToken $oaiResumptionToken does not exist or has already expired.",
                    self::BADRESUMPTIONTOKEN);
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
        $registry = Zend_Registry::getInstance();
        $config = $registry->get('Zend_Config');
        $email = $config->mail->opus->address;
        $repName = $config->oai->repository->name;
        $repIdentifier = $config->oai->repository->identifier;
        $sampleIdentifier = $config->oai->sample->identifier;
        $this->_proc->setParameter('', 'emailAddress', 'mailto:'.$email);
        $this->_proc->setParameter('', 'repName', $repName);
        $this->_proc->setParameter('', 'repIdentifier', $repIdentifier);
        $this->_proc->setParameter('', 'sampleIdentifier', $sampleIdentifier);
        $this->_xml->appendChild($this->_xml->createElement('Documents'));
    }

    /**
     * Implements response for OAI-PMH verb 'ListIdentifiers'.
     *
     * @return void
     */
    private function __handleListIdentifiers($oaiRequest) {

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
        $this->_xml->appendChild($this->_xml->createElement('Documents'));
        $documents = Opus_Document::getAll();
        foreach ($documents as $document) {
            $node = $this->_xml->importNode($document->toXml()->getElementsByTagName('Opus_Document')->item(0), true);
            $this->_xml->documentElement->appendChild($node);
        }
    }

    /**
     * Implements response for OAI-PMH verb 'ListSets'.
     *
     * @return void
     */
    private function __handleListSets($oaiRequest) {

    }

}
