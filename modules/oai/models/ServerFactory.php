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
use Opus\Common\Security\Realm;
use Opus\Common\Util\ClassLoaderHelper;

/**
 * Factory to create a OAI server model instance.
 */
class Oai_Model_ServerFactory
{
    use ConfigTrait;

    /**
     * Creates an oai server model by metaDataPrefix
     *
     * @param string $metaDataPrefix
     * @return Oai_Model_BaseServer
     */
    public function create($metaDataPrefix = '')
    {
        $options = $this->getFormatOptions($metaDataPrefix);

        $serverClass = $options['class'] ?? Oai_Model_BaseServer::class;

        if (empty($serverClass) || ! ClassLoaderHelper::classExists($serverClass)) {
            $server = new Oai_Model_BaseServer();
        } else {
            $server = new $serverClass();
        }

        $server->setOptions($options);

        return $server;
    }

    /**
     * Creates an oai server model by resumption token
     *
     * @param string $resumptionToken
     * @return Oai_Model_BaseServer
     */
    public function createByResumptionToken($resumptionToken)
    {
        $config = $this->getConfig();

        if (isset($config->workspacePath)) {
            $tempPath = $config->workspacePath
                . DIRECTORY_SEPARATOR . 'tmp'
                . DIRECTORY_SEPARATOR . 'resumption';
        }
        $tokenWorker = new Oai_Model_Resumptiontokens();
        $tokenWorker->setResumptionPath($tempPath);
        $token = $tokenWorker->getResumptionToken($resumptionToken);

        if ($token === null) {
            throw new Oai_Model_Exception("file could not be read.", Oai_Model_Error::BADRESUMPTIONTOKEN);
        }

        return $this->create($token->getMetadataPrefix());
    }

    /**
     * Gets all options for the oai server
     *
     * @param string $metadataPrefix
     * @return array
     */
    public function getFormatOptions($metadataPrefix = '')
    {
        $metadataPrefix = strtolower($metadataPrefix);

        $config = $this->getConfig();

        $generalOptions = $this->getGeneralOaiOptions();

        $defaultOptions = [];
        if (isset($config->oai->format->default)) {
            $defaultOptions = $config->oai->format->default->toArray();
        }

        $formatOptions = [];
        if (isset($config->oai->format->$metadataPrefix)) {
            $formatOptions = $config->oai->format->$metadataPrefix->toArray();
        }

        $options = array_merge($generalOptions, $defaultOptions, $formatOptions);

        if (isset($options['viewHelper'])) {
            $options['viewHelper'] = $this->mergeMultiValueOption(
                'viewHelper',
                $defaultOptions,
                $formatOptions
            );
        }

        if (isset($options['documentTypeRestriction'])) {
            $options['documentTypeRestriction'] = $this->mergeMultiValueOption(
                'documentTypeRestriction',
                $defaultOptions,
                $formatOptions
            );
        }

        return $options;
    }

    /**
     * Gets the general oai options from the configuration
     *
     * @return array
     */
    public function getGeneralOaiOptions()
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

        if (isset($config->workspacePath)) {
            $options['resumptionTokenPath'] = $config->workspacePath
                . DIRECTORY_SEPARATOR . 'tmp'
                . DIRECTORY_SEPARATOR . 'resumption';
        }

        if (isset($config->mail->opus->address)) {
            $options['emailContact'] = $config->mail->opus->address;
        }

        return $options;
    }

    /**
     * Merges the default and format specific configuration for an option
     * containing multiple values as an array or comma separated list
     *
     * @param string $optionName
     * @param array $defaultOptions
     * @param array $formatOptions
     * @return array
     */
    protected function mergeMultiValueOption($optionName, $defaultOptions, $formatOptions)
    {
        $default = $defaultOptions[$optionName] ?? [];
        $format  = $formatOptions[$optionName] ?? [];

        if (is_string($default)) {
            $default = array_map('trim', explode(',', $default));
        }

        if (is_string($format)) {
            $format = array_map('trim', explode(',', $format));
        }

        return array_unique(array_merge($default, $format));
    }

    /**
     * Gets all configured format prefixes
     *
     * @return array
     */
    public function getFormats()
    {
        $config = $this->getConfig();

        $prefixes = [];

        if (isset($config->oai->format)) {
            $formats = $config->oai->format->toArray();

            foreach ($formats as $formatIdentifier => $format) {
                if (isset($format['prefixLabel'])) {
                    $prefixLabels = $format['prefixLabel'];
                    if (is_string($prefixLabels)) {
                        $prefixLabels = array_map('trim', explode(',', $prefixLabels));
                    }

                    foreach ($prefixLabels as $prefixLabel) {
                        if ($prefixLabel) {
                            $prefixes[] = $prefixLabel;
                        }
                    }
                } else {
                    $prefixes[] = $formatIdentifier;
                }
            }
        }

        $prefixes = array_diff($prefixes, ['default']);

        // only administrators can request copy_xml format
        if (! Realm::getInstance()->checkModule('admin')) {
            $prefixes = array_diff($prefixes, ['copy_xml']);
        }

        return $prefixes;
    }
}
