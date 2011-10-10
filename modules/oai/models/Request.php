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
 * @category   Application
 * @package    Module_Oai
 * @author     Henning Gerhardt (henning.gerhardt@slub-dresden.de)
 * @copyright  Copyright (c) 2009, OPUS 4 development team
 * @license    http://www.gnu.org/licenses/gpl.html General Public License
 * @version    $Id$
 */

/**
 * TODO
 */
class Oai_Model_Request {

    /**
     * TODO
     *
     * @var string
     */
    private $_dateFormat = 'yyyy-MM-dd';

    /**
     * TODO
     *
     * @var mixed
     */
    private $_errorCode = null; 

    /**
     * TODO
     *
     * @var mixed
     */
    private $_errorMessage = null;

    /**
     * TODO
     *
     * @var mixed
     */
    private $_pathToMetadataPrefixFiles = null;

    /**
     * TODO
     *
     * @var mixed
     */
    private $_resumptionPath = null;

    /**
     * TODO
     *
     * @var array
     */
    private $_validArguments = array(
            'verb',
            'identifier',
            'metadataPrefix',
            'from',
            'until',
            'set',
            'resumptionToken',
        );

    /**
     * TODO
     *
     * @var array
     */
    private $_validQueries = array(
            'GetRecord' => array(
                array('required' => array('identifier', 'metadataPrefix'),
                      'optional' => array()),
                ),
            'ListRecords' => array(
                array('required' => array('metadataPrefix'),
                      'optional' => array('from', 'until', 'set')
                      ),
                array('required' => array('resumptionToken'),
                      'optional' => array()
                    ),
                ),
            'ListIdentifiers' => array(
                array('required' => array('metadataPrefix'),
                      'optional' => array('from', 'until', 'set')
                      ),
                array('required' => array('resumptionToken'),
                      'optional' => array()
                    ),
                ),
            'ListSets' => array(
                array('required' => array(),
                      'optional' => array()
                      ),
                array('required' => array('resumptionToken'),
                      'optional' => array()
                    ),
                ),
            'ListMetadataFormats' => array(
                array('required' => array(),
                      'optional' => array('identifier')),
                ),
            'Identify' => array(
                array('required' => array(),
                      'optional' => array()),
                ),
        );

    /**
     * Checks for a valide date
     *
     * @param &$date Date string to proof
     * @return boolean
     */
    private function checkDate(&$date) {
        // simple proofing
        $result = Zend_Date::isDate($date, $this->_dateFormat);

        if (true === $result) {
             $zd = new Zend_Date($date, $this->_dateFormat);
             $result = $date === $zd->get($this->_dateFormat);
        }
        
        return $result;
    }

    /**
     * Checks if given from date is in preferred date format.
     *
     * @param mixed &$date
     * @return boolean
     */
    private function _validateFrom(&$date) {
        $result = $this->checkDate($date);

        if (false === $result) {
            $this->setErrorCode(Oai_Model_Error::BADARGUMENT);
            $this->setErrorMessage('From date "' . $date . '" is not a correct date format ("' . strtoupper($this->_dateFormat) . '").');
        }
        
        return $result;
    }

    /**
     * Checks the availability of a metadataPrefix.
     *
     * @param mixed $oaiMetadataPrefix
     * @return boolean
     */
    private function _validateMetadataPrefix($oaiMetadataPrefix) {

        // we assuming that a metadata prefix file ends with xslt
        $possibleFiles = glob($this->_pathToMetadataPrefixFiles . DIRECTORY_SEPARATOR . '*.xslt');

        $availableMetadataPrefixes = array('xMetaDissPlus'); // we support both spellings, xMetaDissPlus and XMetaDissPlus
        foreach ($possibleFiles as $prefixFile) {
           $availableMetadataPrefixes[] = basename($prefixFile, '.xslt');
        }

        $result = in_array($oaiMetadataPrefix, $availableMetadataPrefixes);

        if (false === $result) {
            // MetadataPrefix not available.
            $this->setErrorCode(Oai_Model_Error::CANNOTDISSEMINATEFORMAT);
            $this->setErrorMessage('The metadata format "' . $oaiMetadataPrefix . '" given by metadataPrefix is not supported by the item or this repository.');
        }

        return $result;
    }

