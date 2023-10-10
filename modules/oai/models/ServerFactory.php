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

    /** @var Oai_Model_OaiConfig */
    private $oaiConfig;

    /**
     * Gets the oai configuration
     *
     * @return Oai_Model_OaiConfig
     */
    public function getOaiConfig()
    {
        if ($this->oaiConfig === null) {
            $this->oaiConfig = Oai_Model_OaiConfig::getInstance();
        }

        return $this->oaiConfig;
    }

    /**
     * Sets the oai configuration
     *
     * @param Oai_Model_OaiConfig $oaiConfig
     */
    public function setOaiConfig($oaiConfig)
    {
        $this->oaiConfig = $oaiConfig;
    }

    /**
     * Creates an oai server model by metaDataPrefix
     *
     * @param string|null $metaDataPrefix
     * @return Oai_Model_DefaultServer
     */
    public function create($metaDataPrefix = null)
    {
        $oaiConfig = $this->getOaiConfig();

        $defaults = $oaiConfig->getDefaults();
        $options  = $metaDataPrefix ? $oaiConfig->getFormatOptions($metaDataPrefix) : null;

        if (isset($options['class'])) {
            $serverClass = $options['class'];
        } elseif (isset($defaults['class'])) {
            $serverClass = $defaults['class'];
        } else {
            $serverClass = Oai_Model_DefaultServer::class;
        }

        if (empty($serverClass) || ! ClassLoaderHelper::classExists($serverClass)) {
            $server = new Oai_Model_DefaultServer();
        } else {
            $server = new $serverClass();
        }

        $server->setOaiConfig($this->getOaiConfig());
        $server->initDefaults();

        if ($options) {
            if (isset($options['viewHelpers'])) {
                /*
                In order to prevent required view helpers (configured in the default configuration or directly
                in a derived server class) from being unintentionally removed by configuring a specific format,
                they will be always just appended to the existing ones.
                */

                $previousViewHelpers = $server->getViewHelpers();
                $viewHelpers         = $options['viewHelpers'];

                if (is_string($viewHelpers)) {
                    $viewHelpers = array_map('trim', explode(',', $viewHelpers));
                }

                $options['viewHelpers'] = array_unique(array_merge($previousViewHelpers, $viewHelpers));
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
        $resumptionTokenPath = $this->getOaiConfig()->getResumptionTokenPath();
        $tokenWorker         = new Oai_Model_Resumptiontokens();
        $tokenWorker->setResumptionPath($resumptionTokenPath);
        $token = $tokenWorker->getResumptionToken($resumptionToken);

        if ($token === null) {
            throw new Oai_Model_Exception("file could not be read.", Oai_Model_Error::BADRESUMPTIONTOKEN);
        }

        return $this->create($token->getMetadataPrefix());
    }
}
