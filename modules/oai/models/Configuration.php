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
 * @copyright   Copyright (c) 2009, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Configuration model holding OAI module config options
 * gathered from Zend_Registry and application configuration.
 */
class Oai_Model_Configuration
{
    /**
     * Hold path where to store temporary resumption token files.
     *
     * @var string
     */
    private $pathTokens = '';

    /**
     * Holds email address of repository contact person.
     *
     * @var string
     */
    private $emailContact = '';

    /**
     * Holds repository name.
     *
     * @var string
     */
    private $repoName = '';

    /**
     * Holds repository identifier.
     *
     * @var string
     */
    private $repoId = '';

    /**
     * Holds sample identifier.
     *
     * @var string
     */
    private $sampleId = '';

    /**
     * Holds maximum number of identifiers to list per request.
     *
     * @var int
     */
    private $maxListIds = 10;

    /**
     * Holds maximum number of records to list per request.
     *
     * @var int
     */
    private $maxListRecs = 10;

    /**
     * Holds oai base url. If not given, local server name will be used.
     *
     * @var string
     */
    private $oaiBaseUrl = '';

    /**
     * Collect configuration information from Zend_Config instance.
     *
     * @throws Exception Thrown if no oai section is set.
     */
    public function __construct(Zend_Config $config)
    {
        if (false === isset($config->oai)) {
            throw new Exception('No configuration for module oai.');
        }

        if (true === isset($config->oai->repository->name)) {
            $this->repoName = $config->oai->repository->name;
        }
        if (true === isset($config->oai->repository->identifier)) {
            $this->repoId = $config->oai->repository->identifier;
        }
        if (true === isset($config->oai->sample->identifier)) {
            $this->sampleId = $config->oai->sample->identifier;
        }
        if (true === isset($config->oai->max->listidentifiers)) {
            $this->maxListIds = $config->oai->max->listidentifiers;
        }
        if (true === isset($config->oai->max->listrecords)) {
            $this->maxListRecs = $config->oai->max->listrecords;
        }
        if (true === isset($config->oai->baseurl)) {
            $this->oaiBaseUrl = $config->oai->baseurl;
        }

        if (true === isset($config->workspacePath)) {
            $this->pathTokens = $config->workspacePath
                . DIRECTORY_SEPARATOR . 'tmp'
                . DIRECTORY_SEPARATOR . 'resumption';
        }

        if (true === isset($config->mail->opus->address)) {
            $this->emailContact = $config->mail->opus->address;
        }
    }

    /**
     * Return temporary path for resumption tokens.
     *
     * @return string Path.
     */
    public function getResumptionTokenPath()
    {
        return $this->pathTokens;
    }

    /**
     * Return contact email address.
     *
     * @return string Email address.
     */
    public function getEmailContact()
    {
        return $this->emailContact;
    }

    /**
     * Return OAI base url.
     *
     * @return string Oai base url.
     */
    public function getOaiBaseUrl()
    {
        return $this->oaiBaseUrl;
    }

    /**
     * Return repository name.
     *
     * @return string Repository name.
     */
    public function getRepositoryName()
    {
        return $this->repoName;
    }

    /**
     * Return repository identifier.
     *
     * @return string Repository identifier.
     */
    public function getRepositoryIdentifier()
    {
        return $this->repoId;
    }

    /**
     * Return sample identifier.
     *
     * @return string Sample identifier.
     */
    public function getSampleIdentifier()
    {
        return $this->sampleId;
    }

    /**
     * Return maximum number of listable identifiers per request.
     *
     * @return int Maximum number of listable identifiers per request.
     */
    public function getMaxListIdentifiers()
    {
        return $this->maxListIds;
    }

    /**
     * Return maximum number of listable records per request.
     *
     * @return int Maximum number of listable records per request.
     */
    public function getMaxListRecords()
    {
        return $this->maxListRecs;
    }
}
