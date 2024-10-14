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

use Opus\Common\CollectionRole;
use Opus\Search\Facet\Set;

/**
 * TODO refactor as view helper or something better
 */
class Solrsearch_Model_FacetMenu extends Application_Model_Abstract
{
    /**
     * Resolves the facet-options from URL and builds a result array with the number of facets to display.
     *
     * @param array $paramSet
     * @return array result[facet_name] = number
     */
    public function buildFacetArray($paramSet)
    {
        // TODO not sure I like processing request parameters in *opus4-search* class (backend code)
        return Set::getFacetLimitsFromInput($paramSet);
    }

    /**
     * This merges facets configurations and actual facet results.
     *
     * TODO create Facet objects here an populate with data (might be moved later, but is a start)
     *
     * @param Base                         $result
     * @param Zend_Controller_Request_Http $request
     * @return array
     */
    public function getFacets($result, $request)
    {
        $facetManager = new Application_Search_FacetManager();

        $indexFields = $result->getFacets(); // facets returned from search

        $openFacets = $this->buildFacetArray($request->getParams());

        $facetArray = [];

        $facets = $facetManager->getActiveFacets();

        foreach ($facets as $name) {
            $facet = $facetManager->getFacet($name);

            if ($facet === null) {
                continue;
            }

            $indexFieldName = $facet->getIndexField();
            $facetValue     = $request->getParam($name . 'fq', '');

            if (isset($indexFields[$indexFieldName]) && (count($indexFields[$indexFieldName]) > 0 || $facetValue !== '')) {
                $this->getLogger()->debug("found $name facet in search results");

                $values = $indexFields[$indexFieldName];

                if ($facet === null || ! $facet->isAllowed()) {
                    continue;
                }

                $facet->setValues($values);

                if ($facetValue !== '') {
                    $facet->setSelected($facetValue);
                    $facet->setShowFacetExtender(false);
                } else {
                    // TODO encapsulate in Facet object
                    $facet->setShowFacetExtender($facet->getLimit() <= count($values));
                }

                $facet->setOpen(isset($openFacets[$name]));

                $facetArray[$name] = $facet;
            }
        }

        // Hide institutes facet if collection does not exist or is hidden TODO handle somewhere else
        $institutes = CollectionRole::fetchByName('institutes');

        if ($institutes === null || ! $institutes->getVisible()) {
            unset($facetArray['institute']);
        }

        return $facetArray;
    }
}