    /**
     * Checks the availability of given parameter set.
     *
     * @param  string  $oaiSet The set to check for.
     * @return boolean
     */
    private function _validateSet($oaiSet) {
        $setInfo = explode(':', $oaiSet);

        $result = true;
        if ((2 !== count($setInfo)) or
            ('pub-type' !== $setInfo[0])) {
            $this->setErrorCode(Oai_Model_Error::BADARGUMENT);
            $this->setErrorMessage('The given set is not correct');
            $result = false;
        }

        return $result;
    }

    /**
     * Checks if until date is in preferred date format.
     *
     * @param mixed $date
     * @return boolean
     */
    private function _validateUntil(&$date) {
        $result = $this->checkDate($date);

        if (false === $result) {
            $this->setErrorCode(Oai_Model_Error::BADARGUMENT);
            $this->setErrorMessage('Until date "' . $date . '" is not a correct date format ("' . strtoupper($this->_dateFormat) . '").');
        }

        return $result;
    }

    /**
     * Checks if from date is before until date.
     *
     * @param mixed $from
     * @param mixed $until
     * @return boolean
     */
    private function _validateFromUntilRange($from, $until) {

        $result = $this->_validateFrom($from);
        if (false === $result) {
            return false;
        }

        $result = $this->_validateUntil($until);
        if (false === $result) {
            return $result;
        }

        $result = true;

        $untilDate = new Zend_Date($until, $this->_dateFormat);
        $isEqual = $untilDate->equals($from, $this->_dateFormat);
        $isLater = $untilDate->isLater($from, $this->_dateFormat);

        if ((false === $isEqual) and (false === $isLater)) {
            $this->setErrorCode(Oai_Model_Error::BADARGUMENT);
            $this->setErrorMessage('Date "' . $from . '" is later than date "' . $until . '".');
            $result = false;
        }

        return $result;
    }

    /**
     * Validates resumption token.
     *
     * @param  string  $oaiResumptionToken The resumption token to validate.
     * @return boolean
     */
    private function _validateResumptionToken($oaiResumptionToken) {

        $tokenWorker = new Oai_Model_Resumptiontokens;

        try {
            $tokenWorker->setResumptionPath($this->_resumptionPath);
        } catch (Exception $e) {
            // FIXME: should a configuration error hidden like in this case?
            $this->setErrorCode(Oai_Model_Error::BADRESUMPTIONTOKEN);
            $this->setErrorMessage('Directory for resumption tokens not valid. Error reason: ' . $e->getMessage());
            return false;
        }

        $result = $tokenWorker->validateResumptionToken($oaiResumptionToken);

        if (false === $result) {
            $this->setErrorCode(Oai_Model_Error::BADRESUMPTIONTOKEN);
            $this->setErrorMessage('The resumptionToken "' . $oaiResumptionToken . '" does not exist or has already expired.');
        }

        return $result;
    }

    /**
     * Returns current error code.
     *
     * @return mixed
     */
    public function getErrorCode() {
        return $this->_errorCode;
    }

    /**
     * Returns current error message.
     *
     * @return string
     */
    public function getErrorMessage() {
        return $this->_errorMessage;
    }

    /**
     * Set current error code.
     *
     * @param mixed $code
     * @return void
     */
    protected function setErrorCode($code) {
        $this->_errorCode = $code;
    }

    /**
     * Set current error message.
     *
     * @param string $message
     * @return void
     */
    protected function setErrorMessage($message) {
        $this->_errorMessage = $message;
    }

    /**
     * Set path to meta data prefix files. 
     * Returns false if given path is not a directory.
     * There is no check if files are inside given directory!
     *
     * @param mixed $path
     * @return boolean
     */
    public function setPathToMetadataPrefixFiles($path) {
        $realpathToFiles = realpath($path); 

        $result = is_dir($realpathToFiles);

        if (true === $result) {
            $this->_pathToMetadataPrefixFiles = $realpathToFiles;
        }

        return $result;
    }

