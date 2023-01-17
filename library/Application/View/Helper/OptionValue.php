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
 * @copyright   Copyright (c) 2017, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * View helper for returning value of configuration option.
 *
 * TODO support default value?
 * TODO return '' instead of null?
 */
class Application_View_Helper_OptionValue extends Application_View_Helper_Abstract
{
    /**
     * Returns value from configuration.
     *
     * The $context parameter is used as prefix for the option in the configuration.
     * The idea is to make this automatic for modules later, so that inside a module
     * options can be accessed directly without that prefix (TODO).
     *
     * @param string      $optionKey Name of option
     * @param string|null $context Context for option name (prefix)
     * @param bool        $escape Escape value before returning
     * @return null|string|Zend_Config
     */
    public function optionValue($optionKey, $context = null, $escape = false)
    {
        $key = $optionKey;

        if ($context !== null && strlen(trim($context)) !== 0) {
            $key = "$context.$key";
        }

        $value = Application_Configuration::getInstance()->getValue($key);

        if ($escape) {
            return htmlspecialchars($value);
        } else {
            return $value;
        }
    }
}
