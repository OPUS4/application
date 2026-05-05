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
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Db2\Configuration;

class Admin_Model_Option extends Application_Model_Abstract
{
    /** @var string Name of configuration option. */
    private $key;

    /** @var array Parameters for option. */
    private $config;

    public function __construct(string $key, array $config)
    {
        $this->key    = $key;
        $this->config = $config;
    }

    /**
     * Returns option key.
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * Returns translation key for option label.
     */
    public function getLabel(): string
    {
        return Admin_Form_Configuration::LABEL_TRANSLATION_PREFIX . $this->key;
    }

    /**
     * Returns translation key for option description.
     */
    public function getDescription(): string
    {
        return Admin_Form_Configuration::LABEL_TRANSLATION_PREFIX . $this->key . '_description';
    }

    public function getType(): string
    {
        if (isset($this->config['type'])) {
            return $this->config['type'];
        }

        return 'text';
    }

    /**
     * Returns name of form element type for option.
     *
     * @return string
     */
    public function getElementType()
    {
        $type = $this->getType();

        if (null === $type) {
            $type = 'text';
        }

        switch ($this->getType()) {
            case 'int':
                return 'number';
            case 'bool':
                return 'checkbox';
            case 'string':
                return 'text';
            default:
                return $type;
        }
    }

    public function getElementId(): string
    {
        return str_replace('.', '_', $this->getKey());
    }

    /**
     * Returns name of section in configuration for option.
     *
     * @return string
     */
    public function getSection()
    {
        if (isset($this->config['section'])) {
            $sectionName = $this->config['section'];
        } else {
            $sectionName = 'general';
        }

        return $sectionName;
    }

    /**
     * Returns additional options for configuration element.
     *
     * @return array
     */
    public function getOptions()
    {
        if (isset($this->config['options'])) {
            return $this->config['options'];
        } else {
            return [];
        }
    }

    public function getValue(): string
    {
        $configuration = new Configuration();
        return $configuration->getOption($this->getKey());
    }

    public function setValue(?string $value): self
    {
        $configuration = new Configuration();
        $configuration->setOption($this->getKey(), $value);
        return $this;
    }
}