    /**
     * Set path to directory where resumption tokens read / stored.
     * Checks only if given path is a directory.
     * Returns false if given path is not a directory.
     *
     * @param mixed $path
     * @return boolean
     */
    public function setResumptionPath($path) {
        $realpathToFiles = realpath($path); 

        $result = is_dir($realpathToFiles);

        if (true === $result) {
            $this->_resumptionPath = $realpathToFiles;
        }

        return $result;
    }

    /**
     * Validate a given oai request.
     *
     * @param array $request
     * @return boolean
     */
    public function validate(array $oaiRequest) {
        $logger = Zend_Registry::get('Zend_Log');

        $errorInformation = array(
            'message' => 'General oai request validation error.',
            'code' => Oai_Model_Error::BADARGUMENT,
        );

        // Evaluate if a proper verb was supplied.
        if ((false === array_key_exists('verb', $oaiRequest)) or
            (false === in_array($oaiRequest['verb'], array_keys($this->_validQueries)))) {
            // Invalid or unspecified Verb
            $this->setErrorCode(Oai_Model_Error::BADVERB);
            $this->setErrorMessage('The verb provided in the request is illegal.');
            $logger->err( $this->getErrorCode() . "::" .$this->getErrorMessage() );
            return false;
        }

        // Evaluate if any invalid parameters are provided
        $invalidArguments = array_diff(array_keys($oaiRequest), $this->_validArguments);
        if (false === empty($invalidArguments)) {
            // Error occured
            $this->setErrorCode(Oai_Model_Error::BADARGUMENT);
            $this->setErrorMessage(implode(', ', $invalidArguments));
            $logger->err( $this->getErrorCode() . "::" .$this->getErrorMessage() );
            return false;
        }

        // Evaluate if the query is valid, i.e. check for proper parameter combinations.
        $oaiParameters = array_diff(array_keys($oaiRequest), array('verb'));

        $valid = false;
        foreach ($this->_validQueries[$oaiRequest['verb']] as $validRequest) {

            $valid = true;

            $missingRequiredParameters = array_diff(
                    $validRequest['required'], 
                    $oaiParameters
                );

            $unknownParameters = array_diff(
                    $oaiParameters, 
                    array_merge($validRequest['required'], $validRequest['optional'])
                );

            if (false === empty($missingRequiredParameters)) {
                // Missing required parameter
                $errorInformation = array(
                        'message' => 'Missing parameter(s) ' . implode(', ', $missingRequiredParameters), 
                        'code' => Oai_Model_Error::BADARGUMENT
                    );
                $valid = false;
            } else if (false === empty($unknownParameters)) {
                // Superflous parameter
                $errorInformation = array(
                        'message' => 'badArgument ' . implode(', ', $unknownParameters),
                        'code' => Oai_Model_Error::BADARGUMENT
                    );
                $valid = false;
            }

            if (true === $valid) {
                $errorInformation = array(
                        'message' => 'no validation error',
                        'code' => null
                    );
                break;
            }
        }

        if (false === $valid) {
            $this->setErrorMessage($errorInformation['message']);
            $this->setErrorCode($errorInformation['code']);
            $logger->err( $this->getErrorCode() . "::" .$this->getErrorMessage() );
            return false;
        }

        // check if request values are valid

        foreach ($oaiRequest as $parameter => $value) {
            $callname = '_validate' . ucfirst($parameter);
            if (true === method_exists($this, $callname)) {
                $result = $this->$callname($value);

                // if one validate call returns an error
                // do not check other parameter values.
                if (false === $result) {
                    // error code and message are set inside validate method
                    $logger->err( ":: $parameter" );
                    return false;
                }
            }
        }

        // Proof combination of from and until
        if ((true === array_key_exists('from', $oaiRequest)) and
            (true === array_key_exists('until', $oaiRequest))) {
                return $this->_validateFromUntilRange($oaiRequest['from'], $oaiRequest['until']);
        }

        return true;
    }

}
