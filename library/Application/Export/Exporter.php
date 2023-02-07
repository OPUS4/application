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

use Opus\Common\Security\Realm;

/**
 * Class for registering and accessing export format handlers.
 */
class Application_Export_Exporter
{
    /** @var array */
    private $formats = [];

    /**
     * @param array $options
     * @throws Zend_Navigation_Exception
     */
    public function addFormats($options)
    {
        if (! is_array($options)) {
            throw new Exception('Invalid argument: should be array');
        }

        foreach ($options as $key => $option) {
            // TODO use addFormat function, get key from 'name' or use hash?
            $format = new Zend_Navigation_Page_Mvc($option);

            // TODO check if key is string
            $this->formats[$key] = $format;
        }
    }

    /**
     * @return array
     */
    public function getFormats()
    {
        return $this->formats;
    }

    public function removeAll()
    {
        $this->formats = [];
    }

    /**
     * @return array
     */
    public function getAllowedFormats()
    {
        $formats = $this->getFormats();

        $allowed = [];

        foreach ($formats as $format) {
            $module = $format->getModule();

            if (Realm::getInstance()->checkModule($module)) {
                $allowed[] = $format;
            }
        }

        usort($allowed, function ($one, $two) {
            return strcmp($one->get('name'), $two->get('name'));
        });

        return $allowed;
    }
}
