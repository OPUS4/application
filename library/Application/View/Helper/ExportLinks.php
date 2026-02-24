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
 * View helper for rendering export links.
 *
 * TODO render link only if user has access to module
 */
class Application_View_Helper_ExportLinks extends Application_View_Helper_Abstract
{
    /**
     * Returns HTMl for rendering export links.
     *
     * @param string|string[]|null $keys Keys for parameters that should be included in export link
     * @param array|null           $context
     * @param int|null             $numOfHits
     * @return string HTML
     */
    public function exportLinks($keys = null, $context = null, $numOfHits = null)
    {
        return $this->toString($keys, $context, $numOfHits);
    }

    /**
     * @param string|string[]|null $keys
     * @param string|null          $context
     * @param int|null             $numOfHits
     * @return string
     * @throws Zend_Exception
     */
    public function toString($keys = null, $context = null, $numOfHits = null)
    {
        $exporter       = Zend_Registry::get('Opus_Exporter'); // TODO use constant
        $formats        = $exporter->getAllowedFormats();
        $allowExportAll = $this->isAllowExportAll();

        $output = '<ul>';

        foreach ($formats as $format) {
            // if context provided skip format if it has been set to false
            if ($context !== null && $format->get($context) === false) {
                continue;
            }

            $params = $format->getParams();

            if ($keys !== null) {
                if (! is_array($keys)) {
                    $keys = [$keys];
                }

                foreach ($keys as $key) {
                    $params[$key] = $this->view->$key;
                }
            }

            $format->setParams($params);

            if (! $format->exportAll || $allowExportAll) {
                $link = $this->renderLink($format, $context, $numOfHits);
                if ($link !== null) {
                    $output .= '<li>';
                    $output .= $link;
                    $output .= '</li>';
                }
            }
        }

        return $output . '</ul>';
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * @param Zend_Navigation_Page_Mvc $format
     * @param string|null              $context
     * @param int|null                 $numOfHits
     * @return string|null
     *
     * TODO use translations (register module translation first)
     * TODO add docId OR search parameters to link
     */
    public function renderLink($format, $context = null, $numOfHits = null)
    {
        $name        = $format->get('name');
        $description = $format->get('description');
        $formatClass = strtolower($name);
        $format->setResetParams(false);

        // Search export links should use default rows if no other value is provided as parameter
        if ($format->getParam('rows') === null && $context === 'search') {
            $format->setParam('rows', $this->getDefaultRows());
        }

        // TODO move enabled/diable logic outside of rendering code?
        if ($format->exportAll) {
            $exportService = Zend_Registry::get('Opus_ExportService');
            $exportPlugin  = $exportService->getPlugin($format->getAction());
            $maxRows       = $exportPlugin->getMaxRows();
            $numOfHits     = $numOfHits <= $maxRows ? $numOfHits : $maxRows;

            if ($numOfHits < $format->getParam('rows')) {
                return null;
            }
        }

        // Frontdoor links export a single document
        if ($context === 'frontdoor' || $format->exportAll) {
            $format->setParam('rows', null);
        }

        if ($format->label !== null) {
            $label = $format->getLabel();
        } else {
            $label = $name;
        }

        $name = sprintf($label, $numOfHits);

        return "<a href=\"{$format->getHref()}\" title=\"$description\" class=\"export $formatClass\">$name</a>";
    }

    public function getDefaultRows(): int
    {
        $config = $this->getConfig();
        if (isset($config->searchengine->solr->parameterDefaults->rows)) {
            $rows = filter_var($config->searchengine->solr->parameterDefaults->rows, FILTER_SANITIZE_NUMBER_INT);
        } else {
            $rows = 10;
        }

        return $rows;
    }

    public function isAllowExportAll(): bool
    {
        $config = $this->getConfig();
        if (isset($config->export->allowExportAll)) {
            return filter_var($config->export->allowExportAll, FILTER_VALIDATE_BOOLEAN);
        } else {
            return false;
        }
    }
}
