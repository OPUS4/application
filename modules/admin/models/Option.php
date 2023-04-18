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

class Admin_Model_Option extends Application_Model_Abstract
{
    /** @var string Name of configuration option. */
    private $name;

    /** @var array Parameters for option. */
    private $config;

    /**
     * @param string $name Name of option
     * @param array  $config Parameters for option
     */
    public function __construct($name, $config)
    {
        $this->name   = $name;
        $this->config = $config;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->config['key'];
    }

    /**
     * Returns label name for option.
     *
     * @return string
     */
    public function getLabel()
    {
        return Admin_Form_Configuration::LABEL_TRANSLATION_PREFIX . $this->name;
    }

    /**
     * Returns translation key for option description.
     *
     * @return string
     */
    public function getDescription()
    {
        return Admin_Form_Configuration::LABEL_TRANSLATION_PREFIX . $this->name . '_description';
    }

    /**
     * Returns name of form element type for option.
     *
     * @return string
     */
    public function getElementType()
    {
        if (isset($this->config['type'])) {
            $type = $this->config['type'];
        } else {
            $type = 'text';
        }

        return $type;
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
}
