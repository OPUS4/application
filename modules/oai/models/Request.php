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
 * @copyright  Copyright (c) 2009, OPUS 4 development team
 * @license    http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\Date;
use Opus\Common\Log;
use Opus\Common\Security\Realm;

/**
 * TODO documentation is not existent - especially the fact that 'validate' functions are called dynamically
 *
 * @category Application
 * @package Module_Oai
 */
class Oai_Model_Request
{
    const DATE_FORMAT = 'Y-m-d';

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
    private $_validArguments = [
            'verb',
            'identifier',
            'metadataPrefix',
            'from',
            'until',
            'set',
            'resumptionToken',
        ];

    /**
     * TODO
     *
     * @var array
     */
    private $_validQueries = [
            'GetRecord' => [
                ['required' => ['identifier', 'metadataPrefix'],
                      'optional' => []],
                ],
            'ListRecords' => [
                ['required' => ['metadataPrefix'],
                      'optional' => ['from', 'until', 'set']
                      ],
                ['required' => ['resumptionToken'],
                      'optional' => []
                    ],
                ],
            'ListIdentifiers' => [
                ['required' => ['metadataPrefix'],
                      'optional' => ['from', 'until', 'set']
                      ],
                ['required' => ['resumptionToken'],
                      'optional' => []
                    ],
                ],
            'ListSets' => [
                ['required' => [],
                      'optional' => []
                      ],
                ['required' => ['resumptionToken'],
                      'optional' => []
                    ],
                ],
            'ListMetadataFormats' => [
                ['required' => [],
                      'optional' => ['identifier']],
                ],
            'Identify' => [
                ['required' => [],
                      'optional' => []],
                ],
        ];

    /**
     * Checks for a valide date
     *
     * @param $date Date string to proof
     * @return boolean
     */
    public function checkDate($datestr)
    {
        // simple proofing
        $date = DateTime::createFromFormat(self::DATE_FORMAT, $datestr);
        return $date !== false && $date->format(self::DATE_FORMAT) === $datestr;
    }

    /**
     * Checks the availability of a metadataPrefix.
     *
     * @param string $oaiMetadataPrefix
     * @return boolean
     *
     * TODO handling case insensitivity of metadataPrefix is spread through the code (here and other places)
     * TODO function handles access control in addition to checking if format is supported (mixed responsibilities)
     */
    private function validateMetadataPrefix($oaiMetadataPrefix)
    {
        // we assuming that a metadata prefix file ends with xslt
        $possibleFiles = glob($this->_pathToMetadataPrefixFiles . DIRECTORY_SEPARATOR . '*.xslt');

        // we support both spellings, xMetaDissPlus and XMetaDissPlus TODO really?
        $availableMetadataPrefixes = ['xMetaDissPlus'];
        foreach ($possibleFiles as $prefixFile) {
            $availableMetadataPrefixes[] = strtolower(basename($prefixFile, '.xslt'));
        }

        // only administrators can request copy_xml format
        if (! Realm::getInstance()->checkModule('admin')) {
            $availableMetadataPrefixes = array_diff($availableMetadataPrefixes, ['copy_xml']);
        }

        $result = in_array(strtolower($oaiMetadataPrefix), $availableMetadataPrefixes);

        if (false === $result) {
            // MetadataPrefix not available.
            $this->setErrorCode(Oai_Model_Error::CANNOTDISSEMINATEFORMAT);
            $this->setErrorMessage(
                "The metadataPrefix '$oaiMetadataPrefix' is not supported by the item or this repository."
            );
        }

        return $result;
    }

    /**
     * Checks if given 'from' date is valid.
     *
     * @param string $from
     * @return boolean
     */
    private function validateFrom($from)
    {
        if (! $this->checkDate($from)) {
            $this->setErrorMessage("From date '$from' is not a correct date format (" . self::DATE_FORMAT . ').');
            $this->setErrorCode(Oai_Model_Error::BADARGUMENT);
            return false;
        }

        return true;
    }

    /**
     * Checks if given 'until' date is valid.
     *
     * @param string $until
     * @return boolean
     */
    private function validateUntil($until)
    {
        if (! $this->checkDate($until)) {
            $this->setErrorMessage("Until date '$until' is not a correct date format (" . self::DATE_FORMAT . ').');
            $this->setErrorCode(Oai_Model_Error::BADARGUMENT);
            return false;
        }

        return true;
    }

