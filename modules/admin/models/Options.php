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

/**
 * TODO refactor this class (cleanup the design)
 * TODO cleanup dependency on module/admin
 */
class Admin_Model_Options extends Application_Model_Abstract
{
    /**
     * Path to options configuration.
     */
    public const OPTIONS_CONFIG_FILE = '/modules/admin/models/options.json';

    /** @var array Option objects. */
    private $options;

    /** @var Zend_Config */
    private $config;

    /**
     * @param Zend_Config|null $config
     *
     * TODO allow providing Zend_Config object
     */
    public function __construct($config = null)
    {
        if ($config !== null && is_array($config)) {
            $this->config = new Zend_Config($config);
        }
    }

    /**
     * Returns options configuration from file.
     *
     * @return array
     */
    public function getOptions()
    {
        if ($this->options === null) {
            $this->options = [];
            $config        = $this->getConfig();
            $options       = $config->toArray();

            foreach ($options as $name => $parameters) {
                $this->options[$name] = new Admin_Model_Option($name, $parameters);
            }
        }

        return $this->options;
    }

    /**
     * @return Zend_Config
     * @throws Zend_Config_Exception
     */
    public function getConfig()
    {
        if ($this->config === null) {
            $this->config = new Zend_Config_Json(APPLICATION_PATH . self::OPTIONS_CONFIG_FILE);
        }

        return $this->config;
    }
}
