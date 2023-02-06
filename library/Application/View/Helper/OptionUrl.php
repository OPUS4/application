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
 * Gets a URL from a configuration option and renders it properly.
 *
 * URL can be configured in three ways
 *
 * 'home' - will be prefixed with full base url
 * '/home' - will be prefixed with server url
 * 'http://www.opus-repository.org' - will be used without modifications
 */
class Application_View_Helper_OptionUrl extends Application_View_Helper_Abstract
{
    /**
     * @param string      $optionKey
     * @param string|null $context
     * @return string|Zend_Config
     */
    public function optionUrl($optionKey, $context = null)
    {
        $key = $optionKey;

        if ($context !== null && strlen(trim($context)) !== 0) {
            $key = "$context.$key";
        }

        $value = Application_Configuration::getInstance()->getValue($key);

        $urlParts = parse_url($value);

        if (array_key_exists('scheme', $urlParts)) {
            // apparently full URL configured
            $url = $value;
        } else {
            if ($value[0] === '/') {
                // absolute server URL configured
                $url = $this->view->serverUrl() . $value;
            } else {
                $url = $this->view->baseUrl() . '/' . $value;
            }
        }

        return $url;
    }
}