    /**
     * Checks if from date is before until date.
     *
     * @param string $from
     * @param string $until
     * @return boolean
     */
    public function validateFromUntilRange($from, $until)
    {
        if (! $this->validateFrom($from) || ! $this->validateUntil($until)) {
            return false;
        }

        $result = true;

        $untilDate = DateTime::createFromFormat(self::DATE_FORMAT, $until);
        $fromDate  = DateTime::createFromFormat(self::DATE_FORMAT, $from);

        $isEqual = $untilDate->getTimestamp() === $fromDate->getTimestamp();
        $isLater = $untilDate->getTimestamp() > $fromDate->getTimestamp();

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
     * IMPORTANT function may be called dynamically in 'validate' function
     *
     * @param  string $oaiResumptionToken The resumption token to validate.
     * @return boolean
     */
    private function validateResumptionToken($oaiResumptionToken)
    {
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
            $this->setErrorMessage(
                'The resumptionToken "' . $oaiResumptionToken . '" does not exist or has already expired.'
            );
        }

        return $result;
    }

    /**
     * Returns current error code.
     *
     * @return mixed
     */
    public function getErrorCode()
    {
        return $this->_errorCode;
    }

    /**
     * Returns current error message.
     *
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->_errorMessage;
    }

    /**
     * Set current error code.
     *
     * @param mixed $code
     * @return void
     */
    protected function setErrorCode($code)
    {
        $this->_errorCode = $code;
    }

    /**
     * Set current error message.
     *
     * @param string $message
     * @return void
     */
    protected function setErrorMessage($message)
    {
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
    public function setPathToMetadataPrefixFiles($path)
    {
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
    public function setResumptionPath($path)
    {
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
     * @param array $oaiRequest
     * @return boolean
     */
    public function validate($oaiRequest)
    {
        $logger = Log::get();

        $errorInformation = [
            'message' => 'General oai request validation error.',
            'code' => Oai_Model_Error::BADARGUMENT,
        ];

        // Evaluate if a proper verb was supplied.
        if ((false === array_key_exists('verb', $oaiRequest)) or
            (false === in_array($oaiRequest['verb'], array_keys($this->_validQueries)))) {
            // Invalid or unspecified Verb
            $this->setErrorCode(Oai_Model_Error::BADVERB);
            $this->setErrorMessage('The verb provided in the request is illegal.');
            $logger->err($this->getErrorCode() . "::" .$this->getErrorMessage());
            return false;
        }

        // Evaluate if any invalid parameters are provided
        $invalidArguments = array_diff(array_keys($oaiRequest), $this->_validArguments);
        if (false === empty($invalidArguments)) {
            // Error occured
            $this->setErrorCode(Oai_Model_Error::BADARGUMENT);
            $this->setErrorMessage(implode(', ', $invalidArguments));
            $logger->err($this->getErrorCode() . "::" .$this->getErrorMessage());
            return false;
        }

        // Evaluate if the query is valid, i.e. check for proper parameter combinations.
        $oaiParameters = array_diff(array_keys($oaiRequest), ['verb']);

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
                $errorInformation = [
                        'message' => 'Missing parameter(s) ' . implode(', ', $missingRequiredParameters),
                        'code' => Oai_Model_Error::BADARGUMENT
                    ];
                $valid = false;
            } elseif (false === empty($unknownParameters)) {
                // Superflous parameter
                $errorInformation = [
                        'message' => 'badArgument ' . implode(', ', $unknownParameters),
                        'code' => Oai_Model_Error::BADARGUMENT
                    ];
                $valid = false;
            }

            if (true === $valid) {
                $errorInformation = [
                        'message' => 'no validation error',
                        'code' => null
                    ];
                break;
            }
        }

        if (false === $valid) {
            $this->setErrorMessage($errorInformation['message']);
            $this->setErrorCode($errorInformation['code']);
            $logger->err($this->getErrorCode() . "::" .$this->getErrorMessage());
            return false;
        }

        // check if request values are valid

        foreach ($oaiRequest as $parameter => $value) {
            $callname = 'validate' . ucfirst($parameter);
            if (true === method_exists($this, $callname)) {
                $result = $this->$callname($value);

                // if one validate call returns an error
                // do not check other parameter values.
                if (false === $result) {
                    // error code and message are set inside validate method
                    $logger->err(":: $parameter");
                    return false;
                }
            }
        }

        // Proof combination of from and until
        if ((true === array_key_exists('from', $oaiRequest)) and
            (true === array_key_exists('until', $oaiRequest))) {
                return $this->validateFromUntilRange($oaiRequest['from'], $oaiRequest['until']);
        }

        return true;
    }
}
