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
 * @copyright   Copyright (c) 2024, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\EnrichmentKey;
use Opus\Common\EnrichmentKeyInterface;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Imports Yaml configuration for Enrichments.
 *
 * TODO support output over OutputInterface
 */
class Application_Configuration_EnrichmentConfigImporter
{
    /** @var OutputInterface */
    private $output;

    /**
     * @param string      $filePath
     * @param string|null $keyName
     */
    public function import($filePath, $keyName = null)
    {
        $config = yaml_parse_file($filePath);

        $this->processConfig($config, $keyName);
    }

    /**
     * @param string      $yaml
     * @param string|null $keyName
     */
    public function importYaml($yaml, $keyName = null)
    {
        $config = yaml_parse($yaml);

        $this->processConfig($config, $keyName);
    }

    /**
     * @param array       $config
     * @param string|null $keyName
     */
    protected function processConfig($config, $keyName = null)
    {
        if (! $config || ! is_array($config)) {
            throw new InvalidArgumentException('First parameter should be an array');
        }

        $config = $this->changeKeysTolowerCase($config);

        if (isset($config['enrichments'])) {
            $enrichmentConfigs = $config['enrichments'];
        } else {
            if ($keyName !== null) {
                $config['name'] = $keyName;
            }
            $enrichmentConfigs = [$config];
        }

        foreach ($enrichmentConfigs as $enrichment) {
            $enrichmentKey = $this->createEnrichment($enrichment);
            if ($enrichmentKey !== null) {
                $name = $enrichmentKey->getName();
                $this->getOutput()->writeln("Created enrichment key '{$name}'");
            }
        }
    }

    /**
     * @param array $config
     * @return EnrichmentKeyInterface|null
     */
    public function createEnrichment($config)
    {
        $name = $config['name'];

        $enrichmentKey = EnrichmentKey::fetchByName($name);

        if ($enrichmentKey !== null) {
            $this->getOutput()->writeln("Enrichment '{$enrichmentKey}' already exists");
            return null;
        }

        $enrichmentKey = EnrichmentKey::new();

        $enrichmentKey->setName($name);

        $type = null;

        if (isset($config['type'])) {
            $type = ucfirst($config['type'] . 'Type');
            $enrichmentKey->setType($type); // TODO make 'Type' suffix unnecessary
        }

        if (isset($config['options']) && $type !== null) {
            $typeClass      = 'Opus\\Enrichment\\' . $type;
            $enrichmentType = new $typeClass();
            $options        = $config['options'];
            if (is_array($options)) {
                $enrichmentType->setOptions($options);
                $enrichmentKey->setOptions($enrichmentType->getOptions());
            } else {
                $enrichmentType->setOptionsFromString($options);
                $enrichmentKey->setOptions($enrichmentType->getOptions());
            }
        }

        $enrichmentKey->store();

        if (isset($config['translations'])) {
            $this->createTranslations($name, $config['translations']);
        }

        return $enrichmentKey;
    }

    /**
     * @param string $name
     * @param array  $translations
     */
    public function createTranslations($name, $translations)
    {
        $helper          = new Admin_Model_EnrichmentKeys();
        $keys            = array_keys($translations);
        $supportedKeys   = $helper->getSupportedKeys();
        $unsupportedKeys = array_diff($keys, $supportedKeys);

        if (count($unsupportedKeys) > 0) {
            foreach ($unsupportedKeys as $key) {
                $this->getOutput()->writeln("Unsupported translation key: {$key}");
            }
        }

        $helper->createTranslations($name, null, $translations);
    }

    /**
     * @param OutputInterface $output
     * @return $this
     */
    public function setOutput($output)
    {
        $this->output = $output;
        return $this;
    }

    /**
     * @return OutputInterface
     */
    public function getOutput()
    {
        if ($this->output === null) {
            $this->output = new NullOutput();
        }
        return $this->output;
    }

    /**
     * @param array $config
     * @return array
     */
    protected function changeKeysTolowerCase($config)
    {
        return array_map(function ($item) {
            if (is_array($item)) {
                $item = $this->changeKeysToLowerCase($item);
            }
            return $item;
        }, array_change_key_case($config, CASE_LOWER));
    }
}
