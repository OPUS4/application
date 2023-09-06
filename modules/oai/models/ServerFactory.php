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
use Opus\Common\Util\ClassLoaderHelper;

/**
 * Factory to create a OAI server model instance.
 */
class Oai_Model_ServerFactory
{
    use ConfigTrait;

    /** @var Oai_Model_OAIConfig */
    private $oaiConfig;

    public function __constructor()
    {
        $this->oaiConfig = Oai_Model_OAIConfig::getInstance();
    }

    /**
     * Gets the oai configuration
     *
     * @return Oai_Model_OAIConfig
     */
    public function getOaiConfig()
    {
        if ($this->oaiConfig === null) {
            $this->oaiConfig = Oai_Model_OAIConfig::getInstance();
            $this->oaiConfig->setConfig($this->getConfig());
        }

        return $this->oaiConfig;
    }

    /**
     * Sets the oai configuration
     *
     * @param Oai_Model_OAIConfig $oaiConfig
     */
    public function setOaiConfig($oaiConfig)
    {
        $this->oaiConfig = $oaiConfig;
    }

    /**
     * Creates an oai server model by metaDataPrefix
     *
     * @param string $metaDataPrefix
     * @return Oai_Model_DefaultServer
     */
    public function create($metaDataPrefix = '')
    {
        $options = $this->getOaiConfig()->getFormatOptions($metaDataPrefix);

        $serverClass = $options['class'] ?? Oai_Model_DefaultServer::class;

        if (empty($serverClass) || ! ClassLoaderHelper::classExists($serverClass)) {
            $server = new Oai_Model_DefaultServer($this->getConfig());
        } else {
            $server = new $serverClass($this->getConfig());
        }

        if ($options) {
            if (isset($options['viewHelper'])) {
                $previousViewHelper = $server->getViewHelper() ?: [];
                $viewHelper         = $options['viewHelper'];

                if (is_string($viewHelper)) {
                    $viewHelper = array_map('trim', explode(',', $viewHelper));
                }

                $options['viewHelper'] = array_unique(array_merge($previousViewHelper, $viewHelper));
            }

            $server->setOptions($options);
        }

        return $server;
    }

    /**
     * Creates an oai server model by resumption token
     *
     * @param string $resumptionToken
     * @return Oai_Model_DefaultServer
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
     * Gets all configured format prefixes
     *
     * @return array
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
}
