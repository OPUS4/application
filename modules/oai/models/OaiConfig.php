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
 * @copyright   Copyright (c) 2023, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\ConfigTrait;

/**
 * Class to read configuration options for the oai server.
 */
class Oai_Model_OaiConfig
{
    use ConfigTrait;

    /**
     * Factory method
     *
     * @return self
     */
    public static function getInstance()
    {
        return new self();
    }

    /**
     * Gets the configured option defaults.
     *
     * @return array
     */
    public function getDefaults()
    {
        $config = $this->getConfig();

        $generalOptions = $this->getGeneralOaiOptions();

        $defaultOptions = [];

        if (isset($config->oai->format->default)) {
            $defaultOptions = $config->oai->format->default->toArray();
        }

        return array_merge($generalOptions, $defaultOptions);
    }

    /**
     * Gets the format specific options.
     *
     * @param string $metadataPrefix
     * @return array
     */
    public function getFormatOptions($metadataPrefix)
    {
        $metadataPrefix = strtolower($metadataPrefix);

        $config = $this->getConfig();

        $formatOptions = [];

        if (isset($config->oai->format)) {
            $formats = array_change_key_case($config->oai->format->toArray());

            if (isset($formats[$metadataPrefix])) {
                $formatOptions = $formats[$metadataPrefix];
            }
        }

        return $formatOptions;
    }

    /**
     * Gets the general oai options from the configuration
     *
     * @return array
     */
    protected function getGeneralOaiOptions()
    {
        $config = $this->getConfig();

        if (! isset($config->oai)) {
            throw new Exception('No configuration for module oai.');
        }

        $options = [];

        if (isset($config->oai->repository->name)) {
            $options['repositoryName'] = $config->oai->repository->name;
        }
        if (isset($config->oai->repository->identifier)) {
            $options['repositoryIdentifier'] = $config->oai->repository->identifier;
        }
        if (isset($config->oai->sample->identifier)) {
            $options['sampleIdentifier'] = $config->oai->sample->identifier;
        }
        if (isset($config->oai->max->listidentifiers)) {
            $options['maxListIdentifiers'] = (int) $config->oai->max->listidentifiers;
        }

        if (isset($config->oai->max->listrecords)) {
            $options['maxListRecords'] = (int) $config->oai->max->listrecords;
        }
        if (isset($config->oai->baseurl)) {
            $options['oaiBaseUrl'] = $config->oai->baseurl;
        }

        $options['resumptionTokenPath'] = $this->getResumptionTokenPath();

        if (isset($config->mail->opus->address)) {
            $options['emailContact'] = $config->mail->opus->address;
        }

        return $options;
    }

    /**
     * Gets all configured format prefixes
     *
     * @return string[]
     */
    public function getFormats()
    {
        $config = $this->getConfig();

        $prefixes = [];

        if (isset($config->oai->format)) {
            $formats  = $config->oai->format->toArray();
            $prefixes = array_keys($formats);
            $prefixes = array_map('strtolower', $prefixes);
            $prefixes = array_values(array_diff($prefixes, ['default']));
        }

        return $prefixes;
    }

    /**
     * Gets the path for resumption tokens
     *
     * @return string
     */
    public function getResumptionTokenPath()
    {
        $config = $this->getConfig();

        $resumptionTokenPath = '';

        if (isset($config->workspacePath)) {
            $resumptionTokenPath = $config->workspacePath
                . DIRECTORY_SEPARATOR . 'tmp'
                . DIRECTORY_SEPARATOR . 'resumption';
        }

        return $resumptionTokenPath;
    }
}
