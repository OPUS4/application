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
 * @copyright   Copyright (c) 2020, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * TODO allow adding search path for types
 */
class Application_Search_FacetManager
{
    /** @var Zend_Config */
    private $config;

    /**
     * TODO need to get limit?
     *
     * @param string $name
     * @return Application_Search_Facet|null
     */
    public function getFacet($name)
    {
        $activeFacets = $this->getActiveFacets();

        if (! in_array($name, $activeFacets)) {
            // TODO log output err
            return null;
        }

        $config = $this->getFacetConfig($name);

        if (isset($config->type)) {
            $type = $config->type;
            if (! class_exists($type, false)) {
                $type = "Application_Search_Facet_$type";
            }
        } else {
            $type = Application_Search_Facet::class;
        }

        if (class_exists($type)) {
            $facet = new $type($name, $config->toArray());
        }
        // TODO BUG handle class does not exist error

        return $facet;
    }

    /**
     * @param string $name
     * @return Zend_Config
     */
    public function getFacetConfig($name)
    {
        // replace dot with dash because dots have a meaning in INI files
        $name = str_replace('.', '-', $name);

        // TODO merge default with specific
        // cache configuration
        $config = $this->getConfig();

        $default = new Zend_Config($config->search->facet->default->toArray(), true);

        if (isset($config->search->facet->$name)) {
            $facetConfig = $default->merge($config->search->facet->$name);
        } else {
            $facetConfig = $default;
        }

        return $facetConfig;
    }

    /**
     * @return Zend_Config
     */
    public function getConfig()
    {
        if ($this->config === null) {
            $this->config = Application_Configuration::getInstance()->getConfig();
        }
        return $this->config;
    }

    /**
     * TODO overlaps with Opus\Search\Config
     *
     * @return array
     */
    public function getActiveFacets()
    {
        $config = $this->getConfig();

        $facetsList = $config->searchengine->solr->facets;

        $facets = preg_split('/[\s,]+/', trim($facetsList ?? ''), 0, PREG_SPLIT_NO_EMPTY);

        // TODO how to define list of always active facets, like 'server_state'?
        if (! in_array('server_state', $facets)) {
            $facets = array_merge($facets, ['server_state']);
        }

        // TODO there must be a better way, also make year_inverted independent of year
        if (in_array('year_inverted', $facets)) {
            $temp = array_flip($facets);
            unset($temp['year_inverted']);
            $facets   = array_flip($facets);
            $facets[] = 'year';
        }

        return $facets;
    }
}
