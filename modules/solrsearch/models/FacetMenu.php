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
 * @category    Application
 * @package     Module_Solrsearch
 * @author      Michael Lang <lang@zib.de>
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2016, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Class Solrsearch_Model_FacetMenu
 *
 * TODO refactor as view helper or something better
 */
class Solrsearch_Model_FacetMenu extends Application_Model_Abstract {

    private $_facets;

    private $_selectedFacets;

    private $_facetNumberContainer;

    private $_showFacetExtender;

    /**
     * Resolves the facet-options from URL and builds a result array with the number of facets to display.
     * @return array result[facet_name] = number
     */
    public function buildFacetArray($paramSet) {
	    return Opus_Search_Facet_Set::getFacetLimitsFromInput( $paramSet );
    }

    /**
     */
    public function prepareViewFacets($result, $request) {
        $facets = $result->getFacets();

        $facetLimit = Opus_Search_Config::getFacetLimits();

        $facetArray = array();
        $selectedFacets = array();
        $facetNumberContainer = array();
        $showFacetExtender = array();

        foreach ($facets as $key => $facet) {
            $showFacetExtender[$key] = ($facetLimit[$key] <= sizeof($facet));
            $this->getLogger()->debug("found $key facet in search results");
            $facetNumberContainer[$key] = sizeof($facet);
            $facetValue = $request->getParam($key . 'fq', '');
            if ($facetValue !== '') {
                $selectedFacets[$key] = $facetValue;
                $showFacetExtender[$key] = false;
            }

            if (count($facets[$key]) > 1 || $facetValue !== '') {
                $facetArray[$key] = $facet;
            }
        }

        // Hide institutes facet if collection does not exist or is hidden
        $institutes = Opus_CollectionRole::fetchByName('institutes');

        if (is_null($institutes) || !$institutes->getVisible()) {
            unset($facetArray['institute']);
        }

        $this->_facets = $facetArray;
        $this->_selectedFacets = $selectedFacets;
        $this->_facetNumberContainer = $facetNumberContainer;
        $this->_showFacetExtender = $showFacetExtender;
    }

    public function getFacets() {
        return $this->_facets;
    }

    public function getSelectedFacets() {
        return $this->_selectedFacets;
    }

    public function getFacetNumberContainer() {
        return $this->_facetNumberContainer;
    }

    public function getShowFacetExtender() {
        return $this->_showFacetExtender;
    }

}
